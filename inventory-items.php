<?php
require_once 'header.php';
require_once 'core/inventory_db.php';

init_inventory_database($conn);

// Filter Parameters
$filter_cat = intval($_GET['filter_cat'] ?? 0);
$filter_type = trim($_GET['filter_type'] ?? 'all');
$filter_stock = trim($_GET['filter_stock'] ?? 'all');

// Fetch Master Categories & Units for Dropdowns (Tenant & Global sccode=0)
$categories_res = $conn->query("SELECT * FROM inv_categories WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY (sccode = '$sccode') DESC, category_name ASC");
$categories = [];
while ($c = $categories_res->fetch_assoc()) { $categories[] = $c; }

$units_res = $conn->query("SELECT * FROM inv_units WHERE (sccode = '$sccode' OR sccode = 0) AND status = 1 ORDER BY (sccode = '$sccode') DESC, unit_name ASC");
$units = [];
while ($u = $units_res->fetch_assoc()) { $units[] = $u; }

// Build Query
$where_clauses = ["i.sccode = '$sccode'", "i.status = 1"];
if ($filter_cat > 0) {
    $where_clauses[] = "i.category_id = $filter_cat";
}
if ($filter_type !== 'all' && in_array($filter_type, ['SaleItem', 'Consumable', 'Asset'])) {
    $where_clauses[] = "i.item_type = '$filter_type'";
}
if ($filter_stock === 'low') {
    $where_clauses[] = "i.current_stock <= i.reorder_level AND i.current_stock > 0";
} elseif ($filter_stock === 'out') {
    $where_clauses[] = "i.current_stock <= 0";
} elseif ($filter_stock === 'in') {
    $where_clauses[] = "i.current_stock > i.reorder_level";
}

