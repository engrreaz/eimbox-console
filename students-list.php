<?php require_once 'header.php'; ?>

<?php
// কুকি থেকে ফিল্টার প্যারামিটার গ্রহণ
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

// কুয়েরি প্রিপারেশন: sessioninfo এবং students টেবিল জয়েন করা হয়েছে
$students_list = [];
if (!empty($class) && !empty($sessionyear)) {
    $stmt = $conn->prepare("
        SELECT 
            si.stid, si.rollno,
            s.stnameeng, s.stnameben, s.fname, s.mname, 
            s.previll, s.prepo, s.preps, s.predist
        FROM sessioninfo AS si
        JOIN students AS s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? 
        AND si.sessionyear = ? 
        AND si.slot = ? 
        AND si.classname = ? 
        AND si.sectionname = ?
        ORDER BY si.rollno ASC
    ");
    $stmt->bind_param("issss", $sccode, $sessionyear, $slot, $class, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <?php
    $chain_param = '-c 12 -t Students List -u -r -b Show Students';
    include 'components/slot-tree-ui.php';
    ?>

    <?php if (!empty($students_list)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-people me-2 text-primary"></i>
                    Student List: <?= "$class ($section) - $sessionyear" ?>
                </h5>
                <span class="badge bg-label-primary rounded-pill">Total: <?= count($students_list) ?></span>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 70px;">Roll</th>
                            <th>ID & Student Name</th>
                            <th>Parents Info</th>
                            <th>Present Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_list as $st): ?>
                            <tr>
                                <td class="fw-bold text-center text-primary"><?= $st['rollno'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= student_profile_image_path($st['stid']) ?>"
                                            onerror="this.src='assets/img/default-student.png'" alt="Avatar"
                                            class="rounded-circle me-3"
                                            style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #eee;">
                                        <div>
                                            <div class="fw-bold text-dark mb-0"><?= $st['stnameeng'] ?></div>
                                            <div class="text-muted small"><?= $st['stnameben'] ?></div>
                                            <small class="badge bg-light text-muted border-0 p-0">ID: <?= $st['stid'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><span class="text-muted">F:</span> <?= $st['fname'] ?></div>
                                    <div class="small"><span class="text-muted">M:</span> <?= $st['mname'] ?></div>
                                </td>
                                <td class="small text-wrap" style="max-width: 200px;">
                                    <i class="bi bi-geo-alt text-danger me-1"></i>
                                    <?= "{$st['previll']}, {$st['prepo']}, {$st['preps']}, {$st['predist']}" ?>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="student-view-profile.php?stid=<?= $st['stid'] ?>"
                                                target="_blank">
                                                <i class="bi bi-eye me-2"></i> View Profile
                                            </a>
                                            <a class="dropdown-item text-primary"
                                                href="student-edit.php?stid=<?= $st['stid'] ?>" target="_blank">
                                                <i class="bi bi-pencil me-2"></i> Edit Data
                                            </a>
                                            <a class="dropdown-item text-info" href="student-idcard.php?stid=<?= $st['stid'] ?>"
                                                target="_blank">
                                                <i class="bi bi-card-heading me-2"></i> Print ID Card
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                onclick="deleteStudent('<?= $st['stid'] ?>')">
                                                <i class="bi bi-trash me-2"></i> Archive Student
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif (!empty($class)): ?>
        <div class="alert alert-warning border-0 shadow-sm mt-3">
            <i class="bi bi-exclamation-triangle me-2"></i> No students found for the selected criteria.
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <img src="assets/img/illustrations/filter-data.png" style="width: 150px; opacity: 0.5;">
            <p class="text-muted mt-3">Please select Slot, Class, and Section to view students.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        // এই ফাংশনটি ফিল্টার বাটন ক্লিক করলে রান হবে
        location.reload();
    }

    function deleteStudent(stid) {
        if (confirm('Are you sure you want to archive this student?')) {
            // আপনার ডিলিট লজিক এখানে
            console.log('Archiving ID: ' + stid);
        }
    }
</script>
</body>

</html>