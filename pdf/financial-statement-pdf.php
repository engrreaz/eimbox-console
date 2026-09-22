<?php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/core-val.php';
require_once dirname(__DIR__) . '/core/global_values.php';
require_once dirname(__DIR__) . '/core/functions.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$slot = trim($_GET['slot'] ?? '');
$session = trim($_GET['session'] ?? '');
$status_filter = isset($_GET['status_filter']) && $_GET['status_filter'] !== '' ? intval($_GET['status_filter']) : 1;

$filter_basis = trim($_GET['filter_basis'] ?? 'month_year');
$month_param = trim($_GET['month_param'] ?? date('Y-m'));

$date_from = mysqli_real_escape_string($conn, $date_from);
$date_to = mysqli_real_escape_string($conn, $date_to);
$slot = mysqli_real_escape_string($conn, $slot);
$session = mysqli_real_escape_string($conn, $session);

// Determine Period Where Clause
$filter_month = 0;
$filter_year = 0;
if ($month_param && strpos($month_param, '-') !== false) {
    $m_parts = explode('-', $month_param);
    $filter_year = intval($m_parts[0]);
    $filter_month = intval($m_parts[1]);
} else {
    $filter_month = intval(date('n'));
    $filter_year = intval(date('Y'));
}

if ($filter_basis === 'month_year' && $filter_month > 0 && $filter_year > 0) {
    $period_where = "( (c.month = '$filter_month' AND c.year = '$filter_year') OR ((c.month IS NULL OR c.month = 0) AND MONTH(c.date) = '$filter_month' AND YEAR(c.date) = '$filter_year') )";
    $month_name = date('F', mktime(0, 0, 0, $filter_month, 1));
    $period_display_label = "Final Bill Month: $month_name $filter_year";
    if (empty($date_from) || empty($date_to)) {
        $date_from = sprintf('%04d-%02d-01', $filter_year, $filter_month);
        $date_to = date('Y-m-t', strtotime($date_from));
    }
} else {
    $period_where = "c.date BETWEEN '$date_from' AND '$date_to'";
    $period_display_label = "Period: " . date('d M, Y', strtotime($date_from)) . " to " . date('d M, Y', strtotime($date_to));
}

// SQL Filters
$slot_filter = ($slot !== '' && $slot !== 'All') ? " AND (c.slots = '$slot' OR c.slots IS NULL OR c.slots = '')" : "";
$session_filter = ($session !== '') ? " AND (c.sessionyear = '$session' OR c.sessionyear IS NULL OR c.sessionyear = '')" : "";
$status_clause = ($status_filter >= 0) ? " AND c.status = '$status_filter'" : "";

// COA Statement Query
$sql_coa = "SELECT 
                COALESCE(h.id, 0) AS head_id,
                COALESCE(h.account_head, s.account_head, 'General / Unassigned') AS main_head_name,
                COALESCE(s.id, 0) AS sub_head_id,
                COALESCE(s.sub_head, 'General Sub-head') AS sub_head_name,
                SUM(c.income) AS total_income,
                SUM(c.expenditure) AS total_expense,
                COUNT(c.id) AS voucher_count
            FROM cashbook c
            LEFT JOIN account_sub_head s ON (c.account_sub_head = s.id OR c.partid = s.id)
            LEFT JOIN account_head h ON (c.account_head = h.id OR s.account_head_id = h.id)
            WHERE c.sccode = '$sccode' 
            AND $period_where $slot_filter $session_filter $status_clause
            GROUP BY h.id, main_head_name, s.id, s.sub_head
            ORDER BY main_head_name ASC, sub_head_name ASC";

$res_coa = mysqli_query($conn, $sql_coa);

$income_heads = [];
$expense_heads = [];
$total_income_all = 0;
$total_expense_all = 0;

