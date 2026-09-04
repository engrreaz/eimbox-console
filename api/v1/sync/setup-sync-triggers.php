<?php
/**
 * EIMBox Database Trigger Provisioning Script
 * Route: GET/POST /api/v1/sync/setup-sync-triggers.php
 * 
 * Safe, Idempotent Trigger Setup (No Bearer Token required):
 *   1. Creates sync_changed_tables & sync_tombstones tables
 *   2. Drops existing triggers (DROP TRIGGER IF EXISTS) to prevent duplicate errors
 *   3. Recreates AFTER INSERT, AFTER UPDATE, AFTER DELETE MySQL triggers for all tables
 */

require_once __DIR__ . '/../bootstrap.php';

// Allow execution directly from browser, CLI, or admin panel without Bearer Token
$caller = php_sapi_name() === 'cli' ? 'CLI Migration Runner' : 'Web Browser/Admin';

// 1. Create Required System Sync Tables (Idempotent: IF NOT EXISTS)
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

// System tables to exclude from triggers
$excludedSystemTables = [
    'sync_changed_tables', 'sync_tombstones', 'sync_history_log',
    'offline_sync_queue', 'sync_delete_queue', 'sync_dirty_tables'
];

// Discover all tables in the current MySQL database dynamically
$allDbTables = [];
$dbTablesRes = $conn->query("SHOW TABLES");
if ($dbTablesRes) {
    while ($tRow = $dbTablesRes->fetch_row()) {
        $tblName = $tRow[0];
        if (!in_array($tblName, $excludedSystemTables)) {
            $allDbTables[] = $tblName;
        }
    }
}

// Fallback list of known core syncable tables
$whitelist = [
    'areas', 'students', 'sessioninfo', 'teacher', 'stmark', 'stattnd', 
    'stfinance', 'stpr', 'subjects', 'subsetup', 'examlist', 'gpa', 
    'sessionyear', 'settings', 'scinfo', 'ben_address', 'tickets', 
    'ticket_messages', 'events', 'notice', 'notice_category', 'classschedule', 
    'clsroutine', 'syllabus', 'lesson_tracking', 'usersapp', 'permissions_role',
    'user_custom_permissions', 'slots', 'examroutine',
    'account_head', 'account_sub_head', 'bankinfo', 'banktrans', 'cashbook'
];

// Target tables are all discovered tables matching whitelist or present in database
$tablesToProcess = array_unique(array_merge($allDbTables, $whitelist));

$results = [];
$totalTriggersCreated = 0;

foreach ($tablesToProcess as $tbl) {
    if (in_array($tbl, $excludedSystemTables)) continue;

    // Check if table exists in DB
    $chk = $conn->query("SHOW TABLES LIKE '{$tbl}'");
    if (!$chk || $chk->num_rows === 0) {
        $results[] = ['table' => $tbl, 'status' => 'skipped', 'message' => 'Table does not exist in database'];
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

    // 100% IDEMPOTENT: Drop existing triggers first (prevents duplicate trigger errors)
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

    if (!$conn->query($sqlAi)) $success = false; else $totalTriggersCreated++;
    if (!$conn->query($sqlAu)) $success = false; else $totalTriggersCreated++;
    if (!$conn->query($sqlAd)) $success = false; else $totalTriggersCreated++;

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
    'executed_by' => $caller,
    'total_tables_processed' => count($results),
    'total_triggers_active' => $totalTriggersCreated,
    'idempotent' => true,
    'note' => 'Safe to re-run multiple times without duplicate errors.',
    'results' => $results
]);
