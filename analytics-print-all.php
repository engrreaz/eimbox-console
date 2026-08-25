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

    .hide-reference-data .reference-data-block {
        display: none !important;
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

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .no-print {
            display: none !important;
        }
        body{
            background:white;
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

        .hide-reference-data .reference-data-block,
        .hide-reference-data .reference-data-block * {
            display: none !important;
            visibility: hidden !important;
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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center no-print">
            <h5 class="mb-0">Full Analytics Report</h5>
            <div class="d-flex gap-2">
                <a href="analytics-exam-report.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>
                    Back</a>
                <button type="button" id="toggleRefDataBtn" class="btn btn-outline-primary" title="Toggle Reference / Raw Data Tables">
                    <i class="bi bi-eye-slash me-1" id="toggleRefDataIcon"></i> Reference Data
                </button>
                <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>
                    Print</button>
                <!-- PDF download button can be added later if needed -->
            </div>
        </div>
        <div class="card-body" id="main-report-block">
            <!-- Placeholders for each report section -->
            <div id="institute-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="detailed-subject-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="teacher-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="class-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="overall-subject-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="student-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="at-risk-students-report" class="report-section"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const datasetId = <?= json_encode($dataset_id) ?>;
        const loadingHTML = `<div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

        // Toggle Reference / Raw Data Button Handler
        const toggleRefBtn = document.getElementById('toggleRefDataBtn');
        const toggleRefIcon = document.getElementById('toggleRefDataIcon');
        const reportBlock = document.getElementById('main-report-block');

        if (toggleRefBtn && reportBlock) {
            toggleRefBtn.addEventListener('click', function () {
                reportBlock.classList.toggle('hide-reference-data');
                const isHidden = reportBlock.classList.contains('hide-reference-data');
                if (isHidden) {
                    toggleRefBtn.classList.remove('btn-outline-primary');
                    toggleRefBtn.classList.add('btn-outline-secondary');
                    if (toggleRefIcon) {
                        toggleRefIcon.className = 'bi bi-eye me-1';
                    }
                    toggleRefBtn.setAttribute('title', 'Show Reference / Raw Data Tables');
                } else {
                    toggleRefBtn.classList.remove('btn-outline-secondary');
                    toggleRefBtn.classList.add('btn-outline-primary');
                    if (toggleRefIcon) {
                        toggleRefIcon.className = 'bi bi-eye-slash me-1';
                    }
                    toggleRefBtn.setAttribute('title', 'Hide Reference / Raw Data Tables');
                }
            });
        }

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
                return `<h1>${title}</h1><div class="alert alert-warning">No data available for this report.</div>`;
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
                        <div class='row g-3 mb-4'>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Total Students</h4><p style='font-size: 24px;'>${summary.total_students || 0}</p></div></div></div>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Pass Rate</h4><p style='font-size: 24px;'>${parseFloat(summary.pass_rate || 0).toFixed(2)}%</p></div></div></div>
                            <div class='col-md-4'><div class='card text-center'><div class='card-body'><h4>Average Marks</h4><p style='font-size: 24px;'>${parseFloat(summary.overall_avg_marks_percentage || 0).toFixed(2)}%</p></div></div></div>
                        </div>`;

                html += "<div class='row g-3'><div class='col-md-6'><h3>Grade Distribution</h3>";
                html += "<table class='table table-sm'><thead><tr><th>Grade</th><th>Number of Students</th><th>Percentage</th></tr></thead><tbody>";
                const total_students = summary.total_students > 0 ? summary.total_students : 1;
                (data.grade_distribution || []).forEach(grade => {
                    const percentage = (grade.student_count / total_students) * 100;
                    html += `<tr><td>${grade.grade}</td><td>${grade.student_count}</td><td>${create_bar_html(percentage)}</td></tr>`;
                });
                html += "</tbody></table></div>";

                html += "<div class='col-md-6'><h3>Weakest Subjects</h3>";
                html += "<table class='table table-sm'><thead><tr><th>Subject</th><th>Failure Rate</th></tr></thead><tbody>";
                (data.weakest_subjects || []).forEach(subject => {
                    html += `<tr><td>${subject.subject_name}</td><td class='text-danger'>${parseFloat(subject.failure_rate).toFixed(2)}%</td></tr>`;
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
                html += '<div id="custom-detailed-subject-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading Custom View...</span></div></div></div>';

                fetch(`analytics/display_detailed_subject_report.php?dataset_id=${datasetId}`)
                    .then(response => response.text())
                    .then(customHtml => {
                        document.getElementById('custom-detailed-subject-view').innerHTML = customHtml;
                    })
                    .catch(error => {
                        document.getElementById('custom-detailed-subject-view').innerHTML = `<div class="alert alert-danger">Failed to load custom view: ${error}</div>`;
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

                // Append the old generic table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted">Raw Data Table (for reference)</h3>';
                html += renderGenericTable('', data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'teacher-report') {
                let html = '<h1>Teacher\'s Performance Report (শিক্ষকদের পারফরম্যান্স)</h1>';

                // Placeholder for the custom view
                html += '<div id="custom-teacher-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading Custom View...</span></div></div></div>';

                fetch(`analytics/display_teacher_report.php?dataset_id=${datasetId}`)
                    .then(response => response.text())
                    .then(customHtml => {
                        document.getElementById('custom-teacher-view').innerHTML = customHtml;
                    })
                    .catch(error => {
                        document.getElementById('custom-teacher-view').innerHTML = `<div class="alert alert-danger">Failed to load custom view: ${error}</div>`;
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

                // Append the old generic table below it inside reference-data-block
                html += '<div class="reference-data-block">';
                html += '<h3 class="mt-5 text-muted">Raw Data Table (for reference)</h3>';
                html += renderGenericTable('', data);
                html += '</div>';

                return html;
            }
            if (sectionId === 'class-report') {
                let html = '<h1>Class Performance Report (শ্রেণিভিত্তিক পারফরম্যান্স)</h1>';
                html += '<div id="custom-class-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_class_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => document.getElementById('custom-class-view').innerHTML = h);

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
                html += '<h3 class="mt-5 text-muted">Raw Data Table (for reference)</h3>';
                html += renderGenericTable('', data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'overall-subject-report') {
                let html = '<h1>Overall Subject Performance (সামগ্রিক বিষয়ভিত্তিক পারফরম্যান্স)</h1>';
                html += '<div id="custom-overall-subject-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_overall_subject_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => document.getElementById('custom-overall-subject-view').innerHTML = h);

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
                html += '<h3 class="mt-5 text-muted">Raw Data Table (for reference)</h3>';
                html += renderGenericTable('', data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'student-report') {
                let html = '<h1>Student Merit List (শিক্ষার্থীদের মেধাতালিকা)</h1>';
                html += '<div id="custom-student-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_student_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => document.getElementById('custom-student-view').innerHTML = h);

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
                html += '<h3 class="mt-5 text-muted">Full Merit List (for reference)</h3>';
                html += renderGenericTable('', data);
                html += '</div>';
                return html;
            }
            if (sectionId === 'at-risk-students-report') {
                let html = '<h1>At-Risk Students Report (ঝুঁকিপূর্ণ শিক্ষার্থী)</h1>';
                html += '<div id="custom-at-risk-view"><div class="loading-placeholder"><div class="spinner-border text-primary" role="status"></div></div></div>';
                fetch(`analytics/display_at_risk_students_report.php?dataset_id=${datasetId}`)
                    .then(r => r.text()).then(h => document.getElementById('custom-at-risk-view').innerHTML = h);

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
                html += '<h3 class="mt-5 text-muted">Raw Data Table (for reference)</h3>';
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