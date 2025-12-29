<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$sy = $SY;

$id = $_POST['id'];
$tail = $_POST['tail'];
$eng = $_POST['eng'];
$ben = $_POST['ben'];
$month = $_POST['month'];
$inin = $_POST['inin'];
$newt = $_POST['newt'];
$splt = $_POST['splt'];
$exex = $_POST['exex'];
$slot = '';
if ($inin == 'true') {
    $inin = 1;
} else {
    $inin = 0;
}
if ($exex == 'true') {
    $exex = 1;
} else {
    $exex = 0;
}
if ($newt == 'true') {
    $newt = 1;
} else {
    $newt = 0;
}
if ($splt == 'true') {
    $splt = 1;
} else {
    $splt = 0;
}

if ($tail == 1) {
    if ($id == 0) {

        $icode = uniqid();

        $sql0 = "SELECT slno FROM financesetup where sccode = '$sccode' order by slno desc LIMIT 1;";
        $result0rtz = $conn->query($sql0);
        if ($result0rtz->num_rows > 0) {
            while ($row0 = $result0rtz->fetch_assoc()) {
                $slno = $row0["slno"] + 1;
            }
        }

        $query331 = "INSERT INTO financesetup (id, sccode, slot, sessionyear, slno, itemcode, particulareng, particularben, month, inexin, inexex, new_only, splitable) 
                    values (NULL, '$sccode', '$slot', '$sy', '$slno', '$icode', '$eng', '$ben', '$month', '$inin', '$exex', '$newt', 'splt' );";
        $conn->query($query331);
    } else {


        $sql0 = "SELECT itemcode FROM financesetup where sccode = '$sccode' and id='$id' LIMIT 1;";
        $result0rtzt = $conn->query($sql0);
        if ($result0rtzt->num_rows > 0) {
            while ($row0 = $result0rtzt->fetch_assoc()) {
                $icode = $row0["itemcode"];
            }
        }

        $query331 = "UPDATE financesetup set particulareng = '$eng', particularben = '$ben', month = '$month', inexin = '$inin', inexex = '$exex', new_only='$newt', splitable='$splt' where id='$id' and sccode = '$sccode';";
        $conn->query($query331);
    }

    // echo $query331;

    $query331t = "UPDATE financesetupvalue set new_only = '$newt', splitable='$splt'  where itemcode='$icode' and sccode = '$sccode';";
    $conn->query($query331t);


} else if ($tail == 5) {

    $defitemlist = array();
    $sql0 = "SELECT * FROM financesetup where sccode = '0' and sessionyear = '$sy' order by slno ;";
    $result0rtzdd = $conn->query($sql0);
    if ($result0rtzdd->num_rows > 0) {
        while ($row0 = $result0rtzdd->fetch_assoc()) {
            $defitemlist[] = $row0;
        }
    }
    // echo var_dump($defitemlist);

    $sql0 = "SELECT slno FROM financesetup where sccode = '$sccode' order by slno desc LIMIT 1;";
    $result0rtz = $conn->query($sql0);
    if ($result0rtz->num_rows > 0) {
        while ($row0 = $result0rtz->fetch_assoc()) {
            $slno = $row0["slno"] + 1;
        }
    } else {
        $slno = 1;
    }

    foreach ($defitemlist as $item) {

        $icode = uniqid();
        $slot = 'School';
        $eng = $item['particulareng'];
        $ben = $item['particularben'];
        $month = $item['month'];
        $inin = $item['inexin'];
        $exex = $item['inexex'];


        $query331p = "INSERT INTO financesetup (id, sccode, slot, sessionyear, slno, itemcode, particulareng, particularben, month, inexin, inexex, modifieddate) 
                values (NULL, '$sccode', '$slot', '$sy', '$slno', '$icode', '$eng', '$ben', '$month', '$inin', '$exex', '$cur' );";
        $conn->query($query331p);
        $slno++;
    }

} else if ($tail == 10) {
    // echo $tail = $_POST['arr'];


    $new_sl = $_POST['arr'];
    $arr = explode(',', $new_sl);
    $cntrow = count($arr);
    for ($a = 0; $a < $cntrow; $a++) {
        $idno = $arr[$a];
        if ($idno > 0) {
            $query331 = "update financesetup set slno='$a'  where id = '$idno' and sessionyear='$SY' and sccode='$sccode'";
            // echo $query331;
            $conn->query($query331);
        }
    }




} else {
    $query331 = "DELETE from financesetup  where id='$id' and sccode = '$sccode';";
    $conn->query($query331);
}



echo 'Data Processing Successfully';