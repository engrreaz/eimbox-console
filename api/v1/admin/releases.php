<?php
/**
 * EIMBox REST API - Admin Release Notes, Changelog & System Notifications
 * Endpoint: /api/v1/admin/releases.php
 * Routes:
 *   GET /api/v1/admin/releases.php (Fetch all release notes)
 *   POST /api/v1/admin/releases.php (Create/publish release note)
 *   PUT /api/v1/admin/releases.php (Update release note)
 *   DELETE /api/v1/admin/releases.php (Delete release note)
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = null;
try {
    $auth = authenticate_token($conn);
} catch (Exception $e) {}

$method = $_SERVER['REQUEST_METHOD'];
$input = get_api_input();

// Auto-create app_releases table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `app_releases` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(20) DEFAULT 'Desktop',
    `version` VARCHAR(30) NOT NULL,
    `release_name` VARCHAR(255) DEFAULT NULL,
    `release_date` DATE NOT NULL,
    `category` VARCHAR(50) DEFAULT 'Feature Update',
    `is_mandatory` TINYINT(1) DEFAULT 0,
    `summary` TEXT,
    `features` TEXT,
    `fixes` TEXT,
    `changelog_md` MEDIUMTEXT,
    `download_url` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `modifieddate` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

switch ($method) {
    case 'GET':
        $releases = [];
        $res = $conn->query("SELECT * FROM `app_releases` ORDER BY `release_date` DESC, `id` DESC LIMIT 100");
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $features = is_string($row['features']) ? json_decode($row['features'], true) : [];
                if (!is_array($features)) {
                    $features = array_filter(array_map('trim', explode("\n", $row['features'] ?? '')));
                }
                $fixes = is_string($row['fixes']) ? json_decode($row['fixes'], true) : [];
                if (!is_array($fixes)) {
                    $fixes = array_filter(array_map('trim', explode("\n", $row['fixes'] ?? '')));
                }

                $releases[] = [
                    'id' => intval($row['id']),
                    'platform' => $row['platform'] ?? 'Desktop',
                    'version' => $row['version'],
                    'title' => $row['release_name'] ?? $row['version'],
                    'release_name' => $row['release_name'] ?? '',
                    'release_date' => $row['release_date'],
                    'category' => $row['category'] ?? 'Feature Update',
                    'is_mandatory' => intval($row['is_mandatory'] ?? 0),
                    'summary' => $row['summary'] ?? '',
                    'features' => array_values($features),
                    'fixes' => array_values($fixes),
                    'download_url' => $row['download_url'] ?? '',
                    'created_at' => $row['created_at']
                ];
            }
        }

        // Seed default release history if empty
        if (empty($releases)) {
            $defaultReleases = [
                [
                    'platform' => 'Desktop',
                    'version' => 'v1.1.0',
                    'release_name' => 'Admin Panel Suite & Secondary Activity Dock',
                    'release_date' => '2026-08-29',
                    'category' => 'Major Release',
                    'is_mandatory' => 0,
                    'summary' => 'Comprehensive multi-tenant admin console, real-time live usage monitor, and institutional billing engine.',
                    'features' => json_encode([
                        'Secondary Activity Bar Admin dock with institutional live telemetry.',
                        'Multi-tenant User & Role-based Access Control manager with one-click password reset.',
                        'Central Billing, Subscriptions, and Broadcast Notice Dispatcher.',
                        'User Ticket and Helpdesk issue tracker.'
                    ]),
                    'fixes' => json_encode([
                        'Optimized SQLite sync engine for high-concurrency offline POS fee collection.',
                        'Fixed tabulating sheet 4th subject GPA calculation in result processor.'
                    ]),
                    'download_url' => 'https://console.eimbox.com/downloads/eimbox-desktop-v1.1.0-setup.exe'
                ],
                [
                    'platform' => 'Desktop',
                    'version' => 'v1.0.1',
                    'release_name' => 'POS Fee Counter & Academic Cache Enhancements',
                    'release_date' => '2026-08-20',
                    'category' => 'Feature Update',
                    'is_mandatory' => 0,
                    'summary' => 'Direct POS thermal receipt reprint, automatic fine calculation, and admission waiving rules.',
                    'features' => json_encode([
                        'Instant ESC/POS thermal printer output for fee receipts.',
                        'Multi-year session student demographic statistics.'
                    ]),
                    'fixes' => json_encode([
                        'Prevented duplicate transaction ID generation during concurrent offline cash collections.'
                    ]),
                    'download_url' => 'https://console.eimbox.com/downloads/eimbox-desktop-v1.0.1-setup.exe'
                ]
            ];

            foreach ($defaultReleases as $dr) {
                $st = $conn->prepare("INSERT INTO `app_releases` (`platform`, `version`, `release_name`, `release_date`, `category`, `is_mandatory`, `summary`, `features`, `fixes`, `download_url`, `modifieddate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $st->bind_param('sssssissss', $dr['platform'], $dr['version'], $dr['release_name'], $dr['release_date'], $dr['category'], $dr['is_mandatory'], $dr['summary'], $dr['features'], $dr['fixes'], $dr['download_url']);
                $st->execute();
                $st->close();
            }

            // Re-fetch
            $res = $conn->query("SELECT * FROM `app_releases` ORDER BY `release_date` DESC, `id` DESC LIMIT 100");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $features = is_string($row['features']) ? json_decode($row['features'], true) : [];
                    $fixes = is_string($row['fixes']) ? json_decode($row['fixes'], true) : [];
                    $releases[] = [
                        'id' => intval($row['id']),
                        'platform' => $row['platform'] ?? 'Desktop',
                        'version' => $row['version'],
                        'title' => $row['release_name'] ?? $row['version'],
                        'release_name' => $row['release_name'] ?? '',
                        'release_date' => $row['release_date'],
                        'category' => $row['category'] ?? 'Feature Update',
                        'is_mandatory' => intval($row['is_mandatory'] ?? 0),
                        'summary' => $row['summary'] ?? '',
                        'features' => is_array($features) ? array_values($features) : [],
                        'fixes' => is_array($fixes) ? array_values($fixes) : [],
                        'download_url' => $row['download_url'] ?? '',
                        'created_at' => $row['created_at']
                    ];
                }
            }
        }

        api_response('success', 'Release notes and changelog loaded', $releases);
        break;

    case 'POST':
        if (!$auth) {
            api_response('error', 'Authentication required to publish release notes.', null, 401);
        }

        $platform = trim($input['platform'] ?? 'Desktop');
        $version = trim($input['version'] ?? '');
        $title = trim($input['title'] ?? $input['release_name'] ?? '');
        $releaseDate = trim($input['release_date'] ?? date('Y-m-d'));
        $category = trim($input['category'] ?? 'Feature Update');
        $isMandatory = isset($input['is_mandatory']) ? intval($input['is_mandatory']) : 0;
        $summary = trim($input['summary'] ?? '');
        $features = $input['features'] ?? [];
        $fixes = $input['fixes'] ?? [];
        $downloadUrl = trim($input['download_url'] ?? '');

        if (empty($version) || empty($title)) {
            api_response('error', 'Version and Release Title are required.', null, 400);
        }

        $featuresJson = is_array($features) ? json_encode(array_values(array_filter($features))) : json_encode([]);
        $fixesJson = is_array($fixes) ? json_encode(array_values(array_filter($fixes))) : json_encode([]);

        $stmt = $conn->prepare("INSERT INTO `app_releases` (`platform`, `version`, `release_name`, `release_date`, `category`, `is_mandatory`, `summary`, `features`, `fixes`, `download_url`, `modifieddate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('sssssissss', $platform, $version, $title, $releaseDate, $category, $isMandatory, $summary, $featuresJson, $fixesJson, $downloadUrl);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

        api_response('success', "Release $version published successfully", [
            'id' => $insertId,
            'platform' => $platform,
            'version' => $version,
            'title' => $title,
            'release_name' => $title,
            'release_date' => $releaseDate,
            'category' => $category,
            'is_mandatory' => $isMandatory,
            'summary' => $summary,
            'features' => is_array($features) ? $features : [],
            'fixes' => is_array($fixes) ? $fixes : [],
            'download_url' => $downloadUrl
        ]);
        break;

    case 'PUT':
        if (!$auth) {
            api_response('error', 'Authentication required to update release notes.', null, 401);
        }

        $id = intval($input['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid Release ID is required for update.', null, 400);
        }

        $platform = trim($input['platform'] ?? 'Desktop');
        $version = trim($input['version'] ?? '');
        $title = trim($input['title'] ?? $input['release_name'] ?? '');
        $releaseDate = trim($input['release_date'] ?? date('Y-m-d'));
        $category = trim($input['category'] ?? 'Feature Update');
        $isMandatory = isset($input['is_mandatory']) ? intval($input['is_mandatory']) : 0;
        $summary = trim($input['summary'] ?? '');
        $features = $input['features'] ?? [];
        $fixes = $input['fixes'] ?? [];
        $downloadUrl = trim($input['download_url'] ?? '');

        if (empty($version) || empty($title)) {
            api_response('error', 'Version and Release Title are required.', null, 400);
        }

        $featuresJson = is_array($features) ? json_encode(array_values(array_filter($features))) : json_encode([]);
        $fixesJson = is_array($fixes) ? json_encode(array_values(array_filter($fixes))) : json_encode([]);

        $stmt = $conn->prepare("UPDATE `app_releases` SET `platform` = ?, `version` = ?, `release_name` = ?, `release_date` = ?, `category` = ?, `is_mandatory` = ?, `summary` = ?, `features` = ?, `fixes` = ?, `download_url` = ?, `modifieddate` = NOW() WHERE `id` = ?");
        $stmt->bind_param('sssssissssi', $platform, $version, $title, $releaseDate, $category, $isMandatory, $summary, $featuresJson, $fixesJson, $downloadUrl, $id);
        $stmt->execute();
        $stmt->close();

        api_response('success', "Release $version updated successfully", [
            'id' => $id,
            'platform' => $platform,
            'version' => $version,
            'title' => $title,
            'release_name' => $title,
            'release_date' => $releaseDate,
            'category' => $category,
            'is_mandatory' => $isMandatory,
            'summary' => $summary,
            'features' => is_array($features) ? $features : [],
            'fixes' => is_array($fixes) ? $fixes : [],
            'download_url' => $downloadUrl
        ]);
        break;

    case 'DELETE':
        if (!$auth) {
            api_response('error', 'Authentication required to delete release notes.', null, 401);
        }

        $id = intval($input['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid Release ID is required for deletion.', null, 400);
        }

        $stmt = $conn->prepare("DELETE FROM `app_releases` WHERE `id` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        api_response('success', "Release note #{$id} deleted successfully.", ['id' => $id]);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
