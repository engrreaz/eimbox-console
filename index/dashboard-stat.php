<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");

$res = mysqli_query($conn, "SELECT COUNT(yn) cnt FROM stattnd WHERE adate='$today' AND yn=1");
$present = mysqli_fetch_assoc($res)['cnt'];

echo json_encode([
    "today"   => $today,
    "present" => $present
]);