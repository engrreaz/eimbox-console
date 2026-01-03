<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

  
$id = $_POST['id'];
$taka = $_POST['taka'];
$tail = $_POST['tail'];
if ($tail == 1) {
    $query3g = "update stfinance set pr1=0, pr1no=NULL, pr1date=NULL, pr1by=NULL, pr2=0, pr2no=NULL, pr2date=NULL, pr2by=NULL, paid=paid-$taka, dues=dues+$taka where id='$id' and sccode='$sccode'; ";
    $conn->query($query3g);
} else {

    $query3gt = "INSERT INTO stfinance (sccode, sessionyear, classname, sectionname, stid, rollno, partid, itemcode, particulareng, particularben, amount, month, payableamt, paid, dues, pr1, pr1no, pr1date, pr1by) select sccode, sessionyear, classname, sectionname, stid, rollno, partid, itemcode, particulareng, particularben, '$taka', month, '$taka', '$taka', 0, '$taka', pr2no, pr2date, pr2by from stfinance where id = '$id' and sccode='$sccode'; ";
    $conn->query($query3gt);

    $query3g = "update stfinance set pr2=0, pr2no=NULL, pr2date=NULL, pr2by=NULL, dues=0 where id='$id' and sccode='$sccode'; ";
    $conn->query($query3g);

}
// echo $query3g. '<br>';

echo '<i class="bi bi-check-circle text-success"></i>';