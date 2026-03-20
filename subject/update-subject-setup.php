<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_POST['id']);

$ctest = intval($_POST['ctest']);
$mtest = intval($_POST['mtest']);
$subj = intval($_POST['subj']);
$obj = intval($_POST['obj']);
$pra = intval($_POST['pra']);
$ca = intval($_POST['ca']);
$full = intval($_POST['full']);
$pass = intval($_POST['pass']);
$fourth = intval($_POST['fourth']);

$c1 = intval($_POST['c1']);
$c2 = intval($_POST['c2']);
$c3 = intval($_POST['c3']);
$c4 = intval($_POST['c4']);

$sql = "UPDATE subsetup SET
    ctest='$ctest',
    mtest='$mtest',
    subj='$subj',
    obj='$obj',
    pra='$pra',
    ca='$ca',
    fullmarks='$full',
    pass_algorithm='$pass',
    fourth='$fourth',
    combind_1='$c1',
    combind_2='$c2',
    combind_3='$c3',
    combind_4='$c4'
WHERE id='$id'";

echo $conn->query($sql) ? 'OK' : $conn->error;