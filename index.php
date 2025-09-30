<?php
require_once 'core/init.php';

// Check login
// if (!isset($_SESSION['user_id'])) {
//     // Try remember-me
//     $conn = db_connect();
//     if (!verify_remember_token($conn)) {
//         header('Location: login.php');
//         exit;
//     }
// }

$user_name = $_SESSION['user_name'] ?? 'User';

// check userlevel
// check 