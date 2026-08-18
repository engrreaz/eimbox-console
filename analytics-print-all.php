<?php
$dataset_id = (int) ($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    require_once 'header.php';
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

            // A simple function to render report data into HTML.
            // In a real application, you'd have specific rendering logic for each report.
            function renderReport(sectionId, data) {
                let title = sectionId.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
                let html = `<h1>${title}</h1>`;
                if (Array.isArray(data) && data.length > 0) {
                    html += '<table class="table table-bordered table-sm">';
                    // Headers
                    html += '<thead><tr>';
                    Object.keys(data[0]).forEach(key => html += `<th>${key}</th>`);
                    html += '</tr></thead>';
                    // Body
                    html += '<tbody>';
                    data.forEach(row => {
                        html += '<tr>';
                        Object.values(row).forEach(value => html += `<td>${(typeof value === 'object' ? JSON.stringify(value) : value)}</td>`);
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                }
                return html;
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
