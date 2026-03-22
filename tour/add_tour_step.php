<?php
include '../core/config.php';
include '../core/db.php';

$file = $_POST['file'] ?? '';
$step_no = $_POST['step_no'] ?? '';
$element_id = $_POST['element_id'] ?? '';
$content = $_POST['content'] ?? '';

if($file && $step_no && $content){
    $stmt = $conn->prepare("INSERT INTO tours (page, step_no, element_id,  content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $file, $step_no, $element_id,  $content);
    if($stmt->execute()){
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'message'=>$conn->error]);
    }
} else {
    echo json_encode(['success'=>false, 'message'=>'Missing required fields']);
}
?>