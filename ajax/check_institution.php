<?php
// DEV: temporary error display for debugging — disable in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// adjust the require path depending on where this file sits
// if this file is at /project/ajax/check_institution.php and core is /project/core/
// then use: require_once __DIR__ . '/../core/config.php';

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';

// check $conn exists
if (!isset($conn) || !$conn) {
    echo json_encode(['status' => 'error', 'msg' => 'Database connection not found ($conn missing). Check core/db.php']);
    exit;
}

$sccode = trim($_POST['sccode'] ?? '');

if (!$sccode) {
    echo json_encode(['status' => 'error', 'msg' => 'No code provided']);
    exit;
}

// Use mysqli real escape (safe enough here for debugging); you can also use prepared statements.
$safe = mysqli_real_escape_string($conn, $sccode);
$sql = "SELECT scname FROM scinfo WHERE sccode = '$safe' LIMIT 1";

$result = $conn->query($sql);
if ($result === false) {
    // query error
    echo json_encode(['status' => 'error', 'msg' => 'DB query error: ' . $conn->error]);
    exit;
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'found', 'scname' => $row['scname']]);
} else {
    echo json_encode(['status' => 'notfound']);
}

$conn->close();
