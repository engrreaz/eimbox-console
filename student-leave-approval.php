<?php
require_once 'header.php';

$curYear = date('Y');
$selectedClass = trim($_GET['classname'] ?? '');
$selectedStatus = trim($_GET['status'] ?? 'recommended');

// Fetch distinct classes
$classes = [];
$cRes = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = '$sccode' AND (sessionyear = '$curYear' OR sessionyear = '' OR sessionyear IS NULL) AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
if ($cRes) {
    while ($r = $cRes->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// KPI Counts
$kpi_recommended = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'recommended'")->fetch_assoc()['c'] ?? 0;
$kpi_approved = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'approved'")->fetch_assoc()['c'] ?? 0;
$kpi_rejected = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND (status = 'rejected' OR status = 'rejected_by_teacher')")->fetch_assoc()['c'] ?? 0;
$kpi_pending = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'pending'")->fetch_assoc()['c'] ?? 0;

// Filter query
$whereSql = "WHERE a.sccode = '$sccode'";
if (!empty($selectedClass)) {
    $whereSql .= " AND a.classname = '" . $conn->real_escape_string($selectedClass) . "'";
}
if (!empty($selectedStatus) && $selectedStatus !== 'all') {
    if ($selectedStatus === 'all_rejected') {
        $whereSql .= " AND (a.status = 'rejected' OR a.status = 'rejected_by_teacher')";
    } else {
        $whereSql .= " AND a.status = '" . $conn->real_escape_string($selectedStatus) . "'";
    }
}

$apps = [];
$qSql = "SELECT a.*, 
        COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
        s.guarmobile, s.guarname 
    FROM student_leave_app a
    LEFT JOIN students s ON s.stid = a.stid AND s.sccode = a.sccode
    $whereSql
    ORDER BY CASE WHEN a.status = 'recommended' THEN 1 WHEN a.status = 'pending' THEN 2 ELSE 3 END, a.id DESC";

$qRes = $conn->query($qSql);
if ($qRes) {
    while ($row = $qRes->fetch_assoc()) {
        $apps[] = $row;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-shield-check text-success me-2"></i> Final Leave Approval Desk (ছুটি মঞ্জুর ও না-মঞ্জুর ডেস্ক)
            </h4>
            <p class="text-muted mb-0">Tier 2: Head Teacher / Principal final approval & rejection with automatic fine exemption sync</p>
        </div>
        <div class="d-flex gap-2">
            <a href="student-leave-apply.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i> New Application
            </a>
            <a href="student-leave-recommend.php" class="btn btn-outline-info">
                <i class="bi bi-person-check me-1"></i> Teacher Recommendations
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4 bg-info bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Awaiting Approval</span>
                        <h3 class="mb-0 fw-bold text-info"><?= $kpi_recommended ?></h3>
                    </div>
                    <div class="avatar bg-info text-white p-2 rounded">
                        <i class="bi bi-hourglass-top fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 bg-success bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Approved & Granted</span>
                        <h3 class="mb-0 fw-bold text-success"><?= $kpi_approved ?></h3>
                    </div>
                    <div class="avatar bg-success text-white p-2 rounded">
                        <i class="bi bi-check2-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 bg-danger bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Rejected Total</span>
                        <h3 class="mb-0 fw-bold text-danger"><?= $kpi_rejected ?></h3>
                    </div>
                    <div class="avatar bg-danger text-white p-2 rounded">
                        <i class="bi bi-x-octagon fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 bg-warning bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Pending Teacher Review</span>
                        <h3 class="mb-0 fw-bold text-warning"><?= $kpi_pending ?></h3>
                    </div>
                    <div class="avatar bg-warning text-white p-2 rounded">
                        <i class="bi bi-person-exclamation fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Filter by Class</label>
                    <select class="form-select form-select-sm" name="classname" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= ($selectedClass === $c) ? 'selected' : '' ?>>Class <?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Filter by Status</label>
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="all" <?= ($selectedStatus === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="recommended" <?= ($selectedStatus === 'recommended') ? 'selected' : '' ?>>Recommended (Ready for Approval)</option>
                        <option value="approved" <?= ($selectedStatus === 'approved') ? 'selected' : '' ?>>Approved Leaves Only</option>
                        <option value="all_rejected" <?= ($selectedStatus === 'all_rejected') ? 'selected' : '' ?>>Rejected Leaves</option>
                        <option value="pending" <?= ($selectedStatus === 'pending') ? 'selected' : '' ?>>Pending Teacher Review</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="student-leave-approval.php" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approval Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-check-circle-fill me-2 text-success"></i> Leave Applications Approval Queue (<?= count($apps) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Student Details</th>
                            <th>Leave Period</th>
                            <th>Reason & Teacher Notes</th>
                            <th>Current Status</th>
                            <th class="text-center">Final Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($apps)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-all fs-1 d-block mb-2 text-success opacity-50"></i>
                                    No applications pending for final decision.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($apps as $app): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-label-secondary">#<?= $app['id'] ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($app['stname'] ?: "Student #{$app['stid']}") ?></div>
                                        <small class="text-muted d-block">ID: <code><?= $app['stid'] ?></code> | Ph: <?= htmlspecialchars($app['guarmobile'] ?? 'N/A') ?></small>
                                        <span class="badge bg-light text-dark border">Cl: <?= htmlspecialchars($app['classname']) ?> (<?= $app['sectionname'] ?: 'All' ?>) | Roll: <?= $app['rollno'] ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <?php if ($app['leave_type'] === 'advance'): ?>
                                                <span class="badge bg-label-primary mb-1"><i class="bi bi-calendar-check me-1"></i> Advance Leave</span>
                                            <?php else: ?>
                                                <span class="badge bg-label-warning mb-1"><i class="bi bi-clipboard-pulse me-1"></i> Post Absence</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small fw-semibold text-dark">
                                            <?= date('d M Y', strtotime($app['date_from'])) ?> to <?= date('d M Y', strtotime($app['date_to'])) ?>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold"><?= $app['days'] ?> Day(s)</span>
                                    </td>
                                    <td style="max-width: 280px;">
                                        <div class="small mb-1"><strong class="text-dark">Reason:</strong> <?= htmlspecialchars($app['reason']) ?></div>
                                        <?php if (!empty($app['recommend_notes'])): ?>
                                            <div class="p-1 px-2 border border-info rounded bg-info bg-opacity-10 small text-dark mb-1">
                                                <i class="bi bi-chat-left-quote me-1 text-info"></i> <strong>Teacher:</strong> <?= htmlspecialchars($app['recommend_notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($app['rejection_reason'])): ?>
                                            <div class="p-1 px-2 border border-danger rounded bg-danger bg-opacity-10 small text-danger">
                                                <i class="bi bi-x-circle me-1"></i> <strong>Declined Reason:</strong> <?= htmlspecialchars($app['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($app['attachment'])): ?>
                                            <a href="<?= htmlspecialchars($app['attachment']) ?>" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-1">
                                                <i class="bi bi-paperclip me-1"></i> View Attachment
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'approved'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i> Approved / Granted</span>
                                            <small class="d-block text-muted" style="font-size: 11px;">By: <?= htmlspecialchars($app['approved_by']) ?></small>
                                        <?php elseif ($app['status'] === 'recommended'): ?>
                                            <span class="badge bg-info"><i class="bi bi-send-check me-1"></i> Recommended</span>
                                            <small class="d-block text-muted" style="font-size: 11px;">By: <?= htmlspecialchars($app['recommended_by']) ?></small>
                                        <?php elseif ($app['status'] === 'rejected_by_teacher'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected (Teacher)</span>
                                        <?php elseif ($app['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-octagon me-1"></i> Rejected (Admin)</span>
                                            <small class="d-block text-danger" style="font-size: 11px;">By: <?= htmlspecialchars($app['approved_by']) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($app['status'] === 'recommended' || $app['status'] === 'pending'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success btnApproveApp" 
                                                    data-id="<?= $app['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($app['stname']) ?>"
                                                    data-days="<?= $app['days'] ?>"
                                                    data-dates="<?= $app['date_from'] ?> to <?= $app['date_to'] ?>">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btnDeclineApp" 
                                                    data-id="<?= $app['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($app['stname']) ?>">
                                                    <i class="bi bi-x-lg me-1"></i> Decline
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Approve & Grant Leave -->
<div class="modal fade" id="modalApprove" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-check2-circle me-2"></i> Final Approval & Grant Leave</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="appr_app_id">
                <div class="alert alert-success py-2 px-3 mb-3 small">
                    <i class="bi bi-shield-check me-1"></i> <strong>Auto Fine Exemption Sync:</strong> Approving this leave will automatically waive any absence fines recorded for these dates and sync the student finance ledger.
                </div>
                <p class="mb-2">Approve leave application for <strong id="appr_student_name">Student</strong>?</p>
                <div class="small text-muted mb-3" id="appr_meta_info">Period: - | Days: -</div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Admin Notes / Approval Remarks (ঐচ্ছিক):</label>
                    <textarea class="form-control" id="appr_notes" rows="2" placeholder="ছুটি মঞ্জুর করা হলো..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmApprove">
                    <i class="bi bi-check2-all me-1"></i> Confirm & Grant Leave
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Decline / Reject Application -->
<div class="modal fade" id="modalDecline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-x-octagon me-2"></i> Decline / Reject Leave Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="dec_app_id">
                <p class="mb-3">You are declining the leave application for <strong id="dec_student_name">Student</strong>.</p>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-danger">Rejection Reason Note (না-মঞ্জুরের সুনির্দিষ্ট কারণ - আবশ্যক):</label>
                    <textarea class="form-control" id="dec_notes" rows="3" placeholder="ছুটির স্বপক্ষে উপযুক্ত কাগজপত্র বা কারণ না থাকায় আবেদনটি না-মঞ্জুর করা হলো..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDecline">
                    <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve Modal trigger
    document.querySelectorAll('.btnApproveApp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('appr_app_id').value = this.dataset.id;
            document.getElementById('appr_student_name').textContent = this.dataset.name;
            document.getElementById('appr_meta_info').textContent = `Period: ${this.dataset.dates} | Duration: ${this.dataset.days} Day(s)`;
            document.getElementById('appr_notes').value = 'ছুটি মঞ্জুর করা হলো।';
            const modal = new bootstrap.Modal(document.getElementById('modalApprove'));
            modal.show();
        });
    });

    // Confirm Approve
    document.getElementById('btnConfirmApprove').addEventListener('click', function() {
        const appId = document.getElementById('appr_app_id').value;
        const notes = document.getElementById('appr_notes').value;
        const btn = this;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'final_decision_leave_application');
        formData.append('sccode', '<?= $sccode ?>');
        formData.append('app_id', appId);
        formData.append('decision', 'approve');
        formData.append('notes', notes);

        fetch('ajax/leave-actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Leave Granted!', text: data.message }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Approval Failed', text: data.message });
            }
        })
        .catch(err => {
            btn.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
        });
    });

    // Decline Modal trigger
    document.querySelectorAll('.btnDeclineApp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('dec_app_id').value = this.dataset.id;
            document.getElementById('dec_student_name').textContent = this.dataset.name;
            document.getElementById('dec_notes').value = '';
            const modal = new bootstrap.Modal(document.getElementById('modalDecline'));
            modal.show();
        });
    });

    // Confirm Decline
    document.getElementById('btnConfirmDecline').addEventListener('click', function() {
        const appId = document.getElementById('dec_app_id').value;
        const notes = document.getElementById('dec_notes').value.trim();
        if (!notes) {
            Swal.fire({ icon: 'warning', title: 'Reason Required', text: 'Please write a rejection reason note.' });
            return;
        }

        const btn = this;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'final_decision_leave_application');
        formData.append('sccode', '<?= $sccode ?>');
        formData.append('app_id', appId);
        formData.append('decision', 'reject');
        formData.append('notes', notes);

        fetch('ajax/leave-actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Declined', text: data.message }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
            }
        })
        .catch(err => {
            btn.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
