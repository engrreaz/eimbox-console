<?php
require_once 'header.php';

// প্যারামিটার
$tid   = trim($_GET['tid'] ?? $_GET['id'] ?? '');
$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

// শিক্ষকের তালিকা ফেচ করা (ড্রপডাউনের জন্য)
$teachers_list = [];
$tq = $conn->prepare("SELECT tid, tname, position FROM teacher WHERE sccode = ? ORDER BY sl ASC");
$tq->bind_param("i", $sccode);
$tq->execute();
$tr = $tq->get_result();
while ($row = $tr->fetch_assoc()) {
    $teachers_list[] = $row;
}

// যদি কোনো tid নির্দিষ্ট না থাকে, তবে প্রথম শিক্ষক সিলেক্ট করো
if (empty($tid) && !empty($teachers_list)) {
    $tid = $teachers_list[0]['tid'];
}

// নির্বাচিত শিক্ষকের ডাটা
$teacher = null;
if (!empty($tid)) {
    $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
    $stmt->bind_param("si", $tid, $sccode);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
}

if (!$teacher) {
    echo "<div class='container-xxl p-4'><div class='alert alert-danger'>Teacher not found!</div></div>";
    require_once 'footer.php';
    exit;
}

// উইকেন্ড নির্ধারণ
$weekendDays = [];
foreach ($sett as $row) {
    if (isset($row['setting_title']) && $row['setting_title'] == 'Weekends') {
        $weekendDays = array_map('trim', explode(',', trim($row['settings_value'])));
    }
}
if (empty($weekendDays)) {
    $weekendDays = ['Friday'];
}

// তারিখের রেঞ্জ নির্ধারণ (নির্দিষ্ট মাস অথবা পুরো বছর)
$is_full_year = ($month === 'all');
if ($is_full_year) {
    $start_date = "$year-01-01";
    $end_date   = "$year-12-31";
} else {
    $month_num = str_pad(intval($month), 2, '0', STR_PAD_LEFT);
    $days_in_m = cal_days_in_month(CAL_GREGORIAN, (int)$month_num, (int)$year);
    $start_date = "$year-$month_num-01";
    $end_date   = "$year-$month_num-$days_in_m";
}

