<?php
require_once 'header.php';

// ১. অটো আইডি জেনারেশন লজিক
$prefix = $sccode . "99";
$sql_id = "SELECT tid FROM teacher WHERE tid BETWEEN '{$prefix}00' AND '{$prefix}99' ORDER BY sl, ranks ASC LIMIT 1";
$res_id = $conn->query($sql_id);

if ($res_id->num_rows > 0) {
    $last_tid = $res_id->fetch_assoc()['tid'];
    $new_tid = $last_tid - 1;
    // যদি ৯৯০০ এর নিচে নেমে যায় তবে হ্যান্ডেলিং (ঐচ্ছিক)
    if ($new_tid < ($prefix . "00"))
        $new_tid = "ID Limit Exceeded";
} else {
    $new_tid = $prefix . "99";
}

// ২. ডাটা ফেচিং
$sql = "SELECT * FROM teacher WHERE sccode = '$sccode' ORDER BY sl ASC";
$result = $conn->query($sql);
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>সফল হয়েছে!</strong> নতুন শিক্ষক তালিকাভুক্ত করা হয়েছে।
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>



<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Management /</span> Teacher & Staff List</h4>

    </div>

    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm filter-btn active" onclick="filterSlot('all')">All
                    Slot/Unit</button>
                <button class="btn btn-outline-secondary btn-sm filter-btn"
                    onclick="filterSlot('School')">School</button>
                <button class="btn btn-outline-secondary btn-sm filter-btn"
                    onclick="filterSlot('College')">College</button>

                <div class="flex-grow-1"></div>

                <button class="btn btn-primary btn-rounded btn-sm " style="border-radius:100px;" data-bs-toggle="modal" data-bs-target="#newTeacherModal">
                    <i class="bi bi-person-plus me-2"></i> New Teacher
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th></th>
                        <th class="px-2">SL</th>
                        <th class="px-2">ID & Name</th>
                        <th class="px-2">Designation</th>
                        <th class="px-2">Slot</th>
                        <th class="px-2">Group</th>
                        <th class="px-2">Mobile</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody id="teacherTable">
                    <?php
                    $count = 1;
                    while ($row = $result->fetch_assoc()):
                        $group = ($row['ranks'] >= 50) ? 'Staff' : 'Teacher';
                        $badge_color = ($group == 'Staff') ? 'bg-label-warning' : 'bg-label-info';
                        ?>
                        <tr class="teacher-row" data-id="<?= $row['id'] ?>" data-slot="<?= $row['slots'] ?>" style="cursor: move;">
        <td class="drag-handle ms-4"><i class="bi bi-grip-vertical text-muted">


        
                        
                            <td class="px-2"><?= $count ?></td>
                            <td class="px-2">
                                <div class="d-flex align-items-center">
                                    <img class="avatar avatar-sm me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                        style="width: 35px; height:35px;"
                                        src="<?= teacher_profile_image_path($row['tid']) ?>">


                                    <div>
                                        <div class="fw-bold text-dark"><?= $row['tname'] ?></div>
                                        <small class="text-muted"><?= $row['tid'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2"><?= $row['position'] ?></td>
                            <td class="px-2"><span class="badge bg-label-secondary"><?= $row['slots'] ?></span></td>
                            <td class="px-2"><span class="badge <?= $badge_color ?>"><?= $group ?></span></td>
                            <td class="px-2"><?= $row['mobile'] ?></td>
                            <td class="text-center px-2">
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="teacher-view.php?id=<?= $row['tid'] ?>"><i
                                                class="bi bi-eye me-2"></i> View Profile</a>
                                        <a class="dropdown-item text-warning" href="javascript:void(0);"
                                            onclick="openPhotoModal('<?= $row['tid'] ?>', '<?= $row['tname'] ?>')">
                                            <i class="bi bi-camera me-2"></i> Update Photo
                                        </a>
                                        <hr class="dropdown-divider">
                                        <a href="teacher-edit.php?id=<?= $row['tid'] ?>" target="_blank"
                                            class="dropdown-item">
                                            <i class="bi bi-pencil me-2"></i> Edit Profile
                                        </a>
                                        <a class="dropdown-item text-success"
                                            href="salary-settings.php?id=<?= $row['tid'] ?>"><i
                                                class="bi bi-cash-stack me-2"></i> Update Salary</a>
                                        <a class="dropdown-item text-info"
                                            href="attendance-view.php?tid=<?= $row['tid'] ?>"><i
                                                class="bi bi-calendar-check me-2"></i> View Attendance</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php $count++; endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Teacher/Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="save-teacher.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Generated Teacher ID</label>
                        <input type="text" name="tid" class="form-control bg-light fw-bold text-primary"
                            value="<?= $new_tid ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher Name</label>
                        <input type="text" name="tname" class="form-control" placeholder="Enter Full Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slot / Unit</label>
                        <select name="slots" class="form-select">
                            <option value="School">School</option>
                            <option value="College">College</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="photoForm" enctype="multipart/form-data">
                <div class="modal-body text-center">
                    <h6 id="mtname" class="mb-3 text-primary"></h6>
                    <input type="hidden" name="tid" id="mtid">

                    <div class="mb-3">
                        <div class="mx-auto border rounded bg-light d-flex align-items-center justify-content-center"
                            style="width: 150px; height: 150px; overflow: hidden;">
                            <img id="imgPreview" src="assets/img/default-avatar.png" class="img-fluid">
                        </div>
                    </div>

                    <input type="file" name="teacher_photo" id="photoInput" class="form-control form-control-sm"
                        accept="image/*" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Upload & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>



<script>
    function filterSlot(slot) {
        // বাটন একটিভ স্টেট পরিবর্তন
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'btn-secondary');
            btn.classList.add('btn-outline-secondary');
        });
        event.currentTarget.classList.add('active', 'btn-secondary');
        event.currentTarget.classList.remove('btn-outline-secondary');

        // টেবিল রো ফিল্টারিং
        const rows = document.querySelectorAll('.teacher-row');
        rows.forEach(row => {
            if (slot === 'all' || row.getAttribute('data-slot') === slot) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>


<script>
    // মোডাল ওপেন করা
    function openPhotoModal(tid, tname) {
        document.getElementById('mtid').value = tid;
        document.getElementById('mtname').innerText = tname;
        document.getElementById('imgPreview').src = "uploads/teachers/" + tid + ".jpg?t=" + new Date().getTime(); // পুরানো ছবি থাকলে দেখাবে
        var myModal = new bootstrap.Modal(document.getElementById('photoModal'));
        myModal.show();
    }

    // ছবি সিলেক্ট করলে প্রিভিউ দেখানো
    document.getElementById('photoInput').onchange = function (evt) {
        var [file] = this.files;
        if (file) {
            document.getElementById('imgPreview').src = URL.createObjectURL(file);
        }
    }

    // AJAX এর মাধ্যমে ছবি আপলোড
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
                    alert('Photo updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    };
</script>


<script>
    document.getElementById('newTeacherForm').addEventListener('submit', function (e) {
        e.preventDefault(); // ফর্মের ডিফল্ট সাবমিট বন্ধ করা

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true; // বাটন ডিজেবল করা যেন ডাবল ক্লিক না হয়
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';

        const formData = new FormData(this);

        fetch('teacher/save-teacher.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json()) // আমরা PHP থেকে JSON রেসপন্স আশা করছি
            .then(data => {
                if (data.status === 'success') {
                    alert('সফলভাবে সেভ হয়েছে!');
                    location.reload(); // ডাটা দেখানোর জন্য পেজ রিলোড
                } else {
                    alert('ভুল হয়েছে: ' + data.message);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save Teacher';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('সার্ভারে সমস্যা হচ্ছে। আবার চেষ্টা করুন।');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Teacher';
            });
    });
</script>


<script>
    // SortableJS ইনিশিয়ালাইজ করা
    const el = document.getElementById('teacherTable');
    const sortable = new Sortable(el, {
        animation: 150,
        handle: '.drag-handle', // নির্দিষ্ট আইকন দিয়ে ড্রাগ করার জন্য (ঐচ্ছিক)
        ghostClass: 'bg-light-primary', // ড্রাগ করার সময় ব্যাকগ্রাউন্ড কালার
        onEnd: function (evt) {
            updateSerialNumbers();
        }
    });

    function updateSerialNumbers() {
        // নতুন সিরিয়াল অনুযায়ী ID গুলোর অ্যারে তৈরি করা
        let ids = [];
        document.querySelectorAll('#teacherTable tr').forEach((row, index) => {
            ids.push(row.getAttribute('data-id'));
            // স্ক্রিনে তাৎক্ষণিকভাবে SL কলামের লেখা আপডেট করা
            row.cells[0].innerHTML = '<i class="bi bi-grip-vertical text-muted"></i> ' + (index + 1);
        });

        // AJAX এর মাধ্যমে ডাটাবেজে নতুন SL সেভ করা
        fetch('teacher/update-sl.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Serial updated successfully');
                showToast('info', 'Serial updated successfully', 'Reorder List');
            } else {
                alert('Update failed: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>


</body>

</html>