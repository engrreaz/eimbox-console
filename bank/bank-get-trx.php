<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: text/html; charset=utf-8');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){
    echo json_encode(['error'=>'invalid id']);
    exit;
}

$stmt = $conn->prepare("SELECT id, accno, date, transtype, chqno, amount, verified FROM banktrans WHERE id=? AND sccode=? LIMIT 1");
$stmt->bind_param("is", $id, $sccode);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(['error'=>'not found']);
    exit;
}
$row = $res->fetch_assoc();
echo json_encode($row);
