<?php
require_once 'header.php';

$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_slot = $_GET['slot'] ?? '';

// Basic date validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

$prev_date = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$next_date = date('Y-m-d', strtotime($selected_date . ' +1 day'));
$is_today = ($selected_date === date('Y-m-d'));

// 1. Fetch Institution Active Slots / Shifts
$slots_list = [];
$slot_stmt = $conn->prepare("SELECT DISTINCT slotname FROM slots WHERE sccode = ? AND slotname IS NOT NULL AND slotname != '' ORDER BY id ASC");
if ($slot_stmt) {
    $slot_stmt->bind_param("i", $sccode);
    $slot_stmt->execute();
    $s_res = $slot_stmt->get_result();
    while ($sr = $s_res->fetch_assoc()) {
        $slots_list[] = $sr['slotname'];
    }
    $slot_stmt->close();
}

// 2. Fetch Active Teachers
$slot_where = "";
$params = [$sccode];
$types = "i";

if (!empty($selected_slot)) {
    $slot_where = " AND (slots = ? OR slots LIKE ?)";
    $params[] = $selected_slot;
    $params[] = "%$selected_slot%";
    $types .= "ss";
}

$t_sql = "SELECT tid, tname, position, ranks, sl, mobile, slots 
          FROM teacher 
          WHERE sccode = ? $slot_where 
          ORDER BY CAST(ranks AS UNSIGNED) ASC, CAST(sl AS UNSIGNED) ASC, tid ASC";

$t_stmt = $conn->prepare($t_sql);
$t_stmt->bind_param($types, ...$params);
$t_stmt->execute();
$teachers_res = $t_stmt->get_result();

$teachers = [];
while ($row = $teachers_res->fetch_assoc()) {
    $teachers[$row['tid']] = $row;
}
$t_stmt->close();

// 3. Fetch Existing Attendance for the date
$existing_att = [];
$att_stmt = $conn->prepare("SELECT * FROM teacherattnd WHERE sccode = ? AND adate = ?");
$att_stmt->bind_param("is", $sccode, $selected_date);
$att_stmt->execute();
$att_res = $att_stmt->get_result();
while ($row = $att_res->fetch_assoc()) {
    $existing_att[$row['tid']] = $row;
}
$att_stmt->close();

// 4. Fetch Approved Leaves for the date
$approved_leaves = [];
$leave_stmt = $conn->prepare("SELECT tid, leave_type FROM teacher_leave_app WHERE sccode = ? AND status = 1 AND date_from <= ? AND date_to >= ?");
$leave_stmt->bind_param("iss", $sccode, $selected_date, $selected_date);
$leave_stmt->execute();
$leave_res = $leave_stmt->get_result();
while ($row = $leave_res->fetch_assoc()) {
    $approved_leaves[$row['tid']] = $row['leave_type'];
}
$leave_stmt->close();

// 5. Check Holiday in Calendar
$is_holiday = false;
$holiday_reason = '';
$cal_stmt = $conn->prepare("SELECT category, work FROM calendar WHERE (sccode = ? OR sccode = 0) AND date <= ? AND (dateto >= ? OR (dateto IS NULL AND date = ?)) LIMIT 1");
$cal_stmt->bind_param("isss", $sccode, $selected_date, $selected_date, $selected_date);
$cal_stmt->execute();
$cal_res = $cal_stmt->get_result()->fetch_assoc();
if ($cal_res && strval($cal_res['work']) === '0') {
    $is_holiday = true;
    $holiday_reason = $cal_res['category'] ?? 'Holiday';
}
$cal_stmt->close();
?>

