<?php
require_once '../core/config.php';
require_once '../core/db.php';

$sender = intval($_GET['sender']);
$receiver = intval($_GET['receiver']);

$query = "SELECT * FROM chats WHERE 
          (sender_id=$sender AND receiver_id=$receiver) 
       OR (sender_id=$receiver AND receiver_id=$sender)
          ORDER BY sent_at ASC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $class = ($row['sender_id'] == $sender) ? 'text-end text-primary' : 'text-start text-body';
    echo '<div class="mb-2 '.$class.'"><small>'.$row['message'].'</small></div>';
}