<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$type   = $_POST['type'] ?? '';
$value  = $_POST['value'] ?? '';
$sccode = $sccode; // global_values.php থেকে

$data = [];

if ($type === 'slot') {
    $sql = "SELECT id, slotname FROM slot WHERE sccode='$sccode'";
}

if ($type === 'session') {
    $sql = "SELECT id, syear FROM sessionyear WHERE sccode='$sccode' AND slot_id='$value'";
}

if ($type === 'class') {
    $sql = "SELECT id, areaname FROM areas 
            WHERE sccode='$sccode' AND parent_id='$value'";
}

if ($type === 'section') {
    $sql = "SELECT id, subarea FROM areas 
            WHERE sccode='$sccode' AND parent_id='$value'";
}

if (isset($sql)) {
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}

echo json_encode($data);
