<?php
require_once 'header.php';

$stid = $_GET['stid'] ?? '';
$year = $_GET['year'] ?? date('Y');

$student = null;
$session_history = [];

if (!empty($stid)) {
    // 1. Fetch Student Details
    $stmt = $conn->prepare("SELECT * FROM students WHERE stid = ? AND sccode = ? LIMIT 1");
    $stmt->bind_param("si", $stid, $sccode);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. Fetch All Academic Sessions
    $stmt2 = $conn->prepare("SELECT * FROM sessioninfo WHERE stid = ? AND sccode = ? ORDER BY sessionyear DESC, id DESC");
    $stmt2->bind_param("si", $stid, $sccode);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
        $session_history[] = $r;
    }
    $stmt2->close();
}
?>

<style>
    .report-card {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 35px;
        margin: 20px auto;
        max-width: 950px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        position: relative;
    }

    .report-header-title {
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1e293b;
        background: #f1f5f9;
        padding: 8px 15px;
        border-left: 5px solid #696cff;
        border-radius: 0 4px 4px 0;
        margin-top: 25px;
        margin-bottom: 12px;
    }

    .info-tbl {
        width: 100%;
        font-size: 12.5px;
        border-collapse: collapse;
        margin-bottom: 5px;
    }

    .info-tbl th {
        width: 22%;
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .info-tbl td {
        width: 28%;
        color: #1e293b;
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .session-tbl th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 600;
        text-align: center;
        padding: 8px 6px;
        border: 1px solid #cbd5e1;
        font-size: 12px;
    }

    .session-tbl td {
        padding: 8px 6px;
        border: 1px solid #e2e8f0;
        font-size: 12px;
        text-align: center;
        vertical-align: middle;
    }

    .st-photo-box {
        width: 120px;
        height: 140px;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
        background: #f8fafc;
    }

    .st-photo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media print {
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        nav, .no-print, .layout-navbar, .layout-menu, .footer, .content-footer {
            display: none !important;
        }
        .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
        .report-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .info-tbl th, .info-tbl td, .session-tbl th, .session-tbl td {
            border: 1px solid #64748b !important;
            font-size: 11px !important;
            padding: 4px 6px !important;
        }
        .report-header-title {
            background: #f1f5f9 !important;
            border-left: 5px solid #000 !important;
            color: #000 !important;
            font-size: 12px !important;
            margin-top: 15px !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 950px; margin: 0 auto;">
        <div class="d-flex align-items-center gap-2">
            <a href="students-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <h4 class="fw-bold m-0 ms-2">Student Overall Academic Report</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="student-view-profile.php?stid=<?= htmlspecialchars($stid) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i> Full Profile
            </a>
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <?php if (!$student): ?>
        <div class="alert alert-warning text-center border-0 shadow-sm py-4" style="max-width: 950px; margin: 0 auto;">
            <i class="bi bi-exclamation-triangle fs-3 d-block mb-2 text-warning"></i>
            <strong>শিক্ষার্থীর তথ্য পাওয়া যায়নি।</strong>
            <p class="text-muted small mt-1">অনুগ্রহ করে সঠিক Student ID উল্লেখ করুন।</p>
        </div>
    <?php else: ?>
        <div class="report-card">
            
            <!-- Letterhead -->
            <div class="letterhead-wrapper pb-2 border-bottom mb-3">
                <?php include __DIR__ . '/templete/letter-head-01.php'; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-label-dark px-3 py-2 fs-6">
                    OVERALL STUDENT REPORT &amp; ACADEMIC DOSSIER
                </span>
                <span class="text-muted small">
                    Generated: <?= date('d M, Y h:i A') ?>
                </span>
            </div>

            <!-- Top Student Overview -->
            <div class="row align-items-center mb-3">
                <div class="col-8 col-sm-9">
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($student['stnameeng'] ?? '') ?></h4>
                        <?php if (!empty($student['stnameben'])): ?>
                            <span class="text-muted fs-6">(<?= htmlspecialchars($student['stnameben']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-primary">Student ID: <?= htmlspecialchars($student['stid'] ?? '') ?></span>
                        <span class="badge bg-secondary">Total Enrolled Sessions: <?= count($session_history) ?></span>
                        <span class="badge bg-info">Gender: <?= htmlspecialchars($student['gender'] ?: '—') ?></span>
                        <span class="badge bg-danger">Blood Group: <?= htmlspecialchars($student['bgroup'] ?: '—') ?></span>
                    </div>
                </div>
                <div class="col-4 col-sm-3 text-end d-flex justify-content-end">
                    <div class="st-photo-box shadow-sm">
                        <img src="<?= student_profile_image_path($student['stid']) ?>" 
                             onerror="this.src='assets/images/user.png'" 
                             alt="Student Photo">
                    </div>
                </div>
            </div>

            <!-- Basic Student Info -->
            <div class="report-header-title"><i class="bi bi-person-vcard me-1"></i> Student Demographic &amp; Contact Info</div>
            <table class="info-tbl">
                <tr>
                    <th>Father's Name</th>
                    <td><?= htmlspecialchars($student['fname'] ?: '—') ?></td>
                    <th>Mother's Name</th>
                    <td><?= htmlspecialchars($student['mname'] ?: '—') ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?= !empty($student['dob']) && $student['dob'] != '0000-00-00' ? date('d M, Y', strtotime($student['dob'])) : '—' ?></td>
                    <th>Religion</th>
                    <td><?= htmlspecialchars($student['religion'] ?: '—') ?></td>
                </tr>
                <tr>
                    <th>Guardian Name</th>
                    <td><?= htmlspecialchars($student['guarname'] ?: '—') ?> (<?= htmlspecialchars($student['guarrelation'] ?: 'Guardian') ?>)</td>
                    <th>Guardian Mobile</th>
                    <td class="fw-bold"><?= htmlspecialchars($student['guarmobile'] ?: $student['fmobile'] ?: '—') ?></td>
                </tr>
                <tr>
                    <th>Present Address</th>
                    <td colspan="3">
                        <?= htmlspecialchars(implode(', ', array_filter([$student['previll'] ?? '', $student['prepo'] ?? '', $student['preps'] ?? '', $student['predist'] ?? '']))) ?: '—' ?>
                    </td>
                </tr>
            </table>

            <!-- Academic Progression History -->
            <div class="report-header-title"><i class="bi bi-clock-history me-1"></i> Academic Session Progression History</div>
            <table class="table table-bordered session-tbl mb-3">
                <thead>
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th>Session Year</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll No</th>
                        <th>Slot / Branch</th>
                        <th>Group</th>
                        <th>Medium</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($session_history)): ?>
                        <tr>
                            <td colspan="9" class="text-muted py-3">No academic session records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($session_history as $idx => $sh): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($sh['sessionyear']) ?></td>
                                <td><?= htmlspecialchars($sh['classname']) ?></td>
                                <td><?= htmlspecialchars($sh['sectionname']) ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($sh['rollno']) ?></td>
                                <td><?= htmlspecialchars($sh['slot'] ?: 'School') ?></td>
                                <td><?= htmlspecialchars($sh['groupname'] ?: 'General') ?></td>
                                <td><?= htmlspecialchars($sh['medium'] ?: 'Bengali') ?></td>
                                <td>
                                    <?php if ($sh['status'] == 1): ?>
                                        <span class="badge bg-label-success">Active</span>
                                    <?php elseif ($sh['status'] == 0): ?>
                                        <span class="badge bg-label-secondary">Archived</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-warning"><?= htmlspecialchars($sh['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Institutional Observations / Summary Box -->
            <div class="report-header-title"><i class="bi bi-journal-check me-1"></i> Remarks &amp; Overall Record</div>
            <table class="info-tbl">
                <tr>
                    <th>Admission Date</th>
                    <td><?= !empty($student['doa']) && $student['doa'] != '0000-00-00' ? date('d M, Y', strtotime($student['doa'])) : '—' ?></td>
                    <th>Admission TC No</th>
                    <td><?= htmlspecialchars($student['tcno'] ?: '—') ?></td>
                </tr>
                <tr>
                    <th>Previous Institute</th>
                    <td colspan="3"><?= htmlspecialchars($student['preins'] ?: 'N/A') ?> <?= !empty($student['preinsadd']) ? '(' . htmlspecialchars($student['preinsadd']) . ')' : '' ?></td>
                </tr>
                <tr>
                    <th>General Conduct</th>
                    <td>Good / সন্তোষজনক</td>
                    <th>Institutional Standing</th>
                    <td class="text-success fw-bold">Regular Student in Good Standing</td>
                </tr>
            </table>

            <!-- Signatures -->
            <div class="mt-5 pt-4 d-print-block">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-top border-dark pt-1 mx-3">
                            <small class="fw-bold text-dark">Prepared By / Operator</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark pt-1 mx-3">
                            <small class="fw-bold text-dark">Class Teacher / Head Clerk</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark pt-1 mx-3">
                            <small class="fw-bold text-dark">Head of Institution Seal</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
</body>
</html>
