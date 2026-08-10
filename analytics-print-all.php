<?php
require_once 'header-plain.php'; // Use a plain header without nav/footer for printing

$dataset_id = (int)($_GET['dataset_id'] ?? 0);

if (empty($dataset_id)) {
    die('<div class="alert alert-danger">Error: No Dataset ID provided.</div>');
}

// Function to fetch data from backend scripts
function fetch_report_data($report_file, int $dataset_id) {
    // This is a simplified example. In a real scenario, you might want to
    // include the files directly or use a more robust method to get data.
    // For now, we'll simulate by including the logic.
    global $conn, $sccode;

    // This is a placeholder for the actual data fetching logic
    // which you would centralize or copy from get_teacher_report.php, get_student_report.php etc.
    if ($report_file === 'get_teacher_report.php') {
        // This is a simplified direct query for print. For full data, you'd replicate get_institute_report.php logic.
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT stid) AS total_students, SUM(CASE WHEN failed_subjects = 0 THEN 1 ELSE 0 END) AS total_passed FROM analytics_student_performance WHERE dataset_id = ? AND sccode = ?");
        $stmt->bind_param("is", $dataset_id, $sccode);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    if ($report_file === 'get_teacher_report.php') {
        $stmt = $conn->prepare("SELECT t.tname, atp.* FROM analytics_teacher_performance AS atp JOIN teacher AS t ON atp.tid = t.tid AND atp.sccode = t.sccode WHERE atp.dataset_id = ? ORDER BY atp.teacher_impact_adjustment DESC");
        $stmt->bind_param("i", $dataset_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    if ($report_file === 'get_student_report.php') {
        $stmt = $conn->prepare("SELECT s.stname, asp.* FROM analytics_student_performance AS asp JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode WHERE asp.dataset_id = ? ORDER BY asp.class_rank ASC, asp.total_marks_obtained DESC");
        $stmt->bind_param("i", $dataset_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

$institute_summary = fetch_report_data('get_institute_report.php', $dataset_id);
$teacher_data = fetch_report_data('get_teacher_report.php', $dataset_id);
$student_data = fetch_report_data('get_student_report.php', $dataset_id);

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