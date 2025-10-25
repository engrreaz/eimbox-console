<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_POST['ticket_id']);
$user_id = intval($_POST['user_id']);
$message = $conn->real_escape_string($_POST['message']);

$status = $conn->query("SELECT status FROM tickets WHERE id=$ticket_id")->fetch_assoc()['status'];

if ($status === 'closed') {
    echo "closed";
    exit;
}

if (!empty($message)) {
    $conn->query("INSERT INTO ticket_messages (ticket_id, sender_id, message) VALUES ($ticket_id, $user_id, '$message')");
    echo "sent";
} else {
    echo "empty";
}
?>
