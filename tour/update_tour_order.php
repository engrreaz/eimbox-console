<?php
include '../core/config.php';
include '../core/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$order = $data['order'] ?? [];

if($order){
    foreach($order as $step){
        $stmt = $conn->prepare("UPDATE tours SET step_no = ? WHERE id = ?");
        $stmt->bind_param("ii", $step['step_no'], $step['id']);
        $stmt->execute();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>