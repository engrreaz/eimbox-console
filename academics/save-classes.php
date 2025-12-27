<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = $_SESSION['sccode'];
$user   = $_SESSION['user'] ?? 'system';

$session = $_POST['sessionyear'];
$class   = trim($_POST['areaname']);
$section = trim($_POST['subarea']);

if(!$class || !$section){
    echo json_encode(['status'=>'err','msg'=>'Missing data']);
    exit;
}

/* next idno */
$q = mysqli_query($conn,"
    SELECT IFNULL(MAX(idno),0)+1 as nxt
    FROM areas
    WHERE sccode='$sccode'
");
$nxt = mysqli_fetch_assoc($q)['nxt'];

mysqli_query($conn,"
INSERT INTO areas
SET
    sccode='$sccode',
    user='$user',
    sessionyear='$session',
    areaname='$class',
    subarea='$section',
    idno='$nxt'
");

echo json_encode(['status'=>'ok']);
