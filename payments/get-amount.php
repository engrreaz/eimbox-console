<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : '');
$sy = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : '');

$fid = intval($_POST['fid']);
if (isset($_POST['itemcode'])) {
  $itemcode = $_POST['itemcode'];
} else {

  $sql = "SELECT itemcode  FROM financesetup   WHERE id = ? AND sccode = ?  LIMIT 1";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $fid, $sccode);
  $stmt->execute();
  $stmt->bind_result($itemcode);

  if ($stmt->fetch()) {
    // echo $itemcode;
  } else {
    // echo '';
  }

  $stmt->close();

}


$cls = $_POST['class'] ?? '';
$sec = $_POST['section'] ?? '';

$ql = "SELECT amount FROM financesetupvalue WHERE itemcode='$itemcode' AND  classname='$cls' AND sectionname='$sec' and sccode='$sccode' and sessionyear='$sy' and slot='$slot'";
$q = $conn->query($ql);
if ($q->num_rows) {
  echo json_encode(['amount' => $q->fetch_assoc()['amount']]);
} else {
  echo json_encode(['amount' => 0]);
}