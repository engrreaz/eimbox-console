<?php require_once 'header.php'; ?>

<?php
// কুকি থেকে ফিল্টার প্যারামিটার গ্রহণ
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

$status = 1;
// কুয়েরি প্রিপারেশন: sessioninfo এবং students টেবিল জয়েন করা হয়েছে
$students_list = [];
if (!empty($class) && !empty($sessionyear)) {
    $stmt = $conn->prepare("
        SELECT 
           si.id, si.stid, si.rollno, si.icardst,
            s.stnameeng, s.stnameben, s.fname, s.mname, 
            s.previll, s.prepo, s.preps, s.predist
        FROM sessioninfo AS si
        JOIN students AS s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? 
        AND si.sessionyear = ? 
        AND si.slot = ? 
        AND si.classname = ? 
        AND si.sectionname = ?
        AND si.status = ?
        ORDER BY si.rollno ASC
    ");
    $stmt->bind_param("issssi", $sccode, $sessionyear, $slot, $class, $section, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
}
?>

<style>
    .fm {
        min-width: 20px;
    }

    .st-check {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #696cff;
        /* Bootstrap primary color */
        transform: scale(1.1);
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <?php
    $chain_param = '-c 12 -t Choose Class & Section -u -r -b Show Students';
    include 'components/slot-tree-ui.php';
    ?>

    <?php if (!empty($students_list)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-people me-2 text-primary"></i>
                    Student List: <?= "$class ($section) - $sessionyear" ?>
                </h5>
                <span class="badge bg-label-primary rounded-pill me-3">Total: <?= count($students_list) ?></span>
                
                <a href="students-archived.php" class="btn btn-sm btn-outline-warning me-3" title="View Archived Students">
                    <i class="bi bi-archive me-1"></i> Archived Students
                </a>

                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical fs-5"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item"
                            href="student-list-print.php?slot=<?= $slot ?>&session=<?= $sessionyear ?>&class=<?= $class ?>&section=<?= $section ?>"
                            target="_blank">
                            <i class="bi bi-printer me-2"></i> Print List
                        </a>
                        <a class="dropdown-item text-success" href="javascript:void(0);" onclick="printSelected()">
                            <i class="bi bi-printer me-2"></i> Print Selected
                        </a>
                        <hr class="my-0">
                        <a class="dropdown-item"
                            href="student-list-print-compact.php?slot=<?= $slot ?>&session=<?= $sessionyear ?>&class=<?= $class ?>&section=<?= $section ?>"
                            target="_blank">
                            <i class="bi bi-printer-fill me-2"></i> Print List (Compact)
                        </a>
                        <a class="dropdown-item text-success" href="javascript:void(0);" onclick="printSelectedCompact()">
                            <i class="bi bi-printer-fill me-2"></i> Print Selected (Compact)
                        </a>



                    </div>
                </div>


            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="checkAll" class="st-check">
                            </th>
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
                                <td>
                                    <input type="checkbox" class="st-check" value="<?= $st['stid'] ?>">
                                </td>
                                <td class="fw-bold text-center text-primary"><?= $st['rollno'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= student_profile_image_path($st['stid']) ?>" alt="Avatar"
                                            class="rounded-circle me-3"
                                            style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #eee;">
                                        <div>
                                            <div class="fw-bold text-dark mb-0"><?= $st['stnameeng'] ?></div>
                                            <div class="text-muted small"><?= $st['stnameben'] ?></div>
                                            <small class="badge bg-light text-muted border-0 px-2 mt-1 fs-tiny ">ID:
                                                <?= $st['stid'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small d-flex">
                                        <div class="text-muted fm">F:</div> <?= $st['fname'] ?>
                                    </div>
                                    <div class="small d-flex">
                                        <div class="text-muted fm">M:</div> <?= $st['mname'] ?>
                                    </div>
                                </td>
                                <td class="small text-wrap" style="max-width: 200px;">
                                    <div class="d-flex">
                                        <i class="bi bi-geo-alt text-danger fs-6 pt-2 me-3"></i>
                                        <?= "{$st['previll']}, {$st['prepo']}, {$st['preps']}, {$st['predist']}" ?>
                                    </div>

                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <?php
                                        $status_card = $st['icardst'];
                                        if ($status_card == 1) {
                                            $icon = 'card-text';
                                            $color = 'success';
                                        } else if ($status_card == 0) {
                                            $icon = 'check-circle';
                                            $color = 'primary';
                                        } else if ($status_card == "d") {
                                            $icon = 'info-circle';
                                            $color = 'warning';
                                        } else if ($status_card == "x") {
                                            $icon = 'x-lg';
                                            $color = 'danger';
                                        } else {
                                            $icon = 'person-badge';
                                            $color = 'secondary';
                                        }
                                        ?>
                                        <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-4 me-2"></i>
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="student-view-profile.php?stid=<?= $st['stid'] ?>&year=<?= $sessionyear ?>"
                                                target="_blank">
                                                <i class="bi bi-eye me-2"></i> View Profile
                                            </a>
                                            <a class="dropdown-item text-primary" href="enroll-students.php?stid=<?= $st['stid'] ?>&sy=<?= $sessionyear ?>"
                                                target="_blank">
                                                <i class="bi bi-pencil me-2"></i> Edit Profile
                                            </a>
                                            <a class="dropdown-item text-muted disabled" href="javascript:void(0);"
                                                style="pointer-events: none; opacity: 0.6;">
                                                <i class="bi bi-card-heading me-2"></i> Print ID Card <small class="text-muted">(Disabled)</small>
                                            </a>

                                            <hr class="dropdown-divider">
                                            <a class="dropdown-item text-warning" href="javascript:void(0);"
                                                onclick="issueTC('<?= $st['stid'] ?>', '<?= htmlspecialchars(addslashes($st['stnameeng'] ?? ''), ENT_QUOTES) ?>')">
                                                <i class="bi bi-file-earmark-arrow-right me-2"></i> Issue TC
                                            </a>
                                            <a class="dropdown-item text-info" href="student-bonafide.php?stid=<?= $st['stid'] ?>&year=<?= $sessionyear ?>"
                                                target="_blank">
                                                <i class="bi bi-award me-2"></i> Bonafide Certificate
                                            </a>
                                            <a class="dropdown-item text-secondary" href="student-overall-report.php?stid=<?= $st['stid'] ?>&year=<?= $sessionyear ?>"
                                                target="_blank">
                                                <i class="bi bi-file-earmark-person me-2"></i> Overall Report
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                onclick="deleteStudent('<?= $st['stid'] ?>', '<?= htmlspecialchars(addslashes($st['stnameeng'] ?? ''), ENT_QUOTES) ?>')">
                                                <i class="bi bi-archive me-2"></i> Archive Student
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
            <img src="assets/images/filter-data.png" style="width: 150px; opacity: 0.5;">
            <p class="text-muted mt-3">Please select Slot, Class, and Section to view students.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        location.reload();
    }

    function issueTC(stid, name) {
        Swal.fire({
            title: 'Issue TC (Transfer Certificate)',
            html: `Transfer Certificate (TC) module for <strong>${name}</strong> (ID: ${stid}) is planned for an upcoming release.`,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function deleteStudent(stid, name) {
        Swal.fire({
            title: 'Archive Student?',
            html: `Are you sure you want to archive <strong>${name || ''}</strong> (ID: ${stid})?<br><small class="text-muted">Student will be moved to the Archived directory.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-archive me-1"></i> Yes, archive',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("student/delete-student.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "stid=" + encodeURIComponent(stid)
                })
                .then(res => res.text())
                .then(msg => {
                    Swal.fire({
                        title: 'Archived!',
                        text: msg,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1200);
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to archive student: ' + err, 'error');
                });
            }
        });
    }

    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.st-check').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    function printSelected() {
        let ids = [];
        document.querySelectorAll('.st-check:checked').forEach(cb => {
            ids.push(cb.value);
        });

        if (ids.length === 0) {
            Swal.fire('Notice', 'Please select at least one student', 'info');
            return;
        }

        let url = "student-list-print.php?ids=" + ids.join(',');
        window.open(url, '_blank');
    }

    function printSelectedCompact() {
        let ids = [];
        document.querySelectorAll('.st-check:checked').forEach(cb => {
            ids.push(cb.value);
        });

        if (ids.length === 0) {
            Swal.fire('Notice', 'Please select at least one student', 'info');
            return;
        }

        let url = "student-list-print-compact.php?ids=" + ids.join(',');
        window.open(url, '_blank');
    }

    function edit_st_profile(rollno, session, stid) {
        localStorage.setItem("enroll-students_rollno", rollno);
        localStorage.setItem("enroll-students_session", session);
        window.open("enroll-students.php?stid=" + stid + '&sy=' + session, '_blank');
    }
</script>
</body>
</html>