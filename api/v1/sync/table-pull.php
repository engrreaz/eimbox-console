<?php
/**
 * EIMBox REST API - Table Row Pull Synchronization Endpoint
 * Route: GET /api/v1/sync/table-pull.php
 * Params: ?table={table_name}&sccode={sccode}&since={timestamp}&session={sessionyear}
 */

require_once __DIR__ . '/../bootstrap.php';
// Authenticate Request
$user = function_exists('api_authenticate_request') ? api_authenticate_request() : authenticate_token($conn);
$sccode = (int)($user['sccode'] ?? 0);

if (!isset($conn) || !$conn) {
    $conn = function_exists('api_get_db_connection') ? api_get_db_connection() : db_connect();
}

$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', trim($_GET['table'] ?? ''));
$activeSccode = isset($_GET['sccode']) && (int)$_GET['sccode'] > 0 ? (int)$_GET['sccode'] : $sccode;
$since = trim($_GET['since'] ?? '');
$session = trim($_GET['session'] ?? '');

// Forbidden internal authentication & volatile token tables
$forbiddenTables = [
    'active_sessions',
    'user_sessions',
    'qrcodelogin',
    'admin_tokens',
    'password_resets',
    'oauth_access_tokens',
    'oauth_auth_codes',
    'oauth_clients',
    'oauth_personal_access_clients',
    'oauth_refresh_tokens',
    'sync_history_log',
    'user_activity_log',
    'user_screen_logs',
    'offline_sync_queue',
    'sync_queue',
    'connection_log'
];

if (empty($tableName) || in_array(strtolower($tableName), $forbiddenTables)) {
    if (function_exists('api_send_response')) {
        api_send_response(422, false, "Invalid, forbidden, or unauthorized table: " . htmlspecialchars($tableName));
    } else {
        api_response('error', "Invalid, forbidden, or unauthorized table: " . htmlspecialchars($tableName), null, 422);
    }
}

// 1. Verify table existence and introspect columns reliably using direct MySQL SHOW COLUMNS
$showColsRes = $conn->query("SHOW COLUMNS FROM `{$tableName}`");
if (!$showColsRes) {
    if (function_exists('api_send_response')) {
        api_send_response(404, false, "Table '{$tableName}' does not exist in the database or cannot be accessed.");
    } else {
        api_response('error', "Table '{$tableName}' does not exist in the database or cannot be accessed.", null, 404);
    }
}

$tableCols = [];
while ($cRow = $showColsRes->fetch_assoc()) {
    if (isset($cRow['Field'])) {
        $tableCols[] = strtolower($cRow['Field']);
    }
}
$showColsRes->free();

$hasSccode = in_array('sccode', $tableCols);
$hasSessionyear = in_array('sessionyear', $tableCols);
$hasModifieddate = in_array('modifieddate', $tableCols);
$hasId = in_array('id', $tableCols);

