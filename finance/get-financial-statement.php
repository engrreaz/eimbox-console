<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/global_values.php';

$date_from = trim($_POST['date_from'] ?? '');
$date_to = trim($_POST['date_to'] ?? '');
$slot = trim($_POST['slot'] ?? '');
$session = trim($_POST['session'] ?? '');
$status_filter = isset($_POST['status_filter']) && $_POST['status_filter'] !== '' ? intval($_POST['status_filter']) : 1; // Default 1 = Sanctioned Only

$filter_basis = trim($_POST['filter_basis'] ?? 'month_year');
$month_param = trim($_POST['month_param'] ?? date('Y-m'));

$date_from = mysqli_real_escape_string($conn, $date_from);
$date_to = mysqli_real_escape_string($conn, $date_to);
$slot = mysqli_real_escape_string($conn, $slot);
$session = mysqli_real_escape_string($conn, $session);

// Determine Period Where Clause based on Filter Basis
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
    $period_display_label = "Final Bill Month: <strong>$month_name $filter_year</strong>";
    if (empty($date_from) || empty($date_to)) {
        $date_from = sprintf('%04d-%02d-01', $filter_year, $filter_month);
        $date_to = date('Y-m-t', strtotime($date_from));
    }
} else {
    $period_where = "c.date BETWEEN '$date_from' AND '$date_to'";
    $period_display_label = "Period: <strong>" . date('d M, Y', strtotime($date_from)) . "</strong> to <strong>" . date('d M, Y', strtotime($date_to)) . "</strong>";
}

// Build SQL filters
$slot_filter = ($slot !== '' && $slot !== 'All') ? " AND (c.slots = '$slot' OR c.slots IS NULL OR c.slots = '')" : "";
$session_filter = ($session !== '') ? " AND (c.sessionyear = '$session' OR c.sessionyear IS NULL OR c.sessionyear = '')" : "";
$status_clause = ($status_filter >= 0) ? " AND c.status = '$status_filter'" : "";

// ===================================================
// 1. SYNC STUDENT COLLECTIONS TO CASHBOOK WITH COA HEADS
// ===================================================
// Map financesetup sub_head to stfinance
if ($session !== '' && $slot !== '') {
    $sql_fs = "SELECT itemcode, sub_head 
               FROM financesetup 
               WHERE sccode = '$sccode' 
               AND sessionyear = '$session' 
               AND slot = '$slot' 
               AND sub_head > 0";
    $res_fs = mysqli_query($conn, $sql_fs);
    if ($res_fs) {
        while ($rf = mysqli_fetch_assoc($res_fs)) {
            $itemcode = mysqli_real_escape_string($conn, $rf['itemcode']);
            $sub_head_id = intval($rf['sub_head']);
            $sql_u = "UPDATE stfinance 
                      SET sub_head = '$sub_head_id' 
                      WHERE itemcode = '$itemcode' 
                      AND sccode = '$sccode' 
                      AND sessionyear = '$session' 
                      AND pr1date BETWEEN '$date_from' AND '$date_to'";
            mysqli_query($conn, $sql_u);
        }
    }
}

// Fetch map of sub_head => account_head_id
$acc_head_list = [];
$res_map = mysqli_query($conn, "SELECT id, account_head_id FROM account_sub_head WHERE sccode = '$sccode'");
if ($res_map) {
    while ($r = mysqli_fetch_assoc($res_map)) {
        $acc_head_list[$r['id']] = intval($r['account_head_id']);
    }
}

// Delete previously generated auto-collection entries for this period to refresh
$raw_slot_filter = ($slot !== '' && $slot !== 'All') ? " AND (slots = '$slot' OR slots IS NULL OR slots = '')" : "";
$sql_del = "DELETE FROM cashbook 
            WHERE date BETWEEN '$date_from' AND '$date_to' 
            AND sccode = '$sccode' 
            AND module = 'Collection' $raw_slot_filter";
mysqli_query($conn, $sql_del);

// Insert aggregated collection entries from stfinance into cashbook
$sql_col = "SELECT 
                pr1date,
                itemcode,
                particulareng,
                sub_head,
                classname,
                sectionname,
                SUM(pr1) AS total_pr1
            FROM stfinance
            WHERE sccode = '$sccode'
            AND pr1date BETWEEN '$date_from' AND '$date_to'
            AND pr1 > 0
            GROUP BY pr1date, itemcode, particulareng, sub_head, classname, sectionname";
