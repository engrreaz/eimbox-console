<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id = $_POST['id'] ?? 0;
$rollno = $_POST['rollno'] ?? '';

if (!$id || !$rollno) {
    echo json_encode(['status' => 'err', 'msg' => 'Invalid input']);
    exit;
}

$sql = "UPDATE sessioninfo set rollno='$roll' where id='$id' and sccode='$sccode'";

$q = mysqli_query($conn, $sql);

if ($q) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'err', 'msg' => mysqli_error($conn)]);
}