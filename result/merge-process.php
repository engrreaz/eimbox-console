<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];

$index = intval($_POST['index']);
$students = $_SESSION['merge_students'];
$stid = $students[$index];

// Build WHERE condition
$where = "sccode='$sccode' AND slot='$slot' AND sessionyear='$session' AND stid='$stid'";

if (!empty($class)) {
    $where .= " AND classname='$class'";
}
if (!empty($section)) {
    $where .= " AND sectionname='$section'";
}

// যদি class বা section খালি থাকে, fetch করে নাও
if (empty($class) || empty($section)) {
    $qcls = "SELECT classname, sectionname FROM sessioninfo WHERE stid='$stid' AND sessionyear='$session' AND sccode='$sccode' LIMIT 1";
    $rescls = mysqli_fetch_assoc(mysqli_query($conn, $qcls));
    $class = $rescls['classname'];
    $section = $rescls['sectionname'];
}

// Build subject list
$sublist = [];
if (!empty($subject)) {
    $sublist[] = $subject;
} else {
    $qsub = "SELECT subject FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode' AND classname='$class' AND sectionname='$section'";
    $ressub = mysqli_query($conn, $qsub);
    while ($r = mysqli_fetch_assoc($ressub)) {
        $sublist[] = $r['subject'];
    }
}

// Loop through subjects and calculate sums
foreach ($sublist as $subject) {

    // Check if a Grand record already exists
    $found = "SELECT id FROM stmark WHERE sessionyear='$session' AND sccode='$sccode' AND stid='$stid' AND exam='GRAND'";
    $ress = mysqli_query($conn, $found);

    if (mysqli_num_rows($ress) > 0) {
        while ($row = mysqli_fetch_assoc($ress)) {
            $id = $row['id'];
            // Delete existing record
            mysqli_query($conn, "DELETE FROM stmark WHERE id='$id'");
        }
    }


    $sumQ = mysqli_query($conn, "
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
        WHERE $where AND subject='$subject' and exam !='GRAND'
    ");

    $m = mysqli_fetch_assoc($sumQ);

    // Insert grand result
    $insert = "
    INSERT INTO stmark (
        slot, sessionyear, sccode, exam, classname, sectionname, subject, 
        stid, fullmark, 
        ctest, mtest, subj, obj, pra, ca,
        sub_final, obj_final, pra_final,
        markobt, on100, examtype, entryby
    ) VALUES (
        '$slot','$session','$sccode','GRAND','$class','$section','$subject',
        '$stid','{$m['fullmark']}',
        '{$m['ctest']}','{$m['mtest']}','{$m['subj']}','{$m['obj']}','{$m['pra']}','{$m['ca']}',
        '{$m['sub_final']}','{$m['obj_final']}','{$m['pra_final']}',
        '{$m['markobt']}','{$m['on100']}','MG','$usr'
    )";

    mysqli_query($conn, $insert);
}

echo json_encode(["done" => true]);
