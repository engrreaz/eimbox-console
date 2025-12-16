<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

set_time_limit(0);
$data = '';

// POST variables
$slot = $_POST['slot'];
$session = $_POST['session'];
$offset = intval($_POST['offset'] ?? 0);
$batchSize = intval($_POST['batchSize'] ?? 1);
$cls = $_POST['classname'] ?? '';
$sec = $_POST['sectionname'] ?? '';
$subcode = $_POST['subcode'] ?? '';

// Selected exams and their rates from cookies
$selectedExams = [];
$examRates = [];
$data .= '<br>--------------------';
if (!empty($_COOKIE['examitems'])) {
    $selectedExams = explode(",", $_COOKIE['examitems']);
    foreach ($selectedExams as $ex) {
        $key = "rate_" . str_replace(" ", "_", $ex);
        $examRates[$ex] = isset($_COOKIE[$key]) ? floatval($_COOKIE[$key]) / 100 : 1;

        $data .= $ex . '//' . $key . '//' . $examRates[$ex] . ' *********** ';
    }
}

$data .= '--------------------<br>';
$examCount = count($selectedExams);

// Base WHERE for sessioninfo
$whereBase = "sessionyear='$session' AND slot='$slot' AND sccode='$sccode' AND grand_merged=0";
if ($cls !== '')
    $whereBase .= " AND classname='$cls'";
if ($sec !== '')
    $whereBase .= " AND sectionname='$sec'";

// Total students count
$q_total = "SELECT COUNT(*) AS cnt FROM sessioninfo WHERE $whereBase";
$res_total = mysqli_query($conn, $q_total);
$total = mysqli_fetch_assoc($res_total)['cnt'];

// Fetch batch of students
$q = "SELECT stid, classname, sectionname FROM sessioninfo WHERE $whereBase LIMIT $batchSize";
$res = mysqli_query($conn, $q);
$students = [];
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}