<style>
    .att-kpi-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .att-kpi-card:hover {
        transform: translateY(-2px);
    }
    .teacher-photo-thumb {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .teacher-initial-thumb {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        background: #e0e7ff;
        color: #4338ca;
        border: 2px solid #c7d2fe;
    }
    .btn-status-group .btn-check:checked + .btn-outline-success {
        background-color: #198754 !important;
        color: #fff !important;
        border-color: #198754 !important;
    }
    .btn-status-group .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
        border-color: #ffc107 !important;
    }
    .btn-status-group .btn-check:checked + .btn-outline-info {
        background-color: #0dcaf0 !important;
        color: #000 !important;
        border-color: #0dcaf0 !important;
    }
    .btn-status-group .btn-check:checked + .btn-outline-danger {
        background-color: #dc3545 !important;
        color: #fff !important;
        border-color: #dc3545 !important;
    }
    .btn-status-group .btn {
        padding: 4px 10px;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .sticky-action-bar {
        position: sticky;
        bottom: 15px;
        z-index: 1020;
        background: rgba(30, 41, 59, 0.95);
        backdrop-filter: blur(8px);
        color: #fff;
        border-radius: 12px;
        padding: 12px 24px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    }
    .table-att thead th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        font-size: 0.85rem;
        border-bottom: 2px solid #cbd5e1;
    }
    .table-att tbody tr:hover {
        background-color: #f8fafc;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Banner Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="bi bi-calendar-check me-2"></i>Teacher &amp; Staff Attendance Entry
                    </h4>
                    <p class="text-muted mb-0 small">
                        Daily manual attendance recording, check-in/out time logging, and leave synchronization.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="teacher-attendance-report.php?month=<?= date('m', strtotime($selected_date)) ?>&year=<?= date('Y', strtotime($selected_date)) ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-grid-3x3 me-1"></i> Monthly Matrix
                    </a>
                    <a href="attendance-view.php?date=<?= urlencode($selected_date) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-eye me-1"></i> Daily View
                    </a>
                    <a href="teachers-list.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-people me-1"></i> Faculty List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Navigator & Controls Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center justify-content-between">
                
                <!-- Date Selector & Quick Buttons -->
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="d-flex align-items-center gap-2">
                        <a href="teacher-attendance-entry.php?date=<?= $prev_date ?><?= !empty($selected_slot) ? ('&slot='.urlencode($selected_slot)) : '' ?>" class="btn btn-outline-secondary btn-sm" title="Previous Day">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" id="attendance-date" class="form-control fw-bold" value="<?= htmlspecialchars($selected_date) ?>" onchange="changeDate(this.value)">
                        </div>

                        <a href="teacher-attendance-entry.php?date=<?= $next_date ?><?= !empty($selected_slot) ? ('&slot='.urlencode($selected_slot)) : '' ?>" class="btn btn-outline-secondary btn-sm" title="Next Day">
                            <i class="bi bi-chevron-right"></i>
                        </a>

                        <?php if (!$is_today): ?>
                            <a href="teacher-attendance-entry.php?date=<?= date('Y-m-d') ?><?= !empty($selected_slot) ? ('&slot='.urlencode($selected_slot)) : '' ?>" class="btn btn-sm btn-primary">
                                Today
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shift / Slot Filter & Quick Fill -->
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                        <?php if (!empty($slots_list)): ?>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="changeSlot(this.value)">
                                <option value="">All Shifts / Slots</option>
                                <?php foreach ($slots_list as $sl): ?>
                                    <option value="<?= htmlspecialchars($sl) ?>" <?= $selected_slot === $sl ? 'selected' : '' ?>><?= htmlspecialchars($sl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-success" onclick="markAllStatus('present')">
                                <i class="bi bi-check-all me-1"></i> All Present
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="markAllStatus('absent')">
                                <i class="bi bi-x-lg me-1"></i> All Absent
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetDefaultTimes()">
                                <i class="bi bi-clock-history me-1"></i> Reset Times
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <?php if ($is_holiday): ?>
                <div class="alert alert-warning py-2 px-3 mt-3 mb-0 d-flex align-items-center small">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                    <div>
                        <strong>Notice:</strong> This day is marked as a Holiday (<strong><?= htmlspecialchars($holiday_reason) ?></strong>) in the Academic Calendar.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Live Statistics KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-primary">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Total Faculty</div>
                        <h4 class="mb-0 fw-bold text-primary" id="kpi-total"><?= count($teachers) ?></h4>
                    </div>
                    <i class="bi bi-people fs-2 text-primary opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-success">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Present</div>
                        <h4 class="mb-0 fw-bold text-success" id="kpi-present">0</h4>
                    </div>
                    <i class="bi bi-person-check fs-2 text-success opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-warning">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Late Entry</div>
                        <h4 class="mb-0 fw-bold text-warning" id="kpi-late">0</h4>
                    </div>
                    <i class="bi bi-clock fs-2 text-warning opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-danger">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Absent / Leave</div>
                        <h4 class="mb-0 fw-bold text-danger" id="kpi-absent">0</h4>
                    </div>
                    <i class="bi bi-person-x fs-2 text-danger opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Grid Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-att align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="min-width: 250px;">Teacher &amp; Designation</th>
                            <th style="min-width: 240px;" class="text-center">Attendance Status</th>
                            <th style="min-width: 130px;">Check In</th>
                            <th style="min-width: 130px;">Check Out</th>
                            <th style="min-width: 120px;" class="text-center">Log Type</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody">
                        <?php 
                        $sl = 1;
                        foreach ($teachers as $tid => $t): 
                            $att = $existing_att[$tid] ?? null;
                            $has_leave = $approved_leaves[$tid] ?? null;

                            // Default in/out times
                            $in_val = $att['realin'] ?? '09:00';
                            if (strlen($in_val) > 5) $in_val = substr($in_val, 0, 5);

                            $out_val = $att['realout'] ?? '16:00';
                            if (strlen($out_val) > 5) $out_val = substr($out_val, 0, 5);

                            // Determine initial status
                            $init_status = 'present';
                            if ($att) {
                                $st_in = strtolower($att['statusin'] ?? '');
                                if (in_array($st_in, ['absent', 'a'])) {
                                    $init_status = 'absent';
                                } elseif (in_array($st_in, ['late', 'l'])) {
                                    $init_status = 'late';
                                } elseif (in_array($st_in, ['leave', 'lv'])) {
                                    $init_status = 'leave';
                                } else {
                                    $init_status = 'present';
                                }
                            } elseif ($has_leave) {
                                $init_status = 'leave';
                            }
                        ?>
                            <tr class="att-row" data-tid="<?= $tid ?>">
                                <td class="text-center fw-bold text-muted"><?= $sl++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php $tphoto_url = teacher_profile_image_path($tid); ?>
                                        <img src="<?= htmlspecialchars($tphoto_url) ?>" alt="Photo" class="teacher-photo-thumb" onerror="this.onerror=null;this.src='assets/img/avatars/1.png';">
                                        <div>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($t['tname']) ?></div>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($t['position'] ?: 'Faculty') ?> &bull; 
                                                <span class="text-primary fw-semibold">ID: <?= $tid ?></span>
                                                <?php if (!empty($t['slots'])): ?>
                                                    <span class="badge bg-label-secondary ms-1"><?= htmlspecialchars($t['slots']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($has_leave): ?>
                                                <span class="badge bg-info mt-1 small">
                                                    <i class="bi bi-card-text me-1"></i>Approved Leave: <?= htmlspecialchars($has_leave) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Buttons -->
                                <td class="text-center">
                                    <div class="btn-group btn-status-group" role="group">
                                        <input type="radio" class="btn-check status-radio" name="status_<?= $tid ?>" id="st_p_<?= $tid ?>" value="present" <?= $init_status === 'present' ? 'checked' : '' ?> onchange="onStatusChange(<?= $tid ?>)">
                                        <label class="btn btn-outline-success" for="st_p_<?= $tid ?>">Present</label>

                                        <input type="radio" class="btn-check status-radio" name="status_<?= $tid ?>" id="st_l_<?= $tid ?>" value="late" <?= $init_status === 'late' ? 'checked' : '' ?> onchange="onStatusChange(<?= $tid ?>)">
                                        <label class="btn btn-outline-warning" for="st_l_<?= $tid ?>">Late</label>

                                        <input type="radio" class="btn-check status-radio" name="status_<?= $tid ?>" id="st_lv_<?= $tid ?>" value="leave" <?= $init_status === 'leave' ? 'checked' : '' ?> onchange="onStatusChange(<?= $tid ?>)">
                                        <label class="btn btn-outline-info" for="st_lv_<?= $tid ?>">Leave</label>

                                        <input type="radio" class="btn-check status-radio" name="status_<?= $tid ?>" id="st_a_<?= $tid ?>" value="absent" <?= $init_status === 'absent' ? 'checked' : '' ?> onchange="onStatusChange(<?= $tid ?>)">
                                        <label class="btn btn-outline-danger" for="st_a_<?= $tid ?>">Absent</label>
                                    </div>
                                </td>

                                <!-- In Time -->
                                <td>
                                    <input type="time" class="form-control form-control-sm in-time-input" id="in_<?= $tid ?>" value="<?= ($init_status === 'absent' || $init_status === 'leave') ? '' : htmlspecialchars($in_val) ?>" <?= ($init_status === 'absent' || $init_status === 'leave') ? 'disabled' : '' ?>>
                                </td>

                                <!-- Out Time -->
                                <td>
                                    <input type="time" class="form-control form-control-sm out-time-input" id="out_<?= $tid ?>" value="<?= ($init_status === 'absent' || $init_status === 'leave') ? '' : htmlspecialchars($out_val) ?>" <?= ($init_status === 'absent' || $init_status === 'leave') ? 'disabled' : '' ?>>
                                </td>

                                <!-- Log Source -->
                                <td class="text-center">
                                    <span class="badge bg-label-secondary small font-monospace">
                                        <?= htmlspecialchars($att['detectin'] ?? 'Manual') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($teachers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-info-circle fs-3 d-block mb-1"></i>
                                    No active teachers found matching the selected filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sticky Floating Action Bar -->
    <?php if (!empty($teachers)): ?>
        <div class="sticky-action-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-check fs-4 text-success"></i>
                <div>
                    <div class="fw-bold fs-6">Teacher Attendance Session: <?= date('d M Y', strtotime($selected_date)) ?></div>
                    <small class="text-white-50" id="sticky-summary-txt">Ready to save</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Discard
                </button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btn-save-all" onclick="saveAllAttendance()">
                    <i class="bi bi-cloud-check-fill me-1"></i> Save Attendance
                </button>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    function changeDate(newDate) {
        if (!newDate) return;
        var slot = encodeURIComponent('<?= $selected_slot ?>');
        window.location.href = 'teacher-attendance-entry.php?date=' + encodeURIComponent(newDate) + (slot ? '&slot=' + slot : '');
    }

    function changeSlot(newSlot) {
        var date = encodeURIComponent('<?= $selected_date ?>');
        window.location.href = 'teacher-attendance-entry.php?date=' + date + (newSlot ? '&slot=' + encodeURIComponent(newSlot) : '');
    }

    function onStatusChange(tid) {
        var status = $('input[name="status_' + tid + '"]:checked').val();
        var inInput = $('#in_' + tid);
        var outInput = $('#out_' + tid);

        if (status === 'absent' || status === 'leave') {
            inInput.val('').prop('disabled', true);
            outInput.val('').prop('disabled', true);
        } else {
            inInput.prop('disabled', false);
            outInput.prop('disabled', false);
            if (!inInput.val()) inInput.val('09:00');
            if (!outInput.val()) outInput.val('16:00');
        }
        recalculateKPI();
    }

    function markAllStatus(status) {
        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            $('input[name="status_' + tid + '"][value="' + status + '"]').prop('checked', true);
            onStatusChange(tid);
        });
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Marked all as ' + status.toUpperCase(),
            showConfirmButton: false,
            timer: 1500
        });
    }

    function resetDefaultTimes() {
        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var status = $('input[name="status_' + tid + '"]:checked').val();
            if (status !== 'absent' && status !== 'leave') {
                $('#in_' + tid).val('09:00');
                $('#out_' + tid).val('16:00');
            }
        });
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Reset default times (09:00 - 16:00)',
            showConfirmButton: false,
            timer: 1500
        });
    }

    function recalculateKPI() {
        var total = $('.att-row').length;
        var present = 0;
        var late = 0;
        var leave = 0;
        var absent = 0;

        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var st = $('input[name="status_' + tid + '"]:checked').val();
            if (st === 'present') present++;
            else if (st === 'late') late++;
            else if (st === 'leave') leave++;
            else if (st === 'absent') absent++;
        });

        $('#kpi-total').text(total);
        $('#kpi-present').text(present);
        $('#kpi-late').text(late);
        $('#kpi-absent').text(absent + leave);

        $('#sticky-summary-txt').text('Present: ' + present + ' | Late: ' + late + ' | Absent/Leave: ' + (absent + leave));
    }

    function saveAllAttendance() {
        var records = [];
        var dateVal = $('#attendance-date').val();

        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var status = $('input[name="status_' + tid + '"]:checked').val() || 'present';
            var realin = $('#in_' + tid).val();
            var realout = $('#out_' + tid).val();

            records.push({
                tid: tid,
                status: status,
                realin: realin,
                realout: realout,
                detectin: 'Manual'
            });
        });

        if (records.length === 0) {
            Swal.fire('No Records', 'No teacher records found to save.', 'warning');
            return;
        }

        var saveBtn = $('#btn-save-all');
        saveBtn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spin me-1"></i> Saving...');

        $.ajax({
            url: 'backend/save-teacher-attendance.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                date: dateVal,
                records: records
            }),
            dataType: 'json',
            success: function(res) {
                saveBtn.prop('disabled', false).html('<i class="bi bi-cloud-check-fill me-1"></i> Save Attendance');
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Attendance Saved!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire('Save Failed', res.message || 'An error occurred while saving.', 'error');
                }
            },
            error: function() {
                saveBtn.prop('disabled', false).html('<i class="bi bi-cloud-check-fill me-1"></i> Save Attendance');
                Swal.fire('Error', 'Unable to reach server. Please check connection.', 'error');
            }
        });
    }

    $(document).ready(function() {
        recalculateKPI();
    });
</script>

<?php require_once 'footer.php'; ?>
