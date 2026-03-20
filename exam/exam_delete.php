<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id=$_POST['id'];
$stmt=$conn->prepare("DELETE FROM examlist WHERE id=?");
$stmt->bind_param("i",$id);

echo $stmt->execute() ? 'ok' : $conn->error;