<?php
include '../core/config.php'; // DB connection
include '../core/db.php'; // DB connection


$id = $_GET['id'] ?? 0;
$id = (int)$id;

if($id){
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $step = $res->fetch_assoc();

    if($step){
        header('Content-Type: application/json');
        echo json_encode($step);
    } else {
        echo json_encode(['error' => 'Step not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid Step ID']);
}
?>