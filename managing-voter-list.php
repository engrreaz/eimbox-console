<?php 
require_once 'header.php'; 

$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? date('Y');
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

?>

<style>
    .voter-kpi-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .voter-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .action-btn-custom {
        padding: 0.6rem 1.1rem;
        font-weight: 500;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    #studentDetailModal {
        z-index: 1085 !important;
    }
    .modal-backdrop.student-detail-backdrop {
        z-index: 1080 !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Banner -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-primary">
                        <i class="bi bi-person-check-fill me-2"></i>Managing Committee Election &mdash; Guardian Electoral Roll Management
                    </h4>
                    <p class="text-muted mb-0">
                        Profile auditing, sibling clustering, and automated voter number assignment for active students in <strong>Class Six to Twelve</strong> (Session matching <strong><?= htmlspecialchars($sessionyear) ?></strong>).
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="voter-master-list.php" class="btn btn-outline-primary action-btn-custom">
                        <i class="bi bi-file-earmark-text"></i> Master Voter List
                    </a>
                    <button class="btn btn-primary action-btn-custom" onclick="generateVoterList()">
                        <i class="bi bi-lightning-charge-fill"></i> Generate Voter Numbers
                    </button>
                    <button class="btn btn-outline-danger action-btn-custom" onclick="resetVoterList()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics / Health Check KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Students -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card voter-kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Active Students</span>
                        <h3 class="mb-0 mt-1 fw-bold text-dark" id="kpi-total-students"><i class="bi bi-arrow-repeat spin"></i></h3>
                        <small class="text-muted" id="kpi-session-txt">Session: <?= htmlspecialchars($sessionyear) ?></small>
                    </div>
                    <div class="kpi-icon-box bg-label-primary text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: NID Coverage & Check Button -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card voter-kpi-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Guardian NID Coverage</span>
                            <h3 class="mb-0 mt-1 fw-bold text-success" id="kpi-nid-rate"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-success text-success">
                            <i class="bi bi-card-text"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-nid-sub">Missing NID: <strong id="kpi-nid-missing">0</strong></span>
                        <button class="btn btn-sm btn-outline-success py-1 px-2" onclick="openNidAuditModal()">
                            <i class="bi bi-search me-1"></i> Check NID
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Mobile Coverage & Check Button -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card voter-kpi-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Mobile No. Coverage</span>
                            <h3 class="mb-0 mt-1 fw-bold text-info" id="kpi-mobile-rate"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-info text-info">
                            <i class="bi bi-phone"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-mobile-sub">Missing Mobile: <strong id="kpi-mobile-missing">0</strong></span>
                        <button class="btn btn-sm btn-outline-info py-1 px-2" onclick="openMobileAuditModal()">
                            <i class="bi bi-search me-1"></i> Check Mobile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Sibling Groups Detected -->
        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card voter-kpi-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Detected Sibling Clusters</span>
                            <h3 class="mb-0 mt-1 fw-bold text-warning" id="kpi-sibling-clusters"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-warning text-warning">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Families with multiple enrolled children</span>
                        <button class="btn btn-sm btn-outline-warning py-1 px-2" onclick="openSiblingPreviewModal()">
                            <i class="bi bi-eye me-1"></i> Preview Siblings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Unique Voters Generated -->
        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card voter-kpi-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Total Unique Voters</span>
                            <h3 class="mb-0 mt-1 fw-bold text-primary" id="kpi-unique-voters"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-primary text-primary">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-voter-assigned-sub">Sequenced hierarchically from Class Six</span>
                        <a href="voter-master-list.php" class="btn btn-sm btn-primary py-1 px-2">
                            <i class="bi bi-printer me-1"></i> Print Master Roll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section/Class Cascade Tree Selection UI Component -->
    <?php
    $chain_param = '-c 12 -t "Select Voter List Criteria (Class / Section Wise)" -b "View Voter List"';
    include 'components/slot-tree-ui.php';
    ?>
</div>

<!-- Modal 1: NID Audit Modal -->
<div class="modal fade" id="nidAuditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-label-success">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-card-checklist me-2"></i>Parents' NID Analysis & Audit Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-siblings">
                            <i class="bi bi-people me-1"></i> Sibling Clusters (<span id="count-nid-siblings">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-invalid">
                            <i class="bi bi-exclamation-octagon me-1"></i> Invalid Format NID (<span id="count-nid-invalid">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-missing">
                            <i class="bi bi-slash-circle me-1"></i> Missing NID (<span id="count-nid-missing">0</span>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="tab-nid-siblings">
                        <div id="nid-siblings-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-nid-invalid">
                        <div id="nid-invalid-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-nid-missing">
                        <div id="nid-missing-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Mobile Audit Modal -->
<div class="modal fade" id="mobileAuditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-label-info">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-phone-vibrate me-2"></i>Mobile Number Audit & Sibling Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-siblings">
                            <i class="bi bi-phone me-1"></i> Common Mobile Groups (<span id="count-mob-siblings">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-invalid">
                            <i class="bi bi-exclamation-triangle me-1"></i> Invalid Numbers (<span id="count-mob-invalid">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-missing">
                            <i class="bi bi-x-circle me-1"></i> Missing Mobile (<span id="count-mob-missing">0</span>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="tab-mob-siblings">
                        <div id="mob-siblings-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-mob-invalid">
                        <div id="mob-invalid-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-mob-missing">
                        <div id="mob-missing-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Sibling Preview Modal -->
<div class="modal fade" id="siblingPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-label-warning">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-diagram-3 me-2"></i>Detected Sibling Clusters Preview (Multi-Student Families)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-info-circle me-1"></i> Siblings have been matched using Parents' NID and Mobile numbers. Click on any <strong>Student ID</strong> to inspect full student profile, address, parents' info and all session records.
                </div>
                <div id="sibling-preview-table-container">
                    <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> Loading...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="generateVoterList()">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Confirm & Generate Voter Numbers
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Student Complete Detail Profile Popup -->
<div class="modal fade" id="studentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center">
                    <i class="bi bi-person-lines-fill me-2 fs-5"></i> Student Identification & Profile Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="student-detail-modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Loading student profile...</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <a id="student-full-profile-btn" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Profile
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        window.location.href = 'voter-info.php';
    }

    // Handle nested modal z-index stacking and scrolling
    $(document).on('show.bs.modal', '#studentDetailModal', function () {
        var baseZ = 1080;
        $(this).css('z-index', baseZ + 10);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').last().css('z-index', baseZ + 5).addClass('modal-stack student-detail-backdrop');
        }, 10);
    });

    $(document).on('hidden.bs.modal', '#studentDetailModal', function () {
        if ($('.modal.show').length > 0) {
            $('body').addClass('modal-open');
        }
    });

    // Load Overview Analytics on page load
    $(document).ready(function () {
        loadVoterSummary();
    });

    function loadVoterSummary() {
        $.ajax({
            url: 'backend/voter-analysis.php',
            method: 'GET',
            data: { action: 'get_summary' },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    $('#kpi-total-students').text(d.total_students);
                    
                    var nidPercent = d.total_students > 0 ? Math.round((d.with_any_nid / d.total_students) * 100) : 0;
                    $('#kpi-nid-rate').text(d.with_any_nid + ' (' + nidPercent + '%)');
                    $('#kpi-nid-missing').text(d.missing_nid);

                    var mobPercent = d.total_students > 0 ? Math.round((d.with_any_mobile / d.total_students) * 100) : 0;
                    $('#kpi-mobile-rate').text(d.with_any_mobile + ' (' + mobPercent + '%)');
                    $('#kpi-mobile-missing').text(d.missing_mobile);

                    $('#kpi-sibling-clusters').text(d.sibling_clusters_by_nid > 0 ? d.sibling_clusters_by_nid + ' Families' : (d.sibling_clusters_by_mobile + ' Families'));
                    $('#kpi-unique-voters').text(d.total_unique_voters > 0 ? d.total_unique_voters + ' Voters' : 'Not Generated');
                }
            },
            error: function () {
                $('#kpi-total-students').text('—');
            }
        });
    }

    // Function to view full student details popup
    function viewStudentDetails(stid) {
        if (!stid) return;
        var modalEl = document.getElementById('studentDetailModal');
        var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        
        $('#student-detail-modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted small">Loading student profile...</div></div>');
        $('#student-full-profile-btn').attr('href', 'student-view-profile.php?stid=' + encodeURIComponent(stid));
        myModal.show();

        $.ajax({
            url: 'backend/voter-analysis.php',
            method: 'GET',
            data: { action: 'get_student_detail', stid: stid },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    var s = res.data.student;
                    var sessions = res.data.sessions || [];
                    var photo = res.data.photo_url || 'assets/images/user.png';

                    var html = `
                        <!-- Student Hero Card -->
                        <div class="card bg-label-primary border-0 mb-3 shadow-none">
                            <div class="card-body p-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto">
                                        <div class="border rounded p-1 bg-white shadow-sm" style="width: 100px; height: 115px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                            <img src="${photo}" onerror="this.src='assets/images/user.png'" alt="Student Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                            <h5 class="fw-bold mb-0 text-dark">${s.stnameeng || '—'}</h5>
                                            ${s.stnameben ? `<span class="text-muted small">(${s.stnameben})</span>` : ''}
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="badge bg-primary"><i class="bi bi-person-badge me-1"></i>ID: ${s.stid}</span>
                                            <span class="badge bg-dark">Gender: ${s.gender || '—'}</span>
                                            <span class="badge bg-secondary">Blood: ${s.bgroup || '—'}</span>
                                            <span class="badge bg-info">Religion: ${s.religion || '—'}</span>
                                            <span class="badge bg-warning text-dark">DOB: ${s.dob || '—'}</span>
                                            ${s.brn ? `<span class="badge bg-label-primary">BRN: ${s.brn}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parents & Guardian Information -->
                        <div class="card border mb-3 shadow-sm">
                            <div class="card-header bg-light py-2 px-3 fw-bold small text-uppercase text-primary d-flex align-items-center">
                                <i class="bi bi-people-fill me-2"></i> Parents & Guardian Information
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <!-- Father Info -->
                                    <div class="col-12 col-md-6 border-end-md">
                                        <h6 class="fw-bold text-primary mb-2 small"><i class="bi bi-person-fill me-1"></i>Father's Information</h6>
                                        <table class="table table-sm table-borderless mb-0 small">
                                            <tr><td class="text-muted" style="width: 35%;">Name:</td><td class="fw-semibold">${s.fname || '—'} ${s.fnameben ? `(${s.fnameben})` : ''}</td></tr>
                                            <tr><td class="text-muted">NID:</td><td class="fw-bold text-success">${s.fnid || '<span class="badge bg-label-danger">Missing</span>'}</td></tr>
                                            <tr><td class="text-muted">Mobile:</td><td class="fw-bold text-info">${s.fmobile || '<span class="badge bg-label-secondary">—</span>'}</td></tr>
                                            <tr><td class="text-muted">Profession:</td><td>${s.fprof || '—'}</td></tr>
                                        </table>
                                    </div>
                                    <!-- Mother Info -->
                                    <div class="col-12 col-md-6">
                                        <h6 class="fw-bold text-info mb-2 small"><i class="bi bi-person-heart me-1"></i>Mother's Information</h6>
                                        <table class="table table-sm table-borderless mb-0 small">
                                            <tr><td class="text-muted" style="width: 35%;">Name:</td><td class="fw-semibold">${s.mname || '—'} ${s.mnameben ? `(${s.mnameben})` : ''}</td></tr>
                                            <tr><td class="text-muted">NID:</td><td class="fw-bold text-success">${s.mnid || '<span class="badge bg-label-danger">Missing</span>'}</td></tr>
                                            <tr><td class="text-muted">Mobile:</td><td class="fw-bold text-info">${s.mmobile || '<span class="badge bg-label-secondary">—</span>'}</td></tr>
                                            <tr><td class="text-muted">Profession:</td><td>${s.mprof || '—'}</td></tr>
                                        </table>
                                    </div>
                                    ${ (s.guarname || s.guarmobile || s.guarnid) ? `
                                    <div class="col-12 border-top pt-2">
                                        <h6 class="fw-bold text-secondary mb-1 small"><i class="bi bi-shield-check me-1"></i>Guardian (if other than parents):</h6>
                                        <div class="small d-flex flex-wrap gap-3">
                                            <span><strong>Name:</strong> ${s.guarname || '—'} (${s.guarrelation || 'Guardian'})</span>
                                            <span><strong>Mobile:</strong> ${s.guarmobile || '—'}</span>
                                            <span><strong>NID:</strong> ${s.guarnid || '—'}</span>
                                        </div>
                                    </div>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Address Details -->
                        <div class="card border mb-3 shadow-sm">
                            <div class="card-header bg-light py-2 px-3 fw-bold small text-uppercase text-secondary d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill me-2"></i> Address & Village Details
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 small">
                                    <div class="col-12 col-md-6">
                                        <strong class="text-primary d-block mb-1"><i class="bi bi-house-door me-1"></i>Present Address:</strong>
                                        <div class="p-2 bg-light rounded">
                                            <div><strong>Village (previll):</strong> <span class="fw-semibold text-dark">${s.previll || '—'}</span></div>
                                            <div><strong>Post:</strong> ${s.prepo || '—'}, <strong>Thana:</strong> ${s.preps || '—'}, <strong>Dist:</strong> ${s.predist || '—'}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <strong class="text-info d-block mb-1"><i class="bi bi-building me-1"></i>Permanent Address:</strong>
                                        <div class="p-2 bg-light rounded">
                                            <div><strong>Village (pervill):</strong> <span class="fw-semibold text-dark">${s.pervill || '—'}</span></div>
                                            <div><strong>Post:</strong> ${s.perpo || '—'}, <strong>Thana:</strong> ${s.perps || '—'}, <strong>Dist:</strong> ${s.perdist || '—'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic / Session History from sessioninfo -->
                        <div class="card border shadow-sm">
                            <div class="card-header bg-light py-2 px-3 fw-bold small text-uppercase text-dark d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-mortarboard-fill me-2 text-primary"></i> Academic & Session Enrollment History (sessioninfo)</span>
                                <span class="badge bg-primary">${sessions.length} Recorded Session(s)</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover mb-0 small align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Session Year</th>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th class="text-center">Roll No</th>
                                            <th>Slot / Branch</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Voter No</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${sessions.length > 0 ? sessions.map(function(sess) {
                                            return `
                                                <tr>
                                                    <td class="fw-bold text-primary">${sess.sessionyear}</td>
                                                    <td><strong>${sess.classname}</strong></td>
                                                    <td>${sess.sectionname}</td>
                                                    <td class="text-center fw-bold text-dark">${sess.rollno}</td>
                                                    <td>${sess.slot || 'Default'}</td>
                                                    <td class="text-center">${sess.status == 1 ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>'}</td>
                                                    <td class="text-center">${sess.voter_no > 0 ? `<span class="badge bg-primary">#${sess.voter_no}</span>` : '<span class="text-muted">—</span>'}</td>
                                                </tr>
                                            `;
                                        }).join('') : `<tr><td colspan="7" class="text-center py-3 text-muted">No session records found.</td></tr>`}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    $('#student-detail-modal-body').html(html);
                } else {
                    $('#student-detail-modal-body').html('<div class="alert alert-danger my-3"><i class="bi bi-exclamation-triangle me-1"></i> ' + (res.message || 'Error loading student details.') + '</div>');
                }
            },
            error: function() {
                $('#student-detail-modal-body').html('<div class="alert alert-danger my-3"><i class="bi bi-exclamation-triangle me-1"></i> Failed to connect to server.</div>');
            }
        });
    }

    function openNidAuditModal() {
        var myModal = new bootstrap.Modal(document.getElementById('nidAuditModal'));
        myModal.show();
        
        $.ajax({
            url: 'backend/voter-analysis.php',
            method: 'GET',
            data: { action: 'check_nid' },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    $('#count-nid-siblings').text(d.multiple_nid_groups.length);
                    $('#count-nid-invalid').text(d.invalid_nid_list.length);
                    $('#count-nid-missing').text(d.missing_nid_list.length);

                    // Siblings tab
                    if (d.multiple_nid_groups.length > 0) {
                        var h = '<div class="table-responsive"><table class="table table-bordered table-hover table-sm align-middle">';
                        h += '<thead class="table-light"><tr><th>NID & Guardian</th><th>Village (Present/Permanent)</th><th>Children Count</th><th>Students Details (Click ID for popup)</th></tr></thead><tbody>';
                        d.multiple_nid_groups.forEach(function(g) {
                            var gVill = g.previll || g.pervill || '—';
                            h += '<tr>';
                            h += '<td><strong>' + g.nid + '</strong><br/><small class="text-muted">' + g.guardian_name + '</small></td>';
                            h += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + gVill + '</small></td>';
                            h += '<td class="text-center"><span class="badge bg-label-primary">' + g.count + ' Students</span></td>';
                            h += '<td>';
                            g.students.forEach(function(st, idx) {
                                var sVill = st.previll || st.pervill || '—';
                                h += '<div class="small mb-1 p-1 rounded bg-light d-flex justify-content-between align-items-center flex-wrap gap-1">';
                                h += '<span>' + (idx+1) + '. <strong>' + st.name + '</strong> <a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a> &mdash; ' + st.class + ' (Sec: ' + st.section + ', Roll: ' + st.roll + ') <span class="badge bg-label-info py-0 px-1">Session: ' + (st.sessionyear || '—') + '</span></span>';
                                h += '<small class="text-muted"><i class="bi bi-geo-alt"></i> ' + sVill + '</small>';
                                h += '</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#nid-siblings-content').html(h);
                    } else {
                        $('#nid-siblings-content').html('<div class="alert alert-secondary text-center my-3">No NID-matched sibling clusters found.</div>');
                    }

                    // Invalid NID
                    if (d.invalid_nid_list.length > 0) {
                        var h2 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h2 += '<thead class="table-light"><tr><th>Student ID & Name</th><th>Class, Roll & Session</th><th>Village</th><th>NID Type</th><th>Given NID (Invalid)</th></tr></thead><tbody>';
                        d.invalid_nid_list.forEach(function(st) {
                            var sVill = st.previll || st.pervill || '—';
                            h2 += '<tr>';
                            h2 += '<td><strong>' + st.name + '</strong><br/><a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a></td>';
                            h2 += '<td>' + st.class + ' (Roll: ' + st.roll + ')<br/><small class="text-muted">Session: ' + (st.sessionyear || '—') + '</small></td>';
                            h2 += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + sVill + '</small></td>';
                            h2 += '<td>' + st.type + '</td>';
                            h2 += '<td class="text-danger fw-bold">' + st.raw_nid + '</td>';
                            h2 += '</tr>';
                        });
                        h2 += '</tbody></table></div>';
                        $('#nid-invalid-content').html(h2);
                    } else {
                        $('#nid-invalid-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> All recorded NIDs are formatted correctly.</div>');
                    }

                    // Missing NID
                    if (d.missing_nid_list.length > 0) {
                        var h3 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h3 += '<thead class="table-light"><tr><th>Student ID & Name</th><th>Class, Sec, Roll & Session</th><th>Village</th><th>Parents</th><th>Mobile</th></tr></thead><tbody>';
                        d.missing_nid_list.forEach(function(st) {
                            var sVill = st.previll || st.pervill || '—';
                            h3 += '<tr>';
                            h3 += '<td><strong>' + st.name + '</strong><br/><a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a></td>';
                            h3 += '<td>' + st.class + ' (Sec: ' + st.section + ', Roll: ' + st.roll + ')<br/><span class="badge bg-label-secondary py-0 px-1">Session: ' + (st.sessionyear || '—') + '</span></td>';
                            h3 += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + sVill + '</small></td>';
                            h3 += '<td>F: ' + (st.father || '—') + '<br/>M: ' + (st.mother || '—') + '</td>';
                            h3 += '<td>' + (st.mobile || '—') + '</td>';
                            h3 += '</tr>';
                        });
                        h3 += '</tbody></table></div>';
                        $('#nid-missing-content').html(h3);
                    } else {
                        $('#nid-missing-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> All students have valid parent NID on record.</div>');
                    }
                }
            }
        });
    }

    function openMobileAuditModal() {
        var myModal = new bootstrap.Modal(document.getElementById('mobileAuditModal'));
        myModal.show();

        $.ajax({
            url: 'backend/voter-analysis.php',
            method: 'GET',
            data: { action: 'check_mobile' },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    $('#count-mob-siblings').text(d.multiple_mobile_groups.length);
                    $('#count-mob-invalid').text(d.invalid_mobile_list.length);
                    $('#count-mob-missing').text(d.missing_mobile_list.length);

                    // Multiple Mobile Groups
                    if (d.multiple_mobile_groups.length > 0) {
                        var h = '<div class="table-responsive"><table class="table table-bordered table-hover table-sm align-middle">';
                        h += '<thead class="table-light"><tr><th>Mobile & Guardian</th><th>Village</th><th>Children Count</th><th>Students Details (Click ID for popup)</th></tr></thead><tbody>';
                        d.multiple_mobile_groups.forEach(function(g) {
                            var gVill = g.previll || g.pervill || g.village || '—';
                            h += '<tr>';
                            h += '<td><strong>' + g.phone + '</strong><br/><small class="text-muted">' + (g.father || '') + '</small></td>';
                            h += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + gVill + '</small></td>';
                            h += '<td class="text-center"><span class="badge bg-label-info">' + g.count + ' Students</span></td>';
                            h += '<td>';
                            g.students.forEach(function(st, idx) {
                                var sVill = st.previll || st.pervill || '—';
                                h += '<div class="small mb-1 p-1 rounded bg-light d-flex justify-content-between align-items-center flex-wrap gap-1">';
                                h += '<span>' + (idx+1) + '. <strong>' + st.name + '</strong> <a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a> &mdash; ' + st.class + ' (Sec: ' + st.section + ', Roll: ' + st.roll + ') <span class="badge bg-label-info py-0 px-1">Session: ' + (st.sessionyear || '—') + '</span></span>';
                                h += '<small class="text-muted"><i class="bi bi-geo-alt"></i> ' + sVill + '</small>';
                                h += '</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#mob-siblings-content').html(h);
                    } else {
                        $('#mob-siblings-content').html('<div class="alert alert-secondary text-center my-3">No common mobile sibling groups found.</div>');
                    }

                    // Invalid mobile
                    if (d.invalid_mobile_list.length > 0) {
                        var h2 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h2 += '<thead class="table-light"><tr><th>Student ID & Name</th><th>Class, Roll & Session</th><th>Village</th><th>Mobile No. (Invalid)</th></tr></thead><tbody>';
                        d.invalid_mobile_list.forEach(function(st) {
                            var sVill = st.previll || st.pervill || '—';
                            h2 += '<tr>';
                            h2 += '<td><strong>' + st.name + '</strong><br/><a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a></td>';
                            h2 += '<td>' + st.class + ' (Roll: ' + st.roll + ')<br/><small class="text-muted">Session: ' + (st.sessionyear || '—') + '</small></td>';
                            h2 += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + sVill + '</small></td>';
                            h2 += '<td class="text-danger fw-bold">' + st.raw_mobile + '</td>';
                            h2 += '</tr>';
                        });
                        h2 += '</tbody></table></div>';
                        $('#mob-invalid-content').html(h2);
                    } else {
                        $('#mob-invalid-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> All mobile numbers are valid.</div>');
                    }

                    // Missing mobile
                    if (d.missing_mobile_list.length > 0) {
                        var h3 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h3 += '<thead class="table-light"><tr><th>Student ID & Name</th><th>Class, Sec, Roll & Session</th><th>Village</th><th>Father</th></tr></thead><tbody>';
                        d.missing_mobile_list.forEach(function(st) {
                            var sVill = st.previll || st.pervill || '—';
                            h3 += '<tr>';
                            h3 += '<td><strong>' + st.name + '</strong><br/><a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + st.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + st.stid + '</a></td>';
                            h3 += '<td>' + st.class + ' (Sec: ' + st.section + ', Roll: ' + st.roll + ')<br/><span class="badge bg-label-secondary py-0 px-1">Session: ' + (st.sessionyear || '—') + '</span></td>';
                            h3 += '<td><small class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + sVill + '</small></td>';
                            h3 += '<td>' + (st.father || '—') + '</td>';
                            h3 += '</tr>';
                        });
                        h3 += '</tbody></table></div>';
                        $('#mob-missing-content').html(h3);
                    } else {
                        $('#mob-missing-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> All students have guardian mobile on record.</div>');
                    }
                }
            }
        });
    }

    function openSiblingPreviewModal() {
        var myModal = new bootstrap.Modal(document.getElementById('siblingPreviewModal'));
        myModal.show();

        $.ajax({
            url: 'backend/voter-analysis.php',
            method: 'GET',
            data: { action: 'preview_siblings' },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var list = res.data.sibling_groups;
                    if (list.length > 0) {
                        var h = '<div class="table-responsive"><table class="table table-bordered table-hover align-middle">';
                        h += '<thead class="table-light"><tr><th style="width: 50px;">SL</th><th>Guardian Name & Village</th><th>NID & Mobile</th><th>Sibling Students / Children (Click ID for details)</th></tr></thead><tbody>';
                        list.forEach(function(g, idx) {
                            var gVill = g.previll || g.pervill || g.village || '—';
                            h += '<tr>';
                            h += '<td class="text-center fw-bold">' + (idx+1) + '</td>';
                            h += '<td><strong>' + (g.guardian_name || '—') + '</strong><br/><small class="text-muted"><i class="bi bi-geo-alt"></i> ' + gVill + '</small></td>';
                            h += '<td><small>NID: ' + (g.nid || '—') + '</small><br/><small>Mob: ' + (g.mobile || '—') + '</small></td>';
                            h += '<td>';
                            g.children.forEach(function(c, cidx) {
                                var cVill = c.previll || c.pervill || '—';
                                h += '<div class="p-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">';
                                h += '<span>' + (cidx+1) + '. <strong>' + c.name + '</strong> <a href="javascript:void(0)" class="badge bg-primary text-decoration-none" onclick="viewStudentDetails(\'' + c.stid + '\')" title="Click to view full details"><i class="bi bi-person-badge me-1"></i>ID: ' + c.stid + '</a> &mdash; ' + c.class + ' (' + c.section + '), Roll: <strong>' + c.roll + '</strong>, <span class="badge bg-label-info py-0 px-1">Session: ' + (c.sessionyear || '—') + '</span> <small class="text-muted ms-1"><i class="bi bi-geo-alt"></i> ' + cVill + '</small></span>';
                                if (c.current_voter_no > 0) {
                                    h += '<span class="badge bg-primary">Voter No: ' + c.current_voter_no + '</span>';
                                }
                                h += '</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#sibling-preview-table-container').html(h);
                    } else {
                        $('#sibling-preview-table-container').html('<div class="alert alert-secondary text-center my-4">No sibling clusters detected.</div>');
                    }
                }
            }
        });
    }

    function generateVoterList() {
        if (!confirm('Are you sure you want to generate sequential voter numbers for all active students starting hierarchically from Class Six?')) {
            return;
        }

        $.ajax({
            url: 'backend/generate-voter-numbers.php',
            method: 'POST',
            data: { action: 'generate' },
            dataType: 'json',
            beforeSend: function () {
                showToast('info', 'Generating voter numbers, please wait...', 'Processing');
            },
            success: function (res) {
                if (res.status === 'success') {
                    showToast('success', res.message, 'Success');
                    loadVoterSummary();
                    
                    // Hide any opened modal
                    var sibModalEl = document.getElementById('siblingPreviewModal');
                    var sibModal = bootstrap.Modal.getInstance(sibModalEl);
                    if (sibModal) sibModal.hide();
                } else {
                    showToast('danger', res.message || 'Failed to generate voter numbers.', 'Error');
                }
            },
            error: function () {
                showToast('danger', 'Unable to connect to server.', 'Server Error');
            }
        });
    }

    function resetVoterList() {
        if (!confirm('Warning: Are you sure you want to reset (clear) all voter numbers for this academic session?')) {
            return;
        }

        $.ajax({
            url: 'backend/generate-voter-numbers.php',
            method: 'POST',
            data: { action: 'reset' },
            dataType: 'json',
            beforeSend: function () {
                showToast('warning', 'Resetting voter numbers...', 'Resetting');
            },
            success: function (res) {
                if (res.status === 'success') {
                    showToast('success', res.message, 'Success');
                    loadVoterSummary();
                } else {
                    showToast('danger', res.message || 'Failed to reset voter numbers.', 'Error');
                }
            },
            error: function () {
                showToast('danger', 'Unable to connect to server.', 'Server Error');
            }
        });
    }
</script>