<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$mode = $_POST['mode'] ?? '';
$id = $_POST['id'] ?? 0;

$slot = $_POST['slot'] ?? $sctype;
$session = $_POST['sessionyear'] ?? $y_v4;
$class = trim($_POST['areaname']) ?? '';
$section = trim($_POST['subarea']) ?? '';
$tid = trim($_POST['teacher']) ?? '';

header('Content-Type: application/json');


if (!$class || !$section) {
    echo json_encode(['status' => 'err', 'msg' => 'Missing data']);
    exit;
}

if ($mode == 'add') {
    $q = mysqli_query($conn, "SELECT IFNULL(MAX(idno),0)+1 as nxt FROM areas WHERE sccode='$sccode'");
    if (!$q) {
        echo json_encode(['status' => 'err', 'msg' => 'Database error']);
        exit;
    }
    $nxt = mysqli_fetch_assoc($q)['nxt'];

    $result = mysqli_query($conn, "INSERT INTO areas SET sccode='$sccode', user='$username', sessionyear='$session', areaname='$class', subarea='$section', classteacher='$tid', idno='$nxt'");
    if (!$result) {
        echo json_encode(['status' => 'err', 'msg' => 'Insert failed']);
        exit;
    }
} else {
    $result = mysqli_query($conn, "UPDATE areas SET sccode='$sccode', user='$username', sessionyear='$session', areaname='$class', subarea='$section', classteacher='$tid' where id='$id'");
    if (!$result) {
        echo json_encode(['status' => 'err', 'msg' => 'Update failed']);
        exit;
    }
}

echo json_encode(['status' => 'ok']);