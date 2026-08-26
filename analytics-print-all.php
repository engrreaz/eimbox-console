<?php
require_once 'header.php';
$dataset_id = (int) ($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    die('<div class="alert alert-danger">Error: No Dataset ID provided.</div>');
}

// Helper function for creating progress bars
function create_bar($value, $max_value = 100, $color = '#007bff', $height = '18px')
{
    $percentage = ($value / $max_value) * 100;
    return "<div style='background-color: #e9ecef; border-radius: 5px; width: 100px; height: $height;'>
                <div style='width: {$percentage}%; background-color: $color; height: 100%; border-radius: 5px; text-align: center; color: white; font-size: 10px; line-height: $height;'>
                    " . number_format($value, 2) . "
                </div>
            </div>";
}
function create_bar_html($value, $max_value = 100, $color_class = 'bg-primary')
{
    $percentage = min(100, max(0, ($value / $max_value) * 100));
    return "<div class='progress' style='height: 16px; min-height: 16px; width: 100px;'>
                <div class='progress-bar {$color_class}' role='progressbar' style='width: {$percentage}%;' aria-valuenow='{$value}' aria-valuemin='0' aria-valuemax='{$max_value}'>
                    <span style='font-size: 10px; color: white; font-weight: 600; line-height: 16px; display: block;'>" . number_format($value, 1) . "%</span>
                </div>
            </div>";
}
?>
<style>
    /* Global Print Color Adjustments */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Section Visibility */
    .report-section-container.section-hidden {
        display: none !important;
    }

    /* Display Modes */
    /* Mode: Table Only (শুধু টেবিল - গ্রাফিক্যাল কার্ড ও প্রগ্রেস বার ছাড়া) */
    .mode-table-only .custom-graphical-view,
    .mode-table-only #custom-detailed-subject-view,
    .mode-table-only #custom-teacher-view,
    .mode-table-only #custom-class-view,
    .mode-table-only #custom-overall-subject-view,
    .mode-table-only #custom-at-risk-view,
    .mode-table-only .stat-summary-cards {
        display: none !important;
    }
    .mode-table-only .reference-data-block {
        display: block !important;
    }

    /* Mode: Graphical Only (টেবিল বাদ দিয়ে / শুধু গ্রাফিক্যাল কার্ড ও প্রগ্রেস বার) */
    .mode-graphical-only .reference-data-block {
        display: none !important;
    }
    .mode-graphical-only .custom-graphical-view,
    .mode-graphical-only #custom-detailed-subject-view,
    .mode-graphical-only #custom-teacher-view,
    .mode-graphical-only #custom-class-view,
    .mode-graphical-only #custom-overall-subject-view,
    .mode-graphical-only #custom-at-risk-view,
    .mode-graphical-only .stat-summary-cards {
        display: block !important;
    }

    /* Mode: Both (গ্রাফিক্যাল + বিস্তারিত টেবিল) */
    .mode-both .custom-graphical-view,
    .mode-both #custom-detailed-subject-view,
    .mode-both #custom-teacher-view,
    .mode-both #custom-class-view,
    .mode-both #custom-overall-subject-view,
    .mode-both #custom-at-risk-view,
    .mode-both .stat-summary-cards,
    .mode-both .reference-data-block {
        display: block !important;
    }

    /* Fine-grained Toggles */
    .hide-explanation-cards .report-explanation-card {
        display: none !important;
    }

    .no-page-breaks .page-break {
        display: none !important;
        page-break-before: auto !important;
    }

    /* Progress bar base styles with Inset Box-Shadow Fallback */
    .progress {
        display: flex !important;
        position: relative !important;
        background-color: #e9ecef !important;
        box-shadow: inset 0 0 0 1000px #e9ecef !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        overflow: hidden !important;
        height: 14px;
        min-height: 14px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .progress-bar {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        text-align: center !important;
        height: 100% !important;
        min-height: 14px !important;
        background-color: #696cff !important;
        box-shadow: inset 0 0 0 1000px #696cff !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .progress-bar:not([class*="bg-"]) {
        background-color: #696cff !important;
        box-shadow: inset 0 0 0 1000px #696cff !important;
    }

    .progress-bar.bg-primary { 
        background-color: #696cff !important; 
        box-shadow: inset 0 0 0 1000px #696cff !important;
    }
    .progress-bar.bg-success { 
        background-color: #28a745 !important; 
        box-shadow: inset 0 0 0 1000px #28a745 !important;
    }
    .progress-bar.bg-info { 
        background-color: #17a2b8 !important; 
        box-shadow: inset 0 0 0 1000px #17a2b8 !important;
    }
    .progress-bar.bg-warning { 
        background-color: #ffc107 !important; 
        box-shadow: inset 0 0 0 1000px #ffc107 !important;
        color: #000000 !important;
    }
    .progress-bar.bg-danger { 
        background-color: #dc3545 !important; 
        box-shadow: inset 0 0 0 1000px #dc3545 !important;
    }
    .progress-bar.bg-secondary { 
        background-color: #6c757d !important; 
        box-shadow: inset 0 0 0 1000px #6c757d !important;
    }

    .report-explanation-card {
        border-left: 4px solid #696cff !important;
        background-color: #f8f9fc !important;
        border-radius: 8px;
        page-break-inside: avoid;
    }
    .report-explanation-card .border-sm {
        border: 1px solid #e2e8f0;
    }
    .report-explanation-card .formula-tag {
        font-family: Consolas, "Courier New", monospace;
        font-size: 11px;
        background: #eef2ff;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }

    /* Modal Styling */
    .mode-option-card {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        border-width: 2px;
    }
    .btn-check:checked + .mode-option-card {
        background-color: #f0f2ff;
        border-color: #696cff !important;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.15);
    }
    .section-option-item {
        transition: background-color 0.2s ease;
    }
    .section-option-item:hover {
        background-color: #f1f3f7 !important;
    }

    /* A4 Compact Tables & Grouping */
    .a4-compact-table {
        width: 100% !important;
        font-size: 11px;
    }
    .a4-compact-table th, 
    .a4-compact-table td {
        padding: 4px 6px !important;
        vertical-align: middle;
    }
    .detailed-subject-table-group {
        page-break-inside: avoid;
        margin-bottom: 20px;
    }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .a4-compact-table {
            font-size: 9.5px !important;
            width: 100% !important;
        }
        .a4-compact-table th, 
        .a4-compact-table td {
            padding: 2.5px 3.5px !important;
            border: 1px solid #ced4da !important;
            white-space: normal !important;
            word-break: break-word !important;
        }
        .detailed-subject-table-group {
            page-break-inside: avoid;
            margin-bottom: 15px !important;
        }

        .no-print,
        .modal,
        .modal-backdrop {
            display: none !important;
            visibility: hidden !important;
        }
        body {
            background: white;
        }

        .page-break {
            display: none !important;
        }

        /* Intelligent Page Breaks: Only break BEFORE subsequent visible sections */
        .report-section-container {
            break-before: auto;
            page-break-before: auto;
            break-after: auto;
            page-break-after: auto;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        .report-section-container:not(.section-hidden) ~ .report-section-container:not(.section-hidden) {
            break-before: page !important;
            page-break-before: always !important;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        body * {
            visibility: hidden;
        }

        #main-report-block,
        #main-report-block * {
            visibility: visible;
        }

        /* Mode rules in print */
        .report-section-container.section-hidden {
            display: none !important;
            visibility: hidden !important;
        }

        .mode-table-only .custom-graphical-view,
        .mode-table-only #custom-institute-view,
        .mode-table-only #custom-detailed-subject-view,
        .mode-table-only #custom-teacher-view,
        .mode-table-only #custom-class-view,
        .mode-table-only #custom-overall-subject-view,
        .mode-table-only #custom-at-risk-view,
        .mode-table-only .stat-summary-cards {
            display: none !important;
            visibility: hidden !important;
        }
        .mode-table-only .reference-data-block {
            display: block !important;
            visibility: visible !important;
        }

        .mode-graphical-only .reference-data-block {
            display: none !important;
            visibility: hidden !important;
        }

        .hide-explanation-cards .report-explanation-card {
            display: none !important;
            visibility: hidden !important;
        }

        .no-page-breaks .report-section-container:not(.section-hidden) ~ .report-section-container:not(.section-hidden) {
            break-before: auto !important;
            page-break-before: auto !important;
            margin-bottom: 25px !important;
        }

        /* Bulletproof Progress Bars in Print */
        .progress {
            display: flex !important;
            position: relative !important;
            background-color: #e9ecef !important;
            box-shadow: inset 0 0 0 1000px #e9ecef !important;
            border: 1px solid #ced4da !important;
            height: 14px !important;
            min-height: 14px !important;
            overflow: hidden !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .progress-bar {
            display: flex !important;
            height: 100% !important;
            min-height: 14px !important;
            line-height: 14px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .progress-bar:not([class*="bg-"]) {
            background-color: #696cff !important;
            box-shadow: inset 0 0 0 1000px #696cff !important;
        }

        .progress-bar.bg-primary { 
            background-color: #696cff !important; 
            box-shadow: inset 0 0 0 1000px #696cff !important;
        }
        .progress-bar.bg-success { 
            background-color: #28a745 !important; 
            box-shadow: inset 0 0 0 1000px #28a745 !important;
        }
        .progress-bar.bg-info { 
            background-color: #17a2b8 !important; 
            box-shadow: inset 0 0 0 1000px #17a2b8 !important;
        }
        .progress-bar.bg-warning { 
            background-color: #ffc107 !important; 
            box-shadow: inset 0 0 0 1000px #ffc107 !important;
            color: #000000 !important;
        }
        .progress-bar.bg-danger { 
            background-color: #dc3545 !important; 
            box-shadow: inset 0 0 0 1000px #dc3545 !important;
        }
        .progress-bar.bg-secondary { 
            background-color: #6c757d !important; 
            box-shadow: inset 0 0 0 1000px #6c757d !important;
        }

        .badge {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .badge.bg-primary { background-color: #696cff !important; box-shadow: inset 0 0 0 1000px #696cff !important; color: #fff !important; }
        .badge.bg-success { background-color: #28a745 !important; box-shadow: inset 0 0 0 1000px #28a745 !important; color: #fff !important; }
        .badge.bg-info { background-color: #17a2b8 !important; box-shadow: inset 0 0 0 1000px #17a2b8 !important; color: #fff !important; }
        .badge.bg-warning { background-color: #ffc107 !important; box-shadow: inset 0 0 0 1000px #ffc107 !important; color: #000 !important; }
        .badge.bg-danger { background-color: #dc3545 !important; box-shadow: inset 0 0 0 1000px #dc3545 !important; color: #fff !important; }
        .badge.bg-secondary { background-color: #6c757d !important; box-shadow: inset 0 0 0 1000px #6c757d !important; color: #fff !important; }

        .border-start {
            border-left-width: 4px !important;
        }
        .border-danger {
            border-color: #dc3545 !important;
        }

        .report-explanation-card {
            background-color: #f8f9fa !important;
            box-shadow: inset 0 0 0 1000px #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            page-break-inside: avoid;
            margin-top: 15px !important;
            margin-bottom: 15px !important;
        }
        .report-explanation-card .p-2 {
            padding: 4px 8px !important;
        }

        #main-report-block {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .card {
            margin: 0 !important;
            padding: 0 !important;
        }

        h1, h2, h3, h4 {
            margin-top: 20px;
        }
    }

    .page-break {
        page-break-before: always;
    }

    .loading-placeholder {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        background-color: #f8f9fa;
        border: 1px dashed #ddd;
        border-radius: 5px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center no-print">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Full Analytics Report</h5>
            <div class="d-flex gap-2 align-items-center">
                <a href="analytics-exam-report.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <!-- Options Button with active section count badge -->
                <button type="button" id="openReportOptionsBtn" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportOptionsModal" title="Configure Report Sections & Display Mode">
                    <i class="bi bi-sliders2 me-1"></i> Options
                    <span class="badge bg-primary rounded-pill ms-1" id="sectionCountBadge">7/7</span>
                </button>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card-body" id="main-report-block">
            <!-- Placeholders for each report section wrapped with container -->
            <div class="report-section-container" id="container-institute-report" data-section="institute-report">
                <div id="institute-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-detailed-subject-report" data-section="detailed-subject-report">
                <div id="detailed-subject-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-teacher-report" data-section="teacher-report">
                <div id="teacher-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-class-report" data-section="class-report">
                <div id="class-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-overall-subject-report" data-section="overall-subject-report">
                <div id="overall-subject-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-student-report" data-section="student-report">
                <div id="student-report" class="report-section"></div>
            </div>

            <div class="report-section-container" id="container-at-risk-students-report" data-section="at-risk-students-report">
                <div id="at-risk-students-report" class="report-section"></div>
            </div>
        </div>
    </div>
</div>

<!-- Report Options Modal -->
<div class="modal fade no-print" id="reportOptionsModal" tabindex="-1" aria-labelledby="reportOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="reportOptionsModalLabel">
                        <i class="bi bi-sliders2 text-primary me-2"></i> Report Display & Print Options
                    </h5>
                    <small class="text-muted">রিপোর্টের অংশসমূহ ও ডিসপ্লে মোড কাস্টমাইজ করুন</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- 1. Display Mode Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark d-flex align-items-center mb-2">
                        <i class="bi bi-display text-primary me-2"></i> Display Mode (ডিসপ্লে মোড)
                    </label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="displayMode" id="modeBoth" value="both" checked>
                            <label class="btn btn-outline-primary mode-option-card w-100 text-start p-3 h-100 d-flex flex-column justify-content-between" for="modeBoth">
                                <div>
                                    <div class="fw-bold mb-1"><i class="bi bi-card-checklist me-1"></i> Table + Graphical</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 11px;">ভিজ্যুয়াল কার্ড, প্রগ্রেস বার এবং র' ডাটা টেবিল উভয়ই থাকবে।</small>
                                </div>
                                <span class="badge bg-primary text-white mt-2 align-self-start">পূর্ণাঙ্গ ভিউ</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="displayMode" id="modeTableOnly" value="table-only">
                            <label class="btn btn-outline-primary mode-option-card w-100 text-start p-3 h-100 d-flex flex-column justify-content-between" for="modeTableOnly">
                                <div>
                                    <div class="fw-bold mb-1"><i class="bi bi-table me-1"></i> Table Only</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 11px;">কার্ড বাদ দিয়ে শুধুমাত্র পরিচ্ছন্ন অফিশিয়াল ডাটা টেবিল দেখানো হবে।</small>
                                </div>
                                <span class="badge bg-info text-white mt-2 align-self-start">শুধু টেবিল</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="displayMode" id="modeGraphicalOnly" value="graphical-only">
                            <label class="btn btn-outline-primary mode-option-card w-100 text-start p-3 h-100 d-flex flex-column justify-content-between" for="modeGraphicalOnly">
                                <div>
                                    <div class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-1"></i> Graphical Only</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 11px;">র' রেফারেন্স টেবিল ছাড়া শুধুমাত্র সামারি কার্ড ও গ্রাফিক্স থাকবে।</small>
                                </div>
                                <span class="badge bg-success text-white mt-2 align-self-start">টেবিল বাদ দিয়ে</span>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <!-- 2. Report Sections Show/Hide -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="bi bi-ui-checks text-primary me-2"></i> Select Report Sections (রিপোর্টের অংশসমূহ)
                        </label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllSectionsBtn">
                                <i class="bi bi-check-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllSectionsBtn">
                                <i class="bi bi-x me-1"></i> Deselect All
                            </button>
                        </div>
                    </div>
                    <div class="row g-2" id="sectionCheckboxesContainer">
                        <!-- 7 Section items with clear Bangla & English titles -->
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_institute_report" data-target="institute-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_institute_report">
                                        1. Institute Performance Overview
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">প্রতিষ্ঠানের সামগ্রিক পারফরম্যান্স</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Overview</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_detailed_subject_report" data-target="detailed-subject-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_detailed_subject_report">
                                        2. Detailed Subject Performance
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">শ্রেণি ও শাখাভিত্তিক বিশদ পারফরম্যান্স</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Subject Detail</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_teacher_report" data-target="teacher-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_teacher_report">
                                        3. Teacher's Performance Report
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">শিক্ষকদের পারফরম্যান্স ও র‍্যাঙ্কিং</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Teachers</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_class_report" data-target="class-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_class_report">
                                        4. Class Performance Report
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">শ্রেণিভিত্তিক পারফরম্যান্স ও কাঠিন্য সূচক</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Classes</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_overall_subject_report" data-target="overall-subject-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_overall_subject_report">
                                        5. Overall Subject Performance
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">সামগ্রিক বিষয়ভিত্তিক পারফরম্যান্স ও SDF</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Subjects</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_student_report" data-target="student-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_student_report">
                                        6. Student Merit List
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">শিক্ষার্থীদের মেধাতালিকা ও গ্রেডিং</small>
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary small">Merit List</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item d-flex align-items-center justify-content-between">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-toggle-checkbox" type="checkbox" role="switch" id="sec_at_risk_students_report" data-target="at-risk-students-report" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="sec_at_risk_students_report">
                                        7. At-Risk Students Report
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">ঝুঁকিপূর্ণ শিক্ষার্থীদের তালিকা ও কারণ</small>
                                    </label>
                                </div>
                                <span class="badge bg-danger-subtle text-danger small">At-Risk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <!-- 3. Additional Settings -->
                <div>
                    <label class="form-label fw-bold text-dark mb-2">
                        <i class="bi bi-gear-wide-connected text-primary me-2"></i> Additional Settings (অতিরিক্ত সেটিংস)
                    </label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="opt_show_explanation" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="opt_show_explanation">
                                        Show Explanation Cards
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">ব্যাখ্যা ও সূত্র নির্দেশিকা কার্ড দেখান</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-light section-option-item">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="opt_page_breaks" checked>
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="opt_page_breaks">
                                        Page Breaks on Print
                                        <small class="text-muted d-block fw-normal" style="font-size: 11px;">প্রিন্টে প্রতিটি সেকশনে নতুন পেজ ব্রেক</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between py-2 px-4">
                <button type="button" class="btn btn-outline-secondary" id="resetReportOptionsBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default (ডিফল্ট)
                </button>
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" id="applyReportOptionsBtn">
                    <i class="bi bi-check2-circle me-1"></i> Apply & Close (প্রয়োগ করুন)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const datasetId = <?= json_encode($dataset_id) ?>;
        const loadingHTML = `<div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

        // Default State Configuration
        const defaultOptions = {
            mode: 'both', // 'both' | 'table-only' | 'graphical-only'
            sections: {
                'institute-report': true,
                'detailed-subject-report': true,
                'teacher-report': true,
                'class-report': true,
                'overall-subject-report': true,
                'student-report': true,
                'at-risk-students-report': true
            },
            showExplanation: true,
            pageBreaks: true
        };

        let currentOptions = JSON.parse(JSON.stringify(defaultOptions));

        // Load saved preferences from localStorage if exists
        try {
            const saved = localStorage.getItem('eimbox_analytics_report_options');
            if (saved) {
                const parsed = JSON.parse(saved);
                currentOptions = {
                    ...defaultOptions,
                    ...parsed,
                    sections: { ...defaultOptions.sections, ...(parsed.sections || {}) }
                };
            }
        } catch (e) {
            console.warn('Could not read from localStorage', e);
        }

        // Function to apply UI state based on currentOptions
        function applyOptions() {
            const reportBlock = document.getElementById('main-report-block');
            if (!reportBlock) return;

            // 1. Display Mode
            reportBlock.classList.remove('mode-both', 'mode-table-only', 'mode-graphical-only');
            reportBlock.classList.add(`mode-${currentOptions.mode}`);

            // Sync radio button
            const modeRadio = document.querySelector(`input[name="displayMode"][value="${currentOptions.mode}"]`);
            if (modeRadio) {
                modeRadio.checked = true;
            }

            // 2. Section Visibility
            let activeCount = 0;
            const totalCount = Object.keys(currentOptions.sections).length;

            for (const [sectionId, isVisible] of Object.entries(currentOptions.sections)) {
                const container = document.getElementById(`container-${sectionId}`);
                const checkbox = document.querySelector(`.section-toggle-checkbox[data-target="${sectionId}"]`);
                if (checkbox) {
                    checkbox.checked = !!isVisible;
                }

                if (container) {
                    if (isVisible) {
                        container.classList.remove('section-hidden');
                        activeCount++;
                    } else {
                        container.classList.add('section-hidden');
                    }
                }
            }

            // Update badge on Options button
            const badge = document.getElementById('sectionCountBadge');
            if (badge) {
                badge.textContent = `${activeCount}/${totalCount}`;
                if (activeCount === totalCount) {
                    badge.className = 'badge bg-primary rounded-pill ms-1';
                } else if (activeCount === 0) {
                    badge.className = 'badge bg-danger rounded-pill ms-1';
                } else {
                    badge.className = 'badge bg-warning text-dark rounded-pill ms-1';
                }
            }

            // 3. Explanation Cards
            if (currentOptions.showExplanation) {
                reportBlock.classList.remove('hide-explanation-cards');
            } else {
                reportBlock.classList.add('hide-explanation-cards');
            }
            const expSwitch = document.getElementById('opt_show_explanation');
            if (expSwitch) expSwitch.checked = !!currentOptions.showExplanation;

            // 4. Page Breaks
            if (currentOptions.pageBreaks) {
                reportBlock.classList.remove('no-page-breaks');
            } else {
                reportBlock.classList.add('no-page-breaks');
            }
            const pbSwitch = document.getElementById('opt_page_breaks');
            if (pbSwitch) pbSwitch.checked = !!currentOptions.pageBreaks;

            // Save to localStorage
            try {
                localStorage.setItem('eimbox_analytics_report_options', JSON.stringify(currentOptions));
            } catch (e) {
                console.warn('Could not save to localStorage', e);
            }
        }

        // Event Listeners for Modal Controls
        // Display Mode Radio change
        document.querySelectorAll('input[name="displayMode"]').forEach(radio => {
            radio.addEventListener('change', function () {
                currentOptions.mode = this.value;
                applyOptions();
            });
        });

        // Section Toggle Checkboxes
        document.querySelectorAll('.section-toggle-checkbox').forEach(chk => {
            chk.addEventListener('change', function () {
                const target = this.getAttribute('data-target');
                if (target) {
                    currentOptions.sections[target] = this.checked;
                    applyOptions();
                }
            });
        });

        // Select All Sections
        const selectAllBtn = document.getElementById('selectAllSectionsBtn');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                for (const key of Object.keys(currentOptions.sections)) {
                    currentOptions.sections[key] = true;
                }
                applyOptions();
            });
        }

        // Deselect All Sections
        const deselectAllBtn = document.getElementById('deselectAllSectionsBtn');
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function () {
                for (const key of Object.keys(currentOptions.sections)) {
                    currentOptions.sections[key] = false;
                }
                applyOptions();
            });
        }

        // Explanation Cards Toggle
        const optShowExp = document.getElementById('opt_show_explanation');
        if (optShowExp) {
            optShowExp.addEventListener('change', function () {
                currentOptions.showExplanation = this.checked;
                applyOptions();
            });
        }

        // Page Breaks Toggle
        const optPageBreaks = document.getElementById('opt_page_breaks');
        if (optPageBreaks) {
            optPageBreaks.addEventListener('change', function () {
                currentOptions.pageBreaks = this.checked;
                applyOptions();
            });
        }

        // Reset to Default
        const resetBtn = document.getElementById('resetReportOptionsBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                currentOptions = JSON.parse(JSON.stringify(defaultOptions));
                applyOptions();
            });
        }

        // Apply initially
        applyOptions();

        const reportSections = [
            { id: 'institute-report', title: 'Institute Performance Overview', endpoint: 'get_institute_report.php' },
            { id: 'detailed-subject-report', title: 'Detailed Subject Performance', endpoint: 'get_detailed_subject_report.php' },
            { id: 'teacher-report', title: 'Teacher\'s Performance Report', endpoint: 'get_teacher_report.php' },
            { id: 'class-report', title: 'Class Performance Report', endpoint: 'get_class_report.php' },
            { id: 'overall-subject-report', title: 'Overall Subject Performance', endpoint: 'get_subject_report.php' },
            { id: 'student-report', title: 'Student Merit List', endpoint: 'get_student_report.php' },
            { id: 'at-risk-students-report', title: 'At-Risk Students Report', endpoint: 'get_at_risk_students_report.php' }
        ];

        // Function to fetch and render a single report
        async function loadReport(section) {
            const container = document.getElementById(section.id);
            if (!container) return;
            container.innerHTML = loadingHTML;

            try {
                const response = await fetch(`analytics/${section.endpoint}?dataset_id=${datasetId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();

                if (result.status === 'success') {
                    container.innerHTML = renderReport(section.id, result.data);
                } else {
                    throw new Error(result.message || 'API returned an error.');
                }
            } catch (error) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load ${section.id}: ${error.message}</div>`;
            }
        }

        function create_bar_html(value, max_value = 100, color_class = 'bg-primary') {
            const percentage = Math.min(100, Math.max(0, (value / max_value) * 100));
            return `<div class='progress' style='height: 16px; min-height: 16px; width: 100px;'>
                        <div class='progress-bar ${color_class}' role='progressbar' style='width: ${percentage}%;' aria-valuenow='${value}' aria-valuemin='0' aria-valuemax='${max_value}'>
                            <span style='font-size: 10px; color: white; font-weight: 600; line-height: 16px; display: block;'>${parseFloat(value).toFixed(1)}%</span>
                        </div>
                    </div>`;
        }

        function renderGenericTable(title, data) {
            if (!data || data.length === 0) {
                return title ? `<h1>${title}</h1><div class="alert alert-warning">No data available for this report.</div>` : '';
            }

            let html = title ? `<h1>${title}</h1>` : '';
            html += '<div class="table-responsive"><table class="table table-bordered table-sm table-striped">';

            // Create header
            const headers = Object.keys(data[0]);
            html += '<thead><tr>';
            headers.forEach(header => {
                html += `<th>${header.replace(/_/g, ' ').toUpperCase()}</th>`;
            });
            html += '</tr></thead>';

            // Create body
            html += '<tbody>';
            data.forEach(row => {
                html += '<tr>';
                headers.forEach(header => {
                    html += `<td>${row[header] !== null ? row[header] : ''}</td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';

            return html;
        }

        function renderDetailedSubjectTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-warning">No detailed subject data available.</div>';
            }

            // Group data by Class > Section
            const grouped = {};
            data.forEach(row => {
                const cls = row.classname || 'Class';
                const sec = row.sectionname || 'General';
                if (!grouped[cls]) grouped[cls] = {};
                if (!grouped[cls][sec]) grouped[cls][sec] = [];
                grouped[cls][sec].push(row);
            });

            let html = '';

            for (const [cls, sections] of Object.entries(grouped)) {
                for (const [sec, subjects] of Object.entries(sections)) {
                    html += `
                    <div class="detailed-subject-table-group mb-4">
                        <div class="d-flex justify-content-between align-items-center bg-light border p-2 px-3 rounded-top mb-0">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="bi bi-mortarboard-fill me-2"></i>Class: <span class="text-dark">${cls}</span>
                                <span class="mx-2 text-muted">|</span>
                                Section: <span class="text-dark">${sec}</span>
                            </h6>
                            <span class="badge bg-secondary text-white">${subjects.length} Subjects</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                                <thead class="table-light">
                                    <tr class="text-center align-middle" style="font-size: 10px;">
                                        <th style="width: 45px;">Code</th>
                                        <th class="text-start" style="min-width: 120px;">Subject & Teacher</th>
                                        <th style="width: 75px;">Enrolled / Appeared</th>
                                        <th style="width: 85px;">Pass & Avg</th>
                                        <th style="width: 75px;">Exc. (70%+)</th>
                                        <th class="text-start" style="min-width: 155px;">Gender Stats (M : F)</th>
                                        <th style="width: 90px;">Dist. & Variation</th>
                                        <th style="width: 95px;">Range & Indices</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    subjects.forEach(sub => {
                        const code = sub.subject_code || '-';
                        const name = sub.subject_name || `Subject ${code}`;
                        const teacher = sub.teacher_name || 'Unassigned';
                        const tpos = sub.teacher_position ? ` (${sub.teacher_position})` : '';

                        const enrolled = parseInt(sub.student_count) || 0;
                        const appeared = parseInt(sub.appeared_student_count) || 0;
                        const attnRate = enrolled > 0 ? ((appeared / enrolled) * 100).toFixed(1) : '0.0';

                        const passed = parseInt(sub.pass_count) || 0;
                        const failed = parseInt(sub.fail_count) || 0;
                        const passRate = parseFloat(sub.pass_rate || 0).toFixed(1);
                        const avgMarks = parseFloat(sub.marks_percentage || sub.avg_marks || 0).toFixed(1);

                        const excellent = parseInt(sub.excellent_count) || 0;
                        const excellentRate = parseFloat(sub.excellent_rate || 0).toFixed(1);

                        const maleCount = parseInt(sub.male_count) || 0;
                        const femaleCount = parseInt(sub.female_count) || 0;
                        const malePass = parseInt(sub.male_pass_count) || 0;
                        const femalePass = parseInt(sub.female_pass_count) || 0;
                        const maleAvg = parseFloat(sub.male_avg_marks || 0).toFixed(1);
                        const femaleAvg = parseFloat(sub.female_avg_marks || 0).toFixed(1);
                        const malePassRate = maleCount > 0 ? ((malePass / maleCount) * 100).toFixed(1) : '0.0';
                        const femalePassRate = femaleCount > 0 ? ((femalePass / femaleCount) * 100).toFixed(1) : '0.0';

                        const totalGender = maleCount + femaleCount;
                        const genderRatioStr = totalGender > 0 ? `${maleCount}M : ${femaleCount}F` : '-';

                        const aboveAvg = parseInt(sub.count_above_avg) || 0;
                        const belowAvg = parseInt(sub.count_below_avg) || 0;
                        const variance = parseFloat(sub.variance || 0).toFixed(1);
                        const stdDev = parseFloat(sub.std_deviation || 0).toFixed(2);

                        const maxM = parseFloat(sub.max_marks || 0).toFixed(0);
                        const minM = parseFloat(sub.min_marks || 0).toFixed(0);
                        const rangeVal = parseFloat(sub.marks_range || (maxM - minM)).toFixed(0);

                        const cdi = parseFloat(sub.cdi || 0).toFixed(2);
                        const tspi = parseFloat(sub.tspi || 0).toFixed(1);

                        html += `
                                    <tr>
                                        <td class="text-center fw-semibold text-secondary">${code}</td>
                                        <td class="text-start">
                                            <div class="fw-bold text-dark lh-sm">${name}</div>
                                            <small class="text-muted d-block lh-sm" style="font-size: 9.5px;"><i class="bi bi-person me-1"></i>${teacher}${tpos}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold lh-sm">${appeared} <span class="text-muted fw-normal">/ ${enrolled}</span></div>
                                            <small class="text-muted d-block" style="font-size: 9px;">Attn: ${attnRate}%</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="lh-sm"><span class="text-success fw-bold">${passed}P</span> / <span class="text-danger fw-bold">${failed}F</span></div>
                                            <small class="d-block" style="font-size: 9.5px;">PR: <strong class="text-success">${passRate}%</strong> | Avg: <strong class="text-primary">${avgMarks}%</strong></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold text-info lh-sm">${excellent}</div>
                                            <small class="text-muted d-block" style="font-size: 9px;">(${excellentRate}%)</small>
                                        </td>
                                        <td class="text-start" style="font-size: 9.5px;">
                                            <div class="lh-sm"><span class="fw-semibold text-primary">👦 M (${maleCount}):</span> ${malePass} Pass <span class="text-muted">(${malePassRate}%)</span> • Avg: <strong class="text-dark">${maleAvg}%</strong></div>
                                            <div class="lh-sm mt-1"><span class="fw-semibold text-danger">👧 F (${femaleCount}):</span> ${femalePass} Pass <span class="text-muted">(${femalePassRate}%)</span> • Avg: <strong class="text-dark">${femaleAvg}%</strong></div>
                                            <div class="text-muted mt-1" style="font-size: 8.5px;">Ratio: <span class="fw-bold text-secondary">${genderRatioStr}</span></div>
                                        </td>
                                        <td class="text-center" style="font-size: 9.5px;">
                                            <div class="lh-sm"><span class="text-success fw-bold">▲ ${aboveAvg}</span> | <span class="text-danger fw-bold">▼ ${belowAvg}</span></div>
                                            <small class="text-muted d-block mt-1" style="font-size: 8.5px;">SD: <strong>${stdDev}</strong> | Var: <strong>${variance}</strong></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="lh-sm"><span class="text-success fw-bold">${maxM}</span> - <span class="text-danger fw-bold">${minM}</span> <small class="text-muted">(${rangeVal})</small></div>
                                            <div class="mt-1">
                                                <span class="badge bg-warning text-dark me-1" style="font-size: 8.5px;">CDI: ${cdi}</span>
                                                <span class="badge bg-primary text-white" style="font-size: 8.5px;">TSPI: ${tspi}</span>
                                            </div>
                                        </td>
                                    </tr>`;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    </div>`;
                }
            }

            return html;
        }

        function renderTeacherTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-warning">No teacher performance data available.</div>';
            }

            let html = `
            <div class="detailed-teacher-table-container mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                        <thead class="table-light">
                            <tr class="text-center align-middle" style="font-size: 10px;">
                                <th style="width: 45px;">Rank</th>
                                <th class="text-start" style="min-width: 130px;">Teacher & Position</th>
                                <th class="text-start" style="min-width: 140px;">Workload (Subjects & Classes)</th>
                                <th style="width: 85px;">Pass & Avg</th>
                                <th style="width: 75px;">Exc. (70%+)</th>
                                <th style="width: 95px;">Index (TPI & TIA)</th>
                                <th style="width: 90px;">Impact (TCI & TSI)</th>
                                <th style="width: 85px;">Consistency</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(teacher => {
                const rank = teacher.teacher_rank ? `#${teacher.teacher_rank}` : '-';
                const name = teacher.tname || 'Unassigned Teacher';
                const pos = teacher.position || 'Teacher';

                const students = parseInt(teacher.total_students_taught) || 0;
                const subjectsCount = parseInt(teacher.total_subjects_taught) || 0;
                const classesCount = parseInt(teacher.total_classes_taught) || 0;

                const subjectsList = teacher.subjects_list || 'N/A';
                const classesList = teacher.classes_list || 'N/A';

                const avgMarks = parseFloat(teacher.overall_avg_marks || 0).toFixed(1);
                const passRate = parseFloat(teacher.overall_pass_rate || 0).toFixed(1);
                const excRate = parseFloat(teacher.overall_excellent_rate || 0).toFixed(1);

                const tpi = parseFloat(teacher.teacher_performance_index || teacher.tpi || 0).toFixed(1);
                const tii = parseFloat(teacher.teacher_impact_index || 1).toFixed(2);
                const tia = parseFloat(teacher.teacher_impact_adjustment || teacher.tia || 0).toFixed(1);

                const tci = parseFloat(teacher.tci_score || 0);
                const tsi = parseFloat(teacher.tsi_score || 0);
                const tciSign = tci > 0 ? '+' : '';
                const tsiSign = tsi > 0 ? '+' : '';
                const tciColor = tci > 0 ? 'text-success' : (tci < 0 ? 'text-danger' : 'text-muted');
                const tsiColor = tsi > 0 ? 'text-success' : (tsi < 0 ? 'text-danger' : 'text-muted');

                const stdDev = parseFloat(teacher.avg_std_deviation || 0).toFixed(2);
                const variance = parseFloat(teacher.avg_variance || 0).toFixed(1);

                html += `
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 py-1 px-2">${rank}</span>
                                </td>
                                <td class="text-start">
                                    <div class="fw-bold text-dark lh-sm">${name}</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 9.5px;"><i class="bi bi-briefcase me-1"></i>${pos}</small>
                                </td>
                                <td class="text-start" style="font-size: 9.5px;">
                                    <div class="lh-sm"><span class="text-dark fw-bold">${students}</span> Students | <span class="text-secondary">${classesCount} Cls</span> | <span class="text-secondary">${subjectsCount} Sub</span></div>
                                    <div class="text-muted mt-1 text-truncate" style="max-width: 200px;" title="${classesList}"><i class="bi bi-mortarboard me-1"></i>${classesList}</div>
                                    <div class="text-primary mt-0 text-truncate" style="max-width: 200px;" title="${subjectsList}"><i class="bi bi-book me-1"></i>${subjectsList}</div>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm">PR: <strong class="text-success">${passRate}%</strong></div>
                                    <small class="d-block" style="font-size: 9.5px;">Avg: <strong class="text-primary">${avgMarks}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-info lh-sm">${excRate}%</div>
                                    <small class="text-muted d-block" style="font-size: 8.5px;">(70%+ Scored)</small>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm"><strong class="text-primary fs-6">TIA: ${tia}</strong></div>
                                    <small class="text-muted d-block" style="font-size: 9px;">TPI: ${tpi} • TII: ${tii}x</small>
                                </td>
                                <td class="text-center" style="font-size: 9.5px;">
                                    <div class="lh-sm">Class (TCI): <strong class="${tciColor}">${tciSign}${tci.toFixed(1)}</strong></div>
                                    <div class="lh-sm mt-1">Sub (TSI): <strong class="${tsiColor}">${tsiSign}${tsi.toFixed(1)}</strong></div>
                                </td>
                                <td class="text-center" style="font-size: 9px;">
                                    <div class="lh-sm mb-1">
                                        ${(() => {
                                            const val = parseFloat(stdDev);
                                            if (val <= 0) return `<span class="badge bg-secondary-subtle text-secondary px-1 py-0" style="font-size: 8px;">N/A</span>`;
                                            if (val <= 15) return `<span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size: 8px;" title="SD ≤ 15 (Ideal)"><i class="bi bi-check-circle-fill me-1"></i>${stdDev} (Ideal)</span>`;
                                            if (val <= 20) return `<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-1 py-0" style="font-size: 8px;" title="SD 15-20 (Moderate)"><i class="bi bi-exclamation-circle-fill text-warning me-1"></i>${stdDev} (Mod)</span>`;
                                            return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0" style="font-size: 8px;" title="SD > 20 (High Gap)"><i class="bi bi-exclamation-triangle-fill me-1"></i>${stdDev} (High)</span>`;
                                        })()}
                                    </div>
                                    <div class="text-muted" style="font-size: 8.5px;">Var: <strong>${variance}</strong></div>
                                </td>
                            </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;

            return html;
        }

        function renderClassTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-warning">No class performance data available.</div>';
            }

            let html = `
            <div class="detailed-class-table-container mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                        <thead class="table-light">
                            <tr class="text-center align-middle" style="font-size: 10px;">
                                <th style="width: 45px;">Rank</th>
                                <th class="text-start" style="min-width: 120px;">Class & Section</th>
                                <th style="width: 80px;">Appeared / Enrolled</th>
                                <th style="width: 85px;">Pass & Avg</th>
                                <th style="width: 75px;">Exc. (70%+)</th>
                                <th class="text-start" style="min-width: 150px;">Gender Stats (M : F)</th>
                                <th style="width: 90px;">Index (CPI & TII)</th>
                                <th style="width: 95px;">Difficulty (CDF) & Var</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(cls => {
                const rank = cls.class_rank ? `#${cls.class_rank}` : '-';
                const classname = cls.classname || 'Class';
                const sectionname = cls.sectionname || 'Section';

                const enrolled = parseInt(cls.total_enrolled) || parseInt(cls.total_students_appeared) || 0;
                const appeared = parseInt(cls.total_students_appeared) || 0;
                const subjects = parseInt(cls.total_subjects) || 0;
                const attnRate = enrolled > 0 ? ((appeared / enrolled) * 100).toFixed(1) : '0.0';

                const avgMarks = parseFloat(cls.overall_marks_percentage || 0).toFixed(1);
                const subAvg = parseFloat(cls.avg_of_subject_averages || 0).toFixed(1);
                const passRate = parseFloat(cls.pass_rate || 0).toFixed(1);
                const excRate = parseFloat(cls.excellent_rate || 0).toFixed(1);

                const cpi = parseFloat(cls.cpi_score || 0).toFixed(1);
                const difficulty = parseFloat(cls.difficulty_factor || 0).toFixed(2);
                const tii = parseFloat(cls.teacher_impact_index || 1).toFixed(2);

                const maleCount = parseInt(cls.male_count) || 0;
                const femaleCount = parseInt(cls.female_count) || 0;
                const malePassed = parseInt(cls.male_passed) || 0;
                const femalePassed = parseInt(cls.female_passed) || 0;
                const maleAvg = parseFloat(cls.male_avg || 0).toFixed(1);
                const femaleAvg = parseFloat(cls.female_avg || 0).toFixed(1);
                const totalGender = maleCount + femaleCount;
                const genderRatioStr = totalGender > 0 ? `${maleCount}M : ${femaleCount}F` : '-';

                const stdDev = parseFloat(cls.avg_std_dev || 0).toFixed(2);
                const variance = parseFloat(cls.avg_variance || 0).toFixed(1);
                const aboveAvg = parseInt(cls.above_avg_count) || 0;
                const belowAvg = parseInt(cls.below_avg_count) || 0;

                html += `
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 py-1 px-2">${rank}</span>
                                </td>
                                <td class="text-start">
                                    <div class="fw-bold text-dark lh-sm">${classname} - ${sectionname}</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 9.5px;"><i class="bi bi-journal-text me-1"></i>${subjects} Subjects</small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold lh-sm">${appeared} <span class="text-muted fw-normal">/ ${enrolled}</span></div>
                                    <small class="text-muted d-block" style="font-size: 9px;">Attn: ${attnRate}%</small>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm">PR: <strong class="text-success">${passRate}%</strong></div>
                                    <small class="d-block" style="font-size: 9.5px;">Avg: <strong class="text-primary">${avgMarks}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-info lh-sm">${excRate}%</div>
                                    <small class="text-muted d-block" style="font-size: 8.5px;">(70%+ Scored)</small>
                                </td>
                                <td class="text-start" style="font-size: 9.5px;">
                                    <div class="lh-sm"><span class="fw-semibold text-primary">👦 M (${maleCount}):</span> Avg: <strong class="text-dark">${maleAvg}%</strong></div>
                                    <div class="lh-sm mt-1"><span class="fw-semibold text-danger">👧 F (${femaleCount}):</span> Avg: <strong class="text-dark">${femaleAvg}%</strong></div>
                                    <div class="text-muted mt-1" style="font-size: 8.5px;">Ratio: <span class="fw-bold text-secondary">${genderRatioStr}</span></div>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm"><strong class="text-primary fs-6">CPI: ${cpi}</strong></div>
                                    <small class="text-muted d-block" style="font-size: 9px;">TII Boost: ${tii}x</small>
                                </td>
                                <td class="text-center" style="font-size: 9.5px;">
                                    <div class="lh-sm"><span class="badge bg-danger text-white" style="font-size: 9px;">CDF: ${difficulty}</span></div>
                                    <small class="text-muted d-block mt-1" style="font-size: 8.5px;">SD: <strong>${stdDev}</strong> | Var: <strong>${variance}</strong></small>
                                </td>
                            </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;

            return html;
        }

        function renderOverallSubjectTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-warning">No overall subject performance data available.</div>';
            }

            let html = `
            <div class="detailed-overall-subject-table-container mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                        <thead class="table-light">
                            <tr class="text-center align-middle" style="font-size: 10px;">
                                <th style="width: 50px;">Code</th>
                                <th class="text-start" style="min-width: 130px;">Subject & Scope</th>
                                <th style="width: 80px;">Appeared / Enrolled</th>
                                <th style="width: 85px;">Pass & Avg</th>
                                <th style="width: 75px;">Exc. (70%+)</th>
                                <th class="text-start" style="min-width: 150px;">Gender Stats (M : F)</th>
                                <th style="width: 90px;">Difficulty (SDF)</th>
                                <th style="width: 95px;">Dist. & Variance</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(sub => {
                const subCode = sub.subject_code || '-';
                const subName = sub.subject_name || `Subject ${subCode}`;

                const enrolled = parseInt(sub.total_enrolled) || parseInt(sub.total_students_appeared) || 0;
                const appeared = parseInt(sub.total_students_appeared) || 0;
                const attnRate = enrolled > 0 ? ((appeared / enrolled) * 100).toFixed(1) : '0.0';

                const classesCount = parseInt(sub.classes_count) || 0;
                const teachersCount = parseInt(sub.teachers_count) || 0;

                const avgMarks = parseFloat(sub.overall_marks_percentage || 0).toFixed(1);
                const failureRate = parseFloat(sub.failure_rate || 0).toFixed(1);
                const passRate = parseFloat(sub.pass_rate || (100 - failureRate)).toFixed(1);
                const excRate = parseFloat(sub.excellent_rate || 0).toFixed(1);

                const sdf = parseFloat(sub.subject_difficulty_factor || 0).toFixed(1);
                const lowGpa = parseFloat(sub.low_gpa_ratio || 0).toFixed(1);
                const median = parseFloat(sub.median || 0).toFixed(1);

                const maleCount = parseInt(sub.male_count) || 0;
                const femaleCount = parseInt(sub.female_count) || 0;
                const maleAvg = parseFloat(sub.male_avg || 0).toFixed(1);
                const femaleAvg = parseFloat(sub.female_avg || 0).toFixed(1);
                const totalGender = maleCount + femaleCount;
                const genderRatioStr = totalGender > 0 ? `${maleCount}M : ${femaleCount}F` : '-';

                const stdDev = parseFloat(sub.std_deviation || 0).toFixed(2);
                const variance = parseFloat(sub.variance || 0).toFixed(1);
                const aboveAvg = parseInt(sub.above_avg_count) || 0;
                const belowAvg = parseInt(sub.below_avg_count) || 0;

                const sdfBadgeClass = sdf >= 40 ? 'bg-danger' : (sdf >= 25 ? 'bg-warning text-dark' : 'bg-success');

                html += `
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-secondary font-monospace">${subCode}</span>
                                </td>
                                <td class="text-start">
                                    <div class="fw-bold text-dark lh-sm">${subName}</div>
                                    <small class="text-muted d-block lh-sm" style="font-size: 9.5px;"><i class="bi bi-diagram-3 me-1"></i>${classesCount} Classes • ${teachersCount} Teachers</small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold lh-sm">${appeared} <span class="text-muted fw-normal">/ ${enrolled}</span></div>
                                    <small class="text-muted d-block" style="font-size: 9px;">Attn: ${attnRate}%</small>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm">PR: <strong class="text-success">${passRate}%</strong></div>
                                    <small class="d-block" style="font-size: 9.5px;">Avg: <strong class="text-primary">${avgMarks}%</strong></small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-info lh-sm">${excRate}%</div>
                                    <small class="text-muted d-block" style="font-size: 8.5px;">(70%+ Scored)</small>
                                </td>
                                <td class="text-start" style="font-size: 9.5px;">
                                    <div class="lh-sm"><span class="fw-semibold text-primary">👦 M (${maleCount}):</span> Avg: <strong class="text-dark">${maleAvg}%</strong></div>
                                    <div class="lh-sm mt-1"><span class="fw-semibold text-danger">👧 F (${femaleCount}):</span> Avg: <strong class="text-dark">${femaleAvg}%</strong></div>
                                    <div class="text-muted mt-1" style="font-size: 8.5px;">Ratio: <span class="fw-bold text-secondary">${genderRatioStr}</span></div>
                                </td>
                                <td class="text-center">
                                    <div class="lh-sm"><span class="badge ${sdfBadgeClass} fs-6 py-1 px-2">SDF: ${sdf}</span></div>
                                    <small class="text-muted d-block mt-1" style="font-size: 8.5px;">&lt;50% Low: <strong>${lowGpa}%</strong></small>
                                </td>
                                <td class="text-center" style="font-size: 9.5px;">
                                    <div class="lh-sm">▲ <strong>${aboveAvg}</strong> | ▼ <strong>${belowAvg}</strong></div>
                                    <small class="text-muted d-block mt-1" style="font-size: 8.5px;">SD: <strong>${stdDev}</strong> | Var: <strong>${variance}</strong></small>
                                </td>
                            </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;

            return html;
        }

        function renderStudentMeritTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-warning">No student performance data available.</div>';
            }

            // Group by Class and Section
            const grouped = {};
            data.forEach(s => {
                const key = `${s.classname || 'Class'} - ${s.sectionname || 'Section'}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(s);
            });

            let html = `<div class="detailed-student-table-container mb-4">`;

            for (const [classTitle, students] of Object.entries(grouped)) {
                const totalStudents = students.length;
                const passedStudents = students.filter(s => (parseInt(s.failed_subjects) || 0) === 0).length;
                const passRate = totalStudents > 0 ? ((passedStudents / totalStudents) * 100).toFixed(1) : '0.0';

                html += `
                <div class="detailed-student-group mb-4">
                    <div class="d-flex justify-content-between align-items-center bg-light border p-2 rounded-top mb-0">
                        <span class="fw-bold text-dark"><i class="bi bi-mortarboard-fill text-primary me-2"></i>Class: ${classTitle}</span>
                        <span class="badge bg-primary fs-6 py-1 px-2">${totalStudents} Students | Passed: ${passedStudents} (${passRate}%)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                            <thead class="table-light">
                                <tr class="text-center align-middle" style="font-size: 10px;">
                                    <th style="width: 55px;">Rank</th>
                                    <th style="width: 50px;">Roll</th>
                                    <th class="text-start" style="min-width: 140px;">Student Details</th>
                                    <th style="width: 90px;">Marks & %</th>
                                    <th style="width: 65px;">GPA</th>
                                    <th style="width: 55px;">Grade</th>
                                    <th class="text-start" style="min-width: 150px;">Status & Remarks</th>
                                </tr>
                            </thead>
                            <tbody>`;

                students.forEach(st => {
                    const isFail = (parseInt(st.failed_subjects) || 0) > 0;
                    const classRank = st.class_rank ? `#${st.class_rank}` : '-';
                    const secRank = st.section_rank ? `#${st.section_rank}` : '-';

                    const stname = st.stnameeng || `Student ${st.stid}`;
                    const stnameben = st.stnameben || '';
                    const rollno = st.rollno || '-';
                    const stid = st.stid || '';
                    const gender = st.gender || '';
                    const genderIcon = (gender.toLowerCase() === 'female' || gender === 'ছাত্রী') ? '👧' : '👦';

                    const totalMarks = parseFloat(st.total_marks_obtained || 0).toFixed(1);
                    const fullMarks = parseFloat(st.total_full_marks || 0).toFixed(0);
                    const pct = parseFloat(st.percentage || 0).toFixed(1);
                    const gpa = parseFloat(st.gpa || 0).toFixed(2);
                    const grade = st.grade || (isFail ? 'F' : '-');
                    const riskScore = parseFloat(st.risk_score || 0).toFixed(1);
                    const failedNames = st.failed_subject_names || '';

                    let gradeBadge = 'bg-secondary';
                    if (grade === 'A+') gradeBadge = 'bg-primary';
                    else if (grade === 'A' || grade === 'A-') gradeBadge = 'bg-success';
                    else if (grade === 'F') gradeBadge = 'bg-danger';

                    let statusHtml = '';
                    if (isFail) {
                        const failCnt = parseInt(st.failed_subjects) || 1;
                        statusHtml = `<span class="badge bg-danger">Failed (${failCnt})</span>`;
                        if (failedNames) {
                            statusHtml += `<div class="text-danger mt-1" style="font-size: 8.5px;"><i class="bi bi-exclamation-triangle me-1"></i>${failedNames}</div>`;
                        }
                        if (parseFloat(riskScore) > 0) {
                            statusHtml += `<div class="text-muted" style="font-size: 8px;">Risk Score: <strong>${riskScore}</strong></div>`;
                        }
                    } else {
                        statusHtml = `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Passed</span>`;
                    }

                    html += `
                                <tr>
                                    <td class="text-center">
                                        <span class="badge ${isFail ? 'bg-danger' : 'bg-primary'} fs-6 py-1 px-2">${isFail ? 'Fail' : classRank}</span>
                                        <small class="text-muted d-block mt-1" style="font-size: 8.5px;">Sec: ${secRank}</small>
                                    </td>
                                    <td class="text-center fw-bold text-dark font-monospace">${rollno}</td>
                                    <td class="text-start">
                                        <div class="fw-bold text-dark lh-sm">${genderIcon} ${stname}</div>
                                        ${stnameben ? `<small class="text-muted d-block lh-1" style="font-size: 9px;">${stnameben}</small>` : ''}
                                        <small class="text-muted font-monospace" style="font-size: 8.5px;">ID: ${stid}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-primary lh-sm">${totalMarks}</div>
                                        <small class="text-muted d-block" style="font-size: 8.5px;">${pct}% (${fullMarks})</small>
                                    </td>
                                    <td class="text-center fw-bold fs-6 ${isFail ? 'text-danger' : 'text-primary'}">${gpa}</td>
                                    <td class="text-center"><span class="badge ${gradeBadge} fs-6 px-2">${grade}</span></td>
                                    <td class="text-start">${statusHtml}</td>
                                </tr>`;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                </div>`;
            }

            html += `</div>`;
            return html;
        }

        function renderAtRiskTable(data) {
            if (!data || data.length === 0) {
                return '<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>No high-risk students found for this examination dataset.</div>';
            }

            let html = `
            <div class="detailed-at-risk-table-container mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                        <thead class="table-light">
                            <tr class="text-center align-middle" style="font-size: 10px;">
                                <th style="width: 75px;">Risk Level</th>
                                <th style="width: 50px;">Roll</th>
                                <th class="text-start" style="min-width: 140px;">Student & Class</th>
                                <th class="text-start" style="min-width: 160px;">Failed Subject(s)</th>
                                <th style="width: 85px;">Total Marks & %</th>
                                <th style="width: 85px;">Guardian Mobile</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(st => {
                const riskScore = parseFloat(st.risk_score || 0).toFixed(1);
                const failedCount = parseInt(st.failed_subject_count || st.failed_subjects) || 1;
                const failedList = st.failed_subject_list || st.failed_subject_names || '-';

                const stname = st.stnameeng || `Student ${st.stid}`;
                const stnameben = st.stnameben || '';
                const rollno = st.rollno || '-';
                const stid = st.stid || '';
                const classname = st.classname || '-';
                const sectionname = st.sectionname || '-';
                const gender = st.gender || '';
                const genderIcon = (gender.toLowerCase() === 'female' || gender === 'ছাত্রী') ? '👧' : '👦';
                const guarmobile = st.guarmobile || '-';

                const totalMarks = parseFloat(st.total_marks_obtained || 0).toFixed(1);
                const pct = parseFloat(st.percentage || 0).toFixed(1);

                let badgeClass = 'bg-danger text-white';
                let levelLabel = 'Critical';
                if (parseFloat(riskScore) >= 60 || failedCount >= 3) {
                    badgeClass = 'bg-danger text-white';
                    levelLabel = 'Critical';
                } else if (parseFloat(riskScore) >= 35 || failedCount == 2) {
                    badgeClass = 'bg-warning text-dark';
                    levelLabel = 'Moderate';
                } else {
                    badgeClass = 'bg-info text-dark';
                    levelLabel = 'Borderline';
                }

                html += `
                            <tr>
                                <td class="text-center">
                                    <span class="badge ${badgeClass} fs-6 py-1 px-2">SRS: ${riskScore}</span>
                                    <small class="text-muted d-block mt-1 fw-semibold" style="font-size: 8.5px;">${levelLabel}</small>
                                </td>
                                <td class="text-center fw-bold text-dark font-monospace">${rollno}</td>
                                <td class="text-start">
                                    <div class="fw-bold text-dark lh-sm">${genderIcon} ${stname}</div>
                                    ${stnameben ? `<small class="text-muted d-block lh-1" style="font-size: 9px;">${stnameben}</small>` : ''}
                                    <small class="text-muted d-block" style="font-size: 8.5px;"><i class="bi bi-mortarboard-fill me-1"></i>${classname} - ${sectionname} (ID: ${stid})</small>
                                </td>
                                <td class="text-start">
                                    <span class="badge bg-danger mb-1" style="font-size: 8.5px;">Failed: ${failedCount}</span>
                                    <div class="text-danger fw-semibold lh-sm" style="font-size: 9.5px;">${failedList}</div>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark lh-sm">${totalMarks}</div>
                                    <small class="text-muted d-block" style="font-size: 8.5px;">${pct}%</small>
                                </td>
                                <td class="text-center font-monospace" style="font-size: 9.5px;">
                                    ${guarmobile !== '-' ? `<a href="tel:${guarmobile}" class="text-decoration-none text-primary fw-bold"><i class="bi bi-telephone me-1"></i>${guarmobile}</a>` : '<span class="text-muted">-</span>'}
                                </td>
                            </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;

            return html;
        }

        function renderInstituteTable(data) {
            if (!data) return '<div class="alert alert-warning">No institutional data available.</div>';

            const summary = data.summary || {};
            const classes = data.classes_summary || [];

            let html = `
            <div class="institute-summary-table-container mb-4">
                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Class Performance Comparative Matrix (শ্রেণিভিত্তিক তুলনামূলক পারফরম্যান্স)</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle mb-0 a4-compact-table">
                        <thead class="table-light text-center">
                            <tr style="font-size: 10px;">
                                <th style="width: 45px;">Rank</th>
                                <th class="text-start" style="min-width: 130px;">Class & Section</th>
                                <th style="width: 85px;">Appeared / Enrolled</th>
                                <th style="width: 80px;">Passed / Failed</th>
                                <th style="width: 75px;">Pass Rate %</th>
                                <th style="width: 70px;">GPA 5.0 (A+)</th>
                                <th style="width: 75px;">Avg Marks %</th>
                                <th style="width: 80px;">CPI Score</th>
                                <th style="width: 75px;">Difficulty (CDF)</th>
                            </tr>
                        </thead>
                        <tbody>`;

            if (classes.length === 0) {
                html += `<tr><td colspan="9" class="text-center text-muted py-2">No class records available</td></tr>`;
            } else {
                classes.forEach((cls, idx) => {
                    const rank = parseInt(cls.class_rank) || (idx + 1);
                    const classname = cls.classname || '-';
                    const sectionname = cls.sectionname || '-';
                    const appeared = parseInt(cls.total_students_appeared) || 0;
                    const enrolled = parseInt(cls.total_enrolled) || appeared;
                    const passed = parseInt(cls.total_passed) || 0;
                    const failed = parseInt(cls.total_failed) || 0;
                    const passRate = parseFloat(cls.pass_rate || 0).toFixed(1);
                    const aplus = parseInt(cls.aplus_count) || 0;
                    const avgMarks = parseFloat(cls.overall_marks_percentage || 0).toFixed(1);
                    const cpi = parseFloat(cls.cpi_score || 0).toFixed(1);
                    const cdf = parseFloat(cls.difficulty_factor || 0).toFixed(1);

                    const cpiBadge = cpi >= 70 ? 'bg-success' : (cpi >= 50 ? 'bg-primary' : (cpi >= 35 ? 'bg-warning text-dark' : 'bg-danger'));
                    const rankDisplay = rank === 1 ? '<span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #1</span>' : `<span class="badge bg-light text-dark">#${rank}</span>`;

                    html += `
                            <tr>
                                <td class="text-center fw-bold">${rankDisplay}</td>
                                <td class="text-start fw-bold text-dark">${classname} - ${sectionname}</td>
                                <td class="text-center">${appeared} <span class="text-muted fw-normal">/ ${enrolled}</span></td>
                                <td class="text-center"><span class="text-success fw-bold">${passed}</span> / <span class="text-danger fw-bold">${failed}</span></td>
                                <td class="text-center fw-bold ${parseFloat(passRate) >= 70 ? 'text-success' : (parseFloat(passRate) >= 50 ? 'text-primary' : 'text-danger')}">${passRate}%</td>
                                <td class="text-center fw-bold text-info">${aplus}</td>
                                <td class="text-center fw-bold text-dark">${avgMarks}%</td>
                                <td class="text-center"><span class="badge ${cpiBadge} px-2">${cpi}</span></td>
                                <td class="text-center text-muted font-monospace">${cdf}</td>
                            </tr>`;
                });

                // Totals Footer Row
                const totAppeared = summary.total_appeared || 0;
                const totEnrolled = summary.total_enrolled || totAppeared;
                const totPassed = summary.total_passed || 0;
                const totFailed = summary.total_failed || 0;
                const totPassRate = parseFloat(summary.pass_rate || 0).toFixed(1);
                const totAplus = summary.total_aplus || 0;
                const totAvg = parseFloat(summary.overall_avg_marks_percentage || 0).toFixed(1);

                html += `
                            <tr class="table-secondary fw-bold text-dark border-top border-2">
                                <td class="text-center">Total</td>
                                <td class="text-start">All ${classes.length} Classes</td>
                                <td class="text-center">${totAppeared} / ${totEnrolled}</td>
                                <td class="text-center"><span class="text-success">${totPassed}</span> / <span class="text-danger">${totFailed}</span></td>
                                <td class="text-center text-success">${totPassRate}%</td>
                                <td class="text-center text-info">${totAplus}</td>
                                <td class="text-center">${totAvg}%</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                            </tr>`;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            </div>`;

            return html;
        }

        function renderExplanationCard(title, items, notes = '') {
            let html = `
            <div class="card mt-4 border-light shadow-none bg-light report-explanation-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-info-circle-fill text-primary me-2 fs-5"></i>
                        <h6 class="fw-bold text-primary mb-0">${title}</h6>
                    </div>
                    <div class="row g-2 small text-muted">`;

            items.forEach(item => {
                html += `
                    <div class="col-md-6">
                        <div class="p-2 bg-white rounded border-sm h-100">
                            <div class="fw-bold text-dark mb-1"><i class="bi bi-dot text-primary"></i> ${item.term}</div>
                            <div class="text-secondary">${item.desc}</div>
                            ${item.formula ? `<div class="text-primary mt-1 formula-tag"><i class="bi bi-calculator me-1"></i><em>গণনা: ${item.formula}</em></div>` : ''}
                        </div>
                    </div>`;
            });

            html += `</div>`;
            if (notes) {
                html += `
                    <div class="mt-2 pt-2 border-top text-secondary small fst-italic">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i> ${notes}
                    </div>`;
            }
            html += `</div></div>`;
            return html;
        }

        function renderReport(sectionId, data) {
            if (sectionId === 'institute-report') {
                let html = '<h1>Institute Performance Overview (প্রতিষ্ঠানের সামগ্রিক পারফরম্যান্স)</h1>';
                
                // Graphical Dashboard & Top Sheet Container
                html += '<div id="custom-institute-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading Executive Top Sheet...</span></div></div></div>';
                
                fetch(`analytics/display_institute_report.php?dataset_id=${datasetId}`)
                    .then(response => response.text())
                    .then(customHtml => {
                        const target = document.getElementById('custom-institute-view');
                        if (target) target.innerHTML = customHtml;
                    })
                    .catch(error => {
                        const target = document.getElementById('custom-institute-view');
                        if (target) target.innerHTML = `<div class="alert alert-danger">Failed to load executive top sheet: ${error}</div>`;
                    });

                // Comprehensive Institutional Performance Explanation Card
                html += renderExplanationCard(
                    'প্রতিষ্ঠানের পারফরম্যান্স নির্দেশিকা ও প্রাতিষ্ঠানিক সূচকসমূহ (Institute Performance Guide)',
                    [
                        {
                            term: 'Attendance Rate (উপস্থিতির হার %)',
                            desc: 'মোট ভর্তি শিক্ষার্থীর মধ্যে কতজন পরীক্ষায় অংশ নিয়েছে তার শতকরা অনুপাত।',
                            formula: '(অংশগ্রহণকারী শিক্ষার্থী ÷ মোট ভর্তি শিক্ষার্থী) × ১০০'
                        },
                        {
                            term: 'Overall Pass Rate (সামগ্রিক পাসের হার %)',
                            desc: 'সকল বিষয়ে সফলভাবে উত্তীর্ণ অনন্য শিক্ষার্থীর শতকরা হার।',
                            formula: '(সকল বিষয়ে পাস শিক্ষার্থী ÷ মোট অংশগ্রহণকারী) × ১০০'
                        },
                        {
                            term: 'GPA 5.00 (A+) ও Excellence Rate (উৎকর্ষ হার %)',
                            desc: 'সর্বোচ্চ জিপিএ ৫.০০ প্রাপ্ত শিক্ষার্থী এবং ৭০% বা তদূর্ধ্ব সামগ্রিক নম্বর অর্জনকারী শিক্ষার্থীদের হার।',
                            formula: '(জিপিএ ৫.০০ বা ৭০%+ নম্বর প্রাপ্ত শিক্ষার্থী ÷ মোট অংশগ্রহণকারী) × ১০০'
                        },
                        {
                            term: 'Gender Parity & Performance (লিঙ্গভিত্তিক সমতা)',
                            desc: 'ছাত্র এবং ছাত্রীদের অংশগ্রহণ, পাসের হার, অর্জিত জিপিএ ৫.০০ এবং গড় নম্বরের তুলনামূলক বিশ্লেষণ।'
                        },
                        {
                            term: 'CPI & Class Benchmarks (শ্রেণি পারফরম্যান্স)',
                            desc: 'শ্রেণির গড় নম্বর, পাসের হার এবং শিক্ষকের অবদানের সমন্বয়ে প্রস্তুত শ্রেণি পারফরম্যান্স সূচক (CPI)।'
                        },
                        {
                            term: 'Institutional At-Risk (ঝুঁকিপূর্ণ শিক্ষার্থী অনুপাত)',
                            desc: 'প্রতিষ্ঠানের মোট শিক্ষার্থীর মধ্যে যাদের তাৎক্ষণিক নিবিড় প্রতিকারমূলক পাঠদান (Remedial Coaching) প্রয়োজন।'
                        }
                    ],
                    'প্রাতিষ্ঠানিক লক্ষ্য: সকল শ্রেণিতে ন্যূনতম ৮০% পাসের হার নিশ্চিতকরণ, সমাপনী পরীক্ষায় জিপিএ ৫.০০ এর অনুপাত বৃদ্ধি এবং ফেল করা শিক্ষার্থীদের রিমিডিয়াল ক্লাসের আওতায় আনা।'
                );

                // Detailed Class-by-Class Comparative Matrix Table
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Institutional Class Performance Matrix Table</h3>';
                html += renderInstituteTable(data);
                html += '</div>';

                return html;
            }
            if (sectionId === 'detailed-subject-report') {
                let html = '<h1>Detailed Subject Performance (Class & Section wise)</h1>';

                // Placeholder for the custom view
                html += '<div id="custom-detailed-subject-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading Custom View...</span></div></div></div>';

                fetch(`analytics/display_detailed_subject_report.php?dataset_id=${datasetId}`)
                    .then(response => response.text())
                    .then(customHtml => {
                        const target = document.getElementById('custom-detailed-subject-view');
                        if (target) target.innerHTML = customHtml;
                    })
                    .catch(error => {
                        const target = document.getElementById('custom-detailed-subject-view');
                        if (target) target.innerHTML = `<div class="alert alert-danger">Failed to load custom view: ${error}</div>`;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'বিষয়ভিত্তিক বিশদ পারফরম্যান্স নির্দেশিকা ও গণনার সূত্র (Detailed Subject Guide)',
                    [
                        {
                            term: 'Appeared / Enrolled (অংশগ্রহণকারী / অন্তর্ভুক্তি)',
                            desc: 'শ্রেণি ও শাখায় মোট ভর্তি থাকা শিক্ষার্থীর মধ্যে কতজন সংশ্লিষ্ট বিষয়ের পরীক্ষায় অংশ নিয়েছে।'
                        },
                        {
                            term: 'Pass Rate (পাসের হার %)',
                            desc: 'সংশ্লিষ্ট বিষয়ে পাস নম্বর (কমপক্ষে ৩৩%) প্রাপ্ত শিক্ষার্থীদের শতকরা হার।',
                            formula: '(পাস শিক্ষার্থী ÷ অংশ নেওয়া শিক্ষার্থী) × ১০০ [ধাপ ১]'
                        },
                        {
                            term: 'Excellent Rate (উৎকর্ষ হার % - ৭০%+ নম্বর)',
                            desc: 'সংশ্লিষ্ট বিষয়ে ৭০% বা তদূর্ধ্ব নম্বর (Grade A ও A+) প্রাপ্ত শিক্ষার্থীদের শতকরা হার।',
                            formula: '(৭০%+ নম্বর পাওয়া শিক্ষার্থী ÷ অংশ নেওয়া শিক্ষার্থী) × ১০০ [ধাপ ১]'
                        },
                        {
                            term: 'Gender Performance (লিঙ্গভিত্তিক ফলাফল ও অনুপাত)',
                            desc: 'ছাত্র (Male) ও ছাত্রী (Female) এর অন্তর্ভুক্তি সংখ্যা, পাসের সংখ্যা, পাসের হার ও গড় নম্বরের তুলনামূলক বিশ্লেষণ।'
                        },
                        {
                            term: 'Above / Below Avg (গড়ের বেশি/কম বণ্টন)',
                            desc: 'বিষয়ের গড় নম্বরের চেয়ে বেশি (Above Avg) এবং কম (Below Avg) পাওয়া শিক্ষার্থীদের সংখ্যা।'
                        },
                        {
                            term: 'Variance & Std. Dev. (বিস্তার ও আদর্শ বিচ্যুতি)',
                            desc: 'শিক্ষার্থীদের নম্বরের ধারাবাহিকতা ও ব্যবধান। কম SD সমমানের পারফরম্যান্স ও বেশি SD নম্বরের বড় তারতম্য নির্দেশ করে।'
                        },
                        {
                            term: 'Score Range (নম্বর পরিসর)',
                            desc: 'সর্বোচ্চ (Max) ও সর্বনিম্ন (Min) নম্বরের ব্যবধান।',
                            formula: 'সর্বোচ্চ নম্বর - সর্বনিম্ন নম্বর [ধাপ ১]'
                        },
                        {
                            term: 'CDI (Combined Difficulty Index)',
                            desc: 'শ্রেণির কাঠিন্য (CDF) ও বিষয়ের সার্বিক কাঠিন্যের (SDF) সমন্বিত কাঠিন্য সূচক (মান বেশি হলে চ্যালেঞ্জিং)।',
                            formula: '(শ্রেণির CDF × ০.৪০) + (বিষয়ের SDF × ০.৬০) + ১ [ধাপ ৭]'
                        },
                        {
                            term: 'TSPI (Teacher Subject Performance Index)',
                            desc: 'নির্দিষ্ট শ্রেণি-শাখায় শিক্ষকের বিষয়ের সম্মিলিত পারফরম্যান্স মানদণ্ড।',
                            formula: '(গড় নম্বর % × ০.৫০) + (পাসের হার % × ০.৫০) [ধাপ ৮a]'
                        }
                    ],
                    'পরামর্শ: যেসব বিষয়ে CDI বেশি ও TSPI কম, সেগুলোতে পাঠদান পদ্ধতি ও শিক্ষার্থীদের শিখন ঘাটতি নিবিড়ভাবে পর্যালোচনা করা প্রয়োজন।'
                );

                // Append the formatted grouped table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Detailed Subject Performance Table (Class & Section wise)</h3>';
                html += renderDetailedSubjectTable(data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'teacher-report') {
                let html = '<h1>Teacher\'s Performance Report (শিক্ষকদের পারফরম্যান্স)</h1>';

                // Placeholder for the custom view
                html += '<div id="custom-teacher-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading Custom View...</span></div></div></div>';

                fetch(`analytics/display_teacher_report.php?dataset_id=${datasetId}`)
                    .then(response => response.text())
                    .then(customHtml => {
                        const target = document.getElementById('custom-teacher-view');
                        if (target) target.innerHTML = customHtml;
                    })
                    .catch(error => {
                        const target = document.getElementById('custom-teacher-view');
                        if (target) target.innerHTML = `<div class="alert alert-danger">Failed to load custom view: ${error}</div>`;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'শিক্ষক মূল্যায়ন সূচক, পরিমাপ পদ্ধতি ও গণনার সূত্র (Teacher Evaluation Guide)',
                    [
                        {
                            term: 'TPI (Teacher Performance Index - বেস পারফরম্যান্স)',
                            desc: 'শিক্ষকের পাঠদানে শিক্ষার্থীদের সরাসরি ফলাফলের ওয়েটেড স্কোর।',
                            formula: '(পাসের হার % × ০.৪০) + (৭০%+ উৎকর্ষ হার % × ০.২৫) + (গড় নম্বর % × ০.৩৫) [ধাপ ৮]'
                        },
                        {
                            term: 'TII (Teacher Impact Index - কাঠিন্য বুস্ট গুণক)',
                            desc: 'শিক্ষক যে শ্রেণিতে পড়ান তার চ্যালেঞ্জ বা কাঠিন্যের প্রভাব সমন্বয়ক (কঠিন বা দুর্বল শ্রেণিতে পাঠদানকারী শিক্ষকের TII বেশি হয়)।',
                            formula: '১ + (১০০ - ক্লাসের গড় নম্বর %) ÷ ১০০ [ধাপ ৫]'
                        },
                        {
                            term: 'TIA (Teacher Impact Adjustment - চূড়ান্ত স্কোর)',
                            desc: 'কাঠিন্য সমন্বিত চূড়ান্ত স্কোর (এই স্কোরের ক্রমানুযায়ী Teacher Rank নির্ধারিত হয়)।',
                            formula: 'TPI × TII [ধাপ ৮]'
                        },
                        {
                            term: 'TCI (Teacher Class Impact - শ্রেণি প্রভাব)',
                            desc: 'শ্রেণির অন্যান্য সকল বিষয়ের সামগ্রিক গড়ের তুলনায় এই শিক্ষকের বিষয়ের গড়ের ব্যবধান (+ মান ক্লাসের চেয়ে ভালো নির্দেশ করে)।',
                            formula: 'AVG(শিক্ষকের বিষয়ের গড় % - শ্রেণির সার্বিক গড় %) [ধাপ ১২]'
                        },
                        {
                            term: 'TSI (Teacher Subject Impact - বিষয় প্রভাব)',
                            desc: 'প্রতিষ্ঠানের একই বিষয়ের সামগ্রিক গড়ের তুলনায় এই শিক্ষকের সেকশনের শিক্ষার্থীদের অর্জিত গড়ের পার্থক্য (+ মান সার্বিক বিষয়ের চেয়ে ভালো নির্দেশ করে)।',
                            formula: 'AVG(শিক্ষকের বিষয়ের গড় % - প্রতিষ্ঠানের ওই বিষয়ের সার্বিক গড় %) [ধাপ ১২]'
                        },
                        {
                            term: 'SD & Variance (ধারাবাহিকতা ও কালার সূচক)',
                            desc: 'শিক্ষার্থীদের ফলাফলের তারতম্য বা ধারাবাহিকতা। <br><span class="text-success fw-bold">🟢 SD ≤ ১৫</span>: আদর্শ ও সুষম | <span class="text-warning fw-bold">🟡 SD ১৫.১-২০</span>: মধ্যম | <span class="text-danger fw-bold">🔴 SD > ২০</span>: উচ্চ বৈষম্য (দুর্বলদের বিশেষ যত্ন প্রয়োজন)।',
                            formula: 'AVG(Standard Deviation) ও AVG(Variance) [ধাপ ৫]'
                        },
                        {
                            term: 'Teacher Rank (শিক্ষক র‍্যাঙ্ক)',
                            desc: 'প্রতিষ্ঠানের সকল শিক্ষকদের মধ্যে চূড়ান্ত TIA স্কোরের ভিত্তিতে শিক্ষকের অবস্থান (#1, #2...)।',
                            formula: 'RANK() OVER (ORDER BY TIA DESC) [ধাপ ১৫]'
                        }
                    ],
                    'ব্যাখ্যা: দুর্বল বা কঠিন শ্রেণিতে পাঠদানকারী শিক্ষকের পরিশ্রম ও অবদানকে ন্যায্য মূল্যায়ন করতে TIA স্কোরে ক্লাসের কাঠিন্য গুণক (TII) দিয়ে চূড়ান্ত স্কোর বুস্ট করা হয়।'
                );

                // Append the formatted teacher table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Teacher\'s Performance Summary Table (Ranking & Workload)</h3>';
                html += renderTeacherTable(data);
                html += '</div>';

                return html;
            }
            if (sectionId === 'class-report') {
                let html = '<h1>Class Performance Report (শ্রেণিভিত্তিক পারফরম্যান্স)</h1>';
                html += '<div id="custom-class-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_class_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => {
                        const target = document.getElementById('custom-class-view');
                        if (target) target.innerHTML = h;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'শ্রেণিভিত্তিক পারফরম্যান্স সূচক, পরিমাপ পদ্ধতি ও গণনার সূত্র (Class Performance Guide)',
                    [
                        {
                            term: 'CPI Score (Class Performance Index - সামগ্রিক পারফরম্যান্স)',
                            desc: 'একটি শ্রেণি ও শাখার সকল বিষয়ের সমন্বিত ফলাফল মানদণ্ড (যার ভিত্তিতে Class Rank নির্ধারিত হয়)।',
                            formula: '(গড় নম্বর % × ০.৫০) + (পাসের হার % × ০.৩০) + (৭০%+ উৎকর্ষ হার % × ০.২০) [ধাপ ৪]'
                        },
                        {
                            term: 'Difficulty Factor / CDF (শ্রেণি কাঠিন্য ও দুর্বলতা মাত্রা)',
                            desc: 'শ্রেণির সামগ্রিক দুর্বলতা ও পাঠদানের চ্যালেঞ্জের মাত্রা (মান বেশি হলে শ্রেণিটি তুলনামূলক দুর্বল বা চ্যালেঞ্জিং)।',
                            formula: '(গড় নম্বরের ঘাটতি % × ০.৫০) + (ফেলের হার % × ০.৩০) + (ভ্যারিয়েন্স × ০.২০) [ধাপ ২]'
                        },
                        {
                            term: 'TII (Teacher Impact Index - শিক্ষক বুস্ট গুণক)',
                            desc: 'এই শ্রেণিতে পাঠদানকারী শিক্ষকের পরিশ্রমের জন্য প্রাপ্ত বুস্ট গুণক।',
                            formula: '১ + (১০০ - শ্রেণির গড় নম্বর %) ÷ ১০০ [ধাপ ২]'
                        },
                        {
                            term: 'Gender Performance & Ratio (লিঙ্গভিত্তিক পরিসংখ্যান)',
                            desc: 'শ্রেণিতে ছাত্র (Male) ও ছাত্রী (Female) এর অন্তর্ভুক্তি সংখ্যা, অনুপাত এবং অর্জিত গড় নম্বর।'
                        },
                        {
                            term: 'SD & Variance (নম্বরের তারতম্য ও বিস্তার)',
                            desc: 'শ্রেণির বিভিন্ন বিষয়ে শিক্ষার্থীদের নম্বরের সামঞ্জস্যতা ও আদর্শ বিচ্যুতি (কম মান সুষম ফলাফল নির্দেশ করে)।'
                        },
                        {
                            term: 'Class Rank (শ্রেণি র‍্যাঙ্ক)',
                            desc: 'প্রতিষ্ঠানের সকল শ্রেণি ও শাখার মধ্যে CPI স্কোরের ভিত্তিতে তুলনামূলক অবস্থান (#1, #2...)।',
                            formula: 'RANK() OVER (ORDER BY CPI DESC) [ধাপ ৪]'
                        }
                    ],
                    'পরামর্শ: যেসব শ্রেণির Difficulty Factor (CDF) বেশি ও CPI কম, সেগুলোতে বিশেষ নজরদারি, শিখন ঘাটতি পূরণ ও অভিজ্ঞ শিক্ষক নিয়োগ করা বাঞ্ছনীয়।'
                );

                // Append the formatted class table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Class & Section Performance Summary Table (Ranking & Analysis)</h3>';
                html += renderClassTable(data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'overall-subject-report') {
                let html = '<h1>Overall Subject Performance (সামগ্রিক বিষয়ভিত্তিক পারফরম্যান্স)</h1>';
                html += '<div id="custom-overall-subject-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_overall_subject_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => {
                        const target = document.getElementById('custom-overall-subject-view');
                        if (target) target.innerHTML = h;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'সামগ্রিক বিষয়ভিত্তিক পারফরম্যান্স সূচক, পরিমাপ পদ্ধতি ও গণনার সূত্র (Overall Subject Guide)',
                    [
                        {
                            term: 'Overall Avg. Marks (সার্বিক গড় নম্বর %)',
                            desc: 'সকল শ্রেণি-শাখা মিলিয়ে প্রতিষ্ঠানে ওই বিষয়ের সামগ্রিক প্রাপ্ত নম্বরের গড় শতকরা হার।',
                            formula: 'মোট প্রাপ্ত নম্বর ÷ মোট পূর্ণমান × ১০০ [ধাপ ৩]'
                        },
                        {
                            term: 'Pass Rate & Exc. Rate (পাস ও উৎকর্ষ হার %)',
                            desc: 'পুরো প্রতিষ্ঠানে ওই বিষয়ে পাস এবং ৭০%+ নম্বর পাওয়া শিক্ষার্থীদের শতকরা অনুপাত।'
                        },
                        {
                            term: 'SDF (Subject Difficulty Factor - বিষয় কাঠিন্য মাত্রা)',
                            desc: 'বিষয়টির সার্বিক কাঠিন্য সূচক (SDF মান যত বেশি, শিক্ষার্থীদের কাছে বিষয়টি তত বেশি কঠিন বা চ্যালেঞ্জিং)।',
                            formula: '(ফেলের হার % × ০.৩৫) + (মিডিয়ান ঘাটতি % × ০.২৫) + (CV বিচ্যুতি % × ০.২৫) + (৫০% কম পাওয়া শিক্ষার্থীর হার % × ০.১৫) [ধাপ ৬]'
                        },
                        {
                            term: 'CV (Coefficient of Variation - আপেক্ষিক বিচ্যুতি)',
                            desc: 'নম্বরের মানের তারতম্য ও বিচ্যুতির তুলনামূলক মাত্রা।',
                            formula: '(Std. Deviation ÷ Overall Avg Marks) × ১০০ [ধাপ ৬]'
                        },
                        {
                            term: 'Gender Performance & Ratio (লিঙ্গভিত্তিক পরিসংখ্যান)',
                            desc: 'বিষয়টিতে ছাত্র (Male) ও ছাত্রী (Female) এর অংশগ্রহণ সংখ্যা, অনুপাত এবং অর্জিত গড় নম্বর।'
                        },
                        {
                            term: 'SD & Variance (নম্বরের বিস্তার ও বৈষম্য)',
                            desc: 'বিষয়টিতে শিক্ষার্থীদের প্রাপ্ত নম্বরের ধারাবাহিকতা ও বৈষম্যের পরিমাপ (কম মান সুষম ফলাফল নির্দেশ করে)।'
                        }
                    ],
                    'পরামর্শ: যেসব বিষয়ের SDF সূচক বেশি (বিশেষ করে ৪০+), সেগুলোতে দুর্বল শিক্ষার্থীদের চিহ্নিত করে বিশেষ প্রতিকারমূলক পাঠদান (Remedial Teaching) নিশ্চিত করা উচিত।'
                );

                // Append the formatted overall subject table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Overall Subject Performance Summary Table (Difficulty & Breakdown)</h3>';
                html += renderOverallSubjectTable(data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'student-report') {
                let html = '<h1>Student Merit List (শিক্ষার্থীদের মেধাতালিকা)</h1>';
                html += '<div id="custom-student-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_student_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => {
                        const target = document.getElementById('custom-student-view');
                        if (target) target.innerHTML = h;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'শিক্ষার্থীদের মেধাতালিকা, গ্রেডিং পদ্ধতি ও গণনার সূত্র (Student Merit Guide)',
                    [
                        {
                            term: 'Class Rank (শ্রেণি র‍্যাঙ্ক)',
                            desc: 'পুরো শ্রেণিতে (সকল শাখা মিলিয়ে) মোট প্রাপ্ত নম্বর ও জিপিএ অনুযায়ী শিক্ষার্থীর অবস্থান।',
                            formula: 'প্রাপ্ত নম্বরের ক্রম ও জিপিএ সমতা বিচার [ধাপ ৯]'
                        },
                        {
                            term: 'Section Rank (শাখা র‍্যাঙ্ক)',
                            desc: 'শিক্ষার্থীর নিজস্ব শাখায় (Section) তার মেধা অবস্থান।',
                            formula: 'শাখায় প্রাপ্ত নম্বর ও জিপিএ এর ক্রম [ধাপ ৯]'
                        },
                        {
                            term: 'GPA (Grade Point Average) ও Grade',
                            desc: 'আবশ্যিক বিষয়সমূহ এবং ৪র্থ বিষয়ের অতিরিক্ত পয়েন্ট (২.০ এর অতিরিক্ত) সমন্বয়ে অর্জিত চূড়ান্ত GPA ও গ্রেড।',
                            formula: '(আবশ্যিক বিষয়ের মোট পয়েন্ট + ৪র্থ বিষয়ের অতিরিক্ত পয়েন্ট) ÷ আবশ্যিক বিষয়ের সংখ্যা [ধাপ ১১]'
                        },
                        {
                            term: 'Failed Subjects (ফেল বিষয়ের তালিকা)',
                            desc: 'যেকোনো বিষয়ে ৩৩% এর কম নম্বর পেলে অকৃতকার্য হিসেবে গণ্য হয়। কোনো একটি আবশ্যিক বিষয়ে ফেল থাকলে চূড়ান্ত জিপিএ ০.০০ (F) নির্ধারিত হয় [ধাপ ১১]।'
                        },
                        {
                            term: 'Risk Score (SRS - ঝুঁকি স্কোর)',
                            desc: 'অকৃতকার্য শিক্ষার্থীদের ক্ষেত্রে বিষয়ের কাঠিন্য (SDF), নম্বরের ঘাটতি এবং শিক্ষক পারফরম্যান্স সমন্বিত ঝুঁকি মান [ধাপ ১৩]।'
                        }
                    ],
                    'গ্রেডিং স্কেল: ৮০-১০০: A+ (৫.০), ৭০-৭৯: A (৪.০), ৬০-৬৯: A- (৩.৫), ৫০-৫৯: B (৩.০), ৪০-৪৯: C (২.০), ৩৩-৩৯: D (১.০), ০-৩২: F (০.০)। ৪র্থ বিষয়ের ২.০ এর অতিরিক্ত পয়েন্ট মূল পয়েন্টের সাথে যোগ হয় (সর্বোচ্চ জিপিএ ৫.০)।'
                );

                // Append the formatted student merit table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>Student Merit List (Class & Section Grouped)</h3>';
                html += renderStudentMeritTable(data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'at-risk-students-report') {
                let html = '<h1>At-Risk Students Report (ঝুঁকিপূর্ণ শিক্ষার্থী)</h1>';
                html += '<div id="custom-at-risk-view" class="custom-graphical-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_at_risk_students_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => {
                        const target = document.getElementById('custom-at-risk-view');
                        if (target) target.innerHTML = h;
                    });

                // Custom explanation card
                html += renderExplanationCard(
                    'ঝুঁকিপূর্ণ শিক্ষার্থী শনাক্তকরণ, ঝুঁকি সূচক (SRS) ও গণনার সূত্র (At-Risk Assessment Guide)',
                    [
                        {
                            term: 'SRS / Risk Score (শিক্ষার্থী ঝুঁকি সূচক)',
                            desc: 'শিক্ষার্থীর অকৃতকার্য বিষয়গুলোর কাঠিন্য (SDF), নম্বরের ঘাটতি এবং সংশ্লিষ্ট বিষয়ের শিক্ষকের পারফরম্যান্স (TSPI) সমন্বয় করে নির্ণীত সম্ভাব্য চূড়ান্ত ব্যর্থতার ঝুঁকি মাত্রা।',
                            formula: '∑ [ {(বিষয়ের SDF × ০.৬০) + (পাস নম্বরের ঘাটতি % × ০.৪০)} × {১ + ((৫০ - শিক্ষকের TSPI) ÷ ১০০)} ] [ধাপ ১৩]'
                        },
                        {
                            term: 'Risk Severity (ঝুঁকির মাত্রা)',
                            desc: 'SRS ৬০+ হলে Critical High Risk (গুরুতর ঝুঁকি), ৩৫-৫৯ হলে Moderate Risk (মাঝারি ঝুঁকি), এবং ২৫-৩৪ হলে Borderline Risk।'
                        },
                        {
                            term: 'Selection Criteria (তালিকাভুক্তি মানদণ্ড)',
                            desc: 'যেসব শিক্ষার্থী এক বা একাধিক বিষয়ে ফেল করেছে এবং যাদের Risk Score ২৫ এর বেশি [ধাপ ১০]।'
                        },
                        {
                            term: 'Failed Subjects (ফেল বিষয়ের তালিকা)',
                            desc: 'যেসব বিষয়ে শিক্ষার্থী ৩৩% এর কম নম্বর পেয়ে অকৃতকার্য হয়েছে সেগুলোর তালিকা ও বিষয়ের সংখ্যা [ধাপ ১০]।'
                        }
                    ],
                    'পদক্ষেপ: Risk Score প্রাপ্ত শিক্ষার্থীদের চিহ্নিত করে দ্রুত অভিভাবকের সাথে যোগাযোগ (Guardian Contact), শিখন ঘাটতি নিরূপণ ও নিবিড় প্রতিকারমূলক পাঠদান (Remedial Coaching) প্রদান করা আবশ্যক।'
                );

                // Append the formatted at-risk table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 mb-3 text-muted"><i class="bi bi-table me-2"></i>At-Risk Students Actionable Summary Table</h3>';
                html += renderAtRiskTable(data);
                html += '</div>';
                return html;
            }
            return `<h2>${sectionId.replace(/-/g, ' ')}</h2><p>Rendering not implemented.</p>`;
        }

        // Load all reports sequentially
        (async () => {
            for (const section of reportSections) {
                await loadReport(section);
            }
        })();
    });
</script>

<?php require_once 'footer.php'; ?>