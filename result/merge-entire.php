<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// POST থেকে slot এবং session
$slot = $_POST['slot'];
$session = $_POST['session'];

// Fetch all students not yet merged
$q = "SELECT stid, classname, sectionname FROM sessioninfo 
      WHERE sessionyear='$session' 
        AND slot='$slot' 
        AND sccode='$sccode' 
        AND grand_merged=0";
$res = mysqli_query($conn, $q);

$students = [];
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}

// Merge process function
function mergeStudent($stid, $class, $section, $slot, $session, $sccode, $usr, $conn)
{
    // এখানে merge-process.php logic reuse করো
    // মূলত Grand record insert হবে
    // Copy-paste merge-process.php এর core logic
    $where = "sccode='$sccode' AND slot='$slot' AND sessionyear='$session' AND stid='$stid'";

    // Fetch subjects for this student
    $sublist = [];
    $qsub = "SELECT subject FROM subsetup WHERE sessionyear='$session' AND sccode='$sccode' AND classname='$class' AND sectionname='$section'";
    $ressub = mysqli_query($conn, $qsub);
    while ($r = mysqli_fetch_assoc($ressub)) {
        $sublist[] = $r['subject'];
    }

    foreach ($sublist as $subject) {
        // Remove previous Grand record
        $found = "SELECT id FROM stmark WHERE sessionyear='$session' AND sccode='$sccode' AND stid='$stid' AND exam='GRAND' AND subject='$subject'";
        $resf = mysqli_query($conn, $found);
        while ($rowf = mysqli_fetch_assoc($resf)) {
            mysqli_query($conn, "DELETE FROM stmark WHERE id='{$rowf['id']}'");
        }

        // Sum marks
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
            WHERE $where AND subject='$subject'
        ");
        $m = mysqli_fetch_assoc($sumQ);

        // Insert Grand
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

    // Mark as merged
    mysqli_query($conn, "UPDATE sessioninfo SET grand_merged=1 
                         WHERE stid='$stid' AND sessionyear='$session' AND slot='$slot'");
}

// Loop through students
foreach ($students as $stu) {
    mergeStudent($stu['stid'], $stu['classname'], $stu['sectionname'], $slot, $session, $sccode, $usr, $conn);
}

echo json_encode(['done' => true]);
