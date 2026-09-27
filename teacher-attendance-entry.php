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

// 3. Fetch Existing Attendance from teacherattnd for the date
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
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #cbd5e1;
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
    .btn-status-group .btn-check:checked + .btn-outline-secondary {
        background-color: #64748b !important;
        color: #fff !important;
        border-color: #64748b !important;
    }
    .btn-status-group .btn {
        padding: 4px 8px;
        font-size: 0.8rem;
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
    .time-input-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .time-input-wrap input[type="time"] {
        max-width: 110px;
    }
    .row-unmarked {
        background-color: #ffffff;
    }
    .row-present {
        background-color: rgba(25, 135, 84, 0.03);
    }
    .row-late {
        background-color: rgba(255, 193, 7, 0.06);
    }
    .row-absent {
        background-color: rgba(220, 53, 69, 0.04);
    }
    .row-leave {
        background-color: rgba(13, 202, 240, 0.05);
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
                        Real-time attendance recording &mdash; supports separate <strong>Morning Check-In</strong> and <strong>Afternoon Check-Out</strong> workflows.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="teacher-attendance-report.php?month=<?= date('m', strtotime($selected_date)) ?>&year=<?= date('Y', strtotime($selected_date)) ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-grid-3x3 me-1"></i> Monthly Matrix
                    </a>
                    <a href="attendance-view.php?date=<?= urlencode($selected_date) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-eye me-1"></i> Daily Attendance View
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
                <div class="col-12 col-md-5">
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

                <!-- Shift Filter & 2-Stage Action Buttons -->
                <div class="col-12 col-md-7">
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                        <?php if (!empty($slots_list)): ?>
                            <select class="form-select form-select-sm" style="width: auto;" onchange="changeSlot(this.value)">
                                <option value="">All Shifts / Slots</option>
                                <?php foreach ($slots_list as $sl): ?>
                                    <option value="<?= htmlspecialchars($sl) ?>" <?= $selected_slot === $sl ? 'selected' : '' ?>><?= htmlspecialchars($sl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <!-- In-Time Stage Button -->
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickFillCheckIn()" title="Set current time to Check-In for present teachers">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Check-In (Now)
                        </button>

                        <!-- Out-Time Stage Button -->
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="quickFillCheckOut()" title="Set current time to Check-Out for present teachers">
                            <i class="bi bi-box-arrow-right me-1"></i> Check-Out (Now)
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sliders me-1"></i> Batch Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-success" href="javascript:void(0)" onclick="markAllStatus('present')">
                                        <i class="bi bi-check-circle me-2"></i>Mark All Present (09:00 - 16:00)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="markAllStatus('absent')">
                                        <i class="bi bi-x-circle me-2"></i>Mark All Absent
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-muted" href="javascript:void(0)" onclick="clearAllFields()">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Clear Unsaved In/Out Times
                                    </a>
                                </li>
                            </ul>
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
        <div class="col-6 col-md-2">
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

        <div class="col-6 col-md-2">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-success">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Present (In)</div>
                        <h4 class="mb-0 fw-bold text-success" id="kpi-present">0</h4>
                    </div>
                    <i class="bi bi-person-check fs-2 text-success opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-info">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Checked Out</div>
                        <h4 class="mb-0 fw-bold text-info" id="kpi-out">0</h4>
                    </div>
                    <i class="bi bi-box-arrow-right fs-2 text-info opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-warning">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Late</div>
                        <h4 class="mb-0 fw-bold text-warning" id="kpi-late">0</h4>
                    </div>
                    <i class="bi bi-clock fs-2 text-warning opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-danger">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Absent</div>
                        <h4 class="mb-0 fw-bold text-danger" id="kpi-absent">0</h4>
                    </div>
                    <i class="bi bi-person-x fs-2 text-danger opacity-75"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card att-kpi-card border-0 shadow-sm bg-label-secondary">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-muted text-uppercase">Unmarked</div>
                        <h4 class="mb-0 fw-bold text-secondary" id="kpi-unmarked">0</h4>
                    </div>
                    <i class="bi bi-question-circle fs-2 text-secondary opacity-75"></i>
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
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="min-width: 250px;">Teacher &amp; Designation</th>
                            <th style="min-width: 280px;" class="text-center">Attendance Status</th>
                            <th style="min-width: 170px;">Check-In Time</th>
                            <th style="min-width: 170px;">Check-Out Time</th>
                            <th style="min-width: 110px;" class="text-center">Log Type</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-tbody">
                        <?php 
                        $sl = 1;
                        foreach ($teachers as $tid => $t): 
                            $att = $existing_att[$tid] ?? null;
                            $has_leave = $approved_leaves[$tid] ?? null;

                            // Real data from DB
                            $db_in = $att['realin'] ?? '';
                            if (!empty($db_in) && strlen($db_in) > 5) $db_in = substr($db_in, 0, 5);

                            $db_out = $att['realout'] ?? '';
                            if (!empty($db_out) && strlen($db_out) > 5) $db_out = substr($db_out, 0, 5);

                            // Determine actual status based on DB record
                            if ($att) {
                                $st_in = strtolower(trim($att['statusin'] ?? ''));
                                if ($st_in === 'absent' || $st_in === 'a') {
                                    $init_status = 'absent';
                                } elseif ($st_in === 'leave' || $st_in === 'lv') {
                                    $init_status = 'leave';
                                } elseif ($st_in === 'late' || $st_in === 'l') {
                                    $init_status = 'late';
                                } elseif (!empty($db_in) || in_array($st_in, ['normal', 'present', 'fast'])) {
                                    $init_status = 'present';
                                } else {
                                    $init_status = 'unmarked';
                                }
                            } elseif ($has_leave) {
                                $init_status = 'leave';
                            } else {
                                // Not marked yet for this date
                                $init_status = 'unmarked';
                            }
                        ?>
                            <tr class="att-row row-<?= $init_status ?>" data-tid="<?= $tid ?>" id="row_<?= $tid ?>">
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
                                                    <i class="bi bi-card-text me-1"></i>Leave: <?= htmlspecialchars($has_leave) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Selection Radio Group -->
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

                                        <input type="radio" class="btn-check status-radio" name="status_<?= $tid ?>" id="st_u_<?= $tid ?>" value="unmarked" <?= $init_status === 'unmarked' ? 'checked' : '' ?> onchange="onStatusChange(<?= $tid ?>)">
                                        <label class="btn btn-outline-secondary" for="st_u_<?= $tid ?>" title="Unmarked / Not Taken">None</label>
                                    </div>
                                </td>

                                <!-- In Time Input & Quick Now Button -->
                                <td>
                                    <div class="time-input-wrap">
                                        <input type="time" class="form-control form-control-sm in-time-input" id="in_<?= $tid ?>" value="<?= htmlspecialchars($db_in) ?>" <?= ($init_status === 'absent' || $init_status === 'leave' || $init_status === 'unmarked') ? 'disabled' : '' ?> onchange="onTimeManualChange(<?= $tid ?>)">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-2" id="btn_now_in_<?= $tid ?>" onclick="setSingleNow('in', <?= $tid ?>)" title="Set Current Time" <?= ($init_status === 'absent' || $init_status === 'leave' || $init_status === 'unmarked') ? 'disabled' : '' ?>>
                                            <i class="bi bi-clock"></i> Now
                                        </button>
                                    </div>
                                </td>

                                <!-- Out Time Input & Quick Now Button -->
                                <td>
                                    <div class="time-input-wrap">
                                        <input type="time" class="form-control form-control-sm out-time-input" id="out_<?= $tid ?>" value="<?= htmlspecialchars($db_out) ?>" <?= ($init_status === 'absent' || $init_status === 'leave' || $init_status === 'unmarked') ? 'disabled' : '' ?> onchange="onTimeManualChange(<?= $tid ?>)">
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-2" id="btn_now_out_<?= $tid ?>" onclick="setSingleNow('out', <?= $tid ?>)" title="Set Current Time" <?= ($init_status === 'absent' || $init_status === 'leave' || $init_status === 'unmarked') ? 'disabled' : '' ?>>
                                            <i class="bi bi-clock"></i> Now
                                        </button>
                                    </div>
                                </td>

                                <!-- Log Source Badge -->
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
                                    No active faculty found for the selected criteria.
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
                    <small class="text-white-50" id="sticky-summary-txt">Live stats loading...</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reload
                </button>
                <button type="button" class="btn btn-warning px-4 fw-bold text-dark" id="btn-save-all" onclick="saveAllAttendance()">
                    <i class="bi bi-cloud-check-fill me-1"></i> Save Attendance
                </button>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    function getCurrentTimeString() {
        var now = new Date();
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        return hours + ':' + minutes;
    }

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
        var btnIn = $('#btn_now_in_' + tid);
        var btnOut = $('#btn_now_out_' + tid);
        var row = $('#row_' + tid);

        row.removeClass('row-present row-late row-absent row-leave row-unmarked').addClass('row-' + status);

        if (status === 'absent' || status === 'leave' || status === 'unmarked') {
            inInput.val('').prop('disabled', true);
            outInput.val('').prop('disabled', true);
            btnIn.prop('disabled', true);
            btnOut.prop('disabled', true);
        } else {
            inInput.prop('disabled', false);
            outInput.prop('disabled', false);
            btnIn.prop('disabled', false);
            btnOut.prop('disabled', false);

            // If empty and becoming present, set current or default time
            if (!inInput.val() && status === 'present') {
                inInput.val('09:00');
            }
        }
        recalculateKPI();
    }

    function onTimeManualChange(tid) {
        var inVal = $('#in_' + tid).val();
        if (inVal) {
            var currentSt = $('input[name="status_' + tid + '"]:checked').val();
            if (currentSt === 'unmarked' || currentSt === 'absent') {
                $('input[name="status_' + tid + '"][value="present"]').prop('checked', true);
                $('#row_' + tid).removeClass('row-unmarked row-absent').addClass('row-present');
            }
        }
        recalculateKPI();
    }

    function setSingleNow(type, tid) {
        var curTime = getCurrentTimeString();
        var input = (type === 'in') ? $('#in_' + tid) : $('#out_' + tid);
        input.val(curTime);

        // Ensure status is marked as present/late
        var currentSt = $('input[name="status_' + tid + '"]:checked').val();
        if (currentSt === 'unmarked' || currentSt === 'absent' || currentSt === 'leave') {
            $('input[name="status_' + tid + '"][value="present"]').prop('checked', true);
            $('#row_' + tid).removeClass('row-unmarked row-absent row-leave').addClass('row-present');
            $('#in_' + tid).prop('disabled', false);
            $('#out_' + tid).prop('disabled', false);
            $('#btn_now_in_' + tid).prop('disabled', false);
            $('#btn_now_out_' + tid).prop('disabled', false);
        }
        recalculateKPI();
    }

    function quickFillCheckIn() {
        var curTime = getCurrentTimeString();
        var count = 0;
        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var st = $('input[name="status_' + tid + '"]:checked').val();
            if (st !== 'absent' && st !== 'leave') {
                $('input[name="status_' + tid + '"][value="present"]').prop('checked', true);
                $('#in_' + tid).prop('disabled', false).val(curTime);
                $('#out_' + tid).prop('disabled', false);
                $('#btn_now_in_' + tid).prop('disabled', false);
                $('#btn_now_out_' + tid).prop('disabled', false);
                $('#row_' + tid).removeClass('row-unmarked row-absent row-leave').addClass('row-present');
                count++;
            }
        });
        recalculateKPI();
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Set Check-In time (' + curTime + ') for ' + count + ' teachers',
            showConfirmButton: false,
            timer: 1800
        });
    }

    function quickFillCheckOut() {
        var curTime = getCurrentTimeString();
        var count = 0;
        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var st = $('input[name="status_' + tid + '"]:checked').val();
            if (st === 'present' || st === 'late') {
                $('#out_' + tid).prop('disabled', false).val(curTime);
                count++;
            }
        });
        recalculateKPI();
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Set Check-Out time (' + curTime + ') for ' + count + ' teachers',
            showConfirmButton: false,
            timer: 1800
        });
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

    function clearAllFields() {
        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            $('input[name="status_' + tid + '"][value="unmarked"]').prop('checked', true);
            onStatusChange(tid);
        });
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Cleared attendance entries to unmarked',
            showConfirmButton: false,
            timer: 1500
        });
    }

    function recalculateKPI() {
        var total = $('.att-row').length;
        var present = 0;
        var checkedOut = 0;
        var late = 0;
        var leave = 0;
        var absent = 0;
        var unmarked = 0;

        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var st = $('input[name="status_' + tid + '"]:checked').val();
            var outVal = $('#out_' + tid).val();

            if (st === 'present') {
                present++;
                if (outVal) checkedOut++;
            } else if (st === 'late') {
                late++;
                if (outVal) checkedOut++;
            } else if (st === 'leave') {
                leave++;
            } else if (st === 'absent') {
                absent++;
            } else {
                unmarked++;
            }
        });

        $('#kpi-total').text(total);
        $('#kpi-present').text(present);
        $('#kpi-out').text(checkedOut);
        $('#kpi-late').text(late);
        $('#kpi-absent').text(absent);
        $('#kpi-unmarked').text(unmarked);

        $('#sticky-summary-txt').text('Present: ' + present + ' | Checked Out: ' + checkedOut + ' | Late: ' + late + ' | Absent: ' + absent + ' | Unmarked: ' + unmarked);
    }

    function saveAllAttendance() {
        var records = [];
        var dateVal = $('#attendance-date').val();

        $('.att-row').each(function() {
            var tid = $(this).data('tid');
            var status = $('input[name="status_' + tid + '"]:checked').val() || 'unmarked';
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
                    }).then(function() {
                        location.reload();
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
