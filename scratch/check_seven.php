<?php
$conn = new mysqli('localhost', 'root', '', 'secure_auth');

$q = "
    SELECT si.rollno, si.stid, sm.subject, sm.markobt, sm.fullmark, sm.presence, sm.gp, sm.gl,
           COALESCE(ss.fullmarks, sm.fullmark) as ss_fullmark,
           COALESCE(ss.fourth, 0) as fourth,
           COALESCE(ss.combind_1, '0') as combind_1
    FROM sessioninfo si
    JOIN stmark sm ON si.stid = sm.stid AND si.sccode = sm.sccode AND si.sessionyear = sm.sessionyear AND si.slot = sm.slot
    LEFT JOIN subsetup ss ON sm.sccode = ss.sccode AND sm.sessionyear = ss.sessionyear AND sm.classname = ss.classname AND sm.sectionname = ss.sectionname AND sm.subject = ss.subject
    WHERE si.sccode = '700007' AND si.sessionyear = '2025' AND sm.examid = '75' AND si.classname = 'Seven' AND si.sectionname = 'Mayna'
    ORDER BY CAST(si.rollno AS UNSIGNED), sm.subject
";
$res = $conn->query($q);
$students = [];
while ($row = $res->fetch_assoc()) {
    $students[$row['rollno']][] = $row;
}

$target_rolls = [1, 4, 3, 2, 7, 5, 6, 12, 8];

foreach ($students as $roll => $marks) {
    $sep_fails = [];
    foreach ($marks as $m) {
        $sub = $m['subject'];
        $obt = (float)$m['markobt'];
        $fm = (float)$m['fullmark'];
        $pres = (int)$m['presence'];
        if ($fm == 0) continue;
        $pct = ($obt / $fm) * 100;
        if ($pct < 33 || $m['gp'] <= 0 || ($pres == 0 && $obt == 0)) {
            $sep_fails[] = "$sub ({$obt}/{$fm}, gp={$m['gp']}, gl={$m['gl']}, pres={$pres})";
        }
    }
    
    $is_target = in_array((int)$roll, $target_rolls) ? " [TARGET PASSED]" : "";
    echo "Roll $roll$is_target: " . (empty($sep_fails) ? "ALL PASS" : "FAILS: " . implode('; ', $sep_fails)) . "\n";
}
