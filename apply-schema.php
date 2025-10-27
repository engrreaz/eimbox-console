<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
session_start();

$rev = isset($_SESSION['reverse']) ? (int) $_SESSION['reverse'] : 0;

include_once 'core/config.php';

// DB credentials
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
$dbname = ($rev === 0) ? DB_NAME : DB_SYNC;

$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'msg' => '❌ Connection failed: ' . $conn_sync->connect_error]);
    exit;
}

$results = [];

// ---------------------------
// ✅ Column Sync
// ---------------------------
if (!empty($_POST['items'])) {
    $items = json_decode($_POST['items'], true);
    if (!is_array($items)) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'msg' => '❌ Invalid data format']);
        exit;
    }

    foreach ($items as $item) {
        if (!isset($item['table'], $item['column']))
            continue;

        $table = $conn_sync->real_escape_string($item['table']);
        $column = trim($item['column']);

        // ✅ Extract column name safely
        if (preg_match('/^`?([a-zA-Z0-9_]+)`?/', $column, $m)) {
            $colName = $conn_sync->real_escape_string($m[1]);
        } else {
            $colName = '';
        }

        if (!$colName) {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'skipped', 'error' => 'Invalid column name'];
            continue;
        }

        // ✅ Check table exists
        $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'skipped', 'error' => 'Table not found'];
            continue;
        }

        // ✅ Drop column if exists
        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
        if ($checkCol && $checkCol->num_rows > 0) {
            $sqlDrop = "ALTER TABLE `$table` DROP COLUMN `$colName`";
            if ($conn_sync->query($sqlDrop) === TRUE) {
                $results[] = ['table' => $table, 'column' => $colName, 'status' => 'dropped'];
            } else {
                $results[] = ['table' => $table, 'column' => $colName, 'status' => 'error', 'error' => $conn_sync->error];
                continue;
            }
        }

        // ✅ Add column
        $sqlAdd = "ALTER TABLE `$table` ADD $column";
        if ($conn_sync->query($sqlAdd) === TRUE) {
            $results[] = ['table' => $table, 'column' => $colName, 'status' => 'applied'];
        } else {
            $results[] = ['table' => $table, 'column' => $colName, 'status' => 'error', 'error' => $conn_sync->error];
        }
    }

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'results' => $results]);
    exit;
}

// ---------------------------
// ✅ Table Sync
// ---------------------------
if (!empty($_POST['create']) && !empty($_POST['table'])) {
    $table = $conn_sync->real_escape_string($_POST['table']);
    $createSQL = trim($_POST['create']);

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if ($checkTable && $checkTable->num_rows > 0) {
        $results[] = ['table' => $table, 'status' => 'skipped', 'error' => 'Table already exists'];
    } else {
        if ($conn_sync->query($createSQL) === TRUE) {
            $results[] = ['table' => $table, 'status' => 'table_created'];
        } else {
            $results[] = ['table' => $table, 'status' => 'error', 'error' => $conn_sync->error];
        }
    }

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'results' => $results]);
    exit;
}

// ---------------------------
// ✅ No valid input
// ---------------------------
ob_end_clean();
echo json_encode(['status' => 'error', 'msg' => '❌ No valid input received']);
$conn_sync->close();