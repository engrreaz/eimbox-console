<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = $_SESSION['sccode'];

$q = mysqli_query($conn,"
    SELECT DISTINCT slotname as slot
    FROM slots
    WHERE sccode='$sccode'
    ORDER BY slot
");

$data = [];
while($r = mysqli_fetch_assoc($q)){
    $data[] = $r['slot'];
}

echo json_encode($data);
