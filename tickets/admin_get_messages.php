<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_GET['ticket_id']);
$query = "
    SELECT ticket_messages.*, usersapp.profilename 
    FROM ticket_messages 
    JOIN usersapp ON usersapp.id = ticket_messages.sender_id 
    WHERE ticket_id=$ticket_id 
    ORDER BY sent_at ASC
";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $class = ($row['admin_response'] == 1) ? 'text-end text-primary' : 'text-start text-body';
    $msgText = htmlspecialchars($row['message'], ENT_QUOTES);
    echo "<div class='mb-2 $class message-item' data-id='{$row['id']}' data-message='{$msgText}'>
            <strong>{$row['profilename']}:</strong> {$row['message']}
          </div>";
}


?>
