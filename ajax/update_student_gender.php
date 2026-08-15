<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_SESSION['sccode'] ?? null;
$stid = $_POST['stid'] ?? null;
$gender = $_POST['gender'] ?? null;

if (!$sccode || !$stid || !$gender) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

if (!in_array($gender, ['Male', 'Female'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid gender value.']);
    exit;
}

$stmt = $conn->prepare("UPDATE students SET gender = ? WHERE stid = ? AND sccode = ?");
$stmt->bind_param("ssi", $gender, $stid, $sccode);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
?>