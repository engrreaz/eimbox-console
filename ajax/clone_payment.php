<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$source = $_POST['source'] ?? '';
$target = $_POST['target'] ?? '';

if (!$source || !$target) {
    exit("<span class='text-danger'>Invalid source or target session!</span>");
}

$cur = date('Y-m-d H:i:s');

$mapping = []; // old itemcode => new itemcode

// --- Clone financesetup ---
$sql1 = "SELECT * FROM financesetup WHERE sccode='$sccode' AND sessionyear='$source'";
$res1 = $conn->query($sql1);

if ($res1->num_rows == 0) {
    exit("<span class='text-warning'>No finance setup found for source session!</span>");
}

while ($row = $res1->fetch_assoc()) {
    $newItem = uniqid();
    $mapping[$row['itemcode']] = $newItem;

    $ins = "INSERT INTO financesetup 
        (sccode, slot, sessionyear, slno, itemcode, particulareng, particularben, new_only, splitable, month, inexin, inexex, cheque, custom, modifieddate)
        VALUES (
            '$sccode',
            '".$row['slot']."',
            '$target',
            '".$row['slno']."',
            '$newItem',
            '".addslashes($row['particulareng'])."',
            '".addslashes($row['particularben'])."',
            '".$row['new_only']."',
            '".$row['splitable']."',
            '".$row['month']."',
            '".$row['inexin']."',
            '".$row['inexex']."',
            '".$row['cheque']."',
            '".$row['custom']."',
            '$cur'
        )";


    if (!$conn->query($ins)) {
        exit("<span class='text-danger'>Error cloning financesetup: ".$conn->error."</span>");
    }
}

// --- Clone financesetupvalue ---
$sql2 = "SELECT * FROM financesetupvalue WHERE sccode='$sccode' AND sessionyear='$source'";
$res2 = $conn->query($sql2);

while ($row = $res2->fetch_assoc()) {
    if (!isset($mapping[$row['itemcode']])) continue;
    $newItem = $mapping[$row['itemcode']];

    $ins2 = "INSERT INTO financesetupvalue
        (sccode, slot, sessionyear, slno, itemcode, new_only, splitable, classname, sectionname, amount, update_time, month, inexin, inexex, cheque, custom, last_update, modifieddate)
        VALUES (
            '$sccode',
            '".$row['slot']."',
            '$target',
            '".$row['slno']."',
            '$newItem',
            '".$row['new_only']."',
            '".$row['splitable']."',
            '".$row['classname']."',
            '".$row['sectionname']."',
            '".$row['amount']."',
            '$cur',
            '".$row['month']."',
            '".$row['inexin']."',
            '".$row['inexex']."',
            '".$row['cheque']."',
            '".$row['custom']."',
            '$cur',
            '$cur'
        )";



    if (!$conn->query($ins2)) {
        exit("<span class='text-danger'>Error cloning financesetupvalue: ".$conn->error."</span>");
    }
}

echo "<span class='text-success'>✅ Payment cloning completed from $source → $target</span>";
?>
