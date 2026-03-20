<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
if(!$id) exit('Invalid ID');

$q = "DELETE FROM subsetup WHERE id='$id' LIMIT 1";

if($conn->query($q)){
  echo 'OK';
}else{
  echo 'Delete failed';
}