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

$allowedTables = [
    'gpa', 'examroutine', 'examlist', 'subjects', 'subsetup', 'areas', 
    'students', 'sessioninfo', 'stmark', 'stattnd', 'stfinance', 'stpr', 
    'slots', 'teacher', 'classschedule', 'clsroutine', 'syllabus', 
    'lesson_tracking', 'sessionyear', 'settings', 'scinfo', 'ben_address',
    'tickets', 'ticket_messages', 'events', 'notice', 'notice_category',
    'usersapp', 'permissions_role', 'user_custom_permissions',
    'account_head', 'account_sub_head', 'bankinfo', 'banktrans', 'cashbook',
    'account_head_default', 'account_sub_head_default',
    'app_releases', 'app_roadmap', 'faq_desktop',
    'tabulatingsheet', 'tabulatingsheetex', 'tabulatingsheetpibi',
    'issues_tracker', 'features', 'modulelist', 'eimbox_features'
];

if (empty($tableName) || !in_array($tableName, $allowedTables)) {
    if (function_exists('api_send_response')) {
        api_send_response(422, false, "Invalid or unauthorized table: " . htmlspecialchars($tableName));
    } else {
        api_response('error', "Invalid or unauthorized table: " . htmlspecialchars($tableName), null, 422);
    }
}

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
// 1. EXPLICIT LIST: Tables without sccode column (Global Master Tables)
// =========================================================================
$tablesWithoutSccode = [
    'notice_category', 
    'ben_address', 
    'permissions_role', 
    'account_head_default', 
    'app_releases', 
    'app_roadmap', 
    'faq_desktop', 
    'issues_tracker', 
    'features', 
    'modulelist', 
    'eimbox_features'
];

// =========================================================================
// 2. EXPLICIT LIST: Tables where global default fallback (sccode = 0) is allowed
// STRICT: 'slots' is strictly institution-specific and MUST NEVER have sccode = 0
// =========================================================================
$tablesAllowedGlobalSccode0 = [
    'gpa',
    'subjects',
    'examlist',
    'settings',
    'classschedule',
    'account_head',
    'account_sub_head',
    'account_sub_head_default'
];

// =========================================================================
// 3. EXPLICIT LIST: Tables strictly scoped to active session (sessionyear)
// =========================================================================
$sessionAwareTables = [
    'examroutine', 'examlist', 'areas', 'sessioninfo', 'subsetup', 
    'stmark', 'stattnd', 'stfinance', 'stpr', 'classschedule', 
    'clsroutine', 'syllabus', 'lesson_tracking', 'cashbook',
    'tabulatingsheet', 'tabulatingsheetex'
];

// Resolve active session from sessionyear table if not passed from client
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
}

$where = [];
$params = [];
$types = "";

if (in_array($tableName, $tablesWithoutSccode)) {
    // Global master table without sccode column -> no sccode filter needed
} elseif (in_array($tableName, $tablesAllowedGlobalSccode0)) {
    // Whitelisted global fallback tables allow sccode = ? OR sccode = 0
    $where[] = "(sccode = ? OR sccode = 0)";
    $params[] = $activeSccode;
    $types .= "i";
} else {
    // Strict multi-tenant rule: all other tables MUST filter by sccode = ?
    $where[] = "sccode = ?";
    $params[] = $activeSccode;
    $types .= "i";
}

// Session Filtering: Only fetch records for the active sessionyear
if (!empty($session) && in_array($tableName, $sessionAwareTables)) {
    if (in_array($tableName, $tablesAllowedGlobalSccode0)) {
        $where[] = "(sessionyear = ? OR sccode = 0 OR sessionyear IS NULL OR sessionyear = '')";
        $params[] = $session;
        $types .= "s";
    } else {
        $where[] = "sessionyear = ?";
        $params[] = $session;
        $types .= "s";
    }
}

if (!empty($since) && strtotime($since)) {
    $where[] = "(modifieddate >= ? OR modifieddate IS NULL)";
    $params[] = $since;
    $types .= "s";
}

// Category filter for subjects table (e.g. School, Madrasah, College)
if ($tableName === 'subjects') {
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
$sql = "SELECT * FROM `{$tableName}` {$whereClause} ORDER BY id ASC LIMIT 25000";
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
    if (in_array($tableName, $tablesWithoutSccode)) {
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
    'allowed_global_sccode0' => in_array($tableName, $tablesAllowedGlobalSccode0),
    'session_filtered' => in_array($tableName, $sessionAwareTables),
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
