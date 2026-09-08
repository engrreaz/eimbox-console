<?php
require_once 'header.php';

$alert_msg = '';
$alert_type = '';

// ==========================================
// 1. DATE RANGE & FILTER INITIALIZATION
// ==========================================
$default_from = date('Y-m-01');
$default_to = date('Y-m-t');

$date_from = $_GET['date_from'] ?? $default_from;
$date_to = $_GET['date_to'] ?? $default_to;
$filter_head = intval($_GET['filter_head'] ?? 0);
$filter_type = $_GET['filter_type'] ?? 'all';

// ==========================================
// 2. CRUD OPERATIONS (ADD / EDIT / DELETE / APPROVE)
// ==========================================

// ২.১ ভাউচার তৈরি বা আপডেট
if (isset($_POST['save_voucher'])) {
    $entry_id = intval($_POST['entry_id'] ?? 0);
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    $partid = intval($_POST['partid'] ?? 0); // account_sub_head.id
    $head_id = intval($_POST['head_code'] ?? 0); // account_head.id
    $particulars = trim($_POST['particulars'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $type = ($_POST['type'] === 'Income') ? 'Income' : 'Expenditure';
    $memono = intval($_POST['memono'] ?? 0);
    $entryby = $usr ?? 'Admin';

    $month = intval(date('n', strtotime($date)));
    $year = intval(date('Y', strtotime($date)));
    $target_session = $sessionyear ?? date('Y');

    // যদি head_id ফাঁকা থাকে তবে sub_head থেকে বের করা
    if ($head_id <= 0 && $partid > 0) {
        $chk_h = $conn->prepare("SELECT account_head_id FROM account_sub_head WHERE id = ? LIMIT 1");
        $chk_h->bind_param("i", $partid);
        $chk_h->execute();
        $res_h = $chk_h->get_result();
        if ($res_h && $res_h->num_rows > 0) {
            $head_id = intval($res_h->fetch_assoc()['account_head_id']);
        }
    }

    $inc_val = ($type === 'Income') ? $amount : 0;
    $exp_val = ($type === 'Expenditure') ? $amount : 0;

    if ($amount > 0) {
        if ($entry_id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE cashbook SET 
                date = ?, 
                account_head = ?, 
                account_sub_head = ?, 
                partid = ?, 
                particulars = ?, 
                amount = ?, 
                income = ?, 
                expenditure = ?, 
                type = ?, 
                memono = ?, 
                month = ?, 
                year = ?, 
                modifieddate = NOW() 
                WHERE id = ? AND (sccode = ? OR sccode = ?)");
            $sc_sanctioned = $sccode;
            $sc_pending = $sccode * 10;
            $stmt->bind_param("siiisddssiiiiii", $date, $head_id, $partid, $partid, $particulars, $amount, $inc_val, $exp_val, $type, $memono, $month, $year, $entry_id, $sc_sanctioned, $sc_pending);
            if ($stmt->execute()) {
                $alert_msg = "Voucher #$entry_id updated successfully.";
                $alert_type = "success";
            }
        } else {
            // New Insert (সরাসরি Sanctioned হিসেবে সেভ হবে)
            $stmt = $conn->prepare("INSERT INTO cashbook 
                (sccode, sessionyear, month, year, date, account_head, account_sub_head, partid, particulars, amount, income, expenditure, type, memono, entryby, entrytime) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isiisiisddssiis", $sccode, $target_session, $month, $year, $date, $head_id, $partid, $partid, $particulars, $amount, $inc_val, $exp_val, $type, $memono, $entryby);
            if ($stmt->execute()) {
                $alert_msg = "New transaction voucher recorded successfully.";
                $alert_type = "success";
            }
        }
    }
}

// ২.২ ডিলিট অপারেশন
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cashbook WHERE id = ? AND (sccode = ? OR sccode = ?)");
        $sc_pen = $sccode * 10;
        $stmt->bind_param("iii", $del_id, $sccode, $sc_pen);
        if ($stmt->execute()) {
            $alert_msg = "Transaction record deleted.";
            $alert_type = "danger";
        }
    }
}

// ২.৩ পেন্ডিং ভাউচার অনুমোদন / প্রত্যাখ্যান
if (isset($_GET['approve_id'])) {
    $app_id = intval($_GET['approve_id']);
    if ($app_id > 0) {
        $stmt = $conn->prepare("UPDATE cashbook SET sccode = ?, approved_by = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $sc_pen = $sccode * 10;
        $stmt->bind_param("isii", $sccode, $usr, $app_id, $sc_pen);
        if ($stmt->execute()) {
            $alert_msg = "Voucher #$app_id approved and sanctioned.";
            $alert_type = "success";
        }
    }
}

// ==========================================
// 3. MASTER DATA FOR DROPDOWNS & FILTER
// ==========================================
// সব অ্যাকাউন্ট হেড
$heads_res = $conn->query("SELECT id, account_head FROM account_head WHERE sccode = '$sccode' ORDER BY account_head ASC");
$heads_list = [];
if ($heads_res) {
    while ($h = $heads_res->fetch_assoc()) {
        $heads_list[] = $h;
    }
}

// সব সাব-হেড (গ্রুপ আকারে প্রস্তুত)
$sub_heads_res = $conn->query("SELECT s.id, s.sub_head, s.account_head_id, s.income, s.expenditure, COALESCE(h.account_head, 'General') AS account_head
                               FROM account_sub_head s 
                               LEFT JOIN account_head h ON s.account_head_id = h.id 
                               WHERE s.sccode = '$sccode' 
                               ORDER BY h.account_head ASC, s.sub_head ASC");
$all_sub_heads = [];
if ($sub_heads_res) {
    while ($sh = $sub_heads_res->fetch_assoc()) {
        $all_sub_heads[] = $sh;
    }
}

// ==========================================
// 4. CASHBOOK LEDGER FETCH & SUMMARY CALCULATION
// ==========================================
$where_clauses = [
    "(c.sccode = '$sccode' OR c.sccode = '" . ($sccode * 10) . "')",
    "c.date BETWEEN '$date_from' AND '$date_to'"
];

if ($filter_head > 0) {
    $where_clauses[] = "(c.account_head = '$filter_head' OR s.account_head_id = '$filter_head')";
}
if ($filter_type === 'Income' || $filter_type === 'Expenditure') {
    $where_clauses[] = "c.type = '$filter_type'";
}

$where_sql = implode(" AND ", $where_clauses);

$sql_main = "SELECT c.*, 
                    COALESCE(s.sub_head, c.particulars, 'General') AS sub_head_name,
                    COALESCE(h.account_head, 'Unassigned') AS main_head_name
             FROM cashbook c 
             LEFT JOIN account_sub_head s ON (c.account_sub_head = s.id OR c.partid = s.id)
             LEFT JOIN account_head h ON (c.account_head = h.id OR s.account_head_id = h.id)
             WHERE $where_sql
             ORDER BY c.date DESC, c.id DESC";

$res_main = $conn->query($sql_main);

$sanctioned_vouchers = [];
$pending_vouchers = [];
$total_income = 0;
$total_expense = 0;

if ($res_main) {
    while ($row = $res_main->fetch_assoc()) {
        if ($row['sccode'] == $sccode) {
            $sanctioned_vouchers[] = $row;
            if ($row['type'] === 'Income') {
                $total_income += floatval($row['amount']);
            } else {
                $total_expense += floatval($row['amount']);
            }
        } else {
            $pending_vouchers[] = $row;
        }
    }
}
$net_balance = $total_income - $total_expense;
?>

<style>
    :root {
        --cb-income: #28a745;
        --cb-expense: #dc3545;
        --cb-primary: #0d6efd;
    }

    /* KPI Summary Cards */
    .kpi-card {
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.08));
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }

    /* Voucher Stream Cards */
    .voucher-card {
        background-color: var(--bs-card-bg, #ffffff);
        border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.08));
        border-radius: 12px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }

    .voucher-card.Income {
        border-left-color: var(--cb-income);
    }

    .voucher-card.Expenditure {
        border-left-color: var(--cb-expense);
    }

    .voucher-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .voucher-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .icon-income {
        background: rgba(40, 167, 69, 0.12);
        color: var(--cb-income);
    }

    .icon-expense {
        background: rgba(220, 53, 69, 0.12);
        color: var(--cb-expense);
    }

    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .action-edit {
        background: rgba(13, 110, 253, 0.1);
        color: var(--cb-primary);
    }

    .action-edit:hover {
        background: var(--cb-primary);
        color: #ffffff;
    }

    .action-delete {
        background: rgba(220, 53, 69, 0.1);
        color: var(--cb-expense);
    }

    .action-delete:hover {
        background: var(--cb-expense);
        color: #ffffff;
    }

    /* Floating Action Button */
    .cb-fab-btn {
        position: fixed;
        bottom: 80px;
        right: 25px;
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: var(--cb-primary);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.35);
        border: none;
        z-index: 1000;
        transition: transform 0.2s ease;
    }

    .cb-fab-btn:hover {
        transform: scale(1.06);
        color: #ffffff;
    }

    /* Dark Mode Overrides */
    [data-bs-theme="dark"] .kpi-card,
    html.dark-style .kpi-card,
    body.dark-style .kpi-card {
        background-color: var(--bs-card-bg, #2b2c40);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .voucher-card,
    html.dark-style .voucher-card,
    body.dark-style .voucher-card {
        background-color: var(--bs-card-bg, #2b2c40);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-bs-theme="dark"] .modal-content,
    html.dark-style .modal-content {
        background-color: var(--bs-card-bg, #2b2c40);
        color: #dbdade;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Header & Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-primary"><i class="bi bi-book-half me-2"></i>Cash Book & Ledger</h4>
            <p class="text-muted mb-0 small">Income, Expenditure, Voucher Processing & Balance Management</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Search Input -->
            <div class="input-group input-group-sm" style="max-width: 220px;">
                <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="cbSearchInput" class="form-control border-start-0" placeholder="Filter Vouchers...">
            </div>

            <!-- Print Button -->
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>

            <!-- New Voucher Entry Button -->
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="openNewVoucherModal()">
                <i class="bi bi-plus-circle me-1"></i> New Voucher
            </button>
        </div>
    </div>

    <!-- Alert Message -->
    <?php if ($alert_msg): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($alert_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Total Income (Cr)</small>
                        <span class="badge bg-label-success"><i class="bi bi-arrow-down-left"></i></span>
                    </div>
                    <h4 class="mb-0 fw-bold text-success">৳<?= number_format($total_income, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Total Expense (Dr)</small>
                        <span class="badge bg-label-danger"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                    <h4 class="mb-0 fw-bold text-danger">৳<?= number_format($total_expense, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Net Balance</small>
                        <span class="badge bg-label-primary"><i class="bi bi-wallet2"></i></span>
                    </div>
                    <h4 class="mb-0 fw-bold <?= ($net_balance >= 0) ? 'text-primary' : 'text-danger' ?>">
                        ৳<?= number_format($net_balance, 2) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Vouchers / Period</small>
                        <span class="badge bg-label-secondary"><i class="bi bi-receipt"></i></span>
                    </div>
                    <h5 class="mb-0 fw-bold text-body">
                        <?= count($sanctioned_vouchers) ?> Sanctioned
                    </h5>
                    <?php if (count($pending_vouchers) > 0): ?>
                        <small class="text-warning fw-semibold"><?= count($pending_vouchers) ?> Pending Approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range & Filters Bar -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Account Head</label>
                    <select name="filter_head" class="form-select form-select-sm">
                        <option value="0">All Account Heads</option>
                        <?php foreach ($heads_list as $hl): ?>
                            <option value="<?= $hl['id'] ?>" <?= ($filter_head == $hl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($hl['account_head']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="cash-book.php" class="btn btn-light btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Navigation Tabs: Sanctioned vs Pending + View Switcher -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <ul class="nav nav-pills gap-1" id="cashbookTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm px-3" data-bs-toggle="pill" data-bs-target="#sanctionedPane">
                        <i class="bi bi-patch-check-fill me-1"></i> Sanctioned Vouchers
                        <span class="badge bg-primary ms-1"><?= count($sanctioned_vouchers) ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm px-3 text-warning" data-bs-toggle="pill" data-bs-target="#pendingPane">
                        <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                        <span class="badge bg-warning ms-1"><?= count($pending_vouchers) ?></span>
                    </button>
                </li>
            </ul>

            <!-- View Switcher -->
            <div class="btn-group btn-group-sm" role="group" aria-label="View Switcher">
                <button type="button" class="btn btn-outline-secondary active" id="btnViewTable" onclick="toggleCbView('table')">
                    <i class="bi bi-table me-1"></i> Ledger Table
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnViewCards" onclick="toggleCbView('cards')">
                    <i class="bi bi-grid-fill me-1"></i> Cards Stream
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content">

        <!-- PANE 1: SANCTIONED VOUCHERS -->
        <div class="tab-pane fade show active" id="sanctionedPane">

            <!-- 1A. LEDGER TABLE VIEW -->
            <div id="cbTableView">
                <div class="card shadow-sm border-0">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle mb-0" id="sanctionedTable">
                            <thead class="table-light">
                                <tr class="small text-uppercase">
                                    <th style="width: 50px;">#</th>
                                    <th>Date</th>
                                    <th>Memo / Ref</th>
                                    <th>Account Head & Sub-Head</th>
                                    <th>Particulars / Description</th>
                                    <th class="text-end text-success">Income (Cr)</th>
                                    <th class="text-end text-danger">Expense (Dr)</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($sanctioned_vouchers) === 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                            No sanctioned transactions found in this period.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $sl = 1;
                                    foreach ($sanctioned_vouchers as $v): 
                                    ?>
                                        <tr class="voucher-row-item"
                                            data-search="<?= strtolower(htmlspecialchars($v['particulars'] . ' ' . $v['main_head_name'] . ' ' . $v['sub_head_name'] . ' ' . $v['memono'] . ' ' . $v['entryby'])) ?>">
                                            <td class="text-muted small fw-bold"><?= $sl++ ?></td>
                                            <td class="small fw-semibold"><?= date('d M, Y', strtotime($v['date'])) ?></td>
                                            <td>
                                                <?php if ($v['memono']): ?>
                                                    <span class="badge bg-label-secondary">#<?= htmlspecialchars($v['memono']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-label-primary small me-1">
                                                        <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($v['main_head_name']) ?>
                                                    </span>
                                                    <span class="text-secondary small fw-semibold">
                                                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars($v['sub_head_name']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-body fw-semibold"><?= htmlspecialchars($v['particulars'] ?: $v['sub_head_name']) ?></span>
                                                <small class="text-muted d-block" style="font-size: 11px;">By: <?= htmlspecialchars($v['entryby'] ?: 'Admin') ?></small>
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                <?= ($v['type'] === 'Income') ? '৳' . number_format($v['amount'], 2) : '—' ?>
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                <?= ($v['type'] === 'Expenditure') ? '৳' . number_format($v['amount'], 2) : '—' ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    <button class="action-btn action-edit" title="Edit Voucher"
                                                        onclick='openEditVoucherModal(<?= json_encode($v) ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="action-btn action-delete" title="Delete Voucher"
                                                        onclick="confirmDeleteVoucher(<?= $v['id'] ?>)">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 1B. CARDS STREAM VIEW -->
            <div id="cbCardsView" class="d-none">
                <?php if (count($sanctioned_vouchers) === 0): ?>
                    <div class="card shadow-sm border-0 text-center py-5">
                        <div class="text-muted"><i class="bi bi-inbox fs-2"></i></div>
                        <p class="text-muted mt-2 mb-0">No sanctioned transactions found in this period.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sanctioned_vouchers as $v): ?>
                        <div class="card voucher-card shadow-sm <?= $v['type'] ?> voucher-card-item"
                             data-search="<?= strtolower(htmlspecialchars($v['particulars'] . ' ' . $v['main_head_name'] . ' ' . $v['sub_head_name'] . ' ' . $v['memono'] . ' ' . $v['entryby'])) ?>">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="voucher-icon <?= ($v['type'] === 'Income') ? 'icon-income' : 'icon-expense' ?> me-3">
                                        <i class="bi <?= ($v['type'] === 'Income') ? 'bi-arrow-down-left' : 'bi-arrow-up-right' ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-body"><?= htmlspecialchars($v['particulars'] ?: $v['sub_head_name']) ?></h6>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-label-primary small">
                                                <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($v['main_head_name']) ?>
                                            </span>
                                            <span class="text-secondary small fw-semibold">
                                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($v['sub_head_name']) ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="bi bi-calendar3 me-1"></i><?= date('d M, Y', strtotime($v['date'])) ?>
                                            <span class="mx-1">•</span> Memo: <?= $v['memono'] ?: 'N/A' ?>
                                            <span class="mx-1">•</span> Entry: <?= htmlspecialchars($v['entryby'] ?: 'Admin') ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-1 fw-bold <?= ($v['type'] === 'Income') ? 'text-success' : 'text-danger' ?>">
                                            <?= ($v['type'] === 'Income') ? '+' : '-' ?>৳<?= number_format($v['amount'], 2) ?>
                                        </h5>
                                        <div class="d-inline-flex gap-1">
                                            <button class="action-btn action-edit" title="Edit"
                                                onclick='openEditVoucherModal(<?= json_encode($v) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="action-btn action-delete" title="Delete"
                                                onclick="confirmDeleteVoucher(<?= $v['id'] ?>)">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- PANE 2: PENDING APPROVAL VOUCHERS -->
        <div class="tab-pane fade" id="pendingPane">
            <?php if (count($pending_vouchers) === 0): ?>
                <div class="card shadow-sm border-0 text-center py-5">
                    <div class="text-success"><i class="bi bi-check-circle-fill fs-2"></i></div>
                    <p class="text-muted mt-2 mb-0">All vouchers are approved! No pending transactions.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending_vouchers as $v): ?>
                    <div class="card voucher-card shadow-sm border-warning mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h6 class="mb-1 fw-bold text-body"><?= htmlspecialchars($v['particulars'] ?: $v['sub_head_name']) ?></h6>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-label-warning small">
                                            <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($v['main_head_name']) ?>
                                        </span>
                                        <span class="text-secondary small fw-semibold">
                                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($v['sub_head_name']) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M, Y', strtotime($v['date'])) ?>
                                        <span class="mx-1">•</span> Submitted By: <strong><?= htmlspecialchars($v['entryby']) ?></strong>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <h5 class="mb-2 fw-bold text-body">৳<?= number_format($v['amount'], 2) ?></h5>
                                    <div class="d-flex gap-2">
                                        <a href="cash-book.php?approve_id=<?= $v['id'] ?>" class="btn btn-sm btn-success px-3">
                                            <i class="bi bi-check-lg me-1"></i> Approve
                                        </a>
                                        <a href="cash-book.php?delete_id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger px-3"
                                           onclick="return confirm('Reject and delete this voucher?');">
                                            <i class="bi bi-x-lg me-1"></i> Reject
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Floating Action Button -->
<button class="cb-fab-btn shadow-lg" title="Create New Voucher" onclick="openNewVoucherModal()">
    <i class="bi bi-plus-lg"></i>
</button>

<!-- ==========================================
     MODAL: TRANSACTION VOUCHER (ADD / EDIT)
=========================================== -->
<div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-primary" id="voucherModalTitle">
                    <i class="bi bi-receipt me-1"></i> New Transaction Voucher
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="voucherForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="entry_id" id="v_entry_id">
                    <input type="hidden" name="head_code" id="v_head_code">

                    <!-- Date & Type -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">TRANSACTION DATE</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="date" id="v_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">TRANSACTION TYPE</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-arrow-left-right"></i></span>
                                <select name="type" id="v_type" class="form-select" required>
                                    <option value="Expenditure">Expense / Debit (ব্যয়)</option>
                                    <option value="Income">Income / Credit (আয়)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sector / Sub-Head Dropdown -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ACCOUNT HEAD & SECTOR (SUB-HEAD)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-tags"></i></span>
                            <select name="partid" id="v_partid" class="form-select" required>
                                <option value="">Select Account Sector (Sub-Head)</option>
                                <?php
                                $grouped_heads = [];
                                foreach ($all_sub_heads as $sh) {
                                    $grouped_heads[$sh['account_head']][] = $sh;
                                }
                                foreach ($grouped_heads as $head_name => $subs):
                                ?>
                                    <optgroup label="<?= htmlspecialchars($head_name) ?>">
                                        <?php foreach ($subs as $s): ?>
                                            <option value="<?= $s['id'] ?>" 
                                                    data-head="<?= $s['account_head_id'] ?>"
                                                    data-income="<?= $s['income'] ?>"
                                                    data-expense="<?= $s['expenditure'] ?>">
                                                <?= htmlspecialchars($s['sub_head']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Particulars / Description -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">PARTICULARS / DESCRIPTION</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                            <input type="text" name="particulars" id="v_particulars" class="form-control" placeholder="Enter Transaction Details / Reason" required>
                        </div>
                    </div>

                    <!-- Amount & Memo No -->
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">AMOUNT (৳)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="any" name="amount" id="v_amount" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">MEMO / VOUCHER NO</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="number" name="memono" id="v_memono" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_voucher" class="btn btn-primary btn-sm px-4 shadow-sm">Save Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    const voucherModal = new bootstrap.Modal(document.getElementById('voucherModal'));
    let currentCbView = 'table'; // 'table' or 'cards'

    function toggleCbView(mode) {
        currentCbView = mode;
        const tableView = document.getElementById('cbTableView');
        const cardsView = document.getElementById('cbCardsView');
        const btnTable = document.getElementById('btnViewTable');
        const btnCards = document.getElementById('viewCardsBtn');

        if (mode === 'cards') {
            tableView?.classList.add('d-none');
            cardsView?.classList.remove('d-none');
            btnCards?.classList.add('active');
            btnTable?.classList.remove('active');
        } else {
            cardsView?.classList.add('d-none');
            tableView?.classList.remove('d-none');
            btnTable?.classList.add('active');
            btnCards?.classList.remove('active');
        }
    }

    function openNewVoucherModal() {
        document.getElementById('voucherModalTitle').innerHTML = '<i class="bi bi-receipt me-1"></i> New Transaction Voucher';
        document.getElementById('v_entry_id').value = '';
        document.getElementById('voucherForm').reset();
        document.getElementById('v_date').value = '<?= date('Y-m-d') ?>';
        document.getElementById('v_type').value = 'Expenditure';
        document.getElementById('v_head_code').value = '';

        filterSubHeadsByType('Expenditure', null);
        voucherModal.show();
    }

    function openEditVoucherModal(v) {
        document.getElementById('voucherModalTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Edit Transaction Voucher #' + v.id;
        document.getElementById('v_entry_id').value = v.id || '';
        document.getElementById('v_date').value = v.date || '<?= date('Y-m-d') ?>';
        document.getElementById('v_particulars').value = v.particulars || '';
        document.getElementById('v_amount').value = v.amount || '';
        document.getElementById('v_type').value = v.type || 'Expenditure';
        document.getElementById('v_memono').value = (v.memono && v.memono != 0) ? v.memono : '';
        document.getElementById('v_head_code').value = v.account_head || '';

        filterSubHeadsByType(v.type, v.account_sub_head || v.partid);
        voucherModal.show();
    }

    function filterSubHeadsByType(type, selectedSubId) {
        const select = document.getElementById('v_partid');
        const options = select.querySelectorAll('option[data-head]');

        options.forEach(opt => {
            const isInc = opt.getAttribute('data-income') === '1';
            const isExp = opt.getAttribute('data-expense') === '1';

            if (type === 'Income' && !isInc) {
                opt.style.display = 'none';
                opt.disabled = true;
            } else if (type === 'Expenditure' && !isExp) {
                opt.style.display = 'none';
                opt.disabled = true;
            } else {
                opt.style.display = '';
                opt.disabled = false;
            }
        });

        if (selectedSubId) {
            select.value = selectedSubId;
            const chosen = select.querySelector(`option[value="${selectedSubId}"]`);
            if (chosen) {
                document.getElementById('v_head_code').value = chosen.getAttribute('data-head') || '';
            }
        }
    }

    document.getElementById('v_type')?.addEventListener('change', function() {
        filterSubHeadsByType(this.value, null);
    });

    document.getElementById('v_partid')?.addEventListener('change', function() {
        const chosen = this.options[this.selectedIndex];
        if (chosen) {
            document.getElementById('v_head_code').value = chosen.getAttribute('data-head') || '';
        }
    });

    function confirmDeleteVoucher(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete this voucher?',
                text: "This transaction will be permanently removed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = 'cash-book.php?delete_id=' + id;
                }
            });
        } else {
            if (confirm('Delete this transaction permanently?')) {
                window.location.href = 'cash-book.php?delete_id=' + id;
            }
        }
    }

    // Real-time Live Search Filter
    document.getElementById('cbSearchInput')?.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        // Filter Table Rows
        document.querySelectorAll('.voucher-row-item').forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            row.style.display = (query === '' || searchData.includes(query)) ? '' : 'none';
        });

        // Filter Card Items
        document.querySelectorAll('.voucher-card-item').forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            card.style.display = (query === '' || searchData.includes(query)) ? '' : 'none';
        });
    });
</script>
</body>
</html>