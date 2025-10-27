<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
session_start();

$rev = isset($_SESSION['reverse']) ? $_SESSION['reverse'] : 0;

include_once 'core/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;

$dbname = $rev ? DB_SYNC : DB_NAME;

$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Connection failed: ' . $conn_sync->connect_error]);
    exit;
}

$results = [];

// Sync Columns
if (!empty($_POST['items'])) {
    $items = json_decode($_POST['items'], true);
    if (!is_array($items)) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'msg' => 'Invalid data']);
        exit;
    }

    foreach ($items as $item) {
        $table = $conn_sync->real_escape_string($item['table']);
        $column = $item['column'];

        preg_match('/^`([^`]+)`/', $column, $m);
        $colName = $m[1] ?? '';

        $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'skipped', 'error' => 'Table not found'];
            continue;
        }

        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
        if ($checkCol && $checkCol->num_rows > 0) {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'skipped', 'error' => "Column '$colName' exists"];
            // drop & recreate
            $conn_sync->query("ALTER TABLE `$table` DROP COLUMN `$colName`");
        }

        if ($conn_sync->query("ALTER TABLE `$table` ADD $column") === TRUE) {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'applied'];
        } else {
            $results[] = ['table' => $table, 'column' => $column, 'status' => 'error', 'error' => $conn_sync->error];
        }
    }

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'results' => $results]);
    exit;
}

// Sync Table
if (!empty($_POST['create']) && !empty($_POST['table'])) {
    $table = $_POST['table'];
    $createSQL = $_POST['create'];

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if ($checkTable && $checkTable->num_rows > 0) {
        $results[] = ['table' => $table, 'status' => 'skipped', 'error' => 'Table exists'];
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

// No valid input
ob_end_clean();
echo json_encode(['status' => 'error', 'msg' => 'No valid input']);
$conn_sync->close();