<?php

session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_POST['ticket_id']);
$user_id   = intval($_POST['user_id'] ?? $_SESSION['user_id']);
$message   = trim($_POST['message'] ?? '');

if (!$ticket_id || !$user_id || empty($message)) {
    echo "empty";
    exit;
}

// টিকেট স্ট্যাটাস যাচাই
$status = $conn->query("SELECT status FROM tickets WHERE id=$ticket_id")->fetch_assoc()['status'];
if ($status === 'closed') {
    echo "closed";
    exit;
}

// মেসেজ ইনসার্ট
$stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $ticket_id, $user_id, $message);
$stmt->execute();
$stmt->close();

echo "sent";
?>