$where_sql = implode(' AND ', $where_clauses);
$items_res = $conn->query("SELECT i.*, 
    COALESCE(c.category_name, 'General') AS cat_name, 
    COALESCE(u.unit_symbol, 'Pcs') AS unit_sym 
    FROM inv_items i 
    LEFT JOIN inv_categories c ON i.category_id = c.id 
    LEFT JOIN inv_units u ON i.unit_id = u.id 
    WHERE $where_sql 
    ORDER BY i.item_name ASC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i>Product & Inventory Master</h4>
            <p class="text-muted mb-0">Manage catalog, barcodes, pricing, units and reorder thresholds.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary shadow-sm" onclick="openAddItemModal()">
                <i class="bi bi-plus-circle me-1"></i> Add New Product
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="openCategoriesModal()">
                <i class="bi bi-tags me-1"></i> Categories
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="openUnitsModal()">
                <i class="bi bi-rulers me-1"></i> Units
            </button>
            <a href="inventory-dashboard.php" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Category:</label>
                    <select name="filter_cat" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">-- All Categories --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filter_cat == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?> (<?= $cat['category_type'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Item Type:</label>
                    <select name="filter_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= ($filter_type === 'all') ? 'selected' : '' ?>>All Types</option>
                        <option value="SaleItem" <?= ($filter_type === 'SaleItem') ? 'selected' : '' ?>>Student Sale Item</option>
                        <option value="Consumable" <?= ($filter_type === 'Consumable') ? 'selected' : '' ?>>Internal Consumable</option>
                        <option value="Asset" <?= ($filter_type === 'Asset') ? 'selected' : '' ?>>Fixed Asset</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Stock Status:</label>
                    <select name="filter_stock" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= ($filter_stock === 'all') ? 'selected' : '' ?>>All Stock Levels</option>
                        <option value="in" <?= ($filter_stock === 'in') ? 'selected' : '' ?>>In Stock (Sufficient)</option>
                        <option value="low" <?= ($filter_stock === 'low') ? 'selected' : '' ?>>Low Stock Alert</option>
                        <option value="out" <?= ($filter_stock === 'out') ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-3">
                    <a href="inventory-items.php" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Data Table Card -->
    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="itemsMasterTable">
                <thead class="table-light">
                    <tr>
                        <th>Item Code / Barcode</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="text-end">Purchase Rate</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items_res && $items_res->num_rows > 0): ?>
                        <?php while ($it = $items_res->fetch_assoc()): ?>
                            <?php 
                                $is_out = ($it['current_stock'] <= 0);
                                $is_low = ($it['current_stock'] <= $it['reorder_level'] && !$is_out);
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($it['item_code']) ?></span>
                                    <?php if (!empty($it['barcode'])): ?>
                                        <small class="text-muted d-block font-monospace"><i class="bi bi-upc-scan me-1"></i><?= htmlspecialchars($it['barcode']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-heading"><?= htmlspecialchars($it['item_name']) ?></span>
                                    <?php if (!empty($it['location_rack'])): ?>
                                        <small class="text-muted d-block"><i class="bi bi-geo-alt me-1"></i>Rack: <?= htmlspecialchars($it['location_rack']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-label-secondary"><?= htmlspecialchars($it['cat_name']) ?></span></td>
                                <td>
                                    <?php if ($it['item_type'] === 'SaleItem'): ?>
                                        <span class="badge bg-label-primary"><i class="bi bi-cart-check me-1"></i>Sale Item</span>
                                    <?php elseif ($it['item_type'] === 'Consumable'): ?>
                                        <span class="badge bg-label-info"><i class="bi bi-pencil me-1"></i>Consumable</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-warning"><i class="bi bi-building me-1"></i>Asset</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">৳ <?= number_format($it['purchase_price'], 2) ?></td>
                                <td class="text-end fw-bold text-success">৳ <?= number_format($it['sale_price'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ($is_out): ?>
                                        <span class="badge bg-danger">0 <?= htmlspecialchars($it['unit_sym']) ?></span>
                                    <?php elseif ($is_low): ?>
                                        <span class="badge bg-warning"><?= $it['current_stock'] ?> <?= htmlspecialchars($it['unit_sym']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-label-success"><?= $it['current_stock'] ?> <?= htmlspecialchars($it['unit_sym']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge bg-label-secondary"><?= $it['reorder_level'] ?></span></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick='openEditItemModal(<?= json_encode($it) ?>)'>
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick='openAdjustmentModal(<?= $it['id'] ?>, <?= json_encode($it['item_name']) ?>, <?= $it['current_stock'] ?>)'>
                                                    <i class="bi bi-sliders text-warning me-2"></i> Adjust Stock / Damage
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteItem(<?= $it['id'] ?>, '<?= addslashes($it['item_name']) ?>')">
                                                    <i class="bi bi-trash3 text-danger me-2"></i> Delete Item
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No products found matching the criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     ADD / EDIT PRODUCT MODAL
     ========================================== -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="itemModalTitle"><i class="bi bi-box-seam me-2 text-primary"></i>Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="itemForm">
                <input type="hidden" name="action" value="save_item">
                <input type="hidden" name="item_id" id="form_item_id" value="0">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Item / Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" id="form_item_name" class="form-control" placeholder="e.g. Student School Diary 2026, Geometry Box" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Item Type</label>
                            <select name="item_type" id="form_item_type" class="form-select">
                                <option value="SaleItem">Student Sale Item (Store POS)</option>
                                <option value="Consumable">Internal Consumable (Office/Class)</option>
                                <option value="Asset">Fixed Asset</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Category <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-semibold" onclick="openCategoriesModal()" title="Add or manage categories">
                                    <i class="bi bi-plus-circle me-1"></i>Add / Manage
                                </button>
                            </div>
                            <select name="category_id" id="form_category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?> (<?= $cat['category_type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Unit of Measurement <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-semibold" onclick="openUnitsModal()" title="Add or manage measurement units">
                                    <i class="bi bi-plus-circle me-1"></i>Add / Manage
                                </button>
                            </div>
                            <select name="unit_id" id="form_unit_id" class="form-select" required>
                                <option value="">-- Select Unit --</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unit_name']) ?> (<?= $u['unit_symbol'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Item SKU / System Code</label>
                            <div class="input-group">
                                <input type="text" name="item_code" id="form_item_code" class="form-control font-monospace" placeholder="e.g. ITM-260901">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateAutoCode()"><i class="bi bi-arrow-repeat"></i> Auto</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Barcode / EAN</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" id="form_barcode" class="form-control font-monospace" placeholder="Scan or enter barcode...">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Price (৳)</label>
                            <input type="number" step="0.01" name="purchase_price" id="form_purchase_price" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sale Price / MRP (৳)</label>
                            <input type="number" step="0.01" name="sale_price" id="form_sale_price" class="form-control text-success fw-bold" value="0.00">
                        </div>
                        <div class="col-md-4" id="opening_stock_col">
                            <label class="form-label fw-semibold">Opening Stock Qty</label>
                            <input type="number" name="current_stock" id="form_current_stock" class="form-control" value="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reorder Alert Level</label>
                            <input type="number" name="reorder_level" id="form_reorder_level" class="form-control" value="5" title="Alert will be shown when stock drops to or below this quantity">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rack / Shelf Location</label>
                            <input type="text" name="location_rack" id="form_location_rack" class="form-control" placeholder="e.g. Rack A-3, Room 102">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveItemBtn"><i class="bi bi-save me-1"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     STOCK ADJUSTMENT / DAMAGE MODAL
     ========================================== -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2 text-warning"></i>Adjust Stock / Log Damage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustmentForm">
                <input type="hidden" name="action" value="adjust_stock">
                <input type="hidden" name="item_id" id="adj_item_id">

                <div class="modal-body p-4">
                    <div class="alert alert-light border py-2 mb-3">
                        <strong id="adj_item_name_txt">Product Name</strong>
                        <div class="small text-muted">Current Stock: <b id="adj_curr_stock_txt">0</b> Units</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Type</label>
                        <select name="adjustment_type" id="adj_type" class="form-select" required>
                            <option value="Reduction">Damage / Broken / Lost (Deduct Stock)</option>
                            <option value="Addition">Found Extra / Audit Add (Increase Stock)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity to Adjust</label>
                        <input type="number" name="qty" id="adj_qty" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason / Audit Notes</label>
                        <textarea name="reason" id="adj_reason" class="form-control" rows="2" placeholder="e.g. Water damage during rain, physical count discrepancy..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i> Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     CATEGORY QUICK MODAL (2-COLUMN SPLIT)
     ========================================== -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Category Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Left: Entry Form -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-plus-circle me-1"></i>Add New Category</h6>
                        <div id="cat_feedback_alert"></div>
                        <form id="categoryForm">
                            <input type="hidden" name="action" value="save_category">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="category_name" id="cat_inp_name" class="form-control" placeholder="e.g. School Bags, Chemistry Lab, Books" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Category Type</label>
                                <select name="category_type" id="cat_inp_type" class="form-select">
                                    <option value="SaleItem">Student Sale Item</option>
                                    <option value="Consumable">Internal Consumable</option>
                                    <option value="Asset">Fixed Asset</option>
                                </select>
                            </div>
                            <button type="submit" id="save_cat_btn" class="btn btn-primary w-100 shadow-sm"><i class="bi bi-plus-lg me-1"></i> Save Category</button>
                        </form>
                    </div>

                    <!-- Right: Saved Categories Table -->
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-heading"><i class="bi bi-list-check me-1"></i>Saved Categories</h6>
                            <span class="badge bg-label-primary" id="cat_count_badge"><?= count($categories) ?> Categories</span>
                        </div>
                        <div class="table-responsive overflow-auto border rounded" id="cat_table_container" style="max-height: 320px;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Type</th>
                                        <th class="text-center" style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="categories_tbody">
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $c): ?>
                                            <?php 
                                                $is_glob = (intval($c['sccode'] ?? 0) === 0);
                                                $type_badge = '<span class="badge bg-label-primary">SaleItem</span>';
                                                if ($c['category_type'] === 'Consumable') $type_badge = '<span class="badge bg-label-info">Consumable</span>';
                                                if ($c['category_type'] === 'Asset') $type_badge = '<span class="badge bg-label-warning">Asset</span>';
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold"><?= htmlspecialchars($c['category_name']) ?></span>
                                                    <?php if ($is_glob): ?>
                                                        <span class="badge bg-label-secondary ms-1" style="font-size: 0.65rem;">System</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $type_badge ?></td>
                                                <td class="text-center">
                                                    <?php if ($is_glob): ?>
                                                        <span class="badge bg-label-secondary" title="Global System Category"><i class="bi bi-shield-lock"></i></span>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-link text-danger p-0" onclick="deleteCategory(<?= $c['id'] ?>, '<?= addslashes($c['category_name']) ?>')">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted">No categories saved yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     UNIT QUICK MODAL (2-COLUMN SPLIT)
     ========================================== -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-rulers me-2 text-primary"></i>Measurement Units Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Left: Entry Form -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-plus-circle me-1"></i>Add New Unit</h6>
                        <div id="unit_feedback_alert"></div>
                        <form id="unitForm">
                            <input type="hidden" name="action" value="save_unit">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Unit Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="unit_name" id="unit_inp_name" class="form-control" placeholder="e.g. Piece, Packet, Dozen, Kilogram, Box" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Unit Symbol / Abbreviation <span class="text-danger">*</span></label>
                                <input type="text" name="unit_symbol" id="unit_inp_symbol" class="form-control" placeholder="e.g. Pcs, Pkt, Dzn, Kg, Box, Set" required>
                            </div>
                            <button type="submit" id="save_unit_btn" class="btn btn-primary w-100 shadow-sm"><i class="bi bi-plus-lg me-1"></i> Save Unit</button>
                        </form>
                    </div>

                    <!-- Right: Saved Units Table -->
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-heading"><i class="bi bi-list-check me-1"></i>Saved Measurement Units</h6>
                            <span class="badge bg-label-primary" id="unit_count_badge"><?= count($units) ?> Units</span>
                        </div>
                        <div class="table-responsive overflow-auto border rounded" id="units_table_container" style="max-height: 320px;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Unit Name</th>
                                        <th class="text-center">Symbol</th>
                                        <th class="text-center" style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="units_tbody">
                                    <?php if (!empty($units)): ?>
                                        <?php foreach ($units as $u): ?>
                                            <?php $is_glob = (intval($u['sccode'] ?? 0) === 0); ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold"><?= htmlspecialchars($u['unit_name']) ?></span>
                                                    <?php if ($is_glob): ?>
                                                        <span class="badge bg-label-secondary ms-1" style="font-size: 0.65rem;">System</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><span class="badge bg-label-primary"><?= htmlspecialchars($u['unit_symbol']) ?></span></td>
                                                <td class="text-center">
                                                    <?php if ($is_glob): ?>
                                                        <span class="badge bg-label-secondary" title="Global System Unit"><i class="bi bi-shield-lock"></i></span>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-link text-danger p-0" onclick="deleteUnit(<?= $u['id'] ?>, '<?= addslashes($u['unit_name']) ?>')">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted">No units saved yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) {
        console.error('Modal element not found:', modalId);
        return;
    }
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(el);
            modalInstance.show();
            return;
        }
    } catch (e) {
        console.warn('Bootstrap modal instance error:', e);
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
        console.warn('Bootstrap hide modal error:', e);
    }
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        $('#' + modalId).modal('hide');
    }
}

