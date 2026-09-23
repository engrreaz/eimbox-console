<?php
require_once 'header.php';

// ১. tid দিয়ে ডাটা ফেচ করা
if (isset($_GET['id'])) {
    $tid = mysqli_real_escape_string($conn, $_GET['id']);

    // sccode সহ কুয়েরি করা যেন অন্য স্কুলের ডাটা দেখা না যায়
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
    $stmt->bind_param("si", $tid, $sccode);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();

    if (!$teacher) {
        echo "<div class='container-xxl p-4'><div class='alert alert-danger'>Teacher record not found!</div></div>";
        require_once 'footer.php';
        exit;
    }

    // গ্রুপ নির্ধারণ (ranks > 50 হলে Staff)
    $is_staff = ($teacher['ranks'] > 50);

    // ২. teacher_salary_structure থেকে সর্বশেষ বেতন স্ট্রাকচার ফেচ করা
    $salary_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC LIMIT 1");
    $salary_stmt->bind_param("si", $tid, $sccode);
    $salary_stmt->execute();
    $latest_salary = $salary_stmt->get_result()->fetch_assoc();

    // বেতন হিস্টোরি ফেচ করা
    $history_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC");
    $history_stmt->bind_param("si", $tid, $sccode);
    $history_stmt->execute();
    $salary_history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // যদি teacher_salary_structure এ এন্ট্রি থাকে তবে সেটি প্রায়োরিটি পাবে, নতুবা teacher টেবিল
    $active_salary = $latest_salary ?: $teacher;
    $has_custom_structure = !empty($latest_salary);

} else {
    echo "<script>window.location.href='teachers-list.php';</script>";
    exit;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0">
            <span class="text-muted fw-light">Teacher /</span> Profile View
        </h4>
        <div class="d-flex gap-2">
            <a href="teacher-edit.php?id=<?= urlencode($teacher['tid']) ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
            <a href="salary-settings.php?id=<?= urlencode($teacher['tid']) ?>" class="btn btn-success btn-sm">
                <i class="bi bi-cash-stack me-1"></i> Update Salary
            </a>
            <a href="attendance-view.php?tid=<?= urlencode($teacher['tid']) ?>" class="btn btn-info btn-sm">
                <i class="bi bi-calendar-check me-1"></i> View Attendance
            </a>
            <a href="teachers-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Sidebar / Overview Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?= teacher_profile_image_path($teacher['tid']) ?>"
                             alt="Teacher Image"
                             class="rounded shadow-sm mb-3"
                             style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #f8f9fa;">
                        <h4 class="mb-1 fw-bold"><?= htmlspecialchars($teacher['tname']) ?></h4>
                        <?php if (!empty($teacher['tnameb'])): ?>
                            <p class="text-muted mb-1" style="font-size: 15px;"><?= htmlspecialchars($teacher['tnameb']) ?></p>
                        <?php endif; ?>
                        <p class="text-muted mb-2"><?= htmlspecialchars($teacher['position']) ?> (<?= htmlspecialchars($teacher['slots']) ?>)</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-label-<?= $is_staff ? 'warning' : 'info' ?> rounded-pill">
                                <?= $is_staff ? 'Staff Group' : 'Teacher Group' ?>
                            </span>
                            <span class="badge bg-label-<?= ($teacher['status'] == '1' || $teacher['status'] === 'YES') ? 'success' : 'danger' ?> rounded-pill">
                                <?= ($teacher['status'] == '1' || $teacher['status'] === 'YES') ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-list border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Teacher ID:</span>
                            <span class="fw-bold text-primary font-monospace"><?= htmlspecialchars($teacher['tid']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mobile:</span>
                            <span class="fw-semibold"><?= htmlspecialchars($teacher['mobile'] ?: '—') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Email:</span>
                            <span class="small"><?= htmlspecialchars($teacher['email'] ?: '—') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">MPO Index:</span>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($teacher['mpoindex'] ?: 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">RFID Card:</span>
                            <span class="badge bg-label-secondary font-monospace"><?= htmlspecialchars($teacher['rfidtag'] ?: 'Not Assigned') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-briefcase me-1"></i> Service &amp; Duty Information</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">Joining Date:</span>
                            <span class="fw-medium"><?= !empty($teacher['jdate']) ? date('d M, Y', strtotime($teacher['jdate'])) : '—' ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">First Regularized:</span>
                            <span class="fw-medium"><?= (!empty($teacher['fjdate']) && $teacher['fjdate'] != '0000-00-00') ? date('d M, Y', strtotime($teacher['fjdate'])) : '—' ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">Primary Subject:</span>
                            <span class="fw-medium"><?= htmlspecialchars($teacher['subjects'] ?: 'General') ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">Standard Duty In/Out:</span>
                            <span class="badge bg-light text-dark border font-monospace">
                                <?= date('h:i A', strtotime($teacher['curin'] ?? '09:00:00')) ?> - <?= date('h:i A', strtotime($teacher['curout'] ?? '16:00:00')) ?>
                            </span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted small">Rank Priority SL:</span>
                            <span class="badge bg-label-primary"><?= htmlspecialchars($teacher['ranks'] ?? '0') ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted small">Last Modified:</span>
                            <span class="small text-muted"><?= htmlspecialchars($teacher['modifieddate'] ?? '—') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Column / Details Tabs -->
        <div class="col-xl-8 col-lg-7">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs border-bottom-0 shadow-sm bg-white rounded-top" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personal">
                            <i class="bi bi-person me-1"></i> Personal &amp; Bio
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-address">
                            <i class="bi bi-geo-alt me-1"></i> Address
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-banking">
                            <i class="bi bi-bank me-1"></i> Banking Accounts
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finance">
                            <i class="bi bi-cash-stack me-1"></i> Salary Structure
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-extra">
                            <i class="bi bi-sliders me-1"></i> Other Details
                        </button>
                    </li>
                </ul>

                <div class="tab-content bg-white shadow-sm rounded-bottom p-4">

                    <!-- Tab 1: Personal & Bio -->
                    <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Family &amp; Identification</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Full Name (English)</small>
                                        <p class="fw-bold fs-6 mb-0"><?= htmlspecialchars($teacher['tname']) ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Name (Bengali)</small>
                                        <p class="fw-bold fs-6 mb-0"><?= htmlspecialchars($teacher['tnameb'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Father's Name</small>
                                        <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['fname'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Mother's Name</small>
                                        <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['mname'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Spouse Name</small>
                                        <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['spouse'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Emergency Contact</small>
                                        <p class="fw-medium text-danger mb-0"><?= htmlspecialchars($teacher['emergency'] ?: '—') ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Personal Demographics</h6>
                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Gender</small>
                                        <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['gender'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Date of Birth</small>
                                        <p class="fw-medium mb-0"><?= !empty($teacher['dob']) ? date('d M, Y', strtotime($teacher['dob'])) : '—' ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Blood Group</small>
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold"><?= htmlspecialchars($teacher['bgroup'] ?: '—') ?></span>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Religion</small>
                                        <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['religion'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">NID Number</small>
                                        <p class="fw-medium font-monospace mb-0"><?= htmlspecialchars($teacher['nid'] ?: '—') ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Tax ID (TIN)</small>
                                        <p class="fw-medium font-monospace mb-0"><?= htmlspecialchars($teacher['tin'] ?: '—') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Address -->
                    <div class="tab-pane fade" id="tab-address" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-house me-1"></i> Present Address</h6>
                                    <p class="mb-1 small"><strong class="text-muted">Village:</strong> <?= htmlspecialchars($teacher['previll'] ?: '—') ?></p>
                                    <p class="mb-1 small"><strong class="text-muted">Post Office:</strong> <?= htmlspecialchars($teacher['prepo'] ?: '—') ?></p>
                                    <p class="mb-1 small"><strong class="text-muted">Police Station / PS:</strong> <?= htmlspecialchars($teacher['preps'] ?: '—') ?></p>
                                    <p class="mb-2 small"><strong class="text-muted">District:</strong> <?= htmlspecialchars($teacher['predist'] ?: '—') ?></p>
                                    <?php if (!empty($teacher['preadd'])): ?>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="text-muted d-block">Address Note:</small>
                                            <span class="small"><?= htmlspecialchars($teacher['preadd']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-geo-alt me-1"></i> Permanent Address</h6>
                                    <p class="mb-1 small"><strong class="text-muted">Village:</strong> <?= htmlspecialchars($teacher['pervill'] ?: '—') ?></p>
                                    <p class="mb-1 small"><strong class="text-muted">Post Office:</strong> <?= htmlspecialchars($teacher['perpo'] ?: '—') ?></p>
                                    <p class="mb-1 small"><strong class="text-muted">Police Station / PS:</strong> <?= htmlspecialchars($teacher['perps'] ?: '—') ?></p>
                                    <p class="mb-0 small"><strong class="text-muted">District:</strong> <?= htmlspecialchars($teacher['perdist'] ?: '—') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Banking -->
                    <div class="tab-pane fade" id="tab-banking" role="tabpanel">
                        <div class="row g-4">
                            <!-- MPO Bank -->
                            <div class="col-12">
                                <div class="p-3 border rounded border-success-subtle bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-success m-0"><i class="bi bi-shield-check me-1"></i> Government (MPO) Bank Account</h6>
                                        <span class="badge bg-success-subtle text-success border border-success">MPO Account</span>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-3"><small class="text-muted d-block">Account No</small><strong class="font-monospace"><?= htmlspecialchars($active_salary['accno'] ?: '—') ?></strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Bank Name</small><span><?= htmlspecialchars($active_salary['bankname'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Branch</small><span><?= htmlspecialchars($active_salary['branch'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Routing No</small><span class="font-monospace"><?= htmlspecialchars($active_salary['routing'] ?: '—') ?></span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- School Bank -->
                            <div class="col-12">
                                <div class="p-3 border rounded border-primary-subtle bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-primary m-0"><i class="bi bi-building me-1"></i> School / Institutional Bank Account</h6>
                                        <span class="badge bg-primary-subtle text-primary border border-primary">School Account</span>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-3"><small class="text-muted d-block">Account No</small><strong class="font-monospace"><?= htmlspecialchars($active_salary['accnosch'] ?: '—') ?></strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Bank Name</small><span><?= htmlspecialchars($active_salary['bnamesch'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Branch</small><span><?= htmlspecialchars($active_salary['bbrsch'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Routing No</small><span class="font-monospace"><?= htmlspecialchars($active_salary['routesch'] ?: '—') ?></span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- PF Bank -->
                            <div class="col-12">
                                <div class="p-3 border rounded border-info-subtle bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-info m-0"><i class="bi bi-piggy-bank me-1"></i> Provident Fund (PF) Bank Account</h6>
                                        <span class="badge bg-info-subtle text-info border border-info">PF Account</span>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-3"><small class="text-muted d-block">Account No</small><strong class="font-monospace"><?= htmlspecialchars($active_salary['accnopf'] ?: '—') ?></strong></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Bank Name</small><span><?= htmlspecialchars($active_salary['bnamepf'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Branch</small><span><?= htmlspecialchars($active_salary['bbrpf'] ?: '—') ?></span></div>
                                        <div class="col-md-3"><small class="text-muted d-block">Routing No</small><span class="font-monospace"><?= htmlspecialchars($active_salary['routepf'] ?: '—') ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Salary Structure -->
                    <div class="tab-pane fade" id="tab-finance" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary fw-bold m-0"><i class="bi bi-currency-dollar me-1"></i> Latest Salary Structure</h6>
                            <a href="salary-settings.php?id=<?= urlencode($teacher['tid']) ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Configure / Change Structure
                            </a>
                        </div>

                        <?php if ($has_custom_structure): ?>
                            <div class="alert alert-success py-2 mb-3">
                                <small><i class="bi bi-check-circle-fill me-1"></i> <strong>Active Salary Structure:</strong> Effective from <strong><?= date('d M, Y', strtotime($latest_salary['applydate'])) ?></strong> (Updated: <?= $latest_salary['modifieddate'] ?? '—' ?>)</small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <small><i class="bi bi-info-circle me-1"></i> Showing baseline salary from teacher master profile. Click "Configure / Change Structure" to record time-based salary structure updates.</small>
                            </div>
                        <?php endif; ?>

                        <!-- Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Govt Basic Salary</small>
                                    <h4 class="fw-bold mb-0 text-dark">৳<?= number_format($active_salary['basic'] ?? 0) ?></h4>
                                    <small class="text-muted">Pay Scale: <?= $active_salary['payscale'] ?? 0 ?> (Code: <?= $active_salary['paycode'] ?? 0 ?>)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Net Govt (MPO) Pay</small>
                                    <h4 class="fw-bold mb-0 text-success">৳<?= number_format($active_salary['netamtgovt'] ?? 0) ?></h4>
                                    <small class="text-success">After Welfare &amp; Retirement</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center bg-light">
                                    <small class="text-muted d-block">Net School Pay</small>
                                    <h4 class="fw-bold mb-0 text-primary">৳<?= number_format($active_salary['net2'] ?? 0) ?></h4>
                                    <small class="text-primary">After PF Deductions</small>
                                </div>
                            </div>
                        </div>

                        <!-- Govt MPO Breakdown Table -->
                        <h6 class="fw-bold text-success mb-2 border-bottom pb-1">Govt (MPO) Detailed Breakdown</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Basic</th>
                                        <th>Incentive</th>
                                        <th>House Rent</th>
                                        <th>Medical</th>
                                        <th>Arrear</th>
                                        <th class="text-danger">Welfare (-)</th>
                                        <th class="text-danger">Retire (-)</th>
                                        <th class="table-success fw-bold">Net Govt Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-monospace">
                                        <td>৳<?= number_format($active_salary['basic'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['incentive'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['house'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['medical'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['arrea'] ?? 0) ?></td>
                                        <td class="text-danger">৳<?= number_format($active_salary['welfare'] ?? 0) ?></td>
                                        <td class="text-danger">৳<?= number_format($active_salary['retire'] ?? 0) ?></td>
                                        <td class="table-success fw-bold text-success">৳<?= number_format($active_salary['netamtgovt'] ?? 0) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- School Breakdown Table -->
                        <h6 class="fw-bold text-primary mb-2 border-bottom pb-1">School / Institutional Detailed Breakdown</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Base Salary</th>
                                        <th>Mobile</th>
                                        <th>Travel</th>
                                        <th>Medical</th>
                                        <th>Exam</th>
                                        <th>Festival</th>
                                        <th class="text-danger">PF (-)</th>
                                        <th class="table-primary fw-bold">Net School Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-monospace">
                                        <td>৳<?= number_format($active_salary['salary'] ?? $active_salary['salery'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['mobilevata'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['travel'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['medical2'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['exam'] ?? 0) ?></td>
                                        <td>৳<?= number_format($active_salary['festival'] ?? 0) ?></td>
                                        <td class="text-danger">৳<?= number_format($active_salary['pf'] ?? 0) ?></td>
                                        <td class="table-primary fw-bold text-primary">৳<?= number_format($active_salary['net2'] ?? 0) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Salary Structure History -->
                        <?php if (!empty($salary_history)): ?>
                            <h6 class="fw-bold text-dark mb-2 border-bottom pb-1"><i class="bi bi-clock-history me-1"></i> Salary Structure Change History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped border">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Effective Date</th>
                                            <th>Govt Basic</th>
                                            <th>Govt Net</th>
                                            <th>School Base</th>
                                            <th>School Net</th>
                                            <th>Modified Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small font-monospace">
                                        <?php foreach ($salary_history as $hist): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?= date('d M, Y', strtotime($hist['applydate'])) ?></td>
                                                <td>৳<?= number_format($hist['basic'] ?? 0) ?></td>
                                                <td class="text-success fw-bold">৳<?= number_format($hist['netamtgovt'] ?? 0) ?></td>
                                                <td>৳<?= number_format($hist['salary'] ?? 0) ?></td>
                                                <td class="text-primary fw-bold">৳<?= number_format($hist['net2'] ?? 0) ?></td>
                                                <td class="text-muted"><?= $hist['modifieddate'] ?? '—' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 5: Custom Fields -->
                    <div class="tab-pane fade" id="tab-extra" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Custom / Extended Attributes</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block"><?= htmlspecialchars($teacher['ex_1'] ?: 'Field 1 (Not Set)') ?></small>
                                    <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['val_1'] ?: '—') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block"><?= htmlspecialchars($teacher['ex_2'] ?: 'Field 2 (Not Set)') ?></small>
                                    <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['val_2'] ?: '—') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block"><?= htmlspecialchars($teacher['ex_3'] ?: 'Field 3 (Not Set)') ?></small>
                                    <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['val_3'] ?: '—') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block"><?= htmlspecialchars($teacher['ex_4'] ?: 'Field 4 (Not Set)') ?></small>
                                    <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['val_4'] ?: '—') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<style>
    .bg-label-primary {
        background-color: #e7e7ff;
        color: #696cff;
    }
    .bg-label-success {
        background-color: #e8fadf;
        color: #71dd37;
    }
    .bg-label-warning {
        background-color: #fff2e2;
        color: #ffab00;
    }
    .bg-label-info {
        background-color: #d7f5fc;
        color: #03c3ec;
    }
    .bg-label-secondary {
        background-color: #ebedef;
        color: #8592a3;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #696cff !important;
        color: #696cff !important;
        font-weight: bold;
    }
</style>
</body>
</html>