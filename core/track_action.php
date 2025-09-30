<?php
session_start();
require_once 'config.php';
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("INSERT INTO user_actions (email,page,action,timestamp,ip,browser) VALUES (?,?,?,?,?,?)");
$email = $data['email'];
$page = $data['page'];
$action = $data['action'];
$timestamp = $data['timestamp'];
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];

$stmt->bind_param("ssssss", $email, $page, $action, $timestamp, $ip, $browser);
$stmt->execute();
