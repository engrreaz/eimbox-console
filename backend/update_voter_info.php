<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

// ব্যবহারকারী লগইন করা আছে কিনা এবং অনুমতি আছে কিনা তা পরীক্ষা করুন
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$stid = $_POST['stid'] ?? null;
$field = $_POST['field'] ?? null;
$value = $_POST['value'] ?? '';
$sccode = $_SESSION['sccode'] ?? null;

if (!$stid || !$field || !$sccode) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters.']);
    exit;
}

// নিরাপত্তা: শুধুমাত্র নির্দিষ্ট কিছু কলাম আপডেট করার অনুমতি দিন
$allowed_fields = [
    'stnameeng',
    'stnameben',
    'fname',
    'mname',
    'fnid',
    'mnid',
    'previll',
    'prepo'
];

if (!in_array($field, $allowed_fields)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized field update.']);
    exit;
}

// SQL কুয়েরি তৈরি এবং 실행
$sql = "UPDATE students SET `$field` = ? WHERE stid = ? AND sccode = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed.']);
    exit;
}

$stmt->bind_param("ssi", $value, $stid, $sccode);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update the record.']);
}

$stmt->close();