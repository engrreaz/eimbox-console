<?php
include '../core/config.php'; // db connection
include '../core/db.php'; // db connection
$file = $_GET['file'] ?? '';

$stmt = $conn->prepare("SELECT * FROM tours WHERE page = ? ORDER BY step_no ASC");
$stmt->bind_param("s", $file);
$stmt->execute();
$res = $stmt->get_result();

$steps = [];
while($row = $res->fetch_assoc()){
    $steps[] = $row;
}

header('Content-Type: application/json');
echo json_encode($steps);
?>