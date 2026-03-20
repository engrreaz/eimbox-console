<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$ids = $_POST['ids'] ?? '';
if(!$ids) exit('No data');

$arr = explode(',', $ids);
$sl = 1;

foreach($arr as $id){
  $id = intval($id);
  if($id){
    $conn->query("
      UPDATE subsetup 
      SET slno='$sl'
      WHERE id='$id'
      LIMIT 1
    ");
    $sl++;
  }
}

echo 'OK';