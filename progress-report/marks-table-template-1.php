<?php
/**
 * ====================================================================
 * Progress Report - Marks Table Template 1 (Default Standard Template)
 * ====================================================================
 * Data Dependencies:
 * - $stmark    : Current student's row from `tabulatingsheet`
 * - $stmarkex  : Current student's row from `tabulatingsheetex`
 * - $subjects_map : Map of all subjects by `subcode`
 * - $subsetup_map : Map of subject configurations from `subsetup`
 * - $hmark_map    : Highest marks mapped by `subcode`
 * - $hmarktot     : Highest total marks in section/class
 * - $totalmarks, $fullmarks, $gpa, $gla, $clc
 */

$allsubStr = $stmark['allsubject'] ?? ($stmarkex['allsubject'] ?? '');
$allsub = array_filter(explode('.', $allsubStr), function ($v) {
    return trim($v) !== '';
});

$is_fourth = false;
$parsedRows = [];
$total_full_calc = 0;
$total_obt_calc = 0;
$sl = 0;

// Determine if there is any Practical (PRA) mark in this class/section
$has_pra = false;
$has_ca = false;

foreach ($allsub as $code) {
    if ($code == '1000') {
        $is_fourth = true;
        continue;
    }

    $codeNum = (int) $code;
    $subInfo = $subjects_map[$code] ?? [];
    $subnameEn = strip_tags($subInfo['subject'] ?? $code);
    $subnameBn = $subInfo['subben'] ?? '';

    $sub_sub = 0;
    $sub_obj = 0;
    $sub_pra = 0;
    $sub_ca = 0;
    $sub_total = 0;
    $sub_gp = 0;
    $sub_gl = '';
    $sub_fullmark = 100;
    $is_combined = false;

    if ($is_fourth) {
        // 4th Subject (After 1000 separator)
        $sub_sub = $stmarkex['fourth_subj'] ?? ($stmark['sub_12_sub'] ?? 0);
        $sub_obj = $stmarkex['fourth_obj'] ?? ($stmark['sub_12_obj'] ?? 0);
        $sub_pra = $stmarkex['fourth_pra'] ?? ($stmark['sub_12_pra'] ?? 0);
        $sub_ca = $stmarkex['fourth_ca'] ?? ($stmark['sub_12_ca'] ?? 0);
        $sub_total = $stmarkex['fourth_total'] ?? ($stmark['sub_12_total'] ?? 0);
        $sub_gp = $stmarkex['fourth_gp'] ?? ($stmark['sub_12_gp'] ?? 0);
        $sub_gl = $stmarkex['fourth_gl'] ?? ($stmark['sub_12_gl'] ?? '');
        $sub_fullmark = (int) ($subsetup_map[$code]['fullmarks'] ?? 100);
    } elseif ($codeNum > 1000) {
        // Combined Subject (e.g. 1101, 1107, 1103)
        $is_combined = true;
        $sub_fullmark = (int) ($subsetup_map[$code]['fullmarks'] ?? 200);

        if (isset($stmark['comb_1_code']) && $stmark['comb_1_code'] == $code) {
            $sub_sub = $stmark['comb_1_sub'] ?? 0;
            $sub_obj = $stmark['comb_1_obj'] ?? 0;
            $sub_pra = $stmark['comb_1_pra'] ?? 0;
            $sub_ca = $stmark['comb_1_ca'] ?? 0;
            $sub_total = $stmark['comb_1_total'] ?? 0;
            $sub_gp = $stmark['comb_1_gp'] ?? 0;
            $sub_gl = $stmark['comb_1_gl'] ?? '';
        } elseif (isset($stmark['comb_2_code']) && $stmark['comb_2_code'] == $code) {
            $sub_sub = $stmark['comb_2_sub'] ?? 0;
            $sub_obj = $stmark['comb_2_obj'] ?? 0;
            $sub_pra = $stmark['comb_2_pra'] ?? 0;
            $sub_ca = $stmark['comb_2_ca'] ?? 0;
            $sub_total = $stmark['comb_2_total'] ?? 0;
            $sub_gp = $stmark['comb_2_gp'] ?? 0;
            $sub_gl = $stmark['comb_2_gl'] ?? '';
        } elseif (!empty($stmarkex)) {
            $k = array_search($code, $stmarkex);
            if ($k !== false) {
                $kk = str_replace('_code', '', $k);
                $sub_sub = $stmarkex[$kk . '_sub'] ?? 0;
                $sub_obj = $stmarkex[$kk . '_obj'] ?? 0;
                $sub_pra = $stmarkex[$kk . '_pra'] ?? 0;
                $sub_ca = $stmarkex[$kk . '_ca'] ?? 0;
                $sub_total = $stmarkex[$kk . '_total'] ?? 0;
                $sub_gp = $stmarkex[$kk . '_gp'] ?? 0;
                $sub_gl = $stmarkex[$kk . '_gl'] ?? '';
            }
        }
    } else {
        // Single Paper or Standalone Subject (< 1000)
        for ($i = 1; $i <= 15; $i++) {
            if (isset($stmark['sub_' . $i]) && (string) $stmark['sub_' . $i] === (string) $code) {
                $sub_sub = $stmark['sub_' . $i . '_sub'] ?? 0;
                $sub_obj = $stmark['sub_' . $i . '_obj'] ?? 0;
                $sub_pra = $stmark['sub_' . $i . '_pra'] ?? 0;
                $sub_ca = $stmark['sub_' . $i . '_ca'] ?? 0;
                $sub_total = $stmark['sub_' . $i . '_total'] ?? 0;
                $sub_gp = $stmark['sub_' . $i . '_gp'] ?? 0;
                $sub_gl = $stmark['sub_' . $i . '_gl'] ?? '';
                break;
            }
        }
        $sub_fullmark = (int) ($subsetup_map[$code]['fullmarks'] ?? ($code == '154' ? 50 : 100));
    }

    if ((float) $sub_pra > 0)
        $has_pra = true;
    if ((float) $sub_ca > 0)
        $has_ca = true;

    $highest = $hmark_map[$code]['kkk'] ?? '';

    $sl++;
    $parsedRows[] = [
        'sl' => $sl,
        'code' => $code,
        'name_en' => $subnameEn,
        'name_bn' => $subnameBn,
        'fullmark' => $sub_fullmark,
        'cq' => $sub_sub,
        'mcq' => $sub_obj,
        'pra' => $sub_pra,
        'ca' => $sub_ca,
        'total' => $sub_total,
        'highest' => $highest,
        'gp' => $sub_gp,
        'gl' => $sub_gl,
        'is_fourth' => $is_fourth,
        'is_combined' => $is_combined
    ];
}
?>

