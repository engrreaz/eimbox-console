<?php
/**
 * ============================================================
 * 🔧 apply-schema.php
 * Safe DB Schema Apply Script
 * Compatible with: PHP + MySQLi (not PDO)
 * ============================================================
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

// ✅ শুধুমাত্র POST মেথড অনুমোদিত
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'msg' => 'Only POST method allowed']);
    exit;
}

// ✅ Reverse মোড (যদি sync/compare পরিবর্তিত হয়)
$rev = isset($_SESSION['reverse']) ? $_SESSION['reverse'] : 0;

// ✅ ডাটাবেজ কানেকশন সেটআপ
require_once 'core/config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$port = 3306;
if (isset($_SESSION['reverse']) && $_SESSION['reverse'] == 0) {
    $dbname = defined('DB_SYNC') ? DB_SYNC : DB_NAME;
} else {
    $dbname = DB_NAME;
}


$conn_sync = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn_sync->connect_error) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'msg' => 'Connection failed: ' . $conn_sync->connect_error]);
    exit;
}
// $hh = $host . $user . '/' . $pass . '/' . $dbname;
// echo json_encode(['status' => 'error', 'msg' => $hh]);
// exit;

// ✅ ইনপুট ডাটা পার্স
$data = $_POST['data'] ?? '';
if (!$data) {
    echo json_encode(['status' => 'error', 'msg' => 'No data received']);
    exit;
}

$data = json_decode($data, true);
if (!$data || !isset($data['action'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid JSON data']);
    exit;
}

$action = $data['action'];
$results = [];

/* ============================================================
 🔹 1️⃣ APPLY NEW TABLE (Upgraded & Safe Version)
============================================================ */
if ($action === 'apply-table') {
    $table = $conn_sync->real_escape_string($data['table'] ?? '');
    $createSQL = html_entity_decode(trim($data['sql'] ?? ''), ENT_QUOTES);

    // 🧩 Debug লগ (চাইলে আনকমেন্ট করো)
    // file_put_contents('debug_create_sql.log', $createSQL);

    // ✅ Validation
    if (empty($table)) {
        echo json_encode(['status' => 'error', 'msg' => 'Table name missing']);
        exit;
    }

    if (stripos($createSQL, 'CREATE TABLE') !== 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid table SQL syntax']);
        exit;
    }

    // ✅ Check if table already exists
    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if ($checkTable && $checkTable->num_rows > 0) {
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'msg' => "Table '$table' already exists."
        ]);
        $conn_sync->close();
        exit;
    }

    // ✅ Create table
    if ($conn_sync->query($createSQL)) {
        ob_end_clean();
        echo json_encode([
            'status' => 'ok',
            'msg' => "✅ Table '$table' created successfully",
            'results' => [['table' => $table, 'status' => 'table_created']]
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'msg' => 'MySQL Error: ' . $conn_sync->error,
            'sql' => $createSQL
        ]);
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

    if (!$table || !$columnDef) {
        echo json_encode(['status' => 'error', 'msg' => 'Missing table or column']);
        exit;
    }

    preg_match('/^`([^`]+)`/', $columnDef, $m);
    $colName = $m[1] ?? '';

    if (!$colName) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid column definition']);
        exit;
    }

    $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
    if (!$checkTable || $checkTable->num_rows === 0) {
        echo json_encode(['status' => 'error', 'msg' => "Table '$table' not found"]);
        exit;
    }

    $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
    if ($checkCol && $checkCol->num_rows > 0) {
        $conn_sync->query("ALTER TABLE `$table` DROP COLUMN `$colName`");
    }

    if ($conn_sync->query("ALTER TABLE `$table` ADD $columnDef")) {
        ob_end_clean();
        echo json_encode([
            'status' => 'ok',
            'msg' => "✅ Column '$colName' applied to '$table'",
            'results' => [['table' => $table, 'column' => $colName, 'status' => 'applied']]
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'msg' => 'MySQL Error: ' . $conn_sync->error
        ]);
    }

    $conn_sync->close();
    exit;
}

/* ============================================================
 🔹 3️⃣ APPLY MULTIPLE SELECTED COLUMNS
============================================================ */
if ($action === 'apply-selected') {
    $items = $data['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'No columns selected']);
        exit;
    }

    foreach ($items as $item) {
        $table = $conn_sync->real_escape_string($item['table']);
        $columnDef = html_entity_decode(trim($item['column']), ENT_QUOTES);

        preg_match('/^`([^`]+)`/', $columnDef, $m);
        $colName = $m[1] ?? '';

        $checkTable = $conn_sync->query("SHOW TABLES LIKE '$table'");
        if (!$checkTable || $checkTable->num_rows === 0) {
            $results[] = ['table' => $table, 'column' => $colName, 'status' => 'skipped', 'error' => 'Table not found'];
            continue;
        }

        $checkCol = $conn_sync->query("SHOW COLUMNS FROM `$table` LIKE '$colName'");
        if ($checkCol && $checkCol->num_rows > 0) {
            $conn_sync->query("ALTER TABLE `$table` DROP COLUMN `$colName`");
        }

        if ($conn_sync->query("ALTER TABLE `$table` ADD $columnDef")) {
            $results[] = ['table' => $table, 'column' => $colName, 'status' => 'applied'];
        } else {
            $results[] = ['table' => $table, 'column' => $colName, 'status' => 'error', 'error' => $conn_sync->error];
        }
    }

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'results' => $results]);
    $conn_sync->close();
    exit;
}

/* ============================================================
 ❌ INVALID REQUEST
============================================================ */
ob_end_clean();
echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
$conn_sync->close();
exit;