<?php
require_once '../core/config.php';
require_once '../core/db.php';

$sender = intval($_POST['sender']);
$receiver = intval($_POST['receiver']);
$message = $conn->real_escape_string($_POST['message']);

if (!empty($message)) {
    $conn->query("INSERT INTO chats (sender_id, receiver_id, message) 
                    VALUES ($sender, $receiver, '$message')");
    echo "sent";
}