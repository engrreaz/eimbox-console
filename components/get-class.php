<?php

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_GET['slot'];
$session = $_GET['session'];

$q = mysqli_query($conn, "SELECT MAX(idno) as idno, areaname FROM areas 
    WHERE sccode='$sccode' AND slot='$slot' AND sessionyear='$session' 
    GROUP BY areaname order by idno");

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = ["value" => $r['areaname'], "label" => $r['areaname']];
}

echo json_encode($data);
