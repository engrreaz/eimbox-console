<?php
/**
 * EIMBox Database Trigger Provisioning Script
 * Route: GET/POST /api/v1/sync/setup-sync-triggers.php
 * Automatically creates:
 *   1. sync_changed_tables table
 *   2. sync_tombstones table
 *   3. AFTER INSERT, AFTER UPDATE, AFTER DELETE MySQL triggers on all syncable tables
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate: allow CLI execution or Bearer Token via HTTP
if (php_sapi_name() !== 'cli') {
    $user = authenticate_token($conn);
} else {
    $user = ['sccode' => 0, 'profilename' => 'CLI Migration Runner'];
}

// 1. Create Required System Sync Tables
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

$conn->query("
    CREATE TABLE IF NOT EXISTS `sync_tombstones` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `sccode` INT NOT NULL,
        `table_name` VARCHAR(64) NOT NULL,
        `record_id` BIGINT NOT NULL,
        `local_id` VARCHAR(128) DEFAULT NULL,
        `deleted_by` VARCHAR(128) DEFAULT 'DB Trigger',
        `deleted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_sync_tomb` (`sccode`, `table_name`, `deleted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// List of syncable tables
$tables = [
    'areas', 'students', 'sessioninfo', 'teacher', 'stmark', 'stattnd', 
    'stfinance', 'stpr', 'subjects', 'subsetup', 'examlist', 'gpa', 
    'sessionyear', 'settings', 'scinfo', 'ben_address', 'tickets', 
    'ticket_messages', 'events', 'notice', 'notice_category', 'classschedule', 
    'clsroutine', 'syllabus', 'lesson_tracking', 'usersapp', 'permissions_role'
];

$results = [];

foreach ($tables as $tbl) {
    // Check if table exists in DB
    $chk = $conn->query("SHOW TABLES LIKE '{$tbl}'");
    if (!$chk || $chk->num_rows === 0) {
        $results[] = ['table' => $tbl, 'status' => 'skipped', 'message' => 'Table does not exist in MySQL'];
        continue;
    }

    // Inspect columns of the table to detect sccode and id presence
    $colRes = $conn->query("SHOW COLUMNS FROM `{$tbl}`");
    $hasSccode = false;
    $hasId = false;
    while ($col = $colRes->fetch_assoc()) {
        if (strtolower($col['Field']) === 'sccode') $hasSccode = true;
        if (strtolower($col['Field']) === 'id') $hasId = true;
    }

    $sccodeInsertExpr = $hasSccode ? "COALESCE(NEW.sccode, 0)" : "0";
    $sccodeDeleteExpr = $hasSccode ? "COALESCE(OLD.sccode, 0)" : "0";
    $idDeleteExpr = $hasId ? "OLD.id" : "0";

    $trgInsert = "trg_{$tbl}_sync_ai";
    $trgUpdate = "trg_{$tbl}_sync_au";
    $trgDelete = "trg_{$tbl}_sync_ad";

    // Drop existing triggers if any
    $conn->query("DROP TRIGGER IF EXISTS `{$trgInsert}`");
    $conn->query("DROP TRIGGER IF EXISTS `{$trgUpdate}`");
    $conn->query("DROP TRIGGER IF EXISTS `{$trgDelete}`");

    $success = true;

    // 1. Trigger: AFTER INSERT
    $sqlAi = "
        CREATE TRIGGER `{$trgInsert}` AFTER INSERT ON `{$tbl}`
        FOR EACH ROW
        BEGIN
            INSERT INTO `sync_changed_tables` (`sccode`, `table_name`, `last_changed_at`, `change_type`, `change_count`)
            VALUES ({$sccodeInsertExpr}, '{$tbl}', NOW(), 'insert', 1)
            ON DUPLICATE KEY UPDATE `last_changed_at` = NOW(), `change_type` = 'insert', `change_count` = `change_count` + 1;
        END;
    ";

    // 2. Trigger: AFTER UPDATE
    $sqlAu = "
        CREATE TRIGGER `{$trgUpdate}` AFTER UPDATE ON `{$tbl}`
        FOR EACH ROW
        BEGIN
            INSERT INTO `sync_changed_tables` (`sccode`, `table_name`, `last_changed_at`, `change_type`, `change_count`)
            VALUES ({$sccodeInsertExpr}, '{$tbl}', NOW(), 'update', 1)
            ON DUPLICATE KEY UPDATE `last_changed_at` = NOW(), `change_type` = 'update', `change_count` = `change_count` + 1;
        END;
    ";

    // 3. Trigger: AFTER DELETE (records to both sync_changed_tables and sync_tombstones)
    $sqlAd = "
        CREATE TRIGGER `{$trgDelete}` AFTER DELETE ON `{$tbl}`
        FOR EACH ROW
        BEGIN
            INSERT INTO `sync_changed_tables` (`sccode`, `table_name`, `last_changed_at`, `change_type`, `change_count`)
            VALUES ({$sccodeDeleteExpr}, '{$tbl}', NOW(), 'delete', 1)
            ON DUPLICATE KEY UPDATE `last_changed_at` = NOW(), `change_type` = 'delete', `change_count` = `change_count` + 1;

            INSERT INTO `sync_tombstones` (`sccode`, `table_name`, `record_id`, `deleted_by`, `deleted_at`)
            VALUES ({$sccodeDeleteExpr}, '{$tbl}', {$idDeleteExpr}, 'DB Trigger', NOW());
        END;
    ";

    if (!$conn->query($sqlAi)) $success = false;
    if (!$conn->query($sqlAu)) $success = false;
    if (!$conn->query($sqlAd)) $success = false;

    $results[] = [
        'table' => $tbl,
        'has_sccode' => $hasSccode,
        'has_id' => $hasId,
        'triggers' => [$trgInsert, $trgUpdate, $trgDelete],
        'status' => $success ? 'active' : 'error',
        'error' => $success ? null : $conn->error
    ];
}

api_response('success', 'Sync triggers provisioned successfully across database tables.', [
    'total_tables' => count($tables),
    'results' => $results
]);
