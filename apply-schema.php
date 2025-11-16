<?php
/**
 * ============================================================
 * 🔧 apply-schema.php (Safe, No-Data-Loss Version)
 * - Uses MySQLi (not PDO)
 * - HTML responses (no JSON)
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

// Reverse mode (keeps your original behavior)
$rev = $_SESSION['reverse'] ?? 0;

// DB connection
require_once 'core/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
$dbname = ($rev == 0) ? (defined('DB_SYNC') ? DB_SYNC : DB_NAME) : DB_NAME;

echo $dbname;

$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo '<div class="alert alert-danger">❌ Connection failed: ' . htmlspecialchars($conn_sync->connect_error) . '</div>';
    exit;
}

/**
 * Helpers
 */
function h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$data = $_POST['data'] ?? '';
if (!$data) {
    echo '<div class="alert alert-danger">❌ No data received</div>';
    $conn_sync->close();
    exit;
}

$data = json_decode($data, true);
if (!$data || !isset($data['action'])) {
    echo '<div class="alert alert-danger">❌ Invalid JSON data</div>';
    $conn_sync->close();
    exit;
}

$action = $data['action'];

/* ------------------------------------------------------------------
   Safe behavior rules:
   - NEVER DROP existing column automatically.
   - Before MODIFY (or any potentially destructive change) create a
     backup column with suffix _bak_YYYYmmdd_His and copy data there.
   - Use ALTER TABLE ... MODIFY ... to change definition (keeps data).
   - If column doesn't exist -> ADD.
   - Provide clear HTML messages for each step.
   ------------------------------------------------------------------ */

