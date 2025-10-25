<?php
session_start();



// Core files
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/functions.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);

    $user = find_user_by_email($conn, $email);




    if ($user) {
        $_SESSION['_backup'] = $_SESSION; // সেশন ব্যাকআপ
        store_user_session($user['user'], $user['school']); // লগইন সেশন স্টোর



        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'User not found';
    }
}

echo json_encode($response);
exit;