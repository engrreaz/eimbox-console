<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$email = $_POST['email'] ?? '';
$data = $_POST['data'] ?? '';


$sql = "UPDATE usersapp SET st_entry_fld='$data' WHERE email='$email' and sccode='$sccode'";
$conn->query($sql);