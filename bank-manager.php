<?php
require_once 'header.php';

// Fetch distinct slots for this institution
$slots_list = [];
$s_stmt = $conn->prepare("SELECT slotname FROM slots WHERE sccode = ? AND slotname IS NOT NULL AND slotname != '' ORDER BY id ASC");
if ($s_stmt) {
    $s_stmt->bind_param("i", $sccode);
    $s_stmt->execute();
    $s_res = $s_stmt->get_result();
    while ($sr = $s_res->fetch_assoc()) {
        $slots_list[] = $sr['slotname'];
    }
    $s_stmt->close();
}

// Fetch bank list from banklist table for dropdown
$bank_names = [];
$b_stmt = $conn->prepare("SELECT id, bname FROM banklist WHERE sccode = ? OR sccode = 0 OR sccode IS NULL ORDER BY bname ASC");
if ($b_stmt) {
    $b_stmt->bind_param("i", $sccode);
    $b_stmt->execute();
    $b_res = $b_stmt->get_result();
    while ($br = $b_res->fetch_assoc()) {
        $bank_names[] = $br['bname'];
    }
    $b_stmt->close();
}
$bank_names = array_unique(array_filter($bank_names));

// Predefined educational institution account types
$account_types = [
    'General' => 'General (সাধারণ চলতি হিসাব)',
    'Current' => 'Current Account (চলতি হিসাব)',
    'Savings' => 'Savings Account (সঞ্চয়ী হিসাব)',
    'Tuition Fee' => 'Tuition Fee (টিউশন ফি হিসাব)',
    'Development Fund' => 'Development Fund (উন্নয়ন তহবিল)',
    'Exam Fund' => 'Exam Fund (পরীক্ষা তহবিল)',
    'Salary / Payroll' => 'Salary / Payroll (বেতন তহবিল)',
    'FDR' => 'FDR / Term Deposit (স্থায়ী আমানত)',
    'DPS' => 'DPS (ডিপিএস হিসাব)',
    'Scout' => 'Scout / Girl Guides (স্কাউট তহবিল)',
    'Ed. Board' => 'Education Board (শিক্ষা বোর্ড)',
    'Special' => 'Special Fund (বিশেষ তহবিল)',
    'Other' => 'Other (অন্যান্য)'
];

// Fetch all bank accounts for this institution
// Sorted so Active accounts appear first, Closed accounts grouped at the bottom
$accounts = [];
$total_balance = 0;
$active_count = 0;
$closed_count = 0;

$sql = "SELECT b.*, 
        (CASE WHEN b.status = 0 THEN 1 ELSE 0 END) AS is_closed_flag
        FROM bankinfo b
        WHERE b.sccode = ?
        ORDER BY is_closed_flag ASC, b.id ASC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $sccode);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $acc = $row['accno'];
        
        // Fetch latest verified balance from banktrans
        $bls = 0;
        $q2 = $conn->prepare("SELECT balance FROM banktrans 
                              WHERE sccode = ? AND accno = ? AND verified = 1
                              ORDER BY verifytime DESC, date DESC, id DESC LIMIT 1");
        if ($q2) {
            $q2->bind_param("is", $sccode, $acc);
            $q2->execute();
            $r2 = $q2->get_result();
            if ($r2->num_rows > 0) {
                $bls = floatval($r2->fetch_assoc()['balance']);
            } else {
                $bls = floatval($row['opening_balance'] ?? 0);
            }
            $q2->close();
        }

        $row['current_balance'] = $bls;
        
        $is_closed = ($row['is_closed_flag'] == 1 || (!empty($row['closingdate']) && $row['closingdate'] != '0000-00-00' && $row['status'] == 0));
        if ($is_closed) {
            $closed_count++;
        } else {
            $active_count++;
            $total_balance += $bls;
        }

        $accounts[] = $row;
    }
    $stmt->close();
}
?>

