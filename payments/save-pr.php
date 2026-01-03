<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$count = $_POST['count'];

$stid = $_POST['stid'];
$prdate = $_POST['prdate'];
$prno = $_POST['prno'];
$sy = $_POST['year'];

$sql00 = "SELECT * FROM sessioninfo where  sccode='$sccode' and stid='$stid' and sessionyear LIKE '%$sy%'";
$result00g = $conn->query($sql00);
if ($result00g->num_rows > 0) {
    while ($row00 = $result00g->fetch_assoc()) {
        $rollno = $row00['rollno'];
        $cls = $row00['classname'];
        $sec = $row00['sectionname'];
        $sy = $row00['sessionyear'];
    }
}

$sql00 = "SELECT * FROM students where  sccode='$sccode' and stid='$stid'";
$result00d = $conn->query($sql00);
if ($result00d->num_rows > 0) {
    while ($row00 = $result00d->fetch_assoc()) {
        $neng = $row00['stnameeng'];
        $nben = $row00['stnameben'];
        $mobile = $row00['guarmobile'];
    }
}



$tamt = 0;
for ($lp = 0; $lp < $count; $lp++) {
    $fid = $_POST['fid' . $lp];
    $amt = $_POST['amt' . $lp];

    $sql0r = "SELECT * FROM stfinance where id='$fid' ";
    $result0r = $conn->query($sql0r);
    if ($result0r->num_rows > 0) {
        while ($row0r = $result0r->fetch_assoc()) {
            $pr1 = $row0r["pr1"];
            $pr2 = $row0r["pr2"];
        }
    }
    if ($pr1 > 0) {
        $fld = 'pr2';
        $flddt = 'pr2date';
        $fldby = 'pr2by';
        $fldno = 'pr2no';
    } else {
        $fld = 'pr1';
        $flddt = 'pr1date';
        $fldby = 'pr1by';
        $fldno = 'pr1no';
    }
    $query3g = "update stfinance set $fld='$amt', $fldno='$prno', $flddt='$prdate', $fldby='$usr', paid=paid+'$amt', dues=dues-'$amt' where id='$fid';";
    $conn->query($query3g);
    // echo $query3g;
    $tamt = $tamt + $amt;
}

$smstxt = '';
$smscnt = 0;
$st = 0;
$stval = '';

$query33 = "insert into stpr(id, sessionyear, sccode, classname, sectionname, stid, rollno, prno, prdate, partid, amount, entryby, entrytime, smstxt, smscnt, mobileno, smsstatus, statusvalue)
    VALUES (NULL, '$sy', '$sccode', '$cls', '$sec', '$stid', '$rollno', '$prno', '$prdate', '', '$tamt', '$usr', '$cur', '$smstxt', '$smscnt', '$mobile', '$st', '$stval' );";
$conn->query($query33);
// echo $query33;

$query3x = "update sessioninfo set lastpr='$prno' where stid='$stid' and sessionyear LIKE '%$sy%';";
// echo $query3x;
$conn->query($query3x);

// SEnd Message
// Message Table
// Log Table
// Notification Send
// Notification Table

?>

Received