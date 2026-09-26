<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

$conn = db_connect();

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Please login to view payslip.</h3></div>";
    exit;
}

// Fetch Institute details
$sc_stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$sc_stmt->bind_param("i", $sccode);
$sc_stmt->execute();
$institute = $sc_stmt->get_result()->fetch_assoc();

$payslip_no = trim($_GET['payslip_no'] ?? '');
$batch_id = intval($_GET['batch_id'] ?? 0);
$item_id = intval($_GET['id'] ?? 0);

$items = [];

if (!empty($payslip_no)) {
    $stmt = $conn->prepare("SELECT d.*, t.tname, t.position, t.slots, t.mobile, t.accno as t_accno, t.bankname as t_bankname, t.branch as t_branch, t.routing as t_routing, t.accnosch as t_accnosch, t.bnamesch as t_bnamesch, t.accnopf as t_accnopf, t.bnamepf as t_bnamepf, b.batch_title
        FROM teacher_salary_disbursement d
        LEFT JOIN teacher t ON d.tid = t.tid AND d.sccode = t.sccode
        LEFT JOIN teacher_payroll_batch b ON d.batch_id = b.id AND d.sccode = b.sccode
        WHERE d.payslip_no = ? AND d.sccode = ?");
    $stmt->bind_param("si", $payslip_no, $sccode);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($item_id > 0) {
    $stmt = $conn->prepare("SELECT d.*, t.tname, t.position, t.slots, t.mobile, t.accno as t_accno, t.bankname as t_bankname, t.branch as t_branch, t.routing as t_routing, t.accnosch as t_accnosch, t.bnamesch as t_bnamesch, t.accnopf as t_accnopf, t.bnamepf as t_bnamepf, b.batch_title
        FROM teacher_salary_disbursement d
        LEFT JOIN teacher t ON d.tid = t.tid AND d.sccode = t.sccode
        LEFT JOIN teacher_payroll_batch b ON d.batch_id = b.id AND d.sccode = b.sccode
        WHERE d.id = ? AND d.sccode = ?");
    $stmt->bind_param("ii", $item_id, $sccode);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($batch_id > 0) {
    $stmt = $conn->prepare("SELECT d.*, t.tname, t.position, t.slots, t.mobile, t.accno as t_accno, t.bankname as t_bankname, t.branch as t_branch, t.routing as t_routing, t.accnosch as t_accnosch, t.bnamesch as t_bnamesch, t.accnopf as t_accnopf, t.bnamepf as t_bnamepf, b.batch_title
        FROM teacher_salary_disbursement d
        LEFT JOIN teacher t ON d.tid = t.tid AND d.sccode = t.sccode
        LEFT JOIN teacher_payroll_batch b ON d.batch_id = b.id AND d.sccode = b.sccode
        WHERE d.batch_id = ? AND d.sccode = ?
        ORDER BY CAST(t.ranks AS UNSIGNED) ASC, CAST(d.tid AS UNSIGNED) ASC");
    $stmt->bind_param("ii", $batch_id, $sccode);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if (empty($items)) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>No payslip data found.</h3></div>";
    exit;
}

$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

function convertNumberToWords($number) {
    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Forty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        100000              => 'Lakh',
        10000000            => 'Crore'
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
    <title>Teacher Salary Payslip - <?= htmlspecialchars($institute['scname'] ?? 'EIMBox') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #333;
        }
        .payslip-container {
            max-width: 860px;
            margin: 25px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }
        .inst-header {
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .payslip-table th, .payslip-table td {
            padding: 5px 8px;
            font-size: 0.86rem;
        }
        .table-heading {
            background-color: #f1f5f9;
            font-weight: 700;
            font-size: 0.88rem;
        }
        .signature-box {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature-line {
            width: 170px;
            border-top: 1px dashed #555;
            padding-top: 5px;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .no-print-bar {
            background: #2b3445;
            padding: 10px 20px;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .payslip-container {
                box-shadow: none;
                border: 1px solid #ccc;
                margin: 0 auto;
                padding: 20px;
                page-break-inside: avoid;
            }
            .page-break {
                page-break-after: always;
                height: 0;
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar d-flex justify-content-between align-items-center mb-3">
    <div>
        <strong><i class="bi bi-file-earmark-text me-1"></i> EIMBox Teacher Salary Pay Slip Viewer</strong>
        <span class="ms-2 text-white-50">Total Payslips: <?= count($items) ?></span>
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

<?php foreach ($items as $idx => $item): ?>
    <?php
        $month_label = ($month_names[$item['salary_month']] ?? '') . ' ' . $item['salary_year'];
        $govt_earnings = floatval($item['govt_gross']);
        $school_earnings = floatval($item['school_gross']);
        $total_earnings = $govt_earnings + $school_earnings;

        $govt_deduct = floatval($item['govt_welfare']) + floatval($item['govt_retire']);
        $school_deduct = floatval($item['school_pf']) + floatval($item['school_fine_absent']) + floatval($item['school_advance_deduct']) + floatval($item['school_deductions_other']);
        $total_deductions = $govt_deduct + $school_deduct;

        $net_payable = floatval($item['total_net_payable']);
    ?>

    <div class="payslip-container">
        <!-- Institution Header -->
        <div class="inst-header text-center position-relative">
            <h4 class="fw-bold mb-1 text-uppercase text-dark"><?= htmlspecialchars($institute['scname'] ?? 'EDUCATIONAL INSTITUTION') ?></h4>
            <p class="mb-1 small text-muted">
                <?= htmlspecialchars($institute['scadd1'] ?? '') ?> <?= htmlspecialchars($institute['scadd2'] ?? '') ?>, <?= htmlspecialchars($institute['ps'] ?? '') ?>, <?= htmlspecialchars($institute['dist'] ?? '') ?>
                <?php if (!empty($institute['mobile'])): ?> | Phone: <?= htmlspecialchars($institute['mobile']) ?><?php endif; ?>
            </p>
            <div class="badge bg-dark px-3 py-2 text-uppercase fs-6 mt-1">
                SALARY &amp; REMUNERATION PAY SLIP &mdash; <?= strtoupper($month_label) ?>
            </div>
        </div>

        <!-- Employee & Slip Info Bar -->
        <div class="row g-2 mb-3 small bg-light p-2 rounded border">
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Employee Name:</span>
                <strong class="text-dark"><?= htmlspecialchars($item['tname'] ?? 'Teacher') ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Teacher / Staff ID:</span>
                <strong><?= htmlspecialchars($item['tid']) ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Designation:</span>
                <strong><?= htmlspecialchars($item['position'] ?? 'Faculty') ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted d-block">Pay Slip No:</span>
                <strong class="text-primary"><?= htmlspecialchars($item['payslip_no']) ?></strong>
            </div>
            
            <div class="col-6 col-md-3 border-top pt-2">
                <span class="text-muted d-block">MPO Index / Pay Scale:</span>
                <span>Scale: Grade-<?= $item['payscale'] ?: 'N/A' ?></span>
            </div>
            <div class="col-6 col-md-3 border-top pt-2">
                <span class="text-muted d-block">EFT / MPO Bank Acc:</span>
                <span><?= htmlspecialchars($item['t_accno'] ?: 'Not Specified') ?> (<?= htmlspecialchars($item['t_bankname'] ?: 'Govt EFT') ?>)</span>
            </div>
            <div class="col-6 col-md-3 border-top pt-2">
                <span class="text-muted d-block">School Bank Acc:</span>
                <span><?= htmlspecialchars($item['t_accnosch'] ?: 'School Fund') ?></span>
            </div>
            <div class="col-6 col-md-3 border-top pt-2">
                <span class="text-muted d-block">Payment Status:</span>
                <span class="badge bg-<?= $item['payment_status'] === 'Paid' ? 'success' : 'warning text-dark' ?>"><?= htmlspecialchars($item['payment_status']) ?></span>
            </div>
        </div>

        <!-- Earnings & Deductions Tables (Side by Side) -->
        <div class="row g-3">
            <!-- Left: Earnings -->
            <div class="col-6">
                <table class="table table-bordered payslip-table mb-0">
                    <thead>
                        <tr class="table-heading text-primary">
                            <th>Earnings / ভাতার খাত</th>
                            <th class="text-end" style="width: 100px;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Govt / MPO Part -->
                        <tr class="table-light"><td colspan="2" class="fw-bold text-info small py-1"><i class="bi bi-bank me-1"></i> A. Govt / MPO Salary</td></tr>
                        <tr><td>Basic Salary (মূল বেতন)</td><td class="text-end"><?= number_format($item['govt_basic'], 2) ?></td></tr>
                        <?php if ($item['govt_incentive'] > 0): ?><tr><td>Incentive (বিশেষ প্রণোদনা)</td><td class="text-end"><?= number_format($item['govt_incentive'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['govt_house'] > 0): ?><tr><td>House Rent (বাড়িভাড়া ভাতা)</td><td class="text-end"><?= number_format($item['govt_house'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['govt_medical'] > 0): ?><tr><td>Medical Allowance (চিকিৎসা ভাতা)</td><td class="text-end"><?= number_format($item['govt_medical'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['govt_arrea'] > 0): ?><tr><td>Arrear (বকেয়া ভাতা)</td><td class="text-end"><?= number_format($item['govt_arrea'], 2) ?></td></tr><?php endif; ?>
                        
                        <!-- School / Institutional Part -->
                        <tr class="table-light"><td colspan="2" class="fw-bold text-primary small py-1"><i class="bi bi-building me-1"></i> B. School Fund Salary</td></tr>
                        <?php if ($item['school_basic'] > 0): ?><tr><td>School Basic (প্রতিষ্ঠানিক মূল)</td><td class="text-end"><?= number_format($item['school_basic'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_mobilevata'] > 0): ?><tr><td>Mobile Allowance (মোবাইল ভাতা)</td><td class="text-end"><?= number_format($item['school_mobilevata'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_travel'] > 0): ?><tr><td>Travel Allowance (যাতায়াত ভাতা)</td><td class="text-end"><?= number_format($item['school_travel'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_medical'] > 0): ?><tr><td>Medical Allowance-2 (চিকিৎসা-২)</td><td class="text-end"><?= number_format($item['school_medical'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_exam'] > 0): ?><tr><td>Exam Honorarium (পরীক্ষার সম্মানী)</td><td class="text-end"><?= number_format($item['school_exam'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_festival'] > 0): ?><tr><td>Festival Bonus (উৎসব ভাতা)</td><td class="text-end"><?= number_format($item['school_festival'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_allowances_other'] > 0): ?><tr><td>Special Bonus/Others (অন্যান্য ভাতা)</td><td class="text-end"><?= number_format($item['school_allowances_other'], 2) ?></td></tr><?php endif; ?>
                    </tbody>
                    <tfoot class="fw-bold table-light">
                        <tr>
                            <td>Total Gross Earnings:</td>
                            <td class="text-end text-success">৳ <?= number_format($total_earnings, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Right: Deductions -->
            <div class="col-6">
                <table class="table table-bordered payslip-table mb-0">
                    <thead>
                        <tr class="table-heading text-danger">
                            <th>Deductions / কর্তনের বিবরণ</th>
                            <th class="text-end" style="width: 100px;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Govt Deductions -->
                        <tr class="table-light"><td colspan="2" class="fw-bold text-info small py-1"><i class="bi bi-bank me-1"></i> A. Govt Deductions</td></tr>
                        <?php if ($item['govt_welfare'] > 0): ?><tr><td>Welfare Trust (কল্যাণ ট্রাস্ট কর্তন)</td><td class="text-end text-danger"><?= number_format($item['govt_welfare'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['govt_retire'] > 0): ?><tr><td>Retirement Benefit (অবসর সুবিধা)</td><td class="text-end text-danger"><?= number_format($item['govt_retire'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($govt_deduct == 0): ?><tr><td class="text-muted">None</td><td class="text-end">0.00</td></tr><?php endif; ?>

                        <!-- School Deductions -->
                        <tr class="table-light"><td colspan="2" class="fw-bold text-primary small py-1"><i class="bi bi-building me-1"></i> B. School Fund Deductions</td></tr>
                        <?php if ($item['school_pf'] > 0): ?><tr><td>Provident Fund (প্রভিডেন্ট ফান্ড)</td><td class="text-end text-danger"><?= number_format($item['school_pf'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_fine_absent'] > 0): ?><tr><td>Absent Fine (অনুপস্থিতি জরিমানা)</td><td class="text-end text-danger"><?= number_format($item['school_fine_absent'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_advance_deduct'] > 0): ?><tr><td>Advance Salary Recovery (অগ্রিম কর্তন)</td><td class="text-end text-danger"><?= number_format($item['school_advance_deduct'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($item['school_deductions_other'] > 0): ?><tr><td>Other Deductions (অন্যান্য কর্তন)</td><td class="text-end text-danger"><?= number_format($item['school_deductions_other'], 2) ?></td></tr><?php endif; ?>
                        <?php if ($school_deduct == 0): ?><tr><td class="text-muted">None</td><td class="text-end">0.00</td></tr><?php endif; ?>
                    </tbody>
                    <tfoot class="fw-bold table-light">
                        <tr>
                            <td>Total Deductions:</td>
                            <td class="text-end text-danger">৳ <?= number_format($total_deductions, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Net Payable Calculation Card -->
        <div class="mt-3 p-3 bg-light border rounded">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <span class="text-muted small">MPO Net: <strong>৳ <?= number_format($item['govt_net'], 2) ?></strong> | School Net: <strong>৳ <?= number_format($item['school_net'], 2) ?></strong></span>
                    <div class="small fw-bold text-dark mt-1">
                        In Words: <em><?= convertNumberToWords($net_payable) ?></em>
                    </div>
                </div>
                <div class="text-end mt-2 mt-md-0">
                    <span class="d-block small text-muted text-uppercase fw-bold">Net Salary Payable</span>
                    <h4 class="text-success fw-bold mb-0">৳ <?= number_format($net_payable, 2) ?></h4>
                </div>
            </div>
            <?php if (!empty($item['remarks'])): ?>
                <div class="mt-2 pt-2 border-top small text-muted">
                    <strong>Note / Remarks:</strong> <?= htmlspecialchars($item['remarks']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Signatures -->
        <div class="signature-box">
            <div class="signature-line">Prepared By / Accountant</div>
            <div class="signature-line">Checked By</div>
            <div class="signature-line">Headmaster / Principal</div>
        </div>
    </div>

    <?php if ($idx < count($items) - 1): ?>
        <div class="page-break"></div>
    <?php endif; ?>

<?php endforeach; ?>

</body>
</html>
