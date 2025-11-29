<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


// $sms_settings = json_encode($_POST['sms_settings'] ?? "{}", true);
$sms_settings = json_decode($_POST['sms_settings'], true);
$sms_settings = implode(" | ", $sms_settings);
$blockbox = $_POST['blockbox'];

// echo 'KKKKKK<br><br>' . $sms_settings . '<br><br>' . $blockbox;

$upd = "UPDATE scinfo set $blockbox='$sms_settings' where sccode='$sccode'";
// echo '<br><br>' . $upd;
$conn->query($upd);