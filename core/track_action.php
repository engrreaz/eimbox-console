<?php
session_start();
require_once 'config.php';
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("INSERT INTO user_actions (sccode,email,url,page,action,timestamp,ip,browser, points) VALUES (?,?,?,?,?,?,?,?,?)");
$sccode = $data['sccode'];
$email = $data['email'];
$url = $data['url'];
$page = $data['page'];
$action = $data['action'];
$point = $data['point'] ?? 0;
$timestamp = $data['timestamp'];
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];

$stmt->bind_param("ssssssssi", $sccode, $email, $url,  $page, $action, $timestamp, $ip, $browser, $point);
$stmt->execute();