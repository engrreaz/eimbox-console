<?php
require_once 'header.php';
require_once 'core/payroll_engine.php';

$batch_id = intval($_GET['batch_id'] ?? 0);

if ($batch_id <= 0) {
    echo "<script>window.location.href='payroll-dashboard.php';</script>";
    exit;
}

// Fetch batch
$b_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE id = ? AND sccode = ?");
$b_stmt->bind_param("ii", $batch_id, $sccode);
$b_stmt->execute();
$batch = $b_stmt->get_result()->fetch_assoc();

if (!$batch) {
    echo "<div class='container-xxl p-4'><div class='alert alert-danger'>Payroll batch not found!</div></div>";
    require_once 'footer.php';
    exit;
}

// Fetch all disbursement line items joined with teacher metadata
$stmt = $conn->prepare("SELECT 
    d.*, 
    t.tname, 
    t.position, 
    t.ranks, 
    t.slots, 
    t.mobile,
    t.accno as t_accno,
    t.bankname as t_bankname,
    t.accnosch as t_accnosch,
    t.bnamesch as t_bnamesch
FROM teacher_salary_disbursement d
LEFT JOIN teacher t ON d.tid = t.tid AND d.sccode = t.sccode
WHERE d.batch_id = ? AND d.sccode = ?
ORDER BY CAST(t.ranks AS UNSIGNED) ASC, CAST(d.tid AS UNSIGNED) ASC");

$stmt->bind_param("ii", $batch_id, $sccode);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$month_text = ($month_names[$batch['salary_month']] ?? '') . ' ' . $batch['salary_year'];

// Bank accounts for disburse modal
$banks_stmt = $conn->prepare("SELECT id, bankname, accno, branch FROM bankinfo WHERE sccode = ? AND status = 1 ORDER BY id ASC");
$banks_stmt->bind_param("i", $sccode);
$banks_stmt->execute();
$bank_accounts = $banks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="payroll-dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold m-0"><?= htmlspecialchars($batch['batch_title']) ?></h4>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Month: <strong><?= $month_text ?></strong> &bull; 
                Status: <span class="badge bg-label-<?= $batch['status'] === 'Disbursed' ? 'success' : ($batch['status'] === 'Approved' ? 'primary' : 'warning') ?>"><?= htmlspecialchars($batch['status']) ?></span> &bull; 
                Created by: <?= htmlspecialchars($batch['created_by'] ?? 'System') ?>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-primary btn-sm" onclick="recalculateBatch(<?= $batch['id'] ?>)" <?= $batch['status'] === 'Disbursed' ? 'disabled' : '' ?>>
                <i class="bi bi-arrow-repeat me-1"></i> Recalculate
            </button>
            <a href="payroll-summary-report.php?batch_id=<?= $batch['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm" title="Compact 1-2 Page Summary Report">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Summary Sheet (1-2 Page)
            </a>
            <a href="payroll-payslip.php?batch_id=<?= $batch['id'] ?>" target="_blank" class="btn btn-outline-info btn-sm">
                <i class="bi bi-printer me-1"></i> Print Payslips
            </a>
            <?php if ($batch['status'] === 'Draft' || $batch['status'] === 'Reviewed'): ?>
                <button class="btn btn-primary btn-sm" onclick="approveBatch(<?= $batch['id'] ?>)">
                    <i class="bi bi-check2-circle me-1"></i> Approve Batch
                </button>
            <?php elseif ($batch['status'] === 'Approved'): ?>
                <button class="btn btn-success btn-sm" onclick="openDisburseModal(<?= $batch['id'] ?>, '<?= htmlspecialchars(addslashes($batch['batch_title'])) ?>', <?= $batch['total_school_net'] ?>)">
                    <i class="bi bi-cash-coin me-1"></i> Disburse &amp; Pay
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Metrics Ribbon -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">Total Staff / Teachers</span>
                    <h5 class="fw-bold mb-0 text-dark"><?= count($items) ?> Persons</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">Total Govt (MPO) Net</span>
                    <h5 class="fw-bold mb-0 text-info">৳ <?= number_format($batch['total_govt_net'], 2) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">Total School Net Payable</span>
                    <h5 class="fw-bold mb-0 text-primary">৳ <?= number_format($batch['total_school_net'], 2) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">Grand Total Net Payable</span>
                    <h5 class="fw-bold mb-0 text-success">৳ <?= number_format($batch['grand_total_net'], 2) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Payroll Grid -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom py-3 flex-wrap gap-2">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-people-fill text-primary me-2"></i> Employee Salary Sheet Breakdown
            </h5>
            <div class="d-flex gap-2">
                <input type="text" id="teacherSearchInput" class="form-control form-control-sm" placeholder="Search teacher by name/ID..." style="width: 250px;">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0" id="payrollTable" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-center align-middle" style="width: 40px;">#</th>
                        <th rowspan="2" class="align-middle">Teacher / Staff</th>
                        <th colspan="3" class="text-center bg-label-info text-info fw-bold py-1">Govt / MPO Part</th>
                        <th colspan="4" class="text-center bg-label-primary text-primary fw-bold py-1">School / Institutional Part</th>
                        <th rowspan="2" class="text-end align-middle bg-label-success text-success fw-bold">Total Net (৳)</th>
                        <th rowspan="2" class="text-center align-middle" style="width: 90px;">Action</th>
                    </tr>
                    <tr>
                        <th class="text-end py-1">MPO Gross</th>
                        <th class="text-end py-1">Govt Deduct</th>
                        <th class="text-end py-1 text-info fw-bold">Govt Net</th>
                        
                        <th class="text-end py-1">School Gross</th>
                        <th class="text-end py-1">PF</th>
                        <th class="text-end py-1">Other Deduct</th>
                        <th class="text-end py-1 text-primary fw-bold">School Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                No teachers found in this payroll batch.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $it): ?>
                            <?php 
                                $govt_ded = floatval($it['govt_welfare']) + floatval($it['govt_retire']);
                                $school_other_ded = floatval($it['school_fine_absent']) + floatval($it['school_advance_deduct']) + floatval($it['school_deductions_other']);
                            ?>
                            <tr class="teacher-row" data-name="<?= strtolower(htmlspecialchars($it['tname'] ?? '')) ?>" data-tid="<?= strtolower(htmlspecialchars($it['tid'])) ?>">
                                <td class="text-center text-muted fw-bold"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($it['tname'] ?? 'ID: ' . $it['tid']) ?></div>
                                    <small class="text-muted">
                                        ID: <?= htmlspecialchars($it['tid']) ?> | <?= htmlspecialchars($it['position'] ?? 'Faculty') ?>
                                    </small>
                                </td>
                                
                                <!-- Govt MPO -->
                                <td class="text-end">৳ <?= number_format($it['govt_gross'], 2) ?></td>
                                <td class="text-end text-danger">- ৳ <?= number_format($govt_ded, 2) ?></td>
                                <td class="text-end fw-bold text-info">৳ <?= number_format($it['govt_net'], 2) ?></td>

                                <!-- School Fund -->
                                <td class="text-end">৳ <?= number_format($it['school_gross'], 2) ?></td>
                                <td class="text-end text-danger">- ৳ <?= number_format($it['school_pf'], 2) ?></td>
                                <td class="text-end text-danger">- ৳ <?= number_format($school_other_ded, 2) ?></td>
                                <td class="text-end fw-bold text-primary">৳ <?= number_format($it['school_net'], 2) ?></td>

                                <!-- Total Net -->
                                <td class="text-end fw-bold text-success fs-6">
                                    ৳ <?= number_format($it['total_net_payable'], 2) ?>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-xs btn-outline-primary" onclick="openAdjustModal(<?= htmlspecialchars(json_encode($it)) ?>)" title="Adjust &amp; Notes">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="payroll-payslip.php?payslip_no=<?= urlencode($it['payslip_no']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary" title="View Payslip">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Grand Batch Totals:</td>
                        <td class="text-end">৳ <?= number_format($batch['total_govt_gross'], 2) ?></td>
                        <td class="text-end text-danger">- ৳ <?= number_format($batch['total_govt_deduction'], 2) ?></td>
                        <td class="text-end text-info">৳ <?= number_format($batch['total_govt_net'], 2) ?></td>
                        <td class="text-end">৳ <?= number_format($batch['total_school_gross'], 2) ?></td>
                        <td colspan="2" class="text-end text-danger">- ৳ <?= number_format($batch['total_school_deduction'], 2) ?></td>
                        <td class="text-end text-primary">৳ <?= number_format($batch['total_school_net'], 2) ?></td>
                        <td class="text-end text-success fs-6">৳ <?= number_format($batch['grand_total_net'], 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Adjust Teacher Salary Line Item -->
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bi bi-sliders me-2"></i> Adjust Teacher Monthly Salary
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustForm" onsubmit="handleAdjustSubmit(event)">
                <input type="hidden" name="item_id" id="adj_item_id">
                <input type="hidden" name="batch_id" value="<?= $batch['id'] ?>">

                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0 fw-bold" id="adj_teacher_name">Teacher Name</h6>
                            <small class="text-muted" id="adj_teacher_sub">ID: 000 | Designation</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-label-info">Govt Net: <strong id="adj_govt_net">৳ 0.00</strong></span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-plus-slash-minus me-1"></i> School Fund Dynamic Adjustments
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Custom Allowance / Bonus (৳)</label>
                            <input type="number" step="0.01" class="form-control" name="school_allowances_other" id="adj_other_allowance" value="0.00">
                            <small class="text-muted">Special allowance, overtime, or performance bonus</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Absent Fine (৳)</label>
                            <input type="number" step="0.01" class="form-control" name="school_fine_absent" id="adj_absent_fine" value="0.00">
                            <small class="text-muted">Deductions for unapproved leaves</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Advance Salary Recovery (৳)</label>
                            <input type="number" step="0.01" class="form-control" name="school_advance_deduct" id="adj_advance_deduct" value="0.00">
                            <small class="text-muted">Monthly recovery of staff advances</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Other Deductions (৳)</label>
                            <input type="number" step="0.01" class="form-control" name="school_deductions_other" id="adj_other_deduct" value="0.00">
                            <small class="text-muted">Miscellaneous charges or fines</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Remarks / Notes</label>
                            <textarea class="form-control" name="remarks" id="adj_remarks" rows="2" placeholder="Specific notes for this month's payslip..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnAdjustSubmit">
                        <i class="bi bi-save me-1"></i> Save Adjustment &amp; Recompute
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
                <input type="hidden" name="batch_id" value="<?= $batch['id'] ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 mb-3">
                        <div class="fw-bold"><?= htmlspecialchars($batch['batch_title']) ?></div>
                        <small>Total School Net Payable: <strong class="text-dark">৳ <?= number_format($batch['total_school_net'], 2) ?></strong></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="BankTransfer">Bank Transfer (EFT / BEFTN)</option>
                            <option value="Cash">Cash (Hand-to-Hand)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                        </select>
                    </div>

                    <div class="mb-3">
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
                        <input class="form-check-input" type="checkbox" name="post_to_cashbook" id="post_to_cashbook_sheet" value="1" checked>
                        <label class="form-check-label fw-bold" for="post_to_cashbook_sheet">
                            Auto-Post Expenditure Voucher to Cashbook
                        </label>
                        <small class="form-text text-muted d-block">Generates an automated 'Staff Salary' expense entry in the institutional cashbook.</small>
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
// Filter table by teacher search
document.getElementById('teacherSearchInput')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.teacher-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const tid = row.getAttribute('data-tid') || '';
        if (name.includes(q) || tid.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function openAdjustModal(item) {
    document.getElementById('adj_item_id').value = item.id;
    document.getElementById('adj_teacher_name').innerText = item.tname || ('Teacher ID: ' + item.tid);
    document.getElementById('adj_teacher_sub').innerText = 'ID: ' + item.tid + ' | ' + (item.position || 'Faculty');
    document.getElementById('adj_govt_net').innerText = '৳ ' + parseFloat(item.govt_net || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    document.getElementById('adj_other_allowance').value = parseFloat(item.school_allowances_other || 0).toFixed(2);
    document.getElementById('adj_absent_fine').value = parseFloat(item.school_fine_absent || 0).toFixed(2);
    document.getElementById('adj_advance_deduct').value = parseFloat(item.school_advance_deduct || 0).toFixed(2);
    document.getElementById('adj_other_deduct').value = parseFloat(item.school_deductions_other || 0).toFixed(2);
    document.getElementById('adj_remarks').value = item.remarks || '';

    const modal = new bootstrap.Modal(document.getElementById('adjustModal'));
    modal.show();
}

function handleAdjustSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnAdjustSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const formData = new FormData(e.target);
    formData.append('action', 'update_item');

    fetch('api/payroll-action.php', {
        method: 'POST',
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try { return JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid JSON'); }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Adjustment & Recompute';
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Adjustment Saved!',
                text: 'Teacher salary line item updated and recomputed.',
                timer: 1400,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', res.message || 'Error updating adjustment.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Adjustment & Recompute';
        Swal.fire('Error', err.message, 'error');
    });
}

function recalculateBatch(batchId) {
    Swal.fire({
        title: 'Recalculate Batch?',
        text: 'This will refresh all calculations from current salary structures and adjustments.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Yes, Recalculate'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const fd = new FormData();
        fd.append('action', 'recalculate_batch');
        fd.append('batch_id', batchId);

        fetch('api/payroll-action.php', {
            method: 'POST',
            body: fd
        })
        .then(async r => {
            const text = await r.text();
            try { return JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid JSON'); }
        })
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Recalculated!',
                    text: 'Payroll batch successfully recalculated.',
                    timer: 1400,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', res.message || 'Error recalculating batch.', 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    });
}

function approveBatch(batchId) {
    Swal.fire({
        title: 'Approve Payroll Batch?',
        text: 'Once approved, the batch will be finalized and ready for salary disbursement.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Yes, Approve'
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
            try { return JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid JSON'); }
        })
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Batch Approved!',
                    text: 'The batch is now approved.',
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

function openDisburseModal() {
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
            try { return JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid JSON'); }
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
            Swal.fire('Error', err.message || 'Server communication error.', 'error');
        });
    });
}
</script>

<?php require_once 'footer.php'; ?>
