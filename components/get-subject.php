<?php

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// incoming filters
$slot = $_GET['slot'];
$session = $_GET['session'];
$class = $_GET['class'];
$section = $_GET['section'];

// subjects list
$q = mysqli_query($conn, "SELECT subject FROM subsetup 
    WHERE sccode='$sccode' 
    AND slot='$slot' 
    AND sessionyear='$session'
    AND classname='$class' 
    AND sectionname='$section'");

$data = [];

while ($r = mysqli_fetch_assoc($q)) {

    $code = $r['subject'];

    // subject name from subjects table
    $q2 = mysqli_query($conn, "SELECT subject 
        FROM subjects 
        WHERE (sccode='$sccode' OR sccode='0')
        AND sccategory='$sctype'
        AND subcode='$code'
        LIMIT 1");

    $subname = "";
    if ($s2 = mysqli_fetch_assoc($q2)) {
        $subname = $s2['subject'];
    }

    $label = $code . " - " . $subname;

    $data[] = [
        "value" => $code,
        "label" => $label
    ];
}

echo json_encode($data);
