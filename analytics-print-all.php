<?php
require_once 'vendor/autoload.php'; // mPDF
require_once 'core/config.php';
require_once 'core/db.php';
session_start();

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    die('<div class="alert alert-danger">Error: No Dataset ID provided.</div>');
}

// Fetch all data using the new script
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . 'analytics/get_full_report_data.php?dataset_id=' . $dataset_id;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id()); // Pass session cookie
$json_data = curl_exec($ch);
curl_close($ch);
$response = json_decode($json_data, true);

if ($response['status'] !== 'success') {
    die('Failed to fetch report data: ' . ($response['message'] ?? 'Unknown error'));
}

$data = $response['data'];

// Helper function for creating progress bars
function create_bar($value, $max_value = 100, $color = '#007bff', $height = '18px') {
    $percentage = ($value / $max_value) * 100;
    return "<div style='background-color: #e9ecef; border-radius: 5px; width: 100px; height: $height;'>
                <div style='width: {$percentage}%; background-color: $color; height: 100%; border-radius: 5px; text-align: center; color: white; font-size: 10px; line-height: $height;'>
                    " . number_format($value, 2) . "
                </div>
            </div>";
}

// mPDF setup
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 20,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10,
]);

$mpdf->SetAuthor('EIMBox');
$mpdf->SetCreator('EIMBox Analytics Engine');
$mpdf->SetTitle('Exam Analytics Report');
$mpdf->SetHeader('Exam Analytics Report | {PAGENO}');
$mpdf->SetFooter('Generated on: {DATE j-m-Y h:i A}');

