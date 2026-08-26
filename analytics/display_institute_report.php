<?php
/**
 * Display Institute Performance Report & Executive Top Sheet
 * 
 * Renders an executive summary top sheet and interactive performance dashboard for the institution.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sccode = $_SESSION['sccode'] ?? null;

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid or missing Dataset ID.</div>';
    exit;
}

// Fetch report data by internal include
ob_start();
$_GET['dataset_id'] = $dataset_id;
include __DIR__ . '/get_institute_report.php';
$raw_output = ob_get_clean();
$json = json_decode($raw_output, true);
$report_data = $json['data'] ?? [];

if (empty($report_data)) {
    echo '<div class="alert alert-warning">No institutional performance data available for this dataset.</div>';
    exit;
}

$meta = $report_data['meta'] ?? [];
$summary = $report_data['summary'] ?? [];
$gender = $report_data['gender_performance'] ?? [];
$grades = $report_data['grade_distribution'] ?? [];
$classes = $report_data['classes_summary'] ?? [];
$weakest_subs = $report_data['weakest_subjects'] ?? [];
$top_subs = $report_data['top_subjects'] ?? [];
$top_students = $report_data['top_students'] ?? [];
$top_teacher = $report_data['top_teacher'] ?? [];
?>

<div class="institute-performance-dashboard mb-4">
    <!-- Executive Top Sheet Header Card -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <div class="badge bg-white text-primary mb-2 px-3 py-1 fw-bold fs-6">
                        <i class="bi bi-award-fill me-1"></i> EXECUTIVE PERFORMANCE TOP SHEET (প্রাতিষ্ঠানিক সারসংক্ষেপ)
                    </div>
                    <h2 class="h3 fw-bold mb-1 text-white"><?= htmlspecialchars($meta['scname'] ?? 'Institution') ?></h2>
                    <p class="mb-0 text-white-50 small">
                        <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($meta['scaddress'] ?? '') ?> 
                        <span class="mx-2">•</span> EIIN / Code: <strong><?= htmlspecialchars($meta['sccode'] ?? '') ?></strong>
                    </p>
                </div>
                <div class="text-md-end mt-3 mt-md-0">
                    <div class="bg-white bg-opacity-10 p-3 rounded border border-white border-opacity-25">
                        <div class="small text-white-50">Examination & Session</div>
                        <div class="fw-bold fs-5 text-white"><?= htmlspecialchars($meta['exam_title'] ?? 'Examination') ?></div>
                        <div class="small text-white-50">
                            Session: <strong class="text-white"><?= htmlspecialchars($meta['sessionyear'] ?? '') ?></strong> 
                            | Slot: <strong class="text-white"><?= htmlspecialchars($meta['slot'] ?? '') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Institutional KPI Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Appeared / Enrolled</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1">
                                <?= number_format($summary['total_appeared'] ?? 0) ?> 
                                <small class="text-muted fs-6 fw-normal">/ <?= number_format($summary['total_enrolled'] ?? 0) ?></small>
                            </h3>
                            <small class="text-primary fw-semibold">
                                <i class="bi bi-person-check-fill me-1"></i>Attn: <?= number_format($summary['attendance_rate'] ?? 0, 1) ?>%
                                <?php if (($summary['total_absent'] ?? 0) > 0): ?>
                                    <span class="text-danger ms-1">(<?= $summary['total_absent'] ?> Absent)</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Overall Pass Rate</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">
                                <?= number_format($summary['pass_rate'] ?? 0, 1) ?>%
                            </h3>
                            <small class="text-muted">
                                Passed: <strong class="text-success"><?= number_format($summary['total_passed'] ?? 0) ?></strong> | 
                                Failed: <strong class="text-danger"><?= number_format($summary['total_failed'] ?? 0) ?></strong>
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">GPA 5.00 (A+) / Exc.</span>
                            <h3 class="fw-bold text-info mb-0 mt-1">
                                <?= number_format($summary['total_aplus'] ?? 0) ?> 
                                <small class="text-muted fs-6 fw-normal">(<?= number_format($summary['aplus_rate'] ?? 0, 1) ?>%)</small>
                            </h3>
                            <small class="text-muted">
                                70%+ Marks: <strong class="text-info"><?= number_format($summary['total_excellent'] ?? 0) ?></strong> (<?= number_format($summary['excellent_rate'] ?? 0, 1) ?>%)
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-star-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Average Marks Percentage</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">
                                <?= number_format($summary['overall_avg_marks_percentage'] ?? 0, 1) ?>%
                            </h3>
                            <small class="text-muted">Institution-wide Aggregate Score</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-calculator-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-secondary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Academic Workload</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1">
                                <?= $summary['total_classes'] ?? 0 ?> <span class="fs-6 fw-normal text-muted">Classes</span>
                            </h3>
                            <small class="text-muted">
                                <?= $summary['total_subjects'] ?? 0 ?> Subjects • <?= $summary['total_teachers'] ?? 0 ?> Teachers
                            </small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                            <i class="bi bi-diagram-3-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">At-Risk Students</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                <?= number_format($summary['total_at_risk'] ?? 0) ?>
                            </h3>
                            <small class="text-danger fw-semibold">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $summary['critical_at_risk'] ?? 0 ?> Critical (SRS 60+)
                            </small>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-shield-exclamation fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gender Breakdown & Grade Distribution Side-by-Side -->
    <div class="row g-4 mb-4">
        <!-- Gender Comparative Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-gender-ambiguous text-primary me-2"></i>Gender Performance Comparison (লিঙ্গভিত্তিক তুলনা)
                    </h5>
                    <span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($gender['ratio'] ?? '') ?></span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Boys -->
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border border-primary border-opacity-25 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="fs-4 me-2">👦</span>
                                    <div>
                                        <div class="fw-bold text-primary">Boys (ছাত্র)</div>
                                        <small class="text-muted"><?= number_format($gender['total_males'] ?? 0) ?> Appeared</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Pass Rate</span>
                                        <strong class="text-success"><?= number_format($gender['male_pass_rate'] ?? 0, 1) ?>%</strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?= min(100, max(0, $gender['male_pass_rate'] ?? 0)) ?>%"></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted pt-2 border-top">
                                    <span>GPA 5.0: <strong class="text-dark"><?= $gender['aplus_males'] ?? 0 ?></strong></span>
                                    <span>Avg: <strong class="text-primary"><?= number_format($gender['avg_male_marks'] ?? 0, 1) ?>%</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Girls -->
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border border-danger border-opacity-25 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="fs-4 me-2">👧</span>
                                    <div>
                                        <div class="fw-bold text-danger">Girls (ছাত্রী)</div>
                                        <small class="text-muted"><?= number_format($gender['total_females'] ?? 0) ?> Appeared</small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Pass Rate</span>
                                        <strong class="text-success"><?= number_format($gender['female_pass_rate'] ?? 0, 1) ?>%</strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?= min(100, max(0, $gender['female_pass_rate'] ?? 0)) ?>%"></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted pt-2 border-top">
                                    <span>GPA 5.0: <strong class="text-dark"><?= $gender['aplus_females'] ?? 0 ?></strong></span>
                                    <span>Avg: <strong class="text-danger"><?= number_format($gender['avg_female_marks'] ?? 0, 1) ?>%</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Distribution Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-steps text-success me-2"></i>Board Grade Distribution Matrix (গ্রেড বণ্টন)
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 11px;">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Grade</th>
                                    <th>GPA Range</th>
                                    <th>Score Range</th>
                                    <th class="text-center">Students</th>
                                    <th style="width: 120px;">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $g): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $g['badge'] ?> px-2 py-1"><?= $g['grade'] ?></span>
                                        </td>
                                        <td class="text-muted"><?= $g['gpa_range'] ?></td>
                                        <td class="text-muted"><?= $g['score_range'] ?></td>
                                        <td class="text-center fw-bold text-dark"><?= number_format($g['student_count']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-<?= $g['badge'] ?>" style="width: <?= min(100, max(0, $g['percentage'])) ?>%"></div>
                                                </div>
                                                <span class="text-muted small fw-semibold" style="width: 38px;"><?= number_format($g['percentage'], 1) ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class-by-Class Comparative Summary Matrix (Executive Top Sheet Table) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table text-primary me-2"></i>Class Performance Comparative Matrix (শ্রেণিভিত্তিক তুলনামূলক পারফরম্যান্স)
            </h5>
            <span class="badge bg-primary"><?= count($classes) ?> Classes Evaluated</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 11px;">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 50px;">Rank</th>
                            <th class="text-start" style="min-width: 130px;">Class & Section</th>
                            <th style="width: 90px;">Appeared / Enrolled</th>
                            <th style="width: 90px;">Pass / Fail</th>
                            <th style="width: 80px;">Pass Rate %</th>
                            <th style="width: 80px;">GPA 5.0 (A+)</th>
                            <th style="width: 80px;">Avg Marks %</th>
                            <th style="width: 85px;">CPI Score</th>
                            <th style="width: 85px;">Difficulty (CDF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">No class performance records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($classes as $idx => $cls): ?>
                                <?php 
                                    $rank = (int)($cls['class_rank'] ?? ($idx + 1));
                                    $cpi = (float)($cls['cpi_score'] ?? 0);
                                    $cdf = (float)($cls['difficulty_factor'] ?? 0);
                                    $passRate = (float)($cls['pass_rate'] ?? 0);
                                    $avgMarks = (float)($cls['overall_marks_percentage'] ?? 0);
                                    $enrolled = (int)($cls['total_enrolled'] ?? $cls['total_students_appeared']);
                                    $appeared = (int)($cls['total_students_appeared'] ?? 0);
                                    $passed = (int)($cls['total_passed'] ?? 0);
                                    $failed = (int)($cls['total_failed'] ?? 0);
                                    $aplus = (int)($cls['aplus_count'] ?? 0);

                                    $cpiBadgeClass = $cpi >= 70 ? 'bg-success' : ($cpi >= 50 ? 'bg-primary' : ($cpi >= 35 ? 'bg-warning text-dark' : 'bg-danger'));
                                ?>
                                <tr>
                                    <td class="text-center fw-bold">
                                        <?php if ($rank === 1): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #1</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">#<?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-start fw-bold text-dark">
                                        <?= htmlspecialchars($cls['classname']) ?> - <?= htmlspecialchars($cls['sectionname']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $appeared ?> <span class="text-muted fw-normal">/ <?= $enrolled ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success fw-bold"><?= $passed ?></span> / <span class="text-danger fw-bold"><?= $failed ?></span>
                                    </td>
                                    <td class="text-center fw-bold <?= $passRate >= 70 ? 'text-success' : ($passRate >= 50 ? 'text-primary' : 'text-danger') ?>">
                                        <?= number_format($passRate, 1) ?>%
                                    </td>
                                    <td class="text-center fw-bold text-info">
                                        <?= $aplus ?>
                                    </td>
                                    <td class="text-center fw-bold text-dark">
                                        <?= number_format($avgMarks, 1) ?>%
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $cpiBadgeClass ?> px-2"><?= number_format($cpi, 1) ?></span>
                                    </td>
                                    <td class="text-center text-muted font-monospace">
                                        <?= number_format($cdf, 1) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Subject Benchmarks & Top Highlights Grid -->
    <div class="row g-4 mb-4">
        <!-- Top Scoring Subjects -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="bi bi-arrow-up-circle-fill me-2"></i>Top Scoring Subjects (সর্বোচ্চ অর্জিত বিষয়ের তালিকা)
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 11px;">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Code</th>
                                    <th>Subject Name</th>
                                    <th class="text-center">Pass %</th>
                                    <th class="text-center">Avg Marks %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_subs as $ts): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($ts['subject_code']) ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($ts['subject_name']) ?></td>
                                        <td class="text-center text-success fw-bold"><?= number_format($ts['pass_rate'] ?? 0, 1) ?>%</td>
                                        <td class="text-center fw-bold text-primary"><?= number_format($ts['overall_marks_percentage'] ?? 0, 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Challenging Subjects -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>Most Challenging Subjects (কঠিন বিষয় ও ফেলের হার)
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 11px;">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Code</th>
                                    <th>Subject Name</th>
                                    <th class="text-center">Failure %</th>
                                    <th class="text-center">Difficulty (SDF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weakest_subs as $ws): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($ws['subject_code']) ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($ws['subject_name']) ?></td>
                                        <td class="text-center text-danger fw-bold"><?= number_format($ws['failure_rate'] ?? 0, 1) ?>%</td>
                                        <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger"><?= number_format($ws['sdf'] ?? 0, 1) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Students & Top Teacher Grid -->
    <div class="row g-4 mb-4">
        <!-- Top Students -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-award-fill me-2"></i>Institutional Top 5 Merit Students (মেধাতালিকায় শীর্ষ ৫ শিক্ষার্থী)
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 11px;">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Class & Section</th>
                                    <th class="text-center">Roll</th>
                                    <th class="text-center">Total Marks</th>
                                    <th class="text-center">GPA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_students as $st): ?>
                                    <tr>
                                        <td><span class="badge bg-warning text-dark fw-bold">#<?= $st['class_rank'] ?? 1 ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($st['stnameeng']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($st['classname']) ?> - <?= htmlspecialchars($st['sectionname']) ?></td>
                                        <td class="text-center font-monospace"><?= htmlspecialchars($st['rollno']) ?></td>
                                        <td class="text-center fw-bold"><?= number_format($st['total_marks_obtained'] ?? 0, 1) ?> <span class="text-muted small">(<?= number_format($st['percentage'] ?? 0, 1) ?>%)</span></td>
                                        <td class="text-center"><span class="badge bg-success"><?= number_format($st['gpa'] ?? 0, 2) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Evaluated Teacher -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-success">
                        <i class="bi bi-person-check-fill me-2"></i>Top Evaluated Teacher
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if (!empty($top_teacher)): ?>
                        <div class="avatar avatar-lg mb-3">
                            <span class="avatar-initial rounded-circle bg-success text-white fs-3 p-3">👨‍🏫</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($top_teacher['teacher_name'] ?? 'Teacher') ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($top_teacher['teacher_position'] ?? 'Faculty') ?></p>
                        <div class="p-3 bg-white rounded border">
                            <div class="row g-2">
                                <div class="col-6 border-end">
                                    <div class="small text-muted">TIA Score</div>
                                    <div class="fw-bold text-success fs-5"><?= number_format($top_teacher['teacher_impact_adjustment'] ?? 0, 1) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">TPI Score</div>
                                    <div class="fw-bold text-primary fs-5"><?= number_format($top_teacher['teacher_performance_index'] ?? 0, 1) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No teacher evaluation records found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Verification & Signatures Block for Executive Top Sheet -->
    <div class="card border-0 shadow-sm bg-white p-4">
        <div class="row text-center mt-4 pt-4">
            <div class="col-4">
                <div class="border-top border-dark pt-2 mx-3">
                    <strong class="text-dark d-block">Prepared By</strong>
                    <small class="text-muted">Exam Committee Member / Analyst</small>
                </div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-2 mx-3">
                    <strong class="text-dark d-block">Verified By</strong>
                    <small class="text-muted">Convener, Examination Committee</small>
                </div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-2 mx-3">
                    <strong class="text-dark d-block"><?= htmlspecialchars($meta['headname'] ?: 'Head of Institution') ?></strong>
                    <small class="text-muted"><?= htmlspecialchars($meta['headtitle'] ?: 'Headmaster / Principal') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>
