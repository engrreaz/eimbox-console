<?php
require_once 'header.php';

// ১. অটো আইডি জেনারেশন লজিক (৬ ডিজিট sccode + ৪ ডিজিট ৯৯৯৯ থেকে ৯৫০১ অব্দি নিচের দিকে)
$min_range = $sccode . "9501";
$max_range = $sccode . "9999";
$sql_id = "SELECT MIN(CAST(tid AS UNSIGNED)) AS min_tid FROM teacher WHERE sccode = '$sccode' AND tid >= '$min_range' AND tid <= '$max_range'";
$res_id = $conn->query($sql_id);
$row_id = ($res_id && $res_id->num_rows > 0) ? $res_id->fetch_assoc() : null;

if ($row_id && !empty($row_id['min_tid'])) {
    $min_tid = (int)$row_id['min_tid'];
    $calc_tid = $min_tid - 1;
    if ($calc_tid < (int)$min_range) {
        $new_tid = "ID Limit Reached (9501-9999)";
    } else {
        $new_tid = (string)$calc_tid;
    }
} else {
    $new_tid = $max_range;
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
$dq = $conn->query("SELECT id, title, ranks FROM designation ORDER BY ranks ASC, sl ASC");
if ($dq) {
    while ($drow = $dq->fetch_assoc()) {
        $designations[] = $drow;
    }
}

// ২. শিক্ষক ও স্টাফ তালিকা ফেচিং
$sql = "SELECT * FROM teacher WHERE sccode = '$sccode' ORDER BY sl ASC, ranks ASC";
$result = $conn->query($sql);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Management /</span> Teacher &amp; Staff List</h4>
            <p class="text-muted small mb-0">Manage institute faculty members, administrative staff, and assignments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="teacher-attendance-report.php" class="btn btn-outline-info btn-sm">
                <i class="bi bi-calendar-check me-1"></i> Attendance Matrix
            </a>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newTeacherModal">
                <i class="bi bi-person-plus me-1"></i> Add Teacher / Staff
            </button>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="small fw-bold text-muted me-2">Filter by Slot:</span>
                <button class="btn btn-primary btn-sm filter-btn active" onclick="filterSlot('all')">All Slots</button>
                <?php foreach ($slots_list as $sl_opt): ?>
                    <button class="btn btn-outline-secondary btn-sm filter-btn" onclick="filterSlot('<?= htmlspecialchars($sl_opt) ?>')">
                        <?= htmlspecialchars($sl_opt) ?>
                    </button>
                <?php endforeach; ?>

                <div class="vr mx-2 d-none d-md-block"></div>

                <span class="small fw-bold text-muted me-2">Group:</span>
                <button class="btn btn-outline-secondary btn-sm filter-group-btn" onclick="filterGroup('all')">All Groups</button>
                <button class="btn btn-outline-info btn-sm filter-group-btn" onclick="filterGroup('Teacher')">Teachers</button>
                <button class="btn btn-outline-warning btn-sm filter-group-btn" onclick="filterGroup('Staff')">Staff</button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th class="px-2">SL</th>
                        <th class="px-2">ID &amp; Name</th>
                        <th class="px-2">Designation</th>
                        <th class="px-2">Slot</th>
                        <th class="px-2">Group</th>
                        <th class="px-2">Mobile</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody id="teacherTable">
                    <?php
                    $count = 1;
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $group = ($row['ranks'] > 50) ? 'Staff' : 'Teacher';
                            $badge_color = ($group == 'Staff') ? 'bg-label-warning' : 'bg-label-info';
                            ?>
                            <tr class="teacher-row" data-id="<?= $row['id'] ?>" data-slot="<?= htmlspecialchars($row['slots'] ?? '') ?>" data-group="<?= $group ?>" style="cursor: move;">
                                <td class="drag-handle text-center px-1">
                                    <i class="bi bi-grip-vertical text-muted"></i>
                                </td>
                                <td class="px-2 font-monospace text-muted sl-cell"><?= $count ?></td>
                                <td class="px-2">
                                    <div class="d-flex align-items-center">
                                        <img class="avatar avatar-sm me-3 rounded-circle border"
                                             style="width: 38px; height: 38px; object-fit: cover;"
                                             src="<?= teacher_profile_image_path($row['tid']) ?>">
                                        <div>
                                            <a href="teacher-view.php?id=<?= urlencode($row['tid']) ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                                <?= htmlspecialchars($row['tname']) ?>
                                            </a>
                                            <div class="small font-monospace text-primary"><?= htmlspecialchars($row['tid']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($row['position']) ?></span>
                                    <small class="text-muted d-block" style="font-size: 11px;">Rank: <?= htmlspecialchars($row['ranks'] ?? '0') ?></small>
                                </td>
                                <td class="px-2"><span class="badge bg-label-secondary"><?= htmlspecialchars($row['slots'] ?: 'General') ?></span></td>
                                <td class="px-2"><span class="badge <?= $badge_color ?>"><?= $group ?></span></td>
                                <td class="px-2 font-monospace"><?= htmlspecialchars($row['mobile'] ?: '—') ?></td>
                                <td class="text-center px-2">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <a class="dropdown-item" href="teacher-view.php?id=<?= urlencode($row['tid']) ?>" target="_blank">
                                                <i class="bi bi-eye me-2 text-info"></i> View Profile
                                            </a>
                                            <a class="dropdown-item" href="teacher-edit.php?id=<?= urlencode($row['tid']) ?>" target="_blank">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit Profile
                                            </a>
                                            <a class="dropdown-item" href="salary-settings.php?id=<?= urlencode($row['tid']) ?>" target="_blank">
                                                <i class="bi bi-cash-stack me-2 text-success"></i> Update Salary
                                            </a>
                                            <a class="dropdown-item" href="attendance-view.php?tid=<?= urlencode($row['tid']) ?>" target="_blank">
                                                <i class="bi bi-calendar-check me-2 text-info"></i> View Attendance
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick="openPhotoModal('<?= $row['tid'] ?>', '<?= htmlspecialchars(addslashes($row['tname'])) ?>')">
                                                <i class="bi bi-camera me-2 text-warning"></i> Update Photo
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteTeacher('<?= $row['tid'] ?>', '<?= htmlspecialchars(addslashes($row['tname'])) ?>')">
                                                <i class="bi bi-trash me-2"></i> Delete / Remove
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        $count++; 
                        endwhile;
                    else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2"></i>
                                No teachers or staff records found. Click <strong>Add Teacher / Staff</strong> to create one.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Teacher / Staff Modal -->
<div class="modal fade" id="newTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bi bi-person-plus me-2"></i> Add New Teacher / Staff</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newTeacherForm" action="teacher/save-teacher.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Generated Teacher ID (Auto Decrement 9999-9501)</label>
                        <input type="text" name="tid" class="form-control bg-light fw-bold text-primary font-monospace"
                               value="<?= htmlspecialchars($new_tid) ?>" readonly>
                        <small class="text-muted" style="font-size: 11px;">Allocated range: <?= htmlspecialchars($min_range) ?> - <?= htmlspecialchars($max_range) ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Teacher / Staff Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="tname" class="form-control" placeholder="Enter Full Name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Designation / Position <span class="text-danger">*</span></label>
                        <select name="position" id="addPosition" class="form-select" onchange="syncAddRank()" required>
                            <option value="">Select Designation</option>
                            <?php foreach ($designations as $d): ?>
                                <option value="<?= htmlspecialchars($d['title']) ?>" data-rank="<?= $d['ranks'] ?>">
                                    <?= htmlspecialchars($d['title']) ?> (Rank: <?= $d['ranks'] ?><?= $d['ranks'] > 50 ? ' - Staff' : ' - Teacher' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="ranks" id="addRanks" value="22">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Slot / Unit <span class="text-danger">*</span></label>
                            <select name="slots" class="form-select">
                                <?php foreach ($slots_list as $sl_opt): ?>
                                    <option value="<?= htmlspecialchars($sl_opt) ?>"><?= htmlspecialchars($sl_opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="017XXXXXXXX">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="saveBtn" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Update Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6"><i class="bi bi-camera me-1"></i> Update Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="photoForm" enctype="multipart/form-data">
                <div class="modal-body text-center">
                    <h6 id="mtname" class="mb-2 text-primary fw-bold"></h6>
                    <input type="hidden" name="tid" id="mtid">

                    <div class="mb-3">
                        <div class="mx-auto border rounded bg-light d-flex align-items-center justify-content-center shadow-sm"
                             style="width: 140px; height: 140px; overflow: hidden;">
                            <img id="imgPreview" src="assets/img/default-avatar.png" class="img-fluid" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                    </div>

                    <input type="file" name="teacher_photo" id="photoInput" class="form-control form-control-sm" accept="image/*" required>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i> Upload &amp; Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    function syncAddRank() {
        const sel = document.getElementById('addPosition');
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.getAttribute('data-rank')) {
            document.getElementById('addRanks').value = opt.getAttribute('data-rank');
        }
    }

    let activeSlot = 'all';
    let activeGroup = 'all';

    function applyFilters() {
        const rows = document.querySelectorAll('.teacher-row');
        rows.forEach(row => {
            const slot = row.getAttribute('data-slot');
            const group = row.getAttribute('data-group');
            const slotMatch = (activeSlot === 'all' || slot === activeSlot);
            const groupMatch = (activeGroup === 'all' || group === activeGroup);

            if (slotMatch && groupMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterSlot(slot) {
        activeSlot = slot;
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-secondary');
        });
        event.currentTarget.classList.remove('btn-outline-secondary');
        event.currentTarget.classList.add('btn-primary');
        applyFilters();
    }

    function filterGroup(group) {
        activeGroup = group;
        document.querySelectorAll('.filter-group-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
        applyFilters();
    }

    function openPhotoModal(tid, tname) {
        document.getElementById('mtid').value = tid;
        document.getElementById('mtname').innerText = tname;
        document.getElementById('imgPreview').src = "uploads/teachers/" + tid + ".jpg?t=" + new Date().getTime();
        var myModal = new bootstrap.Modal(document.getElementById('photoModal'));
        myModal.show();
    }

    document.getElementById('photoInput').onchange = function (evt) {
        var [file] = this.files;
        if (file) {
            document.getElementById('imgPreview').src = URL.createObjectURL(file);
        }
    };

    document.getElementById('photoForm').onsubmit = function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        fetch('teacher/update-teacher-photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Photo updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    alert('Photo updated successfully!');
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', data.message || 'Error updating photo', 'error');
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Server error occurred while uploading photo.', 'error');
            }
        });
    };

    document.getElementById('newTeacherForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';

        const formData = new FormData(this);

        fetch('teacher/save-teacher.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'New teacher added successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    alert('New teacher added successfully.');
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', data.message || 'Failed to save teacher', 'error');
                } else {
                    alert('Error: ' + data.message);
                }
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Teacher';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Server error occurred. Please try again.', 'error');
            }
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Teacher';
        });
    });

    function deleteTeacher(tid, tname) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to remove ${tname} (ID: ${tid})? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDeleteTeacher(tid);
                }
            });
        } else {
            if (confirm(`Delete teacher ${tname} (ID: ${tid})?`)) {
                performDeleteTeacher(tid);
            }
        }
    }

    function performDeleteTeacher(tid) {
        const formData = new FormData();
        formData.append('tid', tid);

        fetch('teacher/delete-teacher.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: res.message || 'Teacher removed successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', res.message || 'Failed to delete record.', 'error');
                } else {
                    alert('Error: ' + res.message);
                }
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Server error during delete.', 'error');
            }
        });
    }

    // SortableJS Drag & Drop
    const el = document.getElementById('teacherTable');
    if (el) {
        const sortable = new Sortable(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'table-primary',
            onEnd: function (evt) {
                updateSerialNumbers();
            }
        });
    }

    function updateSerialNumbers() {
        let ids = [];
        document.querySelectorAll('#teacherTable tr.teacher-row').forEach((row, index) => {
            ids.push(row.getAttribute('data-id'));
            const cell = row.querySelector('.sl-cell');
            if (cell) cell.innerText = (index + 1);
        });

        fetch('teacher/update-sl.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (typeof showToast === 'function') {
                    showToast('info', 'Serial order updated successfully', 'Reorder List');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>