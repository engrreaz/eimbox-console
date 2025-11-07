<?php
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_POST['sccode'] ?? '';
$sl = $_POST['sl'] ?? '';
$sy = $_POST['sy'] ?? '';
$cc = $_POST['cc'] ?? '';
$ss = $_POST['ss'] ?? '';
$exam = $_POST['ex'] ?? '';
$exam_id = $_POST['id'] ?? '';

if (!$sccode || !$sl || !$sy || !$cc || !$ss) {
    exit('Missing parameters');
}

$sql = "SELECT rootuser FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    $rootuser = $res->fetch_assoc()['rootuser'];
}

$subsetup = [];
$sql_sub = "SELECT subject FROM subsetup WHERE sccode='$sccode' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
$res_sub = $conn->query($sql_sub);
while ($r = $res_sub->fetch_assoc()) {
    $subsetup[] = $r['subject'];
}

$sql_st = "SELECT stid FROM sessioninfo WHERE sccode='$sccode' AND slot='$sl' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
$res_st = $conn->query($sql_st);
$studentCount = $res_st ? $res_st->num_rows : 0;
$done = 0;
$insert_values = [];

while ($r = $res_st->fetch_assoc()) {
    $stid = $r['stid'];
    foreach ($subsetup as $subcode) {
        $sql_rand = "SELECT fullmark, subj, obj, pra, sub_final, obj_final, pra_final, markobt, on100, gp, gl
                     FROM stmark WHERE subject='$subcode' AND classname='$cc' ORDER BY RAND() LIMIT 1";
        $res_rand = $conn->query($sql_rand);
        if ($res_rand && $res_rand->num_rows > 0) {
            $base = $res_rand->fetch_assoc();

            $insert_values[] = "(
                '$sccode', '$sy', '$sl', '$exam', '$exam_id', 'PE', '$cc', '$ss', '$subcode',
                '{$base['fullmark']}', '$stid', '{$base['subj']}', '{$base['obj']}', '{$base['pra']}',
                '{$base['sub_final']}', '{$base['obj_final']}', '{$base['pra_final']}',
                '{$base['markobt']}', '{$base['on100']}', '{$base['gp']}', '{$base['gl']}',
                NOW(), '$rootuser'
            )";
        }
    }
}

if (!empty($insert_values)) {
    $sql_bulk = "INSERT INTO stmark
        (sccode, sessionyear, slot, exam, examid, examtype, classname, sectionname, subject,
        fullmark, stid, subj, obj, pra, sub_final, obj_final, pra_final,
        markobt, on100, gp, gl, entrydate, entryby)
        VALUES " . implode(",", $insert_values);

    $conn->query($sql_bulk);
}

echo "Inserted dummy marks for " . count($insert_values) . " rows ($cc-$ss).";
