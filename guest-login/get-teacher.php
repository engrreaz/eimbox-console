<?php
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

$sccode   = $_POST['sccode'] ?? '';
$class    = $_POST['class'] ?? '';
$section  = $_POST['section'] ?? '';
$session  = $_POST['session'] ?? '';
$unit     = $_POST['unit'] ?? '';

if(empty($sccode) || empty($class) || empty($section)){
    echo "<option value=''>Select Teacher</option>";
    exit;
}

// Step 1: Get main class teacher ID
$sql_main = "SELECT classteacher FROM areas 
             WHERE sccode='$sccode' AND areaname='$class' AND subarea='$section' 
             AND sessionyear='$session' AND slot='$unit' 
             LIMIT 1";
$result_main = mysqli_query($conn, $sql_main);
$main_tid = mysqli_fetch_assoc($result_main)['classteacher'] ?? null;

$teacher_ids = [];

// Step 2: Get 4 random teachers (excluding main teacher if exists)
$sql_random = "SELECT tid FROM teacher 
               WHERE sccode='$sccode' " . ($main_tid ? "AND tid!='$main_tid'" : "") . " 
               ORDER BY RAND() LIMIT 5";
$result_random = mysqli_query($conn, $sql_random);

while($row = mysqli_fetch_assoc($result_random)){
    $teacher_ids[] = $row['tid'];
}

// Step 3: Add main teacher to the array (if exists)
if($main_tid){
    array_unshift($teacher_ids, $main_tid); // main teacher will be first
}

// Step 4: Fetch names for these TIDs
echo "<option value=''>Select Teacher</option>";

foreach($teacher_ids as $tid){
    $sql_name = "SELECT tname FROM teacher WHERE tid='$tid' LIMIT 1";
    $res_name = mysqli_query($conn, $sql_name);
    if($row_name = mysqli_fetch_assoc($res_name)){
        echo "<option value='{$tid}'>{$row_name['tname']}</option>";
    }
}