if ($res_coa) {
    while ($row = mysqli_fetch_assoc($res_coa)) {
        $inc = floatval($row['total_income']);
        $exp = floatval($row['total_expense']);
        
        $total_income_all += $inc;
        $total_expense_all += $exp;

        $head_key = $row['head_id'] ?: $row['main_head_name'];

        $item = [
            'sub_head_id' => $row['sub_head_id'],
            'sub_head_name' => $row['sub_head_name'],
            'income' => $inc,
            'expense' => $exp,
            'vouchers' => $row['voucher_count']
        ];

        if ($exp > $inc || ($exp > 0 && $inc == 0)) {
            if (!isset($expense_heads[$head_key])) {
                $expense_heads[$head_key] = [
                    'head_name' => $row['main_head_name'],
                    'sub_heads' => [],
                    'total_expense' => 0
                ];
            }
            $expense_heads[$head_key]['sub_heads'][] = $item;
            $expense_heads[$head_key]['total_expense'] += $exp;
        } else {
            if (!isset($income_heads[$head_key])) {
                $income_heads[$head_key] = [
                    'head_name' => $row['main_head_name'],
                    'sub_heads' => [],
                    'total_income' => 0
                ];
            }
            $income_heads[$head_key]['sub_heads'][] = $item;
            $income_heads[$head_key]['total_income'] += $inc;
        }
    }
}

$net_surplus = $total_income_all - $total_expense_all;

// Bank Accounts Query
$banks = [];
$sql_bank = "SELECT b.id, b.bankname, b.accno, b.acctype, b.branch, b.opening_balance,
                    COALESCE(SUM(CASE WHEN c.type = 'Income' THEN c.amount ELSE 0 END), 0) AS period_deposits,
                    COALESCE(SUM(CASE WHEN c.type = 'Expenditure' THEN c.amount ELSE 0 END), 0) AS period_withdrawals
             FROM bankinfo b
             LEFT JOIN cashbook c ON c.bank_account_id = b.id AND c.sccode = '$sccode' AND $period_where $status_clause
             WHERE b.sccode = '$sccode' AND b.status = 1
             GROUP BY b.id, b.bankname, b.accno, b.acctype, b.branch, b.opening_balance
             ORDER BY b.bankname ASC";
$res_bank = mysqli_query($conn, $sql_bank);
if ($res_bank) {
    while ($rb = mysqli_fetch_assoc($res_bank)) {
        $banks[] = $rb;
    }
}

// Cash in hand
$sql_cash = "SELECT 
                SUM(c.income) AS total_cash_in,
                SUM(c.expenditure) AS total_cash_out
             FROM cashbook c
             WHERE c.sccode = '$sccode' 
             AND $period_where
             AND (c.payment_method = 'cash' OR c.bank_account_id IS NULL OR c.bank_account_id = 0)
             $slot_filter $session_filter $status_clause";
$res_cash = mysqli_query($conn, $sql_cash);
$cash_row = $res_cash ? mysqli_fetch_assoc($res_cash) : [];
$cash_in = floatval($cash_row['total_cash_in'] ?? 0);
$cash_out = floatval($cash_row['total_cash_out'] ?? 0);
$cash_balance = $cash_in - $cash_out;