// ১. শিক্ষক হাজিরা ডাটা
$attnd_map = [];
$att_stmt = $conn->prepare("SELECT * FROM teacherattnd 
                            WHERE sccode = ? AND tid = ? AND adate BETWEEN ? AND ? 
                            ORDER BY adate ASC");
$att_stmt->bind_param("isss", $sccode, $tid, $start_date, $end_date);
$att_stmt->execute();
$att_res = $att_stmt->get_result();
while ($arow = $att_res->fetch_assoc()) {
    $attnd_map[$arow['adate']] = $arow;
}

// ২. ক্যালেন্ডার হলিডে
$holidays = [];
$cq = $conn->prepare("SELECT date, dateto, category, work FROM calendar 
                      WHERE (sccode=? OR sccode=0) 
                      AND (date BETWEEN ? AND ? OR dateto BETWEEN ? AND ?)");
$cq->bind_param("issss", $sccode, $start_date, $end_date, $start_date, $end_date);
$cq->execute();
$cr = $cq->get_result();
while ($r = $cr->fetch_assoc()) {
    if (strtolower(trim($r['work'])) == '0') {
        $to = $r['dateto'] ?: $r['date'];
        $cur_d = $r['date'];
        while ($cur_d <= $to) {
            if ($cur_d >= $start_date && $cur_d <= $end_date) {
                $holidays[$cur_d] = $r['category'] ?: 'Holiday';
            }
            $cur_d = date("Y-m-d", strtotime("+1 day", strtotime($cur_d)));
        }
    }
}

// ৩. লিভ অ্যাপ্লিকেশন
$leaves = [];
$lq = $conn->prepare("SELECT date_from, date_to, leave_type, leave_reason FROM teacher_leave_app 
                      WHERE sccode=? AND tid=? AND status=1 
                      AND date_from <= ? AND date_to >= ?");
$lq->bind_param("isss", $sccode, $tid, $end_date, $start_date);
$lq->execute();
$lr = $lq->get_result();
while ($r = $lr->fetch_assoc()) {
    $cur_d = $r['date_from'];
    while ($cur_d <= $r['date_to']) {
        if ($cur_d >= $start_date && $cur_d <= $end_date) {
            $leaves[$cur_d] = $r['leave_type'] ?: 'Approved Leave';
        }
        $cur_d = date("Y-m-d", strtotime("+1 day", strtotime($cur_d)));
    }
}

// সামারি কাউন্ট
$stat_total_days   = 0;
$stat_working_days = 0;
$stat_present      = 0;
$stat_late         = 0;
$stat_absent       = 0;
$stat_leave        = 0;
$stat_holidays     = 0;
$stat_weekends     = 0;

$today = date('Y-m-d');
$cur_loop = $start_date;
$day_records = [];

while ($cur_loop <= $end_date) {
    $stat_total_days++;
    $day_name   = date('l', strtotime($cur_loop));
    $is_weekend = in_array($day_name, $weekendDays);
    $holiday    = $holidays[$cur_loop] ?? null;
    $leave      = $leaves[$cur_loop] ?? null;
    $att        = $attnd_map[$cur_loop] ?? null;

    $status = 'Future';
    $status_label = 'Upcoming';
    $badge_class = 'bg-label-secondary';

    if ($holiday) {
        $stat_holidays++;
        $status = 'Holiday';
        $status_label = 'Holiday (' . $holiday . ')';
        $badge_class = 'bg-label-secondary';
    } elseif ($is_weekend) {
        $stat_weekends++;
        $status = 'Weekend';
        $status_label = 'Weekend';
        $badge_class = 'bg-label-dark';
    } elseif ($leave) {
        $stat_leave++;
        $status = 'Leave';
        $status_label = 'Leave (' . $leave . ')';
        $badge_class = 'bg-label-info';
    } elseif ($att) {
        $stat_working_days++;
        $stat_present++;
        
        $is_late = (isset($att['statusin']) && strtolower($att['statusin']) === 'late') || (!empty($att['late_minutes']) && $att['late_minutes'] > 0);
        if ($is_late) {
            $stat_late++;
            $status = 'Late';
            $status_label = 'Late Present';
            $badge_class = 'bg-label-warning';
        } else {
            $status = 'Present';
            $status_label = 'Present';
            $badge_class = 'bg-label-success';
        }
    } elseif ($cur_loop <= $today) {
        $stat_working_days++;
        $stat_absent++;
        $status = 'Absent';
        $status_label = 'Absent';
        $badge_class = 'bg-label-danger';
    }

    $day_records[] = [
        'date'         => $cur_loop,
        'day'          => $day_name,
        'status'       => $status,
        'status_label' => $status_label,
        'badge'        => $badge_class,
        'att'          => $att
    ];

    $cur_loop = date('Y-m-d', strtotime('+1 day', strtotime($cur_loop)));
}

$att_pct = ($stat_working_days > 0) ? round(($stat_present / $stat_working_days) * 100, 1) : 0;
?>

<style>
    @media print {
        @page { size: A4 portrait; margin: 10mm; }
        body { background: #fff !important; }
        .no-print, .layout-navbar, .layout-menu, .content-footer, nav, .footer { display: none !important; }
        .container-xxl { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        .card { border: none !important; box-shadow: none !important; }
        .d-print-block { display: block !important; }
        table { width: 100% !important; font-size: 10px !important; border-collapse: collapse !important; }
        th, td { border: 1px solid #333 !important; padding: 4px !important; }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Controls (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Teacher /</span> Attendance Register</h4>
            <p class="text-muted small mb-0">Individual teacher attendance, punctuality, and leaves report</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
            <a href="teacher-attendance-report.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-sm btn-outline-info">
                <i class="bi bi-grid-3x3 me-1"></i> All Teachers Matrix
            </a>
            <a href="teachers-list.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Filter Card (No Print) -->
    <div class="card mb-4 shadow-none border no-print">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Select Teacher</label>
                    <select name="tid" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($teachers_list as $titem): ?>
                            <option value="<?= htmlspecialchars($titem['tid']) ?>" <?= ($tid == $titem['tid']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($titem['tname']) ?> (ID: <?= htmlspecialchars($titem['tid']) ?> - <?= htmlspecialchars($titem['position']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="all" <?= $month === 'all' ? 'selected' : '' ?>>Full Year (All Months)</option>
                        <?php for ($m = 1; $m <= 12; $m++):
                            $m_str = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                            <option value="<?= $m_str ?>" <?= ($month === $m_str) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="attendance-view.php?tid=<?= urlencode($tid) ?>&month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-sm btn-outline-secondary">Current Month</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Printable Report Container -->
    <div class="d-print-block">
        <!-- Letterhead (Print only) -->
        <div class="d-none d-print-block mb-3">
            <?php include __DIR__ . '/templete/letter-head-01.php'; ?>
            <div class="text-center mt-2">
                <h5 class="mb-1 fw-bold text-dark" style="font-size: 14px;">Individual Teacher Attendance Register</h5>
                <p class="mb-1 text-muted" style="font-size: 12px;">
                    Period: <?= $is_full_year ? "Full Year $year" : date('F, Y', mktime(0, 0, 0, (int)$month, 1, (int)$year)) ?>
                </p>
            </div>
            <div style="border-top: 2px solid #000; margin-top: 5px; margin-bottom: 10px;"></div>
        </div>

        <!-- Teacher Header Profile Box -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <img src="<?= teacher_profile_image_path($teacher['tid']) ?>"
                             class="rounded-circle me-3 border"
                             style="width: 55px; height: 55px; object-fit: cover;">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($teacher['tname']) ?></h5>
                            <div class="small text-muted">
                                ID: <strong class="text-primary font-monospace"><?= htmlspecialchars($teacher['tid']) ?></strong> &bull;
                                <?= htmlspecialchars($teacher['position']) ?> (<?= htmlspecialchars($teacher['slots']) ?>) &bull;
                                Duty Time: <span class="font-monospace text-dark fw-bold"><?= date('h:i A', strtotime($teacher['curin'] ?? '09:00:00')) ?> - <?= date('h:i A', strtotime($teacher['curout'] ?? '16:00:00')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-success fs-6 py-2 px-3">
                            <i class="bi bi-percent me-1"></i> Attendance: <?= $att_pct ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Working Days</small>
                    <h4 class="fw-bold mb-0 text-dark"><?= $stat_working_days ?></h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Present</small>
                    <h4 class="fw-bold mb-0 text-success"><?= $stat_present ?></h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Late Entry</small>
                    <h4 class="fw-bold mb-0 text-warning"><?= $stat_late ?></h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Absent</small>
                    <h4 class="fw-bold mb-0 text-danger"><?= $stat_absent ?></h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Approved Leave</small>
                    <h4 class="fw-bold mb-0 text-info"><?= $stat_leave ?></h4>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card border-0 shadow-sm text-center p-3">
                    <small class="text-muted d-block">Holidays/Weekend</small>
                    <h4 class="fw-bold mb-0 text-secondary"><?= $stat_holidays + $stat_weekends ?></h4>
                </div>
            </div>
        </div>

        <!-- Attendance Day-by-Day Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0 text-dark">
                    <i class="bi bi-calendar3 me-1"></i> Daily Attendance Details (<?= $is_full_year ? "Year $year" : date('F, Y', mktime(0, 0, 0, (int)$month, 1, (int)$year)) ?>)
                </h6>
                <span class="small text-muted"><?= count($day_records) ?> Days recorded</span>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th class="text-center">Status</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th class="text-center">Late (Min)</th>
                            <th class="text-center">Duty Duration</th>
                            <th class="text-center">Device / Method</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php 
                        $sl = 1;
                        foreach ($day_records as $rec): 
                            $att = $rec['att'];
                            $realin  = !empty($att['realin']) ? date('h:i:s A', strtotime($att['realin'])) : '—';
                            $realout = !empty($att['realout']) ? date('h:i:s A', strtotime($att['realout'])) : '—';
                            $duty    = !empty($att['dutytime']) ? $att['dutytime'] : '—';
                            $device  = !empty($att['detectin']) ? $att['detectin'] : (!empty($att) ? 'Manual' : '—');
                            $late_m  = !empty($att['late_minutes']) ? $att['late_minutes'] : 0;
                            ?>
                            <tr class="<?= ($rec['status'] == 'Weekend' || $rec['status'] == 'Holiday') ? 'table-light' : '' ?>">
                                <td class="text-center text-muted"><?= $sl++ ?></td>
                                <td class="font-monospace fw-semibold"><?= date('d M, Y', strtotime($rec['date'])) ?></td>
                                <td><?= $rec['day'] ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $rec['badge'] ?> px-2 py-1"><?= $rec['status_label'] ?></span>
                                </td>
                                <td class="font-monospace text-success fw-medium"><?= $realin ?></td>
                                <td class="font-monospace text-primary fw-medium"><?= $realout ?></td>
                                <td class="text-center font-monospace <?= $late_m > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= $late_m > 0 ? "+{$late_m}m" : '—' ?>
                                </td>
                                <td class="text-center font-monospace"><?= $duty ?></td>
                                <td class="text-center">
                                    <?php if (!empty($att)): ?>
                                        <span class="badge bg-label-secondary font-monospace"><?= htmlspecialchars($device) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signature Area for Print -->
        <div class="d-none d-print-block mt-5 pt-4">
            <table style="width: 100%; border: none !important;">
                <tr>
                    <td style="width: 33%; border: none !important; text-align: center;">
                        <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 30px;">
                            Teacher's Signature
                        </div>
                    </td>
                    <td style="width: 34%; border: none !important;"></td>
                    <td style="width: 33%; border: none !important; text-align: center;">
                        <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 30px;">
                            Headmaster / Principal
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
</body>
</html>
