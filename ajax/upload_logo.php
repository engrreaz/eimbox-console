<?php
// require_once '../core/config.php';
// require_once dirname(__DIR__) .  '/core/core-val.php';

// echo dirname(__DIR__) .  '/core/core-val.php'; 



// 1. 105141
// 2. 105155  ---------------------------------------------
// 3. 105151
// 4. 105118
// 5. 105124
// 6. 105147
// 7. 105138
// 8. 105122
// 9. 105145
// 10. 105154
// 11. 105146
// 12. 105121
// 13. 105126
// 14. 105117
// 15. 105139



$sccode = $_POST['sccode'] ?? '';

$src =  '../../sign/000000.png';
$dest =  '../../sign/' . $sccode . '.png';


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

copy($src, $dest);

if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {



    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Failed to move uploaded file']);
}
