<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");

$res = mysqli_query($conn, "SELECT COUNT(yn) cnt FROM stattnd WHERE adate='$today' AND yn=1 and sccode='$sccode'");
$present = mysqli_fetch_assoc($res)['cnt'];

$res = mysqli_query($conn, "SELECT COUNT(yn) cnt FROM stattnd WHERE adate='$today' AND bunk=1 and sccode='$sccode'");
$bunk = mysqli_fetch_assoc($res)['cnt'];

$res = mysqli_query($conn, "SELECT COUNT(tid) cnt FROM teacherattnd WHERE adate='$today' and sccode='$sccode'");
$teacher = mysqli_fetch_assoc($res)['cnt'];


$res = mysqli_query($conn, "SELECT sum(amount) taka FROM stpr WHERE prdate='$today' and sccode='$sccode'");
$taka = mysqli_fetch_assoc($res)['taka'];





echo json_encode([
    "today" => $today,
    "present" => $present,
    "bunk" => $bunk,
    "teacher" => $teacher,
    "collection" => (int) $taka,
    "expense" => 0,
    "cl" => 0
]);
?>