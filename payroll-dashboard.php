<?php
require_once 'header.php';
require_once 'core/payroll_engine.php';

$selected_year = intval($_GET['year'] ?? date('Y'));

// Fetch summary metrics for this school
$metrics_stmt = $conn->prepare("SELECT 
    COUNT(*) as total_batches,
    COALESCE(SUM(CASE WHEN status = 'Disbursed' THEN grand_total_net ELSE 0 END), 0) as total_disbursed,
    COALESCE(SUM(CASE WHEN status = 'Disbursed' THEN total_govt_net ELSE 0 END), 0) as total_govt_disbursed,
    COALESCE(SUM(CASE WHEN status = 'Disbursed' THEN total_school_net ELSE 0 END), 0) as total_school_disbursed,
    COALESCE(SUM(CASE WHEN status = 'Draft' OR status = 'Reviewed' THEN 1 ELSE 0 END), 0) as pending_batches
FROM teacher_payroll_batch 
WHERE sccode = ? AND salary_year = ?");
$metrics_stmt->bind_param("ii", $sccode, $selected_year);
$metrics_stmt->execute();
$metrics = $metrics_stmt->get_result()->fetch_assoc();

// Fetch batches for this year
$batch_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE sccode = ? AND salary_year = ? ORDER BY salary_month DESC, id DESC");
$batch_stmt->bind_param("ii", $sccode, $selected_year);
$batch_stmt->execute();
$batches = $batch_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Bank accounts for disbursement modal
$banks_stmt = $conn->prepare("SELECT id, bankname, accno, branch FROM bankinfo WHERE sccode = ? AND status = 1 ORDER BY id ASC");
$banks_stmt->bind_param("i", $sccode);
$banks_stmt->execute();
$bank_accounts = $banks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Payroll &amp; HR /</span> Teacher Salary Management</h4>
            <p class="text-muted small mb-0">Monthly payroll calculation, MPO &amp; institutional disbursements, bank advice &amp; payslips</p>
        </div>
        <div class="d-flex gap-2">
            <a href="salary-settings.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i> Salary Structure Setup
            </a>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
                <i class="bi bi-plus-circle me-1"></i> Generate Monthly Payroll
            </button>
        </div>
    </div>

    <!-- Year Filter & KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Year selector card -->
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-primary text-white">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-white text-primary mb-2">Fiscal Year</span>
                        <h5 class="card-title text-white mb-1">Payroll Year: <?= htmlspecialchars($selected_year) ?></h5>
                        <p class="small text-white-50 mb-0">Select year to view batches</p>
                    </div>
                    <form method="GET" class="mt-3">
                        <select name="year" class="form-select form-select-sm bg-white text-dark border-0" onchange="this.form.submit()">
                            <?php for ($y = date('Y') + 1; $y >= date('Y') - 4; $y--): ?>
                                <option value="<?= $y ?>" <?= $y === $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Total Disbursed -->
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success p-2">
                                <i class="bi bi-cash-stack fs-4 text-success"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-semibold">Total Disbursed (<?= $selected_year ?>)</span>
                            <h4 class="card-title mb-0 text-success">৳ <?= number_format($metrics['total_disbursed'] ?? 0, 2) ?></h4>
                        </div>
                    </div>
                    <small class="text-muted">Total Net Salary Paid to Teachers</small>
                </div>
            </div>
        </div>

        <!-- Govt vs School Split -->
        <div class="col-6 col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info p-2">
                                <i class="bi bi-bank fs-4 text-info"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-semibold">Govt (MPO) vs School</span>
                            <div class="small mt-1">
                                <span class="text-info fw-bold">Govt: ৳ <?= number_format($metrics['total_govt_disbursed'] ?? 0, 0) ?></span><br>
                                <span class="text-primary fw-bold">School: ৳ <?= number_format($metrics['total_school_disbursed'] ?? 0, 0) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approval Batches -->
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning p-2">
                                <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-semibold">Pending Approval</span>
                            <h4 class="card-title mb-0 text-warning"><?= intval($metrics['pending_batches'] ?? 0) ?> Batch(es)</h4>
                        </div>
                    </div>
                    <small class="text-muted">Requires headmaster review</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Payroll Batches Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-table text-primary me-2"></i> Monthly Payroll Batches (<?= $selected_year ?>)
            </h5>
            <span class="badge bg-label-primary"><?= count($batches) ?> Total Batches</span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 60px;">#</th>
                        <th>Month / Batch Title</th>
                        <th class="text-center">Teachers</th>
                        <th class="text-end">Govt (MPO) Net</th>
                        <th class="text-end">School Fund Net</th>
                        <th class="text-end">Grand Total Net</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                <span>No payroll batches found for <?= $selected_year ?>. Click <strong>"Generate Monthly Payroll"</strong> to prepare one.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $idx => $b): ?>
                            <?php 
                                $status_badge = match($b['status']) {
                                    'Draft' => '<span class="badge bg-label-warning"><i class="bi bi-pencil me-1"></i> Draft</span>',
                                    'Reviewed' => '<span class="badge bg-label-info"><i class="bi bi-eye me-1"></i> Reviewed</span>',
                                    'Approved' => '<span class="badge bg-label-primary"><i class="bi bi-check2-circle me-1"></i> Approved</span>',
                                    'Disbursed' => '<span class="badge bg-label-success"><i class="bi bi-check-all me-1"></i> Disbursed</span>',
                                    'Cancelled' => '<span class="badge bg-label-danger"><i class="bi bi-x-circle me-1"></i> Cancelled</span>',
                                    default => '<span class="badge bg-label-secondary">' . htmlspecialchars($b['status']) . '</span>'
                                };
                            ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['batch_title']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i> <?= $month_names[$b['salary_month']] ?? '' ?>, <?= $b['salary_year'] ?>
                                        &bull; Created by: <?= htmlspecialchars($b['created_by'] ?? 'System') ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-label-dark"><?= intval($b['total_teachers']) ?> Teachers</span>
                                </td>
                                <td class="text-end fw-semibold text-info">
                                    ৳ <?= number_format($b['total_govt_net'], 2) ?>
                                </td>
                                <td class="text-end fw-semibold text-primary">
                                    ৳ <?= number_format($b['total_school_net'], 2) ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ৳ <?= number_format($b['grand_total_net'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <?= $status_badge ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="payroll-sheet.php?batch_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary" title="View & Edit Payroll Sheet">
                                            <i class="bi bi-table me-1"></i> Sheet
                                        </a>
                                        <a href="payroll-summary-report.php?batch_id=<?= $b['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Compact 1-2 Page Summary Report">
                                            <i class="bi bi-file-earmark-spreadsheet"></i>
                                        </a>
                                        
                                        <?php if ($b['status'] === 'Draft' || $b['status'] === 'Reviewed'): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="approveBatch(<?= $b['id'] ?>)" title="Approve Batch">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'Approved'): ?>
                                            <button class="btn btn-sm btn-success" onclick="openDisburseModal(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['batch_title'])) ?>', <?= $b['total_school_net'] ?>)" title="Disburse & Pay">
                                                <i class="bi bi-cash me-1"></i> Disburse
                                            </button>
                                        <?php elseif ($b['status'] === 'Disbursed'): ?>
                                            <a href="payroll-payslip.php?batch_id=<?= $b['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Print All Payslips">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php endif; ?>
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

<!-- Modal: Generate Monthly Payroll -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bi bi-calculator me-2"></i> Generate Monthly Payroll Batch
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generatePayrollForm" onsubmit="handleGeneratePayroll(event)">
                <div class="modal-body p-4">
                    <p class="text-muted small">
                        This will fetch current salary structures of all active teachers, apply any pending absence/bonus adjustments, and create a draft payroll sheet.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Select Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select" required>
                                <?php foreach ($month_names as $m_num => $m_name): ?>
                                    <option value="<?= $m_num ?>" <?= $m_num === intval(date('n')) ? 'selected' : '' ?>>
                                        <?= $m_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Select Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-select" required>
                                <?php for ($y = date('Y') + 1; $y >= date('Y') - 3; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y === intval(date('Y')) ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnGenSubmit">
                        <i class="bi bi-gear-fill me-1"></i> Generate Draft Batch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Disburse Batch -->
<div class="modal fade" id="disburseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="bi bi-cash-coin me-2"></i> Disburse Payroll &amp; Post to Cashbook
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="disburseForm" onsubmit="handleDisburseSubmit(event)">
                <input type="hidden" name="batch_id" id="disburse_batch_id">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 mb-3">
                        <div class="fw-bold" id="disburse_batch_title">Payroll Batch</div>
                        <small>Total School Net Payable: <strong class="text-dark" id="disburse_school_amount">৳ 0.00</strong></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" id="disburse_payment_method" required>
                            <option value="BankTransfer">Bank Transfer (EFT / BEFTN)</option>
                            <option value="Cash">Cash (Hand-to-Hand)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                        </select>
                    </div>

                    <div class="mb-3" id="bankSelectDiv">
                        <label class="form-label fw-bold">From School Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="0">-- Select School Bank Account (Optional) --</option>
                            <?php foreach ($bank_accounts as $ba): ?>
                                <option value="<?= $ba['id'] ?>">
                                    <?= htmlspecialchars($ba['bankname']) ?> - <?= htmlspecialchars($ba['accno']) ?> (<?= htmlspecialchars($ba['branch']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="post_to_cashbook" id="post_to_cashbook" value="1" checked>
                        <label class="form-check-label fw-bold" for="post_to_cashbook">
                            Auto-Post Expenditure Voucher to Cashbook
                        </label>
                        <small class="form-text text-muted d-block">Creates an automated 'Staff Salary' expenditure voucher under your institutional cashbook.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="btnDisburseSubmit">
                        <i class="bi bi-check2-circle me-1"></i> Confirm Disbursement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleGeneratePayroll(e) {
    e.preventDefault();
    const btn = document.getElementById('btnGenSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';

    const formData = new FormData(e.target);
    formData.append('action', 'generate_batch');

    fetch('api/payroll-action.php', {
        method: 'POST',
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            throw new Error(text || 'Invalid JSON response from server');
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-gear-fill me-1"></i> Generate Draft Batch';
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Draft Batch Generated!',
                text: 'Redirecting to payroll breakdown sheet...',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'payroll-sheet.php?batch_id=' + res.batch.id;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: res.message || 'Failed to generate payroll batch.'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-gear-fill me-1"></i> Generate Draft Batch';
        Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: err.message
        });
    });
}

function approveBatch(batchId) {
    Swal.fire({
        title: 'Approve Payroll Batch?',
        text: 'Are you sure you want to approve this monthly payroll batch for disbursement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Yes, Approve'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const fd = new FormData();
        fd.append('action', 'approve_batch');
        fd.append('batch_id', batchId);

        fetch('api/payroll-action.php', {
            method: 'POST',
            body: fd
        })
        .then(async r => {
            const text = await r.text();
            try { return JSON.parse(text); } catch (e) { throw new Error(text); }
        })
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Batch Approved!',
                    text: 'The payroll batch is now approved.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', res.message || 'Error approving batch.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    });
}

function openDisburseModal(batchId, title, schoolAmount) {
    document.getElementById('disburse_batch_id').value = batchId;
    document.getElementById('disburse_batch_title').innerText = title;
    document.getElementById('disburse_school_amount').innerText = '৳ ' + parseFloat(schoolAmount).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    const modal = new bootstrap.Modal(document.getElementById('disburseModal'));
    modal.show();
}

function handleDisburseSubmit(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Confirm Disbursement?',
        text: 'This will disburse the payroll and post the expenditure voucher to the cashbook.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-cash-coin me-1"></i> Confirm & Disburse'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('btnDisburseSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        const formData = new FormData(e.target);
        formData.append('action', 'disburse_batch');

        fetch('api/payroll-action.php', {
            method: 'POST',
            body: formData
        })
        .then(async r => {
            const text = await r.text();
            try { return JSON.parse(text); } catch (err) { throw new Error(text); }
        })
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm Disbursement';
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Payroll Disbursed!',
                    text: res.message || 'Payroll disbursed successfully!',
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Disbursement Failed', res.message || 'Failed to disburse payroll.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm Disbursement';
            Swal.fire('Error', err.message || 'Error communicating with server.', 'error');
        });
    });
}
</script>

<?php require_once 'footer.php'; ?>
