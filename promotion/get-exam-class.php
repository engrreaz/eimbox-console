<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot    = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';

$examOpt = '<option value="">Select Exam</option>';
$classOpt = '<option value="">Select Class</option>';

$q1 = mysqli_query($conn,"
    SELECT DISTINCT exam 
    FROM tabulatingsheet 
    WHERE slot='$slot' AND sessionyear='$session' AND sccode='$sccode'
");

while($r=mysqli_fetch_assoc($q1)){
    $examOpt .= "<option value='{$r['exam']}'>{$r['exam']}</option>";
}

$q2 = mysqli_query($conn,"
    SELECT DISTINCT classname 
    FROM tabulatingsheet 
    WHERE slot='$slot' AND sessionyear='$session' AND sccode='$sccode'
");

while($r=mysqli_fetch_assoc($q2)){
    $classOpt .= "<option value='{$r['classname']}'>{$r['classname']}</option>";
}

echo json_encode([
    'exam' => $examOpt,
    'classname' => $classOpt
]);
