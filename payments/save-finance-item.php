<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = intval($_POST['id']);
$eng = $_POST['eng'];
$ben = $_POST['ben'];
$mon = $_POST['mon'];
$new = $_POST['new_only'];
$split = $_POST['splitable'];
$acc_head = $_POST['acc_head'] ?? 0;

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : '');
$sy = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : '');


if ($id == 0) {
  $uid = uniqid();
  $sql = "INSERT INTO financesetup
  (sccode,sessionyear,particulareng,particularben,month,new_only,splitable, itemcode, slno, sub_head)
  VALUES('$sccode','$sy','$eng','$ben','$mon','$new','$split', '$uid', 99, '$acc_head')";
  echo $sql;
  $conn->query($sql);
} else {
  $upd = "UPDATE financesetup SET
    particulareng='$eng',
    particularben='$ben',
    month='$mon',
    new_only='$new',
    splitable='$split', sub_head='$acc_head'
    WHERE id=$id";
  $conn->query($upd);
}
echo "<span class='text-success'>success</span>";