$res_col = mysqli_query($conn, $sql_col);

if ($res_col) {
    while ($rc = mysqli_fetch_assoc($res_col)) {
        $pr1date = mysqli_real_escape_string($conn, $rc['pr1date']);
        $parti = mysqli_real_escape_string($conn, $rc['particulareng']);
        $sub_head = intval($rc['sub_head']);
        $class = mysqli_real_escape_string($conn, $rc['classname']);
        $section = mysqli_real_escape_string($conn, $rc['sectionname']);
        $amount = floatval($rc['total_pr1']);

        $particular = $parti . ' - ' . $class . ' (' . $section . ')';
        $acc_head = $acc_head_list[$sub_head] ?? 0;

        // Auto-collections from student fees are marked sanctioned (status = 1)
        $sql_ins = "INSERT INTO cashbook 
            (date, sccode, module, account_head, account_sub_head, partid, amount, income, expenditure, type, slots, sessionyear, particulars, entryby, entrytime, status)
            VALUES 
            ('$pr1date', '$sccode', 'Collection', '$acc_head', '$sub_head', '$sub_head', '$amount', '$amount', 0, 'Income', '$slot', '$session', '$particular', '$usr', NOW(), 1)";
        mysqli_query($conn, $sql_ins);
    }
}

// Internal cashbook column alignment
$conn->query("UPDATE cashbook SET income = amount, expenditure = 0 WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND type = 'Income'");
$conn->query("UPDATE cashbook SET expenditure = amount, income = 0 WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND type = 'Expenditure'");
$conn->query("UPDATE cashbook SET status = 1 WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND status IS NULL");

// ===================================================
// 2. QUERY COA STATEMENT (HEAD & SUB-HEAD WISE)
// ===================================================
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

// ===================================================
// 3. QUERY BANK ACCOUNTS & CASH IN HAND
// ===================================================
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

// Cash in hand (transactions with payment_method = 'cash' or bank_account_id is null/0)
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

// ===================================================
// 4. PENDING VOUCHERS COUNT & AMOUNT
// ===================================================
$sql_pend = "SELECT COUNT(id) AS pend_count, SUM(amount) AS pend_total 
             FROM cashbook c 
             WHERE c.sccode = '$sccode' 
             AND c.status = 0 $slot_filter $session_filter";
$res_pend = mysqli_query($conn, $sql_pend);
$pend_row = $res_pend ? mysqli_fetch_assoc($res_pend) : [];
$pending_count = intval($pend_row['pend_count'] ?? 0);
$pending_total = floatval($pend_row['pend_total'] ?? 0);
?>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .layout-navbar, .layout-menu, .content-footer, .card-header.border-bottom.mb-4, .d-print-none, .row.g-3.mb-4 {
        display: none !important;
    }
    #printableFinancialStatement, #printableFinancialStatement * {
        visibility: visible;
    }
    #printableFinancialStatement {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .card-header {
        background: transparent !important;
        border-bottom: 2px solid #000 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .card-body {
        padding: 0 !important;
    }
    .table-bordered, .table-bordered th, .table-bordered td {
        border: 1px solid #000 !important;
    }
    .table-success, .table-danger, .table-light, .table-warning, .bg-light {
        background-color: transparent !important;
    }
}
</style>

<!-- Financial KPI Summary Cards -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold">Total Revenue (Cr)</small>
                        <h4 class="fw-bold mb-0 text-white mt-1">৳<?= number_format($total_income_all, 2) ?></h4>
                    </div>
                    <div class="avatar bg-white bg-opacity-25 rounded-circle p-2 text-white">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold">Total Expense (Dr)</small>
                        <h4 class="fw-bold mb-0 text-white mt-1">৳<?= number_format($total_expense_all, 2) ?></h4>
                    </div>
                    <div class="avatar bg-white bg-opacity-25 rounded-circle p-2 text-white">
                        <i class="bi bi-graph-down-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm <?= ($net_surplus >= 0) ? 'bg-success' : 'bg-warning' ?> text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold">Net Operating Surplus</small>
                        <h4 class="fw-bold mb-0 text-white mt-1">৳<?= number_format($net_surplus, 2) ?></h4>
                    </div>
                    <div class="avatar bg-white bg-opacity-25 rounded-circle p-2 text-white">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm bg-secondary text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50 text-uppercase fw-semibold">Pending Approval</small>
                        <h4 class="fw-bold mb-0 text-white mt-1"><?= $pending_count ?> Vouchers</h4>
                        <small class="text-white-50">Total: ৳<?= number_format($pending_total, 2) ?></small>
                    </div>
                    <div class="avatar bg-white bg-opacity-25 rounded-circle p-2 text-white">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Statement Card -->
