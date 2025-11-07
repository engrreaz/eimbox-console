<?php
// require_once '../core/config.php';
// require_once '../core/db.php';

$sccode = $_POST['sccode'] ?? '';
if (!$sccode) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid sccode']);
    exit;
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'msg' => 'No file uploaded']);
    exit;
}

// অনুমোদিত ফাইল টাইপ
$allowed = ['image/png', 'image/jpeg', 'image/jpg'];
if (!in_array($_FILES['logo']['type'], $allowed)) {
    echo json_encode(['status' => 'error', 'msg' => 'Only PNG/JPG allowed']);
    exit;
}

$uploadDir = "../../logo/";
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0755, true);

$ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
$targetFile = $uploadDir . $sccode . ".png"; // সবকে png নামে রাখবো

if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Failed to move uploaded file']);
}
