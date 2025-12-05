<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/core-val.php';

$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];

echo $exam;

// ---------------------------
// 0. subsetup থেকে distribution আনবো
// ---------------------------
$q_str = "SELECT * FROM subsetup 
          WHERE sccode='$sccode' 
          AND sessionyear='$session' 
          AND classname='$class' 
          AND sectionname='$section' 
          AND subject='$subject' 
          AND slot='$slot' 
          LIMIT 1";

$qsub = mysqli_query($conn, $q_str);
$setup = mysqli_fetch_assoc($qsub);

// যদি না পায়, সবখাত enabled থাকবে
$ct_max = $setup['ctest'] ?? 0;
$mt_max = $setup['mtest'] ?? 0;
$sub_max = $setup['subj'] ?? 0;
$obj_max = $setup['obj'] ?? 0;
$pra_max = $setup['pra'] ?? 0;
$ca_max = $setup['ca'] ?? 0;
$alg = $setup['pass_algorithm'] ?? 0;



// ---------------------------
// 1. sessioninfo থেকে রোল + stid আনবো
// ---------------------------
$q1 = "SELECT rollno, stid 
       FROM sessioninfo 
       WHERE sccode='$sccode' 
       AND sessionyear='$session'
       AND classname='$class'
       AND sectionname='$section'
       AND slot='$slot'
       ORDER BY rollno ASC";

$r1 = mysqli_query($conn, $q1);

$roll_list = [];
$stid_list = [];

while ($row = mysqli_fetch_assoc($r1)) {
    $roll = $row['rollno'];
    $stid = $row['stid'];
    $roll_list[$stid] = $roll;
    $stid_list[] = $stid;
}

if (count($stid_list) == 0) {
    echo "<p class='text-danger'>No students found!</p>";
    exit;
}

// স্টুডেন্ট আইডিগুলোকে CSV বানানো
$stid_csv = implode(",", $stid_list);


// ---------------------------
// 2. students টেবিল থেকে bulk student info আনবো
// ---------------------------
$q2 = "SELECT stid, stnameeng, stnameben 
       FROM students 
       WHERE stid IN ($stid_csv)";

$r2 = mysqli_query($conn, $q2);

$students = [];
while ($row = mysqli_fetch_assoc($r2)) {
    $students[$row['stid']] = $row;
}

// var_dump($stid_csv);
// ---------------------------
// 3. stmark টেবিল থেকে bulk marks আনবো
// ---------------------------
$q3 = "SELECT * 
       FROM stmark
       WHERE sccode='$sccode'
       AND sessionyear='$session'
       AND exam='$exam'
       AND subject='$subject'
       AND classname='$class'
       AND sectionname='$section'
       AND stid IN ($stid_csv)";

$r3 = mysqli_query($conn, $q3);

$marks = [];
while ($m = mysqli_fetch_assoc($r3)) {
    $marks[$m['stid']] = $m;
}

// var_dump($marks[1031870001]);
// ---------------------------
// 4. এখন এক লুপে সব টেবিল তৈরি
// ---------------------------

echo '
<table class="table table-responsive table-sm table-striped">
    <thead>
        <tr>
        <th style="width:10px;"></th>
            <th colspan="2">Roll</th>
            <th>Name</th>
            <th>C Test</th>
            <th>m Test</th>
            <th>Subj</th>
            <th>Obj</th>
            <th>Pra</th>
            <th>CA.LA</th>
            <th>Total</th>
            <th>GP/GL</th>
            <th style="width:10px;"></th>
        </tr>
    </thead>
    <tbody>
';

// $sub_max = 70;
foreach ($stid_list as $stid) {

    $roll = $roll_list[$stid];
    $name = isset($students[$stid]) ? $students[$stid]['stnameeng'] : $stid;

    $ct_dis = ($ct_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";
    $mt_dis = ($mt_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";
    $sub_dis = ($sub_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";
    $obj_dis = ($obj_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";
    $pra_dis = ($pra_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";
    $ca_dis = ($ca_max == 0 || strtolower($exam) == 'grand') ? "disabled" : "";


    // যদি mark পাওয়া যায়
    $mq = isset($marks[$stid]) ? $marks[$stid] : null;

    $sobi = dirname(dirname(__DIR__)) . '/students/' . $stid . '.jpg';
    if (!file_exists($sobi)) {
        $sobi = BASE_PATH . 'students/noimg.jpg';
    } else {
        $sobi = BASE_PATH . 'students/' . $stid . '.jpg';
    }

    echo '<tr>';

    echo '<td class="p-1"></td>';


    echo '<td class="p-1"><img src="' . $sobi . '" style="height:25px; border-radius: 2px;"/></td>';
    echo '<td class="p-1">' . $roll . '</td>';
    echo '<td class="p-1">' . $name . '</td>';

    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark ct' data-stid='$stid' value='" . ($mq['ctest'] ?? '') . "' $ct_dis></td>";
    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark mt' data-stid='$stid' value='" . ($mq['mtest'] ?? '') . "' $mt_dis></td>";
    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark sub' data-stid='$stid' value='" . ($mq['subj'] ?? '') . "' $sub_dis></td>";
    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark obj' data-stid='$stid' value='" . ($mq['obj'] ?? '') . "' $obj_dis></td>";
    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark pra' data-stid='$stid' value='" . ($mq['pra'] ?? '') . "' $pra_dis></td>";
    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center mark ca' data-stid='$stid' value='" . ($mq['ca'] ?? '') . "' $ca_dis></td>";

    echo "<td class='p-1'><input type='number' class='form-control form-control-sm p-0 text-center total' data-stid='$stid' value='" . ($mq['markobt'] ?? '') . "' disabled></td>";

    echo "<td class='p-1 text-center' id='gpgl_$stid'>" . ($mq['gp'] ?? "-") . "/" . ($mq['gl'] ?? "-") . "</td>";
    echo '<td class="p-1"></td>';
    echo '</tr>';
}

echo '</tbody></table>';


