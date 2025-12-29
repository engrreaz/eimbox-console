<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : '');
$sy = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : '');


$fid = intval($_POST['fid']);
$itemcode = $_POST['itemcode'];
$cls = $_POST['class'] ?? '';
$sec = $_POST['section'] ?? '';

$q = $conn->query("SELECT amount FROM financesetupvalue WHERE itemcode='$itemcode' AND  classname='$cls' AND sectionname='$sec' and sccode='$sccode' and sessionyear='$sy' and slot='$slot'");
if ($q->num_rows) {
  echo json_encode(['amount' => $q->fetch_assoc()['amount']]);
} else {
  echo json_encode(['amount' => 0]);
}