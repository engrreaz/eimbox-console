<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$order  = $_POST['order'] ?? [];

foreach($order as $row){

    $areaname = $row['areaname'];
    $slot     = $row['slot'];
    $session  = $row['session'];
    $idno     = intval($row['idno']);

    mysqli_query($conn,"
        UPDATE areas
        SET idno='$idno'
        WHERE sccode='$sccode'
        AND areaname='$areaname'
        AND slot='$slot'
        AND sessionyear='$session'
    ");
}
