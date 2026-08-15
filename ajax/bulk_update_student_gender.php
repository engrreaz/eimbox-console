<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_SESSION['sccode'] ?? null;
$stids = $_POST['stids'] ?? [];
$gender = $_POST['gender'] ?? null;

if (!$sccode || empty($stids) || !$gender) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

if (!is_array($stids)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid student IDs format.']);
    exit;
}

if (!in_array($gender, ['Male', 'Female'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid gender value.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($stids), '?'));
$types = str_repeat('s', count($stids));

$stmt = $conn->prepare("UPDATE students SET gender = ? WHERE sccode = ? AND stid IN ($placeholders)");
$stmt->bind_param("ss" . $types, $gender, $sccode, ...$stids);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'updated_count' => $stmt->affected_rows]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
?>