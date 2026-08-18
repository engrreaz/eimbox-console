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
    $percentage = ($value / $max_value) * 100;
    return "<div class='progress' style='height: 18px; background-color: #e9ecef; width: 100px;'>
                <div class='progress-bar {$color_class}' role='progressbar' style='width: {$percentage}%;' aria-valuenow='{$value}' aria-valuemin='0' aria-valuemax='{$max_value}'>
                    <span style='font-size: 10px; color: white;'>" . number_format($value, 2) . "</span>
                </div>
            </div>";
}
require_once 'header.php';
?>
<style>
    @media print {
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
            border: none !important;
            box-shadow: none !important;
        }

        body * {
            visibility: hidden;
        }

        #main-report-block,
        #main-report-block * {
            visibility: visible;
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

        h1, h2, h3 {
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
            <div>
                <a href="analytics-exam-report.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>
                    Back</a>
                <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>
                    Print</button>
                <!-- PDF download button can be added later if needed -->
            </div>
        </div>
        <div class="card-body" id="main-report-block">
            <!-- Placeholders for each report section -->
            <div id="institute-report" class="report-section"></div>
            <div class="page-break no-print"></div>
            <div id="teacher-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="class-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="subject-report" class="report-section"></div>
            <div class="page-break"></div>
            <div id="student-report" class="report-section"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const datasetId = <?= json_encode($dataset_id) ?>;
        const loadingHTML = `<div class="loading-placeholder"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

        const reportSections = [
            { id: 'institute-report', endpoint: 'get_institute_report.php' },
            { id: 'teacher-report', endpoint: 'get_teacher_report.php' },
            { id: 'class-report', endpoint: 'get_class_report.php' },
            { id: 'subject-report', endpoint: 'get_subject_report.php' },
            { id: 'student-report', endpoint: 'get_student_report.php' }
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
                    // This is a simplified renderer. You would replace this with
                    // a function that generates the correct HTML for each report type.
                    container.innerHTML = renderReport(section.id, result.data);
                } else {
                    throw new Error(result.message || 'API returned an error.');
                }
            } catch (error) {
                container.innerHTML = `<div class="alert alert-danger">Failed to load ${section.id}: ${error.message}</div>`;
            }
        }

        function create_bar_html(value, max_value = 100, color_class = 'bg-primary') {
            const percentage = (value / max_value) * 100;
            return `<div class='progress' style='height: 18px; background-color: #e9ecef; width: 100px;'>
                        <div class='progress-bar ${color_class}' role='progressbar' style='width: ${percentage}%;' aria-valuenow='${value}' aria-valuemin='0' aria-valuemax='${max_value}'>
                            <span style='font-size: 10px; color: white;'>${parseFloat(value).toFixed(2)}</span>
                        </div>
                    </div>`;
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
                return html;
            }
            if (sectionId === 'teacher-report') {
                let html = '<h1>Teacher\'s Performance Report (শিক্ষকদের পারফরম্যান্স)</h1>';
                html += '<table class="table table-bordered table-sm"><thead><tr><th>Rank</th><th>Teacher</th><th>Avg. Marks</th><th>Pass Rate</th><th>TPI</th><th>TIA</th><th>TCI</th><th>TSI</th></tr></thead><tbody>';
                (data || []).forEach((teacher, index) => {
                    html += `<tr>
                                <td>${index + 1}</td>
                                <td>${teacher.tname}<br><small>${teacher.position || ''}</small></td>
                                <td>${create_bar_html(teacher.overall_avg_marks)}</td>
                                <td>${create_bar_html(teacher.overall_pass_rate)}</td>
                                <td>${parseFloat(teacher.tpi).toFixed(2)}</td>
                                <td style='font-weight: bold;'>${parseFloat(teacher.tia).toFixed(2)}</td>
                                <td class='${teacher.tci_score > 0 ? 'text-success' : 'text-danger'}'>${parseFloat(teacher.tci_score).toFixed(2)}</td>
                                <td class='${teacher.tsi_score > 0 ? 'text-success' : 'text-danger'}'>${parseFloat(teacher.tsi_score).toFixed(2)}</td>
                              </tr>`;
                });
                html += '</tbody></table>';
                return html;
            }
            if (sectionId === 'class-report') {
                let html = '<h1>Class Performance Report (শ্রেণিভিত্তিক পারফরম্যান্স)</h1>';
                html += '<table class="table table-bordered table-sm"><thead><tr><th>Rank</th><th>Class</th><th>Students</th><th>Avg. Marks</th><th>CPI Score</th><th>Difficulty (CDF)</th></tr></thead><tbody>';
                (data || []).forEach(cls => {
                    html += `<tr>
                                <td>${cls.class_rank}</td>
                                <td>${cls.classname} - ${cls.sectionname}</td>
                                <td>${cls.total_students_appeared}</td>
                                <td>${create_bar_html(cls.overall_marks_percentage)}</td>
                                <td style='font-weight: bold;'>${parseFloat(cls.cpi_score).toFixed(2)}</td>
                                <td>${create_bar_html(cls.difficulty_factor, 100, 'bg-danger')}</td>
                              </tr>`;
                });
                html += '</tbody></table>';
                return html;
            }
            if (sectionId === 'subject-report') {
                let html = '<h1>Subject Performance Report (বিষয়ভিত্তিক পারফরম্যান্স)</h1>';
                html += '<table class="table table-bordered table-sm"><thead><tr><th>Subject</th><th>Students</th><th>Avg. Marks</th><th>Pass %</th><th>Fail %</th><th>Difficulty (SDF)</th></tr></thead><tbody>';
                (data || []).forEach(subject => {
                    const pass_rate = 100 - subject.failure_rate;
                    html += `<tr>
                                <td>${subject.subject_name}</td>
                                <td>${subject.total_students_appeared}</td>
                                <td>${create_bar_html(subject.overall_marks_percentage)}</td>
                                <td class='text-success'>${parseFloat(pass_rate).toFixed(2)}%</td>
                                <td class='text-danger'>${parseFloat(subject.failure_rate).toFixed(2)}%</td>
                                <td>${create_bar_html(subject.sdf, 100, 'bg-warning')}</td>
                              </tr>`;
                });
                html += '</tbody></table>';
                return html;
            }
            if (sectionId === 'student-report') {
                let html = '<h1>Student Merit List (শিক্ষার্থীদের মেধাতালিকা)</h1>';
                html += '<table class="table table-bordered table-sm"><thead><tr><th>Rank</th><th>Student</th><th>Class</th><th>Roll</th><th>Total Marks</th><th>Percentage</th><th>GPA</th><th>Grade</th></tr></thead><tbody>';
                (data || []).forEach(student => {
                    const is_fail = student.failed_subjects > 0;
                    const rank = is_fail ? 'F' : student.class_rank;
                    const row_class = is_fail ? "class='table-danger'" : "";
                    html += `<tr ${row_class}>
                                <td style='font-weight: bold;'>${rank}</td>
                                <td>${student.stnameeng}</td>
                                <td>${student.classname} - ${student.sectionname}</td>
                                <td>${student.rollno}</td>
                                <td>${parseFloat(student.total_marks_obtained).toFixed(2)}</td>
                                <td>${parseFloat(student.percentage).toFixed(2)}%</td>
                                <td>${parseFloat(student.gpa).toFixed(2)}</td>
                                <td>${student.grade}</td>
                              </tr>`;
                });
                html += '</tbody></table>';
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