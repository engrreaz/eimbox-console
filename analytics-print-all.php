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

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
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
            page-break-before: always;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important;
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

        .no-page-breaks .page-break {
            display: none !important;
            page-break-before: auto !important;
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
                <div class="page-break"></div>
            </div>

            <div class="report-section-container" id="container-detailed-subject-report" data-section="detailed-subject-report">
                <div id="detailed-subject-report" class="report-section"></div>
                <div class="page-break"></div>
            </div>

            <div class="report-section-container" id="container-teacher-report" data-section="teacher-report">
                <div id="teacher-report" class="report-section"></div>
                <div class="page-break"></div>
            </div>

            <div class="report-section-container" id="container-class-report" data-section="class-report">
                <div id="class-report" class="report-section"></div>
                <div class="page-break"></div>
            </div>

            <div class="report-section-container" id="container-overall-subject-report" data-section="overall-subject-report">
                <div id="overall-subject-report" class="report-section"></div>
                <div class="page-break"></div>
            </div>

            <div class="report-section-container" id="container-student-report" data-section="student-report">
                <div id="student-report" class="report-section"></div>
                <div class="page-break"></div>
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
                const summary = data.summary || {};
                html += `
                        <div class='row g-3 mb-4 stat-summary-cards custom-graphical-view'>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Total Students</h4><p style='font-size: 24px;'>${summary.total_students || 0}</p></div></div></div>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Pass Rate</h4><p style='font-size: 24px;'>${parseFloat(summary.pass_rate || 0).toFixed(2)}%</p></div></div></div>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Average Marks</h4><p style='font-size: 24px;'>${parseFloat(summary.overall_avg_marks_percentage || 0).toFixed(2)}%</p></div></div></div>
                        </div>`;

                html += "<div class='row g-3'><div class='col-md-6'><h3>Grade Distribution</h3>";
                html += "<table class='table table-sm table-bordered table-striped'><thead><tr><th>Grade</th><th>Number of Students</th><th>Percentage</th></tr></thead><tbody>";
                const total_students = summary.total_students > 0 ? summary.total_students : 1;
                (data.grade_distribution || []).forEach(grade => {
                    const percentage = (grade.student_count / total_students) * 100;
                    html += `<tr><td>${grade.grade}</td><td>${grade.student_count}</td><td>${create_bar_html(percentage)}</td></tr>`;
                });
                html += "</tbody></table></div>";

                html += "<div class='col-md-6'><h3>Weakest Subjects</h3>";
                html += "<table class='table table-sm table-bordered table-striped'><thead><tr><th>Subject</th><th>Failure Rate</th></tr></thead><tbody>";
                (data.weakest_subjects || []).forEach(subject => {
                    html += `<tr><td>${subject.subject_name}</td><td class='text-danger fw-bold'>${parseFloat(subject.failure_rate).toFixed(2)}%</td></tr>`;
                });
                html += "</tbody></table></div></div>";

                html += renderExplanationCard(
                    'প্রতিষ্ঠানের পারফরম্যান্স নির্দেশিকা ও গণনার ব্যাখ্যা (Institute Performance Guide)',
                    [
                        {
                            term: 'Total Students (মোট শিক্ষার্থী)',
                            desc: 'পরীক্ষায় অংশগ্রহণকারী অনন্য (Unique) শিক্ষার্থীর সর্বমোট সংখ্যা।'
                        },
                        {
                            term: 'Pass Rate (পাসের হার %)',
                            desc: 'সকল বিষয়ে উত্তীর্ণ পরীক্ষার্থীদের সামগ্রিক শতকরা হার।',
                            formula: '(মোট পাস শিক্ষার্থী ÷ মোট অংশগ্রহণকারী) × ১০০'
                        },
                        {
                            term: 'Average Marks (গড় প্রাপ্ত নম্বর %)',
                            desc: 'প্রতিষ্ঠানের সকল শিক্ষার্থীর প্রাপ্ত নম্বরের শতকরা গড় মান।',
                            formula: '(সর্বমোট প্রাপ্ত নম্বর ÷ সর্বমোট পূর্ণমান) × ১০০'
                        },
                        {
                            term: 'Grade Distribution (গ্রেড বণ্টন)',
                            desc: 'শিক্ষা বোর্ডের মানদণ্ড অনুযায়ী অর্জিত GPA এর ভিত্তিতে বিভিন্ন লেটার গ্রেডে (A+, A, A-, B, C, D, F) শিক্ষার্থীদের বিন্যাস।'
                        },
                        {
                            term: 'Weakest Subjects (দুর্বল বিষয়সমূহ)',
                            desc: 'যেসব বিষয়ে ফেলের হার (Failure Rate) সবচেয়ে বেশি, সেগুলোর অগ্রাধিকার তালিকা।'
                        }
                    ],
                    'পরামর্শ: দুর্বল বিষয়গুলোতে অতিরিক্ত ক্লাস ও নিবিড় নজরদারি নিশ্চিত করলে প্রতিষ্ঠানের সার্বিক ফলাফল দ্রুত উন্নত হবে।'
                );

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
                            term: 'Excellent Rate (A+ হার %)',
                            desc: 'সংশ্লিষ্ট বিষয়ে ৮০% বা তদূর্ধ্ব নম্বর প্রাপ্ত শিক্ষার্থীদের শতকরা হার।',
                            formula: '(৮০%+ পাওয়া শিক্ষার্থী ÷ অংশ নেওয়া শিক্ষার্থী) × ১০০ [ধাপ ১]'
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
                        },
                        {
                            term: 'Score Range (নম্বর পরিসর)',
                            desc: 'সর্বোচ্চ (Max) ও সর্বনিম্ন (Min) নম্বরের ব্যবধান।',
                            formula: 'সর্বোচ্চ নম্বর - সর্বনিম্ন নম্বর [ধাপ ১]'
                        },
                        {
                            term: 'Variance & Std. Dev. (বিস্তার ও বিচ্যুতি)',
                            desc: 'শিক্ষার্থীদের নম্বরের ধারাবাহিকতা। কম মান সমমানের পারফরম্যান্স ও বেশি মান নম্বরের বড় তারতম্য নির্দেশ করে।'
                        }
                    ],
                    'পরামর্শ: যেসব বিষয়ে CDI বেশি ও TSPI কম, সেগুলোতে পাঠদান পদ্ধতি ও শিক্ষার্থীদের শিখন ঘাটতি নিবিড়ভাবে পর্যালোচনা করা প্রয়োজন।'
                );

                // Append the raw generic table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Raw Data Table (Detailed Subject Reference)</h3>';
                html += renderGenericTable('', data);
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
                    'শিক্ষক মূল্যায়ন সূচক ও পরিমাপ পদ্ধতি (Teacher Evaluation Guide)',
                    [
                        {
                            term: 'TPI (Teacher Performance Index - বেস স্কোর)',
                            desc: 'শিক্ষকের পাঠদানে শিক্ষার্থীদের সরাসরি ফলাফলের ওয়েটেড বেস স্কোর।',
                            formula: '(পাসের হার % × ০.৪০) + (A+ এর হার % × ০.২৫) + (গড় নম্বর % × ০.৩৫) [ধাপ ৮]'
                        },
                        {
                            term: 'TII (Teacher Impact Index - প্রভাব সূচক)',
                            desc: 'শিক্ষক যে শ্রেণিতে পড়ান তার চ্যালেঞ্জ বা কাঠিন্যের প্রভাব সমন্বয়ক (কঠিন শ্রেণিতে পড়ানো শিক্ষকের TII বেশি থাকে)।',
                            formula: '১ + (১০০ - ক্লাসের গড় নম্বর %) ÷ ১০০ [ধাপ ৫]'
                        },
                        {
                            term: 'TIA (Teacher Impact Adjustment - চূড়ান্ত স্কোর)',
                            desc: 'শিক্ষকের প্রকৃত মূল্যায়ন ও চূড়ান্ত স্কোর (এই স্কোরের ক্রমানুযায়ী Teacher Rank নির্ধারিত হয়)।',
                            formula: 'TPI × TII (কাঠিন্য সমন্বিত স্কোর) [ধাপ ৮]'
                        },
                        {
                            term: 'TCI (Teacher Class Impact - শ্রেণি প্রভাব)',
                            desc: 'শ্রেণির অন্যান্য বিষয়ের সামগ্রিক গড়ের তুলনায় এই শিক্ষকের বিষয়ের গড়ের পার্থক্য (+ মান হলে ক্লাসের চেয়ে ভালো)।',
                            formula: 'AVG(শিক্ষকের বিষয়ের গড় % - শ্রেণির সার্বিক গড় %) [ধাপ ১২]'
                        },
                        {
                            term: 'TSI (Teacher Subject Impact - বিষয় প্রভাব)',
                            desc: 'প্রতিষ্ঠানের একই বিষয়ের সামগ্রিক গড়ের তুলনায় এই শিক্ষকের সেকশনের গড়ের পার্থক্য (+ মান হলে সার্বিক গড়ের চেয়ে ভালো)।',
                            formula: 'AVG(শিক্ষকের বিষয়ের গড় % - প্রতিষ্ঠানের ওই বিষয়ের সার্বিক গড় %) [ধাপ ১২]'
                        },
                        {
                            term: 'Teacher Rank (শিক্ষক র‍্যাঙ্ক)',
                            desc: 'প্রতিষ্ঠানের সকল শিক্ষকদের মধ্যে TIA স্কোরের ক্রমানুযায়ী শিক্ষকের অবস্থান।'
                        }
                    ],
                    'ব্যাখ্যা: দুর্বল বা কঠিন শ্রেণিতে পাঠদানকারী শিক্ষকের পরিশ্রমের সুবিচার করতে TIA স্কোরে ক্লাসের কাঠিন্যকে বুস্ট ফ্যাক্টর হিসেবে সমন্বয় করা হয়।'
                );

                // Append the raw generic table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Raw Data Table (Teacher Performance Reference)</h3>';
                html += renderGenericTable('', data);
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
                    'শ্রেণিভিত্তিক পারফরম্যান্স সূচক নির্দেশিকা (Class Performance Guide)',
                    [
                        {
                            term: 'CPI Score (Class Performance Index)',
                            desc: 'একটি শ্রেণি ও শাখার সার্বিক ফলাফলের সমন্বিত পারফরম্যান্স স্কোর।',
                            formula: '(গড় নম্বর % × ০.৫০) + (পাসের হার % × ০.৩০) + (A+ এর হার % × ০.২০) [ধাপ ৪]'
                        },
                        {
                            term: 'Difficulty Factor / CDF (শ্রেণি কাঠিন্য মাত্রা)',
                            desc: 'শ্রেণির সামগ্রিক দুর্বলতা ও পাঠদানের চ্যালেঞ্জের মাত্রা (মান বেশি হলে শ্রেণিটি তুলনামূলক দুর্বল বা চ্যালেঞ্জিং)।',
                            formula: '(গড় নম্বরের ঘাটতি % × ০.৫০) + (ফেলের হার % × ০.৩০) + (ভ্যারিয়েন্স × ০.২০) [ধাপ ২]'
                        },
                        {
                            term: 'Students Appeared (অংশগ্রহণকারী)',
                            desc: 'ওই শ্রেণি ও শাখার পরীক্ষায় অংশ নেওয়া মোট শিক্ষার্থীর সংখ্যা (ধাপ ২)।'
                        },
                        {
                            term: 'Class Rank (শ্রেণি র‍্যাঙ্ক)',
                            desc: 'প্রতিষ্ঠানের সকল শ্রেণি ও শাখার মধ্যে CPI স্কোরের ভিত্তিতে তুলনামূলক অবস্থান (ধাপ ৪)।'
                        }
                    ],
                    'পরামর্শ: যেসব শ্রেণির Difficulty Factor বেশি ও CPI কম, সেগুলোতে অভিজ্ঞ শিক্ষক নিয়োগ ও বিশেষ ক্লাসের ব্যবস্থা রাখা উচিত।'
                );

                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Raw Data Table (Class Performance Reference)</h3>';
                html += renderGenericTable('', data);
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
                    'সামগ্রিক বিষয়ভিত্তিক পারফরম্যান্স নির্দেশিকা (Overall Subject Guide)',
                    [
                        {
                            term: 'Overall Avg. Marks (সার্বিক গড় নম্বর %)',
                            desc: 'সকল শ্রেণি-শাখা মিলিয়ে প্রতিষ্ঠানে ওই বিষয়ের সামগ্রিক প্রাপ্ত নম্বরের গড় শতকরা হার (ধাপ ৩)।'
                        },
                        {
                            term: 'Pass Rate / Failure Rate (পাস ও ফেলের হার %)',
                            desc: 'পুরো প্রতিষ্ঠানে ওই বিষয়ে উত্তীর্ণ ও অকৃতকার্য শিক্ষার্থীদের শতকরা অনুপাত (ধাপ ৩)।'
                        },
                        {
                            term: 'SDF (Subject Difficulty Factor - বিষয় কাঠিন্য মাত্রা)',
                            desc: 'বিষয়টির সার্বিক কাঠিন্য সূচক (SDF মান যত বেশি, শিক্ষার্থীদের কাছে বিষয়টি তত বেশি কঠিন ও ভীতিকর)।',
                            formula: '(ফেলের হার % × ০.৩৫) + (মিডিয়ান ঘাটতি % × ০.২৫) + (CV বিচ্যুতি % × ০.২৫) + (৫০% কম পাওয়া ছাত্রের হার % × ০.১৫) [ধাপ ৬]'
                        }
                    ],
                    'ব্যাখ্যা: SDF সূত্রে CV (Coefficient of Variation) হলো (Std. Dev ÷ Avg Marks) × ১০০ এবং মিডিয়ান ঘাটতি হলো (১০০ - Median %)। উচ্চ SDF যুক্ত বিষয়গুলোতে বিশেষ শিখন সহায়তা প্রয়োজন।'
                );

                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Raw Data Table (Overall Subject Reference)</h3>';
                html += renderGenericTable('', data);
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
                    'শিক্ষার্থীদের মেধাতালিকা ও গ্রেডিং নির্দেশিকা (Student Merit Guide)',
                    [
                        {
                            term: 'Class Rank (শ্রেণি র‍্যাঙ্ক)',
                            desc: 'পুরো শ্রেণিতে (সকল শাখা মিলিয়ে) মোট প্রাপ্ত নম্বর ও জিপিএ অনুযায়ী শিক্ষার্থীর অবস্থান (ধাপ ৯)।'
                        },
                        {
                            term: 'Section Rank (শাখা র‍্যাঙ্ক)',
                            desc: 'শিক্ষার্থীর নিজস্ব শাখায় (Section) তার মেধাক্রম অবস্থান (ধাপ ৯)।'
                        },
                        {
                            term: 'GPA (Grade Point Average) ও Grade',
                            desc: 'আবশ্যিক বিষয়সমূহ এবং ৪র্থ বিষয়ের অতিরিক্ত পয়েন্ট (২.০ এর অতিরিক্ত) সমন্বয়ে অর্জিত চূড়ান্ত GPA ও গ্রেড।',
                            formula: '(আবশ্যিক বিষয়ের মোট পয়েন্ট + ৪র্থ বিষয়ের অতিরিক্ত পয়েন্ট) ÷ আবশ্যিক বিষয়ের সংখ্যা [ধাপ ১১]'
                        },
                        {
                            term: 'Failed Subjects (ফেল বিষয়ের সংখ্যা)',
                            desc: 'যেকোনো বিষয়ে ৩৩% এর কম নম্বর পেলে অকৃতকার্য হিসেবে গণ্য হয়। কোনো একটি আবশ্যিক বিষয়েও ফেল থাকলে চূড়ান্ত জিপিএ ০.০০ (F) নির্ধারিত হয় [ধাপ ১১]।'
                        }
                    ],
                    'গ্রেডিং স্কেল: ৮০-১০০: A+ (৫.০), ৭০-৭৯: A (৪.০), ৬০-৬৯: A- (৩.৫), ৫০-৫৯: B (৩.০), ৪০-৪৯: C (২.০), ৩৩-৩৯: D (১.০), ০-৩২: F (০.০)। ৪র্থ বিষয়ের ২.০ এর অতিরিক্ত পয়েন্ট মূল পয়েন্টের সাথে যোগ হয় (সর্বোচ্চ জিপিএ ৫.০)।'
                );

                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Full Merit List (for reference)</h3>';
                html += renderGenericTable('', data);
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
                    'ঝুঁকিপূর্ণ শিক্ষার্থী ও ঝুঁকি সূচক নির্দেশিকা (At-Risk Assessment Guide)',
                    [
                        {
                            term: 'SRS / Risk Score (শিক্ষার্থী ঝুঁকি সূচক)',
                            desc: 'শিক্ষার্থীর অকৃতকার্য বিষয়গুলোর কাঠিন্য (SDF), পাস নম্বরের ঘাটতি এবং সংশ্লিষ্ট বিষয়ের শিক্ষকের পারফরম্যান্স (TSPI) সমন্বয় করে নির্ণীত সম্ভাব্য চূড়ান্ত ব্যর্থতার ঝুঁকি মাত্রা।',
                            formula: '∑ [ {(বিষয়ের SDF × ০.৬০) + (পাস নম্বরের ঘাটতি % × ০.৪০)} × {১ + ((৫০ - শিক্ষকের TSPI) ÷ ১০০)} ] [ধাপ ১৩]'
                        },
                        {
                            term: 'মানদণ্ড (Selection Criteria)',
                            desc: 'যেসব শিক্ষার্থী এক বা একাধিক বিষয়ে ফেল করেছে এবং যাদের Risk Score ২৫ এর বেশি, তাদের এই তালিকায় আনা হয় [ধাপ ১০]।'
                        },
                        {
                            term: 'Failed Subjects (অকৃতকার্য বিষয়সমূহ)',
                            desc: 'যেসব বিষয়ে শিক্ষার্থী ৩৩% এর কম নম্বর পেয়ে অকৃতকার্য হয়েছে সেগুলোর তালিকা [ধাপ ১০]।'
                        },
                        {
                            term: 'Reason (ঝুঁকির কারণ)',
                            desc: 'শিক্ষার্থীর বর্তমান ব্যর্থতার সারসংক্ষেপ, যা দ্রুত প্রতিকারমূলক ব্যবস্থা গ্রহণে সাহায্য করে।'
                        }
                    ],
                    'পদক্ষেপ: Risk Score ২৫ এর বেশি হওয়া শিক্ষার্থীদের অবিলম্বে চিহ্নিত করে অভিভাবক সমাবেশ ও বিশেষ নিবিড় পাঠদান (Remedial Coaching) গ্রহণ করা আবশ্যক।'
                );

                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted"><i class="bi bi-table me-2"></i>Raw Data Table (At-Risk Students Reference)</h3>';
                html += renderGenericTable('', data);
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