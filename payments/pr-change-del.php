<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';



$p1 = $_POST['p1'];   //prno
$p2 = $_POST['p2'];     // tail
$date = $_POST['date'];     // date

if ($p2 == 1) {
    $query3g = "update stfinance set pr1date='$date' where sccode='$sccode' and pr1no='$p1';";
    $conn->query($query3g);
    $query3gx = "update stpr set prdate='$date' where sccode='$sccode' and prno='$p1';";
    $conn->query($query3gx);
} else if ($p2 == 2) {
    $query3g = "update stfinance set dues=paid, paid=0, pr1=0, pr1no='', pr1date=NULL, pr1by=NULL where sccode='$sccode' and pr1no='$p1' and pr1date='$date';";
    echo $query3g . '///';
    $conn->query($query3g);
    $query3gx = "DELETE FROM  stpr  where sccode='$sccode' and prno='$p1' and prdate='$date';";
    $conn->query($query3gx);
} else if ($p2 == 3) {
    // echo $p1 . '/' . $p2 . '/' . $date;

    $newpr = $p1 + 1;

    $query3g = "update stfinance set pr1no='$newpr' where sccode='$sccode' and pr1no='$p1' and pr1date='$date';";
    $conn->query($query3g);
    $query3gx = "update stpr set prno='$newpr' where sccode='$sccode' and prno='$p1' and prdate='$date';";
    $conn->query($query3gx);

    echo $query3g . '//' . $query3gx;

} else if ($p2 == 5) {
    // Mismatch PRNO.


    $sql0x = "SELECT * FROM stpr where prno='$p1' and sccode='$sccode' and prdate='$date';";
    // echo $sql0x;
    $result0x = $conn->query($sql0x);
    if ($result0x->num_rows > 0) {
        while ($row0x = $result0x->fetch_assoc()) {
            $stid = $row0x["stid"];


            $stid_digit = substr($stid, 6, 4);
            $sql0x = "SELECT * FROM stpr where stid='$stid' and sccode='$sccode' and prno like '%$stid_digit%' order by prno desc LIMIT 1;";
            // echo $sql0x;
            $result0xx = $conn->query($sql0x);
            if ($result0xx->num_rows == 1) {
                while ($row0x = $result0xx->fetch_assoc()) {
                    $lastpr = $row0x["prno"];
                }
            } else {
                $lastpr = $sy * 10000 + $stid_digit;
            }

            $newpr = $lastpr + 1;

            $query3g = "update stfinance set pr1no='$newpr' where sccode='$sccode' and pr1no='$p1' and pr1date='$date' and stid='$stid';";
            $conn->query($query3g);
            $query3gx = "update stpr set prno='$newpr' where sccode='$sccode' and prno='$p1' and prdate='$date' and stid='$stid';";
            $conn->query($query3gx);

            // echo $query3g . '//' . $query3gx;
        }
    }
    // echo $stid;



}
// echo $dtx;
// echo 'Not available now.';