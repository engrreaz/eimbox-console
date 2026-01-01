<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';
$exam = $_POST['exam'] ?? '';
$classname = $_POST['classname'] ?? '';

$out = '<option value=""></option>';

$q = mysqli_query($conn, "
    SELECT DISTINCT sectionname 
    FROM tabulatingsheet 
    WHERE slot='$slot'
    AND sessionyear='$session'
    AND exam='$exam'
    AND classname='$classname'
    AND sccode='$sccode'
");

while ($r = mysqli_fetch_assoc($q)) {
    $out .= "<option value='{$r['sectionname']}'>{$r['sectionname']}</option>";
}

echo $out;
