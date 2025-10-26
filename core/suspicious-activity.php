<?php
// check for 3rd verification 
session_start();
$_SESSION['checked_suspicious'] = true;
require_once 'core-val.php';

$suspicious = false;

if ($suspicious == true) {
    $_SESSION['suspicious'] = true;
}

if ($suspicious && $currentFile !== 'mfa-3.php') {
    // echo 'Check';
    header('Location: mfa-3.php');
    exit;
}

if ($suspicious == false) {
    $redi = APP_PATH;
    header("Location: $redi");
}