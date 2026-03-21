<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$stid = $_POST['stid'];
$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];

$ct = (float) $_POST['ct'] ?? 0;
$mt = (float) $_POST['mt'] ?? 0;
$sub = (float) $_POST['sub'] ?? 0;
$obj = (float) $_POST['obj'] ?? 0;
$pra = (float) $_POST['pra'] ?? 0;
$ca = (float) $_POST['ca'] ?? 0;
$total = (float) $_POST['total'] ?? 0;
$alg = (int) $_POST['alg'] ?? 0;


$sql = "SELECT maxvalues FROM gpa 
        WHERE (sccode='$sccode' OR sccode = '0') 
        AND (slot IS NULL OR slot = '$slot')
        AND gp=0
        ORDER BY  sccode DESC, slot DESC LIMIT 1";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$min = $row['maxvalues'] ?? null;
$min = floor($min) + 1;


$sqly = "SELECT decimal_mark FROM slots   WHERE sccode='$sccode'    AND slotname = '$slot'  LIMIT 1";
$resy = mysqli_query($conn, $sqly);
$rowy = mysqli_fetch_assoc($resy);
$decimal = $rowy['decimal_mark'] ?? 0;


$q_str = "SELECT * FROM subsetup 
          WHERE sccode='$sccode' 
          AND sessionyear='$session' 
          AND classname='$class' 
          AND sectionname='$section' 
          AND subject='$subject' 
          AND slot='$slot' 
          LIMIT 1";

$q = mysqli_query($conn, $q_str);

if (mysqli_num_rows($q) > 0) {

    $row = mysqli_fetch_assoc($q);
    $sub_full = $row['subj'];
    $obj_full = $row['obj'];
    $pra_full = $row['pra'];
    $full_full = $row['fullmarks'];

} else {
    $sub_full = 0;
    $obj_full = 0;
    $pra_full = 0;
    $full_full = 0;
}

$on100 = 0;
if ($total > 0) {
    $on100 = $total * 100 / $full_full;
}

// echo $sub_full . '-' . $obj_full . '-' . $pra_full . '-' . $full_full . '***' ;
// delete old if exists
$chk = "DELETE FROM stmark WHERE sccode='$sccode' AND stid='$stid' AND sessionyear='$session' 
        AND exam='$exam' AND subject='$subject' AND classname='$class' AND sectionname='$section'";
mysqli_query($conn, $chk);

// echo $ct . '-' . $mt . '-' . $sub . '-' . $obj . '-' . $pra . '-' . $ca . '-' . $total . '***' ;
// echo $sub_full . '-' . $obj_full . '-' . $pra_full . '-' . $full_full . '***' ;
$p = pass_validation($ct, $mt, $sub, $obj, $pra, $ca, $sub_full, $obj_full, $pra_full, $full_full, $alg, $min, $decimal);


// echo $p;
if ($p === false || $p == 0) {

    $gp = 0;
    $gl = 'F';
} else {
    // echo 'check';
    $gpgl = get_GP_GL($total, $full_full);
    $gp = $gpgl['gp'];
    $gl = $gpgl['gl'];
}


// insert new row
$ins = "INSERT INTO stmark (slot, sccode, stid, sessionyear, exam, subject, classname, sectionname,
        fullmark, ctest, mtest, subj, obj, pra, ca, sub_final, obj_final, pra_final, markobt, on100, gp, gl, entrydate, entryby, modifieddate)
        VALUES ('$slot', '$sccode', '$stid', '$session', '$exam', '$subject', '$class', '$section',
        '$full_full', '$ct', '$mt', '$sub', '$obj', '$pra', '$ca', '$sub', '$obj', '$pra', '$total', '$on100', '$gp', '$gl', '$cur', '$usr', '$cur')";

// echo $ins;
mysqli_query($conn, $ins);

echo "<span class='text-success fw-bold'>$gp | $gl</span>";
echo '<i class="bi bi-check-circle-fill text-success fs-5 ps-3 "></i>';