// CSS for the report
$stylesheet = file_get_contents('assets/css/bootstrap.min.css'); // Use bootstrap for basic styling
$stylesheet .= "
    body { font-family: sans-serif; }
    .report-section { page-break-before: always; }
    h1, h2, h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
    th { background-color: #f2f2f2; }
    .summary-card { border: 1px solid #eee; padding: 10px; text-align: center; width: 30%; float: left; margin: 1%; }
    .clearfix { clear: both; }
    .text-danger { color: #dc3545; }
    .text-success { color: #28a745; }
";
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

// --- Report Content ---

// Page 1: Institute Overview
$html = '<h1>Institute Performance Overview</h1>';
$summary = $data['institute_summary'];
$html .= "
    <div class='summary-card'>
        <h4>Total Students</h4>
        <p style='font-size: 24px;'>" . ($summary['total_students'] ?? 0) . "</p>
    </div>
    <div class='summary-card'>
        <h4>Pass Rate</h4>
        <p style='font-size: 24px;'>" . number_format($summary['pass_rate'] ?? 0, 2) . "%</p>
    </div>
    <div class='summary-card'>
        <h4>Average Marks</h4>
        <p style='font-size: 24px;'>" . number_format($summary['overall_avg_marks_percentage'] ?? 0, 2) . "%</p>
    </div>
    <div class='clearfix'></div>
";

$html .= "<h3>Grade Distribution</h3>";
$html .= "<table><thead><tr><th>Grade</th><th>Number of Students</th><th>Percentage</th></tr></thead><tbody>";
$total_students = $summary['total_students'] ?? 1;
foreach ($data['grade_distribution'] as $grade) {
    $percentage = ($grade['student_count'] / $total_students) * 100;
    $html .= "<tr>
                <td>{$grade['grade']}</td>
                <td>{$grade['student_count']}</td>
                <td>" . create_bar($percentage) . "</td>
              </tr>";
}
$html .= "</tbody></table>";

$html .= "<h3>Weakest Subjects (Highest Failure Rate)</h3>";
$html .= "<table><thead><tr><th>Subject</th><th>Failure Rate</th></tr></thead><tbody>";
foreach ($data['weakest_subjects'] as $subject) {
    $html .= "<tr>
                <td>" . htmlspecialchars($subject['subject_name']) . "</td>
                <td class='text-danger'>" . number_format($subject['failure_rate'], 2) . "%</td>
              </tr>";
}
$html .= "</tbody></table>";
$mpdf->WriteHTML($html);


// Page 2: Teacher Performance
$mpdf->AddPage();
$html = '<h1>Teacher Performance Report</h1>';
$html .= '<table><thead><tr><th>Rank</th><th>Teacher</th><th>Avg. Marks</th><th>Pass Rate</th><th>TPI</th><th>TIA</th><th>TCI</th><th>TSI</th></tr></thead><tbody>';
foreach ($data['teacher_performance'] as $index => $teacher) {
    $html .= "<tr>
                <td>" . ($index + 1) . "</td>
                <td>" . htmlspecialchars($teacher['tname']) . "<br><small>" . htmlspecialchars($teacher['position']) . "</small></td>
                <td>" . create_bar($teacher['overall_avg_marks']) . "</td>
                <td>" . create_bar($teacher['overall_pass_rate']) . "</td>
                <td>" . number_format($teacher['tpi'], 2) . "</td>
                <td style='font-weight: bold;'>" . number_format($teacher['tia'], 2) . "</td>
                <td class='" . ($teacher['tci_score'] > 0 ? 'text-success' : 'text-danger') . "'>" . number_format($teacher['tci_score'], 2) . "</td>
                <td class='" . ($teacher['tsi_score'] > 0 ? 'text-success' : 'text-danger') . "'>" . number_format($teacher['tsi_score'], 2) . "</td>
              </tr>";
}
$html .= '</tbody></table>';
$mpdf->WriteHTML($html);


// Page 3: Class Performance
$mpdf->AddPage();
$html = '<h1>Class Performance Report</h1>';
$html .= '<table><thead><tr><th>Rank</th><th>Class</th><th>Students</th><th>Avg. Marks</th><th>CPI Score</th><th>Difficulty (CDF)</th></tr></thead><tbody>';
foreach ($data['class_performance'] as $class) {
    $html .= "<tr>
                <td>" . $class['class_rank'] . "</td>
                <td>" . htmlspecialchars($class['classname'] . ' - ' . $class['sectionname']) . "</td>
                <td>" . $class['total_students_appeared'] . "</td>
                <td>" . create_bar($class['overall_marks_percentage']) . "</td>
                <td style='font-weight: bold;'>" . number_format($class['cpi_score'], 2) . "</td>
                <td>" . create_bar($class['difficulty_factor'], 100, '#dc3545') . "</td>
              </tr>";
}
$html .= '</tbody></table>';
$mpdf->WriteHTML($html);


// Page 4: Subject Performance
$mpdf->AddPage();
$html = '<h1>Subject Performance Report</h1>';
$html .= '<table><thead><tr><th>Subject</th><th>Students</th><th>Avg. Marks</th><th>Pass %</th><th>Fail %</th><th>Difficulty (SDF)</th></tr></thead><tbody>';
foreach ($data['subject_performance'] as $subject) {
    $pass_rate = 100 - $subject['failure_rate'];
    $html .= "<tr>
                <td>" . htmlspecialchars($subject['subject_name']) . "</td>
                <td>" . $subject['total_students_appeared'] . "</td>
                <td>" . create_bar($subject['overall_marks_percentage']) . "</td>
                <td class='text-success'>" . number_format($pass_rate, 2) . "%</td>
                <td class='text-danger'>" . number_format($subject['failure_rate'], 2) . "%</td>
                <td>" . create_bar($subject['subject_difficulty_factor'], 100, '#ffc107') . "</td>
              </tr>";
}
$html .= '</tbody></table>';
$mpdf->WriteHTML($html);


// Page 5: Student Merit List
$mpdf->AddPage();
$html = '<h1>Student Merit List</h1>';
$html .= '<table><thead><tr><th>Rank</th><th>Student</th><th>Class</th><th>Roll</th><th>Total Marks</th><th>Percentage</th><th>GPA</th><th>Grade</th></tr></thead><tbody>';
foreach ($data['student_merit_list'] as $student) {
    $is_fail = $student['failed_subjects'] > 0;
    $rank = $is_fail ? 'F' : $student['class_rank'];
    $row_class = $is_fail ? "style='background-color: #ffebee;'" : "";

    $html .= "<tr $row_class>
                <td style='font-weight: bold;'>" . $rank . "</td>
                <td>" . htmlspecialchars($student['stnameeng']) . "</td>
                <td>" . htmlspecialchars($student['classname'] . ' - ' . $student['sectionname']) . "</td>
                <td>" . $student['rollno'] . "</td>
                <td>" . number_format($student['total_marks_obtained'], 2) . "</td>
                <td>" . number_format($student['percentage'], 2) . "%</td>
                <td>" . number_format($student['gpa'], 2) . "</td>
                <td>" . $student['grade'] . "</td>
              </tr>";
}
$html .= '</tbody></table>';
$mpdf->WriteHTML($html);


// Output the PDF
$mpdf->Output('Exam_Analytics_Report.pdf', 'I');
exit;

?>
<style>
    body { background-color: #fff; }
    .report-section { page-break-after: always; margin-bottom: 2rem; }
    .report-section:last-child { page-break-after: auto; }
    @media print {
        .no-print { display: none; }
    }
</style>

<div class="container mt-4">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <a href="analytics-exam-report.php" class="btn btn-secondary">Back to Reports</a>
    </div>

    <!-- Institute Overview (Placeholder) -->
    <div class="report-section">
        <h3>প্রাতিষ্ঠানিক সামগ্রিক প্রতিবেদন</h3>
        <p>মোট শিক্ষার্থী: <strong><?= $institute_summary['total_students'] ?? 'N/A' ?></strong></p>
        <p>মোট পাস: <strong><?= $institute_summary['total_passed'] ?? 'N/A' ?></strong></p>
        <p>পাসের হার: <strong><?= ($institute_summary['total_students'] > 0) ? number_format(($institute_summary['total_passed'] / $institute_summary['total_students']) * 100, 2) : '0.00' ?>%</strong></p>
        <!-- Add more summary data here as needed -->
    </div>

    <!-- Teacher Performance -->
    <div class="report-section">
        <h3>Teacher Performance Report</h3>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Rank</th><th>Teacher</th><th>Avg. Marks</th><th>Pass Rate</th><th>TIA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teacher_data)): foreach ($teacher_data as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['tname']) ?></td>
                        <td><?= number_format($row['overall_avg_marks'], 2) ?></td>
                        <td><?= number_format($row['overall_pass_rate'], 2) ?>%</td>
                        <td><?= number_format($row['teacher_impact_adjustment'], 2) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Student Merit List -->
    <div class="report-section">
        <h3>Student Merit List</h3>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Rank</th><th>Student</th><th>Class</th><th>Total Marks</th><th>GPA</th><th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($student_data)): foreach ($student_data as $row): ?>
                    <tr>
                        <td><?= $row['failed_subjects'] > 0 ? 'F' : $row['class_rank'] ?></td>
                        <td><?= htmlspecialchars($row['stname']) ?></td>
                        <td><?= htmlspecialchars($row['classname']) ?>-<?= htmlspecialchars($row['sectionname']) ?></td>
                        <td><?= number_format($row['total_marks_obtained'], 2) ?></td>
                        <td><?= number_format($row['gpa'], 2) ?></td>
                        <td><?= htmlspecialchars($row['grade']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer-plain.php'; ?>

?>
<style>
    body { background-color: #fff; }
    .report-section { page-break-after: always; margin-bottom: 2rem; }
    .report-section:last-child { page-break-after: auto; }
    @media print {
        .no-print { display: none; }
    }
</style>

<div class="container mt-4">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <a href="analytics-exam-report.php" class="btn btn-secondary">Back to Reports</a>
    </div>

    <!-- Institute Overview (Placeholder) -->
    <div class="report-section">
        <h3>প্রাতিষ্ঠানিক সামগ্রিক প্রতিবেদন</h3>
        <p>মোট শিক্ষার্থী: <strong><?= $institute_summary['total_students'] ?? 'N/A' ?></strong></p>
        <p>মোট পাস: <strong><?= $institute_summary['total_passed'] ?? 'N/A' ?></strong></p>
        <p>পাসের হার: <strong><?= ($institute_summary['total_students'] > 0) ? number_format(($institute_summary['total_passed'] / $institute_summary['total_students']) * 100, 2) : '0.00' ?>%</strong></p>
        <!-- Add more summary data here as needed -->
    </div>

    <!-- Teacher Performance -->
    <div class="report-section">
        <h3>Teacher Performance Report</h3>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Rank</th><th>Teacher</th><th>Avg. Marks</th><th>Pass Rate</th><th>TIA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teacher_data)): foreach ($teacher_data as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['tname']) ?></td>
                        <td><?= number_format($row['overall_avg_marks'], 2) ?></td>
                        <td><?= number_format($row['overall_pass_rate'], 2) ?>%</td>
                        <td><?= number_format($row['teacher_impact_adjustment'], 2) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Student Merit List -->
    <div class="report-section">
        <h3>Student Merit List</h3>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Rank</th><th>Student</th><th>Class</th><th>Total Marks</th><th>GPA</th><th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($student_data)): foreach ($student_data as $row): ?>
                    <tr>
                        <td><?= $row['failed_subjects'] > 0 ? 'F' : $row['class_rank'] ?></td>
                        <td><?= htmlspecialchars($row['stname']) ?></td>
                        <td><?= htmlspecialchars($row['classname']) ?>-<?= htmlspecialchars($row['sectionname']) ?></td>
                        <td><?= number_format($row['total_marks_obtained'], 2) ?></td>
                        <td><?= number_format($row['gpa'], 2) ?></td>
                        <td><?= htmlspecialchars($row['grade']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center">No data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer-plain.php'; ?>