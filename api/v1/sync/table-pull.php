<?php
/**
 * EIMBox REST API - Table Row Pull Synchronization Endpoint
 * Route: GET /api/v1/sync/table-pull.php
 * Params: ?table={table_name}&sccode={sccode}&since={timestamp}&session={sessionyear}
 */

require_once __DIR__ . '/../bootstrap.php';
error_log('pull.php*************************************');
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
    'account_head_default', 'account_sub_head_default'
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

// Tables without sccode column (Global Master Tables)
$tablesWithoutSccode = ['notice_category', 'ben_address', 'permissions_role', 'account_head_default'];

// Tables with global sccode=0 fallback
$supportsGlobal = in_array($tableName, ['gpa', 'subjects', 'examlist', 'slots', 'settings', 'classschedule', 'account_head', 'account_sub_head', 'account_sub_head_default']);

$where = [];
$params = [];
$types = "";

if (in_array($tableName, $tablesWithoutSccode)) {
    // Global master table without sccode column -> no sccode filter needed
} elseif ($supportsGlobal) {
    $where[] = "(sccode = ? OR sccode = 0)";
    $params[] = $activeSccode;
    $types .= "i";
} else {
    $where[] = "sccode = ?";
    $params[] = $activeSccode;
    $types .= "i";
}

$sessionAwareTables = [
    'examroutine', 'examlist', 'areas', 'sessioninfo', 'subsetup', 
    'stmark', 'stattnd', 'stfinance', 'stpr', 'classschedule', 
    'clsroutine', 'syllabus', 'lesson_tracking', 'cashbook'
];

if (!empty($session) && in_array($tableName, $sessionAwareTables)) {
    $where[] = "(sessionyear = ? OR sessionyear IS NULL OR sessionyear = '')";
    $params[] = $session;
    $types .= "s";
}

if (!empty($since) && strtotime($since)) {
    $where[] = "(modifieddate >= ? OR modifieddate IS NULL)";
    $params[] = $since;
    $types .= "s";
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