// Merge function per student
function mergeStudent($stid, $class, $section, $slot, $session, $sccode, $usr, $conn, $selectedExams, $examRates, $subcode, &$data)
{
    // Get subject setups
    $sublist = [];
    if ($subcode !== '') {
        $qsub = "SELECT * FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode'
                 AND classname='$class' AND sectionname='$section' AND subject='$subcode'";
    } else {
        $qsub = "SELECT * FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode'
                 AND classname='$class' AND sectionname='$section'";
    }
    $ressub = mysqli_query($conn, $qsub);
    while ($r = mysqli_fetch_assoc($ressub)) {
        $sublist[] = $r;
    }

    if ($subcode == '') {
        $sublist[] = [
            'subject' => 999
        ];
    }



    foreach ($sublist as $subrow) {
        $subject = $subrow['subject'];
        $subfinal = $subrow['subj'] * array_sum($examRates) ?? 0;
        $objfinal = $subrow['obj'] * array_sum($examRates) ?? 0;
        $prafinal = $subrow['pra'] * array_sum($examRates) ?? 0;
        $cafinal = $subrow['ca'] * array_sum($examRates) ?? 0;
        $totalfinal = $subrow['fullmarks'] * array_sum($examRates) ?? 0;
        $algfinal = $subrow['pass_algorithm'] ?? 0;

        // Delete old GRAND marks
        $resf = mysqli_query($conn, "SELECT id FROM stmark WHERE sessionyear='$session' AND sccode='$sccode' AND stid='$stid' AND exam='GRAND' AND subject='$subject'");
        while ($rowf = mysqli_fetch_assoc($resf)) {
            mysqli_query($conn, "DELETE FROM stmark WHERE id='{$rowf['id']}'");
        }

        // Sum marks from selected exams with their rate
        $m = [
            'ctest' => 0,
            'mtest' => 0,
            'subj' => 0,
            'obj' => 0,
            'pra' => 0,
            'ca' => 0,
            'sub_final' => 0,
            'obj_final' => 0,
            'pra_final' => 0,
            'markobt' => 0,
            'on100' => 0,
            'fullmark' => 0
        ];

        foreach ($selectedExams as $exam) {
            $rate = $examRates[$exam];
            $sq = "SELECT * FROM stmark WHERE sessionyear='$session' AND sccode='$sccode' AND stid='$stid' AND exam='" . mysqli_real_escape_string($conn, $exam) . "' AND subject='$subject'";
            $rq = mysqli_query($conn, $sq);
            if ($rq && mysqli_num_rows($rq) > 0) {
                $r = mysqli_fetch_assoc($rq);
                $m['ctest'] += $r['ctest'] * $rate;
                $m['mtest'] += $r['mtest'] * $rate;
                $m['subj'] += $r['subj'] * $rate;
                $m['obj'] += $r['obj'] * $rate;
                $m['pra'] += $r['pra'] * $rate;
                $m['ca'] += $r['ca'] * $rate;
                $m['sub_final'] += $r['sub_final'] * $rate;
                $m['obj_final'] += $r['obj_final'] * $rate;
                $m['pra_final'] += $r['pra_final'] * $rate;
                $m['markobt'] += $r['markobt'] * $rate;

                $m['fullmark'] += $r['fullmark'] * $rate;


            }
        }
        $m['on100'] = $m['markobt'] * 100 / $m['fullmark'];
        $data .= "Subject: $subject | markobt={$m['markobt']} | total=$totalfinal | {$m['on100']} <br>";

        // Pass/fail calculation
        $p = pass_validation($m['ctest'], $m['mtest'], $m['subj'], $m['obj'], $m['pra'], $m['ca'], $subfinal, $objfinal, $prafinal, $totalfinal, $algfinal, 36, 2);
        if ($p === false || $p == 0) {
            $gp = 0;
            $gl = 'F';
        } else {
            $gpgl = get_GP_GL($m['markobt'], $totalfinal);
            $gp = $gpgl['gp'];
            $gl = $gpgl['gl'];
        }

        // Insert GRAND marks
        $insert = "
            INSERT INTO stmark (
                slot, sessionyear, sccode, exam, classname, sectionname, subject, 
                stid, fullmark, 
                ctest, mtest, subj, obj, pra, ca,
                sub_final, obj_final, pra_final,
                markobt, on100, examtype, entryby, gp, gl
            ) VALUES (
                '$slot','$session','$sccode','GRAND','$class','$section','$subject',
                '$stid','{$totalfinal}',
                '{$m['ctest']}','{$m['mtest']}','{$m['subj']}','{$m['obj']}','{$m['pra']}','{$m['ca']}',
                '{$m['sub_final']}','{$m['obj_final']}','{$m['pra_final']}',
                '{$m['markobt']}','{$m['on100']}','MG','$usr', '$gp', '$gl'
            )";
        mysqli_query($conn, $insert);
        $data .= $insert . "<br>";
    }

    // Update sessioninfo
    mysqli_query($conn, "UPDATE sessioninfo SET grand_merged=1 WHERE stid='$stid' AND sessionyear='$session' AND slot='$slot'");
}

// Process batch
foreach ($students as $stu) {
    mergeStudent($stu['stid'], $stu['classname'], $stu['sectionname'], $slot, $session, $sccode, $usr, $conn, $selectedExams, $examRates, $subcode, $data);
}

// Determine next offset
// $nextOffset = (count($students) + $offset < $total) ? ($offset + $batchSize) : null;
$nextOffset = (count($students) + $offset < $total) ? 0 : null;

$data .= count($students) . '//' . $total . '//' . $nextOffset . '................';
echo json_encode([
    'done' => true,
    'count' => count($students),
    'total' => $total,
    'nextOffset' => $nextOffset,
    'data' => $data,
    'stid' => $students[0]['stid'] ?? ''
]);