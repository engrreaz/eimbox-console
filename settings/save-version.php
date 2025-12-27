<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$version = $_POST['version'] ?? '';
$sccode  = $_SESSION['sccode'];

$conn->query("
    UPDATE settings 
    SET settings_value='$version'
    WHERE setting_title='Version' AND sccode='$sccode'
");

echo '<span class="text-success">Version updated</span>';
