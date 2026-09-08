<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$type = intval($_POST['type'] ?? 1); // 1 = Detailed Ledger, 0 = Head-wise Summary, 2 = Date-wise Daily Summary
$recalculation = intval($_POST['recalculation'] ?? 1);

$slot = trim($_POST['slot'] ?? '');
$session = trim($_POST['session'] ?? '');
$date_from = trim($_POST['date_from'] ?? '');
$date_to = trim($_POST['date_to'] ?? '');

if (!$date_from || !$date_to) {
    echo "<div class='alert alert-danger shadow-sm'><i class='bi bi-exclamation-triangle me-2'></i>Invalid date range selected.</div>";
    exit;
}

$date_from = mysqli_real_escape_string($conn, $date_from);
$date_to = mysqli_real_escape_string($conn, $date_to);
$slot = mysqli_real_escape_string($conn, $slot);
$session = mysqli_real_escape_string($conn, $session);

// ==========================================
// 1. RECALCULATION & SYNC FROM STFINANCE
// ==========================================
if ($recalculation === 1) {
    // সাব-হেড ও প্যারেন্ট হেড ম্যাপিং
    $acc_head_list = [];
    $sql_map = "SELECT id, account_head_id FROM account_sub_head WHERE sccode = '$sccode'";
    $res_map = mysqli_query($conn, $sql_map);
    if ($res_map) {
        while ($r = mysqli_fetch_assoc($res_map)) {
            $acc_head_list[$r['id']] = intval($r['account_head_id']);
        }
    }

    // financesetup থেকে sub_head ম্যাপিং নিয়ে stfinance আপডেট (যদি session ও slot থাকে)
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

    // পূর্বের অটো-জেনারেটেড Collection ক্যাশবুক এন্ট্রি মুছে ফেলা
    $slot_del = ($slot !== '') ? " AND (slots = '$slot' OR slots IS NULL OR slots = '')" : "";
    $sql_del = "DELETE FROM cashbook 
                WHERE date BETWEEN '$date_from' AND '$date_to' 
                AND sccode = '$sccode' 
                AND module = 'Collection' $slot_del";
    mysqli_query($conn, $sql_del);

    // stfinance থেকে কালেকশন সামারি এনে ক্যাশবুকে যুক্ত করা
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

            $sql_ins = "INSERT INTO cashbook 
                (date, sccode, module, account_head, account_sub_head, partid, amount, income, expenditure, type, slots, sessionyear, particulars, entryby, entrytime)
                VALUES 
                ('$pr1date', '$sccode', 'Collection', '$acc_head', '$sub_head', '$sub_head', '$amount', '$amount', 0, 'Income', '$slot', '$session', '$particular', '$usr', NOW())";
            mysqli_query($conn, $sql_ins);
        }
    }
}

// ক্যাশবুক কলামগুলোর ইন্টারনাল সিনক্রোনাইজেশন
$conn->query("UPDATE cashbook SET partid = account_sub_head WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND account_sub_head > 0 AND (partid IS NULL OR partid = 0)");
$conn->query("UPDATE cashbook SET account_sub_head = partid WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND partid > 0 AND (account_sub_head IS NULL OR account_sub_head = 0)");
$conn->query("UPDATE cashbook SET income = amount, expenditure = 0 WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND type = 'Income'");
$conn->query("UPDATE cashbook SET expenditure = amount, income = 0 WHERE sccode = '$sccode' AND date BETWEEN '$date_from' AND '$date_to' AND type = 'Expenditure'");

// ==========================================
// 2. REPORT QUERIES BY REPORT TYPE
// ==========================================
$slot_filter = ($slot !== '' && $slot !== 'All') ? " AND (c.slots = '$slot' OR c.slots IS NULL OR c.slots = '')" : "";

