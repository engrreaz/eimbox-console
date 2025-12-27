<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$class = $_POST['class'];
$slot = $_POST['slot'];
$session = $_POST['session'];

$sql = "
SELECT   a.* FROM areas a
WHERE a.sccode='$sccode'
AND a.areaname='$class'
AND a.slot='$slot'
AND a.sessionyear='$session'

GROUP BY a.id
ORDER BY a.idno ASC
";

$q = mysqli_query($conn, $sql);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {

    $r['student_count'] = 10;

    $data[] = $r;
}

echo json_encode($data);