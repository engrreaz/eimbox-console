<?php
/**
 * ============================================================
 * 🔧 apply-schema.php
 * Safe DB Schema Apply Script (HTML response)
 * Compatible with: PHP + MySQLi (not PDO)
 * ============================================================
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

// ✅ শুধুমাত্র POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger">❌ Only POST method allowed</div>';
    exit;
}

// ✅ Reverse mode
$rev = $_SESSION['reverse'] ?? 0;

// ✅ DB connection
require_once 'core/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
$dbname = ($rev == 0) ? (defined('DB_SYNC') ? DB_SYNC : DB_NAME) : DB_NAME;

$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo '<div class="alert alert-danger">❌ Connection failed: ' . $conn_sync->connect_error . '</div>';
    exit;
}

// ✅ Parse input
$data = $_POST['data'] ?? '';
if (!$data) {
    echo '<div class="alert alert-danger">❌ No data received</div>';
    exit;
}

$data = json_decode($data, true);
if (!$data || !isset($data['action'])) {
    echo '<div class="alert alert-danger">❌ Invalid JSON data</div>';
    exit;
}

$action = $data['action'];
$results = [];

/* ============================================================
 🔹 1️⃣ APPLY TABLE
============================================================ */
if ($action === 'apply-table') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $createSQL = html_entity_decode(trim($data['sql'] ?? ''), ENT_QUOTES);

    echo  $createSQL;
    if (empty($table)) {
        echo '<div class="alert alert-danger">❌ Table name missing</div>';
        exit;
    }
    if (stripos($createSQL, 'CREATE TABLE') !== 0) {
        echo '<div class="alert alert-danger">❌ Invalid table SQL syntax</div>';
        exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if ($checkTable && $checkTable->num_rows > 0) {
        echo "<div class='alert alert-warning'>⚠️ Table '$table' already exists</div>";
        $conn_sync->close();
        exit;
    }

    if ($conn_sync->query($createSQL)) {
        echo "<div class='alert alert-success'>✅ Table '$table' created successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ MySQL Error: " . $conn_sync->error . "</div>";
    }

    $conn_sync->close();
    exit;
}

/* ============================================================
 🔹 2️⃣ APPLY SINGLE COLUMN
============================================================ */
if ($action === 'apply-column') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $columnDef = html_entity_decode(trim($data['column'] ?? ''), ENT_QUOTES);

    echo $columnDef;
    if (!$table || !$columnDef) {
        echo '❌ Missing table or column';
        exit;
    }

    preg_match('/^`([^`]+)`/', $columnDef, $m);
    $colName = $m[1] ?? '';
    if (!$colName) {
        echo '<div class="alert alert-danger">❌ Invalid column definition</div>';
        exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if (!$checkTable || $checkTable->num_rows === 0) {
        echo "<div class='alert alert-danger'>❌ Table '$table' not found</div>";
        exit;
    }

    $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
    if ($checkCol && $checkCol->num_rows > 0) {
        $conn_sync->query("ALTER TABLE `$table` DROP COLUMN `$colName`");
    }

    if ($conn_sync->query("ALTER TABLE `$table` ADD $columnDef")) {
        echo "<div class='alert alert-success'>✅ Column '$colName' applied to '$table'</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ MySQL Error: " . $conn_sync->error . "</div>";
    }

    $conn_sync->close();
    exit;
}

/* ============================================================
 🔹 3️⃣ APPLY MULTIPLE SELECTED COLUMNS
============================================================ */
if ($action === 'apply-selected') {
    if (ob_get_level()) ob_end_clean();

    $items = $data['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        echo '<div class="alert alert-danger">❌ No columns selected</div>';
        exit;
    }

    foreach ($items as $item) {
        $table = $conn_sync->real_escape_string($item['table']);
        $columnDef = html_entity_decode(trim($item['column']), ENT_QUOTES);

        preg_match('/^`([^`]+)`/', $columnDef, $m);
        $colName = $m[1] ?? '';

        $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            echo "<div class='alert alert-warning'>⚠️ Table '$table' not found. Skipped '$colName'</div>";
            continue;
        }

        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
        if ($checkCol && $checkCol->num_rows > 0) {
            $conn_sync->query("ALTER TABLE `$table` DROP COLUMN `$colName`");
        }

        if ($conn_sync->query("ALTER TABLE `$table` ADD $columnDef")) {
            echo "<div class='alert alert-success'>✅ Column '$table.$colName' applied</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error '$table.$colName': " . $conn_sync->error . "</div>";
        }
    }

    $conn_sync->close();
    exit;
}

/* ============================================================
 ❌ INVALID ACTION
============================================================ */
ob_end_clean();
echo '<div class="alert alert-danger">❌ Invalid action</div>';
$conn_sync->close();
exit;


