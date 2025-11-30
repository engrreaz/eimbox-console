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

$ct = $_POST['ct'];
$mt = $_POST['mt'];
$sub = $_POST['sub'];
$obj = $_POST['obj'];
$pra = $_POST['pra'];
$ca = $_POST['ca'];
$total = $_POST['total'];
$alg = $_POST['alg'];

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
}



// delete old if exists
$chk = "DELETE FROM stmark WHERE sccode='$sccode' AND stid='$stid' AND sessionyear='$session' 
        AND exam='$exam' AND subject='$subject' AND classname='$class' AND sectionname='$section'";
mysqli_query($conn, $chk);


$p = pass_validation($ct, $mt, $sub, $obj, $pra, $ca, $sub_full, $obj_full, $pra_full, $full_full, $alg);

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
        '$full_full', '$ct', '$mt', '$sub', '$obj', '$pra', '$ca', 0, 0, 0, 0, 0, '$gp', '$gl', '$cur', '$usr', '$cur')";

// echo $ins;
mysqli_query($conn, $ins);

echo "$gp/$gl";
echo $gp . '/' . $gl;


