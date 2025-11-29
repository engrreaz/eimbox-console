<?php

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_GET['slot'];


$q = mysqli_query($conn, "SELECT syear FROM sessionyear 
    WHERE sccode='$sccode' AND active=1 ");
    

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = ["value" => $r['syear'], "label" => $r['syear']];
}

echo json_encode($data);