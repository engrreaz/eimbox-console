<?php
require_once '../core/config.php';
require_once '../core/db.php';

session_start();


$user_id = $_POST['user_id'] ?? $_SESSION['user_id'];
$sccode = $_SESSION['sccode'] ?? '';
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$user_id || !$subject || !$message) {
    echo "fail";
    exit;
}

// টিকেট ইনসার্ট
$stmt = $conn->prepare("INSERT INTO tickets (user_id, sccode, subject) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $sccode, $subject);
$insertTicket = $stmt->execute();
$ticket_id = $stmt->insert_id;
$stmt->close();

if ($insertTicket && $ticket_id) {
    // প্রথম মেসেজ ইনসার্ট
    $stmt = $conn->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ticket_id, $user_id, $message);
    $insertMessage = $stmt->execute();
    $stmt->close();

    if ($insertMessage) {
        echo "success";
        exit;
    }
}

echo "fail";
?>