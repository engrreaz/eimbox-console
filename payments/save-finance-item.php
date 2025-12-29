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
$sy = $_POST['session'] ?? $y_v4;

if ($id == 0) {
    $sql = "INSERT INTO financesetup
  (sccode,sessionyear,particulareng,particularben,month,new_only,splitable)
  VALUES('$sccode','$sy','$eng','$ben','$mon','$new','$split')";
  echo $sql;
    $conn->query($sql);
} else {
    $conn->query("UPDATE financesetup SET
    particulareng='$eng',
    particularben='$ben',
    month='$mon',
    new_only='$new',
    splitable='$split'
    WHERE id=$id");
}
echo "<span class='text-success'>success</span>";
