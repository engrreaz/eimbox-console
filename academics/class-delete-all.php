<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$areaname = $_POST['areaname'];
$sccode   = $_SESSION['sccode'];

mysqli_query($conn,"
    DELETE FROM areas
    WHERE areaname='$areaname'
    AND sccode='$sccode'
");

echo json_encode(['status'=>'ok']);
