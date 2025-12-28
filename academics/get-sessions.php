<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = $_SESSION['sccode'];

// $q = mysqli_query($conn, "
//     SELECT DISTINCT sessionyear 
//     FROM areas 
//     WHERE sccode='$sccode'
//     ORDER BY sessionyear DESC
// ");

$q = mysqli_query($conn, "
    SELECT syear as sessionyear
    FROM sessionyear 
    WHERE sccode='$sccode' AND active=1
    ORDER BY syear
    ");

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r['sessionyear'];
}

echo json_encode($data);
