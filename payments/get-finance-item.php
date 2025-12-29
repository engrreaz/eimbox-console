<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id = intval($_POST['id']);
$q = $conn->query("SELECT * FROM financesetup WHERE id=$id");
echo json_encode($q->fetch_assoc());
