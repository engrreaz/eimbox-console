<?php
require_once 'header.php';

// ১. ডাটা ফেচ করা
if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
    $stmt->bind_param("si", $teacher_id, $sccode);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();

    if (!$teacher) {
        echo "<div class='container-xxl p-4'><div class='alert alert-danger'>Teacher not found!</div></div>";
        require_once 'footer.php';
        exit;
    }
} else {
    echo "<script>window.location.href='teachers-list.php';</script>";
    exit;
}

// স্লট তালিকা ফেচ করা
$slots_list = [];
$sq = $conn->prepare("SELECT slotname FROM slots WHERE sccode = ? ORDER BY id ASC");
$sq->bind_param("i", $sccode);
$sq->execute();
$sr = $sq->get_result();
while ($srow = $sr->fetch_assoc()) {
    if (!empty($srow['slotname'])) {
        $slots_list[] = $srow['slotname'];
    }
}
if (empty($slots_list)) {
    $slots_list = ['School', 'College'];
}

// ডেজিগনেশন তালিকা ফেচ করা
$designations = [];
$dq = $conn->query("SELECT title, ranks FROM designation ORDER BY ranks ASC, sl ASC");
if ($dq) {
    while ($drow = $dq->fetch_assoc()) {
        $designations[] = $drow;
    }
}

