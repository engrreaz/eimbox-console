<?php
/**
 * EIMBox REST API — Server Change Tracker (Changed Tables Query)
 * Route: GET /api/v1/sync/get-changed-tables.php
 * Query Params:
 *   - sccode: 103187 (required or extracted from JWT)
 *   - since: 2026-08-28 04:00:00 (optional ISO/SQL datetime)
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_response('error', 'Method not allowed. Only GET is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$since = trim($_GET['since'] ?? '');

error_log("changed table function start: " . $since);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 1. Ensure sync_changed_tables table exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `sync_changed_tables` (
        `sccode` INT NOT NULL,
        `table_name` VARCHAR(64) NOT NULL,
        `last_changed_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `change_type` VARCHAR(16) DEFAULT 'mutation',
        `change_count` BIGINT DEFAULT 1,
        PRIMARY KEY (`sccode`, `table_name`),
        INDEX `idx_sync_changed_at` (`sccode`, `last_changed_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 2. Fetch server current timestamp
$timeRes = $conn->query("SELECT NOW() as `server_time`");
$serverTimeRow = $timeRes ? $timeRes->fetch_assoc() : null;
$serverTime = $serverTimeRow['server_time'] ?? date('Y-m-d H:i:s');

// 3. Query changed tables for this sccode since timestamp (excluding volatile logging/audit tables)
$ignoredTables = [
    'sync_history_log', 'user_activity_log', 'user_screen_logs', 'app_preferences',
    'offline_sync_queue', 'sync_queue', 'schema_info', 'local_dirty_tables',
    'local_pending_deletions', 'sync_dirty_tables', 'sync_pending_deletions',
    'sync_tombstones', 'sync_changed_tables', 'connection_log', 'logbook',
    'qrcodelogin', 'active_sessions', 'user_sessions', 'todolist', 'user_actions',
    'track_users', 'trackbook', 'admin_actions', 'otp', 'auth_logs'
];
$placeholders = implode(',', array_fill(0, count($ignoredTables), '?'));

$changedTables = [];

if (empty($since)) {
    // If no since param is passed, return all tracked tables for this sccode
    $sql = "SELECT `table_name`, `last_changed_at`, `change_type`, `change_count` 
            FROM `sync_changed_tables` 
            WHERE (`sccode` = ? OR `sccode` = 0) 
              AND `table_name` NOT IN ($placeholders) 
            ORDER BY `last_changed_at` DESC";

            error_log($sql); 
    $stmt = $conn->prepare($sql);
    $types = 'i' . str_repeat('s', count($ignoredTables));
    $params = array_merge([$sccode], $ignoredTables);
    $stmt->bind_param($types, ...$params);
} else {
    $sql = "SELECT `table_name`, `last_changed_at`, `change_type`, `change_count` 
            FROM `sync_changed_tables` 
            WHERE (`sccode` = ? OR `sccode` = 0) 
              AND `last_changed_at` >= ? 
              AND `table_name` NOT IN ($placeholders) 
            ORDER BY `last_changed_at` DESC";
            error_log($sql); 
    $stmt = $conn->prepare($sql);
    $types = 'is' . str_repeat('s', count($ignoredTables));
    $params = array_merge([$sccode, $since], $ignoredTables);
    $stmt->bind_param($types, ...$params);
}
 
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $changedTables[] = [
            'table' => $row['table_name'],
            'last_changed_at' => $row['last_changed_at'],
            'change_type' => $row['change_type'],
            'change_count' => intval($row['change_count'] ?? 1)
        ];
    }
    $stmt->close();
}

$tableNames = array_values(array_unique(array_column($changedTables, 'table')));

error_log("changed table function start: " . date("Y-m-d H:i:s"));
error_log("Changed tables: " . print_r($changedTables, true));

api_response('success', 'Changed tables fetched successfully.', [
    'sccode' => $sccode,
    'since' => $since ?: null,
    'server_time' => $serverTime,
    'total_changed' => count($tableNames),
    'changed_table_names' => $tableNames,
    'details' => $changedTables
]);
