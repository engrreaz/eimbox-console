<?php
require_once '../core/config.php';
require_once '../core/db.php';

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM bankinfo WHERE id='$id'");

echo "<div class='alert alert-danger'>Account Deleted</div>";
