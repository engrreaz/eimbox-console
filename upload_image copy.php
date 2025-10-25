<?php
session_start();

// Allowed extensions
$allowed = ['jpg','jpeg','png','gif','webp'];
$uploadDir = 'uploads/';
$response = ['success'=>false, 'url'=>'', 'error'=>''];

if(isset($_FILES['image'])){
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed)){
        $response['error'] = 'Invalid file type';
        echo json_encode($response); exit;
    }

    if($file['error'] !== 0){
        $response['error'] = 'Upload error';
        echo json_encode($response); exit;
    }

    $filename = uniqid('img_').'.'.$ext;
    $target = $uploadDir.$filename;

    if(move_uploaded_file($file['tmp_name'], $target)){
        $response['success'] = true;
        $response['url'] = $target; // relative path
    } else {
        $response['error'] = 'Could not move uploaded file';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
