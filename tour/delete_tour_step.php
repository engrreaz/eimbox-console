<?php
include '../core/config.php';
include '../core/db.php';


$id = $_POST['id'] ?? 0;
$id = (int)$id;

if($id){
    $stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Step ID']);
}
?>