<style>
    .bank-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    .closed-row {
        background-color: #fafafa !important;
        opacity: 0.85;
    }
    .closed-row:hover {
        background-color: #f1f1f1 !important;
        opacity: 1;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .modal {
        z-index: 1060 !important;
    }
    .modal-backdrop {
        z-index: 1055 !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold m-0 d-flex align-items-center gap-2">
                <i class="bi bi-bank text-primary fs-3"></i>
                Bank Account Manager
            </h4>
            <p class="text-muted small mb-0">প্রতিষ্ঠানের সকল ব্যাংক অ্যাকাউন্ট, স্থিতি ও ক্লোজিং ব্যবস্থাপনা</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="showAddBankNameModal();">
                <i class="bi bi-plus-lg me-1"></i> Add Bank to Directory
            </button>
            <button class="btn btn-primary btn-sm" onclick="addNewBank();">
                <i class="bi bi-plus-circle me-1"></i> Add New Account
            </button>
        </div>
    </div>

    <!-- KPI / Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <span class="text-muted small d-block">Total Accounts</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($accounts) ?></h4>
                    </div>
                    <div class="bank-card-icon bg-label-primary">
                        <i class="bi bi-wallet2 fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <span class="text-muted small d-block">Active Accounts</span>
                        <h4 class="fw-bold mb-0 text-success"><?= $active_count ?></h4>
                    </div>
                    <div class="bank-card-icon bg-label-success">
                        <i class="bi bi-check2-circle fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <span class="text-muted small d-block">Closed Accounts</span>
                        <h4 class="fw-bold mb-0 text-danger"><?= $closed_count ?></h4>
                    </div>
                    <div class="bank-card-icon bg-label-danger">
                        <i class="bi bi-lock-fill fs-4 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <span class="text-muted small d-block">Total Active Balance</span>
                        <h4 class="fw-bold mb-0 text-primary">৳ <?= number_format($total_balance, 2) ?></h4>
                    </div>
                    <div class="bank-card-icon bg-label-info">
                        <i class="bi bi-cash-stack fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card & Filter Toolbar -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Filter Tabs -->
                <div class="col-md-7 d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">
                            All Accounts (<?= count($accounts) ?>)
                        </button>
                        <button type="button" class="btn btn-outline-success filter-btn" data-filter="active">
                            Active (<?= $active_count ?>)
                        </button>
                        <button type="button" class="btn btn-outline-danger filter-btn" data-filter="closed">
                            Closed (<?= $closed_count ?>)
                        </button>
                    </div>
                </div>

                <!-- Live Search Box -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="accountSearch" class="form-control" placeholder="Search account no, bank, branch...">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table tab                        <th style="width: 50px;">#</th>
                        <th>Account Details</th>
                        <th>Bank &amp; Branch</th>
                        <th>Slot / Scope</th>
                        <th>Status</th>
                        <th class="text-end">Current Balance</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-bank fs-1 d-block mb-2 opacity-50"></i>
                                <strong>কোন ব্যাংক অ্যাকাউন্ট পাওয়া যায়নি।</strong>
                                <p class="small mb-0">Add New Account বাটনে ক্লিক করে নতুন অ্যাকাউন্ট যুক্ত করুন।</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $sl = 1;
                        foreach ($accounts as $row): 
                            $acc = $row['accno'];
                            $type = $row['acctype'] ?: 'General';
                            $bank = $row['bankname'];
                            $branch = $row['branch'] ?: '—';
                            $slot_val = $row['slot'] ?: 'All';
                            $id = $row['id'];
                            $cdate = $row['closingdate'] ?? '';
                            $is_closed = ($row['is_closed_flag'] == 1 || (!empty($cdate) && $cdate != '0000-00-00' && $row['status'] == 0));
                            $bls = $row['current_balance'];
                        ?>
                            <tr class="account-row <?= $is_closed ? 'closed-row' : 'active-row' ?>" 
                                data-status="<?= $is_closed ? 'closed' : 'active' ?>"
                                data-account="<?= htmlspecialchars(json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="text-muted fw-bold"><?= $sl++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <span class="badge bg-label-dark font-monospace fs-tiny"><?= htmlspecialchars($acc); ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-label-primary fs-tiny"><?= htmlspecialchars($type); ?></span>
                                        <?php if (!empty($row['routing_no'])): ?>
                                            <span class="badge bg-light text-muted fs-tiny">Routing: <?= htmlspecialchars($row['routing_no']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-building text-primary me-1"></i>
                                        <?= htmlspecialchars($bank); ?>
                                    </div>
                                    <div class="text-muted small ps-4">
                                        <i class="bi bi-geo-alt fs-tiny"></i> <?= htmlspecialchars($branch); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary"><?= htmlspecialchars($slot_val); ?></span>
                                </td>
                                <td>
                                    <?php if ($is_closed): ?>
                                        <span class="badge bg-label-danger d-inline-flex align-items-center gap-1" title="Closed Account">
                                            <i class="bi bi-lock-fill"></i> Closed
                                        </span>
                                        <?php if (!empty($cdate) && $cdate != '0000-00-00'): ?>
                                            <div class="fs-tiny text-danger mt-1">
                                                Date: <?= date('d M, Y', strtotime($cdate)); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-label-success d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold fs-6 <?= $is_closed ? 'text-muted' : 'text-primary' ?>">
                                    ৳ <?= number_format($bls, 2); ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary hide-arrow" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="bank-account.php?accno=<?= urlencode($acc) ?>" target="_blank">
                                                    <i class="bi bi-journal-text text-info"></i> View Ledger
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" onclick="editBank('<?= $id ?>', this);">
                                                    <i class="bi bi-pencil text-primary"></i> Edit Account
                                                </a>
                                            </li>
                                            <?php if ($is_closed): ?>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" onclick="reopenBank('<?= $id ?>', '<?= htmlspecialchars(addslashes($acc)) ?>');">
                                                        <i class="bi bi-unlock text-success"></i> Re-open Account
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" onclick="closeBank('<?= $id ?>', '<?= htmlspecialchars(addslashes($acc)) ?>');">
                                                        <i class="bi bi-lock text-warning"></i> Close Account
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="javascript:void(0);" onclick="removeBank('<?= $id ?>', '<?= htmlspecialchars(addslashes($acc)) ?>');">
                                                    <i class="bi bi-trash text-danger"></i> Delete Account
                                                </a>
                                            </li>
                                        </ul>
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

<!-- ========================================== -->
<!-- MODAL 1: ADD / EDIT BANK ACCOUNT -->
<!-- ========================================== -->
<div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2" id="modalTitle">
                    <i class="bi bi-bank"></i> Add New Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="bankForm">
                    <input type="hidden" id="bank_id">

                    <!-- Bank Name with Dropdown & Quick Add Button -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Bank Name <span class="text-danger">*</span></label>
                            <a href="javascript:void(0);" onclick="showAddBankNameModal();" class="small text-primary fw-bold text-decoration-none">
                                <i class="bi bi-plus-circle me-1"></i> Add New Bank
                            </a>
                        </div>
                        <select id="bankname" class="form-select" required>
                            <option value="">-- Select Bank --</option>
                            <?php foreach ($bank_names as $bn): ?>
                                <option value="<?= htmlspecialchars($bn) ?>"><?= htmlspecialchars($bn) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Account Number & Type -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Account Number <span class="text-danger">*</span></label>
                            <input type="text" id="accno" class="form-control" placeholder="e.g. 0100058538854" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Account Type</label>
                            <select id="acctype" class="form-select">
                                <?php foreach ($account_types as $val => $label): ?>
                                    <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Branch & Routing No -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Branch Name</label>
                            <input type="text" id="branch" class="form-control" placeholder="e.g. Main Branch, Cumilla">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Routing Number</label>
                            <input type="text" id="routing_no" class="form-control" placeholder="Optional">
                        </div>
                    </div>

                    <!-- Slot / Academic Branch -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Slot / Section Scope</label>
                        <select id="slot" class="form-select">
                            <option value="">All (Institution Wide)</option>
                            <?php foreach ($slots_list as $sl_name): ?>
                                <option value="<?= htmlspecialchars($sl_name) ?>"><?= htmlspecialchars($sl_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Opening Balance & Opening Date -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Opening Balance (৳)</label>
                            <input type="number" step="0.01" id="opening_balance" class="form-control" placeholder="0.00" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Opening Date</label>
                            <input type="date" id="openingdate" class="form-control">
                        </div>
                    </div>

                    <!-- Account Closing Section -->
                    <div class="p-3 bg-light rounded border">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="is_closed_toggle" onchange="toggleClosingSection();">
                            <label class="form-check-label fw-bold small text-danger" for="is_closed_toggle">
                                Account is Closed (নিষ্ক্রিয় / বন্ধ অ্যাকাউন্ট)
                            </label>
                        </div>
                        <div id="closingDateWrapper" style="display: none;">
                            <label class="form-label small fw-bold text-dark">Closing Date (অ্যাকাউন্ট বন্ধ করার তারিখ)</label>
                            <input type="date" id="closingdate" class="form-control form-control-sm">
                            <small class="text-muted fs-tiny d-block mt-1">অ্যাকাউন্টটি বন্ধ থাকলে এটি তালিকায় সবার নিচে ড্রপডাউনে প্রদর্শিত হবে।</small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveBank" onclick="saveBank();">
                    <i class="bi bi-save me-1"></i> Save Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: BANK DIRECTORY & LIST MANAGEMENT -->
<!-- ========================================== -->
<div class="modal fade" id="addBankNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-buildings"></i> Bank Directory
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Add New Bank Form -->
                <div class="card bg-light border-0 mb-3 shadow-none">
                    <div class="card-body p-2">
                        <label class="form-label small fw-bold mb-1">Add New Bank to Directory</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="new_bank_name" class="form-control" placeholder="Enter bank name (e.g. Dutch-Bangla Bank)" onkeydown="if(event.key==='Enter'){event.preventDefault();saveNewBankName();}">
                            <button type="button" class="btn btn-primary px-3" id="btnSaveNewBankName" onclick="saveNewBankName();">
                                <i class="bi bi-plus-lg me-1"></i> Add Bank
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Search & Bank List Header -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="small fw-bold text-dark">
                        <i class="bi bi-list-ul me-1"></i> Available Banks (<span id="bankListCount">0</span>)
                    </span>
                    <div class="w-50">
                        <input type="text" id="bankListSearch" class="form-control form-control-sm" placeholder="Search banks..." oninput="filterBankDirectory(this.value)">
                    </div>
                </div>

                <!-- Scrollable Bank List -->
                <div class="border rounded bg-white" style="max-height: 280px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="bankDirectoryList">
                        <li class="list-group-item text-center py-4 text-muted small">
                            <span class="spinner-border spinner-border-sm me-1"></span> Loading banks...
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // Tab filtering (All / Active / Closed)
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            filterTable(filter, document.getElementById('accountSearch').value.toLowerCase());
        });
    });

    // Live search filtering
    document.getElementById('accountSearch')?.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const activeBtn = document.querySelector('.filter-btn.active');
        const filter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
        filterTable(filter, query);
    });

    function filterTable(statusFilter, searchQuery) {
        document.querySelectorAll('#accountsTable tbody tr.account-row').forEach(row => {
            const status = row.getAttribute('data-status');
            const text = row.innerText.toLowerCase();

            const matchesStatus = (statusFilter === 'all') || (status === statusFilter);
            const matchesSearch = !searchQuery || text.includes(searchQuery);

            if (matchesStatus && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function toggleClosingSection() {
        const isClosed = document.getElementById('is_closed_toggle').checked;
        const wrapper = document.getElementById('closingDateWrapper');
        if (isClosed) {
            wrapper.style.display = 'block';
            if (!document.getElementById('closingdate').value) {
                document.getElementById('closingdate').value = new Date().toISOString().split('T')[0];
            }
        } else {
            wrapper.style.display = 'none';
            document.getElementById('closingdate').value = '';
        }
    }

    function addNewBank() {
        $("#modalTitle").html('<i class="bi bi-bank"></i> Add New Account');
        $("#bankForm")[0].reset();
        $("#bank_id").val('');
        $("#acctype").val('General');
        $("#is_closed_toggle").prop('checked', false);
        $("#closingDateWrapper").hide();
        $("#closingdate").val('');
        
        var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("bankModal"));
        myModal.show();
    }

    function showAddBankNameModal() {
        $("#new_bank_name").val('');
        $("#bankListSearch").val('');
        loadBankDirectoryList();
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("addBankNameModal"));
        modal.show();
    }

    function loadBankDirectoryList() {
        $("#bankDirectoryList").html('<li class="list-group-item text-center py-4 text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Loading banks...</li>');
        
        $.get("bank/bank-list-get.php", function(data) {
            try {
                let list = typeof data === 'object' ? data : JSON.parse(data);
                if (!Array.isArray(list) || list.length === 0) {
                    $("#bankDirectoryList").html('<li class="list-group-item text-center py-4 text-muted small"><i class="bi bi-info-circle me-1"></i> No banks found in directory.</li>');
                    $("#bankListCount").text(0);
                    return;
                }

                $("#bankListCount").text(list.length);
                let html = '';
                list.forEach(function(b) {
                    let isGlobal = (b.sccode == 0 || !b.sccode);
                    let badge = isGlobal 
                        ? '<span class="badge bg-label-secondary fs-tiny">Global</span>' 
                        : '<span class="badge bg-label-primary fs-tiny">Custom</span>';
                    
                    let bnameEsc = $('<div>').text(b.bname).html();
                    let bnameSafe = b.bname.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bank-item" data-name="${bnameEsc.toLowerCase()}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-bank text-primary"></i>
                                <span class="fw-medium text-dark">${bnameEsc}</span>
                                ${badge}
                            </div>
                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger" 
                                    onclick="deleteBankFromList(${b.id}, '${bnameSafe}');" 
                                    title="Delete from directory">
                                <i class="bi bi-trash"></i>
                            </button>
                        </li>
                    `;
                });
                $("#bankDirectoryList").html(html);
            } catch(e) {
                $("#bankDirectoryList").html('<li class="list-group-item text-center py-3 text-danger small">Failed to load banks</li>');
            }
        }).fail(function() {
            $("#bankDirectoryList").html('<li class="list-group-item text-center py-3 text-danger small">Network error while loading banks</li>');
        });
    }

    function filterBankDirectory(query) {
        let q = query.trim().toLowerCase();
        let count = 0;
        $("#bankDirectoryList li.bank-item").each(function() {
            let name = $(this).attr('data-name') || '';
            if (!q || name.includes(q)) {
                $(this).removeClass('d-none').addClass('d-flex');
                count++;
            } else {
                $(this).removeClass('d-flex').addClass('d-none');
            }
        });
        $("#bankListCount").text(count);
    }

    function saveNewBankName() {
        const bname = $("#new_bank_name").val().trim();

        if (!bname) {
            Swal.fire('Error', 'Please enter a Bank Name', 'warning');
            return;
        }

        $("#btnSaveNewBankName").prop('disabled', true);

        $.post("bank/bank-list-add.php", { bname: bname }, function(res) {
            $("#btnSaveNewBankName").prop('disabled', false);
            try {
                let data = typeof res === 'object' ? res : JSON.parse(res);
                if (data.status === 'success') {
                    // Add to dropdown if not already there
                    let exists = false;
                    $("#bankname option").each(function() {
                        if ($(this).val().toLowerCase() === bname.toLowerCase()) {
                            exists = true;
                        }
                    });

                    if (!exists) {
                        $("#bankname").append(new Option(bname, bname));
                    }
                    $("#bankname").val(bname);

                    // Reset input & reload directory list
                    $("#new_bank_name").val('');
                    loadBankDirectoryList();

                    Swal.fire({
                        title: 'Bank Added!',
                        text: data.message,
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to add bank', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Invalid server response: ' + res, 'error');
            }
        });
    }

    function deleteBankFromList(id, bname) {
        Swal.fire({
            title: 'Delete Bank from Directory?',
            html: `Are you sure you want to remove <strong>${bname}</strong> from the bank directory?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("bank/bank-list-delete.php", { id: id }, function(res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            // Remove option from dropdown
                            $("#bankname option").each(function() {
                                if ($(this).val().toLowerCase() === bname.toLowerCase()) {
                                    $(this).remove();
                                }
                            });

                            loadBankDirectoryList();

                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else if (data.status === 'in_use') {
                            Swal.fire('Cannot Delete', data.message, 'warning');
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete bank', 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Server response: ' + res, 'error');
                    }
                });
            }
        });
    }

    function populateBankForm(x) {
        $("#modalTitle").html('<i class="bi bi-pencil-square"></i> Edit Bank Account');
        $("#bank_id").val(x.id);
        $("#accno").val(x.accno || '');
        
        // Set Account Type dropdown with fallback if not present
        if (x.acctype) {
            let typeExists = false;
            $("#acctype option").each(function() {
                if ($(this).val().toLowerCase() === String(x.acctype).toLowerCase()) {
                    typeExists = true;
                    $(this).prop('selected', true);
                }
            });
            if (!typeExists) {
                $("#acctype").append(new Option(x.acctype, x.acctype, true, true));
            }
            $("#acctype").val(x.acctype);
        } else {
            $("#acctype").val('General');
        }

        $("#branch").val(x.branch || '');
        $("#routing_no").val(x.routing_no || '');
        $("#slot").val(x.slot || '');
        $("#opening_balance").val(x.opening_balance || '0.00');
        $("#openingdate").val(x.openingdate && x.openingdate !== '0000-00-00' ? x.openingdate : '');

        // Ensure bank name exists in dropdown
        if (x.bankname) {
            let exists = false;
            $("#bankname option").each(function() {
                if ($(this).val().toLowerCase() === String(x.bankname).toLowerCase()) {
                    exists = true;
                }
            });
            if (!exists) {
                $("#bankname").append(new Option(x.bankname, x.bankname));
            }
            $("#bankname").val(x.bankname);
        }

        // Check closing status
        const isClosed = (x.status == 0 || (x.closingdate && x.closingdate !== '0000-00-00'));
        $("#is_closed_toggle").prop('checked', isClosed);
        if (isClosed) {
            $("#closingDateWrapper").show();
            $("#closingdate").val(x.closingdate && x.closingdate !== '0000-00-00' ? x.closingdate : new Date().toISOString().split('T')[0]);
        } else {
            $("#closingDateWrapper").hide();
            $("#closingdate").val('');
        }

        var mElem = document.getElementById("bankModal");
        var myModal = bootstrap.Modal.getOrCreateInstance(mElem);
        myModal.show();
    }

    function editBank(id, btn) {
        if (btn) {
            var tr = $(btn).closest('tr.account-row');
            if (tr.length && tr.attr('data-account')) {
                try {
                    var localData = JSON.parse(tr.attr('data-account'));
                    if (localData && localData.id) {
                        populateBankForm(localData);
                        return;
                    }
                } catch(e) {
                    console.error("Parse row data error:", e);
                }
            }
        }

        $.post("bank/bank-get.php", { id: id }, function (data) {
            try {
                let x = typeof data === 'object' ? data : JSON.parse(data);
                if (x.error) {
                    Swal.fire('Error', x.error, 'error');
                    return;
                }
                populateBankForm(x);
            } catch(e) {
                Swal.fire('Error', 'Could not parse account data: ' + data, 'error');
            }
        }).fail(function() {
            Swal.fire('Error', 'Failed to retrieve account data', 'error');
        });
    }

    function saveBank() {
        const accno = $("#accno").val().trim();
        const bankname = $("#bankname").val().trim();

        if (!accno || !bankname) {
            Swal.fire('Required Fields', 'Please provide Account Number and select a Bank Name.', 'warning');
            return;
        }

        const isClosed = $("#is_closed_toggle").is(':checked') ? 1 : 0;
        const closingDate = $("#closingdate").val();

        let form = {
            id: $("#bank_id").val(),
            accno: accno,
            acctype: $("#acctype").val().trim(),
            bankname: bankname,
            branch: $("#branch").val().trim(),
            routing_no: $("#routing_no").val().trim(),
            slot: $("#slot").val(),
            opening_balance: $("#opening_balance").val(),
            openingdate: $("#openingdate").val(),
            is_closed: isClosed,
            closingdate: closingDate
        };

        $("#btnSaveBank").prop('disabled', true);

        $.post("bank/bank-save.php", form, function (res) {
            $("#btnSaveBank").prop('disabled', false);
            try {
                let data = typeof res === 'object' ? res : JSON.parse(res);
                if (data.status === 'success') {
                    var mElem = document.getElementById("bankModal");
                    var mInst = bootstrap.Modal.getInstance(mElem) || bootstrap.Modal.getOrCreateInstance(mElem);
                    mInst.hide();
                    
                    Swal.fire({
                        title: 'Saved!',
                        text: data.message,
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error', data.message || 'Failed to save account', 'error');
                }
            } catch(e) {
                Swal.fire('Response', res, 'info');
            }
        });
    }

    function closeBank(id, accno) {
        Swal.fire({
            title: 'Close Account?',
            html: `Are you sure you want to close account <strong>${accno}</strong>?<br><br>
                   <div class="text-start">
                       <label class="form-label small fw-bold">Select Closing Date:</label>
                       <input type="date" id="swal_closing_date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-lock-fill me-1"></i> Yes, Close Account',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const dt = document.getElementById('swal_closing_date').value;
                if (!dt) {
                    Swal.showValidationMessage('Please select a closing date');
                }
                return dt;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("bank/bank-toggle-status.php", { id: id, action: 'close', closingdate: result.value }, function(res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Closed!',
                                text: data.message,
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire('Error', data.message || 'Failed to close account', 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Server response: ' + res, 'error');
                    }
                });
            }
        });
    }

    function reopenBank(id, accno) {
        Swal.fire({
            title: 'Re-open Account?',
            html: `Do you want to re-open and activate account <strong>${accno}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-unlock-fill me-1"></i> Yes, Re-open Account',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("bank/bank-toggle-status.php", { id: id, action: 'reopen' }, function(res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Re-opened!',
                                text: data.message,
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire('Error', data.message || 'Failed to reopen account', 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Server response: ' + res, 'error');
                    }
                });
            }
        });
    }

    function removeBank(id, accno) {
        Swal.fire({
            title: 'Delete Account?',
            html: `Are you sure you want to permanently delete account <strong>${accno}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("bank/bank-delete.php", { id: id }, function (res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1000);
                        } else if (data.status === 'has_transactions') {
                            Swal.fire({
                                title: 'Cannot Delete Account',
                                html: data.message,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ff9800',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: '<i class="bi bi-lock-fill me-1"></i> Close Account Instead',
                                cancelButtonText: 'Cancel'
                            }).then((r2) => {
                                if (r2.isConfirmed) {
                                    closeBank(id, accno);
                                }
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete account', 'error');
                        }
                    } catch(e) {
                        Swal.fire('Notice', res, 'info');
                    }
                });
            }
        });
    }
</script>
</body>
</html>