<?php
if (!isset($_SESSION)) {
    session_start();
}


$release_colors = [
    0 => "#f8f8f8ff",  // white
    1 => "#808080",  // Gray
    2 => "#FF0000",  // Red
    3 => "#FFA500",  // Orange
    4 => "#FFD700",  // Yellow
    5 => "#1E90FF",  // Blue
    6 => "#07af07ff",  // Green
    7 => "#800080",  // Purple
    8 => "#012201ff"   // Dark Green
];

$release_text = [
    0 => "#140202ff",  // white
    1 => "#1a0a0aff",    // Gray
    2 => "#f5f5f5ff",    // Red
    3 => "#332306ff",    // Orange
    4 => "#1a180dff",    // Yellow
    5 => "#072746ff",    // Blue
    6 => "#ffffffff",  // Green
    7 => "#e6d8e6ff",    // Purple
    8 => "#edf0edff"   // Dark Green
];

$error_message = '';
// echo 'User ID is set: ' . $_SESSION['user_id'];
$user_id_no = $_SESSION['user_id'] ?? '';
$usr = $_SESSION['user_email'] ?? '';
$username = $_SESSION['user_name'] ?? '';
$pth = $_SESSION['photourl'] ?? '';
$userlevel = $_SESSION['userlevel'] ?? '';
$is_admin = 5;

$sccode = $_SESSION['sccode'] ?? '';

$sctype = $_SESSION['sccategory'] ?? '';
$scname = $_SESSION['scname'] ?? '';

$cur = date('Y-m-d H:i:s');

// var_dump($_SESSION);
// echo $usr;





// $_SESSION['first_name'] . '/ ' .
// $_SESSION['last_name'] . '/ ' .
// $_SESSION['phone'] . '/ ' .
// $_SESSION['address'] . '/ ' .
// $_SESSION['dob'];