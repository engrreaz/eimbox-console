<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$data = '';
set_time_limit(0); // বড় session handle করতে

$slot = $_POST['slot'];
$session = $_POST['session'];
$offset = intval($_POST['offset'] ?? 0);
$batchSize = intval($_POST['batchSize'] ?? 1);

$cls = $_POST['classname'] ?? '';
$sec = $_POST['sectionname'] ?? '';
$subcode = $_POST['subcode'] ?? '';

$selectedExams = [];
$examcount = 0;
if (!empty($_COOKIE['examitems'])) {
    $selectedExams = explode(",", $_COOKIE['examitems']);
    $examcount = count($selectedExams);
}

// Build exam condition
$examCondition = "";
if (count($selectedExams) > 0) {
    $escapedExams = array_map(function ($ex) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $ex) . "'";
    }, $selectedExams);
    $examList = implode(",", $escapedExams);
    $examCondition = " AND exam IN ($examList)";
} else {
    // কোন exam select করা হয়নি → সব বাদ
    $examCondition = " AND 0";
}

// Base WHERE conditions
$where = "sessionyear='$session' AND slot='$slot' AND sccode='$sccode' AND grand_merged=0";

// Add classname filter if provided
if ($cls !== '') {
    $where .= " AND classname='$cls'";
}

// Add section filter if provided
if ($sec !== '') {
    $where .= " AND sectionname='$sec'";
}

// Total students
$q_total = "SELECT COUNT(*) AS cnt FROM sessioninfo WHERE $where";
$res_total = mysqli_query($conn, $q_total);
$total = mysqli_fetch_assoc($res_total)['cnt'];

// Fetch batch of students
$q = "SELECT stid, classname, sectionname 
      FROM sessioninfo 
      WHERE $where 
      LIMIT $offset, $batchSize";
$res = mysqli_query($conn, $q);

$students = [];
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}

// Merge function (reuse merge-process logic)
function mergeStudent($stid, $class, $section, $slot, $session, $sccode, $usr, $conn)
{
    $where = "sccode='$sccode' AND slot='$slot' AND sessionyear='$session' AND stid='$stid'";
    global $examCondition;
    global $examcount;
    global $data;
    global $subcode;

    $subfinal = $objfinal = $prafinal = $cafinal = $totalfinal = $algfinal = 0;
    // Fetch subjects

    $sublist = [];

    if ($subcode !== '') {
        $qsub = "SELECT * FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode'
             AND classname='$class' AND sectionname='$section' AND subject='$subcode'";
        $ressub = mysqli_query($conn, $qsub);
        while ($r = mysqli_fetch_assoc($ressub)) {
            $sublist[] = $r;
        }
    } else {
        $qsub = "SELECT * FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode' AND classname='$class' AND sectionname='$section'";
        $ressub = mysqli_query($conn, $qsub);
        while ($r = mysqli_fetch_assoc($ressub)) {
            $sublist[] = $r;
        }
    }



    foreach ($sublist as $subrow) {

        $subject = $subrow['subject'];
        $subfinal = $subrow['subj'] * $examcount;
        $objfinal = $subrow['obj'] * $examcount;
        $prafinal = $subrow['pra'] * $examcount;
        $cafinal = $subrow['ca'] * $examcount;
        $totalfinal = $subrow['fullmarks'] * $examcount;
        $algfinal = $subrow['pass_algorithm'];

        // Delete previous GRAND
        $found = "SELECT id FROM stmark WHERE sessionyear='$session' AND sccode='$sccode' AND stid='$stid' AND exam='GRAND' AND subject='$subject'";
        $resf = mysqli_query($conn, $found);
        while ($rowf = mysqli_fetch_assoc($resf)) {
            mysqli_query($conn, "DELETE FROM stmark WHERE id='{$rowf['id']}'");
        }

        // Sum marks
        $sqlq = "
            SELECT 
                SUM(ctest) AS ctest,
                SUM(mtest) AS mtest,
                SUM(subj) AS subj,
                SUM(obj) AS obj,
                SUM(pra) AS pra,
                SUM(ca) AS ca,
                SUM(sub_final) AS sub_final,
                SUM(obj_final) AS obj_final,
                SUM(pra_final) AS pra_final,
                SUM(markobt) AS markobt,
                SUM(on100) AS on100,
                SUM(fullmark) AS fullmark
            FROM stmark 
            WHERE $where AND subject='$subject' $examCondition
        ";
        $sumQ = mysqli_query($conn, $sqlq);
        $m = mysqli_fetch_assoc($sumQ);

        $data .= $sqlq . '<br><br>';

        $p = pass_validation($m['ctest'], $m['mtest'], $m['subj'], $m['obj'], $m['pra'], $m['ca'], $subfinal, $objfinal, $prafinal, $totalfinal, $algfinal, 36, 2);
        // echo $p;
        if ($p === false || $p == 0) {
            $gp = 0;
            $gl = 'F';
        } else {
            // echo 'check';
            $gpgl = get_GP_GL($m['markobt'], $totalfinal);
            $gp = $gpgl['gp'];
            $gl = $gpgl['gl'];
        }

        // Insert GRAND
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
        $data .= $insert;
    }

    // Mark as merged
    mysqli_query($conn, "UPDATE sessioninfo SET grand_merged=1 
                         WHERE stid='$stid' AND sessionyear='$session' AND slot='$slot'");
}

// Process batch
foreach ($students as $stu) {
    mergeStudent($stu['stid'], $stu['classname'], $stu['sectionname'], $slot, $session, $sccode, $usr, $conn);
}

// Determine next offset
$nextOffset = (count($students) + $offset < $total) ? ($offset + $batchSize) : null;

echo json_encode([
    'done' => true,
    'count' => count($students),
    'total' => $total,
    'nextOffset' => $nextOffset,
    'data' => $data
]);