<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

$filter_room = trim($_GET['filter_room'] ?? 'all');
$filter_cond = trim($_GET['filter_cond'] ?? 'all');

// Fetch Asset Categories (Tenant & Global sccode=0)
$cats_res = $conn->query("SELECT * FROM inv_categories WHERE (sccode = '$sccode' OR sccode = 0) AND (category_type = 'Asset' OR status = 1) ORDER BY (sccode = '$sccode') DESC, category_name ASC");
$categories = [];
while ($c = $cats_res->fetch_assoc()) { $categories[] = $c; }

// Fetch Distinct Rooms for filter
$rooms_res = $conn->query("SELECT DISTINCT location_room FROM fixed_assets WHERE sccode = '$sccode' AND location_room IS NOT NULL AND location_room != '' ORDER BY location_room ASC");
$rooms = [];
while ($r = $rooms_res->fetch_assoc()) { $rooms[] = $r['location_room']; }

// Query Assets
$where_clauses = ["fa.sccode = '$sccode'"];
if ($filter_room !== 'all' && !empty($filter_room)) {
    $where_clauses[] = "fa.location_room = '" . $conn->real_escape_string($filter_room) . "'";
}
if ($filter_cond !== 'all' && !empty($filter_cond)) {
    $where_clauses[] = "fa.asset_condition = '" . $conn->real_escape_string($filter_cond) . "'";
}
$where_sql = implode(' AND ', $where_clauses);

