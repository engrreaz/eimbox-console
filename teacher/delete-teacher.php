<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tid'])) {
    $tid = trim($_POST['tid'] ?? '');

    if (empty($tid) || empty($sccode)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid teacher or school identifier']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM teacher WHERE tid = ? AND sccode = ?");
    $stmt->bind_param("si", $tid, $sccode);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher record deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Teacher record not found or already removed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
}
exit();
