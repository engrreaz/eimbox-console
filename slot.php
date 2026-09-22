<?php
include 'header.php'; // already includes config & db
$slotQ = mysqli_query($conn, "SELECT * FROM slots WHERE sccode='$sccode' ORDER BY id DESC");
$slotCount = $slotQ ? mysqli_num_rows($slotQ) : 0;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Page Header & Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1 fw-bold text-primary">
                    <i class="bi bi-diagram-3-fill me-2"></i>Academic Slot / Unit Management
                </h5>
                <small class="text-muted">
                    Configure shift units, merit evaluation rules, mark rounding, name translations, and guardian title formats.
                </small>
            </div>
            <div class="d-flex gap-2">
                <?php if ($slotCount == 0) { ?>
                    <button class="btn btn-warning btn-sm px-3 shadow-sm fw-bold" onclick="setDefault()">
                        <i class="bi bi-lightning-charge me-1"></i> Set Default Slot
                    </button>
                <?php } ?>
                <button class="btn btn-primary btn-sm px-3 shadow-sm fw-bold" onclick="openCreate()">
                    <i class="bi bi-plus-lg me-1"></i> Add New Slot
                </button>
            </div>
        </div>
    </div>

    <?php if ($slotCount == 0) { ?>
        <div class="alert alert-warning shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>No Slots Configured!</strong><br>
                Your institution currently has no active slot/unit entries. Click <strong>Set Default Slot</strong> or <strong>Add New Slot</strong> to configure your academic unit.
            </div>
        </div>
    <?php } ?>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center bg-light">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-list-ul me-2 text-primary"></i>Configured Academic Slots (<span class="text-primary"><?= $slotCount ?></span>)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Slot / Unit Name</th>
                        <th class="text-center">Merit System</th>
                        <th class="text-center">Decimal Rounding</th>
                        <th class="text-center">Mark Entry Format</th>
                        <th class="text-center">Guardian Format</th>
                        <th class="text-center">Translations</th>
                        <th class="text-center">Custom Report</th>
                        <th class="text-center" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($slotCount > 0) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($slotQ)) {
                            $meritLabel = ($row['merit'] == 1) ? '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-trophy me-1"></i>GPA System</span>' : '<span class="badge bg-info bg-opacity-10 text-info border border-info"><i class="bi bi-calculator me-1"></i>Total Marks</span>';
                            
                            $decVal = intval($row['decimal_mark']);
                            $decLabel = ($decVal == 1) ? '<span class="badge bg-primary bg-opacity-10 text-primary">Exact Decimal</span>' : (($decVal == 2) ? '<span class="badge bg-secondary bg-opacity-10 text-secondary">Round Off</span>' : '<span class="badge bg-light text-dark border">Nearest Top Int</span>');

                            $entryVal = intval($row['disp_entry_mark'] ?? 0);
                            $entryLabel = ($entryVal == 1) ? '<span class="badge bg-primary bg-opacity-10 text-primary">Allow Decimal</span>' : '<span class="badge bg-light text-dark border">Integer Only</span>';

                            $parentsVal = htmlspecialchars($row['parents'] ?? 'DOSO');
                            $parentsLabel = ($parentsVal === 'FM') ? '<span class="badge bg-dark bg-opacity-10 text-dark">FM (Father/Mother)</span>' : '<span class="badge bg-primary bg-opacity-10 text-primary">DOSO (D/O, S/O)</span>';

                            $engTrans = intval($row['trans_name_eng'] ?? 1) == 1;
                            $benTrans = intval($row['trans_name_ben'] ?? 1) == 1;
                            $transLabel = '<span class="badge ' . ($engTrans ? 'bg-success' : 'bg-secondary') . ' me-1">ENG</span><span class="badge ' . ($benTrans ? 'bg-success' : 'bg-secondary') . '">BEN</span>';

                            $cusReport = htmlspecialchars($row['cus_report'] ?? 'default');
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2 bg-primary bg-opacity-10 rounded text-primary d-flex align-items-center justify-content-center">
                                            <i class="bi bi-tag-fill"></i>
                                        </div>
                                        <div>
                                            <strong class="text-primary fs-6"><?= htmlspecialchars($row['slotname']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?= $meritLabel ?></td>
                                <td class="text-center"><?= $decLabel ?></td>
                                <td class="text-center"><?= $entryLabel ?></td>
                                <td class="text-center"><?= $parentsLabel ?></td>
                                <td class="text-center"><?= $transLabel ?></td>
                                <td class="text-center"><span class="badge bg-light text-secondary border"><?= $cusReport ?></span></td>
                                <td class="text-center">
                                    <!-- 3-Dot Action Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light rounded-circle dropdown-toggle hide-arrow shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-6"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <a class="dropdown-item py-2" href="javascript:void(0)" onclick='openEdit(<?= json_encode($row) ?>)'>
                                                    <i class="bi bi-pencil-square text-info me-2"></i> Edit Slot Setup
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="deleteSlot(<?= $row['id'] ?>)">
                                                    <i class="bi bi-trash text-danger me-2"></i> Delete Slot
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No academic slots found. Click <strong>Add New Slot</strong> to create one.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<!-- Configure Slot Modal -->
<div class="modal fade" id="slotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="slotForm">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-primary" id="slotModalTitle">
                        <i class="bi bi-gear-wide-connected me-2"></i>Configure Academic Slot / Unit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="sccode" value="<?= $sccode ?>">

                    <div class="row g-3">
                        
                        <!-- Slot Name -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">SLOT / UNIT NAME <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" name="slotname" id="slotname" placeholder="e.g. School, College, Morning, Day" required>
                            </div>
                            <small class="text-muted">Unique name for this shift or academic unit</small>
                        </div>

                        <!-- Merit System -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">MERIT EVALUATION SYSTEM</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-trophy"></i></span>
                                <select class="form-select" name="merit" id="merit">
                                    <option value="1" selected>GPA System (Grade Point Average)</option>
                                    <option value="0">Total Marks System</option>
                                </select>
                            </div>
                            <small class="text-muted">Primary basis for calculating merit positions</small>
                        </div>

                        <!-- Decimal Rounding -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">DECIMAL MARK ROUNDING</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                                <select class="form-select" name="decimal_mark" id="decimal_mark">
                                    <option value="0">0 — Nearest Top Integer</option>
                                    <option value="1">1 — Exact Decimal Precision</option>
                                    <option value="2">2 — Round Off (.5 and above)</option>
                                </select>
                            </div>
                            <small class="text-muted">How fractional marks are stored and displayed</small>
                        </div>

                        <!-- Display Entry Mark -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">MARK ENTRY FORMAT</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-input-cursor-text"></i></span>
                                <select class="form-select" name="disp_entry_mark" id="disp_entry_mark">
                                    <option value="0">0 — Integer Only (Whole numbers)</option>
                                    <option value="1">1 — Allow Decimal Inputs (.5, .75)</option>
                                </select>
                            </div>
                            <small class="text-muted">Formatting allowed during teacher mark entry</small>
                        </div>

                        <!-- Guardian Format -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">GUARDIAN TITLE FORMAT</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <select class="form-select" name="parents" id="parents">
                                    <option value="DOSO" selected>DOSO — Daughter of / Son of</option>
                                    <option value="FM">FM — Father / Mother</option>
                                </select>
                            </div>
                            <small class="text-muted">Header styling for parent details in documents</small>
                        </div>

                        <!-- Custom Report Template -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">CUSTOM REPORT TEMPLATE</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-file-earmark-code"></i></span>
                                <input type="text" class="form-control" name="cus_report" id="cus_report" value="default" placeholder="default">
                            </div>
                            <small class="text-muted">Template identifier for progress reports</small>
                        </div>

                        <!-- English Name Translation -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ENGLISH NAME TRANSLATION</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-translate"></i></span>
                                <select class="form-select" name="trans_name_eng" id="trans_name_eng">
                                    <option value="1" selected>1 — Enabled</option>
                                    <option value="0">0 — Disabled</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bengali Name Translation -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">BENGALI NAME TRANSLATION</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-translate"></i></span>
                                <select class="form-select" name="trans_name_ben" id="trans_name_ben">
                                    <option value="1" selected>1 — Enabled</option>
                                    <option value="0">0 — Disabled</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save Configuration
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    // Open Create Modal
    function openCreate() {
        document.getElementById("slotForm").reset();
        document.getElementById("id").value = "";
        document.getElementById("slotModalTitle").innerHTML = '<i class="bi bi-plus-circle me-2 text-primary"></i>Add New Slot / Unit';
        document.getElementById("merit").value = "1";
        document.getElementById("decimal_mark").value = "0";
        document.getElementById("disp_entry_mark").value = "0";
        document.getElementById("parents").value = "DOSO";
        document.getElementById("trans_name_eng").value = "1";
        document.getElementById("trans_name_ben").value = "1";
        document.getElementById("cus_report").value = "default";

        const modal = new bootstrap.Modal(document.getElementById('slotModal'));
        modal.show();
    }

    // Open Edit Modal
    function openEdit(row) {
        document.getElementById("id").value = row.id;
        document.getElementById("slotname").value = row.slotname || "";
        document.getElementById("merit").value = row.merit !== undefined ? row.merit : "1";
        document.getElementById("decimal_mark").value = row.decimal_mark !== undefined ? row.decimal_mark : "0";
        document.getElementById("disp_entry_mark").value = row.disp_entry_mark !== undefined ? row.disp_entry_mark : "0";
        document.getElementById("parents").value = row.parents || "DOSO";
        document.getElementById("trans_name_eng").value = row.trans_name_eng !== undefined ? row.trans_name_eng : "1";
        document.getElementById("trans_name_ben").value = row.trans_name_ben !== undefined ? row.trans_name_ben : "1";
        document.getElementById("cus_report").value = row.cus_report || "default";

        document.getElementById("slotModalTitle").innerHTML = '<i class="bi bi-pencil-square me-2 text-info"></i>Edit Slot Setup — ' + (row.slotname || '');
        
        const modal = new bootstrap.Modal(document.getElementById('slotModal'));
        modal.show();
    }

    // Submit Slot Form via AJAX
    document.getElementById("slotForm")?.addEventListener("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        fetch("settings/save-slot.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.text())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Save Configuration';

            const modalEl = document.getElementById('slotModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Slot Saved!',
                    text: data,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                alert(data);
                location.reload();
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Save Configuration';
            console.error("Save Slot Error:", err);
            alert("Failed to save slot: " + err.message);
        });
    });

    // Delete Slot
    function deleteSlot(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Slot?',
                text: 'Are you sure you want to delete this slot/unit configuration? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete Slot'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDelete(id);
                }
            });
        } else {
            if (confirm("Are you sure you want to delete this slot?")) {
                executeDelete(id);
            }
        }
    }

    function executeDelete(id) {
        fetch("settings/slot-delete.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        })
        .then(res => res.text())
        .then(data => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Slot Deleted',
                    text: data,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                alert(data);
                location.reload();
            }
        })
        .catch(err => {
            console.error("Delete Slot Error:", err);
            alert("Failed to delete slot: " + err.message);
        });
    }

    // Set Default Slot
    function setDefault() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Create Default Slot?',
                text: 'This will automatically generate a default academic slot for your institution.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Yes, Create Default'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeSetDefault();
                }
            });
        } else {
            if (confirm("Create default slot for your institution?")) {
                executeSetDefault();
            }
        }
    }

    function executeSetDefault() {
        fetch("settings/save-slot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "slotname=<?= urlencode($sctype ?? 'School') ?>"
        })
        .then(res => res.text())
        .then(data => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Default Slot Created',
                    text: data,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                alert(data);
                location.reload();
            }
        });
    }
</script>
</body>
</html>