<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$push = '';



$data = json_decode($_POST['order'], true);

foreach ($data as $row) {

    $id = intval($row['section_id']);
    $pos = intval($row['position']);

    $stmt = $conn->prepare("UPDATE areas 
     SET idno = ? 
     WHERE  id = ? AND sccode=?");
    $stmt->bind_param("iii", $pos, $id, $sccode);
    $stmt->execute();
}


$stmt->close();

echo json_encode(['status' => 'ok']);
