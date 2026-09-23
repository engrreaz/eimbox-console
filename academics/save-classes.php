<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$mode = $_POST['mode'] ?? '';
$id = intval($_POST['id'] ?? 0);

$slot = trim($_POST['slot'] ?? '');
$session = trim($_POST['sessionyear'] ?? '');
$class = trim($_POST['areaname'] ?? '');
$section = trim($_POST['subarea'] ?? '');
$tid = trim($_POST['teacher'] ?? '');

if (!$class || !$section) {
    echo json_encode(['status' => 'err', 'msg' => 'Missing class or section name']);
    exit;
}

$tid_val = (!empty($tid) && $tid !== '0') ? $conn->real_escape_string($tid) : '0';
$slot = $conn->real_escape_string($slot);
$session = $conn->real_escape_string($session);
$class = $conn->real_escape_string($class);
$section = $conn->real_escape_string($section);

if ($mode == 'add') {
    $q = mysqli_query($conn, "SELECT IFNULL(MAX(idno),0)+1 as nxt FROM areas WHERE sccode='$sccode'");
    if (!$q) {
        echo json_encode(['status' => 'err', 'msg' => 'Database error']);
        exit;
    }
    $nxt = mysqli_fetch_assoc($q)['nxt'];

    $result = mysqli_query($conn, "INSERT INTO areas (sccode, user, sessionyear, slot, areaname, subarea, classteacher, idno) VALUES ('$sccode', '$username', '$session', '$slot', '$class', '$section', '$tid_val', '$nxt')");
    if (!$result) {
        echo json_encode(['status' => 'err', 'msg' => 'Insert failed: ' . mysqli_error($conn)]);
        exit;
    }
} else {
    $result = mysqli_query($conn, "UPDATE areas SET sessionyear='$session', slot='$slot', areaname='$class', subarea='$section', classteacher='$tid_val', user='$username' WHERE id='$id' AND sccode='$sccode'");
    if (!$result) {
        echo json_encode(['status' => 'err', 'msg' => 'Update failed: ' . mysqli_error($conn)]);
        exit;
    }
}

echo json_encode(['status' => 'ok']);
?>