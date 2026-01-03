<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$package = $_POST['package'] ?? '2';



header('Content-Type: application/json');

if (!$package) {
    echo json_encode(['status' => 'err', 'msg' => 'Invalid package']);
    exit;
}


$q = mysqli_query($conn, "SELECT package_name FROM packages WHERE status='active' and id='$package' ORDER BY id");

while ($row = mysqli_fetch_assoc($q)) {
    $packname = $row['package_name'] ?? 'TRIAL';
}

$u = mysqli_query(
    $conn,
    "UPDATE scinfo 
     SET package_id='$package' ,
     package_name='$packname'
     WHERE sccode='$sccode'"
);

if (!$u) {
    echo json_encode(['status' => 'err', 'msg' => 'Update failed']);
    exit;
}

echo json_encode(['status' => 'ok']);