// Setup mPDF
$defaultConfig = (new ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/fonts']),
    'fontdata' => $fontData + [
        'solaimanlipi' => ['R' => 'SolaimanLipi.ttf']
    ],
    'default_font' => 'solaimanlipi',
    'margin_left' => 12,
    'margin_right' => 12,
    'margin_top' => 12,
    'margin_bottom' => 12
]);

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: solaimanlipi, Arial, sans-serif; font-size: 12px; color: #222; }
    h3, h4, h5 { margin: 4px 0; }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .text-success { color: #198754; }
    .text-danger { color: #dc3545; }
    .text-primary { color: #0d6efd; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .table th, .table td { border: 1px solid #444; padding: 5px 8px; }
    .table th { background-color: #f2f2f2; font-weight: bold; }
    .bg-light { background-color: #f8f9fa; }
    .bg-summary { background-color: #e9ecef; }
    .section-header { font-size: 14px; font-weight: bold; padding: 6px 0; margin-top: 15px; margin-bottom: 8px; border-bottom: 2px solid #333; }
    .signature-table { width: 100%; margin-top: 40px; border: none; }
    .signature-table td { border: none; text-align: center; width: 33%; padding-top: 35px; }
    .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 4px; font-weight: bold; }
</style>
</head>
<body>

<!-- Letterhead -->
<div class="text-center">
    <?php include dirname(__DIR__) . '/templete/letter-head-01.php'; ?>
</div>
<hr style="border: 0; border-top: 2px solid #000; margin: 10px 0;">

<div class="text-center" style="margin-bottom: 12px;">
    <h3 style="margin: 0; font-size: 16px;">Comprehensive Financial Statement</h3>
    <p style="margin: 2px 0; font-size: 11px; color: #555;">
        <?= $period_display_label ?> | Filter Status: <?= ($status_filter === 1) ? 'Sanctioned Vouchers Only' : (($status_filter === 0) ? 'Pending Only' : 'All Vouchers') ?>
    </p>
</div>

<!-- 1. Revenue & Income -->
<div class="section-header text-success">1. REVENUE & INCOME HEADS (Cr)</div>
<table class="table">
    <thead>
        <tr>
            <th>Account Head (COA)</th>
            <th>Sub-Sector / Item</th>
            <th class="text-center" style="width: 70px;">Vouchers</th>
            <th class="text-end" style="width: 130px;">Amount (৳)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($income_heads)): ?>
            <tr><td colspan="4" class="text-center" style="color:#777;">No revenue items recorded for this period.</td></tr>
        <?php else: ?>
            <?php foreach ($income_heads as $h): ?>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-primary"><?= htmlspecialchars($h['head_name']) ?></td>
                    <td class="text-end text-success">৳<?= number_format($h['total_income'], 2) ?></td>
                </tr>
                <?php foreach ($h['sub_heads'] as $sh): ?>
                    <tr>
                        <td></td>
                        <td style="padding-left: 20px;"><?= htmlspecialchars($sh['sub_head_name']) ?></td>
                        <td class="text-center"><?= $sh['vouchers'] ?></td>
                        <td class="text-end fw-bold text-success">৳<?= number_format($sh['income'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="bg-summary fw-bold">
                <td colspan="3" class="text-end">TOTAL OPERATING REVENUE:</td>
                <td class="text-end text-success">৳<?= number_format($total_income_all, 2) ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- 2. Expenditure & Cost -->
<div class="section-header text-danger">2. EXPENDITURE & COST HEADS (Dr)</div>
<table class="table">
    <thead>
        <tr>
            <th>Account Head (COA)</th>
            <th>Sub-Sector / Item</th>
            <th class="text-center" style="width: 70px;">Vouchers</th>
            <th class="text-end" style="width: 130px;">Amount (৳)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($expense_heads)): ?>
            <tr><td colspan="4" class="text-center" style="color:#777;">No expense items recorded for this period.</td></tr>
        <?php else: ?>
            <?php foreach ($expense_heads as $h): ?>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-danger"><?= htmlspecialchars($h['head_name']) ?></td>
                    <td class="text-end text-danger">৳<?= number_format($h['total_expense'], 2) ?></td>
                </tr>
                <?php foreach ($h['sub_heads'] as $sh): ?>
                    <tr>
                        <td></td>
                        <td style="padding-left: 20px;"><?= htmlspecialchars($sh['sub_head_name']) ?></td>
                        <td class="text-center"><?= $sh['vouchers'] ?></td>
                        <td class="text-end fw-bold text-danger">৳<?= number_format($sh['expense'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="bg-summary fw-bold">
                <td colspan="3" class="text-end">TOTAL OPERATING EXPENSES:</td>
                <td class="text-end text-danger">৳<?= number_format($total_expense_all, 2) ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- 3. Net Surplus / Deficit -->
<table class="table bg-light" style="margin-top: 10px;">
    <tr>
        <td><strong>NET OPERATING RESULT (Revenue - Expense)</strong></td>
        <td class="text-end fw-bold <?= ($net_surplus >= 0) ? 'text-success' : 'text-danger' ?>" style="font-size: 14px;">
            ৳<?= number_format($net_surplus, 2) ?> (<?= ($net_surplus >= 0) ? 'Surplus' : 'Deficit' ?>)
        </td>
    </tr>
</table>

<!-- 4. Bank Accounts & Cash Summary -->
<div class="section-header text-primary">3. BANK ACCOUNTS & CASH LIQUIDITY SUMMARY</div>
<table class="table">
    <thead>
        <tr>
            <th style="width: 25px;">#</th>
            <th>Bank Name</th>
            <th>Account No</th>
            <th>Branch / Type</th>
            <th class="text-end">Opening (৳)</th>
            <th class="text-end text-success">Deposits (৳)</th>
            <th class="text-end text-danger">Withdrawals (৳)</th>
            <th class="text-end">Balance (৳)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="bg-light fw-bold">
            <td class="text-center">*</td>
            <td colspan="3">Cash-in-Hand (Vault)</td>
            <td class="text-end">—</td>
            <td class="text-end text-success">৳<?= number_format($cash_in, 2) ?></td>
            <td class="text-end text-danger">৳<?= number_format($cash_out, 2) ?></td>
            <td class="text-end text-primary">৳<?= number_format($cash_balance, 2) ?></td>
        </tr>
        <?php if (empty($banks)): ?>
            <tr><td colspan="8" class="text-center" style="color:#777;">No active bank accounts registered.</td></tr>
        <?php else: ?>
            <?php 
            $sl = 1;
            $total_open = 0; $total_dep = 0; $total_with = 0; $total_est = 0;
            foreach ($banks as $b):
                $op = floatval($b['opening_balance']);
                $dep = floatval($b['period_deposits']);
                $with = floatval($b['period_withdrawals']);
                $est = $op + $dep - $with;

                $total_open += $op; $total_dep += $dep; $total_with += $with; $total_est += $est;
            ?>
                <tr>
                    <td class="text-center"><?= $sl++ ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($b['bankname']) ?></td>
                    <td><?= htmlspecialchars($b['accno']) ?></td>
                    <td><?= htmlspecialchars($b['branch']) ?> (<?= htmlspecialchars($b['acctype']) ?>)</td>
                    <td class="text-end">৳<?= number_format($op, 2) ?></td>
                    <td class="text-end text-success">৳<?= number_format($dep, 2) ?></td>
                    <td class="text-end text-danger">৳<?= number_format($with, 2) ?></td>
                    <td class="text-end fw-bold">৳<?= number_format($est, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="bg-summary fw-bold">
                <td colspan="4" class="text-end">TOTAL BANK POSITION:</td>
                <td class="text-end">৳<?= number_format($total_open, 2) ?></td>
                <td class="text-end text-success">৳<?= number_format($total_dep, 2) ?></td>
                <td class="text-end text-danger">৳<?= number_format($total_with, 2) ?></td>
                <td class="text-end text-primary">৳<?= number_format($total_est, 2) ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Signature Section -->
<table class="signature-table">
    <tr>
        <td>
            <div class="sig-line">Prepared By</div>
            <small style="color: #666;">Accountant / Cashier</small>
        </td>
        <td>
            <div class="sig-line">Verified By</div>
            <small style="color: #666;">Accounts Officer / Audit</small>
        </td>
        <td>
            <div class="sig-line">Approved By</div>
            <small style="color: #666;">Head Teacher / Principal</small>
        </td>
    </tr>
</table>

</body>
</html>
<?php
$html = ob_get_clean();
$fname = "Financial_Statement_" . date('Ymd_His') . ".pdf";

$mpdf->WriteHTML($html);
$mpdf->Output($fname, 'I');
?>
