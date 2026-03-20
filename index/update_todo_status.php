<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);
    $now = date('Y-m-d H:i:s');

    // ডাটাবেজ আপডেট
    $stmt = $conn->prepare("UPDATE todolist SET status = ?, responsetime = ? WHERE id = ?");
    $stmt->bind_param("isi", $status, $now, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    $stmt->close();
}
?>