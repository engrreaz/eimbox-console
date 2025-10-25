<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_GET['ticket_id']);
$result = $conn->query("SELECT * FROM ticket_messages WHERE ticket_id=$ticket_id ORDER BY sent_at ASC");

while ($row = $result->fetch_assoc()) {
    $align = ($row['sender_id'] == $_GET['user_id']) ? 'text-end text-primary' : 'text-start text-body';
    echo '<div class="'.$align.' mb-2"><small>'.$row['message'].'</small></div>';
}
?>
