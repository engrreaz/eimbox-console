<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

header('Content-Type: application/json');

$class = $conn->real_escape_string($_POST['class'] ?? '');
$slot = $conn->real_escape_string($_POST['slot'] ?? '');
$session = $conn->real_escape_string($_POST['session'] ?? '');

$sc_sql = "SELECT sectionname, count(sectionname) as cnt FROM sessioninfo WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND classname='$class' GROUP BY sectionname";
$sc_q = mysqli_query($conn, $sc_sql);
$sc_data = [];
if ($sc_q) {
    while ($r = mysqli_fetch_assoc($sc_q)) {
        $sc_data[$r['sectionname']] = $r['cnt'];
    }
}

$tea_data = [];
$tea_q = mysqli_query($conn, "SELECT id, tid, tname, position FROM teacher WHERE sccode='$sccode'");
if ($tea_q) {
    while ($r = mysqli_fetch_assoc($tea_q)) {
        if (!empty($r['tid']) && $r['tid'] !== '0') {
            $tea_data[$r['tid']] = $r['tname'];
        }
        $tea_data[$r['id']] = $r['tname'];
    }
}

$sql = "
    SELECT a.* FROM areas a
    WHERE a.sccode='$sccode'
    AND a.areaname='$class'
    AND a.slot='$slot'
    AND a.sessionyear='$session'
    ORDER BY a.idno ASC
";

$q = mysqli_query($conn, $sql);

$data = [];
if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
        $sec = $r['subarea'];
        $tid = $r['classteacher'] ?? '';
        $tname = (!empty($tid) && isset($tea_data[$tid])) ? $tea_data[$tid] : '';

        $photo_path = teacher_profile_image_path($tid);
        $r['tid'] = $tid;
        $r['teacher_name'] = $tname;
        $r['photourl'] = $photo_path;
        $r['student_count'] = $sc_data[$sec] ?? 0;

        $data[] = $r;
    }
}

echo json_encode($data);
?>