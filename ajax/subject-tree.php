<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sql = "SELECT DISTINCT sessionyear, areaname, subarea 
        FROM areas WHERE sccode='$sccode' 
        ORDER BY sessionyear DESC, areaname, subarea";

$data = [];
$q = $conn->query($sql);
while($r = $q->fetch_assoc()){
  $data[$r['sessionyear']][$r['areaname']][] = $r['subarea'];
}

foreach($data as $year => $areas){
  echo "<div class='mb-1'>";
  echo "<button class='btn btn-sm btn-outline-secondary w-100 text-start' data-bs-toggle='collapse' data-bs-target='#y$year'>$year</button>";
  echo "<div id='y$year' class='collapse ms-3 mt-1'>";
  
  foreach($areas as $area => $subs){
    $aid = md5($year.$area);
    echo "<button class='btn btn-sm btn-outline-info w-100 text-start' data-bs-toggle='collapse' data-bs-target='#a$aid'>$area</button>";
    echo "<div id='a$aid' class='collapse ms-3 mt-1'>";
    foreach($subs as $sub){
      $val = "$year|$area|$sub";
      echo "<div><input type='checkbox' class='form-check-input me-1 chkSource' value='$val'> <label>$sub</label></div>";
    }
    echo "</div>";
  }
  echo "</div></div>";
}
?>
