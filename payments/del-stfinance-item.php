<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id = $_POST['fid'];

 $query331 = "DELETE FROM stfinance where id = '$id' and sccode='$sccode'";
$conn->query($query331);

