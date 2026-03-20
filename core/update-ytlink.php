<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
$yt = trim($_POST['ytlink'] ?? '');

if (!$id) exit('Invalid ID');

$stmt = $conn->prepare("UPDATE modulemanager SET ytlink=? WHERE id=?");
$stmt->bind_param("si", $yt, $id);

if ($stmt->execute()) echo 'OK';
else echo 'DB Error';