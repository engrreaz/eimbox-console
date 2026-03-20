<?php
require_once 'header.php';

// ১. ডাটা ফেচ করা
if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
    $stmt->bind_param("ii", $teacher_id, $sccode);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();

    if (!$teacher) {
        echo "<div class='alert alert-danger m-3'>Teacher not found!</div>";
        exit;
    }
} else {
    header("Location: teacher-list.php");
    exit;
}

// ২. ডাটা আপডেট হ্যান্ডলিং
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    // এখানে অনেকগুলো ফিল্ড, তাই আমি প্রধান কয়েকটা দেখাচ্ছি। আপনি আপনার প্রয়োজন মত সব ফিল্ড যোগ করবেন।
    $tname = $_POST['tname'];
    $tnameb = $_POST['tnameb'];
    $mobile = $_POST['mobile'];
    $position = $_POST['position'];
    $slots = $_POST['slots'];
    $email = $_POST['email'];
    $modifieddate = date('Y-m-d H:i:s');

    $sql = "UPDATE teacher SET tname=?, tnameb=?, mobile=?, position=?, slots=?, email=?, modifieddate=? WHERE id=? AND sccode=?";
    $up_stmt = $conn->prepare($sql);
    $up_stmt->bind_param("ssssssiii", $tname, $tnameb, $mobile, $position, $slots, $email, $modifieddate, $teacher_id, $sccode);

    if ($up_stmt->execute()) {
        echo "<script>alert('Updated Successfully!'); window.location.href='teacher-edit.php?id=$teacher_id';</script>";
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Teacher /</span> Edit Profile</h4>
        <a href="teacher-list.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" id="editTeacherForm">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?= teacher_profile_image_path($teacher['tid'] ) ?>"
                             class="rounded mb-3 shadow-sm"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="mb-1"><?= $teacher['tid'] ?></h5>
                        <div class="badge bg-label-primary mb-3"><?= $teacher['position'] ?></div>

                        <div class="text-start border-top pt-3">
                            <label class="form-label small text-muted">Account Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="1" <?= $teacher['status'] == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $teacher['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="nav-align-top mb-4">
                    <ul class="nav nav-tabs shadow-sm" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#tab-personal">Personal</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-service">Service</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-address">Address</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-bank">Banking</button>
                        </li>
                    </ul>
                    <div class="tab-content border shadow-none p-4">

                        <div class="tab-pane fade show active" id="tab-personal">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name (English)</label>
                                    <input type="text" name="tname" class="form-control"
                                        value="<?= $teacher['tname'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">নাম (বাংলায়)</label>
                                    <input type="text" name="tnameb" class="form-control"
                                        value="<?= $teacher['tnameb'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mobile</label>
                                    <input type="text" name="mobile" class="form-control"
                                        value="<?= $teacher['mobile'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= $teacher['email'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male" <?= $teacher['gender'] == 'Male' ? 'selected' : '' ?>>Male
                                        </option>
                                        <option value="Female" <?= $teacher['gender'] == 'Female' ? 'selected' : '' ?>>Female
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NID No</label>
                                    <input type="text" name="nid" class="form-control" value="<?= $teacher['nid'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= $teacher['dob'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <input type="text" name="bgroup" class="form-control"
                                        value="<?= $teacher['bgroup'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-service">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="position" class="form-control"
                                        value="<?= $teacher['position'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slot / Unit</label>
                                    <select name="slots" class="form-select">
                                        <option value="School" <?= $teacher['slots'] == 'School' ? 'selected' : '' ?>>School
                                        </option>
                                        <option value="College" <?= $teacher['slots'] == 'College' ? 'selected' : '' ?>>College
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Joining Date</label>
                                    <input type="date" name="jdate" class="form-control"
                                        value="<?= $teacher['jdate'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Rank (Priority SL)</label>
                                    <input type="number" name="ranks" class="form-control"
                                        value="<?= $teacher['ranks'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">MPO Index</label>
                                    <input type="text" name="mpoindex" class="form-control"
                                        value="<?= $teacher['mpoindex'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-address">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Present Address</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4"><label class="form-label">Village</label><input type="text"
                                        name="previll" class="form-control" value="<?= $teacher['previll'] ?>"></div>
                                <div class="col-md-4"><label class="form-label">PO</label><input type="text"
                                        name="prepo" class="form-control" value="<?= $teacher['prepo'] ?>"></div>
                                <div class="col-md-4"><label class="form-label">PS/Upazila</label><input type="text"
                                        name="preps" class="form-control" value="<?= $teacher['preps'] ?>"></div>
                            </div>
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Permanent Address</h6>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Village</label><input type="text"
                                        name="pervill" class="form-control" value="<?= $teacher['pervill'] ?>"></div>
                                <div class="col-md-4"><label class="form-label">PO</label><input type="text"
                                        name="perpo" class="form-control" value="<?= $teacher['perpo'] ?>"></div>
                                <div class="col-md-4"><label class="form-label">PS/Upazila</label><input type="text"
                                        name="perps" class="form-control" value="<?= $teacher['perps'] ?>"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-bank">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Account No (MPO)</label>
                                    <input type="text" name="accno" class="form-control"
                                        value="<?= $teacher['accno'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bankname" class="form-control"
                                        value="<?= $teacher['bankname'] ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Branch & Address</label>
                                    <input type="text" name="branch" class="form-control"
                                        value="<?= $teacher['branch'] ?>">
                                </div>
                                <hr class="my-4">
                                <div class="col-md-6">
                                    <label class="form-label">School Payment Acc.</label>
                                    <input type="text" name="accnosch" class="form-control"
                                        value="<?= $teacher['accnosch'] ?>">
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
    // ফরম পরিবর্তনের আগে সতর্কবার্তা (ঐচ্ছিক)
    let formChanged = false;
    document.getElementById('editTeacherForm').addEventListener('change', () => formChanged = true);

    window.onbeforeunload = function () {
        if (formChanged) return "You have unsaved changes!";
    };

    document.getElementById('editTeacherForm').addEventListener('submit', () => {
        window.onbeforeunload = null;
    });
</script>
</body>

</html>