// ২. ডাটা আপডেট হ্যান্ডলিং
$save_status = null;
$save_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    // Personal & Identity
    $tname      = trim($_POST['tname'] ?? '');
    $tnameb     = trim($_POST['tnameb'] ?? '');
    $fname      = trim($_POST['fname'] ?? '');
    $mname      = trim($_POST['mname'] ?? '');
    $spouse     = trim($_POST['spouse'] ?? '');
    $dob        = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender     = trim($_POST['gender'] ?? '');
    $religion   = trim($_POST['religion'] ?? '');
    $bgroup     = trim($_POST['bgroup'] ?? '');
    $nid        = trim($_POST['nid'] ?? '');
    $tin        = trim($_POST['tin'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $emergency  = trim($_POST['emergency'] ?? '');
    $rfidtag    = trim($_POST['rfidtag'] ?? '');
    $status     = trim($_POST['status'] ?? '1');
    $sl         = !empty($_POST['sl']) ? intval($_POST['sl']) : null;

    // Service & Timings
    $position   = trim($_POST['position'] ?? '');
    $ranks      = intval($_POST['ranks'] ?? 0);
    $slots      = trim($_POST['slots'] ?? '');
    $subjects   = trim($_POST['subjects'] ?? '');
    $jdate      = !empty($_POST['jdate']) ? $_POST['jdate'] : null;
    $fjdate     = !empty($_POST['fjdate']) ? $_POST['fjdate'] : '0000-00-00';
    $mpoindex   = trim($_POST['mpoindex'] ?? '');
    $curin      = !empty($_POST['curin']) ? $_POST['curin'] : '09:00:00';
    $curout     = !empty($_POST['curout']) ? $_POST['curout'] : '16:00:00';

    // Addresses
    $preadd     = trim($_POST['preadd'] ?? '');
    $previll    = trim($_POST['previll'] ?? '');
    $prepo      = trim($_POST['prepo'] ?? '');
    $preps      = trim($_POST['preps'] ?? '');
    $predist    = trim($_POST['predist'] ?? '');
    $pervill    = trim($_POST['pervill'] ?? '');
    $perpo      = trim($_POST['perpo'] ?? '');
    $perps      = trim($_POST['perps'] ?? '');
    $perdist    = trim($_POST['perdist'] ?? '');

    // MPO Bank
    $accno      = trim($_POST['accno'] ?? '');
    $bankname   = trim($_POST['bankname'] ?? '');
    $branch     = trim($_POST['branch'] ?? '');
    $routing    = trim($_POST['routing'] ?? '');

    // School Bank
    $accnosch   = trim($_POST['accnosch'] ?? '');
    $bnamesch   = trim($_POST['bnamesch'] ?? '');
    $bbrsch     = trim($_POST['bbrsch'] ?? '');
    $routesch   = trim($_POST['routesch'] ?? '');

    // PF Bank
    $accnopf    = trim($_POST['accnopf'] ?? '');
    $bnamepf    = trim($_POST['bnamepf'] ?? '');
    $bbrpf      = trim($_POST['bbrpf'] ?? '');
    $routepf    = trim($_POST['routepf'] ?? '');

    // Govt / MPO Salary
    $paycode    = intval($_POST['paycode'] ?? 0);
    $payscale   = intval($_POST['payscale'] ?? 0);
    $basic      = intval($_POST['basic'] ?? 0);
    $incentive  = intval($_POST['incentive'] ?? 0);
    $house      = intval($_POST['house'] ?? 0);
    $medical    = intval($_POST['medical'] ?? 0);
    $arrea      = intval($_POST['arrea'] ?? 0);
    $welfare    = intval($_POST['welfare'] ?? 0);
    $retire     = intval($_POST['retire'] ?? 0);
    $netamtgovt = intval($_POST['netamtgovt'] ?? 0);

    // School Salary
    $salary     = intval($_POST['salary'] ?? 0);
    $salery     = $salary; // sync backward compatibility
    $mobilevata = intval($_POST['mobilevata'] ?? 0);
    $travel     = intval($_POST['travel'] ?? 0);
    $medical2   = intval($_POST['medical2'] ?? 0);
    $exam       = intval($_POST['exam'] ?? 0);
    $festival   = intval($_POST['festival'] ?? 0);
    $pf         = intval($_POST['pf'] ?? 0);
    $net2       = intval($_POST['net2'] ?? 0);

    // Extra Custom Fields
    $ex_1       = trim($_POST['ex_1'] ?? '');
    $val_1      = trim($_POST['val_1'] ?? '');
    $ex_2       = trim($_POST['ex_2'] ?? '');
    $val_2      = trim($_POST['val_2'] ?? '');
    $ex_3       = trim($_POST['ex_3'] ?? '');
    $val_3      = trim($_POST['val_3'] ?? '');
    $ex_4       = trim($_POST['ex_4'] ?? '');
    $val_4      = trim($_POST['val_4'] ?? '');

    $modifieddate = date('Y-m-d H:i:s');

    $sql = "UPDATE teacher SET 
                sl=?, tname=?, tnameb=?, position=?, slots=?, jdate=?, ranks=?, subjects=?,
                fname=?, mname=?, spouse=?, emergency=?, preadd=?, previll=?, prepo=?, preps=?, predist=?,
                pervill=?, perpo=?, perps=?, perdist=?, dob=?, religion=?, gender=?, email=?, mobile=?,
                nid=?, bgroup=?, status=?, curin=?, curout=?, salery=?, fjdate=?, mpoindex=?, tin=?,
                accno=?, bankname=?, branch=?, routing=?, accnosch=?, bnamesch=?, bbrsch=?, routesch=?,
                accnopf=?, bnamepf=?, bbrpf=?, routepf=?, paycode=?, payscale=?, basic=?, incentive=?,
                house=?, medical=?, arrea=?, welfare=?, retire=?, netamtgovt=?, salary=?, mobilevata=?,
                travel=?, medical2=?, exam=?, festival=?, pf=?, net2=?, ex_1=?, val_1=?, ex_2=?, val_2=?,
                ex_3=?, val_3=?, ex_4=?, val_4=?, rfidtag=?, modifieddate=?
            WHERE tid=? AND sccode=?";

    $up_stmt = $conn->prepare($sql);
    $up_stmt->bind_param(
        "isssssissssssssssssssssssssssssisssssssssssssssiiiiiiiiiiiiiiiiiisssssssssssi",
        $sl, $tname, $tnameb, $position, $slots, $jdate, $ranks, $subjects,
        $fname, $mname, $spouse, $emergency, $preadd, $previll, $prepo, $preps, $predist,
        $pervill, $perpo, $perps, $perdist, $dob, $religion, $gender, $email, $mobile,
        $nid, $bgroup, $status, $curin, $curout, $salery, $fjdate, $mpoindex, $tin,
        $accno, $bankname, $branch, $routing, $accnosch, $bnamesch, $bbrsch, $routesch,
        $accnopf, $bnamepf, $bbrpf, $routepf, $paycode, $payscale, $basic, $incentive,
        $house, $medical, $arrea, $welfare, $retire, $netamtgovt, $salary, $mobilevata,
        $travel, $medical2, $exam, $festival, $pf, $net2, $ex_1, $val_1, $ex_2, $val_2,
        $ex_3, $val_3, $ex_4, $val_4, $rfidtag, $modifieddate,
        $teacher_id, $sccode
    );

    if ($up_stmt->execute()) {
        $save_status = 'success';
        $save_message = 'Teacher profile updated successfully!';
        
        // Re-fetch updated teacher data
        $stmt->execute();
        $teacher = $stmt->get_result()->fetch_assoc();
    } else {
        $save_status = 'error';
        $save_message = 'Failed to update teacher profile: ' . $conn->error;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Teacher /</span> Edit Profile</h4>
        <div class="d-flex gap-2">
            <a href="teacher-view.php?id=<?= urlencode($teacher['tid']) ?>" class="btn btn-outline-info btn-sm">
                <i class="bi bi-eye me-1"></i> View Profile
            </a>
            <a href="teachers-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <form method="POST" id="editTeacherForm">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body text-center">
                        <img src="<?= teacher_profile_image_path($teacher['tid']) ?>"
                             class="rounded mb-3 shadow-sm"
                             style="width: 140px; height: 140px; object-fit: cover; border: 3px solid #eee;">
                        <h5 class="mb-1 fw-bold"><?= htmlspecialchars($teacher['tname']) ?></h5>
                        <p class="text-muted small mb-2">ID: <span class="fw-bold text-primary"><?= htmlspecialchars($teacher['tid']) ?></span></p>
                        <div class="badge bg-label-primary mb-3"><?= htmlspecialchars($teacher['position']) ?></div>

                        <div class="text-start border-top pt-3">
                            <label class="form-label small text-muted fw-bold">Account Status</label>
                            <select name="status" class="form-select form-select-sm mb-3">
                                <option value="1" <?= ($teacher['status'] == '1' || $teacher['status'] === 'YES') ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($teacher['status'] == '0' || $teacher['status'] === 'NO') ? 'selected' : '' ?>>Inactive</option>
                            </select>

                            <label class="form-label small text-muted fw-bold">Display Order (SL)</label>
                            <input type="number" name="sl" class="form-control form-control-sm mb-3" value="<?= htmlspecialchars($teacher['sl'] ?? '') ?>">

                            <label class="form-label small text-muted fw-bold">RFID Card Tag</label>
                            <input type="text" name="rfidtag" class="form-control form-control-sm" placeholder="RFID No" value="<?= htmlspecialchars($teacher['rfidtag'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="nav-align-top mb-4">
                    <ul class="nav nav-tabs shadow-sm" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personal">
                                <i class="bi bi-person me-1"></i> Personal &amp; Bio
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-service">
                                <i class="bi bi-briefcase me-1"></i> Service &amp; Duty
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-address">
                                <i class="bi bi-geo-alt me-1"></i> Address
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bank">
                                <i class="bi bi-bank me-1"></i> Banking (MPO/School/PF)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-salary">
                                <i class="bi bi-cash-stack me-1"></i> Salary Structure
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-extra">
                                <i class="bi bi-sliders me-1"></i> Custom Fields
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border shadow-none p-4 bg-white rounded-bottom">

                        <!-- Tab 1: Personal -->
                        <div class="tab-pane fade show active" id="tab-personal">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Basic &amp; Family Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="tname" class="form-control" value="<?= htmlspecialchars($teacher['tname'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">নাম (বাংলায়)</label>
                                    <input type="text" name="tnameb" class="form-control" value="<?= htmlspecialchars($teacher['tnameb'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($teacher['fname'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mname" class="form-control" value="<?= htmlspecialchars($teacher['mname'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Spouse Name</label>
                                    <input type="text" name="spouse" class="form-control" value="<?= htmlspecialchars($teacher['spouse'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male" <?= ($teacher['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= ($teacher['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= ($teacher['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($teacher['dob'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <select name="religion" class="form-select">
                                        <option value="Islam" <?= ($teacher['religion'] == 'Islam') ? 'selected' : '' ?>>Islam</option>
                                        <option value="Hinduism" <?= ($teacher['religion'] == 'Hinduism' || $teacher['religion'] == 'Hindu') ? 'selected' : '' ?>>Hinduism</option>
                                        <option value="Christianity" <?= ($teacher['religion'] == 'Christianity' || $teacher['religion'] == 'Christian') ? 'selected' : '' ?>>Christianity</option>
                                        <option value="Buddhism" <?= ($teacher['religion'] == 'Buddhism' || $teacher['religion'] == 'Buddhist') ? 'selected' : '' ?>>Buddhism</option>
                                        <option value="Other" <?= ($teacher['religion'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Blood Group</label>
                                    <select name="bgroup" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg): ?>
                                            <option value="<?= $bg ?>" <?= ($teacher['bgroup'] == $bg) ? 'selected' : '' ?>><?= $bg ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary mt-4 mb-3 border-bottom pb-2">Contact &amp; Identification</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($teacher['mobile'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" name="emergency" class="form-control" value="<?= htmlspecialchars($teacher['emergency'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">National ID (NID)</label>
                                    <input type="text" name="nid" class="form-control" value="<?= htmlspecialchars($teacher['nid'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax ID (TIN)</label>
                                    <input type="text" name="tin" class="form-control" value="<?= htmlspecialchars($teacher['tin'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Service -->
                        <div class="tab-pane fade" id="tab-service">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Designation, Slot &amp; Rank</h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Designation / Position <span class="text-danger">*</span></label>
                                    <select name="position" id="positionSelect" class="form-select" onchange="syncDesignationRank()">
                                        <option value="">Select Designation</option>
                                        <?php foreach ($designations as $d): ?>
                                            <option value="<?= htmlspecialchars($d['title']) ?>" data-rank="<?= $d['ranks'] ?>" <?= ($teacher['position'] == $d['title']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($d['title']) ?> (Rank: <?= $d['ranks'] ?><?= $d['ranks'] > 50 ? ' - Staff' : ' - Teacher' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Rank Priority</label>
                                    <input type="number" name="ranks" id="ranksInput" class="form-control" value="<?= htmlspecialchars($teacher['ranks'] ?? '20') ?>">
                                    <small class="text-muted" style="font-size: 11px;">Rank &gt; 50 is treated as Staff</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slot / Unit <span class="text-danger">*</span></label>
                                    <select name="slots" class="form-select">
                                        <?php foreach ($slots_list as $sl_item): ?>
                                            <option value="<?= htmlspecialchars($sl_item) ?>" <?= ($teacher['slots'] == $sl_item) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sl_item) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Primary Subject</label>
                                    <input type="text" name="subjects" class="form-control" placeholder="e.g. Mathematics, English" value="<?= htmlspecialchars($teacher['subjects'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="jdate" class="form-control" value="<?= htmlspecialchars($teacher['jdate'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First Joining Date (Regularized)</label>
                                    <input type="date" name="fjdate" class="form-control" value="<?= htmlspecialchars($teacher['fjdate'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">MPO Index Number</label>
                                    <input type="text" name="mpoindex" class="form-control" placeholder="MPO Index" value="<?= htmlspecialchars($teacher['mpoindex'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Standard In-Time (Attendance)</label>
                                    <input type="time" step="1" name="curin" class="form-control" value="<?= htmlspecialchars($teacher['curin'] ?? '09:00:00') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Standard Out-Time (Attendance)</label>
                                    <input type="time" step="1" name="curout" class="form-control" value="<?= htmlspecialchars($teacher['curout'] ?? '16:00:00') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Address -->
                        <div class="tab-pane fade" id="tab-address">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-house me-1"></i> Present Address</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Village / House</label>
                                    <input type="text" name="previll" class="form-control" value="<?= htmlspecialchars($teacher['previll'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Post Office (PO)</label>
                                    <input type="text" name="prepo" class="form-control" value="<?= htmlspecialchars($teacher['prepo'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Thana / Upazila (PS)</label>
                                    <input type="text" name="preps" class="form-control" value="<?= htmlspecialchars($teacher['preps'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">District</label>
                                    <input type="text" name="predist" class="form-control" value="<?= htmlspecialchars($teacher['predist'] ?? '') ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Full Address Note</label>
                                    <input type="text" name="preadd" class="form-control" placeholder="Full present address..." value="<?= htmlspecialchars($teacher['preadd'] ?? '') ?>">
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-geo-alt me-1"></i> Permanent Address</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Village / House</label>
                                    <input type="text" name="pervill" class="form-control" value="<?= htmlspecialchars($teacher['pervill'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Post Office (PO)</label>
                                    <input type="text" name="perpo" class="form-control" value="<?= htmlspecialchars($teacher['perpo'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Thana / Upazila (PS)</label>
                                    <input type="text" name="perps" class="form-control" value="<?= htmlspecialchars($teacher['perps'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">District</label>
                                    <input type="text" name="perdist" class="form-control" value="<?= htmlspecialchars($teacher['perdist'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Banking -->
                        <div class="tab-pane fade" id="tab-bank">
                            <!-- MPO Bank -->
                            <div class="p-3 mb-4 rounded border bg-light-subtle">
                                <h6 class="fw-bold text-success mb-3"><i class="bi bi-shield-check me-1"></i> MPO (Govt) Bank Account</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="accno" class="form-control font-monospace" value="<?= htmlspecialchars($teacher['accno'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bankname" class="form-control" value="<?= htmlspecialchars($teacher['bankname'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Branch</label>
                                        <input type="text" name="branch" class="form-control" value="<?= htmlspecialchars($teacher['branch'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Routing No</label>
                                        <input type="text" name="routing" class="form-control" value="<?= htmlspecialchars($teacher['routing'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- School Bank -->
                            <div class="p-3 mb-4 rounded border bg-light-subtle">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building me-1"></i> School / Institutional Bank Account</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="accnosch" class="form-control font-monospace" value="<?= htmlspecialchars($teacher['accnosch'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bnamesch" class="form-control" value="<?= htmlspecialchars($teacher['bnamesch'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Branch</label>
                                        <input type="text" name="bbrsch" class="form-control" value="<?= htmlspecialchars($teacher['bbrsch'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Routing No</label>
                                        <input type="text" name="routesch" class="form-control" value="<?= htmlspecialchars($teacher['routesch'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- PF Bank -->
                            <div class="p-3 rounded border bg-light-subtle">
                                <h6 class="fw-bold text-info mb-3"><i class="bi bi-piggy-bank me-1"></i> Provident Fund (PF) Bank Account</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="accnopf" class="form-control font-monospace" value="<?= htmlspecialchars($teacher['accnopf'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bnamepf" class="form-control" value="<?= htmlspecialchars($teacher['bnamepf'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Branch</label>
                                        <input type="text" name="bbrpf" class="form-control" value="<?= htmlspecialchars($teacher['bbrpf'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Routing No</label>
                                        <input type="text" name="routepf" class="form-control" value="<?= htmlspecialchars($teacher['routepf'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 5: Salary Setup -->
                        <div class="tab-pane fade" id="tab-salary">
                            <h6 class="fw-bold text-success mb-3 border-bottom pb-2"><i class="bi bi-currency-dollar me-1"></i> Government (MPO) Salary Breakdown</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Pay Code</label>
                                    <input type="number" name="paycode" class="form-control" value="<?= htmlspecialchars($teacher['paycode'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pay Scale</label>
                                    <input type="number" name="payscale" class="form-control" value="<?= htmlspecialchars($teacher['payscale'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Basic Salary</label>
                                    <input type="number" name="basic" id="gov_basic" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['basic'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Incentive</label>
                                    <input type="number" name="incentive" id="gov_incentive" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['incentive'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">House Rent</label>
                                    <input type="number" name="house" id="gov_house" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['house'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Medical Allowance</label>
                                    <input type="number" name="medical" id="gov_medical" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['medical'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Arrear</label>
                                    <input type="number" name="arrea" id="gov_arrea" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['arrea'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Welfare (Deduction)</label>
                                    <input type="number" name="welfare" id="gov_welfare" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['welfare'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Retirement (Deduction)</label>
                                    <input type="number" name="retire" id="gov_retire" class="form-control calc-gov" value="<?= htmlspecialchars($teacher['retire'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-success">Net Govt Amount</label>
                                    <input type="number" name="netamtgovt" id="gov_net" class="form-control fw-bold border-success text-success bg-light" value="<?= htmlspecialchars($teacher['netamtgovt'] ?? '0') ?>">
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-wallet2 me-1"></i> School / Institutional Pay Breakdown</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Base School Salary</label>
                                    <input type="number" name="salary" id="sch_salary" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['salary'] ?? $teacher['salery'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mobile Allowance</label>
                                    <input type="number" name="mobilevata" id="sch_mobile" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['mobilevata'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Travel Allowance</label>
                                    <input type="number" name="travel" id="sch_travel" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['travel'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Medical Allowance (Inst.)</label>
                                    <input type="number" name="medical2" id="sch_med" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['medical2'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Exam Remuneration</label>
                                    <input type="number" name="exam" id="sch_exam" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['exam'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Festival Bonus</label>
                                    <input type="number" name="festival" id="sch_fest" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['festival'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">PF Contribution (Deduction)</label>
                                    <input type="number" name="pf" id="sch_pf" class="form-control calc-sch" value="<?= htmlspecialchars($teacher['pf'] ?? '0') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-primary">Net School Pay</label>
                                    <input type="number" name="net2" id="sch_net" class="form-control fw-bold border-primary text-primary bg-light" value="<?= htmlspecialchars($teacher['net2'] ?? '0') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 6: Custom Fields -->
                        <div class="tab-pane fade" id="tab-extra">
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Custom / Extended Attributes</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Field 1 Title</label>
                                    <input type="text" name="ex_1" class="form-control mb-2" placeholder="e.g. Higher Degree" value="<?= htmlspecialchars($teacher['ex_1'] ?? '') ?>">
                                    <label class="form-label">Field 1 Value</label>
                                    <input type="text" name="val_1" class="form-control" placeholder="Value..." value="<?= htmlspecialchars($teacher['val_1'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Field 2 Title</label>
                                    <input type="text" name="ex_2" class="form-control mb-2" placeholder="e.g. Training Details" value="<?= htmlspecialchars($teacher['ex_2'] ?? '') ?>">
                                    <label class="form-label">Field 2 Value</label>
                                    <input type="text" name="val_2" class="form-control" placeholder="Value..." value="<?= htmlspecialchars($teacher['val_2'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Field 3 Title</label>
                                    <input type="text" name="ex_3" class="form-control mb-2" placeholder="e.g. Registration No" value="<?= htmlspecialchars($teacher['ex_3'] ?? '') ?>">
                                    <label class="form-label">Field 3 Value</label>
                                    <input type="text" name="val_3" class="form-control" placeholder="Value..." value="<?= htmlspecialchars($teacher['val_3'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Field 4 Title</label>
                                    <input type="text" name="ex_4" class="form-control mb-2" placeholder="e.g. Additional Duty" value="<?= htmlspecialchars($teacher['ex_4'] ?? '') ?>">
                                    <label class="form-label">Field 4 Value</label>
                                    <input type="text" name="val_4" class="form-control" placeholder="Value..." value="<?= htmlspecialchars($teacher['val_4'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" name="update_teacher" class="btn btn-primary px-5 shadow-sm">
                            <i class="bi bi-save me-1"></i> Update Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function syncDesignationRank() {
        const select = document.getElementById('positionSelect');
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.getAttribute('data-rank')) {
            document.getElementById('ranksInput').value = selectedOpt.getAttribute('data-rank');
        }
    }

    // Calculation helper for govt net pay
    function calculateGovNet() {
        const basic = parseFloat(document.getElementById('gov_basic').value) || 0;
        const incentive = parseFloat(document.getElementById('gov_incentive').value) || 0;
        const house = parseFloat(document.getElementById('gov_house').value) || 0;
        const medical = parseFloat(document.getElementById('gov_medical').value) || 0;
        const arrea = parseFloat(document.getElementById('gov_arrea').value) || 0;
        const welfare = parseFloat(document.getElementById('gov_welfare').value) || 0;
        const retire = parseFloat(document.getElementById('gov_retire').value) || 0;
        
        const net = (basic + incentive + house + medical + arrea) - (welfare + retire);
        document.getElementById('gov_net').value = Math.max(0, net);
    }

    // Calculation helper for school net pay
    function calculateSchNet() {
        const sal = parseFloat(document.getElementById('sch_salary').value) || 0;
        const mob = parseFloat(document.getElementById('sch_mobile').value) || 0;
        const trv = parseFloat(document.getElementById('sch_travel').value) || 0;
        const med = parseFloat(document.getElementById('sch_med').value) || 0;
        const exm = parseFloat(document.getElementById('sch_exam').value) || 0;
        const fst = parseFloat(document.getElementById('sch_fest').value) || 0;
        const pf = parseFloat(document.getElementById('sch_pf').value) || 0;
        
        const net = (sal + mob + trv + med + exm + fst) - pf;
        document.getElementById('sch_net').value = Math.max(0, net);
    }

    document.querySelectorAll('.calc-gov').forEach(input => {
        input.addEventListener('input', calculateGovNet);
    });

    document.querySelectorAll('.calc-sch').forEach(input => {
        input.addEventListener('input', calculateSchNet);
    });

    <?php if ($save_status === 'success'): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= addslashes($save_message) ?>',
                timer: 2000,
                showConfirmButton: false
            });
        }
    <?php elseif ($save_status === 'error'): ?>
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= addslashes($save_message) ?>'
            });
        }
    <?php endif; ?>
</script>
</body>
</html>