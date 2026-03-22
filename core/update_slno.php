<?php
require_once 'config.php';
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) exit('Invalid');

$stmt = $conn->prepare("UPDATE modulemanager SET slno=? WHERE id=?");

foreach ($data as $row) {
    $slno = (int)$row['slno'];
    $id   = (int)$row['id'];
    $stmt->bind_param("ii", $slno, $id);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo 'OK';