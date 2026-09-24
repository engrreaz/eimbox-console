<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$year   = $_GET['year'] ?? '';
$cls    = $_GET['cls'] ?? '';
$global = $_GET['global'] ?? '';

if(!$year){
  $q="SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY syear DESC";
  $r=$conn->query($q);
  $opt='';
  if($r){
    while($row=$r->fetch_assoc()){
      $y=$row['syear'];
      $opt.="<option value='$y'>$y</option>";
    }
  }
  echo json_encode(['years'=>$opt]);
  exit;
}

if(!$cls){
  if($global == '1') {
    $q="SELECT DISTINCT classname FROM subsetup WHERE sccode=0 AND sessionyear='$year' ORDER BY classname";
  } else {
    $q="SELECT areaname AS classname FROM areas WHERE sccode='$sccode' AND sessionyear='$year' AND areaname IS NOT NULL AND areaname != '' GROUP BY areaname ORDER BY MIN(idno) ASC, areaname ASC";
  }
  $r=$conn->query($q);
  $opt='';
  if($r){
    while($row=$r->fetch_assoc()){
      $v=$row['classname'];
      if($v !== null && $v !== ''){
        $opt.="<option value='$v'>$v</option>";
      }
    }
  }
  echo json_encode(['classes'=>$opt]);
  exit;
}

if($global == '1') {
  $q="SELECT DISTINCT sectionname FROM subsetup WHERE sccode=0 AND sessionyear='$year' AND classname='$cls' ORDER BY sectionname";
} else {
  $q="SELECT DISTINCT subarea AS sectionname FROM areas WHERE sccode='$sccode' AND sessionyear='$year' AND areaname='$cls' ORDER BY subarea";
}
$r=$conn->query($q);
$opt='';
if($r){
  while($row=$r->fetch_assoc()){
    $v=$row['sectionname'];
    if($v !== null && $v !== ''){
      $opt.="<option value='$v'>$v</option>";
    }
  }
}
echo json_encode(['sections'=>$opt]);