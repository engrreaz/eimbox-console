<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$year = $_GET['year'] ?? '';
$cls  = $_GET['cls'] ?? '';

if(!$year){
  $q="SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' and active=1 ORDER BY syear DESC";
  $r=$conn->query($q);
  $opt='';
  while($row=$r->fetch_assoc()){
    $y=$row['syear'];
    $opt.="<option value='$y'>$y</option>";
  }
  echo json_encode(['years'=>$opt]);
  exit;
}

if(!$cls){
  $q="SELECT DISTINCT classname FROM subsetup WHERE sessionyear='$year'";
  $r=$conn->query($q);
  $opt='';
  while($row=$r->fetch_assoc()){
    $v=$row['classname'];
    $opt.="<option value='$v'>$v</option>";
  }
  echo json_encode(['classes'=>$opt]);
  exit;
}

$q="SELECT DISTINCT sectionname FROM subsetup 
    WHERE sessionyear='$year' AND classname='$cls'";
$r=$conn->query($q);
$opt='';
while($row=$r->fetch_assoc()){
  $v=$row['sectionname'];
  $opt.="<option value='$v'>$v</option>";
}
echo json_encode(['sections'=>$opt]);