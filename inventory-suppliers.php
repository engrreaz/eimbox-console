<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

// Fetch Suppliers with aggregated purchase data
$suppliers_res = $conn->query("SELECT s.*, 
    COUNT(p.id) as total_purchases, 
    COALESCE(SUM(p.net_amount), 0) as total_bought, 
    COALESCE(SUM(p.paid_amount), 0) as total_paid, 
    COALESCE(SUM(p.due_amount), 0) as total_due 
    FROM inv_suppliers s 
    LEFT JOIN inv_purchases p ON s.id = p.supplier_id 
    WHERE s.sccode = '$sccode' AND s.status = 1 
    GROUP BY s.id 
    ORDER BY s.supplier_name ASC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-people me-2 text-primary"></i>Suppliers & Vendor Directory</h4>
            <p class="text-muted mb-0">Manage suppliers, wholesale publishers, contact details and ledger balances.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary shadow-sm" onclick="openAddSupplierModal()">
                <i class="bi bi-person-plus me-1"></i> Add New Supplier
            </button>
            <a href="inventory-purchases.php" class="btn btn-outline-success">
                <i class="bi bi-bag-plus me-1"></i> Purchases
            </a>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Supplier / Contact Person</th>
                        <th>Company / Store Name</th>
                        <th>Phone & Email</th>
                        <th>Address</th>
                        <th class="text-center">Purchases</th>
                        <th class="text-end">Total Bought</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($suppliers_res && $suppliers_res->num_rows > 0): ?>
                        <?php while ($s = $suppliers_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-heading"><?= htmlspecialchars($s['supplier_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary"><?= htmlspecialchars($s['company_name'] ?? 'Local Vendor') ?></span>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($s['phone']) ?></div>
                                    <?php if (!empty($s['email'])): ?>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($s['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($s['address'] ?? 'N/A') ?></small></td>
                                <td class="text-center"><span class="badge bg-label-info"><?= $s['total_purchases'] ?> Bills</span></td>
                                <td class="text-end fw-semibold">৳ <?= number_format($s['total_bought'], 2) ?></td>
                                <td class="text-end">
                                    <?php if ($s['total_due'] > 0): ?>
                                        <span class="fw-bold text-danger">৳ <?= number_format($s['total_due'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-label-success">Clear</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick='openEditSupplierModal(<?= json_encode($s) ?>)'>
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Supplier
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="inventory-purchases.php?supplier_id=<?= $s['id'] ?>">
                                                    <i class="bi bi-receipt text-info me-2"></i> View Invoices
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteSupplier(<?= $s['id'] ?>, '<?= addslashes($s['supplier_name']) ?>')">
                                                    <i class="bi bi-trash3 text-danger me-2"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No suppliers added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     ADD / EDIT SUPPLIER MODAL
     ========================================== -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="supplierModalTitle"><i class="bi bi-person-plus me-2 text-primary"></i>Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="supplierForm">
                <input type="hidden" name="action" value="save_supplier">
                <input type="hidden" name="supplier_id" id="sup_id" value="0">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier / Contact Person Name <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" id="sup_name" class="form-control" placeholder="e.g. Md. Rafiqul Islam" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company / Press / Shop Name</label>
                        <input type="text" name="company_name" id="sup_company" class="form-control" placeholder="e.g. Anupam Stationery & Press, Dhaka">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Phone / Mobile <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="sup_phone" class="form-control" placeholder="017XXXXXXXX" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" id="sup_email" class="form-control" placeholder="vendor@domain.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" id="sup_address" class="form-control" rows="2" placeholder="e.g. Banglabazar, Dhaka-1100"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="saveSupplierBtn" class="btn btn-primary shadow-sm"><i class="bi bi-save me-1"></i> Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(el);
            modalInstance.show();
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

function openAddSupplierModal() {
    $('#supplierModalTitle').html('<i class="bi bi-person-plus me-2 text-primary"></i>Add Supplier');
    $('#sup_id').val(0);
    $('#supplierForm')[0].reset();
    showModal('supplierModal');
}

function openEditSupplierModal(s) {
    $('#supplierModalTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Supplier');
    $('#sup_id').val(s.id);
    $('#sup_name').val(s.supplier_name);
    $('#sup_company').val(s.company_name || '');
    $('#sup_phone').val(s.phone);
    $('#sup_email').val(s.email || '');
    $('#sup_address').val(s.address || '');
    showModal('supplierModal');
}

function deleteSupplier(id, name) {
    Swal.fire({
        title: 'Delete Supplier?',
        html: `Are you sure you want to remove <b>${name}</b> from suppliers list?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api/inventory-action.php', { action: 'delete_supplier', supplier_id: id }, function (res) {
                if (res && res.status === 'success') {
                    Swal.fire('Deleted', res.message, 'success').then(() => location.reload());
                } else {
                    const msg = (res && res.message) ? res.message : 'Error removing supplier.';
                    Swal.fire('Error', msg, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Server communication error.', 'error');
            });
        }
    });
}

$(document).ready(function () {
    $('#supplierForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#saveSupplierBtn');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                hideModal('supplierModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const msg = (res && res.message) ? res.message : 'Failed to save supplier.';
                Swal.fire('Error', msg, 'error');
            }
        }, 'json').fail(function (xhr) {
            $btn.prop('disabled', false).html(origHtml);
            console.error('AJAX Error:', xhr.responseText);
            Swal.fire('Error', 'Server connection failed. Please try again.', 'error');
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
