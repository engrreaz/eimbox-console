<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = $_POST['id'] ?? '';
$mode = $_POST['mode'];


$sessionyear = $_POST['sessionyear'];
$slot = $_POST['slot'];
$examtitle = $_POST['examtitle'];
$examcode = $_POST['examcode'];
$classname = $_POST['classname'];
$sectionname = $_POST['sectionname'];
$datestart = !empty($_POST['datestart']) ? $_POST['datestart'] : $td;
$result_publish = !empty($_POST['result_publish']) ? $_POST['result_publish'] : $td;

$status = $_POST['status'];

if($mode=='add'){
    $examcode = uniqid();
    $stmt=$conn->prepare("INSERT INTO examlist
    (sccode,sessionyear,slot,examtitle,examcode,classname,sectionname,datestart,result_publish,status)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssssi",$sccode,$sessionyear,$slot,$examtitle,$examcode,$classname,$sectionname,$datestart,$result_publish,$status);
}
else{
    $stmt=$conn->prepare("UPDATE examlist SET
    sccode=?,sessionyear=?,slot=?,examtitle=?,examcode=?,classname=?,sectionname=?,datestart=?,result_publish=?,status=?
    WHERE id=?");
    $stmt->bind_param("issssssssii",$sccode,$sessionyear,$slot,$examtitle,$examcode,$classname,$sectionname,$datestart,$result_publish,$status,$id);
}

echo $stmt->execute() ? 'ok' : $conn->error;