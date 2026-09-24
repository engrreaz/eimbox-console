<?php
include 'header.php';

$slot = $_GET['slot'] ?? 'School';
$session = $_GET['session'] ?? date('Y');
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Filters -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-card-checklist text-primary"></i> Examination List
                    </h4>
                    <p class="text-muted small mb-0">পরীক্ষা তালিকা ও শিডিউল ব্যবস্থাপনা</p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Slot Filter -->
                    <div>
                        <label class="form-label small fw-semibold mb-0">Slot / Unit</label>
                        <select id="slotFilter" class="form-select form-select-sm">
                            <?php
                            $slotRes = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' AND slotname IS NOT NULL AND slotname != '' ORDER BY id ASC");
                            if ($slotRes && $slotRes->num_rows > 0) {
                                while ($s = $slotRes->fetch_assoc()) {
                                    $sel = ($slot === $s['slotname']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($s['slotname']) . "\" $sel>" . htmlspecialchars($s['slotname']) . "</option>";
                                }
                            } else {
                                echo "<option value='School' selected>School</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Session Filter -->
                    <div>
                        <label class="form-label small fw-semibold mb-0">Session Year</label>
                        <select id="sessionFilter" class="form-select form-select-sm">
                            <?php
                            $sesRes = $conn->query("SELECT DISTINCT syear FROM sessionyear WHERE active=1 AND sccode='$sccode' ORDER BY syear DESC");
                            if ($sesRes && $sesRes->num_rows > 0) {
                                while ($y = $sesRes->fetch_assoc()) {
                                    $sel = ($session == $y['syear']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($y['syear']) . "\" $sel>" . htmlspecialchars($y['syear']) . "</option>";
                                }
                            } else {
                                echo "<option value=\"" . date('Y') . "\" selected>" . date('Y') . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="pt-3">
                        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" id="addNewBtn">
                            <i class="bi bi-plus-circle"></i> Add Exam
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" width="70">#ID</th>
                            <th>Exam Title</th>
                            <th>Class / Section</th>
                            <th>Start Date</th>
                            <th>Result Publish</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM examlist WHERE sccode = ? AND sessionyear = ? AND slot = ? ORDER BY id DESC");
                        $stmt->bind_param("iss", $sccode, $session, $slot);
                        $stmt->execute();
                        $res = $stmt->get_result();

                        if ($res && $res->num_rows > 0):
                            while ($row = $res->fetch_assoc()):
                                $statusBadge = $row['status'] 
                                    ? '<span class="badge bg-label-success">Active</span>' 
                                    : '<span class="badge bg-label-secondary">Inactive</span>';
                                $clsSec = htmlspecialchars($row['classname'] ?: 'All Classes');
                                if (!empty($row['sectionname'])) {
                                    $clsSec .= ' <span class="badge bg-label-info fs-tiny">Sec: ' . htmlspecialchars($row['sectionname']) . '</span>';
                                }
                                $startDate = !empty($row['datestart']) ? date('d M Y', strtotime($row['datestart'])) : '&mdash;';
                                $pubDate = !empty($row['result_publish']) ? date('d M Y, h:i A', strtotime($row['result_publish'])) : '&mdash;';
                                ?>
                                <tr>
                                    <td class="ps-3 text-muted small fw-semibold">#<?= $row['id'] ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['examtitle']) ?></div>
                                        <?php if (!empty($row['examcode'])): ?>
                                            <span class="fs-tiny text-muted">Code: <code><?= htmlspecialchars($row['examcode']) ?></code></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $clsSec ?></td>
                                    <td>
                                        <i class="bi bi-calendar3 text-muted me-1 small"></i>
                                        <span class="small"><?= $startDate ?></span>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock-history text-muted me-1 small"></i>
                                        <span class="small text-muted"><?= $pubDate ?></span>
                                    </td>
                                    <td class="text-center"><?= $statusBadge ?></td>
                                    <td class="text-center pe-3">
                                        <!-- 3-Dot Action Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-light rounded-circle dropdown-toggle hide-arrow shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-6"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item py-2 editBtn" href="javascript:void(0)" data-id='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>
                                                        <i class="bi bi-pencil-square text-info me-2"></i> Edit Exam
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="exam-routine.php">
                                                        <i class="bi bi-calendar2-range text-primary me-2"></i> Exam Routine
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-danger delBtn" href="javascript:void(0)" data-id="<?= $row['id'] ?>" data-title="<?= htmlspecialchars($row['examtitle']) ?>">
                                                        <i class="bi bi-trash text-danger me-2"></i> Delete Exam
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                    No examinations found for Slot: <strong><?= htmlspecialchars($slot) ?></strong>, Session: <strong><?= htmlspecialchars($session) ?></strong>
                                </td>
                            </tr>
                        <?php endif; 
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add / Edit Exam -->
<div class="modal fade" id="examModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="examForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="examModalTitle">
                        <i class="bi bi-card-checklist text-primary me-1"></i> Exam Entry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="mode" id="mode">

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Session Year</label>
                        <select name="sessionyear" id="sessionyear" class="form-select form-select-sm">
                            <?php
                            $sesRes2 = $conn->query("SELECT DISTINCT syear FROM sessionyear WHERE active=1 AND sccode='$sccode' ORDER BY syear DESC");
                            if ($sesRes2) {
                                while ($y = $sesRes2->fetch_assoc()) {
                                    $sel = ($session == $y['syear']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($y['syear']) . "\" $sel>" . htmlspecialchars($y['syear']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Slot / Unit</label>
                        <select name="slot" id="slot" class="form-select form-select-sm">
                            <?php
                            $slotRes2 = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' AND slotname IS NOT NULL AND slotname != '' ORDER BY id ASC");
                            if ($slotRes2) {
                                while ($s = $slotRes2->fetch_assoc()) {
                                    $sel = ($slot === $s['slotname']) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($s['slotname']) . "\" $sel>" . htmlspecialchars($s['slotname']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Exam Title <span class="text-danger">*</span></label>
                        <input type="text" name="examtitle" id="examtitle" class="form-control form-control-sm" placeholder="e.g. Half Yearly Examination 2026" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Exam Code</label>
                        <input type="text" name="examcode" id="examcode" class="form-control form-control-sm" placeholder="Auto-generated if empty">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Target Class</label>
                        <select name="classname" id="classname" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            <?php
                            $clsQ = $conn->query("SELECT DISTINCT areaname FROM areas WHERE sccode='$sccode' AND areaname IS NOT NULL AND areaname != '' ORDER BY areaname ASC");
                            if ($clsQ) {
                                while ($c = $clsQ->fetch_assoc()) {
                                    echo "<option value=\"" . htmlspecialchars($c['areaname']) . "\">" . htmlspecialchars($c['areaname']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Target Section (Optional)</label>
                        <input type="text" name="sectionname" id="sectionname" class="form-control form-control-sm" placeholder="Leave empty for all sections">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Start Date</label>
                        <input type="date" name="datestart" id="datestart" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Result Publish Date & Time</label>
                        <input type="datetime-local" name="result_publish" id="result_publish" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveExamBtn">
                        <i class="bi bi-check-circle me-1"></i> Save Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    const examModalEl = document.getElementById('examModal');
    const examModal = new bootstrap.Modal(examModalEl);

    // Open Add Modal
    document.getElementById('addNewBtn').onclick = () => {
        document.getElementById('examForm').reset();
        document.getElementById('id').value = '';
        document.getElementById('mode').value = 'add';
        document.getElementById('examModalTitle').innerHTML = '<i class="bi bi-plus-circle text-primary me-1"></i> Add New Exam';
        document.getElementById('sessionyear').value = document.getElementById('sessionFilter').value;
        document.getElementById('slot').value = document.getElementById('slotFilter').value;
        document.getElementById('status').value = '1';
        examModal.show();
    };

    // Open Edit Modal
    $(document).on('click', '.editBtn', function() {
        let d = JSON.parse($(this).attr('data-id'));
        document.getElementById('mode').value = 'edit';
        document.getElementById('id').value = d.id;
        document.getElementById('examModalTitle').innerHTML = '<i class="bi bi-pencil-square text-info me-1"></i> Edit Exam';
        document.getElementById('sessionyear').value = d.sessionyear || '';
        document.getElementById('slot').value = d.slot || '';
        document.getElementById('examtitle').value = d.examtitle || '';
        document.getElementById('examcode').value = d.examcode || '';
        document.getElementById('classname').value = d.classname || '';
        document.getElementById('sectionname').value = d.sectionname || '';
        document.getElementById('datestart').value = d.datestart || '';
        document.getElementById('result_publish').value = d.result_publish ? d.result_publish.replace(' ', 'T') : '';
        document.getElementById('status').value = d.status !== undefined ? d.status : '1';
        examModal.show();
    });

    // Form Submit Handler
    document.getElementById('examForm').onsubmit = e => {
        e.preventDefault();
        let formData = new FormData(document.getElementById('examForm'));
        
        fetch('exam/exam_save.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                examModal.hide();
                Swal.fire({
                    title: 'Success!',
                    text: res.message || 'Exam saved successfully',
                    icon: 'success',
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message || 'Failed to save exam', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'An unexpected network error occurred', 'error');
        });
    };

    // Delete Exam Handler
    $(document).on('click', '.delBtn', function() {
        let id = $(this).attr('data-id');
        let title = $(this).attr('data-title') || 'this exam';

        Swal.fire({
            title: 'Delete Exam?',
            html: `Are you sure you want to delete <strong>${title}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('exam/exam_delete.php', { id: id }, function(res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Exam deleted successfully',
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete exam', 'error');
                        }
                    } catch(e) {
                        if (res === 'ok') {
                            location.reload();
                        } else {
                            Swal.fire('Error', 'Server response: ' + res, 'error');
                        }
                    }
                }).fail(() => {
                    Swal.fire('Error', 'Could not connect to server', 'error');
                });
            }
        });
    });

    // Filter Change Handlers
    function applyFilter() {
        const slot = document.getElementById('slotFilter').value;
        const session = document.getElementById('sessionFilter').value;
        window.location.href = `?slot=${encodeURIComponent(slot)}&session=${encodeURIComponent(session)}`;
    }

    document.getElementById('slotFilter').onchange = applyFilter;
    document.getElementById('sessionFilter').onchange = applyFilter;
</script>
</body>
</html>