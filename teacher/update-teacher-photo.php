<?php
// require_once 'db_config.php'; // আপনার ডাটাবেজ কানেকশন ফাইল
require_once('../core/core-val.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['teacher_photo'])) {
    $tid = $_POST['tid'];
    $file = $_FILES['teacher_photo'];
    
    $targetDir = BASE_ROOT ."teacher/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = $tid . ".jpg"; // টিচার আইডি অনুযায়ী নাম
    $targetFilePath = $targetDir . $fileName;

    // ফাইল টাইপ চেক করা
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            // যদি ডাটাবেজে ছবির নাম রাখতে চান (ঐচ্ছিক)
            // $conn->query("UPDATE teacher SET photourl = '$fileName' WHERE tid = '$tid'");
            
            echo json_encode(['status' => 'success', 'message' => 'Uploaded successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    }
}
?>