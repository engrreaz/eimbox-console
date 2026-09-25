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
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header Banner -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-primary">
                        <i class="bi bi-person-check-fill me-2"></i>ম্যানেজিং কমিটি নির্বাচন — অভিভাবক ভোটার তালিকা কন্ট্রোল প্যানেল
                    </h4>
                    <p class="text-muted mb-0">
                        চলমান সেশন <strong><?= htmlspecialchars($sessionyear) ?></strong> এর সকল শিক্ষার্থীর প্রোফাইল বিশ্লেষণ, সিবলিং ক্লাস্টারিং এবং ভোটার নম্বর ব্যবস্থাপনা।
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="voter-master-list.php" class="btn btn-outline-primary action-btn-custom">
                        <i class="bi bi-file-earmark-text"></i> মাস্টার ভোটার তালিকা দেখুন
                    </a>
                    <button class="btn btn-primary action-btn-custom" onclick="generateVoterList()">
                        <i class="bi bi-lightning-charge-fill"></i> ভোটার নম্বর জেনারেট করুন
                    </button>
                    <button class="btn btn-outline-danger action-btn-custom" onclick="resetVoterList()">
                        <i class="bi bi-arrow-counterclockwise"></i> রিসেট
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
                        <span class="text-muted small fw-semibold text-uppercase">মোট সক্রিয় শিক্ষার্থী</span>
                        <h3 class="mb-0 mt-1 fw-bold text-dark" id="kpi-total-students"><i class="bi bi-arrow-repeat spin"></i></h3>
                        <small class="text-muted" id="kpi-session-txt">সেশন: <?= htmlspecialchars($sessionyear) ?></small>
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
                            <span class="text-muted small fw-semibold text-uppercase">অভিভাবক NID কভারেজ</span>
                            <h3 class="mb-0 mt-1 fw-bold text-success" id="kpi-nid-rate"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-success text-success">
                            <i class="bi bi-card-text"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-nid-sub">NID অনুপস্থিত: <strong id="kpi-nid-missing">0</strong></span>
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
                            <span class="text-muted small fw-semibold text-uppercase">মোবাইল নম্বর কভারেজ</span>
                            <h3 class="mb-0 mt-1 fw-bold text-info" id="kpi-mobile-rate"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-info text-info">
                            <i class="bi bi-phone"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-mobile-sub">মোবাইল নেই: <strong id="kpi-mobile-missing">0</strong></span>
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
                            <span class="text-muted small fw-semibold text-uppercase">শনাক্তকৃত সিবলিং ক্লাস্টার</span>
                            <h3 class="mb-0 mt-1 fw-bold text-warning" id="kpi-sibling-clusters"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-warning text-warning">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">একই পিতা-মাতার একাধিক সন্তান বিশিষ্ট পরিবার</span>
                        <button class="btn btn-sm btn-outline-warning py-1 px-2" onclick="openSiblingPreviewModal()">
                            <i class="bi bi-eye me-1"></i> সিবলিং তালিকা প্রিভিউ
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
                            <span class="text-muted small fw-semibold text-uppercase">চূড়ান্ত অভিভাবক ভোটার সংখ্যা</span>
                            <h3 class="mb-0 mt-1 fw-bold text-primary" id="kpi-unique-voters"><i class="bi bi-arrow-repeat spin"></i></h3>
                        </div>
                        <div class="kpi-icon-box bg-label-primary text-primary">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" id="kpi-voter-assigned-sub">ষষ্ঠ থেকে দশম শ্রেণি ক্রমানুযায়ী নির্ধারিত</span>
                        <a href="voter-master-list.php" class="btn btn-sm btn-primary py-1 px-2">
                            <i class="bi bi-printer me-1"></i> প্রিন্ট মাস্টার লিস্ট
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
                    <i class="bi bi-card-checklist me-2"></i>পিতা/মাতার NID বিশ্লেষণ ও অডিট রিপোর্ট
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-siblings">
                            <i class="bi bi-people me-1"></i> সিবলিং গ্রুপ (<span id="count-nid-siblings">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-invalid">
                            <i class="bi bi-exclamation-octagon me-1"></i> ভুল ফরম্যাট NID (<span id="count-nid-invalid">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-nid-missing">
                            <i class="bi bi-slash-circle me-1"></i> NID নেই (<span id="count-nid-missing">0</span>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="tab-nid-siblings">
                        <div id="nid-siblings-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-nid-invalid">
                        <div id="nid-invalid-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-nid-missing">
                        <div id="nid-missing-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
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
                    <i class="bi bi-phone-vibrate me-2"></i>মোবাইল নম্বর বিশ্লেষণ ও সিবলিং ক্লাস্টার অডিট
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-siblings">
                            <i class="bi bi-phone me-1"></i> কমন মোবাইল গ্রুপ (<span id="count-mob-siblings">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-invalid">
                            <i class="bi bi-exclamation-triangle me-1"></i> ভুল মোবাইল (<span id="count-mob-invalid">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-mob-missing">
                            <i class="bi bi-x-circle me-1"></i> মোবাইল নেই (<span id="count-mob-missing">0</span>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="tab-mob-siblings">
                        <div id="mob-siblings-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-mob-invalid">
                        <div id="mob-invalid-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-mob-missing">
                        <div id="mob-missing-content">
                            <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
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
                    <i class="bi bi-diagram-3 me-2"></i>সনাক্তকৃত সিবলিং ক্লাস্টার প্রিভিউ (একই পরিবারের একাধিক সন্তান)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-info-circle me-1"></i> পিতা/মাতার NID এবং মোবাইল নম্বরের ভিত্তিতে সিবলিং সনাক্ত করা হয়েছে। ভোটার নম্বর জেনারেটের সময় এই সকল ভাই-বোন একই ভোটার নম্বর লাভ করবে।
                </div>
                <div id="sibling-preview-table-container">
                    <div class="text-center py-4"><i class="bi bi-arrow-repeat spin fs-4"></i> লোড হচ্ছে...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn btn-primary" onclick="generateVoterList()">
                    <i class="bi bi-lightning-charge-fill me-1"></i> ভোটার নম্বর নিশ্চিত ও জেনারেট করুন
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        window.location.href = 'voter-info.php';
    }

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

                    $('#kpi-sibling-clusters').text(d.sibling_clusters_by_nid > 0 ? d.sibling_clusters_by_nid + ' টি পরিবার' : (d.sibling_clusters_by_mobile + ' টি পরিবার'));
                    $('#kpi-unique-voters').text(d.total_unique_voters > 0 ? d.total_unique_voters + ' জন' : 'জেনারেট হয়নি');
                }
            },
            error: function () {
                $('#kpi-total-students').text('—');
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
                        var h = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h += '<thead class="table-light"><tr><th>NID ও অভিভাবক</th><th>সন্তানের সংখ্যা</th><th>শিক্ষার্থীদের বিবরণ (শ্রেণি, শাখা, রোল)</th></tr></thead><tbody>';
                        d.multiple_nid_groups.forEach(function(g) {
                            h += '<tr>';
                            h += '<td><strong>' + g.nid + '</strong><br/><small class="text-muted">' + g.guardian_name + '</small></td>';
                            h += '<td class="text-center"><span class="badge bg-label-primary">' + g.count + ' জন</span></td>';
                            h += '<td>';
                            g.students.forEach(function(st, idx) {
                                h += '<div class="small mb-1">' + (idx+1) + '. <strong>' + st.name + '</strong> (' + st.class + ', শাখা: ' + st.section + ', রোল: ' + st.roll + ')</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#nid-siblings-content').html(h);
                    } else {
                        $('#nid-siblings-content').html('<div class="alert alert-secondary text-center my-3">NID ভিত্তিক কোনো সিবলিং গ্রুপ পাওয়া যায়নি।</div>');
                    }

                    // Invalid NID
                    if (d.invalid_nid_list.length > 0) {
                        var h2 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h2 += '<thead class="table-light"><tr><th>শিক্ষার্থীর নাম</th><th>শ্রেণি ও রোল</th><th>NID এর ধরন</th><th>প্রদত্ত NID (ভুল)</th></tr></thead><tbody>';
                        d.invalid_nid_list.forEach(function(st) {
                            h2 += '<tr>';
                            h2 += '<td>' + st.name + '</td>';
                            h2 += '<td>' + st.class + ' (রোল: ' + st.roll + ')</td>';
                            h2 += '<td>' + st.type + '</td>';
                            h2 += '<td class="text-danger fw-bold">' + st.raw_nid + '</td>';
                            h2 += '</tr>';
                        });
                        h2 += '</tbody></table></div>';
                        $('#nid-invalid-content').html(h2);
                    } else {
                        $('#nid-invalid-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> সকল NID সঠিক ফরম্যাটে রয়েছে।</div>');
                    }

                    // Missing NID
                    if (d.missing_nid_list.length > 0) {
                        var h3 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h3 += '<thead class="table-light"><tr><th>শিক্ষার্থীর নাম</th><th>শ্রেণি ও রোল</th><th>পিতা ও মাতা</th><th>মোবাইল</th></tr></thead><tbody>';
                        d.missing_nid_list.forEach(function(st) {
                            h3 += '<tr>';
                            h3 += '<td>' + st.name + '</td>';
                            h3 += '<td>' + st.class + ' (শাখা: ' + st.section + ', রোল: ' + st.roll + ')</td>';
                            h3 += '<td>F: ' + (st.father || '—') + '<br/>M: ' + (st.mother || '—') + '</td>';
                            h3 += '<td>' + (st.mobile || '—') + '</td>';
                            h3 += '</tr>';
                        });
                        h3 += '</tbody></table></div>';
                        $('#nid-missing-content').html(h3);
                    } else {
                        $('#nid-missing-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> সকল শিক্ষার্থীর অভিভাবকের NID রয়েছে।</div>');
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
                        var h = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h += '<thead class="table-light"><tr><th>মোবাইল ও পিতা/অভিভাবক</th><th>সন্তানের সংখ্যা</th><th>শিক্ষার্থীদের বিবরণ</th></tr></thead><tbody>';
                        d.multiple_mobile_groups.forEach(function(g) {
                            h += '<tr>';
                            h += '<td><strong>' + g.phone + '</strong><br/><small class="text-muted">' + (g.father || '') + ' (' + (g.village || '') + ')</small></td>';
                            h += '<td class="text-center"><span class="badge bg-label-info">' + g.count + ' জন</span></td>';
                            h += '<td>';
                            g.students.forEach(function(st, idx) {
                                h += '<div class="small mb-1">' + (idx+1) + '. <strong>' + st.name + '</strong> (' + st.class + ', শাখা: ' + st.section + ', রোল: ' + st.roll + ')</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#mob-siblings-content').html(h);
                    } else {
                        $('#mob-siblings-content').html('<div class="alert alert-secondary text-center my-3">কমন মোবাইল ভিত্তিক সিবলিং গ্রুপ পাওয়া যায়নি।</div>');
                    }

                    // Invalid mobile
                    if (d.invalid_mobile_list.length > 0) {
                        var h2 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h2 += '<thead class="table-light"><tr><th>শিক্ষার্থীর নাম</th><th>শ্রেণি ও রোল</th><th>মোবাইল নম্বর (ভুল)</th></tr></thead><tbody>';
                        d.invalid_mobile_list.forEach(function(st) {
                            h2 += '<tr>';
                            h2 += '<td>' + st.name + '</td>';
                            h2 += '<td>' + st.class + ' (রোল: ' + st.roll + ')</td>';
                            h2 += '<td class="text-danger fw-bold">' + st.raw_mobile + '</td>';
                            h2 += '</tr>';
                        });
                        h2 += '</tbody></table></div>';
                        $('#mob-invalid-content').html(h2);
                    } else {
                        $('#mob-invalid-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> সকল মোবাইল নম্বর সঠিক ফরম্যাটে রয়েছে।</div>');
                    }

                    // Missing mobile
                    if (d.missing_mobile_list.length > 0) {
                        var h3 = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">';
                        h3 += '<thead class="table-light"><tr><th>শিক্ষার্থীর নাম</th><th>শ্রেণি ও রোল</th><th>পিতা</th></tr></thead><tbody>';
                        d.missing_mobile_list.forEach(function(st) {
                            h3 += '<tr>';
                            h3 += '<td>' + st.name + '</td>';
                            h3 += '<td>' + st.class + ' (শাখা: ' + st.section + ', রোল: ' + st.roll + ')</td>';
                            h3 += '<td>' + (st.father || '—') + '</td>';
                            h3 += '</tr>';
                        });
                        h3 += '</tbody></table></div>';
                        $('#mob-missing-content').html(h3);
                    } else {
                        $('#mob-missing-content').html('<div class="alert alert-success text-center my-3"><i class="bi bi-check-circle me-1"></i> সকল শিক্ষার্থীর অভিভাবকের মোবাইল নম্বর রয়েছে।</div>');
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
                        h += '<thead class="table-light"><tr><th style="width: 50px;">SL</th><th>অভিভাবকের নাম ও ঠিকানা</th><th>NID ও মোবাইল</th><th>সিবলিং শিক্ষার্থীগণ (সন্তান)</th></tr></thead><tbody>';
                        list.forEach(function(g, idx) {
                            h += '<tr>';
                            h += '<td class="text-center fw-bold">' + (idx+1) + '</td>';
                            h += '<td><strong>' + (g.guardian_name || '—') + '</strong><br/><small class="text-muted"><i class="bi bi-geo-alt"></i> ' + (g.village || '—') + '</small></td>';
                            h += '<td><small>NID: ' + (g.nid || '—') + '</small><br/><small>Mob: ' + (g.mobile || '—') + '</small></td>';
                            h += '<td>';
                            g.children.forEach(function(c, cidx) {
                                h += '<div class="p-1 border-bottom d-flex justify-content-between align-items-center">';
                                h += '<span>' + (cidx+1) + '. <strong>' + c.name + '</strong> &mdash; ' + c.class + ' (' + c.section + '), রোল: ' + c.roll + '</span>';
                                if (c.current_voter_no > 0) {
                                    h += '<span class="badge bg-primary">ভোটার নং: ' + c.current_voter_no + '</span>';
                                }
                                h += '</div>';
                            });
                            h += '</td></tr>';
                        });
                        h += '</tbody></table></div>';
                        $('#sibling-preview-table-container').html(h);
                    } else {
                        $('#sibling-preview-table-container').html('<div class="alert alert-secondary text-center my-4">কোনো সিবলিং গ্রুপ সনাক্ত হয়নি।</div>');
                    }
                }
            }
        });
    }

    function generateVoterList() {
        if (!confirm('আপনি কি চলমান শিক্ষাবর্ষের সকল শিক্ষার্থীদের জন্য ষষ্ঠ শ্রেণি থেকে ক্রমানুযায়ী নতুন ভোটার নম্বর জেনারেট করতে চান?')) {
            return;
        }

        $.ajax({
            url: 'backend/generate-voter-numbers.php',
            method: 'POST',
            data: { action: 'generate' },
            dataType: 'json',
            beforeSend: function () {
                showToast('info', 'ভোটার নম্বর জেনারেট হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...', 'Processing');
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
                    showToast('danger', res.message || 'ভোটার নম্বর তৈরি ব্যর্থ হয়েছে।', 'Error');
                }
            },
            error: function () {
                showToast('danger', 'সার্ভারে অনুরোধ পাঠানো সম্ভব হয়নি।', 'Server Error');
            }
        });
    }

    function resetVoterList() {
        if (!confirm('সতর্কতা: আপনি কি নিশ্চিত যে চলমান শিক্ষাবর্ষের সকল ভোটার নম্বর রিসেট (০) করতে চান?')) {
            return;
        }

        $.ajax({
            url: 'backend/generate-voter-numbers.php',
            method: 'POST',
            data: { action: 'reset' },
            dataType: 'json',
            beforeSend: function () {
                showToast('warning', 'ভোটার নম্বর রিসেট হচ্ছে...', 'Resetting');
            },
            success: function (res) {
                if (res.status === 'success') {
                    showToast('success', res.message, 'Success');
                    loadVoterSummary();
                } else {
                    showToast('danger', res.message || 'রিসেট করা সম্ভব হয়নি।', 'Error');
                }
            },
            error: function () {
                showToast('danger', 'সার্ভারে অনুরোধ পাঠানো সম্ভব হয়নি।', 'Server Error');
            }
        });
    }
</script>