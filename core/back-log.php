<?php
session_start();
$_SESSION = $_SESSION['_backup'];
unset($_SESSION['_backup']);
header('Location: ../index.php');
exit;