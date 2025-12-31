<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


// 0 == Add new (Expenditute), 1 == Edit, 2 == Delete, 3 == Set Memo No., 4 == ........, 5 == Add New (Income)

$id = $_POST['rowid'];
$slot = $_POST['slot'];
$sy = $_POST['session'];
$item = $_POST['itemcode'];
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$amt = $_POST['amount'];
$tag = $_POST['tag'];
$stid = $_POST['stid'];
// $indid = $_POST['indid'];


if ($tag == 'IND') {
    if ($id == 0) {
        $query331 = "INSERT INTO financesetupind (id, sccode, slot, sessionyear, stid, slno, itemcode, classname, sectionname, amount, update_time) 
    values (NULL, '$sccode',  '$slot',  '$sy', '$stid', '',  '$item',  '$cls',  '$sec',  '$amt',  '$cur');";
    } else {
        $query331 = "UPDATE financesetupind set amount = '$amt', update_time = '$cur' where id='$id' and sccode = '$sccode' and stid='$stid';";
    }
} else {
    $query331 = "INSERT INTO financesetupind (id, sccode, slot, sessionyear, stid, slno, itemcode, classname, sectionname, amount, update_time) 
    values (NULL, '$sccode',  '$slot',  '$sy', '$stid', '',  '$item',  '$cls',  '$sec',  '$amt',  '$cur');";
}

echo $query331;
$conn->query($query331);