function openUnitsModal() {
    loadUnitsList();
    showModal('unitModal');
}

function openCategoriesModal() {
    loadCategoriesList();
    showModal('categoryModal');
}

function generateAutoCode() {
    const code = 'ITM-' + Math.floor(100000 + Math.random() * 900000);
    $('#form_item_code').val(code);
}

function openAddItemModal() {
    $('#itemModalTitle').html('<i class="bi bi-plus-circle me-2 text-primary"></i>Add New Product');
    $('#form_item_id').val(0);
    $('#itemForm')[0].reset();
    $('#opening_stock_col').removeClass('d-none');
    generateAutoCode();
    showModal('itemModal');
}

function openEditItemModal(it) {
    $('#itemModalTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Product Details');
    $('#form_item_id').val(it.id);
    $('#form_item_name').val(it.item_name);
    $('#form_item_code').val(it.item_code);
    $('#form_barcode').val(it.barcode || '');
    $('#form_item_type').val(it.item_type);
    $('#form_category_id').val(it.category_id);
    $('#form_unit_id').val(it.unit_id);
    $('#form_purchase_price').val(it.purchase_price);
    $('#form_sale_price').val(it.sale_price);
    $('#form_reorder_level').val(it.reorder_level);
    $('#form_location_rack').val(it.location_rack || '');
    $('#opening_stock_col').addClass('d-none');
    showModal('itemModal');
}

