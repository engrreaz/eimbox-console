<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// ----------------------
// Return STID as plain text
// ----------------------

$sccode = $sccode ?? 0;

// Last STID Query
$sql = "SELECT stid FROM students WHERE sccode='$sccode' ORDER BY stid DESC LIMIT 1";
$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $newStid = $row['stid'] + 1;
} else {
    // First student of this institute
    $newStid = ($sccode * 10000) + 1;
}

echo $newStid;
exit;
