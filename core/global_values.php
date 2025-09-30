<?php
if (!isset($_SESSION)) {
    session_start();
}


$error_message = '';
// echo 'User ID is set: ' . $_SESSION['user_id'];
$user_id_no = $_SESSION['user_id'] ?? '';
$usr = $_SESSION['user_email'] ?? '';
$username = $_SESSION['user_name'] ?? '';
$userlevel = $_SESSION['userlevel'] ?? '';
$sccode = 103187;
$sctype = 'school';

$cur = date('Y-m-d H:i:s');

// var_dump($_SESSION);
// echo $usr;





// $_SESSION['first_name'] . '/ ' .
// $_SESSION['last_name'] . '/ ' .
// $_SESSION['phone'] . '/ ' .
// $_SESSION['address'] . '/ ' .
// $_SESSION['dob'];