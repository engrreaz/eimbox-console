<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid exam ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM examlist WHERE id = ? AND sccode = ?");
$stmt->bind_param("ii", $id, $sccode);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Exam deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete exam or exam not found']);
}
$stmt->close();