function openAdjustmentModal(id, name, stock) {
    $('#adj_item_id').val(id);
    $('#adj_item_name_txt').text(name);
    $('#adj_curr_stock_txt').text(stock);
    $('#adj_qty').val(1);
    $('#adj_reason').val('');
    showModal('adjustmentModal');
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Remove Product?',
        html: `Are you sure you want to remove <b>${name}</b> from inventory?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('api/inventory-action.php', { action: 'delete_item', item_id: id }, function (res) {
                if (res.status === 'success') {
                    Swal.fire('Deleted', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
}

// ----------------------------------------------------
// DYNAMIC UNIT AJAX HANDLERS
// ----------------------------------------------------
// ----------------------------------------------------
// DYNAMIC UNIT AJAX HANDLERS
// ----------------------------------------------------
function loadUnitsList(highlightId = null) {
    $.get('api/inventory-action.php', { action: 'get_units' }, function (res) {
        if (res && res.status === 'success') {
            const units = res.data;
            $('#unit_count_badge').text(units.length + ' Units');

            let rows = '';
            let optHtml = '<option value="">-- Select Unit --</option>';

            if (units.length === 0) {
                rows = '<tr><td colspan="3" class="text-center py-3 text-muted">No units saved yet.</td></tr>';
            } else {
                units.forEach(u => {
                    const isGlobal = (parseInt(u.is_global) === 1 || parseInt(u.sccode) === 0);
                    const isNew = highlightId && parseInt(u.id) === parseInt(highlightId);
                    const rowClass = isNew ? 'table-success fw-bold' : '';
                    const actionBtn = isGlobal ? 
                        '<span class="badge bg-label-secondary" title="Global System Unit"><i class="bi bi-shield-lock"></i></span>' : 
                        `<button type="button" class="btn btn-link text-danger p-0" onclick="deleteUnit(${u.id}, '${escapeJs(u.unit_name)}')" title="Delete Custom Unit"><i class="bi bi-trash3"></i></button>`;

                    rows += `
                        <tr class="${rowClass}">
                            <td>
                                <span class="fw-semibold">${escapeHtml(u.unit_name)}</span>
                                ${isGlobal ? '<span class="badge bg-label-secondary ms-1" style="font-size: 0.65rem;">System</span>' : ''}
                                ${isNew ? '<span class="badge bg-success ms-1 animate__animated animate__pulse">Just Added</span>' : ''}
                            </td>
                            <td class="text-center"><span class="badge bg-label-primary">${escapeHtml(u.unit_symbol)}</span></td>
                            <td class="text-center">${actionBtn}</td>
                        </tr>
                    `;
                    optHtml += `<option value="${u.id}">${escapeHtml(u.unit_name)} (${escapeHtml(u.unit_symbol)})</option>`;
                });
            }
            $('#units_tbody').html(rows);
            $('#form_unit_id').html(optHtml);
            if (highlightId) {
                $('#form_unit_id').val(highlightId);
                $('#units_table_container').scrollTop(0);
            }
        }
    }, 'json');
}

function deleteUnit(id, name) {
    Swal.fire({
        title: 'Delete Custom Unit?',
        text: `Are you sure you want to delete unit "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('api/inventory-action.php', { action: 'delete_unit', unit_id: id }, function (r) {
                if (r.status === 'success') {
                    loadUnitsList();
                    $('#unit_feedback_alert').html(`
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i> ${r.message}
                        </div>
                    `);
                    setTimeout(() => { $('#unit_feedback_alert .alert').fadeOut(); }, 3000);
                } else {
                    Swal.fire('Error', r.message, 'error');
                }
            }, 'json');
        }
    });
}

