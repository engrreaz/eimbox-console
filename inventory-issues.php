<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

// Fetch all available items in stock
$items_res = $conn->query("SELECT i.id, i.item_name, i.item_code, i.current_stock, COALESCE(u.unit_symbol, 'Pcs') as unit_sym 
    FROM inv_items i 
    LEFT JOIN inv_units u ON i.unit_id = u.id 
    WHERE i.sccode = '$sccode' AND i.status = 1 AND i.current_stock > 0 
    ORDER BY i.item_name ASC");
$avail_items = [];
while ($it = $items_res->fetch_assoc()) { $avail_items[] = $it; }

// Fetch Issues History
$issues_res = $conn->query("SELECT iss.*, 
    COUNT(isi.id) as item_types_count, 
    SUM(isi.qty) as total_issued_qty 
    FROM inv_issues iss 
    LEFT JOIN inv_issue_items isi ON iss.id = isi.issue_id 
    WHERE iss.sccode = '$sccode' 
    GROUP BY iss.id 
    ORDER BY iss.issue_date DESC, iss.id DESC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-arrow-right me-2 text-secondary"></i>Internal Requisitions & Material Distribution</h4>
            <p class="text-muted mb-0">Track stationery, office supplies and consumables issued to teachers, staff and classrooms.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#newIssueModal">
                <i class="bi bi-plus-circle me-1"></i> Issue Consumables
            </button>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Issues History Table Card -->
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>Distribution & Requisition Log</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="issuesTable">
                <thead class="table-light">
                    <tr>
                        <th>Issue Voucher No</th>
                        <th>Date</th>
                        <th>Recipient / Department</th>
                        <th>Recipient Type</th>
                        <th class="text-center">Items Issued</th>
                        <th>Purpose / Remarks</th>
                        <th>Issued By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($issues_res && $issues_res->num_rows > 0): ?>
                        <?php while ($iss = $issues_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-secondary font-monospace"><?= htmlspecialchars($iss['issue_no']) ?></span>
                                </td>
                                <td><?= date('d M, Y', strtotime($iss['issue_date'])) ?></td>
                                <td>
                                    <span class="fw-semibold text-heading"><?= htmlspecialchars($iss['issued_to_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-label-info"><?= htmlspecialchars($iss['issued_to_type']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-primary"><?= number_format($iss['total_issued_qty'] ?? 0) ?> Units (<?= $iss['item_types_count'] ?> items)</span>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($iss['purpose'] ?? 'General consumption') ?></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($iss['issued_by']) ?></small></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No internal issues recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     NEW REQUISITION / ISSUE MODAL
     ========================================== -->
<div class="modal fade" id="newIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-secondary"><i class="bi bi-box-arrow-right me-2"></i>Issue Internal Consumables</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="issueForm">
                <input type="hidden" name="action" value="save_issue">

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Recipient Type</label>
                            <select name="issued_to_type" class="form-select" required>
                                <option value="Department">Department / Office</option>
                                <option value="Teacher">Teacher / Faculty</option>
                                <option value="Staff">Staff / Admin</option>
                                <option value="Classroom">Classroom / Lab</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Recipient Name / Room <span class="text-danger">*</span></label>
                            <input type="text" name="issued_to_name" class="form-control" placeholder="e.g. Science Lab, Exam Committee, Mr. Anisur Rahman" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Purpose of Requisition</label>
                            <input type="text" name="purpose" class="form-control" placeholder="e.g. Monthly whiteboard markers for teachers, Final exam answer sheet papers...">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-1 text-secondary"></i>Items to Issue</h6>
                    <div class="table-responsive mb-2">
                        <table class="table table-bordered align-middle" id="issueItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px;">Select Item <span class="text-danger">*</span></th>
                                    <th style="width: 140px;" class="text-center">Issue Quantity</th>
                                    <th style="width: 50px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="issue_items_tbody">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" onclick="addIssueRow()">
                        <i class="bi bi-plus-lg me-1"></i> Add Another Item
                    </button>
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Issue & Deduct Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const availableItems = <?= json_encode($avail_items) ?>;

function addIssueRow() {
    let optionsHtml = '<option value="">-- Choose Item --</option>';
    availableItems.forEach(it => {
        optionsHtml += `<option value="${it.id}" data-stock="${it.current_stock}">${it.item_name} (Stock: ${it.current_stock} ${it.unit_sym})</option>`;
    });

    const rowId = 'irow_' + Date.now() + '_' + Math.floor(Math.random() * 100);
    const rowHtml = `
        <tr id="${rowId}">
            <td>
                <select class="form-select form-select-sm issue-item-sel" onchange="onIssueItemChange('${rowId}')" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-center issue-qty-inp" value="1" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="$('#${rowId}').remove();">
                    <i class="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    `;
    $('#issue_items_tbody').append(rowHtml);
}

function onIssueItemChange(rowId) {
    const row = $('#' + rowId);
    const selected = row.find('.issue-item-sel option:selected');
    const stock = parseInt(selected.data('stock')) || 1;
    row.find('.issue-qty-inp').attr('max', stock);
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

$(document).ready(function () {
    addIssueRow();

    $('#issueForm').on('submit', function (e) {
        e.preventDefault();

        const items = [];
        $('#issue_items_tbody tr').each(function () {
            const itmId = $(this).find('.issue-item-sel').val();
            const qty = parseInt($(this).find('.issue-qty-inp').val()) || 0;
            if (itmId && qty > 0) {
                items.push({ item_id: itmId, qty: qty });
            }
        });

        if (items.length === 0) {
            Swal.fire('Warning', 'Please select at least one item to issue.', 'warning');
            return;
        }

        const formData = $(this).serializeArray();
        formData.push({ name: 'items', value: JSON.stringify(items) });

        const $btn = $(this).find('button[type="submit"]');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Issuing...');

        $.post('api/inventory-action.php', formData, function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                hideModal('newIssueModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Issued Successfully!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const msg = (res && res.message) ? res.message : 'Error saving issue.';
                Swal.fire('Error', msg, 'error');
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html(origHtml);
            Swal.fire('Error', 'Server error while issuing items.', 'error');
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
