<?php
/**
 * EIMBox Platform Operational Guard (Web Modern / Materio Edition)
 * File: eimbox-materio/core/PlatformGuard.php
 * 
 * Enforces real-time operational status (active, maintenance, isolated, read_only)
 * managed via Platform Manager in eimbox-core.
 * 
 * Architecture:
 * - Direct MySQLi query against central DB (zero cross-repo filesystem paths).
 * - Multi-subdomain safe and fail-safe (fails open if table missing).
 * - Android Material Design 3 (M3) & Modern Materio layout.
 * - Strictly normal font-weights (no bold / fw-bold / <b> / <strong>).
 */

class MaterioPlatformGuard
{
    private static ?array $cachedStatus = null;
    private const PLATFORM_KEY = 'web_modern';

    /**
     * Fetch status record from database
     */
    public static function getStatus($conn): array
    {
        if (self::$cachedStatus !== null) {
            return self::$cachedStatus;
        }

        $default = [
            'platform_key' => self::PLATFORM_KEY,
            'status' => 'active',
            'headline' => 'Operational',
            'message' => 'All systems functional.',
            'estimated_uptime' => null,
            'allow_admin_bypass' => 1
        ];

        if (!$conn || !($conn instanceof mysqli)) {
            self::$cachedStatus = $default;
            return $default;
        }

        try {
            $stmt = $conn->prepare("SELECT platform_key, platform_name, status, headline, message, estimated_uptime, allow_admin_bypass FROM core_platform_operational_status WHERE platform_key = ? LIMIT 1");
            if ($stmt) {
                $key = self::PLATFORM_KEY;
                $stmt->bind_param("s", $key);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    self::$cachedStatus = array_merge($default, $row);
                    $stmt->close();
                    return self::$cachedStatus;
                }
                $stmt->close();
            }
        } catch (\Throwable $e) {
            // Fail-open for safety
            error_log("PlatformGuard web_modern status query error: " . $e->getMessage());
        }

