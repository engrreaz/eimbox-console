<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id = $_POST['id'];

$q = mysqli_query($conn, "SELECT * FROM gpa WHERE id='$id'");
echo json_encode(mysqli_fetch_assoc($q));