<!-- ===== Marks Table (Template 1) ===== -->
<table class="marks-table" style="width:100%; border-collapse:collapse; border:1px solid #333; font-size:12.5px; z-index:5;" cellpadding="4">
    <thead style="background:#f4f6f8; font-weight:bold; text-align:center; border-bottom:1.5px solid #333;">
        <tr>
            <th style="width:30px; border:1px solid #333;">#</th>
            <th style="text-align:left; border:1px solid #333; padding-left:8px;">Name of Subjects</th>
            <th style="width:45px; border:1px solid #333;">Full</th>
            <th style="width:45px; border:1px solid #333;">CQ</th>
            <th style="width:45px; border:1px solid #333;">MCQ</th>
            <?php if ($has_pra): ?>
                <th style="width:45px; border:1px solid #333;">Pra</th>
            <?php endif; ?>
            <?php if ($has_ca): ?>
                <th style="width:45px; border:1px solid #333;">CA</th>
            <?php endif; ?>
            <th style="width:55px; border:1px solid #333;">Total</th>
            <th style="width:50px; border:1px solid #333;">Highest</th>
            <th style="width:45px; border:1px solid #333;">GP</th>
            <th style="width:45px; border:1px solid #333;">Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parsedRows as $r):
            $isComb = $r['is_combined'];
            $is4th = $r['is_fourth'];
            $rowStyle = $isComb ? 'background-color:#f9fbff; font-weight:600;' : ($is4th ? 'background-color:#fcfdf4;' : '');
            
            $subDisplayName = $engname ? $r['name_en'] : ($r['name_bn'] ?: $r['name_en']);
            if ($is4th) {
                $subDisplayName .= ' <span style="font-size:10px; color:#666; font-style:italic;">(4th Subject)</span>';
            } elseif ($isComb) {
                $subDisplayName = '<b>' . $subDisplayName . '</b>';
            }

            $glStyle = ($r['gl'] === 'F') ? 'color:red; font-weight:bold;' : (($r['gp'] == 5) ? 'color:#28a745; font-weight:bold;' : '');
            ?>
            <tr style="<?= $rowStyle ?> border-bottom:1px solid #ccc;">
                <td align="center" style="border:1px solid #333;"><?= $r['sl'] ?></td>
                <td style="border:1px solid #333; padding-left:8px;"><?= $subDisplayName ?></td>
                <td align="center" style="border:1px solid #333;"><?= $r['fullmark'] ?></td>
                <td align="center" style="border:1px solid #333;"><?= ($r['cq'] > 0 || !$isComb) ? $r['cq'] : '—' ?></td>
                <td align="center" style="border:1px solid #333;"><?= ($r['mcq'] > 0 || !$isComb) ? $r['mcq'] : '—' ?></td>
                <?php if ($has_pra): ?>
                    <td align="center" style="border:1px solid #333;"><?= ($r['pra'] > 0) ? $r['pra'] : '—' ?></td>
                <?php endif; ?>
                <?php if ($has_ca): ?>
                    <td align="center" style="border:1px solid #333;"><?= ($r['ca'] > 0) ? $r['ca'] : '—' ?></td>
                <?php endif; ?>
                <td align="center" style="border:1px solid #333; font-weight:bold;"><?= $r['total'] ?></td>
                <td align="center" style="border:1px solid #333; color:#555;"><?= $r['highest'] ?: '—' ?></td>
                <td align="center" style="border:1px solid #333;"><?= sprintf('%0.2f', (float)$r['gp']) ?></td>
                <td align="center" style="border:1px solid #333; <?= $glStyle ?>"><?= htmlspecialchars($r['gl']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>

    <!-- ===== Totals Summary Footer ===== -->
    <tfoot style="background:#f4f6f8; font-weight:bold; border-top:1.5px solid #333;">
        <?php
        $extraCols = ($has_pra ? 1 : 0) + ($has_ca ? 1 : 0);
        $totalColspan = 4 + $extraCols;
        ?>
        <tr>
            <td colspan="2" align="right" style="border:1px solid #333; padding-right:10px;">Grand Total:</td>
            <td align="center" style="border:1px solid #333;"><?= $fullmarks ?: '—' ?></td>
            <td colspan="<?= $extraCols + 2 ?>" align="center" style="border:1px solid #333; color:#777; font-size:11px;">—</td>
            <td align="center" style="border:1px solid #333; font-size:13px; color:#000;"><?= $totalmarks ?></td>
            <td align="center" style="border:1px solid #333; font-size:11px;"><?= $hmarktot ?: '—' ?></td>
            <td align="center" style="border:1px solid #333; font-size:13px;"><?= sprintf('%0.2f', (float)($gpa ?? 0)) ?></td>
            <td align="center" style="border:1px solid #333; font-size:14px; color:<?= $clc ?>"><?= htmlspecialchars($gla ?? '') ?></td>
        </tr>
    </tfoot>
</table>
