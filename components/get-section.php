<?php

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_GET['slot'];
$session = $_GET['session'];
$class = $_GET['class'];

$q = mysqli_query($conn, "SELECT subarea FROM areas 
    WHERE sccode='$sccode' AND slot='$slot' AND sessionyear='$session' 
    AND areaname='$class'  order by idno");

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = ["value" => $r['subarea'], "label" => $r['subarea']];
}

echo json_encode($data);
