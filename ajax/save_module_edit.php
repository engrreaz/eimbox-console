<?php
require_once '../core/config.php';
require_once '../core/db.php';

$id = intval($_POST['id']);
$name = trim($_POST['module_name']);
$icon = trim($_POST['module_icon']);
$desc = trim($_POST['descrip']);

$stmt = $conn->prepare("UPDATE modulelist SET module_name=?, module_icon=?, descrip=?, modifieddate=NOW() WHERE id=?");
$stmt->bind_param('sssi', $name, $icon, $desc, $id);
$stmt->execute();

echo $stmt->affected_rows ? "Saved" : "No change";

