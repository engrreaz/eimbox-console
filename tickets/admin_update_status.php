<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ticket_id = intval($_POST['ticket_id']);
$status = $conn->real_escape_string($_POST['status']);
$tail = $conn->real_escape_string($_POST['tail']);

if ($tail == 'status') {
    $col = 'status';
} else if ($tail == 'category') {
    $col = 'category';
} else if ($tail == 'priority') {
    $col = 'priority';
}

$conn->query("UPDATE tickets SET $col='$status' WHERE id=$ticket_id");
echo "updated";

?>

