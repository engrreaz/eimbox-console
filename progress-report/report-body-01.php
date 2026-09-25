<!-- ===================== REPORT CARD (SINGLE A4 PAGE) ===================== -->
<div class="paper">
    <?php if (!empty($progress_report_wartermark) && $progress_report_wartermark == 1) { ?>
        <div class="logo-watermark">
            <img src="<?= BASE_PATH . 'logo/' . $sccode . '.png' ?>" style="width:100%; height:auto;" onerror="this.style.display='none'">
        </div>
    <?php } ?>

    <div class="report-content-wrapper" style="position:relative; z-index:2;">
        <!-- ===== Letterhead / Institutional Header ===== -->
        <div class="report-header-area" style="transform:scale(<?= $progress_report_header_scale ?>); transform-origin: top center; margin-bottom: 5px;">
            <?php
            $ppp = '';
            if (!empty($progress_report_head)) {
                if (file_exists($progress_report_head)) {
                    $ppp = $progress_report_head;
                } elseif (defined('BASE_ROOT') && file_exists(BASE_ROOT . $progress_report_head)) {
                    $ppp = BASE_ROOT . $progress_report_head;
                } elseif (file_exists(__DIR__ . '/../' . $progress_report_head)) {
                    $ppp = __DIR__ . '/../' . $progress_report_head;
                }
            }

            if ($ppp) {
                include $ppp; // include on every student page
            } else {
                // Default Standard Institutional Letterhead
                ?>
                <table style="width:100%; border-collapse:collapse; margin-bottom:4px;">
                    <tr>
                        <td style="width:65px; vertical-align:middle; text-align:left;">
                            <img src="<?= BASE_PATH . 'logo/' . $sccode . '.png' ?>" style="height:55px; max-width:65px;" onerror="this.style.display='none'">
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                            <h2 style="margin:0; font-size:18px; font-weight:bold; color:#1a3353; text-transform:uppercase; letter-spacing:0.5px;"><?= htmlspecialchars($scname) ?></h2>
                            <div style="font-size:11.5px; color:#444; margin-top:2px;"><?= htmlspecialchars($eiaddress) ?></div>
                            <div style="font-size:11px; color:#666;">EIIN: <?= htmlspecialchars($sccode) ?><?= !empty($eicontact) ? ' | Phone: ' . htmlspecialchars($eicontact) : '' ?></div>
                        </td>
                        <td style="width:65px; vertical-align:middle; text-align:right;">
                            <!-- Optional Right Badge/Logo -->
                        </td>
                    </tr>
                </table>
                <hr style="border:0; border-top:1.5px solid #1a3353; margin:3px 0 6px 0;">
                <?php
            }
            ?>
        </div>

        <!-- ===== Exam Title Badge ===== -->
        <div style="text-align:center; margin-bottom:8px;">
            <span style="display:inline-block; border:1px solid #1a3353; padding:2px 14px; border-radius:12px; background:#f0f4f9; font-weight:bold; font-size:13px; color:#1a3353;">
                PROGRESS REPORT &mdash; <?= strtoupper(htmlspecialchars($exam)) ?> EXAMINATION <?= $sessionyear ?>
            </span>
            <?php if (!empty($progress_report_text)): ?>
                <div style="margin-top:2px;"><img src="<?= BASE_PATH . $progress_report_text ?>" style="height:16px;" /></div>
            <?php endif; ?>
        </div>

        <!-- ===== Student Information Card ===== -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:8px; font-size:12px; background:#fafbfc; border:1px solid #d0d7de; border-radius:4px;" cellpadding="4">
            <tr>
                <td style="width:12%; color:#555;"><b>Student Name:</b></td>
                <td style="width:38%; font-weight:bold; color:#111; font-size:12.5px;">
                    <?= $engname ? htmlspecialchars($stnameeng) : htmlspecialchars($stnameben ?: $stnameeng) ?>
                    <?php if ($engname && !empty($stnameben)): ?>
                        <span style="font-size:11px; color:#666; font-weight:normal;">(<?= htmlspecialchars($stnameben) ?>)</span>
                    <?php endif; ?>
                </td>
                <td style="width:12%; color:#555;"><b>Roll No:</b></td>
                <td style="width:38%; font-weight:bold; color:#111; font-size:13px;"><?= htmlspecialchars($rollno) ?></td>
            </tr>
            <tr>
                <td style="color:#555;"><b>Class:</b></td>
                <td style="font-weight:bold; color:#111;"><?= htmlspecialchars($cn) ?></td>
                <td style="color:#555;"><b>Section:</b></td>
                <td style="font-weight:bold; color:#111;"><?= htmlspecialchars($secname) ?></td>
            </tr>
            <tr>
                <td style="color:#555;"><b>Guardian:</b></td>
                <td colspan="3" style="color:#333;"><?= htmlspecialchars($parent_line) ?></td>
            </tr>
        </table>

        <!-- ===== Marks Table (Modular Template) ===== -->
        <?php
        $marks_template = $settings['marks_table_template'] ?? 'progress-report/marks-table-template-1.php';
        if (!file_exists($marks_template)) {
            $marks_template = 'progress-report/marks-table-template-1.php';
        }
        include $marks_template;
        ?>

        <!-- ===== Summary & Performance Matrix ===== -->
        <table style="width:100%; margin-top:8px; border-collapse:collapse; border:1px solid #d0d7de; font-size:12px; background:#fafbfc;" cellpadding="4">
            <tr>
                <td style="width:25%; border-right:1px solid #d0d7de;"><b>Section Merit:</b> <span style="font-size:13px; font-weight:bold; color:#1a3353;"><?= htmlspecialchars($meritplace ?: '—') ?></span></td>
                <td style="width:25%; border-right:1px solid #d0d7de;"><b>Class Combined:</b> <span style="font-size:13px; font-weight:bold; color:#1a3353;"><?= htmlspecialchars($mcomb ?: '—') ?></span></td>
                <td style="width:25%; border-right:1px solid #d0d7de;"><b>Failed Subjects:</b> <?= htmlspecialchars($failsub ?: 'None') ?></td>
                <td style="width:25%;"><b>Result:</b> <?= $totalfail ? '<span style="color:red; font-weight:bold;">FAILED (' . $totalfail . ')</span>' : '<span style="color:#28a745; font-weight:bold;">PASSED</span>' ?></td>
            </tr>
            <tr>
                <td colspan="2" style="border-right:1px solid #d0d7de;"><b>Remarks:</b> <?= htmlspecialchars($remark ?: ($totalfail ? 'Needs Improvement' : 'Satisfactory Performance')) ?></td>
                <td colspan="2"><b>Publish Date:</b> <?= htmlspecialchars($rpubdt) ?></td>
            </tr>
        </table>
    </div>

    <!-- ===== Signatures Footer (Bottom Anchored) ===== -->
    <div style="position:relative; z-index:2; margin-top:15px;">
        <table style="width:100%; border-collapse:collapse; text-align:center; font-size:11.5px;">
            <tr>
                <td style="width:33%; vertical-align:bottom;">
                    <div style="border-top:1px dashed #444; width:80%; margin:0 auto; padding-top:4px;">
                        <b>Class Teacher</b><br>
                        <span style="color:#555; font-size:11px;"><?= htmlspecialchars($cteacher ?: 'Authorized Signature') ?></span>
                    </div>
                </td>
                <td style="width:34%; vertical-align:bottom;">
                    <div style="border-top:1px dashed #444; width:80%; margin:0 auto; padding-top:4px;">
                        <b>Guardian Signature</b><br>
                        <span style="color:#777; font-size:10.5px;">Date: ____________</span>
                    </div>
                </td>
                <td style="width:33%; vertical-align:bottom;">
                    <?php if (!empty($sign_path)): ?>
                        <div style="margin-bottom:2px;"><img src="<?= $sign_path ?>" style="height:22px; max-width:80px;" onerror="this.style.display='none'"></div>
                    <?php endif; ?>
                    <div style="border-top:1px dashed #444; width:80%; margin:0 auto; padding-top:4px;">
                        <b><?= htmlspecialchars($headtitle ?: 'Head Teacher') ?></b><br>
                        <span style="color:#555; font-size:11px;"><?= htmlspecialchars($headname ?: 'Authorized Signature') ?></span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<!-- =================== END REPORT CARD =================== -->