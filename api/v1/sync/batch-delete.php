<?php
/**
 * EIMBox REST API — Batch Delete & Tombstone Synchronization Endpoint
 * Route: POST /api/v1/sync/batch-delete.php
 * Accepts an array of deletion requests:
 * {
 *   "sccode": 1052,
 *   "deletions": [
 *     { "table": "students", "server_id": 104, "local_id": "uuid-123" },
 *     { "table": "areas", "server_id": 12, "local_id": "uuid-456" }
 *   ]
 * }
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$input = get_api_input();
$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$deletions = $input['deletions'] ?? [];
$deletedBy = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Desktop Client';

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

if (!is_array($deletions) || empty($deletions)) {
    api_response('error', 'Deletions array cannot be empty.', null, 400);
}

// Ensure sync_tombstones table exists in MySQL
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

$allowedTables = [
    'gpa', 'examroutine', 'examlist', 'subjects', 'subsetup', 'areas', 
    'students', 'sessioninfo', 'stmark', 'stattnd', 'stfinance', 'stpr', 
    'slots', 'teacher', 'classschedule', 'clsroutine', 'syllabus', 
    'lesson_tracking', 'sessionyear', 'settings', 'scinfo', 'ben_address',
    'tickets', 'ticket_messages', 'events', 'notice', 'notice_category',
    'usersapp', 'permissions_role', 'user_custom_permissions',
    'account_head', 'account_sub_head', 'bankinfo', 'banktrans', 'cashbook'
];

$results = [];
$deletedCount = 0;
$failedCount = 0;

$tombStmt = $conn->prepare("INSERT INTO `sync_tombstones` (sccode, table_name, record_id, local_id, deleted_by, deleted_at) VALUES (?, ?, ?, ?, ?, NOW())");

foreach ($deletions as $del) {
    $tbl = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower(trim($del['table'] ?? $del['table_name'] ?? '')));
    $serverId = intval($del['server_id'] ?? $del['id'] ?? 0);
    $localId = trim($del['local_id'] ?? '');

    if (empty($tbl) || !in_array($tbl, $allowedTables) || $serverId <= 0) {
        $results[] = [
            'table' => $tbl,
            'server_id' => $serverId,
            'local_id' => $localId,
            'status' => 'skipped',
            'reason' => 'Invalid table name or missing server_id'
        ];
        $failedCount++;
        continue;
    }

    $conn->begin_transaction();
    try {
        // 1. Delete from target table
        $delStmt = $conn->prepare("DELETE FROM `{$tbl}` WHERE `id` = ? AND (`sccode` = ? OR `sccode` = 0)");
        $delStmt->bind_param('ii', $serverId, $sccode);
        $delStmt->execute();
        $affected = $delStmt->affected_rows;
        $delStmt->close();

        // 2. Record in tombstones table
        if ($tombStmt) {
            $tombStmt->bind_param('isis', $sccode, $tbl, $serverId, $localId, $deletedBy);
            $tombStmt->execute();
        }

        $conn->commit();
        $deletedCount++;
        $results[] = [
            'table' => $tbl,
            'server_id' => $serverId,
            'local_id' => $localId,
            'status' => 'deleted',
            'affected' => $affected
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $failedCount++;
        $results[] = [
            'table' => $tbl,
            'server_id' => $serverId,
            'local_id' => $localId,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

if ($tombStmt) {
    $tombStmt->close();
}

api_response('success', "Processed {$deletedCount} deletions successfully.", [
    'sccode' => $sccode,
    'deleted_count' => $deletedCount,
    'failed_count' => $failedCount,
    'results' => $results,
    'server_time' => date('Y-m-d H:i:s')
]);
