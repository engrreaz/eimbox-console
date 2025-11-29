<?php
require_once dirname(__DIR__) . "/core/config.php";   // depends on your structure
require_once dirname(__DIR__) . "/core/db.php";
require_once dirname(__DIR__) . "/core/functions.php";

// mobile & text receive
$mobile = $_POST['mobile'] ?? '';
$text = $_POST['text'] ?? '';
$camp = $_POST['camp'] ?? 'Regular';

$mobile = trim($mobile);
$text = trim($text);


if ($mobile == "" || $text == "") {
    echo "Invalid request";
    exit;
}

if (global_send_sms($mobile, $text, $camp)) {
    echo "SMS sent to $mobile";
} else {
    echo "Failed to send SMS!";
}
