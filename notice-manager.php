<?php
require_once 'header.php';

// --- ডাটাবেজ অ্যাকশন হ্যান্ডলিং ---

// ১. নোটিশ সেভ (Add/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notice'])) {
    $id = $_POST['notice_id'] ?? null;
    $category = $_POST['category'];
    $title = $_POST['title'];
    $descrip = $_POST['descrip'];
    $icon = $_POST['icon'] ?: 'bell-fill';
    $color = $_POST['color'] ?: 'black';
    $expdate = $_POST['expdate'];

    // টার্গেট অডিয়েন্স (Checkbox logic)
    $teacher = isset($_POST['teacher']) ? 1 : 0;
    $smc = isset($_POST['smc']) ? 1 : 0;
    $guardian = isset($_POST['guardian']) ? 1 : 0;

    // নোটিফিকেশন চ্যানেল
    $sms = isset($_POST['sms']) ? 1 : 0;
    $pushnoti = isset($_POST['pushnoti']) ? 1 : 0;
    $email = isset($_POST['email']) ? 1 : 0;

    $now = date('Y-m-d H:i:s');
    $user_email = $_SESSION['user_email'] ?? 'admin'; // সেশন থেকে ইউজার ইমেইল

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE notice SET category=?, title=?, descrip=?, icon=?, color=?, expdate=?, teacher=?, smc=?, guardian=?, sms=?, pushnoti=?, email=?, modifieddate=? WHERE id=? AND sccode=?");
        $stmt->bind_param("ssssssiiiiiisii", $category, $title, $descrip, $icon, $color, $expdate, $teacher, $smc, $guardian, $sms, $pushnoti, $email, $now, $id, $sccode);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO notice (sccode, category, title, descrip, icon, color, expdate, teacher, smc, guardian, sms, pushnoti, email, entryby, entrytime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssiiiiisss", $sccode, $category, $title, $descrip, $icon, $color, $expdate, $teacher, $smc, $guardian, $sms, $pushnoti, $email, $user_email, $now);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Notice saved successfully!'); window.location.href='notice-manager.php';</script>";
    }
}

// ২. নোটিশ ডিলিট
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notice WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $del_id, $sccode);
    if ($stmt->execute()) {
        echo "<script>alert('Notice deleted!'); window.location.href='notice-manager.php';</script>";
    }
}

// নোটিশ লিস্ট ফেচ করা
$notices = $conn->query("SELECT * FROM notice WHERE sccode = '$sccode' ORDER BY entrytime DESC");
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Communication /</span> Notice Manager</h4>
        <button class="btn btn-primary shadow-sm" onclick="openNoticeModal()">
            <i class="bi bi-megaphone me-2"></i> Add New Notice
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Date & Title</th>
                        <th>Category</th>
                        <th>Audience</th>
                        <th>Expiry</th>
                        <th>Channels</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $notices->fetch_assoc()):
                        $is_expired = (strtotime($row['expdate']) < time());
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-primary p-2 rounded me-3">
                                        <i class="bi bi-<?= $row['icon'] ?> fs-5" style="color: <?= $row['color'] ?>;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= $row['title'] ?></div>
                                        <small
                                            class="text-muted"><?= date('d M, Y', strtotime($row['entrytime'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $row['category'] ?: 'General' ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <?php if ($row['teacher'])
                                        echo '<span class="badge bg-info p-1" title="Teacher">T</span>'; ?>
                                    <?php if ($row['smc'])
                                        echo '<span class="badge bg-warning p-1" title="SMC">S</span>'; ?>
                                    <?php if ($row['guardian'])
                                        echo '<span class="badge bg-success p-1" title="Guardian">G</span>'; ?>
                                </div>
                            </td>
                            <td>
                                <span class="small <?= $is_expired ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= date('d-m-Y', strtotime($row['expdate'])) ?>
                                    <?= $is_expired ? ' (Exp)' : '' ?>
                                </span>
                            </td>
                            <td>
                                <div class="fs-5 text-muted">
                                    <i class="bi bi-chat-text <?= $row['sms'] ? 'text-success' : 'opacity-25' ?> me-1"
                                        title="SMS"></i>
                                    <i class="bi bi-phone-vibrate <?= $row['pushnoti'] ? 'text-primary' : 'opacity-25' ?> me-1"
                                        title="Push"></i>
                                    <i class="bi bi-envelope <?= $row['email'] ? 'text-danger' : 'opacity-25' ?>"
                                        title="Email"></i>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item" onclick='editNotice(<?= json_encode($row) ?>)'>
                                            <i class="bi bi-pencil me-2"></i> Edit
                                        </button>
                                        <a class="dropdown-item text-danger" href="?delete=<?= $row['id'] ?>"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Notice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="notice_id" id="notice_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Notice Title</label>
                            <input type="text" name="title" id="n_title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Category</label>
                            <input type="text" name="category" id="n_category" class="form-control"
                                placeholder="e.g. Exam, Holiday">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notice Description</label>
                            <textarea name="descrip" id="n_descrip" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Expiry Date</label>
                            <input type="date" name="expdate" id="n_expdate" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Icon (Bootstrap)</label>
                            <input type="text" name="icon" id="n_icon" class="form-control" placeholder="bell-fill">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Theme Color</label>
                            <input type="color" name="color" id="n_color" class="form-control h-px-40" value="#000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold border-bottom pb-2">Target Audience</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="checkbox" name="teacher" id="n_teacher">
                                <label class="form-check-label">Teacher</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="smc" id="n_smc">
                                <label class="form-check-label">SMC</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="guardian" id="n_guardian">
                                <label class="form-check-label">Guardian</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold border-bottom pb-2">Notifications</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="checkbox" name="sms" id="n_sms">
                                <label class="form-check-label">SMS</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="pushnoti" id="n_push">
                                <label class="form-check-label">Push</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="email" id="n_email">
                                <label class="form-check-label">Email</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_notice" class="btn btn-primary px-4">Save Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    const nModal = new bootstrap.Modal(document.getElementById('noticeModal'));

    function openNoticeModal() {
        document.getElementById('modalTitle').innerText = "Add New Notice";
        document.getElementById('notice_id').value = "";
        document.getElementById('editTeacherForm')?.reset(); // Clean if shared
        nModal.show();
    }

    function editNotice(data) {
        document.getElementById('modalTitle').innerText = "Edit Notice";
        document.getElementById('notice_id').value = data.id;
        document.getElementById('n_title').value = data.title;
        document.getElementById('n_category').value = data.category;
        document.getElementById('n_descrip').value = data.descrip;
        document.getElementById('n_expdate').value = data.expdate;
        document.getElementById('n_icon').value = data.icon;
        document.getElementById('n_color').value = data.color;

        // Checkboxes
        document.getElementById('n_teacher').checked = data.teacher == 1;
        document.getElementById('n_smc').checked = data.smc == 1;
        document.getElementById('n_guardian').checked = data.guardian == 1;
        document.getElementById('n_sms').checked = data.sms == 1;
        document.getElementById('n_push').checked = data.pushnoti == 1;
        document.getElementById('n_email').checked = data.email == 1;

        nModal.show();
    }
</script>

</body>

</html>