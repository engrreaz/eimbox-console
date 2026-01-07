<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
header('Content-Type: text/plain');

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : '');
$sy = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : '');

$fid = intval($_POST['fid']);
$aitemcode = $_POST['fitemcode'];
$cls = $_POST['class'] ?? '';
$sec = $_POST['section'] ?? '';
$amt = floatval($_POST['amount']);
$spl = $_POST['spl'];

// Check existing
$q = $conn->query("SELECT id FROM financesetupvalue WHERE itemcode='$aitemcode' AND classname='$cls' AND sectionname='$sec' AND sessionyear='$sy' AND slot='$slot' AND sccode='$sccode'");
if ($q->num_rows) {
    $id = $q->fetch_assoc()['id'];
    $upd = $conn->query("UPDATE financesetupvalue SET amount='$amt', splitable='$spl', modifieddate='$cur' WHERE id='$id' and itemcode='$aitemcode'");
    if ($upd)
        echo "success: Amount updated";
    else
        echo "error: Failed to update";
} else {
    $ins = $conn->query("INSERT INTO financesetupvalue(classname,sectionname,amount, sessionyear, slot, sccode, itemcode, splitable, modifieddate) VALUES('$cls','$sec','$amt', '$sy', '$slot', '$sccode', '$aitemcode', '$spl', '$cur')");

    if ($ins)
        echo "success: Amount saved";
    else
        echo "error: Failed to save";
}