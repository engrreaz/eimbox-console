<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_GET['ticket_id']);
$query = "SELECT ticket_messages.*, users.name 
          FROM ticket_messages 
          JOIN users ON users.id = ticket_messages.sender_id 
          WHERE ticket_id=$ticket_id ORDER BY sent_at ASC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo '<div class="mb-2"><strong>'.$row['name'].':</strong> '.$row['message'].'</div>';
}
?>
