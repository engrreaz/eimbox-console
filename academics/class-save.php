<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$sccode = $_SESSION['sccode'];
$user   = $_SESSION['user'] ?? 'system';

$mode    = $_POST['mode'];
$session = $_POST['sessionyear'];
$class   = trim($_POST['areaname']);
$section = trim($_POST['subarea']);

if($mode === 'edit'){

    $id = intval($_POST['id']);

    mysqli_query($conn,"
        UPDATE areas SET
            sessionyear='$session',
            areaname='$class',
            subarea='$section',
            modifieddate=NOW()
        WHERE id='$id'
        AND sccode='$sccode'
    ");

    echo json_encode(['status'=>'ok']);
    exit;
}

/* add mode stays same as Step–1 */