if ($type === 1) {
    // টাইপ ১: Detailed Transaction Ledger View
    $sql_rep = "SELECT c.*, 
                       COALESCE(h.account_head, 'Unassigned') AS main_head_name,
                       COALESCE(s.sub_head, c.particulars, 'General') AS sub_head_name
                FROM cashbook c
                LEFT JOIN account_sub_head s ON (c.account_sub_head = s.id OR c.partid = s.id)
                LEFT JOIN account_head h ON (c.account_head = h.id OR s.account_head_id = h.id)
                WHERE c.sccode = '$sccode' 
                AND c.date BETWEEN '$date_from' AND '$date_to' $slot_filter
                ORDER BY c.date ASC, c.id ASC";
} elseif ($type === 0) {
    // টাইপ ০: Head-wise & Sub-Head Summary View
    $sql_rep = "SELECT COALESCE(h.id, 0) AS head_id,
                       COALESCE(h.account_head, 'General / Unassigned') AS main_head_name,
                       COALESCE(s.sub_head, 'General Items') AS sub_head_name,
                       SUM(c.income) AS total_income,
                       SUM(c.expenditure) AS total_expense,
                       SUM(c.amount) AS total_amount,
                       COUNT(c.id) AS voucher_count
                FROM cashbook c
                LEFT JOIN account_sub_head s ON (c.account_sub_head = s.id OR c.partid = s.id)
                LEFT JOIN account_head h ON (c.account_head = h.id OR s.account_head_id = h.id)
                WHERE c.sccode = '$sccode' 
                AND c.date BETWEEN '$date_from' AND '$date_to' $slot_filter
                GROUP BY h.id, h.account_head, s.id, s.sub_head
                ORDER BY main_head_name ASC, sub_head_name ASC";
} else {
    // টাইপ ২: Date-wise Daily Summary View
    $sql_rep = "SELECT c.date,
                       SUM(c.income) AS total_income,
                       SUM(c.expenditure) AS total_expense,
                       SUM(c.income) - SUM(c.expenditure) AS net_daily,
                       COUNT(c.id) AS voucher_count
                FROM cashbook c
                WHERE c.sccode = '$sccode' 
                AND c.date BETWEEN '$date_from' AND '$date_to' $slot_filter
                GROUP BY c.date
                ORDER BY c.date ASC";
}

$res_rep = mysqli_query($conn, $sql_rep);

