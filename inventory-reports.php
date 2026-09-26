<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

$report_type = trim($_GET['report'] ?? 'sales');
$date_from = trim($_GET['date_from'] ?? date('Y-m-01'));
$date_to = trim($_GET['date_to'] ?? date('Y-m-d'));
$cat_id = intval($_GET['cat_id'] ?? 0);
$supplier_id = intval($_GET['supplier_id'] ?? 0);
$pay_mode = trim($_GET['pay_mode'] ?? '');
$asset_room = trim($_GET['room'] ?? '');

// Fetch Categories for filters
$categories = [];
$cat_q = $conn->query("SELECT id, category_name FROM inv_categories WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY category_name ASC");
if ($cat_q) {
    while ($c = $cat_q->fetch_assoc()) {
        $categories[] = $c;
    }
}

// Fetch Suppliers for filters
$suppliers = [];
$sup_q = $conn->query("SELECT id, supplier_name FROM inv_suppliers WHERE sccode = '$sccode' AND status = 1 ORDER BY supplier_name ASC");
if ($sup_q) {
    while ($sp = $sup_q->fetch_assoc()) {
        $suppliers[] = $sp;
    }
}

// ==========================================
// 1. SALES & REVENUE REPORT DATA
// ==========================================
if ($report_type === 'sales') {
    $where_sales = "s.sccode = '$sccode' AND s.sale_date BETWEEN '$date_from' AND '$date_to'";
    if (!empty($pay_mode)) {
        $where_sales .= " AND s.payment_mode = '" . $conn->real_escape_string($pay_mode) . "'";
    }

    $sales_res = $conn->query("SELECT s.*, 
        COUNT(si.id) as item_count, 
        COALESCE(SUM(si.purchase_rate * si.qty), 0) as total_cogs 
        FROM inv_sales s 
        LEFT JOIN inv_sale_items si ON s.id = si.sale_id 
        WHERE $where_sales 
        GROUP BY s.id 
        ORDER BY s.sale_date DESC, s.id DESC");

    $sales_summary = $conn->query("SELECT 
        COUNT(*) as total_invoices,
        COALESCE(SUM(total_amount), 0) as gross_sales,
        COALESCE(SUM(discount_amount), 0) as total_discount,
        COALESCE(SUM(net_payable), 0) as net_sales,
        COALESCE(SUM(paid_amount), 0) as total_paid
        FROM inv_sales s 
        WHERE $where_sales")->fetch_assoc();
}

// ==========================================
// 2. STOCK VALUATION DATA
// ==========================================
if ($report_type === 'stock') {
    $where_stock = "i.sccode = '$sccode' AND i.status = 1";
    if ($cat_id > 0) {
        $where_stock .= " AND i.category_id = $cat_id";
    }

    $stock_res = $conn->query("SELECT i.*, 
        COALESCE(c.category_name, 'General') as cat_name, 
        COALESCE(u.unit_symbol, 'Pcs') as unit_sym,
        (i.current_stock * i.purchase_price) as purchase_val,
        (i.current_stock * i.sale_price) as sale_val
        FROM inv_items i 
        LEFT JOIN inv_categories c ON i.category_id = c.id 
        LEFT JOIN inv_units u ON i.unit_id = u.id 
        WHERE $where_stock 
        ORDER BY i.item_name ASC");

    $stock_summary = $conn->query("SELECT 
        COUNT(*) as total_items,
        COALESCE(SUM(current_stock), 0) as total_qty,
        COALESCE(SUM(current_stock * purchase_price), 0) as total_purchase_val,
        COALESCE(SUM(current_stock * sale_price), 0) as total_sale_val
        FROM inv_items i 
        WHERE $where_stock")->fetch_assoc();
}

// ==========================================
// 3. LOW STOCK & REORDER ALERTS DATA
// ==========================================
if ($report_type === 'low_stock') {
    $where_low = "i.sccode = '$sccode' AND i.status = 1 AND i.current_stock <= i.reorder_level";
    if ($cat_id > 0) {
        $where_low .= " AND i.category_id = $cat_id";
    }

    $low_res = $conn->query("SELECT i.*, 
        COALESCE(c.category_name, 'General') as cat_name, 
        COALESCE(u.unit_symbol, 'Pcs') as unit_sym,
        GREATEST(0, i.reorder_level - i.current_stock) as shortage_qty,
        (GREATEST(0, i.reorder_level - i.current_stock) * i.purchase_price) as reorder_cost
        FROM inv_items i 
        LEFT JOIN inv_categories c ON i.category_id = c.id 
        LEFT JOIN inv_units u ON i.unit_id = u.id 
        WHERE $where_low 
        ORDER BY i.current_stock ASC, i.item_name ASC");

    $low_summary = $conn->query("SELECT 
        COUNT(*) as total_low_items,
        COALESCE(SUM(GREATEST(0, reorder_level - current_stock)), 0) as total_shortage_qty,
        COALESCE(SUM(GREATEST(0, reorder_level - current_stock) * purchase_price), 0) as total_reorder_cost
        FROM inv_items i 
        WHERE $where_low")->fetch_assoc();
}

// ==========================================
// 4. PURCHASES & PROCUREMENT LEDGER DATA
// ==========================================
if ($report_type === 'purchases') {
    $where_purch = "p.sccode = '$sccode' AND p.purchase_date BETWEEN '$date_from' AND '$date_to'";
    if ($supplier_id > 0) {
        $where_purch .= " AND p.supplier_id = $supplier_id";
    }

    $purch_res = $conn->query("SELECT p.*, 
        COALESCE(s.supplier_name, 'Direct Vendor') as supplier_name,
        COALESCE(s.phone, '') as supplier_phone,
        COUNT(pi.id) as item_count
        FROM inv_purchases p 
        LEFT JOIN inv_suppliers s ON p.supplier_id = s.id 
        LEFT JOIN inv_purchase_items pi ON p.id = pi.purchase_id 
        WHERE $where_purch 
        GROUP BY p.id 
        ORDER BY p.purchase_date DESC, p.id DESC");

    $purch_summary = $conn->query("SELECT 
        COUNT(*) as total_bills,
        COALESCE(SUM(total_amount), 0) as total_bill_amount,
        COALESCE(SUM(paid_amount), 0) as total_paid,
        COALESCE(SUM(due_amount), 0) as total_due
        FROM inv_purchases p 
        WHERE $where_purch")->fetch_assoc();
}

// ==========================================
// 5. STOCK ISSUE & CONSUMPTION DATA
// ==========================================
if ($report_type === 'issues') {
    $where_iss = "iss.sccode = '$sccode' AND iss.issue_date BETWEEN '$date_from' AND '$date_to'";

    $issue_res = $conn->query("SELECT iss.*, 
        i.item_name, i.item_code, 
        COALESCE(u.unit_symbol, 'Pcs') as unit_sym,
        COALESCE(c.category_name, 'General') as cat_name
        FROM inv_issues iss 
        LEFT JOIN inv_items i ON iss.item_id = i.id 
        LEFT JOIN inv_categories c ON i.category_id = c.id 
        LEFT JOIN inv_units u ON i.unit_id = u.id 
        WHERE $where_iss 
        ORDER BY iss.issue_date DESC, iss.id DESC");

    $issue_summary = $conn->query("SELECT 
        COUNT(*) as total_issue_count,
        COALESCE(SUM(qty), 0) as total_issue_units
        FROM inv_issues iss 
        WHERE $where_iss")->fetch_assoc();
}

// ==========================================
// 6. FIXED ASSETS ROOM AUDIT DATA
// ==========================================
if ($report_type === 'assets') {
    $where_assets = "fa.sccode = '$sccode'";
    if (!empty($asset_room)) {
        $where_assets .= " AND fa.location_room = '" . $conn->real_escape_string($asset_room) . "'";
    }
    if ($cat_id > 0) {
        $where_assets .= " AND fa.category_id = $cat_id";
    }

    $assets_res = $conn->query("SELECT fa.*, COALESCE(c.category_name, 'General') as cat_name 
        FROM fixed_assets fa 
        LEFT JOIN inv_categories c ON fa.category_id = c.id 
        WHERE $where_assets 
        ORDER BY fa.location_room ASC, fa.asset_name ASC");

    $assets_summary = $conn->query("SELECT 
        COUNT(*) as total_assets,
        COALESCE(SUM(current_valuation), 0) as total_valuation,
        COUNT(DISTINCT location_room) as total_rooms
        FROM fixed_assets fa 
        WHERE $where_assets")->fetch_assoc();
}
?>

<style>
/* ============================================================
   A4 PORTRAIT REPORT STYLES & PRINT MEDIA QUERIES
   ============================================================ */
@page {
    size: A4 portrait;
    margin: 12mm 10mm 15mm 10mm;
}

.report-table th, .report-table td {
    padding: 6px 8px;
    font-size: 12px;
}

.print-report-wrapper {
    background: #fff;
}

.letter-head-container {
    display: none;
}

#letter-head {
    margin: 0 auto !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    border: 0 !important;
}

#letter-head td {
    padding: 0 6px !important;
    vertical-align: top !important;
    border: 0 !important;
}

#letter-head .a {
    font-size: 19px !important;
    font-weight: 700 !important;
    line-height: 22px !important;
    margin: 0 !important;
    padding: 0 !important;
}

#letter-head .b {
    font-size: 13px !important;
    line-height: 17px !important;
    margin: 0 !important;
    padding: 0 !important;
}

#letter-head .c {
    font-size: 11px !important;
    line-height: 15px !important;
    margin: 0 !important;
    padding: 0 !important;
}

.report-meta-box {
    border-top: 2px solid #2e384d;
    border-bottom: 1px solid #c2c9d6;
    padding: 6px 0;
    margin-bottom: 12px;
}

.print-signatures {
    display: none;
    margin-top: 50px;
    padding-top: 10px;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 10mm 12mm 10mm;
    }

    html, body {
        background: #fff !important;
        color: #000 !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 10.5px !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* 1. Hide all elements by default */
    body * {
        visibility: hidden !important;
    }

    /* 2. Show only the printable report area and its children */
    #print-report-area,
    #print-report-area * {
        visibility: visible !important;
    }

    /* 3. Snap report area directly to the top-left of the A4 page */
    #print-report-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        display: block !important;
    }

    /* 4. Eliminate layout parent paddings and hide non-print elements */
    .layout-wrapper,
    .layout-container,
    .layout-page,
    .content-wrapper,
    .container-xxl,
    .container-p-y {
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .no-print,
    .layout-navbar,
    .layout-menu,
    .footer,
    .content-footer,
    #mainFooter,
    #extend-footer,
    .btn,
    .nav-pills,
    .card-header-actions,
    .modal,
    .modal-backdrop,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
        height: 0 !important;
        overflow: hidden !important;
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .letter-head-container {
        display: block !important;
        text-align: center !important;
        margin: 0 0 4px 0 !important;
        padding: 0 !important;
    }

    #letter-head {
        margin: 0 auto !important;
        padding: 0 !important;
        border: 0 !important;
        border-collapse: collapse !important;
    }

    #letter-head td {
        padding: 0 4px !important;
        vertical-align: top !important;
        border: 0 !important;
    }

    /* Crisp Black & White Printable Table */
    .print-table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 10px !important;
        color: #000 !important;
        margin-top: 5px !important;
    }

    .print-table th, 
    .print-table td {
        border: 1px solid #444 !important;
        padding: 4px 5px !important;
        color: #000 !important;
        vertical-align: middle !important;
    }

    .print-table thead th {
        background-color: #eee !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-weight: bold !important;
        text-align: center !important;
        color: #000 !important;
    }

    .print-table tfoot td,
    .print-table tfoot th {
        background-color: #f5f5f5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-weight: bold !important;
        border-top: 2px solid #000 !important;
    }

    .print-table tr {
        page-break-inside: avoid !important;
    }

    .print-signatures {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        margin-top: 45px !important;
        page-break-inside: avoid !important;
    }

    .signature-line {
        width: 170px;
        text-align: center;
        border-top: 1px dashed #333;
        padding-top: 4px;
        font-size: 10px;
        color: #000;
        font-weight: bold;
    }

    .badge {
        border: 1px solid #666 !important;
        color: #000 !important;
        background: transparent !important;
        padding: 1px 3px !important;
        font-size: 9px !important;
        font-weight: normal !important;
    }
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Top Header Toolbar (Hidden in Print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Store, Stock & Inventory Audit Reports</h4>
            <p class="text-muted mb-0">Official institutional ledgers, A4 valuation balance sheets & audit registers.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print A4 Report
            </button>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Report Type Navigation Tabs (Hidden in Print) -->
    <div class="card shadow-sm mb-3 no-print">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'sales') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=sales">
                        <i class="bi bi-receipt me-1"></i> Sales Ledger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'stock') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=stock">
                        <i class="bi bi-boxes me-1"></i> Stock Valuation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'low_stock') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=low_stock">
                        <i class="bi bi-exclamation-triangle me-1"></i> Reorder Alerts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'purchases') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=purchases">
                        <i class="bi bi-truck me-1"></i> Purchases Ledger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'issues') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=issues">
                        <i class="bi bi-arrow-up-right-circle me-1"></i> Issues & Usage
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($report_type === 'assets') ? 'active fw-bold' : '' ?>" href="inventory-reports.php?report=assets">
                        <i class="bi bi-buildings me-1"></i> Fixed Asset Audit
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Filter Control Card (Hidden in Print) -->
    <div class="card shadow-sm mb-3 no-print">
        <div class="card-body py-2 px-3">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="report" value="<?= htmlspecialchars($report_type) ?>">

                <?php if (in_array($report_type, ['sales', 'purchases', 'issues'])): ?>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Date From:</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Date To:</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                <?php endif; ?>

                <?php if ($report_type === 'sales'): ?>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Payment Method:</label>
                        <select name="pay_mode" class="form-select form-select-sm">
                            <option value="">All Payment Modes</option>
                            <option value="Cash" <?= ($pay_mode === 'Cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="bKash" <?= ($pay_mode === 'bKash') ? 'selected' : '' ?>>bKash</option>
                            <option value="Nagad" <?= ($pay_mode === 'Nagad') ? 'selected' : '' ?>>Nagad</option>
                            <option value="Card" <?= ($pay_mode === 'Card') ? 'selected' : '' ?>>Card</option>
                            <option value="StudentFeeAccount" <?= ($pay_mode === 'StudentFeeAccount') ? 'selected' : '' ?>>Student Fee Acc.</option>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (in_array($report_type, ['stock', 'low_stock', 'assets'])): ?>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Category Filter:</label>
                        <select name="cat_id" class="form-select form-select-sm">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($cat_id == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($report_type === 'purchases'): ?>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Supplier Filter:</label>
                        <select name="supplier_id" class="form-select form-select-sm">
                            <option value="0">All Suppliers</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>" <?= ($supplier_id == $sup['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sup['supplier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-2 col-sm-6">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         PRINTABLE REPORT AREA (A4 PORTRAIT)
         ============================================================ -->
    <div id="print-report-area" class="print-report-wrapper">

        <!-- 1. INSTITUTIONAL LETTERHEAD -->
        <div class="letter-head-container" id="institutional_letterhead">
            <?php 
            if (file_exists(__DIR__ . '/templete/letter-head-01.php')) {
                include __DIR__ . '/templete/letter-head-01.php';
            } else {
                echo "<h3 class='fw-bold mb-0 text-center'>" . htmlspecialchars($scname ?? 'EIMBox Educational Institution') . "</h3>";
                echo "<p class='text-center text-muted mb-0 small'>" . htmlspecialchars($scaddress ?? '') . "</p>";
            }
            ?>
        </div>

        <!-- 2. REPORT TITLE & METADATA BAR -->
        <div class="report-meta-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">
                        <?php
                        if ($report_type === 'sales') echo '<i class="bi bi-receipt me-1 no-print"></i> Sales & Revenue Ledger';
                        elseif ($report_type === 'stock') echo '<i class="bi bi-boxes me-1 no-print"></i> Comprehensive Stock Valuation & Balance Sheet';
                        elseif ($report_type === 'low_stock') echo '<i class="bi bi-exclamation-triangle me-1 no-print"></i> Critical Reorder & Shortage Alert Register';
                        elseif ($report_type === 'purchases') echo '<i class="bi bi-truck me-1 no-print"></i> Purchase & Procurement Ledger';
                        elseif ($report_type === 'issues') echo '<i class="bi bi-arrow-up-right-circle me-1 no-print"></i> Departmental Stock Issue & Consumption Register';
                        elseif ($report_type === 'assets') echo '<i class="bi bi-buildings me-1 no-print"></i> Fixed Assets Physical Room Audit Register';
                        ?>
                    </h5>
                    <small class="text-muted">
                        <?php if (in_array($report_type, ['sales', 'purchases', 'issues'])): ?>
                            Period: <b><?= date('d M, Y', strtotime($date_from)) ?></b> to <b><?= date('d M, Y', strtotime($date_to)) ?></b>
                        <?php else: ?>
                            Audit Date: <b><?= date('d M, Y') ?></b>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="text-end small">
                    <div>Printed: <b><?= date('d-m-Y h:i A') ?></b></div>
                    <div class="text-muted">User: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Staff') ?></div>
                </div>
            </div>
        </div>

        <!-- 3. REPORT DATA SECTION -->

        <!-- A. SALES LEDGER REPORT -->
        <?php if ($report_type === 'sales'): ?>
            <!-- Screen KPI Cards -->
            <div class="row g-2 mb-3 no-print">
                <div class="col-md-3 col-6">
                    <div class="card bg-label-primary p-2">
                        <small class="text-muted d-block">Total Invoices</small>
                        <h5 class="fw-bold mb-0 text-primary"><?= number_format($sales_summary['total_invoices'] ?? 0) ?></h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-label-secondary p-2">
                        <small class="text-muted d-block">Gross Sales</small>
                        <h5 class="fw-bold mb-0 text-heading">৳ <?= number_format($sales_summary['gross_sales'] ?? 0, 2) ?></h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-label-danger p-2">
                        <small class="text-muted d-block">Total Discount</small>
                        <h5 class="fw-bold mb-0 text-danger">৳ <?= number_format($sales_summary['total_discount'] ?? 0, 2) ?></h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-label-success p-2">
                        <small class="text-muted d-block">Net Cash Inflow</small>
                        <h5 class="fw-bold mb-0 text-success">৳ <?= number_format($sales_summary['total_paid'] ?? 0, 2) ?></h5>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 95px;">Invoice No</th>
                            <th style="width: 80px;">Date</th>
                            <th>Customer / Student</th>
                            <th style="width: 75px;">Payment</th>
                            <th class="text-end" style="width: 80px;">Gross</th>
                            <th class="text-end" style="width: 70px;">Discount</th>
                            <th class="text-end" style="width: 85px;">Paid (৳)</th>
                            <th class="text-center no-print" style="width: 50px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($sales_res && $sales_res->num_rows > 0): 
                            while ($s = $sales_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td><span class="fw-bold font-monospace"><?= htmlspecialchars($s['invoice_no']) ?></span></td>
                                <td class="text-center"><?= date('d-m-Y', strtotime($s['sale_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($s['customer_name']) ?></span>
                                    <?php if (!empty($s['student_stid'])): ?>
                                        <small class="badge bg-label-info ms-1"><?= htmlspecialchars($s['student_stid']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($s['payment_mode']) ?></td>
                                <td class="text-end">৳ <?= number_format($s['total_amount'], 2) ?></td>
                                <td class="text-end text-danger"><?= ($s['discount_amount'] > 0) ? '৳ ' . number_format($s['discount_amount'], 2) : '-' ?></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($s['paid_amount'], 2) ?></td>
                                <td class="text-center no-print">
                                    <button type="button" class="btn btn-xs btn-outline-primary p-1" title="View & Print POS Receipt" onclick="viewAndPrintReceipt(<?= $s['id'] ?>)">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No sales records found in selected criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end fw-bold">Grand Total:</th>
                            <th class="text-end fw-bold">৳ <?= number_format($sales_summary['gross_sales'] ?? 0, 2) ?></th>
                            <th class="text-end fw-bold text-danger">৳ <?= number_format($sales_summary['total_discount'] ?? 0, 2) ?></th>
                            <th class="text-end fw-bold text-success">৳ <?= number_format($sales_summary['total_paid'] ?? 0, 2) ?></th>
                            <th class="no-print"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- B. STOCK VALUATION SHEET -->
        <?php if ($report_type === 'stock'): ?>
            <!-- Screen KPI Cards -->
            <div class="row g-2 mb-3 no-print">
                <div class="col-md-4 col-6">
                    <div class="card bg-label-primary p-2">
                        <small class="text-muted d-block">Total Products / Total Units</small>
                        <h5 class="fw-bold mb-0 text-primary"><?= number_format($stock_summary['total_items'] ?? 0) ?> items (<?= number_format($stock_summary['total_qty'] ?? 0) ?> units)</h5>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="card bg-label-info p-2">
                        <small class="text-muted d-block">Total Purchase Value (Cost)</small>
                        <h5 class="fw-bold mb-0 text-info">৳ <?= number_format($stock_summary['total_purchase_val'] ?? 0, 2) ?></h5>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="card bg-label-success p-2">
                        <small class="text-muted d-block">Total Estimated MRP (Sale Value)</small>
                        <h5 class="fw-bold mb-0 text-success">৳ <?= number_format($stock_summary['total_sale_val'] ?? 0, 2) ?></h5>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 90px;">Item Code</th>
                            <th>Product Name</th>
                            <th style="width: 110px;">Category</th>
                            <th class="text-center" style="width: 75px;">Stock</th>
                            <th class="text-end" style="width: 80px;">Cost Rate</th>
                            <th class="text-end" style="width: 80px;">Sale Rate</th>
                            <th class="text-end" style="width: 95px;">Cost Value</th>
                            <th class="text-end" style="width: 95px;">Retail Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($stock_res && $stock_res->num_rows > 0): 
                            while ($st = $stock_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td><span class="font-monospace fw-bold"><?= htmlspecialchars($st['item_code']) ?></span></td>
                                <td><span class="fw-semibold"><?= htmlspecialchars($st['item_name']) ?></span></td>
                                <td><?= htmlspecialchars($st['cat_name']) ?></td>
                                <td class="text-center fw-bold <?= ($st['current_stock'] <= $st['reorder_level']) ? 'text-danger' : '' ?>">
                                    <?= $st['current_stock'] ?> <?= htmlspecialchars($st['unit_sym']) ?>
                                </td>
                                <td class="text-end">৳ <?= number_format($st['purchase_price'], 2) ?></td>
                                <td class="text-end">৳ <?= number_format($st['sale_price'], 2) ?></td>
                                <td class="text-end fw-bold text-info">৳ <?= number_format($st['purchase_val'], 2) ?></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($st['sale_val'], 2) ?></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No inventory stock records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end fw-bold">Grand Total (<?= number_format($stock_summary['total_items'] ?? 0) ?> items):</th>
                            <th class="text-center fw-bold"><?= number_format($stock_summary['total_qty'] ?? 0) ?></th>
                            <th colspan="2" class="text-end fw-bold">Valuation Totals:</th>
                            <th class="text-end fw-bold text-info">৳ <?= number_format($stock_summary['total_purchase_val'] ?? 0, 2) ?></th>
                            <th class="text-end fw-bold text-success">৳ <?= number_format($stock_summary['total_sale_val'] ?? 0, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- C. CRITICAL REORDER ALERTS -->
        <?php if ($report_type === 'low_stock'): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 90px;">Item Code</th>
                            <th>Product Name</th>
                            <th style="width: 120px;">Category</th>
                            <th class="text-center" style="width: 75px;">In Stock</th>
                            <th class="text-center" style="width: 75px;">Reorder Lvl</th>
                            <th class="text-center" style="width: 80px;">Shortage</th>
                            <th class="text-end" style="width: 85px;">Unit Cost</th>
                            <th class="text-end" style="width: 100px;">Est. Restock Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($low_res && $low_res->num_rows > 0): 
                            while ($l = $low_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td><span class="font-monospace fw-bold"><?= htmlspecialchars($l['item_code']) ?></span></td>
                                <td><span class="fw-semibold text-danger"><?= htmlspecialchars($l['item_name']) ?></span></td>
                                <td><?= htmlspecialchars($l['cat_name']) ?></td>
                                <td class="text-center fw-bold text-danger"><?= $l['current_stock'] ?> <?= htmlspecialchars($l['unit_sym']) ?></td>
                                <td class="text-center"><?= $l['reorder_level'] ?></td>
                                <td class="text-center fw-bold text-danger"><?= $l['shortage_qty'] ?> <?= htmlspecialchars($l['unit_sym']) ?></td>
                                <td class="text-end">৳ <?= number_format($l['purchase_price'], 2) ?></td>
                                <td class="text-end fw-bold text-danger">৳ <?= number_format($l['reorder_cost'], 2) ?></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="9" class="text-center text-success py-4"><i class="bi bi-check-circle me-1"></i> All products have adequate stock above reorder threshold.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end fw-bold">Total Procurement Shortage Required:</th>
                            <th class="text-center fw-bold text-danger"><?= number_format($low_summary['total_shortage_qty'] ?? 0) ?> Units</th>
                            <th class="text-end fw-bold">Est. Budget:</th>
                            <th class="text-end fw-bold text-danger">৳ <?= number_format($low_summary['total_reorder_cost'] ?? 0, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- D. PURCHASES LEDGER REPORT -->
        <?php if ($report_type === 'purchases'): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 95px;">Purchase No</th>
                            <th style="width: 80px;">Date</th>
                            <th>Supplier / Vendor</th>
                            <th class="text-center" style="width: 60px;">Items</th>
                            <th class="text-end" style="width: 85px;">Total (৳)</th>
                            <th class="text-end" style="width: 85px;">Paid (৳)</th>
                            <th class="text-end" style="width: 85px;">Due (৳)</th>
                            <th class="text-center" style="width: 75px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($purch_res && $purch_res->num_rows > 0): 
                            while ($p = $purch_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td><span class="fw-bold font-monospace"><?= htmlspecialchars($p['purchase_no']) ?></span></td>
                                <td class="text-center"><?= date('d-m-Y', strtotime($p['purchase_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($p['supplier_name']) ?></span>
                                    <?php if (!empty($p['supplier_phone'])): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($p['supplier_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $p['item_count'] ?></td>
                                <td class="text-end fw-bold">৳ <?= number_format($p['total_amount'], 2) ?></td>
                                <td class="text-end text-success">৳ <?= number_format($p['paid_amount'], 2) ?></td>
                                <td class="text-end text-danger"><?= ($p['due_amount'] > 0) ? '৳ ' . number_format($p['due_amount'], 2) : '-' ?></td>
                                <td class="text-center"><span class="badge bg-label-secondary"><?= htmlspecialchars($p['payment_status']) ?></span></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No purchase records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end fw-bold">Total Procurement:</th>
                            <th class="text-end fw-bold">৳ <?= number_format($purch_summary['total_bill_amount'] ?? 0, 2) ?></th>
                            <th class="text-end fw-bold text-success">৳ <?= number_format($purch_summary['total_paid'] ?? 0, 2) ?></th>
                            <th class="text-end fw-bold text-danger">৳ <?= number_format($purch_summary['total_due'] ?? 0, 2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- E. STOCK ISSUE & CONSUMPTION REPORT -->
        <?php if ($report_type === 'issues'): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 85px;">Date</th>
                            <th>Item Name & Code</th>
                            <th style="width: 110px;">Category</th>
                            <th class="text-center" style="width: 75px;">Qty Issued</th>
                            <th>Issued To / Staff / Dept</th>
                            <th>Purpose / Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($issue_res && $issue_res->num_rows > 0): 
                            while ($iss = $issue_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td class="text-center"><?= date('d-m-Y', strtotime($iss['issue_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($iss['item_name']) ?></span>
                                    <small class="text-muted d-block font-monospace"><?= htmlspecialchars($iss['item_code']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($iss['cat_name']) ?></td>
                                <td class="text-center fw-bold"><?= $iss['qty'] ?> <?= htmlspecialchars($iss['unit_sym']) ?></td>
                                <td><span class="fw-semibold"><?= htmlspecialchars($iss['issued_to']) ?></span></td>
                                <td><small><?= htmlspecialchars($iss['purpose'] ?: 'Institutional Usage') ?></small></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No departmental stock issue entries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end fw-bold">Total Units Issued:</th>
                            <th class="text-center fw-bold"><?= number_format($issue_summary['total_issue_units'] ?? 0) ?> Units</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- F. FIXED ASSETS ROOM AUDIT -->
        <?php if ($report_type === 'assets'): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle print-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th style="width: 120px;">Room / Location</th>
                            <th style="width: 95px;">Tag Code</th>
                            <th>Asset Description</th>
                            <th style="width: 100px;">Custodian</th>
                            <th class="text-end" style="width: 90px;">Valuation (৳)</th>
                            <th class="text-center" style="width: 75px;">Condition</th>
                            <th class="text-center" style="width: 110px;">Audit Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl = 1;
                        if ($assets_res && $assets_res->num_rows > 0): 
                            while ($ast = $assets_res->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-center"><?= $sl++ ?></td>
                                <td><span class="fw-semibold"><?= htmlspecialchars($ast['location_room']) ?></span></td>
                                <td><span class="font-monospace fw-bold"><?= htmlspecialchars($ast['asset_tag_code']) ?></span></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($ast['asset_name']) ?></span>
                                    <small class="text-muted d-block"><?= htmlspecialchars($ast['cat_name']) ?></small>
                                </td>
                                <td><small><?= htmlspecialchars($ast['custodian_name'] ?: 'N/A') ?></small></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($ast['current_valuation'], 2) ?></td>
                                <td class="text-center"><?= htmlspecialchars($ast['asset_condition']) ?></td>
                                <td class="text-center small text-muted">[ &nbsp; ] Verified</td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No fixed asset records registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end fw-bold">Total Valuation (<?= number_format($assets_summary['total_assets'] ?? 0) ?> items):</th>
                            <th class="text-end fw-bold text-success">৳ <?= number_format($assets_summary['total_valuation'] ?? 0, 2) ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- 4. INSTITUTIONAL SIGNATURE BLOCK (A4 Footer) -->
        <div class="print-signatures">
            <div class="signature-line">
                Prepared By<br>
                <small class="text-muted fw-normal">Store In-Charge / Assistant</small>
            </div>
            <div class="signature-line">
                Verified By<br>
                <small class="text-muted fw-normal">Accountant / Internal Auditor</small>
            </div>
            <div class="signature-line">
                Approved By<br>
                <small class="text-muted fw-normal">Head Teacher / Principal</small>
            </div>
        </div>

    </div>
</div>

<!-- ==========================================
     THERMAL 80MM RECEIPT MODAL (TOP Z-INDEX)
     ========================================== -->
<div class="modal fade no-print" id="receiptModal" tabindex="-1" aria-hidden="true" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px; z-index: 1105;">
        <div class="modal-content shadow-lg border">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-1"></i>Sale Receipt Preview</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 font-monospace" id="receipt_print_area">
                <div class="text-center mb-2">
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($scname ?? 'EIMBox Educational Store') ?></h5>
                    <small class="text-muted d-block"><?= htmlspecialchars($scaddress ?? 'Institutional Campus Store') ?></small>
                    <small class="text-muted">Mobile: <?= htmlspecialchars($scmobile ?? '') ?></small>
                    <hr class="my-2 border-dashed">
                    <h6 class="fw-bold mb-1">SALES MEMO</h6>
                    <div class="small d-flex justify-content-between">
                        <span>Inv: <b id="rcpt_inv">INV-0000</b></span>
                        <span id="rcpt_date">2026-09-27</span>
                    </div>
                    <div class="small text-start mt-1" id="rcpt_customer_box">
                        Customer: <span id="rcpt_customer">Counter Customer</span>
                    </div>
                    <hr class="my-2 border-dashed">
                </div>

                <!-- Items List in Receipt -->
                <table class="table table-sm table-borderless small mb-2">
                    <thead>
                        <tr class="border-bottom">
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody id="rcpt_items_tbody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>

                <hr class="my-2 border-dashed">

                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Amount:</span>
                        <span id="rcpt_total">৳ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Discount:</span>
                        <span id="rcpt_discount">৳ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-6 border-top border-bottom py-1 my-1">
                        <span>Net Payable:</span>
                        <span id="rcpt_net">৳ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Paid (<span id="rcpt_paymode">Cash</span>):</span>
                        <span id="rcpt_paid">৳ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Change Return:</span>
                        <span id="rcpt_change">৳ 0.00</span>
                    </div>
                </div>

                <hr class="my-2 border-dashed">
                <div class="text-center small text-muted">
                    <p class="mb-1">Thank you for your purchase!</p>
                    <small>Generated by EIMBox POS</small>
                </div>
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="printReceiptArea()"><i class="bi bi-printer me-1"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<script>
let showLetterhead = true;

function toggleLetterhead() {
    showLetterhead = !showLetterhead;
    const el = document.getElementById('institutional_letterhead');
    const btn = document.getElementById('toggle_letterhead_btn');
    if (el) {
        el.style.display = showLetterhead ? 'block' : 'none';
    }
    if (btn) {
        btn.innerHTML = showLetterhead ? '<i class="bi bi-file-earmark-image me-1"></i> Hide Letterhead' : '<i class="bi bi-file-earmark-image me-1"></i> Show Letterhead';
    }
}

function showModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
    } catch (e) {
        console.warn('Bootstrap modal show error:', e);
    }
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#' + modalId).modal('show');
    }
}

function hideModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
            if (modalInstance) {
                modalInstance.hide();
                return;
            }
        }
    } catch (e) {
        console.warn('Bootstrap modal hide error:', e);
    }
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#' + modalId).modal('hide');
    }
}

function viewAndPrintReceipt(saleId) {
    $.get('api/inventory-action.php', { action: 'get_sale_details', sale_id: saleId }, function (res) {
        if (res && res.status === 'success' && res.invoice) {
            const inv = res.invoice;
            $('#rcpt_inv').text(inv.invoice_no);
            $('#rcpt_date').text(inv.sale_date);
            $('#rcpt_customer').text(inv.customer_name + (inv.student_stid ? ` (${inv.student_stid})` : ''));
            $('#rcpt_total').text(`৳ ${parseFloat(inv.total_amount).toFixed(2)}`);
            $('#rcpt_discount').text(`৳ ${parseFloat(inv.discount_amount).toFixed(2)}`);
            $('#rcpt_net').text(`৳ ${parseFloat(inv.net_payable).toFixed(2)}`);
            $('#rcpt_paid').text(`৳ ${parseFloat(inv.paid_amount).toFixed(2)}`);
            $('#rcpt_change').text(`৳ ${parseFloat(inv.change_amount).toFixed(2)}`);
            $('#rcpt_paymode').text(inv.payment_mode);

            let html = '';
            inv.items.forEach(it => {
                html += `
                    <tr>
                        <td>${it.item_name}</td>
                        <td class="text-center">${it.qty}</td>
                        <td class="text-end">${parseFloat(it.unit_price).toFixed(2)}</td>
                        <td class="text-end fw-bold">${parseFloat(it.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            $('#rcpt_items_tbody').html(html);
            showModal('receiptModal');
        } else {
            Swal.fire('Error', 'Unable to fetch invoice details.', 'error');
        }
    }, 'json').fail(function () {
        Swal.fire('Error', 'Server connection error.', 'error');
    });
}

function printReceiptArea() {
    const printContent = document.getElementById('receipt_print_area');
    if (!printContent) return;
    const win = window.open('', '', 'width=420,height=600');
    win.document.write('<html><head><title>Print Sale Receipt</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    win.document.write('<style>body{padding:15px;font-family:monospace;font-size:13px;}.border-dashed{border-style:dashed;}</style>');
    win.document.write('</head><body>');
    win.document.write(printContent.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(() => {
        win.print();
        win.close();
    }, 300);
}
</script>

<?php require_once 'footer.php'; ?>