// Ensure sync_tombstones exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `sync_tombstones` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `sccode` INT NOT NULL,
        `table_name` VARCHAR(64) NOT NULL,
        `record_id` BIGINT NOT NULL,
        `local_id` VARCHAR(128) DEFAULT NULL,
        `deleted_by` VARCHAR(128) DEFAULT NULL,
        `deleted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_sync_tomb` (`sccode`, `table_name`, `deleted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// =========================================================================
// 1. EXPLICIT LIST: Tables where global default fallback (sccode = 0) is allowed
// STRICT: 'slots', 'financesetup', 'financesetupind', 'financesetupvalue' are strictly institution-specific and MUST NEVER have sccode = 0
// =========================================================================
$tablesAllowedGlobalSccode0 = [
    'gpa',
    'subjects',
    'examlist',
    'settings',
    'classschedule',
    'account_head_default',
    'account_sub_head_default',
    'bloodgroup',
    'religions',
    'designations',
    'departments',
    'shifts',
    'rooms',
    'holiday',
    'holidays',
    'modulelist',
    'modules',
    'permissions_role',
    'district',
    'upazila',
    'postoffice'
];

$where = [];
$params = [];
$types = "";

if ($hasSccode) {
    if (in_array(strtolower($tableName), $tablesAllowedGlobalSccode0) || (isset($_GET['include_global']) && ($_GET['include_global'] === '1' || $_GET['include_global'] === 'true' || $_GET['include_global'] === 1))) {
        $where[] = "(sccode = ? OR sccode = 0)";
        $params[] = $activeSccode;
        $types .= "i";
    } else {
        $where[] = "sccode = ?";
        $params[] = $activeSccode;
        $types .= "i";
    }
}

// Session Filtering:
// If session is provided, filter by sessionyear.
// If session is not provided, resolve active session from sessionyear table for this school.
if ($hasSessionyear) {
    if (empty($session) && $activeSccode > 0) {
        $syStmt = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode = ? AND active = 1 ORDER BY syear DESC LIMIT 1");
        if ($syStmt) {
            $syStmt->bind_param("i", $activeSccode);
            $syStmt->execute();
            $syRes = $syStmt->get_result();
            if ($syRow = $syRes->fetch_assoc()) {
                $session = trim($syRow['syear'] ?? '');
            }
            $syStmt->close();
        }
        if (empty($session)) {
            $syStmt2 = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode = ? ORDER BY syear DESC LIMIT 1");
            if ($syStmt2) {
                $syStmt2->bind_param("i", $activeSccode);
                $syStmt2->execute();
                $syRes2 = $syStmt2->get_result();
                if ($syRow2 = $syRes2->fetch_assoc()) {
                    $session = trim($syRow2['syear'] ?? '');
                }
                $syStmt2->close();
            }
        }
        if (empty($session)) {
            $scStmt = $conn->prepare("SELECT sessionyear FROM scinfo WHERE sccode = ? LIMIT 1");
            if ($scStmt) {
                $scStmt->bind_param("i", $activeSccode);
                $scStmt->execute();
                $scRes = $scStmt->get_result();
                if ($scRow = $scRes->fetch_assoc()) {
                    $session = trim($scRow['sessionyear'] ?? '');
                }
                $scStmt->close();
            }
        }
    }

    if (!empty($session) && strtolower($session) !== 'all' && strtolower($session) !== '*') {
        if (in_array(strtolower($tableName), $tablesAllowedGlobalSccode0)) {
            $where[] = "(sessionyear = ? OR sessionyear IS NULL OR sessionyear = '' OR sessionyear = '0' OR sccode = 0)";
            $params[] = $session;
            $types .= "s";
        } else {
            $where[] = "sessionyear = ?";
            $params[] = $session;
            $types .= "s";
        }
    }
}

// Incremental sync filter: Only apply if table has modifieddate and since timestamp is supplied
if ($hasModifieddate && !empty($since) && strtotime($since)) {
    $where[] = "(modifieddate >= ? OR modifieddate IS NULL)";
    $params[] = $since;
    $types .= "s";
}

// Category filter for subjects table (e.g. School, Madrasah, College)
if (strtolower($tableName) === 'subjects') {
    $sccategory = trim($_GET['sccategory'] ?? $_GET['category'] ?? '');
    if (empty($sccategory)) {
        $scStmt = $conn->prepare("SELECT sccategory FROM scinfo WHERE sccode = ? LIMIT 1");
        if ($scStmt) {
            $scStmt->bind_param("i", $activeSccode);
            $scStmt->execute();
            $scRes = $scStmt->get_result();
            if ($scRow = $scRes->fetch_assoc()) {
                $sccategory = trim($scRow['sccategory'] ?? '');
            }
            $scStmt->close();
        }
    }
    if (empty($sccategory)) {
        $sccategory = 'School';
    }

    $where[] = "(LOWER(sccategory) = LOWER(?) OR sccategory = '' OR sccategory IS NULL OR sccode = ?)";
    $params[] = $sccategory;
    $params[] = $activeSccode;
    $types .= "si";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$orderByClause = $hasId ? "ORDER BY id ASC" : "ORDER BY 1 ASC";
$sql = "SELECT * FROM `{$tableName}` {$whereClause} {$orderByClause} LIMIT 25000";
error_log($sql);
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

// Fetch deleted tombstones if incremental sync timestamp provided
$deletedIds = [];
if (!empty($since) && strtotime($since)) {
    if (!$hasSccode) {
        $tombStmt = $conn->prepare("SELECT record_id FROM `sync_tombstones` WHERE table_name = ? AND deleted_at >= ? LIMIT 1000");
        if ($tombStmt) {
            $tombStmt->bind_param('ss', $tableName, $since);
            $tombStmt->execute();
            $tombRes = $tombStmt->get_result();
            while ($tRow = $tombRes->fetch_assoc()) {
                $deletedIds[] = (int)$tRow['record_id'];
            }
            $tombStmt->close();
        }
    } else {
        $tombStmt = $conn->prepare("SELECT record_id FROM `sync_tombstones` WHERE (sccode = ? OR sccode = 0) AND table_name = ? AND deleted_at >= ? LIMIT 1000");
        if ($tombStmt) {
            $tombStmt->bind_param('iss', $activeSccode, $tableName, $since);
            $tombStmt->execute();
            $tombRes = $tombStmt->get_result();
            while ($tRow = $tombRes->fetch_assoc()) {
                $deletedIds[] = (int)$tRow['record_id'];
            }
            $tombStmt->close();
        }
    }
}

$responseData = [
    'table' => $tableName,
    'sccode' => $activeSccode,
    'session' => $session,
    'allowed_global_sccode0' => in_array(strtolower($tableName), $tablesAllowedGlobalSccode0),
    'has_sccode' => $hasSccode,
    'has_sessionyear' => $hasSessionyear,
    'count' => count($rows),
    'rows' => $rows,
    'deleted_ids' => $deletedIds,
    'server_time' => date('Y-m-d H:i:s')
];

if (function_exists('api_send_response')) {
    api_send_response(200, true, "Table rows pulled successfully.", $responseData);
} else {
    api_response('success', "Table rows pulled successfully.", $responseData, 200);
}
