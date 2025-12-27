<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$weekends = $_POST['weekends'] ?? '';
$sccode   = $_SESSION['sccode'];

$sql = "UPDATE settings 
        SET settings_value='$weekends'
        WHERE setting_title='Weekends' AND sccode='$sccode'";

$conn->query($sql);
echo '<span class="text-success">Updated</span>';
