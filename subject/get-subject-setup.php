<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_GET['id']);
$r = $conn->query("SELECT * FROM subsetup WHERE id=$id");
echo json_encode($r->fetch_assoc());