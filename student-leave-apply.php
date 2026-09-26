<?php
require_once 'header.php';

$curYear = date('Y');
$classes = [];
$cRes = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = '$sccode' AND (sessionyear = '$curYear' OR sessionyear = '' OR sessionyear IS NULL) AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
if ($cRes) {
    while ($r = $cRes->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// Fetch recent leave applications for this school
$recentApps = [];
$rStmt = $conn->prepare("SELECT a.*, 
        COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
        s.guarmobile 
    FROM student_leave_app a
    LEFT JOIN students s ON s.stid = a.stid AND s.sccode = a.sccode
    WHERE a.sccode = ? 
    ORDER BY a.id DESC LIMIT 50");
$rStmt->bind_param('i', $sccode);
$rStmt->execute();
$rRes = $rStmt->get_result();
while ($row = $rRes->fetch_assoc()) {
    $recentApps[] = $row;
}
$rStmt->close();
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-calendar2-plus text-primary me-2"></i> Student Leave Application (ছুটির আবেদন)
            </h4>
            <p class="text-muted mb-0">Apply for advance leave or post-absence leave for students with instant validation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="student-leave-recommend.php" class="btn btn-outline-info">
                <i class="bi bi-person-check me-1"></i> Class Teacher Review
            </a>
            <a href="student-leave-approval.php" class="btn btn-outline-success">
                <i class="bi bi-shield-check me-1"></i> Head Teacher Approval
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Application Form Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary bg-opacity-10 border-bottom">
                    <h5 class="card-title text-primary mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Submit New Application (নতুন আবেদন)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="leaveApplicationForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="submit_leave_application">
                        <input type="hidden" name="sccode" value="<?= $sccode ?>">

                        <!-- Student Search & Selection Mode -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lookup Student (শিক্ষার্থী নির্বাচন)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="number" class="form-control" name="lookup_stid" id="lookup_stid" placeholder="Enter Student ID (e.g. 1031871631)">
                                <button class="btn btn-primary" type="button" id="btnLookupStudent">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Find
                                </button>
                            </div>
                        </div>

                        <!-- Class, Section, Roll cascading -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Class (শ্রেণি)</label>
                                <select class="form-select form-select-sm" name="classname" id="app_classname" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Section (শাখা)</label>
                                <select class="form-select form-select-sm" name="sectionname" id="app_sectionname">
                                    <option value="">-- All / Section --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Roll (রোল / শিক্ষার্থী)</label>
                                <select class="form-select form-select-sm" name="rollno" id="app_rollno">
                                    <option value="">-- Select Roll --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Student Verified Card -->
                        <div id="studentInfoBox" class="alert alert-primary d-none py-2 px-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong id="info_stname" class="fs-6">Student Name</strong>
                                    <div class="small text-muted" id="info_stmeta">Class: Eight | Roll: 1 | ID: 1031871631</div>
                                </div>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Verified</span>
                            </div>
                        </div>
                        <input type="hidden" name="stid" id="app_stid" value="">

                        <!-- Leave Type (Advance / Post-absence / Sick) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Application Type (ছুটির ধরন)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="leave_type" id="type_advance" value="advance" checked>
                                    <label class="btn btn-outline-primary w-100 py-2 text-start" for="type_advance">
                                        <i class="bi bi-calendar-check me-1"></i> <strong>Advance Leave</strong>
                                        <div class="small text-muted">অগ্রিম ছুটির আবেদন</div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="leave_type" id="type_post_absence" value="post_absence">
                                    <label class="btn btn-outline-warning w-100 py-2 text-start" for="type_post_absence">
                                        <i class="bi bi-clipboard-pulse me-1"></i> <strong>Post Absence</strong>
                                        <div class="small text-muted">অনুপস্থিতির পর আবেদন</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Date From (শুরুর তারিখ)</label>
                                <input type="date" class="form-control" name="date_from" id="app_date_from" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Date To (শেষ তারিখ)</label>
                                <input type="date" class="form-control" name="date_to" id="app_date_to" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="small text-primary fw-semibold" id="durationDisplay">
                                    <i class="bi bi-info-circle me-1"></i> Total Duration: 1 Day(s)
                                </div>
                            </div>
                        </div>

                        <!-- Reason Textarea -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason for Leave (ছুটির কারণ ও বিস্তারিত)</label>
                            <textarea class="form-control" name="reason" id="app_reason" rows="3" placeholder="অসুস্থতা, জরুরি পারিবারিক কাজ বা অন্যান্য কারণ লিখুন..." required></textarea>
                        </div>

                        <!-- Supporting Document Attachment -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Supporting Document (ডাক্তারের সার্টিফিকেট / প্রত্যয়নপত্র - ঐচ্ছিক)</label>
                            <input type="file" class="form-control" name="attachment" id="app_attachment" accept="image/*,.pdf">
                            <div class="form-text small text-muted">Supports JPG, PNG, PDF formats (Max 5MB)</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="btnSubmitLeave">
                                <i class="bi bi-send-check me-2"></i> Submit Application (আবেদন জমা দিন)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2 text-secondary"></i> Recent Submissions (সাম্প্রতিক আবেদনসমূহ)
                    </h5>
                    <span class="badge bg-primary"><?= count($recentApps) ?> Total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#ID</th>
                                    <th>Student</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentApps)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                             <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            No leave applications submitted yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentApps as $app): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-secondary">#<?= $app['id'] ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($app['stname'] ?: "ID #{$app['stid']}") ?></div>
                                                <small class="text-muted">Cl: <?= htmlspecialchars($app['classname']) ?> | Roll: <?= $app['rollno'] ?></small>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold"><?= date('d M', strtotime($app['date_from'])) ?> - <?= date('d M', strtotime($app['date_to'])) ?></div>
                                                <span class="badge bg-light text-dark border"><?= $app['days'] ?> Day(s)</span>
                                            </td>
                                            <td>
                                                <?php if ($app['status'] === 'approved'): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> Approved</span>
                                                <?php elseif ($app['status'] === 'recommended'): ?>
                                                    <span class="badge bg-info"><i class="bi bi-person-check me-1"></i> Recommended</span>
                                                <?php elseif ($app['status'] === 'rejected_by_teacher'): ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected (Teacher)</span>
                                                <?php elseif ($app['status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger"><i class="bi bi-x-octagon me-1"></i> Rejected (Admin)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary btnViewLeaveDetails" 
                                                    data-id="<?= $app['id'] ?>"
                                                    data-student="<?= htmlspecialchars($app['stname']) ?>"
                                                    data-meta="Class: <?= htmlspecialchars($app['classname']) ?>, Roll: <?= $app['rollno'] ?>, ID: <?= $app['stid'] ?>"
                                                    data-dates="<?= $app['date_from'] ?> to <?= $app['date_to'] ?> (<?= $app['days'] ?> Days)"
                                                    data-type="<?= ucfirst(str_replace('_', ' ', $app['leave_type'])) ?>"
                                                    data-reason="<?= htmlspecialchars($app['reason']) ?>"
                                                    data-attachment="<?= $app['attachment'] ? htmlspecialchars($app['attachment']) : '' ?>"
                                                    data-status="<?= $app['status'] ?>"
                                                    data-recnotes="<?= htmlspecialchars($app['recommend_notes'] ?? '') ?>"
                                                    data-recby="<?= htmlspecialchars($app['recommended_by'] ?? '') ?>"
                                                    data-rejnotes="<?= htmlspecialchars($app['rejection_reason'] ?? $app['admin_notes'] ?? '') ?>"
                                                    data-appby="<?= htmlspecialchars($app['approved_by'] ?? '') ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
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
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="modalLeaveDetails" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-card-text me-2"></i> Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar bg-primary bg-opacity-10 text-primary p-3 rounded me-3">
                        <i class="bi bi-person fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold" id="m_stname">Student Name</h5>
                        <div class="text-muted small" id="m_stmeta">Class: - | Roll: -</div>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Leave Type:</strong> <span id="m_type" class="text-primary fw-semibold">Advance</span></div>
                        <div class="col-6"><strong>Duration:</strong> <span id="m_dates">-</span></div>
                        <div class="col-12 mt-2"><strong>Status:</strong> <span id="m_status_badge" class="badge bg-warning text-dark">Pending</span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold small text-muted">Reason for Leave (ছুটির কারণ):</label>
                    <p class="p-2 border rounded bg-white small mb-0" id="m_reason">-</p>
                </div>

                <div id="m_rec_box" class="mb-3 d-none">
                    <label class="fw-bold small text-info">Class Teacher Note (শিক্ষকের মন্তব্য):</label>
                    <p class="p-2 border border-info rounded bg-info bg-opacity-10 small mb-0" id="m_recnotes">-</p>
                </div>

                <div id="m_rej_box" class="mb-3 d-none">
                    <label class="fw-bold small text-danger">Rejection / Approval Reason (সিদ্ধান্তের কারণ):</label>
                    <p class="p-2 border border-danger rounded bg-danger bg-opacity-10 small mb-0" id="m_rejnotes">-</p>
                </div>

                <div id="m_attachment_box" class="d-none">
                    <label class="fw-bold small text-muted">Attached Document:</label>
                    <div>
                        <a href="#" target="_blank" id="m_attach_link" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-paperclip me-1"></i> View Attached Document
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromInput = document.getElementById('app_date_from');
    const toInput = document.getElementById('app_date_to');
    const durationDisplay = document.getElementById('durationDisplay');
    const classSelect = document.getElementById('app_classname');
    const secSelect = document.getElementById('app_sectionname');
    const rollSelect = document.getElementById('app_rollno');
    const stidInput = document.getElementById('app_stid');
    const lookupStid = document.getElementById('lookup_stid');
    const studentInfoBox = document.getElementById('studentInfoBox');
    const infoStname = document.getElementById('info_stname');
    const infoStmeta = document.getElementById('info_stmeta');

    function updateDuration() {
        if (!fromInput.value || !toInput.value) return;
        const d1 = new Date(fromInput.value);
        const d2 = new Date(toInput.value);
        if (d2 >= d1) {
            const diffTime = Math.abs(d2 - d1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            durationDisplay.innerHTML = `<i class="bi bi-info-circle me-1"></i> Total Duration: <strong>${diffDays} Day(s)</strong>`;
        } else {
            durationDisplay.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> End date cannot be before start date</span>`;
        }
    }

    fromInput.addEventListener('change', updateDuration);
    toInput.addEventListener('change', updateDuration);

    function showVerifiedStudent(st) {
        stidInput.value = st.stid;
        lookupStid.value = st.stid;
        infoStname.textContent = st.stname || `Student ID: ${st.stid}`;
        infoStmeta.textContent = `Class: ${st.classname} | Section: ${st.sectionname || 'All'} | Roll: ${st.rollno} | Guardian: ${st.guarmobile || 'N/A'}`;
        studentInfoBox.classList.remove('d-none');
    }

    function clearVerifiedStudent() {
        stidInput.value = '';
        studentInfoBox.classList.add('d-none');
    }

    // Load sections dynamically for a class
    function loadSections(className, selectedSec = '', callback = null) {
        secSelect.innerHTML = '<option value="">-- All / Section --</option>';
        if (!className) {
            if (callback) callback();
            return;
        }

        const fd = new FormData();
        fd.append('action', 'get_sections');
        fd.append('sccode', '<?= $sccode ?>');
        fd.append('classname', className);

        fetch('ajax/leave-actions.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.sections) && data.sections.length > 0) {
                data.sections.forEach(sec => {
                    const opt = document.createElement('option');
                    opt.value = sec;
                    opt.textContent = sec;
                    if (selectedSec && sec.toLowerCase() === selectedSec.toLowerCase()) {
                        opt.selected = true;
                    }
                    secSelect.appendChild(opt);
                });
            }
            if (callback) callback();
        })
        .catch(err => {
            console.error('Failed to load sections:', err);
            if (callback) callback();
        });
    }

    // Load rolls / students dynamically for Class & Section
    function loadStudents(className, sectionName = '', selectedRoll = '', callback = null) {
        rollSelect.innerHTML = '<option value="">Loading students...</option>';
        if (!className) {
            rollSelect.innerHTML = '<option value="">-- Select Roll --</option>';
            if (callback) callback([]);
            return;
        }

        const fd = new FormData();
        fd.append('action', 'get_students_list');
        fd.append('sccode', '<?= $sccode ?>');
        fd.append('classname', className);
        fd.append('sectionname', sectionName);

        fetch('ajax/leave-actions.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            rollSelect.innerHTML = '<option value="">-- Select Roll --</option>';
            let foundMatch = null;
            if (data.status === 'success' && Array.isArray(data.students) && data.students.length > 0) {
                data.students.forEach(st => {
                    const opt = document.createElement('option');
                    opt.value = st.rollno;
                    opt.textContent = `Roll ${st.rollno} - ${st.stname || ('ID #' + st.stid)}`;
                    opt.dataset.stid = st.stid;
                    opt.dataset.stname = st.stname || '';
                    opt.dataset.classname = st.classname;
                    opt.dataset.sectionname = st.sectionname || '';
                    opt.dataset.rollno = st.rollno;
                    opt.dataset.guarmobile = st.guarmobile || '';

                    if (selectedRoll && String(st.rollno) === String(selectedRoll)) {
                        opt.selected = true;
                        foundMatch = st;
                    }
                    rollSelect.appendChild(opt);
                });
            } else {
                rollSelect.innerHTML = '<option value="">-- No students found --</option>';
            }

            if (foundMatch) {
                showVerifiedStudent(foundMatch);
            }
            if (callback) callback(data.students || []);
        })
        .catch(err => {
            console.error('Failed to load students:', err);
            rollSelect.innerHTML = '<option value="">-- Select Roll --</option>';
            if (callback) callback([]);
        });
    }

    // Class selection change event
    classSelect.addEventListener('change', function() {
        clearVerifiedStudent();
        loadSections(this.value, '', () => {
            loadStudents(this.value, '');
        });
    });

    // Section selection change event
    secSelect.addEventListener('change', function() {
        clearVerifiedStudent();
        loadStudents(classSelect.value, this.value);
    });

    // Roll selection change event
    rollSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt && selectedOpt.dataset.stid) {
            showVerifiedStudent({
                stid: selectedOpt.dataset.stid,
                stname: selectedOpt.dataset.stname,
                classname: selectedOpt.dataset.classname,
                sectionname: selectedOpt.dataset.sectionname,
                rollno: selectedOpt.dataset.rollno,
                guarmobile: selectedOpt.dataset.guarmobile
            });
        } else {
            clearVerifiedStudent();
        }
    });

    // Student Lookup handler
    function lookupStudent() {
        const stid = lookupStid.value.trim();
        const cName = classSelect.value.trim();
        const secName = secSelect.value.trim();
        const roll = rollSelect.value.trim();

        if (!stid && (!cName || !roll)) {
            Swal.fire({ icon: 'warning', title: 'Input Required', text: 'Please enter Student ID or select Class and Roll Number.' });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'get_student_info');
        formData.append('sccode', '<?= $sccode ?>');
        formData.append('stid', stid);
        formData.append('classname', cName);
        formData.append('sectionname', secName);
        formData.append('rollno', roll);

        fetch('ajax/leave-actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const s = data.student;
                classSelect.value = s.classname;
                loadSections(s.classname, s.sectionname || '', () => {
                    loadStudents(s.classname, s.sectionname || '', s.rollno, () => {
                        showVerifiedStudent(s);
                    });
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Not Found', text: data.message });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to lookup student details.' });
        });
    }

    document.getElementById('btnLookupStudent').addEventListener('click', lookupStudent);
    lookupStid.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupStudent();
        }
    });

    // Form Submission Handler
    document.getElementById('leaveApplicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const stid = document.getElementById('app_stid').value;
        if (!stid) {
            Swal.fire({ icon: 'warning', title: 'Student Required', text: 'Please find and verify the student before submitting.' });
            return;
        }

        const submitBtn = document.getElementById('btnSubmitLeave');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Submitting...`;

        const formData = new FormData(this);

        fetch('ajax/leave-actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="bi bi-send-check me-2"></i> Submit Application (আবেদন জমা দিন)`;
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Application Submitted!',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Submission Failed', text: data.message });
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="bi bi-send-check me-2"></i> Submit Application (আবেদন জমা দিন)`;
            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
        });
    });

    // View Details Modal Trigger
    document.querySelectorAll('.btnViewLeaveDetails').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('m_stname').textContent = this.dataset.student || 'Student';
            document.getElementById('m_stmeta').textContent = this.dataset.meta;
            document.getElementById('m_type').textContent = this.dataset.type;
            document.getElementById('m_dates').textContent = this.dataset.dates;
            document.getElementById('m_reason').textContent = this.dataset.reason || 'No reason provided';

            const status = this.dataset.status;
            let statusBadge = `<span class="badge bg-warning text-dark">Pending</span>`;
            if (status === 'approved') statusBadge = `<span class="badge bg-success">Approved</span>`;
            if (status === 'recommended') statusBadge = `<span class="badge bg-info">Recommended</span>`;
            if (status === 'rejected_by_teacher') statusBadge = `<span class="badge bg-danger">Rejected by Teacher</span>`;
            if (status === 'rejected') statusBadge = `<span class="badge bg-danger">Rejected by Admin</span>`;
            document.getElementById('m_status_badge').innerHTML = statusBadge;

            if (this.dataset.recnotes) {
                document.getElementById('m_recnotes').textContent = `${this.dataset.recnotes} (By: ${this.dataset.recby})`;
                document.getElementById('m_rec_box').classList.remove('d-none');
            } else {
                document.getElementById('m_rec_box').classList.add('d-none');
            }

            if (this.dataset.rejnotes) {
                document.getElementById('m_rejnotes').textContent = `${this.dataset.rejnotes} (By: ${this.dataset.appby})`;
                document.getElementById('m_rej_box').classList.remove('d-none');
            } else {
                document.getElementById('m_rej_box').classList.add('d-none');
            }

            if (this.dataset.attachment) {
                document.getElementById('m_attach_link').href = this.dataset.attachment;
                document.getElementById('m_attachment_box').classList.remove('d-none');
            } else {
                document.getElementById('m_attachment_box').classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('modalLeaveDetails'));
            modal.show();
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