<div class="card border-0 shadow-sm mb-4" id="printableFinancialStatement">
    
    <!-- Printable Letterhead Header (Only visible during print) -->
    <div class="d-none d-print-block text-center mb-3 pt-3">
        <?php include dirname(__DIR__) . '/templete/letter-head-01.php'; ?>
        <hr style="border-top: 2px solid #000; margin-top: 15px; margin-bottom: 15px;">
    </div>

    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 bg-light">
        <div>
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-bank me-2"></i>Comprehensive Financial Statement (COA)
            </h5>
            <small class="text-muted">
                Reporting Period: <?= $period_display_label ?>
                | Filter Status: <strong><?= ($status_filter === 1) ? 'Sanctioned Vouchers Only' : (($status_filter === 0) ? 'Pending Vouchers Only' : 'All Vouchers') ?></strong>
            </small>
        </div>
        <div class="d-print-none d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Statement
            </button>
            <a href="pdf/financial-statement-pdf.php?filter_basis=<?= urlencode($filter_basis) ?>&month_param=<?= urlencode($month_param) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&slot=<?= urlencode($slot) ?>&session=<?= urlencode($session) ?>&status_filter=<?= urlencode($status_filter) ?>" target="_blank" class="btn btn-danger btn-sm px-3 shadow-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        
        <!-- SECTION 1: INCOME STATEMENT -->
        <div class="mb-5">
            <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
                <i class="bi bi-arrow-down-left-circle me-2"></i>1. REVENUE & INCOME HEADS (Cr)
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th>Account Head (COA)</th>
                            <th>Sub-Sector / Item</th>
                            <th class="text-center" style="width: 120px;">Vouchers</th>
                            <th class="text-end" style="width: 200px;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($income_heads)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No revenue items recorded for this period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($income_heads as $h): ?>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-primary">
                                        <i class="bi bi-folder-fill me-2"></i><?= htmlspecialchars($h['head_name']) ?>
                                    </td>
                                    <td class="text-end text-success fs-6">৳<?= number_format($h['total_income'], 2) ?></td>
                                </tr>
                                <?php foreach ($h['sub_heads'] as $sh): ?>
                                    <tr>
                                        <td></td>
                                        <td class="ps-4 fw-semibold text-secondary">
                                            <i class="bi bi-arrow-return-right me-2 text-muted"></i><?= htmlspecialchars($sh['sub_head_name']) ?>
                                        </td>
                                        <td class="text-center"><span class="badge bg-label-secondary"><?= $sh['vouchers'] ?></span></td>
                                        <td class="text-end fw-bold text-success">৳<?= number_format($sh['income'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <tr class="table-success fw-bold">
                                <td colspan="3" class="text-uppercase text-end pe-3">Total Operating Revenue:</td>
                                <td class="text-end fs-6 text-success">৳<?= number_format($total_income_all, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: EXPENDITURE STATEMENT -->
        <div class="mb-5">
            <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">
                <i class="bi bi-arrow-up-right-circle me-2"></i>2. EXPENDITURE & COST HEADS (Dr)
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th>Account Head (COA)</th>
                            <th>Sub-Sector / Item</th>
                            <th class="text-center" style="width: 120px;">Vouchers</th>
                            <th class="text-end" style="width: 200px;">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expense_heads)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No expense items recorded for this period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expense_heads as $h): ?>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-danger">
                                        <i class="bi bi-folder-fill me-2"></i><?= htmlspecialchars($h['head_name']) ?>
                                    </td>
                                    <td class="text-end text-danger fs-6">৳<?= number_format($h['total_expense'], 2) ?></td>
                                </tr>
                                <?php foreach ($h['sub_heads'] as $sh): ?>
                                    <tr>
                                        <td></td>
                                        <td class="ps-4 fw-semibold text-secondary">
                                            <i class="bi bi-arrow-return-right me-2 text-muted"></i><?= htmlspecialchars($sh['sub_head_name']) ?>
                                        </td>
                                        <td class="text-center"><span class="badge bg-label-secondary"><?= $sh['vouchers'] ?></span></td>
                                        <td class="text-end fw-bold text-danger">৳<?= number_format($sh['expense'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <tr class="table-danger fw-bold">
                                <td colspan="3" class="text-uppercase text-end pe-3">Total Operating Expenses:</td>
                                <td class="text-end fs-6 text-danger">৳<?= number_format($total_expense_all, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 3: NET SURPLUS / DEFICIT SUMMARY -->
        <div class="p-3 bg-light rounded border mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-bold text-dark">NET OPERATING RESULT (Revenue - Expense)</h6>
                    <small class="text-muted">Calculated strictly from sanctioned cashbook entries and student receipts.</small>
                </div>
                <div class="text-end">
                    <span class="fs-4 fw-bold <?= ($net_surplus >= 0) ? 'text-success' : 'text-danger' ?>">
                        ৳<?= number_format($net_surplus, 2) ?>
                    </span>
                    <span class="badge <?= ($net_surplus >= 0) ? 'bg-success' : 'bg-danger' ?> ms-2">
                        <?= ($net_surplus >= 0) ? 'Surplus' : 'Deficit' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- SECTION 4: BANK & CASH ACCOUNTS SUMMARY -->
        <div>
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-building-check me-2"></i>3. BANK ACCOUNTS & CASH LIQUIDITY SUMMARY
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th>#</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>Branch / Type</th>
                            <th class="text-end">Opening (৳)</th>
                            <th class="text-end text-success">Deposits (৳)</th>
                            <th class="text-end text-danger">Withdrawals (৳)</th>
                            <th class="text-end">Estimated Balance (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cash-in-Hand Row -->
                        <tr class="table-warning fw-semibold">
                            <td class="text-center"><i class="bi bi-cash-stack text-warning"></i></td>
                            <td colspan="3"><strong>Cash-in-Hand (Physical Cash Vault)</strong></td>
                            <td class="text-end">—</td>
                            <td class="text-end text-success">৳<?= number_format($cash_in, 2) ?></td>
                            <td class="text-end text-danger">৳<?= number_format($cash_out, 2) ?></td>
                            <td class="text-end fw-bold text-dark">৳<?= number_format($cash_balance, 2) ?></td>
                        </tr>

                        <?php if (empty($banks)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-3 text-muted">No active bank accounts registered.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $sl = 1;
                            $total_open = 0;
                            $total_dep = 0;
                            $total_with = 0;
                            $total_est = 0;
                            foreach ($banks as $b): 
                                $op = floatval($b['opening_balance']);
                                $dep = floatval($b['period_deposits']);
                                $with = floatval($b['period_withdrawals']);
                                $est = $op + $dep - $with;

                                $total_open += $op;
                                $total_dep += $dep;
                                $total_with += $with;
                                $total_est += $est;
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $sl++ ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($b['bankname']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($b['accno']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($b['branch']) ?> (<?= htmlspecialchars($b['acctype']) ?>)</td>
                                    <td class="text-end text-muted">৳<?= number_format($op, 2) ?></td>
                                    <td class="text-end text-success">৳<?= number_format($dep, 2) ?></td>
                                    <td class="text-end text-danger">৳<?= number_format($with, 2) ?></td>
                                    <td class="text-end fw-bold text-dark">৳<?= number_format($est, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="4" class="text-end text-uppercase pe-3">Total Bank Position:</td>
                                <td class="text-end">৳<?= number_format($total_open, 2) ?></td>
                                <td class="text-end text-success">৳<?= number_format($total_dep, 2) ?></td>
                                <td class="text-end text-danger">৳<?= number_format($total_with, 2) ?></td>
                                <td class="text-end text-primary fs-6">৳<?= number_format($total_est, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <!-- Signature Block for Official Reports (Print mode only) -->
        <div class="d-none d-print-block mt-5 pt-5">
            <div class="row text-center">
                <div class="col-4">
                    <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                        <strong>Prepared By</strong><br>
                        <small class="text-muted">Accountant / Cashier</small>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                        <strong>Verified By</strong><br>
                        <small class="text-muted">Accounts Officer / Audit</small>
                    </div>
                </div>
                <div class="col-4">
                    <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                        <strong>Approved By</strong><br>
                        <small class="text-muted">Head Teacher / Principal</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
