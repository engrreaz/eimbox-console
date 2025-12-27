<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$ids = $_POST['ids'] ?? [];

$i=1;
foreach($ids as $id){
    mysqli_query($conn,"
        UPDATE areas SET idno='$i' WHERE id='$id'
    ");
    $i++;
}
