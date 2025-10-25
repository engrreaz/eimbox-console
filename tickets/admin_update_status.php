<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_POST['ticket_id']);
$status = $conn->real_escape_string($_POST['status']);

if (in_array($status, ['open', 'in_progress', 'closed'])) {
    $conn->query("UPDATE tickets SET status='$status' WHERE id=$ticket_id");
    echo "updated";
}
?>
