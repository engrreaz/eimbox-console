<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

$conn = db_connect();

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Please login to view payroll report.</h3></div>";
    exit;
}

$batch_id = intval($_GET['batch_id'] ?? 0);

if ($batch_id <= 0) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Invalid Payroll Batch ID.</h3></div>";
    exit;
}

// Fetch Batch Details
$b_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE id = ? AND sccode = ?");
$b_stmt->bind_param("ii", $batch_id, $sccode);
$b_stmt->execute();
$batch = $b_stmt->get_result()->fetch_assoc();

if (!$batch) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Payroll Batch not found.</h3></div>";
    exit;
}

// Fetch Institution Info
$sc_stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$sc_stmt->bind_param("i", $sccode);
$sc_stmt->execute();
$institute = $sc_stmt->get_result()->fetch_assoc();

// Fetch all disbursement line items joined with teacher metadata
$stmt = $conn->prepare("SELECT 
    d.*, 
    t.tname, 
    t.position, 
    t.ranks, 
    t.slots, 
    t.mobile,
    t.accno as t_accno,
    t.bankname as t_bankname,
    t.branch as t_branch,
    t.accnosch as t_accnosch,
    t.bnamesch as t_bnamesch
FROM teacher_salary_disbursement d
LEFT JOIN teacher t ON d.tid = t.tid AND d.sccode = t.sccode
WHERE d.batch_id = ? AND d.sccode = ?
ORDER BY CAST(t.ranks AS UNSIGNED) ASC, CAST(d.tid AS UNSIGNED) ASC");

$stmt->bind_param("ii", $batch_id, $sccode);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$month_text = ($month_names[$batch['salary_month']] ?? '') . ' ' . $batch['salary_year'];

function convertNumberToWords($number) {
    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety', 100 => 'Hundred', 1000 => 'Thousand', 100000 => 'Lakh', 10000000 => 'Crore'
    ];

    if (!is_numeric($number)) return '';
    $number = round($number, 2);
    if ($number < 0) return $negative . convertNumberToWords(abs($number));

    $string = $fraction = null;
    if (strpos((string)$number, '.') !== false) {
        list($number, $fraction) = explode('.', (string)$number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number] ?? '';
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . convertNumberToWords($remainder);
            break;
        case $number < 100000:
            $thousands   = (int)($number / 1000);
            $remainder = $number % 1000;
            $string = convertNumberToWords($thousands) . ' ' . $dictionary[1000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
        case $number < 10000000:
            $lakhs     = (int)($number / 100000);
            $remainder = $number % 100000;
            $string = convertNumberToWords($lakhs) . ' ' . $dictionary[100000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
        default:
            $crores    = (int)($number / 10000000);
            $remainder = $number % 10000000;
            $string = convertNumberToWords($crores) . ' ' . $dictionary[10000000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
    }

    if (null !== $fraction && is_numeric($fraction) && (int)$fraction > 0) {
        $string .= ' and ' . convertNumberToWords((int)$fraction) . ' Paisa';
    }

    return $string . ' Taka Only';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Teacher Payroll Summary Report - <?= htmlspecialchars($month_text) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 6mm 10mm 6mm;
        }
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #111;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .report-wrapper {
            background: #fff;
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        .inst-header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .report-table th, .report-table td {
            border: 1px solid #777;
            padding: 3px 4px;
            vertical-align: middle;
        }
        .report-table th {
            background-color: #eaeef3 !important;
            font-weight: 700;
            text-align: center;
        }
        .bg-mpo-head {
            background-color: #e0f2fe !important;
            color: #0369a1;
        }
        .bg-school-head {
            background-color: #ede9fe !important;
            color: #5b21b6;
        }
        .bg-total-head {
            background-color: #dcfce7 !important;
            color: #15803d;
        }
        .no-print-bar {
            background: #1e293b;
            padding: 10px 20px;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .signature-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 180px;
        }
        .sig-line {
            border-top: 1px dashed #333;
            padding-top: 4px;
            font-weight: 600;
            font-size: 10px;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print-bar {
                display: none !important;
            }
            .report-wrapper {
                padding: 0;
            }
            .report-table th, .report-table td {
                border-color: #444 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar d-flex justify-content-between align-items-center">
    <div>
        <strong><i class="bi bi-file-earmark-spreadsheet me-1"></i> Teacher Payroll Summary Report (1-2 Pages Short View)</strong>
        <span class="ms-2 text-white-50">Batch: <?= htmlspecialchars($batch['batch_title']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-light fw-bold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
        </button>
        <button class="btn btn-sm btn-outline-light" onclick="window.close()">
            <i class="bi bi-x-lg me-1"></i> Close
        </button>
    </div>
</div>

<div class="report-wrapper">
    <!-- Header -->
    <div class="inst-header">
        <h4 class="fw-bold mb-0 text-uppercase"><?= htmlspecialchars($institute['scname'] ?? 'EDUCATIONAL INSTITUTION') ?></h4>
        <div class="small text-muted">
            <?= htmlspecialchars($institute['scadd1'] ?? '') ?> <?= htmlspecialchars($institute['scadd2'] ?? '') ?>, <?= htmlspecialchars($institute['ps'] ?? '') ?>, <?= htmlspecialchars($institute['dist'] ?? '') ?>
            <?php if (!empty($institute['sccode'])): ?> | School Code: <?= htmlspecialchars($institute['sccode']) ?><?php endif; ?>
            <?php if (!empty($institute['mobile'])): ?> | Phone: <?= htmlspecialchars($institute['mobile']) ?><?php endif; ?>
        </div>
        <div class="fw-bold fs-6 mt-1 text-dark">
            TEACHER &amp; STAFF MONTHLY SALARY SUMMARY SHEET &mdash; <?= strtoupper($month_text) ?>
        </div>
        <div class="small text-secondary">
            Batch Status: <strong><?= strtoupper($batch['status']) ?></strong> | Total Staff: <strong><?= count($items) ?></strong> | Generated On: <?= date('d M, Y') ?>
        </div>
    </div>

    <!-- Data Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">#</th>
                <th rowspan="2" style="width: 50px;">ID</th>
                <th rowspan="2" style="width: 140px;" class="text-start">Teacher / Staff Name</th>
                <th rowspan="2" style="width: 90px;" class="text-start">Designation</th>
                <th rowspan="2" style="width: 110px;" class="text-start">Bank Details</th>
                <th colspan="4" class="bg-mpo-head">Govt / MPO Part (৳)</th>
                <th colspan="4" class="bg-school-head">School Fund Part (৳)</th>
                <th rowspan="2" class="bg-total-head" style="width: 75px;">Net Pay (৳)</th>
                <th rowspan="2" style="width: 90px;">Signature / Receipt</th>
            </tr>
            <tr>
                <!-- MPO -->
                <th class="bg-mpo-head" style="width: 50px;">Basic</th>
                <th class="bg-mpo-head" style="width: 50px;">Gross</th>
                <th class="bg-mpo-head text-danger" style="width: 45px;">Deduct</th>
                <th class="bg-mpo-head fw-bold" style="width: 55px;">MPO Net</th>
                
                <!-- School -->
                <th class="bg-school-head" style="width: 50px;">Basic</th>
                <th class="bg-school-head" style="width: 50px;">Gross</th>
                <th class="bg-school-head text-danger" style="width: 45px;">Deduct</th>
                <th class="bg-school-head fw-bold" style="width: 55px;">School Net</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="15" class="text-center py-4 text-muted">No teacher records found in this batch.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $idx => $it): ?>
                    <?php 
                        $govt_ded = floatval($it['govt_welfare']) + floatval($it['govt_retire']);
                        $school_ded = floatval($it['school_pf']) + floatval($it['school_fine_absent']) + floatval($it['school_advance_deduct']) + floatval($it['school_deductions_other']);
                        $bank_display = !empty($it['t_accnosch']) ? $it['t_accnosch'] : (!empty($it['t_accno']) ? $it['t_accno'] : '-');
                    ?>
                    <tr>
                        <td class="text-center text-muted"><?= $idx + 1 ?></td>
                        <td class="text-center fw-bold"><?= htmlspecialchars($it['tid']) ?></td>
                        <td class="fw-bold text-dark text-truncate" style="max-width: 140px;"><?= htmlspecialchars($it['tname'] ?? 'Faculty') ?></td>
                        <td class="text-muted text-truncate" style="max-width: 90px;"><?= htmlspecialchars($it['position'] ?? 'Teacher') ?></td>
                        <td class="small text-truncate" style="max-width: 110px;" title="<?= htmlspecialchars($bank_display) ?>">
                            <?= htmlspecialchars($bank_display) ?>
                        </td>

                        <!-- Govt MPO -->
                        <td class="text-end"><?= number_format($it['govt_basic'], 0) ?></td>
                        <td class="text-end"><?= number_format($it['govt_gross'], 0) ?></td>
                        <td class="text-end text-danger"><?= $govt_ded > 0 ? number_format($govt_ded, 0) : '-' ?></td>
                        <td class="text-end fw-bold text-primary"><?= number_format($it['govt_net'], 0) ?></td>

                        <!-- School Fund -->
                        <td class="text-end"><?= number_format($it['school_basic'], 0) ?></td>
                        <td class="text-end"><?= number_format($it['school_gross'], 0) ?></td>
                        <td class="text-end text-danger"><?= $school_ded > 0 ? number_format($school_ded, 0) : '-' ?></td>
                        <td class="text-end fw-bold text-primary"><?= number_format($it['school_net'], 0) ?></td>

                        <!-- Total Net -->
                        <td class="text-end fw-bold text-success bg-light fs-7">
                            <?= number_format($it['total_net_payable'], 2) ?>
                        </td>

                        <!-- Signature -->
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot class="fw-bold table-light">
            <tr style="background-color: #f1f5f9;">
                <td colspan="5" class="text-end text-uppercase">Total Amount (৳):</td>
                <td class="text-end"><?= number_format(array_sum(array_column($items, 'govt_basic')), 0) ?></td>
                <td class="text-end"><?= number_format($batch['total_govt_gross'], 0) ?></td>
                <td class="text-end text-danger"><?= number_format($batch['total_govt_deduction'], 0) ?></td>
                <td class="text-end text-primary fw-bold"><?= number_format($batch['total_govt_net'], 0) ?></td>
                
                <td class="text-end"><?= number_format(array_sum(array_column($items, 'school_basic')), 0) ?></td>
                <td class="text-end"><?= number_format($batch['total_school_gross'], 0) ?></td>
                <td class="text-end text-danger"><?= number_format($batch['total_school_deduction'], 0) ?></td>
                <td class="text-end text-primary fw-bold"><?= number_format($batch['total_school_net'], 0) ?></td>
                
                <td class="text-end text-success fs-6 bg-total-head">
                    ৳ <?= number_format($batch['grand_total_net'], 2) ?>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Amount in Words & Note -->
    <div class="mt-2 p-2 bg-light border d-flex justify-content-between align-items-center" style="font-size: 10.5px;">
        <div>
            <strong>In Words (সর্বমোট কথায়):</strong> <em><?= convertNumberToWords($batch['grand_total_net']) ?></em>
        </div>
        <div>
            <strong>Govt Net:</strong> ৳ <?= number_format($batch['total_govt_net'], 2) ?> | 
            <strong>School Net:</strong> ৳ <?= number_format($batch['total_school_net'], 2) ?>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="sig-block">
            <div class="sig-line">Prepared By (Accountant)</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Checked By / Audit</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Headmaster / Principal</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">President / Managing Committee</div>
        </div>
    </div>
</div>

</body>
</html>
