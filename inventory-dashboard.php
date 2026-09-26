<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

// Initialize tables
init_inventory_database($conn);

// ==========================================
// 1. KPI DATA CALCULATIONS
// ==========================================
// Total Items & Total Stock Valuation
$kpi_items = $conn->query("SELECT 
    COUNT(*) as total_items,
    SUM(current_stock) as total_qty,
    SUM(current_stock * purchase_price) as total_purchase_val,
    SUM(current_stock * sale_price) as total_sale_val
    FROM inv_items WHERE sccode = '$sccode' AND status = 1")->fetch_assoc();

// Today's Sales
$today = date('Y-m-d');
$kpi_sales = $conn->query("SELECT 
    COUNT(*) as today_invoices,
    COALESCE(SUM(paid_amount), 0) as today_cash
    FROM inv_sales WHERE sccode = '$sccode' AND sale_date = '$today'")->fetch_assoc();

// Low Stock Alert Count
$kpi_low_stock = $conn->query("SELECT COUNT(*) as low_count 
    FROM inv_items WHERE sccode = '$sccode' AND status = 1 AND current_stock <= reorder_level")->fetch_assoc();

// Total Fixed Assets Valuation
$kpi_assets = $conn->query("SELECT 
    COUNT(*) as total_assets,
    COALESCE(SUM(current_valuation), 0) as asset_val
    FROM fixed_assets WHERE sccode = '$sccode'")->fetch_assoc();

// Low Stock List (Top 10 Critical)
$low_items_res = $conn->query("SELECT i.*, COALESCE(c.category_name, 'General') as cat_name, COALESCE(u.unit_symbol, 'Pcs') as unit_sym
    FROM inv_items i 
    LEFT JOIN inv_categories c ON i.category_id = c.id
    LEFT JOIN inv_units u ON i.unit_id = u.id
    WHERE i.sccode = '$sccode' AND i.status = 1 AND i.current_stock <= i.reorder_level 
    ORDER BY i.current_stock ASC LIMIT 10");

// Recent Sales (Last 5)
$recent_sales_res = $conn->query("SELECT * FROM inv_sales WHERE sccode = '$sccode' ORDER BY id DESC LIMIT 5");

// Top Selling Items (This Month)
$cur_m_start = date('Y-m-01');
$top_items_res = $conn->query("SELECT it.item_id, i.item_name, i.item_code, SUM(it.qty) as total_sold, SUM(it.subtotal) as total_revenue
    FROM inv_sale_items it
    JOIN inv_sales s ON it.sale_id = s.id
    JOIN inv_items i ON it.item_id = i.id
    WHERE s.sccode = '$sccode' AND s.sale_date >= '$cur_m_start'
    GROUP BY it.item_id 
    ORDER BY total_sold DESC LIMIT 5");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i>Store & Inventory Command Center</h4>
            <p class="text-muted mb-0">Overview of institutional stock, student sales, consumables, and fixed assets.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="inventory-pos.php" class="btn btn-primary shadow-sm">
                <i class="bi bi-cart4 me-1"></i> Open POS Terminal
            </a>
            <a href="inventory-purchases.php" class="btn btn-outline-success">
                <i class="bi bi-bag-plus me-1"></i> Stock In / Purchase
            </a>
            <a href="inventory-items.php" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Manage Items
            </a>
        </div>
    </div>

    <!-- 1. KPI Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Today's POS Sales -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-primary h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2 bg-label-primary rounded p-2">
                            <i class="bi bi-cart-check-fill fs-3"></i>
                        </div>
                        <h5 class="ms-1 mb-0">৳ <?= number_format($kpi_sales['today_cash'] ?? 0, 2) ?></h5>
                    </div>
                    <p class="mb-1 fw-semibold text-heading">Today's Store Sales</p>
                    <p class="mb-0">
                        <span class="badge bg-label-success"><i class="bi bi-receipt me-1"></i><?= $kpi_sales['today_invoices'] ?? 0 ?> Invoices</span>
                        <small class="text-muted ms-1">Today</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Inventory Valuation -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2 bg-label-success rounded p-2">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <h5 class="ms-1 mb-0">৳ <?= number_format($kpi_items['total_purchase_val'] ?? 0, 2) ?></h5>
                    </div>
                    <p class="mb-1 fw-semibold text-heading">Total Stock Valuation</p>
                    <p class="mb-0">
                        <span class="badge bg-label-primary"><?= number_format($kpi_items['total_qty'] ?? 0) ?> Units</span>
                        <small class="text-muted ms-1">in <?= $kpi_items['total_items'] ?? 0 ?> items</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 3: Low Stock Warnings -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-danger h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2 bg-label-danger rounded p-2">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <h5 class="ms-1 mb-0 text-danger"><?= $kpi_low_stock['low_count'] ?? 0 ?> Items</h5>
                    </div>
                    <p class="mb-1 fw-semibold text-heading">Low Stock Alerts</p>
                    <p class="mb-0">
                        <span class="badge bg-label-danger">Requires Reorder</span>
                        <small class="text-muted ms-1">Critical items</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 4: Fixed Assets Valuation -->
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2 bg-label-warning rounded p-2">
                            <i class="bi bi-buildings-fill fs-3"></i>
                        </div>
                        <h5 class="ms-1 mb-0">৳ <?= number_format($kpi_assets['asset_val'] ?? 0, 2) ?></h5>
                    </div>
                    <p class="mb-1 fw-semibold text-heading">Fixed Assets Value</p>
                    <p class="mb-0">
                        <span class="badge bg-label-warning"><?= $kpi_assets['total_assets'] ?? 0 ?> Registered Assets</span>
                        <small class="text-muted ms-1">Institutional</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Navigation Module Shortcuts -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <a href="inventory-pos.php" class="card text-decoration-none shadow-sm h-100 border-start border-primary border-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-primary fw-bold"><i class="bi bi-cart4 me-2"></i>Sales POS Terminal</h6>
                        <small class="text-muted">Student sales & instant receipt</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="inventory-items.php" class="card text-decoration-none shadow-sm h-100 border-start border-info border-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-info fw-bold"><i class="bi bi-box-seam me-2"></i>Product Master</h6>
                        <small class="text-muted">Items, Barcodes & Pricing</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="inventory-issues.php" class="card text-decoration-none shadow-sm h-100 border-start border-secondary border-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-secondary fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Internal Issues</h6>
                        <small class="text-muted">Office & Classroom Requisition</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="fixed-assets.php" class="card text-decoration-none shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-warning fw-bold"><i class="bi bi-building-gear me-2"></i>Fixed Assets</h6>
                        <small class="text-muted">Lab, AC & Equipment tracker</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- 3. Tables Row: Low Stock Alert & Recent Sales -->
    <div class="row g-4 mb-4">
        <!-- Low Stock Items Table -->
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-danger fw-bold"><i class="bi bi-exclamation-octagon me-2"></i>Critical Low Stock Warnings</h5>
                    <a href="inventory-purchases.php" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-bag-plus me-1"></i> Create Purchase Order
                    </a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Code & Name</th>
                                <th>Category</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-center">Re-order Level</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($low_items_res && $low_items_res->num_rows > 0): ?>
                                <?php while ($li = $low_items_res->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-heading"><?= htmlspecialchars($li['item_name']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($li['item_code']) ?></small>
                                        </td>
                                        <td><span class="badge bg-label-secondary"><?= htmlspecialchars($li['cat_name']) ?></span></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger fs-6"><?= $li['current_stock'] ?> <?= htmlspecialchars($li['unit_sym']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-warning"><?= $li['reorder_level'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="inventory-purchases.php?item_id=<?= $li['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-cart-plus me-1"></i> Stock In
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                                        All inventory stocks are within safe threshold levels.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Selling Items (This Month) -->
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-stars text-warning me-2"></i>Top Selling Stationery</h5>
                    <small class="text-muted"><?= date('F Y') ?></small>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Sold Qty</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($top_items_res && $top_items_res->num_rows > 0): ?>
                                <?php while ($ti = $top_items_res->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-heading"><?= htmlspecialchars($ti['item_name']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($ti['item_code']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-primary"><?= number_format($ti['total_sold']) ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            ৳ <?= number_format($ti['total_revenue'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        No sales records logged yet this month.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Recent POS Sales Ledger Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Recent POS Sales Transactions</h5>
            <a href="inventory-reports.php?report=sales" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> View Full Sales Ledger
            </a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer / Student</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Net Payable</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-center">Cashbook Sync</th>
                        <th class="text-center" style="width: 80px;">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_sales_res && $recent_sales_res->num_rows > 0): ?>
                        <?php while ($rs = $recent_sales_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary"><?= htmlspecialchars($rs['invoice_no']) ?></span>
                                </td>
                                <td><?= date('d M, Y', strtotime($rs['sale_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($rs['customer_name']) ?></span>
                                    <?php if (!empty($rs['student_stid'])): ?>
                                        <small class="badge bg-label-info ms-1"><?= htmlspecialchars($rs['student_stid']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary"><?= htmlspecialchars($rs['payment_mode']) ?></span>
                                </td>
                                <td class="text-end fw-semibold">৳ <?= number_format($rs['net_payable'], 2) ?></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($rs['paid_amount'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ($rs['cashbook_entry_id']): ?>
                                        <span class="badge bg-label-success" title="Cashbook Voucher #<?= $rs['cashbook_entry_id'] ?>">
                                            <i class="bi bi-check-circle-fill me-1"></i> Linked (#<?= $rs['cashbook_entry_id'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">Direct</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" title="Print POS Receipt" onclick="viewAndPrintReceipt(<?= $rs['id'] ?>)">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No sales records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     THERMAL 80MM RECEIPT MODAL
     ========================================== -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content">
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
                <button type="button" class="btn btn-sm btn-primary" onclick="printReceiptArea()"><i class="bi bi-printer me-1"></i> Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
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
