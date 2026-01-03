<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';



$id = $_POST['fid'];
$amt = $_POST['amt'];
$tail = (int) $_POST['tail'];

$stid = '100';

if ($tail == 1) {



  $sql = "INSERT INTO stfinance 
            SELECT NULL, sccode, sessionyear, classname, sectionname, stid, rollno, partid, itemcode, particulareng, particularben, amount, month, idmon, '$cur', '$usr', payableamt, '$cur', '$usr', paid, paidx, dues, pr1, pr1no, pr1date, pr1by, cashbook1, pr2, pr2no, pr2date, pr2by, cashbook2, remark, extra, last_update, validate, validationtime,  deleteby, deletetime, splitid, scan_status
            FROM stfinance 
            WHERE id = $id and sccode='$sccode'";


  $conn->query($sql);
  $newId = $conn->insert_id;

  $q2 = $conn->query("SELECT stid FROM stfinance WHERE id=$newId LIMIT 1");
  $r2 = $q2->fetch_assoc();
  $stid = $r2['stid'];

  echo "New copied row ID = " . $newId;

  $query331 = "UPDATE stfinance set payableamt='$amt', dues='$amt', splitid='$newId' where id = '$id' and sccode='$sccode'";
  $conn->query($query331);
  $query331 = "UPDATE stfinance set payableamt= payableamt-$amt, dues=dues-$amt, splitid=NULL where id = '$newId' and sccode='$sccode'";
  $conn->query($query331);

  echo '<div id="fin-stid">' . $stid . '</div>';

} else if ($tail == 2) {
  $splitid = $amt;
  $connectid = 0;
  $stid = 0;
  $sql5 = "SELECT * FROM stfinance where  sccode='$sccode' and id='$id'  ";
  // echo $sql5;
  $result5 = $conn->query($sql5);
  if ($result5->num_rows > 0) {
    while ($row5 = $result5->fetch_assoc()) {
      $connectid = $row5["splitid"];
      $stid = $row5["stid"];
    }
  }

  if ($connectid == 0) {


  } else {
    $query331r = "UPDATE stfinance t
          JOIN (
              SELECT 
                  SUM(payableamt) AS total_payable,
                  SUM(dues) AS total_dues
              FROM stfinance
              WHERE id IN ($id, $splitid)
              AND sccode = '$sccode'
          ) s ON t.id = $id AND t.sccode = '$sccode'
          SET 
              t.payableamt = s.total_payable,
              t.dues = s.total_dues,
              t.splitid = NULL;;";

    // echo $query331r;

    $conn->query($query331r);
    $query331x = "DELETE FROM stfinance  where id = '$splitid' and sccode='$sccode'";
    echo $query331x;
    $conn->query($query331x);


  }

  echo '<div id="fin-stid-merge">' . $stid . '</div>';


} else {
  $stid = 'KUTTA';
}

