<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = $_SESSION['sccode'];
$order  = $_POST['order'] ?? [];

foreach($order as $row){

    $id   = intval($row['id']);
    $idno = intval($row['idno']);

    mysqli_query($conn,"
        UPDATE areas
        SET idno='$idno'
        WHERE id='$id'
        AND sccode='$sccode'
    ");
}
