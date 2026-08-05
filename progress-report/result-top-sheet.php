<?php
/* =====================================================
   FAST STATS QUERIES
===================================================== */

// ---- totals
$row = fetchRow($conn, "
    SELECT 
        COUNT(*) total_students,
        count(totalmarks>0) appeared,
        count(gpa>0) passed
    FROM tabulatingsheet
    WHERE sccode='$sccode'
      AND exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sy'
      AND slot='$slot'
");




$stsd = (int) ($row['total_students'] ?? 0);
$stappear = (int) ($row['appeared'] ?? 0);
$passst = (int) ($row['passed'] ?? 0);
$abs = $stsd - $stappear;
$passrate = $stappear ? ($passst * 100 / $stappear) : 0;

// ---- grade distribution
$gradeDist = fetchAll($conn, "
    SELECT gla, COUNT(*) cnt
    FROM tabulatingsheet
    WHERE sccode='$sccode'
      AND exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sy'
      AND totalmarks>0
    GROUP BY gla
    ORDER BY gla
");

// ---- fail distribution
$failDist = fetchAll($conn, "
    SELECT totalfail, COUNT(*) cnt
    FROM tabulatingsheet
    WHERE sccode='$sccode'
      AND exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sy'
      AND totalmarks>0
      AND totalfail>0
    GROUP BY totalfail
    ORDER BY totalfail
");

// ---- subject list
$subjects = fetchAll($conn, "
    SELECT subcode, subject, subshname, subben
    FROM subjects
    WHERE sccategory='$sctype'
");
$subMap = idxBy($subjects, 'subcode');

// ---- subject setup
$subsetup = fetchAll($conn, "
    SELECT subject, fullmarks
    FROM subsetup
    WHERE sccode='$sccode'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sy'
    ORDER BY subject
");

// ---- subject highest marks (single query)
$subHigh = fetchAll($conn, "
    SELECT subject, MAX(markobt) hi
    FROM stmark
    WHERE sccode='$sccode'
      AND classname='$cn'
      AND sectionname='$secname'
      AND exam='$exam'
      AND sessionyear='$sy'
    GROUP BY subject
");
$subHighMap = idxBy($subHigh, 'subject');

// ---- subject grade counts
$glRows = fetchAll($conn, "
    SELECT subject, gl, COUNT(*) cnt
    FROM stmark
    WHERE sccode='$sccode'
      AND classname='$cn'
      AND sectionname='$secname'
      AND exam='$exam'
    GROUP BY subject, gl
");

$glMap = [];
foreach ($glRows as $r) {
    $glMap[$r['subject']][$r['gl']] = $r['cnt'];
}
?>



<!-- ===================================================== -->
<div class="full-box-2"
    style="width:<?= $paper ?>;margin:auto;text-align:center;font-family:'Segoe UI';page-break-after:always">



    <h2 style="color:#005678;margin:8px 0">Result : At a Glance</h2>
    <h3 style="color:#008765;margin:0"><?= htmlspecialchars($exam) ?> Examination - <?= htmlspecialchars($sy) ?></h3>

    <!-- ================= CLASS INFO ================= -->
    <table width="100%" style="margin:10px 15mm">
        <tr style="font-weight:bold;color:#e02a67">
            <td>Class Info</td>
            <td>Grading Statistics</td>
            <td>Fail Statistics</td>
            <td>Grading System</td>
        </tr>
        <tr>
            <!-- CLASS INFO -->
            <td>
                Class : <b><?= $cn ?></b><br>
                Section : <b><?= $secname ?></b><br>
                Total Student : <b><?= $stsd ?></b><br>
                Appeared : <b><?= $stappear ?></b><br>
                Pass : <b><?= $passst ?></b><br>
                Pass Rate : <b><?= number_format($passrate, 2) ?>%</b>
            </td>

            <!-- GRADE DIST -->
            <td>
                <?php foreach ($gradeDist as $g):
                    $rate = $stappear ? ($g['cnt'] * 100 / $stappear) : 0; ?>
                    <?= $g['gla'] ?> : <b><?= $g['cnt'] ?></b>
                    (<?= number_format($rate, 2) ?>%)<br>
                <?php endforeach; ?>
            </td>

            <!-- FAIL DIST -->
            <td>
                <?php
                $failTotal = array_sum(array_column($failDist, 'cnt'));
                foreach ($failDist as $f):
                    $rate = $failTotal ? ($f['cnt'] * 100 / $failTotal) : 0; ?>
                    <?= $f['totalfail'] ?> Subject(s) :
                    <b><?= $f['cnt'] ?></b>
                    (<?= number_format($rate, 2) ?>%)<br>
                <?php endforeach; ?>
            </td>

            <!-- GRADING SYSTEM -->
            <td>
                <table border="1" width="100%" style="font-size:11px;color:#1c702c">
                    <tr>
                        <th>Grade</th>
                        <th>Point</th>
                        <th>Marks</th>
                    </tr>
                    <tr>
                        <td>A+</td>
                        <td>5.00</td>
                        <td>80+</td>
                    </tr>
                    <tr>
                        <td>A</td>
                        <td>4.00</td>
                        <td>70–79</td>
                    </tr>
                    <tr>
                        <td>A-</td>
                        <td>3.50</td>
                        <td>60–69</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>3.00</td>
                        <td>50–59</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>2.00</td>
                        <td>40–49</td>
                    </tr>
                    <tr>
                        <td>D</td>
                        <td>1.00</td>
                        <td>33–39</td>
                    </tr>
                    <tr>
                        <td>F</td>
                        <td>0.00</td>
                        <td>0–32</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ================= SUBJECT ANALYSIS ================= -->
    <h3 style="margin-top:10px">Subject Wise Grading Analysis</h3>

    <table border="1" width="100%" style="margin:5px 15mm;font-size:12px">
        <tr style="font-weight:bold;color:green">
            <td>Code</td>
            <td>Subject</td>
            <td>Full</td>
            <td>Highest</td>
            <td>A+</td>
            <td>A</td>
            <td>A-</td>
            <td>B</td>
            <td>C</td>
            <td>D</td>
            <td>F</td>
        </tr>

        <?php foreach ($subsetup as $ss):
            $code = $ss['subject'];
            $fm = $ss['fullmarks'];

            $info = $subMap[$code] ?? [];
            $name = ($info['subject'] ?? '') . ' / ' . ($info['subben'] ?? '');
            $sh = $info['subshname'] ?? '';

            $hi = $subHighMap[$code]['hi'] ?? 0;

            $g = $glMap[$code] ?? [];
            $ap = $g['A+'] ?? 0;
            $a = $g['A'] ?? 0;
            $am = $g['A-'] ?? 0;
            $b = $g['B'] ?? 0;
            $c = $g['C'] ?? 0;
            $d = $g['D'] ?? 0;
            $f = $stappear - ($ap + $a + $am + $b + $c + $d);
            ?>
            <tr>
                <td align="center"><?= $code ?></td>
                <td><?= htmlspecialchars($name) ?> <b>[<?= htmlspecialchars($sh) ?>]</b></td>
                <td align="center"><?= $fm ?></td>
                <td align="center"><?= $hi ?></td>
                <td align="center"><?= $ap ?></td>
                <td align="center"><?= $a ?></td>
                <td align="center"><?= $am ?></td>
                <td align="center"><?= $b ?></td>
                <td align="center"><?= $c ?></td>
                <td align="center"><?= $d ?></td>
                <td align="center" style="color:red"><?= $f ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>