<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$medium = $_POST['medium'] ?? '';
$sccode = $_SESSION['sccode'];

$conn->query("
    UPDATE settings 
    SET settings_value='$medium'
    WHERE setting_title='Medium' AND sccode='$sccode'
");

echo '<span class="text-success">Medium updated</span>';
