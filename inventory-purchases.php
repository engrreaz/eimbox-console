<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

// Pre-selected item if opened from dashboard
$preset_item_id = intval($_GET['item_id'] ?? 0);

// Fetch Suppliers
$suppliers_res = $conn->query("SELECT * FROM inv_suppliers WHERE sccode = '$sccode' AND status = 1 ORDER BY supplier_name ASC");
$suppliers = [];
while ($s = $suppliers_res->fetch_assoc()) { $suppliers[] = $s; }

// Fetch All Items for selector
$items_res = $conn->query("SELECT i.id, i.item_name, i.item_code, i.purchase_price, COALESCE(u.unit_symbol, 'Pcs') as unit_sym 
    FROM inv_items i 
    LEFT JOIN inv_units u ON i.unit_id = u.id 
    WHERE i.sccode = '$sccode' AND i.status = 1 
    ORDER BY i.item_name ASC");
$all_items = [];
while ($it = $items_res->fetch_assoc()) { $all_items[] = $it; }

// Fetch Past Purchases
$purchases_res = $conn->query("SELECT p.*, COALESCE(s.supplier_name, 'Direct Market') AS supplier_name, s.company_name 
    FROM inv_purchases p 
    LEFT JOIN inv_suppliers s ON p.supplier_id = s.id 
    WHERE p.sccode = '$sccode' 
    ORDER BY p.purchase_date DESC, p.id DESC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-bag-plus me-2 text-primary"></i>Stock In & Vendor Procurement</h4>
            <p class="text-muted mb-0">Record incoming stock batches, vendor challans and cashbook expense sync.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newPurchaseModal">
                <i class="bi bi-plus-circle me-1"></i> New Purchase Entry
            </button>
            <a href="inventory-suppliers.php" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Manage Suppliers
            </a>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Purchases History Table Card -->
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Procurement & Stock-In History</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="purchasesTable">
                <thead class="table-light">
                    <tr>
                        <th>Challan / Ref No</th>
                        <th>Purchase Date</th>
                        <th>Supplier / Vendor</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Due Balance</th>
                        <th>Payment Mode</th>
                        <th class="text-center">Cashbook</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($purchases_res && $purchases_res->num_rows > 0): ?>
                        <?php while ($p = $purchases_res->fetch_assoc()): ?>
                            <?php $is_cancelled = (isset($p['status']) && intval($p['status']) === 0); ?>
                            <tr class="<?= $is_cancelled ? 'table-light text-muted opacity-75' : '' ?>">
                                <td>
                                    <span class="fw-bold <?= $is_cancelled ? 'text-decoration-line-through text-muted' : 'text-primary' ?> font-monospace"><?= htmlspecialchars($p['purchase_no']) ?></span>
                                </td>
                                <td><?= date('d M, Y', strtotime($p['purchase_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold text-heading"><?= htmlspecialchars($p['supplier_name']) ?></span>
                                    <?php if (!empty($p['company_name'])): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($p['company_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold">৳ <?= number_format($p['net_amount'], 2) ?></td>
                                <td class="text-end text-success fw-bold">৳ <?= number_format($p['paid_amount'], 2) ?></td>
                                <td class="text-end text-danger fw-bold">
                                    <?php if ($p['due_amount'] > 0): ?>
                                        ৳ <?= number_format($p['due_amount'], 2) ?>
                                    <?php else: ?>
                                        <span class="badge bg-label-success">Paid in Full</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-label-secondary"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                                <td class="text-center">
                                    <?php if ($p['cashbook_entry_id']): ?>
                                        <span class="badge bg-label-success"><i class="bi bi-check-circle me-1"></i> Voucher #<?= $p['cashbook_entry_id'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">No Sync</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_cancelled): ?>
                                        <span class="badge bg-label-danger"><i class="bi bi-x-circle me-1"></i> Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-success"><i class="bi bi-check2 me-1"></i> Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info" title="View Challan Details & Print" onclick="viewPurchaseDetails(<?= $p['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (!$is_cancelled): ?>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger" title="Cancel & Revert Stock" onclick="deletePurchase(<?= $p['id'] ?>, '<?= htmlspecialchars($p['purchase_no']) ?>')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" disabled title="Already Cancelled">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No purchase records logged yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     NEW PURCHASE ENTRY MODAL
     ========================================== -->
<div class="modal fade" id="newPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-bag-plus me-2"></i>New Stock In & Purchase Challan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="purchaseForm">
                <input type="hidden" name="action" value="save_purchase">

                <div class="modal-body p-4">
                    <!-- Top Info Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Supplier / Vendor <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="pur_supplier_id" class="form-select" required>
                                <option value="0">Direct Local Market / Cash</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?> (<?= htmlspecialchars($s['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Challan / Bill Number</label>
                            <input type="text" name="purchase_no" id="pur_no" class="form-control font-monospace" placeholder="e.g. CHAL-2026-004">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Items Dynamic Table -->
                    <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-1 text-primary"></i>Purchase Items List</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="purchaseItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px;">Select Product / Item <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-center">Quantity</th>
                                    <th style="width: 150px;" class="text-end">Unit Price (৳)</th>
                                    <th style="width: 150px;" class="text-end">Subtotal (৳)</th>
                                    <th style="width: 50px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="pur_items_tbody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" onclick="addPurchaseRow()">
                        <i class="bi bi-plus-lg me-1"></i> Add Another Item
                    </button>

                    <!-- Bottom Calculation Row -->
                    <div class="row g-3 bg-light p-3 rounded">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Discount (৳):</label>
                            <input type="number" step="0.01" name="discount_amount" id="pur_discount" class="form-control form-control-sm text-end" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Paid Amount (৳):</label>
                            <input type="number" step="0.01" name="paid_amount" id="pur_paid" class="form-control form-control-sm text-end fw-bold text-success" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Payment Mode:</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="Cash" selected>Cash (Auto Cashbook)</option>
                                <option value="Bank">Bank Account</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Due">Full Due / Credit</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end d-flex flex-column justify-content-center">
                            <div class="small text-muted">Net Total: <b class="fs-6 text-primary" id="pur_net_total_txt">৳ 0.00</b></div>
                            <div class="small text-danger">Due: <b id="pur_due_txt">৳ 0.00</b></div>
                        </div>
                        <div class="col-12 mt-2">
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional notes or supplier memo remarks...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Save & Stock In</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     VIEW CHALLAN DETAILS MODAL
     ========================================== -->
<div class="modal fade" id="viewChallanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-receipt-cutoff me-2"></i>Purchase Challan Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewChallanModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-outline-dark" onclick="printChallan()">
                    <i class="bi bi-printer me-1"></i> Print Challan
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const itemsList = <?= json_encode($all_items) ?>;

function addPurchaseRow(defaultItemId = 0) {
    let optionsHtml = '<option value="">-- Choose Item --</option>';
    itemsList.forEach(it => {
        const isSel = (defaultItemId > 0 && it.id == defaultItemId) ? 'selected' : '';
        optionsHtml += `<option value="${it.id}" data-rate="${it.purchase_price}" ${isSel}>${it.item_name} (${it.item_code})</option>`;
    });

    const rowId = 'prow_' + Date.now() + '_' + Math.floor(Math.random() * 100);
    const rowHtml = `
        <tr id="${rowId}">
            <td>
                <select class="form-select form-select-sm pur-item-sel" onchange="onItemSelect('${rowId}')" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-center pur-qty-inp" value="1" min="1" oninput="calcRowTotal('${rowId}')" required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm text-end pur-rate-inp" value="0.00" oninput="calcRowTotal('${rowId}')" required>
            </td>
            <td class="text-end fw-bold pur-subtotal-txt">৳ 0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="$('#${rowId}').remove(); calcPurGrandTotal();">
                    <i class="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    `;
    $('#pur_items_tbody').append(rowHtml);
    if (defaultItemId > 0) {
        onItemSelect(rowId);
    }
}

function onItemSelect(rowId) {
    const row = $('#' + rowId);
    const selected = row.find('.pur-item-sel option:selected');
    const rate = parseFloat(selected.data('rate')) || 0;
    row.find('.pur-rate-inp').val(rate.toFixed(2));
    calcRowTotal(rowId);
}

function calcRowTotal(rowId) {
    const row = $('#' + rowId);
    const qty = parseInt(row.find('.pur-qty-inp').val()) || 0;
    const rate = parseFloat(row.find('.pur-rate-inp').val()) || 0;
    const subtotal = qty * rate;
    row.find('.pur-subtotal-txt').text('৳ ' + subtotal.toFixed(2));
    calcPurGrandTotal();
}

function calcPurGrandTotal() {
    let grandTotal = 0;
    $('#pur_items_tbody tr').each(function () {
        const qty = parseInt($(this).find('.pur-qty-inp').val()) || 0;
        const rate = parseFloat($(this).find('.pur-rate-inp').val()) || 0;
        grandTotal += (qty * rate);
    });

    const discount = parseFloat($('#pur_discount').val()) || 0;
    const netTotal = Math.max(0, grandTotal - discount);
    
    let paid = parseFloat($('#pur_paid').val());
    if (isNaN(paid) || paid <= 0) {
        paid = netTotal;
        $('#pur_paid').val(netTotal.toFixed(2));
    }
    const due = Math.max(0, netTotal - paid);

    $('#pur_net_total_txt').text('৳ ' + netTotal.toFixed(2));
    $('#pur_due_txt').text('৳ ' + due.toFixed(2));
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

function viewPurchaseDetails(purchaseId) {
    $('#viewChallanModalBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Fetching challan items & details...</p>
        </div>
    `);
    showModal('viewChallanModal');

    $.get('api/inventory-action.php', { action: 'get_purchase_details', purchase_id: purchaseId }, function(res) {
        if (res && res.status === 'success') {
            const p = res.purchase;
            const items = res.items || [];
            
            let itemsRows = '';
            let totalQty = 0;
            items.forEach((it, idx) => {
                totalQty += parseInt(it.qty) || 0;
                itemsRows += `
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td>
                            <span class="fw-semibold text-heading">${it.item_name}</span>
                            <small class="text-muted d-block font-monospace">${it.item_code}</small>
                        </td>
                        <td class="text-center font-monospace">${it.qty} ${it.unit_sym || 'Pcs'}</td>
                        <td class="text-end">৳ ${parseFloat(it.unit_price).toFixed(2)}</td>
                        <td class="text-end fw-bold">৳ ${parseFloat(it.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });

            const isCancelled = (parseInt(p.status) === 0);
            const statusBadge = isCancelled 
                ? '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Cancelled / Rolled Back</span>' 
                : '<span class="badge bg-success"><i class="bi bi-check2 me-1"></i> Received & Active</span>';

            const modalHtml = `
                <div id="printableChallan">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                        <div>
                            <span class="badge bg-label-primary mb-1">Procurement Challan</span>
                            <h4 class="fw-bold mb-0 text-primary font-monospace">${p.purchase_no}</h4>
                            <small class="text-muted">Recorded: ${p.created_at || p.purchase_date}</small>
                        </div>
                        <div class="text-end">
                            ${statusBadge}
                            <div class="mt-1 small text-muted">Purchase Date: <b>${p.purchase_date}</b></div>
                            <div class="small text-muted">Entry By: <b>${p.entryby || 'Admin'}</b></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 p-3 bg-light rounded">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-1"><i class="bi bi-shop me-1 text-primary"></i> Supplier Information</h6>
                            <div class="fw-bold text-heading">${p.supplier_name}</div>
                            ${p.company_name ? `<div class="small text-muted">${p.company_name}</div>` : ''}
                            ${p.supplier_phone ? `<div class="small text-muted"><i class="bi bi-telephone me-1"></i> ${p.supplier_phone}</div>` : ''}
                            ${p.supplier_address ? `<div class="small text-muted"><i class="bi bi-geo-alt me-1"></i> ${p.supplier_address}</div>` : ''}
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="fw-bold mb-1"><i class="bi bi-wallet2 me-1 text-primary"></i> Payment Summary</h6>
                            <div class="small">Payment Method: <b>${p.payment_method}</b></div>
                            <div class="small">Cashbook Voucher: <b>${p.cashbook_entry_id ? '#' + p.cashbook_entry_id : 'None'}</b></div>
                            ${p.remarks ? `<div class="small text-muted mt-1 fst-italic">"${p.remarks}"</div>` : ''}
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th>Item Description</th>
                                    <th class="text-center" style="width: 120px;">Qty</th>
                                    <th class="text-end" style="width: 130px;">Rate (৳)</th>
                                    <th class="text-end" style="width: 140px;">Subtotal (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRows || '<tr><td colspan="5" class="text-center text-muted">No item records found.</td></tr>'}
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total:</th>
                                    <th class="text-center font-monospace">${totalQty}</th>
                                    <th></th>
                                    <th class="text-end fw-bold">৳ ${parseFloat(p.total_amount).toFixed(2)}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Sub Total:</td>
                                    <td class="text-end fw-semibold">৳ ${parseFloat(p.total_amount).toFixed(2)}</td>
                                </tr>
                                ${parseFloat(p.discount_amount) > 0 ? `
                                <tr>
                                    <td class="text-muted">Discount:</td>
                                    <td class="text-end text-danger">- ৳ ${parseFloat(p.discount_amount).toFixed(2)}</td>
                                </tr>` : ''}
                                <tr class="border-top">
                                    <td class="fw-bold">Net Total:</td>
                                    <td class="text-end fw-bold text-primary fs-6">৳ ${parseFloat(p.net_amount).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-success fw-semibold">Paid Amount:</td>
                                    <td class="text-end fw-bold text-success">৳ ${parseFloat(p.paid_amount).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-danger fw-semibold">Due Balance:</td>
                                    <td class="text-end fw-bold text-danger">৳ ${parseFloat(p.due_amount).toFixed(2)}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            $('#viewChallanModalBody').html(modalHtml);
        } else {
            $('#viewChallanModalBody').html(`
                <div class="alert alert-danger mb-0">
                    ${(res && res.message) ? res.message : 'Unable to load purchase details.'}
                </div>
            `);
        }
    }, 'json').fail(function() {
        $('#viewChallanModalBody').html(`
            <div class="alert alert-danger mb-0">
                Network connection error while fetching purchase details.
            </div>
        `);
    });
}

function printChallan() {
    const printContent = document.getElementById('printableChallan');
    if (!printContent) return;
    const win = window.open('', '', 'width=800,height=700');
    win.document.write('<html><head><title>Print Purchase Challan</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    win.document.write('<style>body{padding:25px;font-family:sans-serif;}</style>');
    win.document.write('</head><body>');
    win.document.write(printContent.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(() => {
        win.print();
        win.close();
    }, 400);
}

function deletePurchase(purchaseId, challanNo) {
    Swal.fire({
        title: 'Cancel Purchase Challan?',
        html: `Are you sure you want to cancel Challan <b>#${challanNo}</b>?<br><br>
               <div class="text-start alert alert-warning p-2 small mb-0">
                 <i class="bi bi-exclamation-triangle-fill me-1"></i>
                 <b>Important:</b> This will automatically decrement current stock in inventory and void the cashbook expense voucher. If items from this challan have already been sold or issued, the operation will be safely aborted.
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, Rollback & Cancel',
        cancelButtonText: 'No, Keep It'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing Rollback...',
                text: 'Validating inventory stock and reverting entries...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post('api/inventory-action.php', {
                action: 'delete_purchase',
                purchase_id: purchaseId
            }, function(res) {
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    const msg = (res && res.message) ? res.message : 'Error cancelling purchase.';
                    Swal.fire('Cannot Cancel Purchase', msg, 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Server connection error during cancellation.', 'error');
            });
        }
    });
}

$(document).ready(function () {
    // Initial row
    const presetId = <?= $preset_item_id ?>;
    addPurchaseRow(presetId);

    if (presetId > 0) {
        showModal('newPurchaseModal');
    }

    $('#pur_discount, #pur_paid').on('input', function () {
        calcPurGrandTotal();
    });

    // Form Submit
    $('#purchaseForm').on('submit', function (e) {
        e.preventDefault();

        const items = [];
        $('#pur_items_tbody tr').each(function () {
            const itmId = $(this).find('.pur-item-sel').val();
            const qty = parseInt($(this).find('.pur-qty-inp').val()) || 0;
            const unitPrice = parseFloat($(this).find('.pur-rate-inp').val()) || 0;
            if (itmId && qty > 0) {
                items.push({ item_id: itmId, qty: qty, unit_price: unitPrice });
            }
        });

        if (items.length === 0) {
            Swal.fire('Warning', 'Please select at least one item with valid quantity.', 'warning');
            return;
        }

        const formData = $(this).serializeArray();
        formData.push({ name: 'items', value: JSON.stringify(items) });

        const $btn = $(this).find('button[type="submit"]');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.post('api/inventory-action.php', formData, function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                hideModal('newPurchaseModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Purchase Recorded!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const msg = (res && res.message) ? res.message : 'Error saving purchase.';
                Swal.fire('Error', msg, 'error');
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html(origHtml);
            Swal.fire('Error', 'Server connection error.', 'error');
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
