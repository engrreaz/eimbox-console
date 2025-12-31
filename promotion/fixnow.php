<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id = $_POST['id'] ?? '';
$roll = $_POST['roll'] ?? '';

$sql = "UPDATE sessioninfo set rollno='$roll' where id='$id' and sccode='$sccode'";
$conn->query($sql);