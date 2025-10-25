<?php
require_once '../core/config.php';
require_once '../core/db.php';

$user_id = intval($_POST['user_id']);
$subject = $conn->real_escape_string($_POST['subject']);
$message = $conn->real_escape_string($_POST['message']);

if (!empty($subject) && !empty($message)) {
    $conn->query("INSERT INTO tickets (user_id, subject) VALUES ($user_id, '$subject')");
    $ticket_id = $conn->insert_id;
    $conn->query("INSERT INTO ticket_messages (ticket_id, sender_id, message) 
                    VALUES ($ticket_id, $user_id, '$message')");
    echo "success";
} else {
    echo "empty";
}
?>
