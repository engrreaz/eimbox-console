<?php
/* =====================================================
   FAST STATS QUERIES
===================================================== */

// ---- totals
$row = fetchRow($conn, "
    SELECT 
        COUNT(*) total_students,
        SUM(totalmarks>0) appeared,
        SUM(gpa>0) passed
    FROM tabulatingsheet
    WHERE sccode='$sccode'
      AND exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sessionyear'
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
      AND sessionyear='$sessionyear'
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
      AND sessionyear='$sessionyear'
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
      AND sessionyear='$sessionyear'
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
      AND sessionyear='$sessionyear'
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
    style="width:<?= $paper ?>;margin:auto;padding-top:15mm; text-align:center;font-family:'Segoe UI';page-break-after:always">

    <!-- ================= HEADER ================= -->
     <img src="https://www.eimbox.com/logo/<?= $sccode ?>.png" style="width:100px;"/>
     <h3 style="font-weight:900;"><?= $scname ?></h3>


    <h2 style="color:#005678;margin:8px 0">Result : At a Glance</h2>
    <h3 style="color:#008765;margin:0"><?= htmlspecialchars($exam) ?> Examination - <?= htmlspecialchars($sessionyear) ?></h3>

    <!-- ================= CLASS INFO ================= -->
    <table  style="width:calc(100% - 30mm); margin:10px 15mm">
        <tr style="font-weight:bold;color:#e02a67">
            <td style="padding:2px; text-align:center;">Class Info</td>
            <td style="padding:2px; text-align:center;">Grading Statistics</td>
            <td style="padding:2px; text-align:center;">Fail Statistics</td>
            <td style="padding:2px; text-align:center;">Grading System</td>
        </tr>
        <tr>
            <!-- CLASS INFO -->
            <td style="padding:5px;">
                <table style="width:100%; border:0; border-collapse:collapse;">
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Class :</td><td style="border:0; text-align:left;"><b><?= $cn ?></b></td></tr>
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Section :</td><td style="border:0; text-align:left;"><b><?= $secname ?></b></td></tr>
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Total Student :</td><td style="border:0; text-align:left;"><b><?= $stsd ?></b></td></tr>
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Appeared :</td><td style="border:0; text-align:left;"><b><?= $stappear ?></b></td></tr>
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Pass :</td><td style="border:0; text-align:left;"><b><?= $passst ?></b></td></tr>
                    <tr><td style="border:0; text-align:right; padding-right:5px;">Pass Rate :</td><td style="border:0; text-align:left;"><b><?= number_format($passrate, 2) ?>%</b></td></tr>
                </table>
            </td>

            <!-- GRADE DIST -->
            <td style="padding:5px;">
                <table style="width:100%; border:0; border-collapse:collapse;">
                    <?php foreach ($gradeDist as $g):
                        $rate = $stappear ? ($g['cnt'] * 100 / $stappear) : 0; ?>
                        <tr><td style="border:0; text-align:right; padding-right:5px;"><?= $g['gla'] ?> :</td>
                        <td style="border:0; text-align:left;"><b><?= $g['cnt'] ?></b> (<?= number_format($rate, 2) ?>%)</td></tr>
                    <?php endforeach; ?>
                </table>
            </td>

            <!-- FAIL DIST -->
            <td style="padding:5px;">
                <table style="width:100%; border:0; border-collapse:collapse;">
                    <?php
                    $failTotal = array_sum(array_column($failDist, 'cnt'));
                    foreach ($failDist as $f):
                        $rate = $failTotal ? ($f['cnt'] * 100 / $failTotal) : 0; ?>
                        <tr><td style="border:0; text-align:right; padding-right:5px;"><?= $f['totalfail'] ?> Subject(s) :</td>
                        <td style="border:0; text-align:left;"><b><?= $f['cnt'] ?></b> (<?= number_format($rate, 2) ?>%)</td></tr>
                    <?php endforeach; ?>
                </table>
            </td>

            <!-- GRADING SYSTEM -->
            <td style="padding:5px;">
                <table border="1" class="grading-system-table" style="font-size:11px;color:#1c702c; width:100%;">
                    <tr>
                        <th>Grade</th>
                        <th>Point</th>
                        <th>Marks</th>
                    </tr>
                    <tr>
                        <td style="text-align:center;">A+</td>
                        <td style="text-align:center;">5.00</td>
                        <td style="text-align:center;">80+</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">A</td>
                        <td style="text-align:center;">4.00</td>
                        <td style="text-align:center;">70–79</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">A-</td>
                        <td style="text-align:center;">3.50</td>
                        <td style="text-align:center;">60–69</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">B</td>
                        <td style="text-align:center;">3.00</td>
                        <td style="text-align:center;">50–59</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">C</td>
                        <td style="text-align:center;">2.00</td>
                        <td style="text-align:center;">40–49</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">D</td>
                        <td style="text-align:center;">1.00</td>
                        <td style="text-align:center;">33–39</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">F</td>
                        <td style="text-align:center;">0.00</td>
                        <td style="text-align:center;">0–32</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ================= SUBJECT ANALYSIS ================= -->
    <h3 style="margin-top:10px; page-break-before: always;">Subject Wise Grading Analysis</h3>

    <table border="1"  style="width:calc(100% - 30mm);margin:5px 15mm;font-size:12px">
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