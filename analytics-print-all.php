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

        .page-break {
            page-break-before: always;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
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
        <div class="card-body">
            <!-- Placeholders for each report section -->
            <div id="institute-report" class="report-section"></div>
            <div class="page-break"></div>
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
                let html = '<h1>Institute Performance Overview</h1>';
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
            // অন্যান্য রিপোর্টের জন্য রেন্ডারিং ফাংশন এখানে যোগ করা হবে
            return `<h2>${sectionId.replace('-', ' ')}</h2><pre>${JSON.stringify(data, null, 2)}</pre>`;
        }

        // শুধুমাত্র প্রথম রিপোর্টটি লোড করা হচ্ছে
        if (reportSections.length > 0) {
            loadReport(reportSections[0]);
        }
    });
</script>

<?php require_once 'footer.php'; ?>