        self::$cachedStatus = $default;
        return $default;
    }

    /**
     * Enforce status against current request and user context
     */
    public static function check($conn, array $userContext = []): bool
    {
        $statusInfo = self::getStatus($conn);
        $status = strtolower($statusInfo['status'] ?? 'active');

        if ($status === 'active') {
            return true;
        }

        $isAdmin = self::isAdminUser($userContext);
        $allowBypass = !empty($statusInfo['allow_admin_bypass']);

        // 1. Maintenance Mode
        if ($status === 'maintenance') {
            if ($allowBypass && $isAdmin) {
                $GLOBALS['PLATFORM_MAINTENANCE_ADMIN_BYPASS'] = true;
                return true;
            }
            self::handleBlock(503, 'maintenance', $statusInfo);
        }

        // 2. Isolated Mode (Emergency Quarantine)
        if ($status === 'isolated') {
            $isSuperAdmin = self::isSuperAdminUser($userContext);
            if ($allowBypass && $isSuperAdmin) {
                $GLOBALS['PLATFORM_ISOLATION_ADMIN_BYPASS'] = true;
                return true;
            }
            self::handleBlock(503, 'isolated', $statusInfo);
        }

        // 3. Read-Only Mode
        if ($status === 'read_only') {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $isMutation = in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']);

            if ($isMutation) {
                if ($allowBypass && $isAdmin) {
                    return true;
                }
                self::handleMutationBlock($statusInfo);
            } else {
                $GLOBALS['PLATFORM_READ_ONLY_ACTIVE'] = true;
                return true;
            }
        }

        return true;
    }

    /**
     * Automatically extract user context from $_SESSION if not explicitly provided
     */
    public static function extractSessionContext(): array
    {
        $admin = 0;
        if (isset($_SESSION['isadmin'])) {
            $admin = intval($_SESSION['isadmin']);
        } elseif (isset($_SESSION['admin'])) {
            $admin = intval($_SESSION['admin']);
        }

        $userlevel = $_SESSION['userlevel'] ?? '';
        $userRole = $_SESSION['user_role'] ?? '';
        $userName = $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? '';
        $isChief = (strtolower((string)$userlevel) === 'chief' || !empty($_SESSION['is_chief'])) ? 1 : 0;

        return [
            'is_admin' => $admin,
            'userlevel' => $userlevel,
            'user_role' => $userRole,
            'user_name' => $userName,
            'is_chief' => $isChief
        ];
    }

    /**
     * Determine if user context has Administrator privileges (Level 4+)
     */
    public static function isAdminUser(array $userContext = []): bool
    {
        if (empty($userContext)) {
            $userContext = self::extractSessionContext();
        }

        $adminLevel = intval($userContext['is_admin'] ?? 0);
        $chiefLevel = intval($userContext['is_chief'] ?? 0);
        $userlevel = strtolower(trim((string)($userContext['userlevel'] ?? '')));
        $userRole = strtolower(trim((string)($userContext['user_role'] ?? '')));
        $userName = strtolower(trim((string)($userContext['user_name'] ?? '')));

        if ($adminLevel >= 4 || $chiefLevel > 0) {
            return true;
        }

        if ($userRole === 'admin') {
            return true;
        }

        if (in_array($userlevel, ['super administrator', 'chief', 'administrator', 'moderator'])) {
            return true;
        }

        if ($userName === 'engrreaz@gmail.com') {
            return true;
        }

        return false;
    }

    /**
     * Determine if user context has Super Administrator / Chief privileges (Level 5)
     */
    public static function isSuperAdminUser(array $userContext = []): bool
    {
        if (empty($userContext)) {
            $userContext = self::extractSessionContext();
        }

        $adminLevel = intval($userContext['is_admin'] ?? 0);
        $chiefLevel = intval($userContext['is_chief'] ?? 0);
        $userlevel = strtolower(trim((string)($userContext['userlevel'] ?? '')));
        $userName = strtolower(trim((string)($userContext['user_name'] ?? '')));

        if ($adminLevel >= 5 || $chiefLevel > 0) {
            return true;
        }

        if (in_array($userlevel, ['super administrator', 'chief'])) {
            return true;
        }

        if ($userName === 'engrreaz@gmail.com') {
            return true;
        }

        return false;
    }

    /**
     * Handle block for Maintenance or Isolated mode
     */
    private static function handleBlock(int $httpCode, string $mode, array $statusInfo): void
    {
        http_response_code($httpCode);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'status' => $mode,
                'headline' => $statusInfo['headline'] ?? '',
                'message' => $statusInfo['message'] ?? '',
                'estimated_uptime' => $statusInfo['estimated_uptime'] ?? null
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        self::renderScreen($httpCode, $mode, $statusInfo);
        exit;
    }

    /**
     * Handle block for mutation requests in Read-Only mode
     */
    private static function handleMutationBlock(array $statusInfo): void
    {
        http_response_code(423); // 423 Locked

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_GET['format']) && $_GET['format'] === 'json');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'status' => 'read_only',
                'error' => 'Modifications Locked',
                'message' => 'Platform is currently in read-only mode. New submissions or modifications are temporarily disabled.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        self::renderScreen(423, 'read_only_locked', $statusInfo);
        exit;
    }

    /**
     * Render full-screen Material Design 3 error / status screen
     */
    public static function renderScreen(int $httpCode, string $mode, array $statusInfo): void
    {
        $headline = htmlspecialchars($statusInfo['headline'] ?: ($mode === 'isolated' ? 'System Temporarily Isolated' : 'System Under Scheduled Maintenance'));
        $message = nl2br(htmlspecialchars($statusInfo['message'] ?: ($mode === 'isolated' ? 'This platform has been temporarily quarantined for security inspection. Please check back later.' : 'We are performing routine maintenance and optimization to improve your experience. Services will resume shortly.')));
        $uptime = $statusInfo['estimated_uptime'] ? htmlspecialchars($statusInfo['estimated_uptime']) : null;
        $allowBypass = !empty($statusInfo['allow_admin_bypass']);

        $isMaintenance = ($mode === 'maintenance');
        $isIsolated = ($mode === 'isolated');
        $isReadOnly = ($mode === 'read_only_locked');

        $iconClass = $isMaintenance ? 'bi-cone-striped' : ($isIsolated ? 'bi-slash-circle' : 'bi-lock');
        $pillText = $isMaintenance ? 'Scheduled Maintenance' : ($isIsolated ? 'Emergency Isolation' : 'Read-Only Locked');
        $accentColor = $isMaintenance ? '#F59E0B' : ($isIsolated ? '#EF4444' : '#38BDF8');
        $accentBg = $isMaintenance ? 'rgba(245, 158, 11, 0.15)' : ($isIsolated ? 'rgba(239, 68, 68, 0.15)' : 'rgba(56, 189, 248, 0.15)');
        ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $headline ?> - EIMBox Console</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --m3-bg: #141218;
            --m3-surface: #1D1B20;
            --m3-surface-container: #211F26;
            --m3-outline: #49454F;
            --m3-text-high: #E6E1E5;
            --m3-text-med: #CAC4D0;
            --m3-text-muted: #938F99;
            --m3-primary: #D0BCFF;
            --m3-on-primary: #381E72;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-weight: normal !important;
        }

        body {
            background-color: var(--m3-bg);
            color: var(--m3-text-high);
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 16px;
        }

        .m3-status-card {
            width: 100%;
            max-width: 460px;
            background-color: var(--m3-surface);
            border: 1px solid var(--m3-outline);
            border-radius: 24px;
            padding: 32px 24px 24px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.45);
        }

        .m3-state-icon-circle {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background-color: <?= $accentBg ?>;
            color: <?= $accentColor ?>;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 16px;
        }

        .m3-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            background-color: <?= $accentBg ?>;
            color: <?= $accentColor ?>;
            font-size: 0.76rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .m3-title {
            color: var(--m3-text-high);
            font-size: 1.3rem;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .m3-body {
            color: var(--m3-text-med);
            font-size: 0.88rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .m3-info-box {
            background-color: var(--m3-surface-container);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 12px 14px;
            text-align: left;
            margin-bottom: 20px;
            font-size: 0.8rem;
        }

        .btn-m3-primary {
            background-color: var(--m3-primary);
            color: var(--m3-on-primary);
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            transition: opacity 0.2s ease;
        }

        .btn-m3-primary:hover, .btn-m3-primary:focus {
            color: var(--m3-on-primary);
            opacity: 0.9;
        }

        .btn-m3-tonal {
            background-color: var(--m3-surface-container);
            color: var(--m3-text-med);
            border: 1px solid var(--m3-outline);
            border-radius: 12px;
            padding: 10px 16px;
            font-size: 0.82rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-m3-tonal:hover {
            color: var(--m3-text-high);
            background-color: rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body>

<div class="m3-status-card">
    <div class="m3-state-icon-circle">
        <i class="bi <?= $iconClass ?>"></i>
    </div>

    <div>
        <span class="m3-status-pill">
            <i class="bi bi-info-circle"></i> <?= $pillText ?>
        </span>
    </div>

    <h1 class="m3-title"><?= $headline ?></h1>
    <div class="m3-body"><?= $message ?></div>

    <?php if ($uptime): ?>
        <div class="m3-info-box">
            <div class="d-flex align-items-center gap-2 mb-1" style="color: <?= $accentColor ?>;">
                <i class="bi bi-clock-history"></i>
                <span>Estimated Completion</span>
            </div>
            <div style="color: var(--m3-text-high);" id="etaDisplay"><?= $uptime ?></div>
            <div id="countdownBox" style="color: var(--m3-text-muted); font-size: 0.74rem; margin-top: 4px;"></div>
        </div>
        <script>
            (function() {
                var target = new Date("<?= str_replace(' ', 'T', $uptime) ?>").getTime();
                if (!isNaN(target)) {
                    function updateCountdown() {
                        var now = new Date().getTime();
                        var diff = target - now;
                        var box = document.getElementById("countdownBox");
                        if (!box) return;
                        if (diff <= 0) {
                            box.innerText = "Finishing system checks... Refresh to continue.";
                        } else {
                            var hours = Math.floor(diff / (1000 * 60 * 60));
                            var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            var secs = Math.floor((diff % (1000 * 60)) / 1000);
                            box.innerText = "Remaining: " + (hours > 0 ? hours + "h " : "") + mins + "m " + secs + "s";
                        }
                    }
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                }
            })();
        </script>
    <?php endif; ?>

    <div class="d-grid gap-2">
        <button type="button" class="btn-m3-primary" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh / Check Status
        </button>

        <?php if ($isReadOnly): ?>
            <button type="button" class="btn-m3-tonal" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Return to Previous Page
            </button>
        <?php elseif ($allowBypass && !$isIsolated): ?>
            <a href="login.php" class="btn-m3-tonal">
                <i class="bi bi-shield-lock"></i> Administrator Sign In
            </a>
        <?php endif; ?>
    </div>

    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25" style="font-size: 0.72rem; color: var(--m3-text-muted);">
        <div>EIMBox Platform Operational Safety Engine</div>
    </div>
</div>

</body>
</html>
        <?php
    }
}

if (!class_exists('PlatformGuard', false)) {
    class_alias('MaterioPlatformGuard', 'PlatformGuard');
}