// ----------------------------------------------------
// DYNAMIC CATEGORY AJAX HANDLERS
// ----------------------------------------------------
function loadCategoriesList(highlightId = null) {
    $.get('api/inventory-action.php', { action: 'get_categories' }, function (res) {
        if (res && res.status === 'success') {
            const categories = res.data;
            $('#cat_count_badge').text(categories.length + ' Categories');

            let rows = '';
            let optHtml = '<option value="">-- Select Category --</option>';

            if (categories.length === 0) {
                rows = '<tr><td colspan="3" class="text-center py-3 text-muted">No categories saved yet.</td></tr>';
            } else {
                categories.forEach(c => {
                    const isGlobal = (parseInt(c.is_global) === 1 || parseInt(c.sccode) === 0);
                    const isNew = highlightId && parseInt(c.id) === parseInt(highlightId);
                    const rowClass = isNew ? 'table-success fw-bold' : '';
                    let typeBadge = '<span class="badge bg-label-primary">SaleItem</span>';
                    if (c.category_type === 'Consumable') typeBadge = '<span class="badge bg-label-info">Consumable</span>';
                    if (c.category_type === 'Asset') typeBadge = '<span class="badge bg-label-warning">Asset</span>';

                    const actionBtn = isGlobal ? 
                        '<span class="badge bg-label-secondary" title="Global System Category"><i class="bi bi-shield-lock"></i></span>' : 
                        `<button type="button" class="btn btn-link text-danger p-0" onclick="deleteCategory(${c.id}, '${escapeJs(c.category_name)}')" title="Delete Custom Category"><i class="bi bi-trash3"></i></button>`;

                    rows += `
                        <tr class="${rowClass}">
                            <td>
                                <span class="fw-semibold">${escapeHtml(c.category_name)}</span>
                                ${isGlobal ? '<span class="badge bg-label-secondary ms-1" style="font-size: 0.65rem;">System</span>' : ''}
                                ${isNew ? '<span class="badge bg-success ms-1 animate__animated animate__pulse">Just Added</span>' : ''}
                            </td>
                            <td>${typeBadge}</td>
                            <td class="text-center">${actionBtn}</td>
                        </tr>
                    `;
                    optHtml += `<option value="${c.id}">${escapeHtml(c.category_name)} (${escapeHtml(c.category_type)})</option>`;
                });
            }
            $('#categories_tbody').html(rows);
            $('#form_category_id').html(optHtml);
            if (highlightId) {
                $('#form_category_id').val(highlightId);
                $('#cat_table_container').scrollTop(0);
            }
        }
    }, 'json');
}

