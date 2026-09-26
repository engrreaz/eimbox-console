<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

// Fetch categories for filter tabs (Tenant & Global sccode=0)
$cats_res = $conn->query("SELECT * FROM inv_categories WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY (sccode = '$sccode') DESC, category_name ASC");
$categories = [];
if ($cats_res) {
    while ($c = $cats_res->fetch_assoc()) {
        $categories[] = $c;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-cart4 me-2"></i>Student Store & Sales POS Terminal</h4>
            <small class="text-muted">Instant billing, barcode checkout & automated stock deduction</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm" onclick="openRecentSalesModal()">
                <i class="bi bi-clock-history me-1"></i> Sales History & Reprint
            </button>
            <a href="inventory-reports.php?report=sales" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Full Ledger
            </a>
            <a href="inventory-dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="inventory-items.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i> Items
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- ==========================================
             LEFT COLUMN: ITEM SELECTOR & SEARCH
             ========================================== -->
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body p-3">
                    <!-- Search & Barcode Row -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" id="item_search_input" class="form-control" placeholder="Search product name, code or scan barcode..." autofocus>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" id="barcode_input" class="form-control" placeholder="Barcode scanner input...">
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter Tabs -->
                    <div class="d-flex gap-1 overflow-auto pb-2 mb-3" style="white-space: nowrap;">
                        <button class="btn btn-sm btn-primary cat-filter-btn" data-cat="all">
                            <i class="bi bi-grid me-1"></i> All Items
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <button class="btn btn-sm btn-outline-secondary cat-filter-btn" data-cat="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Products Grid Container -->
                    <div id="products_grid" class="row g-2 overflow-auto" style="max-height: 580px;">
                        <div class="col-12 text-center py-5 text-muted">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Loading inventory items...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             RIGHT COLUMN: CART & BILLING CHECKOUT
             ========================================== -->
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-heading"><i class="bi bi-receipt me-1"></i>Current Invoice Cart</h6>
                    <button class="btn btn-sm btn-outline-danger" id="clear_cart_btn">
                        <i class="bi bi-trash3 me-1"></i> Clear Cart
                    </button>
                </div>

                <div class="card-body p-3">
                    <!-- Student / Customer Search Box -->
                    <div class="mb-3 position-relative">
                        <label class="form-label small fw-semibold text-muted mb-1">Customer / Student Info:</label>
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text bg-light"><i class="bi bi-person-search"></i></span>
                            <input type="text" id="student_search_input" class="form-control" placeholder="Search by Student ID (STID), Roll, Name or Mobile..." autocomplete="off">
                        </div>
                        <div id="student_search_results" class="list-group position-absolute shadow-lg d-none" style="z-index: 1050; width: 100%; top: 100%; left: 0; max-height: 260px; overflow-y: auto;"></div>
                        
                        <!-- Selected Student Badge/Info -->
                        <div id="selected_student_badge" class="alert alert-primary py-1 px-2 mb-0 d-none d-flex justify-content-between align-items-center">
                            <div class="small">
                                <strong id="sel_st_name">Student Name</strong> 
                                <span class="badge bg-primary ms-1" id="sel_st_id">STID</span>
                                <div class="text-muted" id="sel_st_detail">Class - Sec</div>
                            </div>
                            <button type="button" class="btn-close btn-sm" id="remove_student_btn"></button>
                        </div>
                    </div>

                    <!-- Cart Items Table Container -->
                    <div class="table-responsive mb-3 overflow-auto" style="max-height: 240px; min-height: 160px;">
                        <table class="table table-sm table-hover align-middle mb-0" id="cart_table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center" style="width: 100px;">Qty</th>
                                    <th class="text-end" style="width: 80px;">Price</th>
                                    <th class="text-end" style="width: 80px;">Total</th>
                                    <th class="text-center" style="width: 35px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cart_items_tbody">
                                <tr id="empty_cart_row">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-cart-x fs-2 d-block text-secondary mb-1"></i>
                                        No items in cart. Click a product on the left to add.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bill Calculations Section -->
                    <div class="bg-light rounded p-2 mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Sub Total:</span>
                            <span class="fw-bold" id="bill_subtotal">৳ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="text-muted">Discount (৳):</span>
                            <input type="number" id="bill_discount" class="form-control form-control-sm text-end" value="0" min="0" style="width: 90px;">
                        </div>
                        <div class="d-flex justify-content-between border-top pt-1 mt-1">
                            <span class="fw-bold fs-6 text-primary">Net Payable:</span>
                            <span class="fw-bold fs-6 text-primary" id="bill_net_payable">৳ 0.00</span>
                        </div>
                    </div>

                    <!-- Payment Mode & Paid Amount Inputs -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Payment Method:</label>
                            <select id="payment_mode" class="form-select form-select-sm">
                                <option value="Cash" selected>Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Card">Card</option>
                                <option value="StudentFeeAccount">Student Fee Acc.</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Paid Amount (৳):</label>
                            <input type="number" id="bill_paid_amount" class="form-control form-control-sm text-end fw-bold text-success" value="0" min="0">
                        </div>
                        <div class="col-12 d-flex justify-content-between small px-1">
                            <span class="text-muted">Change / Return:</span>
                            <span class="fw-bold text-danger" id="bill_change_return">৳ 0.00</span>
                        </div>
                    </div>

                    <!-- Checkout Action Button -->
                    <button class="btn btn-success btn-lg w-100 py-2 shadow-sm" id="complete_sale_btn" disabled>
                        <i class="bi bi-printer-fill me-2"></i> Complete Sale & Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     RECENT SALES & INVOICE REPRINT MODAL
     ========================================== -->
<div class="modal fade" id="recentSalesModal" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header py-3 border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-receipt-cutoff me-2"></i>Recent POS Sales & Invoice Reprint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Customer / Student</th>
                                <th>Payment</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-center" style="width: 110px;">Reprint</th>
                            </tr>
                        </thead>
                        <tbody id="recent_sales_tbody">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading sales history...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 border-top">
                <a href="inventory-reports.php?report=sales" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Complete Sales Ledger
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     THERMAL 80MM RECEIPT MODAL (Top Stacking)
     ========================================== -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true" style="z-index: 1100;">
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
            <div class="modal-footer py-2 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideModal('receiptModal'); setTimeout(() => showModal('recentSalesModal'), 200);">
                    <i class="bi bi-arrow-left me-1"></i> Sales List
                </button>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="printReceiptArea()"><i class="bi bi-printer me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allItems = [];
let cart = [];
let selectedStudent = null;
let currentCategoryFilter = 'all';

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

function openRecentSalesModal() {
    showModal('recentSalesModal');
    loadRecentSales();
}

function loadRecentSales() {
    $('#recent_sales_tbody').html(`
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading sales history...
            </td>
        </tr>
    `);

    $.get('api/inventory-action.php', { action: 'get_recent_sales', limit: 30 }, function (res) {
        if (res && res.status === 'success' && res.data && res.data.length > 0) {
            let html = '';
            res.data.forEach(s => {
                const cust = s.customer_name + (s.student_stid ? ` <small class="badge bg-label-info ms-1">${s.student_stid}</small>` : '');
                html += `
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary">${s.invoice_no}</span></td>
                        <td><small>${s.sale_date}</small></td>
                        <td><span class="fw-semibold text-heading">${cust}</span></td>
                        <td><span class="badge bg-label-secondary">${s.payment_mode}</span></td>
                        <td class="text-end fw-bold text-success">৳ ${parseFloat(s.paid_amount).toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" onclick="reprintSale(${s.id})" title="Print / View Receipt">
                                <i class="bi bi-printer me-1"></i> Receipt
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#recent_sales_tbody').html(html);
        } else {
            $('#recent_sales_tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">No recent sales records found.</td></tr>');
        }
    }, 'json').fail(function () {
        $('#recent_sales_tbody').html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load sales history.</td></tr>');
    });
}

function reprintSale(saleId) {
    hideModal('recentSalesModal');
    $.get('api/inventory-action.php', { action: 'get_sale_details', sale_id: saleId }, function (res) {
        if (res && res.status === 'success' && res.invoice) {
            populateReceipt(res.invoice);
            setTimeout(() => {
                showModal('receiptModal');
            }, 200);
        } else {
            Swal.fire('Error', 'Unable to fetch invoice details.', 'error');
        }
    }, 'json').fail(function () {
        Swal.fire('Error', 'Server connection error.', 'error');
    });
}

$(document).ready(function () {
    loadItems();

    // 1. Live Product Search
    $('#item_search_input').on('input', function () {
        renderProductsGrid();
    });

    // 2. Barcode Scanner Enter Key
    $('#barcode_input').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            const scannedCode = $(this).val().trim();
            if (scannedCode) {
                addItemByBarcode(scannedCode);
                $(this).val('');
            }
        }
    });

    // 3. Category Filter Buttons
    $('.cat-filter-btn').on('click', function () {
        $('.cat-filter-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        currentCategoryFilter = $(this).data('cat');
        renderProductsGrid();
    });

    // 4. Student Search Autocomplete
    let stSearchTimeout = null;
    $('#student_search_input').on('input', function () {
        clearTimeout(stSearchTimeout);
        const query = $(this).val().trim();
        if (query.length < 1) {
            $('#student_search_results').addClass('d-none').empty();
            return;
        }

        $('#student_search_results').html('<div class="list-group-item py-2 text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Searching students...</div>').removeClass('d-none');

        stSearchTimeout = setTimeout(() => {
            $.get('api/inventory-action.php', { action: 'search_student', q: query }, function (res) {
                if (res && res.status === 'success' && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(st => {
                        const name = st.stnameeng || st.stnameben || 'Student';
                        const rollText = (st.rollno && st.rollno !== 'N/A') ? ` | Roll: ${st.rollno}` : '';
                        const classText = st.classname ? ` | ${st.classname}` : '';
                        const secText = st.sectionname ? ` (${st.sectionname})` : '';
                        const mobText = st.guarmobile ? ` | <i class="bi bi-telephone"></i> ${st.guarmobile}` : '';

                        html += `
                            <a href="javascript:void(0);" class="list-group-item list-group-item-action py-2 px-3 sel-student-item" 
                               data-stid="${st.stid}" data-name="${name}" data-class="${st.classname || ''}" data-sec="${st.sectionname || ''}" data-roll="${st.rollno || ''}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-primary">${name}</strong>
                                        <small class="text-muted d-block">ID: <span class="fw-bold font-monospace">${st.stid}</span>${rollText}${classText}${secText}${mobText}</small>
                                    </div>
                                    <span class="badge bg-label-info"><i class="bi bi-check2"></i> Select</span>
                                </div>
                            </a>
                        `;
                    });
                    $('#student_search_results').html(html).removeClass('d-none');
                } else {
                    $('#student_search_results').html('<div class="list-group-item py-2 text-muted small"><i class="bi bi-info-circle me-1"></i> No student found matching "' + $('<div>').text(query).html() + '"</div>').removeClass('d-none');
                }
            }, 'json').fail(function () {
                $('#student_search_results').html('<div class="list-group-item py-2 text-danger small"><i class="bi bi-exclamation-circle me-1"></i> Error searching students.</div>').removeClass('d-none');
            });
        }, 200);
    });

    // Close student search results on clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#student_search_input, #student_search_results').length) {
            $('#student_search_results').addClass('d-none');
        }
    });

    // Select Student from dropdown
    $(document).on('click', '.sel-student-item', function () {
        selectedStudent = {
            stid: $(this).data('stid'),
            name: $(this).data('name'),
            class: $(this).data('class'),
            sec: $(this).data('sec'),
            roll: $(this).data('roll')
        };
        $('#sel_st_name').text(selectedStudent.name);
        $('#sel_st_id').text(selectedStudent.stid);
        const classSec = (selectedStudent.class || selectedStudent.sec) ? `Class ${selectedStudent.class} - Sec: ${selectedStudent.sec} ` : '';
        const rollInfo = selectedStudent.roll ? `(Roll: ${selectedStudent.roll})` : '';
        $('#sel_st_detail').text(classSec + rollInfo);
        $('#selected_student_badge').removeClass('d-none');
        $('#student_search_results').addClass('d-none').empty();
        $('#student_search_input').val('').addClass('d-none');
    });

    // Remove Selected Student
    $('#remove_student_btn').on('click', function () {
        selectedStudent = null;
        $('#selected_student_badge').addClass('d-none');
        $('#student_search_input').removeClass('d-none').val('').focus();
    });

    // 5. Discount & Paid Amount inputs calculation
    $('#bill_discount, #bill_paid_amount').on('input', function () {
        calculateCartTotals();
    });

    // 6. Clear Cart
    $('#clear_cart_btn').on('click', function () {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear Cart?',
            text: 'Are you sure you want to remove all items from the current cart?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear',
            cancelButtonText: 'Cancel'
        }).then((res) => {
            if (res.isConfirmed) {
                cart = [];
                renderCart();
            }
        });
    });

    // 7. Complete Sale Submit
    $('#complete_sale_btn').on('click', function () {
        if (cart.length === 0) return;

        const subtotal = calculateSubtotal();
        const discount = parseFloat($('#bill_discount').val()) || 0;
        const netPayable = Math.max(0, subtotal - discount);
        const paidAmount = parseFloat($('#bill_paid_amount').val()) || 0;
        const paymentMode = $('#payment_mode').val();
        const customerName = selectedStudent ? selectedStudent.name : 'Counter Customer';
        const studentStid = selectedStudent ? selectedStudent.stid : '';

        Swal.fire({
            title: 'Confirm Sale & Print?',
            html: `Customer: <b>${customerName}</b><br>Net Payable: <b>৳ ${netPayable.toFixed(2)}</b><br>Paid: <b>৳ ${paidAmount.toFixed(2)}</b> (${paymentMode})`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-printer-fill me-1"></i> Pay & Print',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Post to API
                $.post('api/inventory-action.php', {
                    action: 'process_pos_sale',
                    cart: JSON.stringify(cart),
                    customer_name: customerName,
                    student_stid: studentStid,
                    payment_mode: paymentMode,
                    discount_amount: discount,
                    paid_amount: paidAmount
                }, function (response) {
                    if (response.status === 'success') {
                        // Show Receipt Modal
                        populateReceipt(response.invoice);
                        showModal('receiptModal');

                        // Reset Cart & UI
                        cart = [];
                        selectedStudent = null;
                        $('#selected_student_badge').addClass('d-none');
                        $('#student_search_input').removeClass('d-none').val('');
                        $('#bill_discount').val(0);
                        $('#bill_paid_amount').val(0);
                        renderCart();
                        loadItems(); // reload current stock levels

                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).fire({
                            icon: 'success',
                            title: response.message
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }, 'json').fail(function () {
                    Swal.fire('Error', 'Server connection failed while processing sale.', 'error');
                });
            }
        });
    });
});

// Load Items from API
function loadItems() {
    $.get('api/inventory-action.php', { action: 'search_items' }, function (res) {
        if (res && res.status === 'success' && Array.isArray(res.data)) {
            allItems = res.data;
            renderProductsGrid();
        } else {
            allItems = [];
            renderProductsGrid();
        }
    }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
        console.error('Failed to load items:', textStatus, errorThrown);
        allItems = [];
        $('#products_grid').html(`
            <div class="col-12 text-center text-danger py-5">
                <i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>
                <h6 class="text-danger">Failed to load product items.</h6>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadItems()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                </button>
            </div>
        `);
    });
}

// Render Products Grid
function renderProductsGrid() {
    const search = $('#item_search_input').val().toLowerCase().trim();
    let filtered = allItems.filter(item => {
        const matchesCat = (currentCategoryFilter === 'all' || item.category_id == currentCategoryFilter);
        const matchesSearch = (!search || 
            item.item_name.toLowerCase().includes(search) || 
            item.item_code.toLowerCase().includes(search) || 
            (item.barcode && item.barcode.toLowerCase().includes(search))
        );
        return matchesCat && matchesSearch;
    });

    let html = '';
    if (filtered.length === 0) {
        html = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-1"></i>No matching products found.</div>';
    } else {
        filtered.forEach(it => {
            const isOutOfStock = parseInt(it.current_stock) <= 0;
            const isLowStock = parseInt(it.current_stock) <= parseInt(it.reorder_level) && !isOutOfStock;
            
            let stockBadge = `<span class="badge bg-label-success">${it.current_stock} ${it.unit_symbol}</span>`;
            if (isOutOfStock) {
                stockBadge = `<span class="badge bg-danger">Out of Stock</span>`;
            } else if (isLowStock) {
                stockBadge = `<span class="badge bg-warning">${it.current_stock} ${it.unit_symbol}</span>`;
            }

            html += `
                <div class="col-md-4 col-sm-6">
                    <div class="card h-100 product-card shadow-sm border ${isOutOfStock ? 'opacity-50' : 'cursor-pointer'}" 
                         onclick="${isOutOfStock ? '' : `addToCart(${it.id})`}" style="cursor: ${isOutOfStock ? 'not-allowed' : 'pointer'};">
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <small class="badge bg-label-secondary">${it.category_name}</small>
                                    ${stockBadge}
                                </div>
                                <h6 class="fw-semibold text-heading mb-1 text-truncate" title="${it.item_name}">${it.item_name}</h6>
                                <small class="text-muted d-block font-monospace">${it.item_code}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                <span class="fw-bold text-primary fs-6">৳ ${parseFloat(it.sale_price).toFixed(2)}</span>
                                <button class="btn btn-sm btn-outline-primary p-1 px-2" ${isOutOfStock ? 'disabled' : ''}>
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#products_grid').html(html);
}

// Add Item by Barcode scanner
function addItemByBarcode(code) {
    const item = allItems.find(it => it.barcode === code || it.item_code === code);
    if (item) {
        if (parseInt(item.current_stock) <= 0) {
            Swal.fire('Out of Stock', `${item.item_name} is currently out of stock!`, 'warning');
            return;
        }
        addToCart(item.id);
    } else {
        Swal.fire('Not Found', `No item matches barcode: ${code}`, 'info');
    }
}

// Add Item to Cart
function addToCart(itemId) {
    const item = allItems.find(it => it.id == itemId);
    if (!item) return;

    const existing = cart.find(c => c.id == itemId);
    if (existing) {
        if (existing.qty + 1 > parseInt(item.current_stock)) {
            Swal.fire('Stock Limit', `Cannot exceed available stock (${item.current_stock} ${item.unit_symbol})`, 'warning');
            return;
        }
        existing.qty += 1;
    } else {
        cart.push({
            id: item.id,
            name: item.item_name,
            code: item.item_code,
            price: parseFloat(item.sale_price),
            stock: parseInt(item.current_stock),
            unit: item.unit_symbol,
            qty: 1
        });
    }
    renderCart();
}

// Render Cart Table
function renderCart() {
    if (cart.length === 0) {
        $('#cart_items_tbody').html(`
            <tr id="empty_cart_row">
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-cart-x fs-2 d-block text-secondary mb-1"></i>
                    No items in cart. Click a product on the left to add.
                </td>
            </tr>
        `);
        $('#complete_sale_btn').prop('disabled', true);
    } else {
        let html = '';
        cart.forEach((c, idx) => {
            const rowTotal = c.price * c.qty;
            html += `
                <tr>
                    <td>
                        <span class="fw-semibold text-heading small d-block text-truncate" style="max-width: 130px;" title="${c.name}">${c.name}</span>
                        <small class="text-muted font-monospace">${c.code}</small>
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm justify-content-center">
                            <button class="btn btn-outline-secondary px-1" onclick="changeQty(${idx}, -1)"><i class="bi bi-dash"></i></button>
                            <span class="input-group-text px-2 bg-white fw-bold">${c.qty}</span>
                            <button class="btn btn-outline-secondary px-1" onclick="changeQty(${idx}, 1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </td>
                    <td class="text-end small">৳ ${c.price.toFixed(2)}</td>
                    <td class="text-end fw-bold small text-primary">৳ ${rowTotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${idx})"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>
            `;
        });
        $('#cart_items_tbody').html(html);
        $('#complete_sale_btn').prop('disabled', false);
    }
    calculateCartTotals();
}

function changeQty(idx, delta) {
    if (cart[idx]) {
        const newQty = cart[idx].qty + delta;
        if (newQty <= 0) {
            cart.splice(idx, 1);
        } else if (newQty > cart[idx].stock) {
            Swal.fire('Stock Limit', `Only ${cart[idx].stock} units available in stock.`, 'warning');
        } else {
            cart[idx].qty = newQty;
        }
        renderCart();
    }
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function calculateSubtotal() {
    return cart.reduce((sum, it) => sum + (it.price * it.qty), 0);
}

function calculateCartTotals() {
    const subtotal = calculateSubtotal();
    const discount = parseFloat($('#bill_discount').val()) || 0;
    const netPayable = Math.max(0, subtotal - discount);
    
    let paidAmount = parseFloat($('#bill_paid_amount').val());
    if (isNaN(paidAmount) || paidAmount <= 0) {
        paidAmount = netPayable;
        $('#bill_paid_amount').val(netPayable.toFixed(2));
    }

    const changeReturn = Math.max(0, paidAmount - netPayable);

    $('#bill_subtotal').text(`৳ ${subtotal.toFixed(2)}`);
    $('#bill_net_payable').text(`৳ ${netPayable.toFixed(2)}`);
    $('#bill_change_return').text(`৳ ${changeReturn.toFixed(2)}`);
}

// Populate Thermal Receipt Modal
function populateReceipt(inv) {
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
}

// Print Thermal Receipt
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
