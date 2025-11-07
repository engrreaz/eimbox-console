<?php
require_once 'core/init.php';
session_destroy();
setcookie('remember_me', '', time() - 3600, '/', '', true, true);

if (isset($_GET['admission'])) {
    header('Location: admission-login.php');
    exit;
}

header('Location: login.php');
exit;
