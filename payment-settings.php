<?php
require_once 'header.php';

$slot = $_COOKIE['slot'] ?? $_GET['slot'] ?? '';
$session = $_COOKIE['session'] ?? $_GET['session'] ?? $sessionyear;

// Month / Frequency dictionary for rendering badges & labels
function getFrequencyBadge($freq) {
    $freq = strval($freq);
    $map = [
        '0'  => ['text' => 'Every Month', 'badge' => 'bg-label-primary', 'icon' => 'bi-arrow-repeat'],
        '1'  => ['text' => 'January', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '2'  => ['text' => 'February', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '3'  => ['text' => 'March', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '4'  => ['text' => 'April', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '5'  => ['text' => 'May', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '6'  => ['text' => 'June', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '7'  => ['text' => 'July', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '8'  => ['text' => 'August', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '9'  => ['text' => 'September', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '10' => ['text' => 'October', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '11' => ['text' => 'November', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '12' => ['text' => 'December', 'badge' => 'bg-label-info', 'icon' => 'bi-calendar-event'],
        '22' => ['text' => '2 Months (Feb, Apr, Jun, Aug, Oct, Dec)', 'badge' => 'bg-label-warning', 'icon' => 'bi-calendar-range'],
        '33' => ['text' => '3 Months (Quarterly: Mar, Jun, Sep, Nov)', 'badge' => 'bg-label-warning', 'icon' => 'bi-calendar-range'],
        '44' => ['text' => '4 Months (Apr, Aug, Nov)', 'badge' => 'bg-label-warning', 'icon' => 'bi-calendar-range'],
        '66' => ['text' => '6 Months (Half-Yearly: Jan, Nov)', 'badge' => 'bg-label-warning', 'icon' => 'bi-calendar-range'],
    ];

    $item = $map[$freq] ?? ['text' => 'Month #' . $freq, 'badge' => 'bg-label-secondary', 'icon' => 'bi-calendar'];
    return '<span class="badge ' . $item['badge'] . ' me-1"><i class="bi ' . $item['icon'] . ' me-1"></i>' . $item['text'] . '</span>';
}
?>

<style>
    .pointer {
        cursor: pointer;
    }
    .item.dragging {
        opacity: 0.4;
    }
    .class-row,
    .session-row {
        margin-left: 10px;
    }
    .drag-placeholder {
        height: 65px;
        border: 2px dashed rgba(var(--bs-primary-rgb), 0.5);
        background: rgba(var(--bs-primary-rgb), 0.05);
        margin-bottom: 8px;
        border-radius: 8px;
    }
    .fee-item-card {
        transition: all 0.2s ease-in-out;
    }
    .fee-item-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }
    .fee-item-card.is-deactivated {
        opacity: 0.75;
        border-style: dashed !important;
        background-color: rgba(var(--bs-secondary-rgb), 0.04);
    }
    .drag-handle {
        cursor: grab;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header & Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1 text-primary"><i class="bi bi-cash-stack me-2"></i>Student Payment Setup</h4>
            <span class="text-muted small">Configure academic fee items, frequencies, class-wise amounts, and chart of accounts mappings.</span>
        </div>
        <div class="d-flex gap-2">
            <a href="sync-payments.php" class="btn btn-outline-info btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Dues to Students
            </a>
            <button class="btn btn-outline-warning btn-sm" onclick="saveOrder()" title="Save drag & drop order">
                <i class="bi bi-arrow-down-up me-1"></i> Update Order
            </button>
            <button class="btn btn-primary btn-sm" onclick="openAdd()">
                <i class="bi bi-plus-circle me-1"></i> Add New Item
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row align-items-end g-2">
                <?php
                $chain_param = '-c 10 -t Choose Values -u -r -b View Settings -h class exam';
                include 'components/slot-tree-ui.php';
                ?>
            </div>
        </div>
    </div>

    <!-- Notification Toast / Alert Box -->
    <div id="actionAlert" class="alert alert-dismissible d-none mb-3" role="alert">
        <span id="actionAlertMsg"></span>
        <button type="button" class="btn-close" onclick="$('#actionAlert').addClass('d-none')"></button>
    </div>

    <!-- ITEM LIST -->
    <div id="itemlist">
        <?php
        // 1. Fetch Global/Default amounts
        $sqlAmt = "SELECT itemcode, amount 
                   FROM financesetupvalue 
                   WHERE sccode='$sccode' 
                     AND sessionyear LIKE '%$session%' 
                     AND (slot='$slot' OR slot='' OR slot IS NULL)
                     AND classname='' AND sectionname=''
                   ORDER BY id";
        $resAmt = $conn->query($sqlAmt);
        $amounts = [];
        if ($resAmt && $resAmt->num_rows > 0) {
            while ($row = $resAmt->fetch_assoc()) {
                $amounts[$row['itemcode']] = $row['amount'];
            }
        }

        // 2. Fetch Account Sub-Head mappings for quick lookup
        $subHeadMap = [];
        $subHeadQuery = $conn->query("SELECT s.id, s.sub_head, h.account_head 
                                      FROM account_sub_head s 
                                      LEFT JOIN account_head h ON h.id = s.account_head_id 
                                      WHERE s.sccode='$sccode'");
        if ($subHeadQuery && $subHeadQuery->num_rows > 0) {
            while ($sh = $subHeadQuery->fetch_assoc()) {
                $subHeadMap[$sh['id']] = $sh['account_head'] . ' → ' . $sh['sub_head'];
            }
        }

        // 3. Fetch Items
        $sqlItems = "SELECT *, COALESCE(active, 1) AS active FROM financesetup 
                     WHERE sccode='$sccode' 
                       AND sessionyear LIKE '%$session%' 
                       AND (slot='$slot' OR slot='' OR slot IS NULL)
                     ORDER BY slno ASC, id ASC";
        $rsItems = $conn->query($sqlItems);

        if ($rsItems && $rsItems->num_rows > 0):
            while ($r = $rsItems->fetch_assoc()):
                $itemcode = $r['itemcode'];
                $valAmount = isset($amounts[$itemcode]) ? floatval($amounts[$itemcode]) : 0;
                $subHeadName = isset($subHeadMap[$r['sub_head']]) ? $subHeadMap[$r['sub_head']] : '';
                $isActive = intval($r['active'] ?? 1);
                ?>
                <div class="card mb-2 item fee-item-card <?= ($isActive == 0) ? 'is-deactivated' : '' ?>" data-id="<?= $r['id']; ?>" draggable="true">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        
                        <!-- Left Details & Expand Toggle -->
                        <div class="pointer d-flex flex-grow-1 align-items-center"
                            onclick="toggleItem(<?= $r['id'] ?>, '<?= $itemcode ?>', <?= intval($r['splitable'] ?? 0) ?>)">
                            <div class="col-auto me-3 drag-handle text-muted" title="Drag to re-order">
                                <i class="bi bi-grip-vertical fs-5"></i>
                            </div>
                            <div class="col-auto me-3 text-primary">
                                <i class="bi bi-chevron-right fs-6 chev-icon" id="chevIcon<?= $r['id'] ?>"></i>
                            </div>
                            <div class="col-md-5 col-12 me-2">
                                <strong class="text-heading fs-6"><?= htmlspecialchars($r['particulareng']); ?></strong>
                                <?php if (!empty($r['particularben'])): ?>
                                    <span class="text-muted ms-1">(<?= htmlspecialchars($r['particularben']); ?>)</span>
                                <?php endif; ?>
                                
                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                    <?php if ($isActive == 0): ?>
                                        <span class="badge bg-label-secondary" title="Archived / Inactive Item"><i class="bi bi-archive me-1"></i>Deactivated</span>
                                    <?php endif; ?>

                                    <?= getFrequencyBadge($r['month']); ?>

                                    <?php if (!empty($r['new_only'])): ?>
                                        <span class="badge bg-label-danger" title="New Admission Only"><i class="bi bi-person-plus me-1"></i>New Only</span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['splitable'])): ?>
                                        <span class="badge bg-label-info" title="Section-wise Splitable"><i class="bi bi-diagram-3 me-1"></i>Splitable</span>
                                    <?php endif; ?>
                                    <?php if (!empty($subHeadName)): ?>
                                        <span class="badge bg-label-secondary" title="Linked Account Sub-Head"><i class="bi bi-bank me-1"></i><?= htmlspecialchars($subHeadName); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions & Amount Display -->
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-success font-monospace"
                                    onclick="openAmountModal(<?= $r['id'] ?>, '<?= $r['itemcode'] ?>', <?= intval($r['splitable'] ?? 0) ?>, '<?= addslashes($r['particulareng']) . ' | ' . addslashes($r['particularben']) ?>')"
                                    title="Click to set default amount">
                                <strong>৳ <?= number_format($valAmount, 2) ?></strong>
                            </button>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="openEdit(<?= $r['id'] ?>)">
                                            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Item Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           onclick="openAmountModal(<?= $r['id'] ?>, '<?= $r['itemcode'] ?>', <?= intval($r['splitable'] ?? 0) ?>, '<?= addslashes($r['particulareng']) . ' | ' . addslashes($r['particularben']) ?>')">
                                            <i class="bi bi-currency-dollar me-2 text-success"></i> Set Default Fee (৳ <?= number_format($valAmount, 2) ?>)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           onclick="applyFeeToAll('<?= $r['itemcode'] ?>', <?= $valAmount ?>, '<?= addslashes($r['particulareng']) ?>')">
                                            <i class="bi bi-check2-all me-2 text-info"></i> Apply Amount to All Classes
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="sync-payments.php?type=item&icode=<?= $r['itemcode'] ?>">
                                            <i class="bi bi-arrow-repeat me-2 text-primary"></i> Sync Dues to Students
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="toggleItemStatus(<?= $r['id'] ?>)">
                                            <i class="bi bi-toggle-<?= ($isActive == 1) ? 'on text-success' : 'off text-muted' ?> me-2"></i> 
                                            <?= ($isActive == 1) ? 'Deactivate / Archive Item' : 'Activate Item' ?>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="delItem(<?= $r['id'] ?>)">
                                            <i class="bi bi-trash me-2"></i> Delete Item
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Class & Section Drill-down Container -->
                    <div class="card-body border-top bg-light-subtle" id="itemBody<?= $r['id'] ?>" style="display:none;"></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card text-center p-5">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-wallet2 text-warning display-4"></i>
                    </div>
                    <h5 class="card-title text-heading">No Payment Items Configured Yet</h5>
                    <p class="text-muted mb-4">No student fee items found for Session: <strong><?= htmlspecialchars($session) ?></strong>. You can click below to import default fee templates or create a new custom item.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-warning btn-sm" onclick="importDefaultFinance()">
                            <i class="bi bi-cloud-arrow-down me-1"></i> Import Default Fee Settings
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="openAdd()">
                            <i class="bi bi-plus-circle me-1"></i> Add New Custom Item
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ================= MODALS ================= -->

<!-- Add / Edit Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalTitle"><i class="bi bi-plus-circle me-2 text-primary"></i>Students Payable Fee Item</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fid" value="0">
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Particulars (English) <span class="text-danger">*</span></label>
                        <input type="text" id="peng" class="form-control" placeholder="e.g. Monthly Tuition Fee">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Particulars (Bengali)</label>
                        <input type="text" id="pben" class="form-control" placeholder="যেমনঃ মাসিক বেতন">
                    </div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Frequency / Month <span class="text-danger">*</span></label>
                        <select id="mon" class="form-select">
                            <!-- Specific Month -->
                            <optgroup label="Specific Month (একক মাস)">
                                <option value="1">1 - January (জানুয়ারি)</option>
                                <option value="2">2 - February (ফেব্রুয়ারি)</option>
                                <option value="3">3 - March (মার্চ)</option>
                                <option value="4">4 - April (এপ্রিল)</option>
                                <option value="5">5 - May (মে)</option>
                                <option value="6">6 - June (জুন)</option>
                                <option value="7">7 - July (জুলাই)</option>
                                <option value="8">8 - August (আগস্ট)</option>
                                <option value="9">9 - September (সেপ্টেম্বর)</option>
                                <option value="10">10 - October (অক্টোবর)</option>
                                <option value="11">11 - November (নভেম্বর)</option>
                                <option value="12">12 - December (ডিসেম্বর)</option>
                            </optgroup>

                            <!-- Periodic Frequency (Classic System Equivalent) -->
                            <optgroup label="Periodic Intervals (নির্দিষ্ট ব্যবধানে)">
                                <option value="0" selected>Every Month : প্রতি মাসে</option>
                                <option value="22">2 Months Frequency : February, April, June, August, October, December (২ মাস অন্তর)</option>
                                <option value="33">3 Months Frequency : March, June, September, November (৩ মাস অন্তর / ত্রৈমাসিক)</option>
                                <option value="44">4 Months Frequency : April, August, November (৪ মাস অন্তর)</option>
                                <option value="66">6 Months Frequency : January, November (৬ মাস অন্তর / ষান্মাসিক)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Linked Account Sub-Head (Chart of Accounts)</label>
                        <select id="acc_head" class="form-select">
                            <option value="0">-- No Account Head Linked --</option>
                            <?php
                            $headQuery = $conn->query("SELECT * FROM account_head WHERE sccode = '$sccode' ORDER BY id ASC");
                            if ($headQuery && $headQuery->num_rows > 0) {
                                while ($head = $headQuery->fetch_assoc()) {
                                    $head_id = $head['id'];
                                    echo '<optgroup label="' . htmlspecialchars($head['account_head']) . '">';
                                    
                                    $subQuery = $conn->query("SELECT * FROM account_sub_head 
                                                              WHERE sccode = '$sccode' 
                                                                AND account_head_id = '$head_id' 
                                                                AND income = 1 
                                                              ORDER BY id ASC");
                                    if ($subQuery && $subQuery->num_rows > 0) {
                                        while ($sub = $subQuery->fetch_assoc()) {
                                            echo '<option value="' . $sub['id'] . '">' . htmlspecialchars($sub['sub_head']) . '</option>';
                                        }
                                    }
                                    echo '</optgroup>';
                                }
                            }
                            ?>
                        </select>
                        <div class="form-text small">Student fee collection will automatically credit this income account.</div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-4 p-2 bg-light-subtle rounded border">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="new_only">
                                <label class="form-check-label fw-semibold" for="new_only">New Admission Only (শুধুমাত্র নতুন ভর্তি)</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="splitable">
                                <label class="form-check-label fw-semibold" for="splitable">Item Splitable (সেকশনভিত্তিক আলাদা ফি)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="itemMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm px-4" onclick="saveItem()">
                    <i class="bi bi-check-circle me-1"></i> Save Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Amount Modal -->
<div class="modal fade" id="amountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-currency-dollar me-2 text-success"></i>Set Fee Amount</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="afid">
                <input type="hidden" id="aitemcode">
                <input type="hidden" id="aclass">
                <input type="hidden" id="asection">
                <input type="hidden" id="splyn">

                <div class="p-2 mb-3 bg-light-subtle rounded border">
                    <div class="text-muted small">Target Item / Scope:</div>
                    <strong class="text-primary" id="ainfo"></strong>
                    <div class="text-heading fw-semibold mt-1" id="set-amount-title"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Amount (৳) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" id="aamount" class="form-control form-control-lg font-monospace" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div id="amountMsg" class="small mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-sm px-4" onclick="saveAmount()">
                    <i class="bi bi-save me-1"></i> Save Amount
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- SweetAlert2 if available -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ================= JS ================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

<script>
    const itemModal = new bootstrap.Modal('#itemModal');
    const amountModal = new bootstrap.Modal('#amountModal');

    function showToast(msg, isSuccess = true, syncLink = false) {
        const alertBox = $('#actionAlert');
        const alertMsg = $('#actionAlertMsg');
        alertBox.removeClass('d-none alert-success alert-danger alert-warning')
                .addClass(isSuccess ? 'alert-success' : 'alert-danger');
        
        let content = msg;
        if (syncLink) {
            content += ` <a href="sync-payments.php" class="btn btn-sm btn-dark ms-2"><i class="bi bi-arrow-repeat me-1"></i> Sync Dues to Students</a>`;
        }
        alertMsg.html(content);
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    // ---------- Slot/Session Filter Handling ----------
    function applyFilter(slot, session) {
        if (!slot || !session) return;
        setCookie('slot', slot);
        setCookie('session', session);

        const urlParams = new URLSearchParams(window.location.search);
        const urlSlot = urlParams.get('slot') || '';
        const urlSession = urlParams.get('session') || '';

        if (slot === urlSlot && session === urlSession) {
            return;
        } else {
            window.location.href = '?slot=' + encodeURIComponent(slot) + '&session=' + encodeURIComponent(session);
        }
    }

    $('#slot-main,#session-main').off('change').on('change', function () {
        let slot = $('#slot-main').val();
        let session = $('#session-main').val();
        applyFilter(slot, session);
    });

    document.addEventListener('DOMContentLoaded', function () {
        let slot = getCookie('slot');
        let session = getCookie('session');
        if (slot) $('#slot-main').val(slot);
        if (session) $('#session-main').val(session);
    });

    // ---------- Add/Edit Item ----------
    function openAdd() {
        $('#fid').val(0);
        $('#itemModalTitle').html('<i class="bi bi-plus-circle me-2 text-primary"></i>Add Students Payable Fee Item');
        $('#peng').val('');
        $('#pben').val('');
        $('#mon').val('0');
        $('#acc_head').val('0');
        $('#new_only,#splitable').prop('checked', false);
        $('#itemMsg').html('');
        itemModal.show();
    }

    function openEdit(id) {
        $('#itemModalTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Fee Item Details');
        $.post('payments/get-finance-item.php', { id }, function (res) {
            try {
                let d = (typeof res === 'object') ? res : JSON.parse(res);
                $('#fid').val(d.id);
                $('#peng').val(d.particulareng || '');
                $('#pben').val(d.particularben || '');
                $('#mon').val(d.month || '0');
                $('#acc_head').val(d.sub_head || '0');
                $('#new_only').prop('checked', d.new_only == 1);
                $('#splitable').prop('checked', d.splitable == 1);
                $('#itemMsg').html('');
                itemModal.show();
            } catch(e) {
                console.error("Failed to parse edit item response", e);
            }
        });
    }

    function saveItem() {
        let eng = $('#peng').val().trim();
        if (!eng) {
            $('#itemMsg').html('<span class="text-danger">English particulars is required.</span>');
            return;
        }

        let data = {
            id: $('#fid').val(),
            eng: eng,
            ben: $('#pben').val().trim(),
            mon: $('#mon').val(),
            acc_head: $('#acc_head').val(),
            new_only: $('#new_only').is(':checked') ? 1 : 0,
            splitable: $('#splitable').is(':checked') ? 1 : 0,
            slot: $('#slot-main').val(),
            session: $('#session-main').val()
        };

        $.post('payments/save-finance-item.php', data, function (res) {
            $('#itemMsg').html(res);
            if (res.includes('success')) {
                setTimeout(() => location.reload(), 500);
            }
        });
    }

    // ---------- Toggle Status (Activate / Deactivate) ----------
    function toggleItemStatus(id) {
        $.post('payments/toggle-finance-item-status.php', { id }, function(res) {
            try {
                let r = (typeof res === 'object') ? res : JSON.parse(res);
                if (r.status === 'success') {
                    showToast(r.msg, true);
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(r.msg || 'Failed to update item status.', false);
                }
            } catch(e) {
                location.reload();
            }
        });
    }

    // ---------- Delete with Payment Protection ----------
    function delItem(id) {
        if (!confirm('Are you sure you want to delete this payment item?')) return;

        $.post('payments/delete-finance-item.php', { id }, function(res) {
            try {
                let r = (typeof res === 'object') ? res : JSON.parse(res);

                if (r.status === 'success') {
                    showToast(r.msg, true);
                    setTimeout(() => location.reload(), 600);
                } else if (r.status === 'blocked' && r.code === 'PAYMENT_EXISTS') {
                    // SweetAlert / Rich Alert for Payment Protection
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Cannot Delete Item!',
                            html: `<div class="text-start">
                                    <p class="text-danger fw-semibold">${r.msg}</p>
                                    <div class="alert alert-warning small p-2">
                                        <b>পরিশোধিত শিক্ষার্থী:</b> ${r.paid_students} জন<br>
                                        <b>মোট সংগৃহীত টাকা:</b> ৳ ${r.total_amount}
                                    </div>
                                    <p class="small text-muted">আপনি কি আইটেমটিকে ডিলিট না করে <b>নিষ্ক্রিয় (Deactivate / Archive)</b> করে রাখতে চান?</p>
                                   </div>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ffab00',
                            cancelButtonColor: '#8592a3',
                            confirmButtonText: '<i class="bi bi-archive me-1"></i> Deactivate Item',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                toggleItemStatus(id);
                            }
                        });
                    } else {
                        if (confirm(r.msg + "\n\nআইটেমটিকে নিষ্ক্রিয় (Deactivate / Archive) করতে 'OK' চাপুন।")) {
                            toggleItemStatus(id);
                        }
                    }
                } else {
                    alert(r.msg || 'Failed to delete item');
                }
            } catch(e) {
                location.reload();
            }
        });
    }

    // ---------- Amount Modal ----------
    function openAmountModal(fid, itemcode, splitable, itemText, cls = '', sec = '') {
        $('#afid').val(fid);
        $('#aitemcode').val(itemcode);
        $('#aclass').val(cls);
        $('#asection').val(sec);
        $('#set-amount-title').text(itemText);

        let text = 'Global / Default Item Amount';
        if (cls) text = 'Class: ' + cls;
        if (sec) text += ' | Section: ' + sec;
        $('#ainfo').text(text);
        $('#aamount').val('');
        $('#amountMsg').html('');

        $.post('payments/get-amount.php', { fid, itemcode, class: cls, section: sec, spl: splitable }, function (r) {
            if (!r) return;
            try {
                let resObj = (typeof r === 'object') ? r : JSON.parse(r);
                let amt = resObj.amount;
                let spl = resObj.splitable;
                $('#aamount').val(amt);
                $('#splyn').val(spl);

                $('#amountModal').one('shown.bs.modal', function () {
                    $('#aamount').focus().select();
                });
            } catch(e) {
                console.error("Error reading amount", e);
            }
        });

        amountModal.show();
    }

    function saveAmount() {
        let data = {
            fid: $('#afid').val(),
            fitemcode: $('#aitemcode').val(),
            class: $('#aclass').val(),
            section: $('#asection').val(),
            amount: $('#aamount').val(),
            spl: $('#splyn').val(),
            acc_head: $('#acc_head').val()
        };

        $.post('payments/save-amount.php', data, function (res) {
            $('#amountMsg').html(res);
            if (res.includes('success')) {
                setTimeout(() => {
                    amountModal.hide();
                    showToast('Fee amount saved successfully! Remember to sync unpaid students if needed.', true, true);
                    setTimeout(() => location.reload(), 1200);
                }, 400);
            }
        });
    }

    // ---------- Apply Fee to All Classes & Sections ----------
    function applyFeeToAll(itemcode, currentAmount, itemTitle) {
        let amtStr = prompt(`Enter amount (৳) to apply to ALL classes & sections for "${itemTitle}":`, currentAmount || '0.00');
        if (amtStr === null) return;
        let amount = parseFloat(amtStr);
        if (isNaN(amount) || amount < 0) {
            alert('Please enter a valid numeric amount.');
            return;
        }

        $.post('payments/apply-fee-to-all.php', {
            itemcode: itemcode,
            amount: amount,
            slot: $('#slot-main').val(),
            session: $('#session-main').val()
        }, function(res) {
            try {
                let r = (typeof res === 'object') ? res : JSON.parse(res);
                if (r.status === 'success') {
                    showToast(r.msg, true, true);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast(r.msg || 'Failed to apply amounts.', false);
                }
            } catch(e) {
                location.reload();
            }
        });
    }

    // ---------- Import Default Finance Settings ----------
    function importDefaultFinance() {
        if (!confirm('Do you want to import default payment settings for the selected session?')) return;
        $.post('payments/import-default-finance.php', {
            slot: $('#slot-main').val(),
            session: $('#session-main').val()
        }, function(res) {
            try {
                let r = (typeof res === 'object') ? res : JSON.parse(res);
                if (r.status === 'success') {
                    showToast(r.msg, true);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(r.msg || 'Failed to import default settings.', false);
                }
            } catch(e) {
                location.reload();
            }
        });
    }

    // ---------- Toggle & Load Classes Accordion ----------
    function toggleItem(id, itemcode, spl) {
        let box = $('#itemBody' + id);
        let icon = $('#chevIcon' + id);

        if (box.is(':visible')) {
            box.slideUp(150);
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
            return;
        }

        icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');

        if (box.data('loaded') === 1) {
            box.slideDown(150);
            return;
        }

        box.html('<div class="text-muted p-2"><i class="bi bi-hourglass-split me-2"></i>Loading classes and sections...</div>').slideDown(100);
        $.post('payments/load-item-classes.php', {
            fid: id,
            itemcode: itemcode,
            spl: spl,
            session: $('#session-main').val()
        }, function (res) {
            box.html(res);
            box.data('loaded', 1);
        });
    }

    // ---------- Drag & Drop Sorting ----------
    const list = document.getElementById('itemlist');
    let dragItem = null;
    let placeholder = document.createElement('div');
    placeholder.className = 'drag-placeholder';

    if (list) {
        list.addEventListener('dragstart', e => {
            if (e.target.classList.contains('item')) {
                dragItem = e.target;
                e.target.classList.add('dragging');
            }
        });

        list.addEventListener('dragend', e => {
            if (dragItem) {
                dragItem.classList.remove('dragging');
                dragItem = null;
            }
        });

        list.addEventListener('dragover', e => {
            e.preventDefault();
            const after = getAfterElement(list, e.clientY);
            if (after == null) {
                list.appendChild(placeholder);
            } else {
                list.insertBefore(placeholder, after);
            }
        });

        list.addEventListener('drop', () => {
            if (placeholder.parentNode && dragItem) {
                list.insertBefore(dragItem, placeholder);
                placeholder.remove();
            }
        });
    }

    function getAfterElement(container, y) {
        const els = [...container.querySelectorAll('.item:not(.dragging)')];
        return els.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) return { offset: offset, element: child };
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function saveOrder() {
        const ids = [...document.querySelectorAll('.item')].map((el, i) => el.dataset.id + '=' + (i + 1));
        if (ids.length === 0) {
            alert('No items to reorder.');
            return;
        }

        $.post('payments/save-item-order.php', { order: ids.join(',') }, function (res) {
            showToast(res, true);
            setTimeout(() => location.reload(), 600);
        });
    }

    function chainBtnFunc() {
        window.location.reload();
    }
</script>

</body>
</html>