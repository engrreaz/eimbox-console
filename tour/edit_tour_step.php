<?php
include '../core/config.php';
include '../core/db.php';

$id = $_POST['id'] ?? 0;
$content = $_POST['content'] ?? '';

$id = (int)$id;

// শুধু ID এবং content check
if($id && $content){
    $stmt = $conn->prepare("UPDATE tours SET  content = ? WHERE id = ?");
    $stmt->bind_param("si", $content, $id);
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>