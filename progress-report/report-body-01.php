<!-- ===================== REPORT CARD ===================== -->
<style>
    /* Screen এ (ডিসপ্লে মোড) */
    .paper {
        width: 210mm;
        height: 297mm;
        margin: 30px auto;
        /* top/bottom margin থাকবে */
        padding: 10mm;
        border: 1px solid #ccc;
        page-break-after: always;
        background: #fff;
        position: relative;
        <?php if ($preview != '') { ?>
            background-image: url('<?= BASE_PATH . $progress_report_background ?>');
            transform: scale(1);
        <?php } ?>
    }

    .logo {
        position: absolute;
        width: 50mm;
        height: 50mm;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        /* background এর উপর */
        opacity: 0.99;

    }

    /* Print এ */
    @media print {
        .paper {
            margin: 0;
            background-image: url('<?= BASE_PATH . $progress_report_background ?>');
            background-size: cover;
            /* পুরো পেজ ঢাকবে */
            background-repeat: no-repeat;
            background-position: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }


    }
</style>

<div class="paper">
    <?php if ($progress_report_wartermark == 1) { ?>
        <div class="logo"><img src="<?= BASE_PATH . 'logo/' . $sccode . '.png' ?>" style="height:50mm;"></div>
    <?php } ?>

    <div style="transform:scale(<?= $progress_report_header_scale ?>);">
        <?php
        $ppp = BASE_ROOT . $progress_report_head;
        include_once($ppp); ?>
    </div>



    <!-- ===== Header ===== -->
    <div style="text-align:center;margin-bottom:10px">
        <h4 style="margin:8px 0"><?= htmlspecialchars($exam) ?> Examination &mdash; <?= $sessionyear ?></h4>
        <img src="<?= BASE_PATH . $progress_report_text ?>" style="height:20px;" />
    </div>

    <!-- ===== Student Info ===== -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:14px; z-index:3;">
        <tr>
            <td><b>Name</b></td>
            <td><?= $engname ? htmlspecialchars($stnameeng) : htmlspecialchars($stnameben) ?></td>
            <td><b>Roll</b></td>
            <td><?= htmlspecialchars($rollno) ?></td>
        </tr>
        <tr>
            <td><b>Class</b></td>
            <td><?= htmlspecialchars($cn) ?></td>
            <td><b>Section</b></td>
            <td><?= htmlspecialchars($secname) ?></td>
        </tr>
        <tr>
            <td><b>Guardian</b></td>
            <td colspan="3"><?= htmlspecialchars($parent_line) ?></td>
        </tr>
    </table>

    <!-- ===== Marks Table ===== -->
    <table style="width:100%;border-collapse:collapse; border:1px solid black; font-size:13px; z-index:5;"
        cellpadding="5">
        <thead style="background:#f2f2f2">
            <tr>
                <th>#</th>
                <th style="text-align:left;">Subject</th>
                <th>Full</th>
                <th>Obtained</th>
                <th>Highest</th>
                <th>GPA</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $total_obt = 0;
            $total_full = 0;

            foreach ($subsetup as $ss) {
                $subcode = $ss['subject'];
                if (!$subcode)
                    continue;

                $sub = $subjects_map[$subcode] ?? [];
                $subname = $sub['subname'] ?? $subcode;

                $fm = (int) ($ss['fullmarks'] ?? 0);

                $obt_key = 'sub_' . $i . '_total';
                $gpa_key = 'sub_' . $i . '_gpa';
                $gl_key = 'sub_' . $i . '_gl';

                $obt = (int) ($stmark[$obt_key] ?? 0);
                $gpa = $stmark[$gpa_key] ?? '';
                $gl = $stmark[$gl_key] ?? '';

                $hi = $hmark_map[$subcode]['kkk'] ?? '';

                $total_obt += $obt;
                $total_full += $fm;
                ?>
                <tr>
                    <td align="center"><?= $i ?></td>
                    <td><?= htmlspecialchars($subname) ?></td>
                    <td align="center"><?= $fm ?></td>
                    <td align="center"><?= $obt ?></td>
                    <td align="center"><?= $hi ?></td>
                    <td align="center"><?= htmlspecialchars($gpa) ?></td>
                    <td align="center"><?= htmlspecialchars($gl) ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </tbody>

        <!-- ===== Totals ===== -->
        <tfoot style="background:#fafafa;font-weight:bold">
            <tr>
                <td colspan="2" align="right">Total</td>
                <td align="center"><?= $total_full ?></td>
                <td align="center"><?= $totalmarks ?></td>
                <td align="center"><?= $hmarktot ?></td>
                <td align="center"><?= htmlspecialchars($gpa) ?></td>
                <td align="center" style="color:<?= $clc ?>"><?= htmlspecialchars($gla) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- ===== Summary ===== -->
    <table style="width:100%;margin-top:10px;font-size:14px">
        <tr>
            <td><b>Merit Position:</b> <?= htmlspecialchars($meritplace) ?></td>
            <td><b>Fail Subjects:</b> <?= htmlspecialchars($failsub ?: 'None') ?></td>
            <td><b>Result:</b> <?= $totalfail ? '<span style="color:red">Fail</span>' : 'Pass' ?></td>
        </tr>
        <tr>
            <td><b>Remark:</b> <?= htmlspecialchars($remark) ?></td>
            <td colspan="2"><b>Published:</b> <?= htmlspecialchars($rpubdt) ?></td>
        </tr>
    </table>

    <!-- ===== Signatures ===== -->
    <table style="width:100%;margin-top:40px;text-align:center">
        <tr>
            <td>
                ___________________<br>
                Class Teacher<br>
                <?= htmlspecialchars($cteacher) ?>
            </td>
            <td>
                ___________________<br>
                <?= htmlspecialchars($headtitle) ?><br>
                <?= htmlspecialchars($headname) ?>
            </td>
        </tr>
    </table>


</div>
<!-- =================== END REPORT CARD =================== -->