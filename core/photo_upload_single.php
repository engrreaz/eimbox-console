<?php
// photo_upload_single.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $output_dir = $_POST['output_dir'] ?? 'uploads/';
    $output_file_name = $_POST['output_file_name'] ?? 'image.jpg';

    // ফোল্ডার না থাকলে তৈরি করো
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0777, true);
    }

    if (isset($_FILES['file']['tmp_name'])) {
        $target = $output_dir . $output_file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully!',
                'path' => 'core/' . $target
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file received!']);
    }
}
?>
