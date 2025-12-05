<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';



$id = $_POST['id'];
$mode = $_POST['mode'];
$slot = $_POST['slot'];

$min = $_POST['minv'];
$max = $_POST['maxv'];
$gp = $_POST['gp'];
$gl = $_POST['gl'];
$remark = $_POST['remark'];
// $color = toHexColor($_POST['color']);
$color = str_replace('#', '', $_POST['color']);

$base_sccode = $_POST['base_sccode'];

if ($base_sccode == 0) {
    $mode = 'add';
}

if ($mode == "add") {
    $sql = "INSERT INTO gpa(sccode,slot,minvalues,maxvalues,gp,gl,remark,colorcode)
            VALUES('$sccode','$slot','$min','$max','$gp','$gl','$remark','$color')";
    mysqli_query($conn, $sql);
} else if ($mode == "edit") {
    // normal update
    $sql = "UPDATE gpa SET 
                minvalues='$min', maxvalues='$max', gp='$gp', gl='$gl',
                remark='$remark', colorcode='$color'
                WHERE id='$id'";

    mysqli_query($conn, $sql);
}

echo "OK";
