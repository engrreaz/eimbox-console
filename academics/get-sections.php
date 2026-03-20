<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';


$class = $_POST['class'];
$slot = $_POST['slot'];
$session = $_POST['session'];


$sc_sql = "SELECT sectionname, count(sectionname) as cnt from sessioninfo where sccode='$sccode' and sessionyear='$session' and slot='$slot' and classname='$class' group by sectionname";
$sc_q = mysqli_query($conn, $sc_sql);
$sc_data = [];
while ($r = mysqli_fetch_assoc($sc_q)) {
    $sc_data[$r['sectionname'] = $r['cnt']] ?? 0;
}


$tea_data = "SELECT tid, tname, position FROM teacher WHERE sccode='$sccode'";
$tea_q = mysqli_query($conn, $tea_data);
$tea_data = [];
while ($r = mysqli_fetch_assoc($tea_q)) {
    $tea_data[$r['tid']] = $r['tname'];
}



$sql = "
    SELECT   a.* FROM areas a
    WHERE a.sccode='$sccode'
    AND a.areaname='$class'
    AND a.slot='$slot'
    AND a.sessionyear='$session'

    GROUP BY a.id
    ORDER BY a.idno ASC
    ";

// echo $sql;

$q = mysqli_query($conn, $sql);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {

    $sec = $r['subarea'];
    $tid = $r['classteacher'];
    $tname = $tea_data[$tid];
    $count = $sc_data[$sec] ?? 0;

    $photo_path = teacher_profile_image_path($tid);
    $r['tid'] = $tid;
    $r['classteacher'] = $tname;
    $r['photourl'] = $photo_path;
    $r['student_count'] = $count;

    $data[] = $r;
}

echo json_encode($data);