$assets_res = $conn->query("SELECT fa.*, COALESCE(c.category_name, 'General Asset') as cat_name 
    FROM fixed_assets fa 
    LEFT JOIN inv_categories c ON fa.category_id = c.id 
    WHERE $where_sql 
    ORDER BY fa.purchase_date DESC, fa.id DESC");

// Valuation summary
$summary = $conn->query("SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(purchase_cost), 0) as total_purchase,
    COALESCE(SUM(current_valuation), 0) as total_current
    FROM fixed_assets WHERE sccode = '$sccode'")->fetch_assoc();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-buildings me-2 text-warning"></i>Institutional Fixed Assets Register</h4>
            <p class="text-muted mb-0">Track computers, projectors, lab apparatus, AC, furniture and room-wise custodians.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#assetModal" onclick="openAddAssetModal()">
                <i class="bi bi-plus-circle me-1"></i> Register New Asset
            </button>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-border-shadow-primary h-100 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-primary rounded p-2"><i class="bi bi-boxes fs-3"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold"><?= number_format($summary['total_count'] ?? 0) ?></h5>
                            <small class="text-muted">Total Registered Assets</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-border-shadow-info h-100 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-info rounded p-2"><i class="bi bi-receipt fs-3"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">৳ <?= number_format($summary['total_purchase'] ?? 0, 2) ?></h5>
                            <small class="text-muted">Original Purchase Value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-border-shadow-success h-100 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 bg-label-success rounded p-2"><i class="bi bi-cash-stack fs-3"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">৳ <?= number_format($summary['total_current'] ?? 0, 2) ?></h5>
                            <small class="text-muted">Current Asset Valuation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Room / Lab Location:</label>
                    <select name="filter_room" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">-- All Rooms & Locations --</option>
                        <?php foreach ($rooms as $rm): ?>
                            <option value="<?= htmlspecialchars($rm) ?>" <?= ($filter_room === $rm) ? 'selected' : '' ?>><?= htmlspecialchars($rm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Asset Condition:</label>
                    <select name="filter_cond" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= ($filter_cond === 'all') ? 'selected' : '' ?>>All Conditions</option>
                        <option value="Good" <?= ($filter_cond === 'Good') ? 'selected' : '' ?>>Good (Functional)</option>
                        <option value="Under Repair" <?= ($filter_cond === 'Under Repair') ? 'selected' : '' ?>>Under Repair</option>
                        <option value="Damaged" <?= ($filter_cond === 'Damaged') ? 'selected' : '' ?>>Damaged / Broken</option>
                        <option value="Disposed" <?= ($filter_cond === 'Disposed') ? 'selected' : '' ?>>Disposed / Retired</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-3">
                    <a href="fixed-assets.php" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Assets Table Card -->
    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="assetsTable">
                <thead class="table-light">
                    <tr>
                        <th>Asset Tag Code</th>
                        <th>Asset Name</th>
                        <th>Location / Room</th>
                        <th>Custodian</th>
                        <th class="text-end">Original Cost</th>
                        <th class="text-end">Current Value</th>
                        <th class="text-center">Condition</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($assets_res && $assets_res->num_rows > 0): ?>
                        <?php while ($a = $assets_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-warning font-monospace"><i class="bi bi-qr-code me-1"></i><?= htmlspecialchars($a['asset_tag_code']) ?></span>
                                    <small class="text-muted d-block"><?= date('d M, Y', strtotime($a['purchase_date'])) ?></small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-heading"><?= htmlspecialchars($a['asset_name']) ?></span>
                                    <small class="badge bg-label-secondary d-block mt-1" style="width: fit-content;"><?= htmlspecialchars($a['cat_name']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($a['location_room']) ?></span>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($a['custodian_name'] ?: 'Not Assigned') ?></small></td>
                                <td class="text-end">৳ <?= number_format($a['purchase_cost'], 2) ?></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($a['current_valuation'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ($a['asset_condition'] === 'Good'): ?>
                                        <span class="badge bg-label-success"><i class="bi bi-check-circle me-1"></i>Good</span>
                                    <?php elseif ($a['asset_condition'] === 'Under Repair'): ?>
                                        <span class="badge bg-label-warning"><i class="bi bi-tools me-1"></i>In Repair</span>
                                    <?php elseif ($a['asset_condition'] === 'Damaged'): ?>
                                        <span class="badge bg-label-danger"><i class="bi bi-x-circle me-1"></i>Damaged</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">Disposed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick='openEditAssetModal(<?= json_encode($a) ?>)'>
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Details
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteAsset(<?= $a['id'] ?>, '<?= addslashes($a['asset_name']) ?>')">
                                                    <i class="bi bi-trash3 text-danger me-2"></i> Delete Asset
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No fixed assets registered yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     ADD / EDIT ASSET MODAL
     ========================================== -->
<div class="modal fade" id="assetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="assetModalTitle"><i class="bi bi-building me-2 text-warning"></i>Register Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assetForm">
                <input type="hidden" name="action" value="save_asset">
                <input type="hidden" name="asset_id" id="ast_id" value="0">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Asset / Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" name="asset_name" id="ast_name" class="form-control" placeholder="e.g. Dell OptiPlex Desktop Core i5, Epson EB-E01 Projector" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Asset Tag Code</label>
                            <input type="text" name="asset_tag_code" id="ast_tag" class="form-control font-monospace" placeholder="e.g. AST-2026-004">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" id="ast_cat" class="form-select">
                                <option value="0">-- General Asset --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / Room <span class="text-danger">*</span></label>
                            <input type="text" name="location_room" id="ast_room" class="form-control" placeholder="e.g. Computer Lab 1, Principal Office, Room 202" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" id="ast_pur_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Cost (৳)</label>
                            <input type="number" step="0.01" name="purchase_cost" id="ast_cost" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Current Valuation (৳)</label>
                            <input type="number" step="0.01" name="current_valuation" id="ast_val" class="form-control text-success fw-bold" value="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Custodian / Teacher</label>
                            <input type="text" name="custodian_name" id="ast_custodian" class="form-control" placeholder="e.g. Lab Assistant / Engr. Reaz">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Condition Status</label>
                            <select name="asset_condition" id="ast_cond" class="form-select">
                                <option value="Good">Good (Working)</option>
                                <option value="Under Repair">Under Repair</option>
                                <option value="Damaged">Damaged / Defective</option>
                                <option value="Disposed">Disposed / Auctioned</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Warranty Expiry</label>
                            <input type="date" name="warranty_expiry" id="ast_warranty" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Serial Number</label>
                            <textarea name="remarks" id="ast_remarks" class="form-control" rows="2" placeholder="e.g. Serial: SN984729482, Brand: Dell, Model: 3080"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Save Asset</button>
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

function openAddAssetModal() {
    $('#assetModalTitle').html('<i class="bi bi-building me-2 text-warning"></i>Register Asset');
    $('#ast_id').val(0);
    $('#assetForm')[0].reset();
    $('#ast_tag').val('AST-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000));
    showModal('assetModal');
}

function openEditAssetModal(a) {
    $('#assetModalTitle').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Edit Asset Record');
    $('#ast_id').val(a.id);
    $('#ast_name').val(a.asset_name);
    $('#ast_tag').val(a.asset_tag_code);
    $('#ast_cat').val(a.category_id);
    $('#ast_room').val(a.location_room);
    $('#ast_pur_date').val(a.purchase_date);
    $('#ast_cost').val(a.purchase_cost);
    $('#ast_val').val(a.current_valuation);
    $('#ast_custodian').val(a.custodian_name || '');
    $('#ast_cond').val(a.asset_condition);
    $('#ast_warranty').val(a.warranty_expiry || '');
    $('#ast_remarks').val(a.remarks || '');
    showModal('assetModal');
}

function deleteAsset(id, name) {
    Swal.fire({
        title: 'Delete Asset?',
        html: `Are you sure you want to delete asset <b>${name}</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('api/inventory-action.php', { action: 'delete_asset', asset_id: id }, function (resp) {
                if (resp && resp.status === 'success') {
                    Swal.fire('Deleted', resp.message, 'success').then(() => location.reload());
                } else {
                    const msg = (resp && resp.message) ? resp.message : 'Error removing asset.';
                    Swal.fire('Error', msg, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Server communication error.', 'error');
            });
        }
    });
}

$(document).ready(function () {
    $('#assetForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                hideModal('assetModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const msg = (res && res.message) ? res.message : 'Failed to save asset.';
                Swal.fire('Error', msg, 'error');
            }
        }, 'json').fail(function () {
            $btn.prop('disabled', false).html(origHtml);
            Swal.fire('Error', 'Server connection failed. Please try again.', 'error');
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
