<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = $_POST['id'];
mysqli_query($conn, "DELETE FROM slots WHERE id='$id'");
echo "Slot Deleted";
?>
