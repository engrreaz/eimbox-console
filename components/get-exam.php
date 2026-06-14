<?php

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_GET['slot'];
$session = $_GET['session'];

$q = mysqli_query($conn, "SELECT examtitle FROM examlist 
    WHERE sccode='$sccode' AND slot='$slot' AND sessionyear='$session'");

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = ["value" => $r['examtitle'], "label" => $r['examtitle']];
}
 $data[] = ["value" => 'Grand', "label" => 'Grand'];

echo json_encode($data);