function deleteCategory(id, name) {
    Swal.fire({
        title: 'Delete Category?',
        text: `Delete category "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('api/inventory-action.php', { action: 'delete_category', category_id: id }, function (r) {
                if (r.status === 'success') {
                    loadCategoriesList();
                    $('#cat_feedback_alert').html(`
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i> ${r.message}
                        </div>
                    `);
                    setTimeout(() => { $('#cat_feedback_alert .alert').fadeOut(); }, 3000);
                } else {
                    Swal.fire('Error', r.message, 'error');
                }
            }, 'json');
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return $('<div>').text(str).html();
}

function escapeJs(str) {
    if (!str) return '';
    return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

$(document).ready(function () {
    // Save Item Form
    $('#itemForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#saveItemBtn');
        const origText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            $btn.prop('disabled', false).html(origText);
            if (res.status === 'success') {
                hideModal('itemModal');
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html(origText);
            Swal.fire('Error', 'Server connection failed', 'error');
        });
    });

    // Stock Adjustment Form
    $('#adjustmentForm').on('submit', function (e) {
        e.preventDefault();
        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                hideModal('adjustmentModal');
                Swal.fire('Updated', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    // Save Category Form (Live AJAX without closing modal)
    $('#categoryForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#save_cat_btn');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        $('#cat_feedback_alert').html('');

        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                $('#cat_inp_name').val('').focus();
                
                // Show instant in-modal success message
                $('#cat_feedback_alert').html(`
                    <div class="alert alert-success alert-dismissible fade show py-2 px-3 small d-flex align-items-center mb-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
                        <div><strong>Saved!</strong> ${res.message}</div>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                setTimeout(() => { $('#cat_feedback_alert .alert').fadeOut(); }, 4000);

                // Reload list and highlight the new category
                loadCategoriesList(res.id);
            } else {
                const errMsg = res && res.message ? res.message : 'Failed to save category.';
                $('#cat_feedback_alert').html(`
                    <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2"></i>
                        <div><strong>Error:</strong> ${errMsg}</div>
                    </div>
                `);
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html(origHtml);
            $('#cat_feedback_alert').html(`
                <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-0 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2"></i>
                    <div><strong>Error:</strong> Server connection failed.</div>
                </div>
            `);
        });
    });

    // Save Unit Form (Live AJAX without closing modal)
    $('#unitForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#save_unit_btn');
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        $('#unit_feedback_alert').html('');

        $.post('api/inventory-action.php', $(this).serialize(), function (res) {
            $btn.prop('disabled', false).html(origHtml);
            if (res && res.status === 'success') {
                $('#unit_inp_name').val('').focus();
                $('#unit_inp_symbol').val('');

                // Show instant in-modal success message
                $('#unit_feedback_alert').html(`
                    <div class="alert alert-success alert-dismissible fade show py-2 px-3 small d-flex align-items-center mb-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
                        <div><strong>Saved!</strong> ${res.message}</div>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                setTimeout(() => { $('#unit_feedback_alert .alert').fadeOut(); }, 4000);

                // Reload list and highlight the new unit
                loadUnitsList(res.id);
            } else {
                const errMsg = res && res.message ? res.message : 'Failed to save unit.';
                $('#unit_feedback_alert').html(`
                    <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2"></i>
                        <div><strong>Error:</strong> ${errMsg}</div>
                    </div>
                `);
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html(origHtml);
            $('#unit_feedback_alert').html(`
                <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-0 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger me-2"></i>
                    <div><strong>Error:</strong> Server connection failed.</div>
                </div>
            `);
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