if (!$res_rep) {
    echo "<div class='alert alert-danger shadow-sm'><i class='bi bi-x-circle me-2'></i>Report query failed: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}

$total_income = 0;
$total_expense = 0;
$rows = [];
while ($row = mysqli_fetch_assoc($res_rep)) {
    $rows[] = $row;
    if ($type === 1) {
        $total_income += floatval($row['income']);
        $total_expense += floatval($row['expenditure']);
    } else {
        $total_income += floatval($row['total_income'] ?? 0);
        $total_expense += floatval($row['total_expense'] ?? 0);
    }
}
$net_balance = $total_income - $total_expense;
?>

<div class="card shadow-sm border-0 mt-3" id="printableReportCard">
    <div class="card-header border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                <?php 
                if ($type === 1) echo "Detailed Cash Book Ledger";
                elseif ($type === 0) echo "Account Head-wise Summary Report";
                else echo "Date-wise Daily Cash Flow Summary";
                ?>
            </h5>
            <small class="text-muted">Period: <strong><?= date('d M, Y', strtotime($date_from)) ?></strong> to <strong><?= date('d M, Y', strtotime($date_to)) ?></strong></small>
        </div>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <?php if ($type === 1): ?>
                        <tr class="small text-uppercase">
                            <th style="width: 45px;">#</th>
                            <th>Date</th>
                            <th>Memo</th>
                            <th>Account Head</th>
                            <th>Sector (Sub-Head)</th>
                            <th>Particulars / Description</th>
                            <th class="text-end text-success">Income (Cr)</th>
                            <th class="text-end text-danger">Expense (Dr)</th>
                        </tr>
                    <?php elseif ($type === 0): ?>
                        <tr class="small text-uppercase">
                            <th style="width: 45px;">#</th>
                            <th>Primary Account Head</th>
                            <th>Sub-Sector</th>
                            <th class="text-center">Vouchers</th>
                            <th class="text-end text-success">Total Income (Cr)</th>
                            <th class="text-end text-danger">Total Expense (Dr)</th>
                            <th class="text-end">Balance (Cr - Dr)</th>
                        </tr>
                    <?php else: ?>
                        <tr class="small text-uppercase">
                            <th style="width: 45px;">#</th>
                            <th>Date</th>
                            <th class="text-center">Vouchers</th>
                            <th class="text-end text-success">Daily Income (Cr)</th>
                            <th class="text-end text-danger">Daily Expense (Dr)</th>
                            <th class="text-end">Daily Balance</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (count($rows) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                No transactions found for the selected period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $sl = 1;
                        foreach ($rows as $r): 
                        ?>
                            <tr>
                                <td class="text-muted small fw-bold text-center"><?= $sl++ ?></td>

                                <?php if ($type === 1): ?>
                                    <td class="text-nowrap fw-semibold"><?= date('d M, Y', strtotime($r['date'])) ?></td>
                                    <td><?= $r['memono'] ? '#' . htmlspecialchars($r['memono']) : '<span class="text-muted">—</span>' ?></td>
                                    <td>
                                        <span class="badge bg-label-primary"><i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($r['main_head_name']) ?></span>
                                    </td>
                                    <td class="fw-semibold text-secondary"><?= htmlspecialchars($r['sub_head_name']) ?></td>
                                    <td><?= htmlspecialchars($r['particulars'] ?: $r['sub_head_name']) ?></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= ($r['income'] > 0) ? number_format($r['income'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        <?= ($r['expenditure'] > 0) ? number_format($r['expenditure'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>

                                <?php elseif ($type === 0): ?>
                                    <td class="fw-bold text-primary">
                                        <i class="bi bi-folder-fill me-1"></i><?= htmlspecialchars($r['main_head_name']) ?>
                                    </td>
                                    <td class="fw-semibold text-body"><?= htmlspecialchars($r['sub_head_name']) ?></td>
                                    <td class="text-center"><span class="badge bg-label-secondary"><?= $r['voucher_count'] ?></span></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= ($r['total_income'] > 0) ? number_format($r['total_income'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        <?= ($r['total_expense'] > 0) ? number_format($r['total_expense'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end fw-bold <?= ($r['total_income'] - $r['total_expense'] >= 0) ? 'text-primary' : 'text-danger' ?>">
                                        <?= number_format($r['total_income'] - $r['total_expense'], 2) ?>
                                    </td>

                                <?php else: ?>
                                    <td class="fw-bold"><?= date('d M, Y (l)', strtotime($r['date'])) ?></td>
                                    <td class="text-center"><span class="badge bg-label-secondary"><?= $r['voucher_count'] ?></span></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= ($r['total_income'] > 0) ? number_format($r['total_income'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        <?= ($r['total_expense'] > 0) ? number_format($r['total_expense'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end fw-bold <?= ($r['net_daily'] >= 0) ? 'text-primary' : 'text-danger' ?>">
                                        <?= number_format($r['net_daily'], 2) ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Row -->
                        <tr class="table-light fw-bold">
                            <td colspan="<?= ($type === 1) ? '6' : (($type === 0) ? '4' : '3') ?>" class="text-end text-uppercase pe-3">
                                Grand Total:
                            </td>
                            <td class="text-end text-success fs-6">
                                ৳<?= number_format($total_income, 2) ?>
                            </td>
                            <td class="text-end text-danger fs-6">
                                ৳<?= number_format($total_expense, 2) ?>
                            </td>
                            <?php if ($type !== 1): ?>
                                <td class="text-end fs-6 <?= ($net_balance >= 0) ? 'text-primary' : 'text-danger' ?>">
                                    ৳<?= number_format($net_balance, 2) ?>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Net Closing Balance Banner -->
                        <tr class="table-primary fw-bold text-center">
                            <td colspan="<?= ($type === 1) ? '8' : '7' ?>" class="py-2">
                                <span class="me-3">Net Closing Balance (Total Inflow - Total Outflow):</span>
                                <span class="fs-5 fw-bold <?= ($net_balance >= 0) ? 'text-primary' : 'text-danger' ?>">
                                    ৳<?= number_format($net_balance, 2) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>