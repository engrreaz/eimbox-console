<?php
session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'global_values.php';
// require_once 'functions.php';

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("INSERT INTO user_actions (sccode,email,url,page,action,timestamp,ip,browser, points) VALUES (?,?,?,?,?,?,?,?,?)");
$sccode = $data['sccode'] ?? $_SESSION['sccode'] ?? '101010';
$email = $data['email'];
$url = $data['url'] ?? basename($_SERVER['PHP_SELF']);;
$page = $data['page'];
$action = $data['action'];
$point = $data['point'] ?? 0;
// $timestamp = $data['timestamp'];
$timestamp = $cur;
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];

$stmt->bind_param("ssssssssi", $sccode, $email, $url,  $page, $action, $timestamp, $ip, $browser, $point);
$stmt->execute();

echo 'Success';