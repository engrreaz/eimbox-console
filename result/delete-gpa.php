<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM gpa WHERE id='$id' AND sccode='$sccode'");

echo "OK";
