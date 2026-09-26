<?php
require_once 'header.php';

$curYear = date('Y');
$selectedClass = trim($_GET['classname'] ?? '');
$selectedStatus = trim($_GET['status'] ?? 'pending');

// Fetch distinct classes
$classes = [];
$cRes = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = '$sccode' AND (sessionyear = '$curYear' OR sessionyear = '' OR sessionyear IS NULL) AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
if ($cRes) {
    while ($r = $cRes->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// KPI Counts
$kpi_pending = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'pending'")->fetch_assoc()['c'] ?? 0;
$kpi_recommended = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'recommended'")->fetch_assoc()['c'] ?? 0;
$kpi_rejected = $conn->query("SELECT COUNT(*) as c FROM student_leave_app WHERE sccode = '$sccode' AND status = 'rejected_by_teacher'")->fetch_assoc()['c'] ?? 0;

// Filter query
$whereSql = "WHERE a.sccode = '$sccode'";
if (!empty($selectedClass)) {
    $whereSql .= " AND a.classname = '" . $conn->real_escape_string($selectedClass) . "'";
}
if (!empty($selectedStatus) && $selectedStatus !== 'all') {
    $whereSql .= " AND a.status = '" . $conn->real_escape_string($selectedStatus) . "'";
}

$apps = [];
$qSql = "SELECT a.*, 
        COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
        s.guarmobile, s.guarname 
    FROM student_leave_app a
    LEFT JOIN students s ON s.stid = a.stid AND s.sccode = a.sccode
    $whereSql
    ORDER BY CASE WHEN a.status = 'pending' THEN 1 WHEN a.status = 'recommended' THEN 2 ELSE 3 END, a.id DESC";

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
                <i class="bi bi-person-check-fill text-info me-2"></i> Class Teacher Recommendation (ছুটির আবেদন যাচাই ও সুপারিশ)
            </h4>
            <p class="text-muted mb-0">Tier 1: Review student leave requests, recommend to Head Teacher or reject with reason</p>
        </div>
        <div class="d-flex gap-2">
            <a href="student-leave-apply.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i> New Application
            </a>
            <a href="student-leave-approval.php" class="btn btn-outline-success">
                <i class="bi bi-shield-check me-1"></i> Final Approval Desk
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4 bg-warning bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Pending Review (যাচাইয়ের অপেক্ষায়)</span>
                        <h3 class="mb-0 fw-bold text-warning"><?= $kpi_pending ?></h3>
                    </div>
                    <div class="avatar bg-warning text-white p-2 rounded">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-info border-4 bg-info bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Recommended (সুপারিশকৃত)</span>
                        <h3 class="mb-0 fw-bold text-info"><?= $kpi_recommended ?></h3>
                    </div>
                    <div class="avatar bg-info text-white p-2 rounded">
                        <i class="bi bi-send-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4 bg-danger bg-opacity-10">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Rejected by Teacher (বাতিলকৃত)</span>
                        <h3 class="mb-0 fw-bold text-danger"><?= $kpi_rejected ?></h3>
                    </div>
                    <div class="avatar bg-danger text-white p-2 rounded">
                        <i class="bi bi-x-circle fs-4"></i>
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
                        <option value="pending" <?= ($selectedStatus === 'pending') ? 'selected' : '' ?>>Pending Review Only</option>
                        <option value="recommended" <?= ($selectedStatus === 'recommended') ? 'selected' : '' ?>>Recommended</option>
                        <option value="rejected_by_teacher" <?= ($selectedStatus === 'rejected_by_teacher') ? 'selected' : '' ?>>Rejected by Teacher</option>
                        <option value="approved" <?= ($selectedStatus === 'approved') ? 'selected' : '' ?>>Approved by Admin</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="student-leave-recommend.php" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-list-check me-2 text-primary"></i> Leave Applications for Review (<?= count($apps) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Student Details</th>
                            <th>Leave Category & Dates</th>
                            <th>Reason (কারণ)</th>
                            <th>Status</th>
                            <th class="text-center">Review Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($apps)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                                    No applications matching current filters. All caught up!
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
                                        <small class="text-muted d-block">ID: <code><?= $app['stid'] ?></code></small>
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
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= $app['days'] ?> Day(s)</span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="text-truncate small text-muted" title="<?= htmlspecialchars($app['reason']) ?>">
                                            <?= htmlspecialchars($app['reason']) ?>
                                        </div>
                                        <?php if (!empty($app['attachment'])): ?>
                                            <a href="<?= htmlspecialchars($app['attachment']) ?>" target="_blank" class="small text-primary text-decoration-none">
                                                <i class="bi bi-paperclip me-1"></i> Attachment
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'approved'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> Approved (Admin)</span>
                                        <?php elseif ($app['status'] === 'recommended'): ?>
                                            <span class="badge bg-info"><i class="bi bi-send-check me-1"></i> Recommended</span>
                                            <small class="d-block text-muted" style="font-size: 11px;">By: <?= htmlspecialchars($app['recommended_by']) ?></small>
                                        <?php elseif ($app['status'] === 'rejected_by_teacher'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected (Teacher)</span>
                                            <small class="d-block text-danger" style="font-size: 11px;"><?= htmlspecialchars($app['recommend_notes'] ?? '') ?></small>
                                        <?php elseif ($app['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-octagon me-1"></i> Rejected (Admin)</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success btnRecommendApp" 
                                                    data-id="<?= $app['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($app['stname']) ?>">
                                                    <i class="bi bi-hand-thumbs-up me-1"></i> Recommend
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btnRejectApp" 
                                                    data-id="<?= $app['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($app['stname']) ?>">
                                                    <i class="bi bi-x-lg me-1"></i> Reject
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="bi bi-lock me-1"></i> Processed</span>
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

<!-- Modal: Recommend Application -->
<div class="modal fade" id="modalRecommend" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-hand-thumbs-up me-2"></i> Recommend Leave Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rec_app_id">
                <p class="mb-3">Are you sure you want to recommend the leave application for <strong id="rec_student_name">Student</strong> to the Head Teacher?</p>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Recommendation Note / Remarks (মন্তব্য - ঐচ্ছিক):</label>
                    <textarea class="form-control" id="rec_notes" rows="2" placeholder="ছুটির কারণ যথার্থ, সুপারিশ করা হলো..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmRecommend">
                    <i class="bi bi-check-circle me-1"></i> Confirm & Forward
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reject Application -->
<div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-x-circle me-2"></i> Reject Leave Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rej_app_id">
                <p class="mb-3">You are rejecting the leave application for <strong id="rej_student_name">Student</strong>.</p>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-danger">Rejection Reason (বাতিলের সুনির্দিষ্ট কারণ - আবশ্যক):</label>
                    <textarea class="form-control" id="rej_notes" rows="3" placeholder="পর্যাপ্ত কারণ বা প্রমাণ না থাকায় আবেদনটি বাতিল করা হলো..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReject">
                    <i class="bi bi-trash3 me-1"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recommend Modal trigger
    document.querySelectorAll('.btnRecommendApp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rec_app_id').value = this.dataset.id;
            document.getElementById('rec_student_name').textContent = this.dataset.name;
            document.getElementById('rec_notes').value = 'ছুটির কারণ গ্রহণযোগ্য। ছুটির সুপারিশ করা হলো।';
            const modal = new bootstrap.Modal(document.getElementById('modalRecommend'));
            modal.show();
        });
    });

    // Confirm Recommend
    document.getElementById('btnConfirmRecommend').addEventListener('click', function() {
        const appId = document.getElementById('rec_app_id').value;
        const notes = document.getElementById('rec_notes').value;
        const btn = this;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'recommend_leave_application');
        formData.append('sccode', '<?= $sccode ?>');
        formData.append('app_id', appId);
        formData.append('decision', 'recommend');
        formData.append('notes', notes);

        fetch('ajax/leave-actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Recommended!', text: data.message }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
            }
        })
        .catch(err => {
            btn.disabled = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
        });
    });

    // Reject Modal trigger
    document.querySelectorAll('.btnRejectApp').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rej_app_id').value = this.dataset.id;
            document.getElementById('rej_student_name').textContent = this.dataset.name;
            document.getElementById('rej_notes').value = '';
            const modal = new bootstrap.Modal(document.getElementById('modalReject'));
            modal.show();
        });
    });

    // Confirm Reject
    document.getElementById('btnConfirmReject').addEventListener('click', function() {
        const appId = document.getElementById('rej_app_id').value;
        const notes = document.getElementById('rej_notes').value.trim();
        if (!notes) {
            Swal.fire({ icon: 'warning', title: 'Reason Required', text: 'Please write a rejection reason note.' });
            return;
        }

        const btn = this;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'recommend_leave_application');
        formData.append('sccode', '<?= $sccode ?>');
        formData.append('app_id', appId);
        formData.append('decision', 'reject');
        formData.append('notes', notes);

        fetch('ajax/leave-actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Rejected', text: data.message }).then(() => location.reload());
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