/* ------------------------- APPLY TABLE --------------------------- */
if ($action === 'apply-table') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $createSQL = html_entity_decode(trim($data['sql'] ?? ''), ENT_QUOTES);

    if (empty($table)) {
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

/* ------------------------ APPLY SINGLE COLUMN -------------------- */
if ($action === 'apply-column') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $columnDef = html_entity_decode(trim($data['column'] ?? ''), ENT_QUOTES);

    echo $table . '/' . $columnDef;

    if (!$table || !$columnDef) {
        echo '<div class="alert alert-danger">❌ Missing table or column</div>';
        $conn_sync->close();
        exit;
    }

    // extract column name
    preg_match('/^`([^`]+)`/', $columnDef, $m);
    $colName = $m[1] ?? '';
    if (!$colName) {
        echo '<div class="alert alert-danger">❌ Invalid column definition</div>';
        $conn_sync->close();
        exit;
    }

    // table exists?
    $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
    if (!$checkTable || $checkTable->num_rows === 0) {
        echo "<div class='alert alert-danger'>❌ Table '" . h($table) . "' not found</div>";
        $conn_sync->close();
        exit;
    }

    // column exists?
    $checkCol = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($colName) . "'");
    $colExists = ($checkCol && $checkCol->num_rows > 0);

    if ($colExists) {
        // fetch current column metadata
        $current = $checkCol->fetch_assoc();
        $currentType = $current['Type'];      // e.g. varchar(50)
        $currentNull = $current['Null'];      // YES/NO
        $currentDefault = $current['Default'];
        $currentExtra = $current['Extra'];    // e.g. auto_increment

        // create safe backup column name
        $ts = date('Ymd_His');
        $bakCol = $colName . "_bak_" . $ts;
        // ensure backup name doesn't conflict
        $bakCheck = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($bakCol) . "'");
        if ($bakCheck && $bakCheck->num_rows > 0) {
            // extremely unlikely; append random
            $bakCol .= '_' . substr(md5(rand()), 0, 6);
        }

        // Build a minimal backup column definition using current Type and nullability
        $bakDefParts = [];
        $bakDefParts[] = $currentType;
        if ($currentNull === 'NO')
            $bakDefParts[] = 'NOT NULL';
        else
            $bakDefParts[] = 'NULL';
        if ($currentDefault !== null)
            $bakDefParts[] = "DEFAULT " . $conn_sync->real_escape_string($currentDefault);
        $bakDef = implode(' ', $bakDefParts);

        // Add backup column
        $addBakSql = "ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}";
        if (!$conn_sync->query($addBakSql)) {
            echo "<div class='alert alert-danger'>❌ Failed to create backup column '" . h($bakCol) . "': " . h($conn_sync->error) . "</div>";
            $conn_sync->close();
            exit;
        }

        // copy data into backup
        $copySql = "UPDATE `{$table}` SET `{$bakCol}` = `{$colName}`";
        if (!$conn_sync->query($copySql)) {
            echo "<div class='alert alert-danger'>❌ Failed to copy data to backup column '" . h($bakCol) . "': " . h($conn_sync->error) . "</div>";
            // we don't drop backup automatically; manual cleanup by user
            $conn_sync->close();
            exit;
        }

        // Now attempt safe MODIFY (keeps column name and data as much as possible)
        $modifySQL = "ALTER TABLE `{$table}` MODIFY {$columnDef}";

        if ($conn_sync->query($modifySQL)) {
            echo "<div class='alert alert-success'>🔄 Column '" . h($table) . "." . h($colName) . "' modified successfully. Backup: `" . h($bakCol) . "`</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error modifying '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
            echo "<div class='alert alert-warning'>⚠️ The old data is preserved in backup column: <strong>" . h($bakCol) . "</strong></div>";
        }

    } else {
        // column does not exist: ADD (safe)
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

/* ---------------------- APPLY MULTIPLE SELECTED ------------------ */
if ($action === 'apply-selected') {
    if (ob_get_level())
        ob_end_clean();

    $items = $data['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        echo '<div class="alert alert-danger">❌ No columns selected</div>';
        $conn_sync->close();
        exit;
    }

    foreach ($items as $item) {
        $table = $conn_sync->real_escape_string($item['table'] ?? '');
        $columnDef = html_entity_decode(trim($item['column'] ?? ''), ENT_QUOTES);

        if (!$table || !$columnDef) {
            echo "<div class='alert alert-danger'>❌ Missing table or column definition in one item. Skipped.</div>";
            continue;
        }

        // extract column name
        preg_match('/^`([^`]+)`/', $columnDef, $m);
        $colName = $m[1] ?? '';
        if (!$colName) {
            echo "<div class='alert alert-danger'>❌ Invalid column definition: " . h($columnDef) . " (skipped)</div>";
            continue;
        }

        // table exists?
        $checkTable = $conn_sync->query("SHOW TABLES LIKE '{$table}'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            echo "<div class='alert alert-warning'>⚠️ Table '" . h($table) . "' not found. Skipped '" . h($colName) . "'.</div>";
            continue;
        }

        // column exists?
        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($colName) . "'");
        $colExists = ($checkCol && $checkCol->num_rows > 0);

        if ($colExists) {
            // fetch existing metadata
            $current = $checkCol->fetch_assoc();
            $currentType = $current['Type'];   // e.g. varchar(50)
            $newDefClean = strtoupper($columnDef);

            // If the new definition contains the current type string, assume likely compatible -> skip
            if (strpos($newDefClean, strtoupper($currentType)) !== false) {
                echo "<div class='alert alert-info'>ℹ️ No change needed for '" . h($table) . "." . h($colName) . "'</div>";
                continue;
            }

            // create backup column
            $ts = date('Ymd_His');
            $bakCol = $colName . "_bak_" . $ts;
            $bakCheck = $conn_sync->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $conn_sync->real_escape_string($bakCol) . "'");
            if ($bakCheck && $bakCheck->num_rows > 0) {
                $bakCol .= '_' . substr(md5(rand()), 0, 6);
            }

            // build backup definition using current type/null/default
            $currentNull = $current['Null'];
            $currentDefault = $current['Default'];

            $bakDefParts = [$currentType];
            if ($currentNull === 'NO')
                $bakDefParts[] = 'NOT NULL';
            else
                $bakDefParts[] = 'NULL';
            if ($currentDefault !== null)
                $bakDefParts[] = "DEFAULT " . $conn_sync->real_escape_string($currentDefault);
            $bakDef = implode(' ', $bakDefParts);

            $addBakSql = "ALTER TABLE `{$table}` ADD COLUMN `{$bakCol}` {$bakDef}";
            if (!$conn_sync->query($addBakSql)) {
                echo "<div class='alert alert-danger'>❌ Failed to create backup column '" . h($bakCol) . "' for '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
                continue;
            }

            // copy data
            $copySql = "UPDATE `{$table}` SET `{$bakCol}` = `{$colName}`";
            if (!$conn_sync->query($copySql)) {
                echo "<div class='alert alert-danger'>❌ Failed to copy data to backup column '" . h($bakCol) . "': " . h($conn_sync->error) . "</div>";
                // do not attempt modify if backup copy failed
                continue;
            }

            // attempt MODIFY
            $modifySQL = "ALTER TABLE `{$table}` MODIFY {$columnDef}";
            if ($conn_sync->query($modifySQL)) {
                echo "<div class='alert alert-success'>🔄 Updated '" . h($table) . "." . h($colName) . "' (MODIFY). Backup: <strong>" . h($bakCol) . "</strong></div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error updating '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
                echo "<div class='alert alert-warning'>⚠️ Old data preserved in backup column: <strong>" . h($bakCol) . "</strong></div>";
            }

        } else {
            // column not exists -> safe ADD
            $addSQL = "ALTER TABLE `{$table}` ADD {$columnDef}";
            if ($conn_sync->query($addSQL)) {
                echo "<div class='alert alert-success'>🆕 Added '" . h($table) . "." . h($colName) . "'</div>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error adding '" . h($table) . "." . h($colName) . "': " . h($conn_sync->error) . "</div>";
            }
        }
    }

    $conn_sync->close();
    exit;
}

/* ----------------------- INVALID ACTION ------------------------ */
ob_end_clean();
echo '<div class="alert alert-danger">❌ Invalid action</div>';
$conn_sync->close();
exit;
