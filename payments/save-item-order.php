<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : '';
$session = isset($_COOKIE['session']) ? $_COOKIE['session'] : '';

$order = explode(',', $_POST['order']);

foreach($order as $row){
    list($id,$sl) = explode('=',$row);
    $conn->query("
        UPDATE financesetup 
        SET slno='".intval($sl)."'
        WHERE id='".intval($id)."'
          AND sccode='$sccode'
          AND sessionyear='$session'
    ");
}

echo "Order Saved Successfully";