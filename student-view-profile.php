<?php
require_once 'header.php';

// Parameters
$stid = $_GET['stid'] ?? null;
$cls = $_GET['cls'] ?? null;
$sec = $_GET['sec'] ?? null;
$year = $_GET['year'] ?? date('Y');

$students_data = [];

if (!empty($stid)) {
    // Query single student with specific session year or latest session
    if (!empty($year)) {
        $stmt = $conn->prepare("SELECT s.*, si.*, si.status AS session_status, si.rollno AS current_roll
                                FROM students s 
                                INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                                WHERE s.stid = ? AND s.sccode = ? AND si.sessionyear = ? LIMIT 1");
        $stmt->bind_param("sis", $stid, $sccode, $year);
    } else {
        $stmt = $conn->prepare("SELECT s.*, si.*, si.status AS session_status, si.rollno AS current_roll
                                FROM students s 
                                INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                                WHERE s.stid = ? AND s.sccode = ? 
                                ORDER BY si.sessionyear DESC LIMIT 1");
        $stmt->bind_param("si", $stid, $sccode);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students_data[] = $row;
    }
    $stmt->close();
} elseif (!empty($cls)) {
    // Query class students
    if (!empty($sec)) {
        $stmt = $conn->prepare("SELECT s.*, si.*, si.status AS session_status, si.rollno AS current_roll
                                FROM students s 
                                INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                                WHERE si.classname = ? AND si.sectionname = ? AND si.sccode = ? AND si.sessionyear = ?
                                ORDER BY si.rollno ASC");
        $stmt->bind_param("ssis", $cls, $sec, $sccode, $year);
    } else {
        $stmt = $conn->prepare("SELECT s.*, si.*, si.status AS session_status, si.rollno AS current_roll
                                FROM students s 
                                INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                                WHERE si.classname = ? AND si.sccode = ? AND si.sessionyear = ?
                                ORDER BY si.rollno ASC");
        $stmt->bind_param("sis", $cls, $sccode, $year);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students_data[] = $row;
    }
    $stmt->close();
}
?>

<style>
    .profile-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        position: relative;
    }

    .student-photo-box {
        width: 130px;
        height: 150px;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4b5563;
        background: #f1f5f9;
        padding: 6px 12px;
        border-left: 4px solid #696cff;
        border-radius: 0 4px 4px 0;
        margin-bottom: 12px;
        margin-top: 20px;
    }

    .info-tbl {
        width: 100%;
        font-size: 12.5px;
        border-collapse: collapse;
        margin-bottom: 5px;
    }

    .info-tbl th {
        width: 22%;
        background-color: #fbfbfb;
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

    .badge-field {
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
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
        .profile-card {
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            margin: 0 0 20px 0 !important;
            box-shadow: none !important;
            page-break-after: always;
        }
        .info-tbl th, .info-tbl td {
            border: 1px solid #94a3b8 !important;
            font-size: 11px !important;
            padding: 4px 6px !important;
        }
        .section-title {
            background: #f1f5f9 !important;
            border-left: 4px solid #000 !important;
            color: #000 !important;
            font-size: 11.5px !important;
            margin-top: 12px !important;
            margin-bottom: 6px !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="students-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            <h4 class="fw-bold m-0 ms-2">Student Profile</h4>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($students_data) && count($students_data) === 1): ?>
                <a href="enroll-students.php?stid=<?= $students_data[0]['stid'] ?>&sy=<?= $students_data[0]['sessionyear'] ?>" 
                   target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
                <a href="student-bonafide.php?stid=<?= $students_data[0]['stid'] ?>&year=<?= $students_data[0]['sessionyear'] ?>" 
                   target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-award me-1"></i> Bonafide Certificate
                </a>
                <a href="student-overall-report.php?stid=<?= $students_data[0]['stid'] ?>&year=<?= $students_data[0]['sessionyear'] ?>" 
                   target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-file-earmark-person me-1"></i> Overall Report
                </a>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Profile
            </button>
        </div>
    </div>

    <?php if (empty($students_data)): ?>
        <div class="alert alert-warning text-center border-0 shadow-sm py-4">
            <i class="bi bi-exclamation-circle fs-3 d-block mb-2 text-warning"></i>
            <strong>কোন শিক্ষার্থীর তথ্য পাওয়া যায়নি।</strong>
            <p class="text-muted small mt-1">অনুগ্রহ করে সঠিক Student ID অথবা Class/Section সিলেক্ট করুন।</p>
        </div>
    <?php else: ?>
        <?php foreach ($students_data as $s): ?>
            <div class="profile-card">
                
                <!-- Institutional Letterhead Header -->
                <div class="letterhead-wrapper mb-3 pb-2 border-bottom">
                    <?php include __DIR__ . '/templete/letter-head-01.php'; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-label-primary px-3 py-2 fs-6">
                        STUDENT PROFILE / আইডেন্টিফিকেশন প্রোফাইল
                    </span>
                    <span class="text-muted small">
                        Printed on: <?= date('d M, Y h:i A') ?>
                    </span>
                </div>

                <!-- Basic Header with Photo & Quick Info -->
                <div class="row align-items-center mb-3">
                    <div class="col-8 col-sm-9">
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($s['stnameeng'] ?? '') ?></h4>
                            <?php if (!empty($s['stnameben'])): ?>
                                <span class="text-muted fs-6">(<?= htmlspecialchars($s['stnameben']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge bg-primary">ID: <?= htmlspecialchars($s['stid'] ?? '') ?></span>
                            <span class="badge bg-dark">Class: <?= htmlspecialchars($s['classname'] ?? '') ?></span>
                            <span class="badge bg-info">Section: <?= htmlspecialchars($s['sectionname'] ?? '') ?></span>
                            <span class="badge bg-secondary">Roll: <?= htmlspecialchars($s['current_roll'] ?? $s['rollno'] ?? '') ?></span>
                            <span class="badge bg-success">Session: <?= htmlspecialchars($s['sessionyear'] ?? '') ?></span>
                            <?php if (!empty($s['slot'])): ?>
                                <span class="badge bg-label-secondary">Slot: <?= htmlspecialchars($s['slot']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-4 col-sm-3 text-end d-flex justify-content-end">
                        <div class="student-photo-box shadow-sm">
                            <img src="<?= student_profile_image_path($s['stid']) ?>" 
                                 onerror="this.src='assets/images/user.png'" 
                                 class="student-photo" alt="Student Photo">
                        </div>
                    </div>
                </div>

                <!-- Academic Info -->
                <div class="section-title"><i class="bi bi-mortarboard me-1"></i> Academic Details</div>
                <table class="info-tbl">
                    <tr>
                        <th>Student ID</th>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($s['stid'] ?? '') ?></td>
                        <th>Session Year</th>
                        <td class="fw-bold"><?= htmlspecialchars($s['sessionyear'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Class Name</th>
                        <td><?= htmlspecialchars($s['classname'] ?? '') ?></td>
                        <th>Section Name</th>
                        <td><?= htmlspecialchars($s['sectionname'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Class Roll</th>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($s['current_roll'] ?? $s['rollno'] ?? '') ?></td>
                        <th>Slot / Branch</th>
                        <td><?= htmlspecialchars($s['slot'] ?? 'Default') ?></td>
                    </tr>
                    <tr>
                        <th>Group / Track</th>
                        <td><?= htmlspecialchars($s['groupname'] ?: 'General') ?></td>
                        <th>Medium &amp; Version</th>
                        <td><?= htmlspecialchars(($s['medium'] ?? 'Bengali') . ' / ' . ($s['version'] ?? 'Bengali')) ?></td>
                    </tr>
                    <tr>
                        <th>RFID / ID Tag</th>
                        <td><?= htmlspecialchars($s['rfidtag'] ?: '—') ?></td>
                        <th>Admission Date</th>
                        <td><?= !empty($s['doa']) && $s['doa'] != '0000-00-00' ? date('d M, Y', strtotime($s['doa'])) : '—' ?></td>
                    </tr>
                </table>

                <!-- Personal Information -->
                <div class="section-title"><i class="bi bi-person-lines-fill me-1"></i> Personal Information</div>
                <table class="info-tbl">
                    <tr>
                        <th>Full Name (English)</th>
                        <td class="fw-bold"><?= htmlspecialchars($s['stnameeng'] ?? '') ?></td>
                        <th>Full Name (Bengali)</th>
                        <td><?= htmlspecialchars($s['stnameben'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td><?= !empty($s['dob']) && $s['dob'] != '0000-00-00' ? date('d M, Y', strtotime($s['dob'])) : '—' ?></td>
                        <th>Gender</th>
                        <td><?= htmlspecialchars($s['gender'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Religion</th>
                        <td><?= htmlspecialchars($s['religion'] ?: '—') ?></td>
                        <th>Blood Group</th>
                        <td class="text-danger fw-bold"><?= htmlspecialchars($s['bgroup'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Birth Reg. No (BRN)</th>
                        <td><?= htmlspecialchars($s['brn'] ?: '—') ?></td>
                        <th>Unique Student ID</th>
                        <td><?= htmlspecialchars($s['uniqueid'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Student Mobile</th>
                        <td><?= htmlspecialchars($s['mobileself'] ?: '—') ?></td>
                        <th>Special Needs / Disability</th>
                        <td><?= htmlspecialchars($s['disables'] ?: 'None') ?></td>
                    </tr>
                </table>

                <!-- Parents Information -->
                <div class="section-title"><i class="bi bi-people-fill me-1"></i> Parents Information</div>
                <table class="info-tbl">
                    <tr>
                        <th>Father's Name (Eng)</th>
                        <td><?= htmlspecialchars($s['fname'] ?: '—') ?></td>
                        <th>Father's Name (Ben)</th>
                        <td><?= htmlspecialchars($s['fnameben'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Father's Occupation</th>
                        <td><?= htmlspecialchars($s['fprof'] ?: '—') ?></td>
                        <th>Father's Mobile</th>
                        <td><?= htmlspecialchars($s['fmobile'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Father's NID</th>
                        <td><?= htmlspecialchars($s['fnid'] ?: '—') ?></td>
                        <th>Father's Status</th>
                        <td><?= isset($s['falive']) && $s['falive'] == 0 ? '<span class="text-danger">Late / প্রয়াত</span>' : 'Alive / জীবিত' ?></td>
                    </tr>
                    <tr>
                        <th>Mother's Name (Eng)</th>
                        <td><?= htmlspecialchars($s['mname'] ?: '—') ?></td>
                        <th>Mother's Name (Ben)</th>
                        <td><?= htmlspecialchars($s['mnameben'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Mother's Occupation</th>
                        <td><?= htmlspecialchars($s['mprof'] ?: '—') ?></td>
                        <th>Mother's Mobile</th>
                        <td><?= htmlspecialchars($s['mmobile'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Mother's NID</th>
                        <td><?= htmlspecialchars($s['mnid'] ?: '—') ?></td>
                        <th>Mother's Status</th>
                        <td><?= isset($s['malive']) && $s['malive'] == 0 ? '<span class="text-danger">Late / প্রয়াত</span>' : 'Alive / জীবিত' ?></td>
                    </tr>
                </table>

                <!-- Guardian Information -->
                <div class="section-title"><i class="bi bi-shield-check me-1"></i> Guardian &amp; Emergency Contact</div>
                <table class="info-tbl">
                    <tr>
                        <th>Guardian Name (Eng)</th>
                        <td><?= htmlspecialchars($s['guarname'] ?: '—') ?></td>
                        <th>Guardian Name (Ben)</th>
                        <td><?= htmlspecialchars($s['guarnameben'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Relation with Student</th>
                        <td><?= htmlspecialchars($s['guarrelation'] ?: '—') ?></td>
                        <th>Guardian Mobile</th>
                        <td class="fw-bold"><?= htmlspecialchars($s['guarmobile'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Guardian NID</th>
                        <td><?= htmlspecialchars($s['guarnid'] ?: '—') ?></td>
                        <th>Guardian Email</th>
                        <td><?= htmlspecialchars($s['guaremail'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Guardian Address</th>
                        <td colspan="3"><?= htmlspecialchars($s['guaradd'] ?: '—') ?></td>
                    </tr>
                </table>

                <!-- Addresses -->
                <div class="section-title"><i class="bi bi-geo-alt-fill me-1"></i> Address Details</div>
                <table class="info-tbl">
                    <tr>
                        <th>Present Address</th>
                        <td colspan="3">
                            <?= htmlspecialchars(implode(', ', array_filter([$s['previll'] ?? '', $s['prepo'] ?? '', $s['preps'] ?? '', $s['predist'] ?? '']))) ?: '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Permanent Address</th>
                        <td colspan="3">
                            <?= htmlspecialchars(implode(', ', array_filter([$s['pervill'] ?? '', $s['perpo'] ?? '', $s['perps'] ?? '', $s['perdist'] ?? '']))) ?: '—' ?>
                        </td>
                    </tr>
                </table>

                <!-- Previous School / Admission Info -->
                <?php if (!empty($s['preins']) || !empty($s['tcno'])): ?>
                <div class="section-title"><i class="bi bi-building me-1"></i> Previous Academic Record</div>
                <table class="info-tbl">
                    <tr>
                        <th>Previous Institute</th>
                        <td><?= htmlspecialchars($s['preins'] ?: '—') ?></td>
                        <th>Institute Address</th>
                        <td><?= htmlspecialchars($s['preinsadd'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th>Previous TC No</th>
                        <td><?= htmlspecialchars($s['tcno'] ?: '—') ?></td>
                        <th>Board Reg No</th>
                        <td><?= htmlspecialchars($s['regdno'] ?: '—') ?></td>
                    </tr>
                </table>
                <?php endif; ?>

                <!-- Signatures -->
                <div class="mt-5 pt-4 d-print-block">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <small class="fw-bold text-dark">Prepared / Class Teacher</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <small class="fw-bold text-dark">Guardian Signature</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <small class="fw-bold text-dark">Headmaster / Principal Seal</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
</body>
</html>