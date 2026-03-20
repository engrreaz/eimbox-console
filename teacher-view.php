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
        echo "<div class='alert alert-danger m-4'>Teacher record not found!</div>";
        exit;
    }

    // গ্রুপ নির্ধারণ (ranks >= 50 হলে Staff)
    $is_staff = ($teacher['ranks'] >= 50);
} else {
    header("Location: teacher-list.php");
    exit;
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0">
            <span class="text-muted fw-light">Teacher /</span> Profile View
        </h4>
        <div class="d-flex gap-2">
            <a href="teacher-edit.php?id=<?= $teacher['tid'] ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
            <a href="teacher-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?= teacher_profile_image_path($tid) ?>"
                             alt="Teacher Image"
                            class="rounded shadow-sm mb-3"
                            style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #f8f9fa;">
                        <h4 class="mb-1 fw-bold"><?= $teacher['tname'] ?></h4>
                        <p class="text-muted mb-2"><?= $teacher['position'] ?> (<?= $teacher['slots'] ?>)</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-label-<?= $is_staff ? 'warning' : 'info' ?> rounded-pill">
                                <?= $is_staff ? 'Staff Group' : 'Teacher Group' ?>
                            </span>
                            <span
                                class="badge bg-label-<?= ($teacher['status'] == '1') ? 'success' : 'danger' ?> rounded-pill">
                                <?= ($teacher['status'] == '1') ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-list border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Teacher ID:</span>
                            <span class="fw-bold text-primary"><?= $teacher['tid'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mobile:</span>
                            <span class="fw-semibold"><?= $teacher['mobile'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Email:</span>
                            <span class="small"><?= $teacher['email'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">MPO Index:</span>
                            <span class="badge bg-light text-dark border"><?= $teacher['mpoindex'] ?: 'N/A' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Service Timeline</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-calendar-check text-primary me-2"></i>
                            <div>
                                <small class="text-muted d-block">Joining Date</small>
                                <span class="fw-medium"><?= date('d M, Y', strtotime($teacher['jdate'])) ?></span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-clock-history text-info me-2"></i>
                            <div>
                                <small class="text-muted d-block">Modified Date</small>
                                <span class="fw-medium"><?= $teacher['modifieddate'] ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs border-bottom-0 shadow-sm bg-white rounded-top" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#tab-personal">
                            <i class="bi bi-person me-1"></i> Personal
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-address">
                            <i class="bi bi-geo-alt me-1"></i> Address
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finance">
                            <i class="bi bi-bank me-1"></i> Finance & Salary
                        </button>
                    </li>
                </ul>
                <div class="tab-content bg-white shadow-sm rounded-bottom p-4">

                    <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Identification & Family</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Name (Bengali)</small>
                                        <p class="fw-bold fs-5"><?= $teacher['tnameb'] ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Father's Name</small>
                                        <p class="fw-medium"><?= $teacher['fname'] ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Mother's Name</small>
                                        <p class="fw-medium"><?= $teacher['mname'] ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Spouse Name</small>
                                        <p class="fw-medium"><?= $teacher['spouse'] ?: 'N/A' ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Bio Data</h6>
                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">NID Number</small>
                                        <p class="fw-medium"><?= $teacher['nid'] ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Date of Birth</small>
                                        <p class="fw-medium"><?= $teacher['dob'] ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Blood Group</small>
                                        <p class="badge bg-danger bg-opacity-10 text-danger"><?= $teacher['bgroup'] ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Religion</small>
                                        <p class="fw-medium"><?= $teacher['religion'] ?></p>
                                    </div>
                                    <div class="col-sm-4">
                                        <small class="text-muted d-block">Emergency Contact</small>
                                        <p class="fw-medium text-danger"><?= $teacher['emergency'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-address" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-house me-1"></i> Present Address</h6>
                                    <p class="mb-1 small"><?= $teacher['previll'] ?></p>
                                    <p class="mb-1 small">PO: <?= $teacher['prepo'] ?>, PS: <?= $teacher['preps'] ?></p>
                                    <p class="mb-0 small">Dist: <?= $teacher['predist'] ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light-subtle h-100">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-geo me-1"></i> Permanent Address</h6>
                                    <p class="mb-1 small"><?= $teacher['pervill'] ?></p>
                                    <p class="mb-1 small">PO: <?= $teacher['perpo'] ?>, PS: <?= $teacher['perps'] ?></p>
                                    <p class="mb-0 small">Dist: <?= $teacher['perdist'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-finance" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Banking Details</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered border-light">
                                        <tr class="bg-light small">
                                            <th>Acc Type</th>
                                            <th>Account Number</th>
                                            <th>Bank Name</th>
                                            <th>Branch / Routing</th>
                                        </tr>
                                        <tr>
                                            <td class="small fw-bold">MPO (Govt)</td>
                                            <td class="font-monospace"><?= $teacher['accno'] ?></td>
                                            <td class="small"><?= $teacher['bankname'] ?></td>
                                            <td class="small"><?= $teacher['branch'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="small fw-bold">School Unit</td>
                                            <td class="font-monospace"><?= $teacher['accnosch'] ?></td>
                                            <td class="small"><?= $teacher['bnamesch'] ?></td>
                                            <td class="small"><?= $teacher['bbrsch'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Monthly Salary Structure</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <small class="text-muted d-block">Basic Salary</small>
                                            <h4 class="fw-bold mb-0 text-dark">৳<?= number_format($teacher['basic']) ?>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <small class="text-muted d-block">Net MPO (Govt)</small>
                                            <h4 class="fw-bold mb-0 text-success">
                                                ৳<?= number_format($teacher['netamtgovt']) ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <small class="text-muted d-block">Net School Pay</small>
                                            <h4 class="fw-bold mb-0 text-primary">
                                                ৳<?= number_format($teacher['net2']) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 alert alert-info py-2">
                                    <small><i class="bi bi-info-circle me-1"></i> This is the current approved salary
                                        structure for the 2025-2026 session.</small>
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