<?php
/**
 * ============================================================
 * 🔧 apply-schema.php (Safe, No-Data-Loss Version)
 * - Uses MySQLi (not PDO)
 * - HTML responses only (no JSON)
 * - 406-free, Base64 safe
 * - Creates backups before risky changes (no DROP COLUMN)
 * ============================================================
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger">❌ Only POST method allowed</div>';
    exit;
}

// DB connection
require_once 'core/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
$dbname = (isset($_SESSION['reverse']) && $_SESSION['reverse'] == 1) ? DB_NAME : (defined('DB_SYNC') ? DB_SYNC : DB_NAME);
echo $dbname;
$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo '<div class="alert alert-danger">❌ Connection failed: ' . htmlspecialchars($conn_sync->connect_error) . '</div>';
    exit;
}

// HTML escape helper
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// --------------------------- POST DATA -----------------------
$data = $_POST['data'] ?? '';
if (!$data) {
    echo '<div class="alert alert-danger">❌ No data received</div>';
    $conn_sync->close();
    exit;
}

// Base64 decode
$decoded = base64_decode($data, true);
if ($decoded === false) {
    echo '<div class="alert alert-danger">❌ Invalid Base64 data</div>';
    $conn_sync->close();
    exit;
}

// JSON decode
$dataArr = json_decode($decoded, true);
if (!$dataArr || !isset($dataArr['action'])) {
    echo '<div class="alert alert-danger">❌ Invalid JSON data</div>';
    $conn_sync->close();
    exit;
}

$action = $dataArr['action'];

// ------------------------- APPLY TABLE ----------------------
if ($action === 'apply-table') {
    $table = $conn_sync->real_escape_string($dataArr['table'] ?? '');
    $createSQL = html_entity_decode(trim($dataArr['create'] ?? ''), ENT_QUOTES);

    if (!$table) {
        echo '<div class="alert alert-danger">❌ Table name missing</div>';
        $conn_sync->close();
        exit;
    }
    if (stripos($createSQL, 'CREATE TABLE') !== 0) {
        echo '<div class="alert alert-danger">❌ Invalid table SQL syntax</div>';
        $conn_sync->close();
        exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
    if ($checkTable && $checkTable->num_rows > 0) {
        echo "<div class='alert alert-warning'>⚠️ Table '" . h($table) . "' already exists</div>";
        $conn_sync->close();
        exit;
    }

    if ($conn_sync->query($createSQL)) {
        echo "<div class='alert alert-success'>✅ Table '" . h($table) . "' created successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ MySQL Error: " . h($conn_sync->error) . "</div>";
    }

    $conn_sync->close();
    exit;
}

// ---------------------- APPLY SINGLE COLUMN -----------------
if ($action === 'apply-column') {
    $table = $conn_sync->real_escape_string($dataArr['table'] ?? '');
    $columnDef = html_entity_decode(trim($dataArr['column'] ?? ''), ENT_QUOTES);

    if (!$table || !$columnDef) {
        echo '<div class="alert alert-danger">❌ Missing table or column</div>';
        $conn_sync->close();
        exit;
    }

    preg_match('/^`([^`]+)`/', $columnDef, $m);
    $colName = $m[1] ?? '';
    if (!$colName) {
        echo '<div class="alert alert-danger">❌ Invalid column definition</div>';
        $conn_sync->close();
        exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
    if (!$checkTable || $checkTable->num_rows === 0) {
        echo "<div class='alert alert-danger'>❌ Table '" . h($table) . "' not found</div>";
        $conn_sync->close();
        exit;
    }

    $checkCol = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($colName) . "'");
    $colExists = ($checkCol && $checkCol->num_rows > 0);

    if ($colExists) {
        $current = $checkCol->fetch_assoc();
        $currentType = $current['Type'];
        $currentNull = $current['Null'];
        $currentDefault = $current['Default'];

        $ts = date('Ymd_His');
        $bakCol = $colName . "_bak_" . $ts;
        $bakCheck = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($bakCol) . "'");
        if ($bakCheck && $bakCheck->num_rows > 0) $bakCol .= '_' . substr(md5(rand()),0,6);

        $bakDefParts = [$currentType];
        $bakDefParts[] = $currentNull === 'NO' ? 'NOT NULL' : 'NULL';
        if ($currentDefault !== null) $bakDefParts[] = "DEFAULT " . $conn_sync->real_escape_string($currentDefault);
        $bakDef = implode(' ', $bakDefParts);

        $addBakSql = "ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}";
        if (!$conn_sync->query($addBakSql)) {
            echo "<div class='alert alert-danger'>❌ Failed to create backup column '" . h($bakCol) . "': " . h($conn_sync->error) . "</div>";
            $conn_sync->close();
            exit;
        }

        $copySql = "UPDATE `{$table}` SET `{$bakCol}` = `{$colName}`";
        if (!$conn_sync->query($copySql)) {
            echo "<div class='alert alert-danger'>❌ Failed to copy data to backup column '" . h($bakCol) . "'</div>";
            $conn_sync->close();
            exit;
        }

        $modifySQL = "ALTER TABLE `{$table}` MODIFY {$columnDef}";
        if ($conn_sync->query($modifySQL)) {
            echo "<div class='alert alert-success'>🔄 Column '" . h($table) . "." . h($colName) . "' modified successfully. Backup: `" . h($bakCol) . "`</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error modifying '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
            echo "<div class='alert alert-warning'>⚠️ Old data preserved in backup: <strong>" . h($bakCol) . "</strong></div>";
        }

    } else {
        $addSQL = "ALTER TABLE `{$table}` ADD {$columnDef}";
        if ($conn_sync->query($addSQL)) {
            echo "<div class='alert alert-success'>🆕 Column '" . h($colName) . "' added to '" . h($table) . "'</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error adding '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
        }
    }

    $conn_sync->close();
    exit;
}

// -------------------- APPLY MULTIPLE SELECTED -----------------
if ($action === 'apply-selected') {
    $items = $dataArr['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        echo '<div class="alert alert-danger">❌ No columns selected</div>';
        $conn_sync->close();
        exit;
    }

    foreach ($items as $item) {
        $table = $conn_sync->real_escape_string($item['table'] ?? '');
        $columnDef = html_entity_decode(trim($item['column'] ?? ''), ENT_QUOTES);
        if (!$table || !$columnDef) {
            echo "<div class='alert alert-danger'>❌ Missing table/column definition. Skipped.</div>";
            continue;
        }

        preg_match('/^`([^`]+)`/', $columnDef, $m);
        $colName = $m[1] ?? '';
        if (!$colName) {
            echo "<div class='alert alert-danger'>❌ Invalid column definition: " . h($columnDef) . " (skipped)</div>";
            continue;
        }

        $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            echo "<div class='alert alert-warning'>⚠️ Table '" . h($table) . "' not found. Skipped '" . h($colName) . "'.</div>";
            continue;
        }

        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($colName) . "'");
        $colExists = ($checkCol && $checkCol->num_rows > 0);

        if ($colExists) {
            $current = $checkCol->fetch_assoc();
            $currentType = $current['Type'];
            $currentNull = $current['Null'];
            $currentDefault = $current['Default'];

            // skip if same type (simple check)
            if (stripos($columnDef, $currentType) !== false) {
                echo "<div class='alert alert-info'>ℹ️ No change needed for '" . h($table) . "." . h($colName) . "'</div>";
                continue;
            }

            $ts = date('Ymd_His');
            $bakCol = $colName . "_bak_" . $ts;
            $bakCheck = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($bakCol) . "'");
            if ($bakCheck && $bakCheck->num_rows > 0) $bakCol .= '_' . substr(md5(rand()),0,6);

            $bakDefParts = [$currentType];
            $bakDefParts[] = $currentNull === 'NO' ? 'NOT NULL' : 'NULL';
            if ($currentDefault !== null) $bakDefParts[] = "DEFAULT " . $conn_sync->real_escape_string($currentDefault);
            $bakDef = implode(' ', $bakDefParts);

            $addBakSql = "ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}";
            if (!$conn_sync->query($addBakSql)) {
                echo "<div class='alert alert-danger'>❌ Failed to create backup for '" . h($table) . "." . h($colName) . "'</div>";
                continue;
            }

            $copySql = "UPDATE `{$table}` SET `{$bakCol}` = `{$colName}`";
            $conn_sync->query($copySql);

            $modifySQL = "ALTER TABLE `{$table}` MODIFY {$columnDef}";
            if ($conn_sync->query($modifySQL)) {
                echo "<div class='alert alert-success'>🔄 Updated '" . h($table) . "." . h($colName) . "'. Backup: <strong>" . h($bakCol) . "</strong></div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error updating '" . h($table) . "." . h($colName) . "'</div>";
                echo "<div class='alert alert-warning'>⚠️ Old data preserved in backup: <strong>" . h($bakCol) . "</strong></div>";
            }

        } else {
            $addSQL = "ALTER TABLE `{$table}` ADD {$columnDef}";
            if ($conn_sync->query($addSQL)) {
                echo "<div class='alert alert-success'>🆕 Added '" . h($table) . "." . h($colName) . "'</div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error adding '" . h($table) . "." . h($colName) . "'</div>";
            }
        }
    }

    $conn_sync->close();
    exit;
}

// --------------------- INVALID ACTION ------------------------
ob_end_clean();
echo '<div class="alert alert-danger">❌ Invalid action</div>';
$conn_sync->close();
exit;
