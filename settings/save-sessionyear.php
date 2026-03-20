<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$years = $_POST['years'] ?? [];

// loop করে update/insert
foreach ($years as $y) {
    $year = $conn->real_escape_string($y['year']);
    $active = (int) $y['active'];

    // check if exists
    $chk = $conn->query("SELECT id FROM sessionyear WHERE sccode='$sccode' AND syear='$year' LIMIT 1");


    if ($chk && $chk->num_rows > 0) {
        $ddl = "UPDATE sessionyear SET active='$active' WHERE sccode='$sccode' AND syear='$year'";
    } else {
        $ddl = "INSERT INTO sessionyear (sccode, syear, active) VALUES ('$sccode', '$year', '$active')";
    }
    // echo $ddl;
    $conn->query($ddl);
}

echo json_encode(['status' => 'success']);
