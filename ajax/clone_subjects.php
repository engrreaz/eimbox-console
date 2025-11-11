<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sources = $_POST['sources'] ?? [];
$target = $_POST['target'] ?? '';

if (!$sccode || !$target || !is_array($sources) || empty($sources)) {
    exit("<div class='text-danger'>Invalid input!</div>");
}

$ok = 0;
foreach ($sources as $src) {
    [$year, $area, $sub] = explode('|', $src);
    $sql = "INSERT INTO subsetup (sccode, sessionyear, classname, sectionname, subject,  modifieddate, slot, fullmarks, ctest, mtest, subj, obj, pra, ca, camanual, ctmt, pass_algorithm, cnt, reverse, tid, combind_1, combind_2, combind_3, combind_4, fourth )
            SELECT '$sccode', '$target', '$area', '$sub', s.subject,  '$cur', s.slot, s.fullmarks, s.ctest, s.mtest, s.subj, s.obj, s.pra, s.ca, s.camanual, s.ctmt, s.pass_algorithm, s.cnt, s.reverse, s.tid,  s.combind_1, s.combind_2, s.combind_3, s.combind_4, s.fourth
            FROM subsetup s
            WHERE s.sccode='$sccode'
              AND s.sessionyear='$year'
              AND s.classname='$area'
              AND s.sectionname='$sub'";

  
    if ($conn->query($sql))
        $ok++;
}

echo "<div class='text-success'>✅ Cloned $ok item(s) successfully!</div>";
?>