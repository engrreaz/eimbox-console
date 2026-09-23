<?php
ob_start();
require_once 'core/init.php';

// প্যারামিটার
$cls = $_COOKIE['chain-class'] ?? $_GET['cls'] ?? '';
$sec = $_COOKIE['chain-section'] ?? $_GET['sec'] ?? '';
$year = $_COOKIE['chain-session'] ?? $_GET['session'] ?? date('Y');
$slot = $_COOKIE['chain-slot'] ?? $_GET['slot'] ?? 'School';

$days = [
    1 => 'Saturday',
    2 => 'Sunday',
    3 => 'Monday',
    4 => 'Tuesday',
    5 => 'Wednesday',
    6 => 'Thursday',
    7 => 'Friday'
];

// Weekends resolution from settings table (setting_title = 'weekends', dot-separated)
$weekendDays = [];
$wStmt = $conn->query("SELECT settings_value FROM settings WHERE (sccode = '$sccode' OR sccode = 0) AND LOWER(setting_title) = 'weekends' ORDER BY (sccode = '$sccode') DESC LIMIT 1");
if ($wStmt && $wStmt->num_rows > 0) {
    $wRow = $wStmt->fetch_assoc();
    $rawVal = trim($wRow['settings_value'] ?? '');
    if (!empty($rawVal)) {
        $weekendDays = array_values(array_filter(array_map('trim', preg_split('/[\.,]+/', $rawVal))));
    }
}
if (empty($weekendDays) && isset($sett) && is_array($sett)) {
    foreach ($sett as $row) {
        if (strtolower($row['setting_title'] ?? '') == 'weekends') {
            $weekendDays = array_values(array_filter(array_map('trim', preg_split('/[\.,]+/', trim($row['settings_value'] ?? '')))));
        }
    }
}
if (empty($weekendDays)) {
    $weekendDays = ['Friday'];
}

// Current day calculation
$jd = intval(date('w')); // 0 (Sun) to 6 (Sat)
$today_wday = ($jd === 6) ? 1 : ($jd + 2); // 1 (Sat) to 7 (Fri)

// ==========================================
// AJAX ACTION HANDLER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $ajax_action = $_POST['ajax_action'];

    // 1. SAVE CELL
    if ($ajax_action === 'save_cell') {
        $rid     = isset($_POST['rid']) ? intval($_POST['rid']) : 0;
        $period  = intval($_POST['period'] ?? 0);
        $wday    = intval($_POST['wday'] ?? 0);
        $subcode = intval($_POST['subcode'] ?? 0);
        $tid     = trim($_POST['tid'] ?? '0');
        $c_cls   = trim($_POST['cls'] ?? $cls);
        $c_sec   = trim($_POST['sec'] ?? $sec);
        $c_year  = trim($_POST['year'] ?? $year);
        $c_slot  = trim($_POST['slot'] ?? $slot);
        $entryby = $usr ?? 'admin';
        $dayName = $days[$wday] ?? 'Saturday';

        if (!$period || !$wday || !$c_cls || !$c_sec) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (in_array($dayName, $weekendDays)) {
            echo json_encode(['status' => 'error', 'message' => "Cannot assign periods on $dayName because it is configured as a weekend."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Clash check for teacher
        $clashInfo = null;
        if (!empty($tid) && $tid != '0') {
            $cstmt = $conn->prepare("SELECT r.classname, r.sectionname, t.tname 
                                    FROM clsroutine r 
                                    LEFT JOIN teacher t ON (t.tid = r.tid AND t.sccode = r.sccode)
                                    WHERE r.sccode = ? AND r.sessionyear = ? AND r.wday = ? AND r.period = ? AND r.tid = ? 
                                    AND NOT (r.classname = ? AND r.sectionname = ?)");
            $cstmt->bind_param("isisiss", $sccode, $c_year, $wday, $period, $tid, $c_cls, $c_sec);
            $cstmt->execute();
            $cres = $cstmt->get_result();
            if ($crow = $cres->fetch_assoc()) {
                $clashTeacher = $crow['tname'] ?: "Teacher #$tid";
                $clashInfo = "$clashTeacher is already scheduled in Class {$crow['classname']} ({$crow['sectionname']}) on $dayName Period $period.";
            }
            $cstmt->close();
        }

        if ($rid > 0) {
            $stmt = $conn->prepare("UPDATE clsroutine SET period=?, wday=?, day=?, subcode=?, tid=?, modifieddate=NOW() WHERE id=? AND sccode=?");
            $stmt->bind_param("iisisii", $period, $wday, $dayName, $subcode, $tid, $rid, $sccode);
            $stmt->execute();
            $stmt->close();
            $targetId = $rid;
        } else {
            // Remove previous entry on same cell if any
            $del = $conn->prepare("DELETE FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND period=? AND wday=?");
            $del->bind_param("isssii", $sccode, $c_year, $c_cls, $c_sec, $period, $wday);
            $del->execute();
            $del->close();

            $stmt = $conn->prepare("INSERT INTO clsroutine (sccode, sessionyear, classname, sectionname, period, wday, day, subcode, tid, entryby, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssiissis", $sccode, $c_year, $c_cls, $c_sec, $period, $wday, $dayName, $subcode, $tid, $entryby);
            $stmt->execute();
            $targetId = $conn->insert_id;
            $stmt->close();

            if (!$targetId) {
                $chk = $conn->prepare("SELECT id FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND period=? AND wday=? ORDER BY id DESC LIMIT 1");
                $chk->bind_param("isssii", $sccode, $c_year, $c_cls, $c_sec, $period, $wday);
                $chk->execute();
                $chkRes = $chk->get_result();
                if ($chkRow = $chkRes->fetch_assoc()) {
                    $targetId = intval($chkRow['id']);
                }
                $chk->close();
            }
        }

        // Fetch subject and teacher names for live update
        $subname = "Subject #$subcode";
        $subq = $conn->query("SELECT subject, subben FROM subjects WHERE (sccode='$sccode' OR sccode=0) AND subcode='$subcode' AND (sccategory='$sctype' OR sccategory='' OR sccategory IS NULL) ORDER BY (sccode='$sccode') DESC, id DESC LIMIT 1");
        if ($subq && $subq->num_rows > 0) {
            $srow = $subq->fetch_assoc();
            $subname = $srow['subject'] ?: ($srow['subben'] ?: "Subject #$subcode");
        }

        $tname = "Not Assigned";
        if (!empty($tid) && $tid != '0') {
            $tq = $conn->query("SELECT tname FROM teacher WHERE sccode='$sccode' AND tid='$tid' LIMIT 1");
            if ($tq && $tq->num_rows > 0) {
                $tname = $tq->fetch_assoc()['tname'] ?: "Teacher #$tid";
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Routine period assigned successfully.' . ($clashInfo ? " Note: $clashInfo" : ""),
            'id' => $targetId,
            'period' => $period,
            'wday' => $wday,
            'subcode' => $subcode,
            'subname' => $subname,
            'tid' => $tid,
            'tname' => $tname,
            'clash' => $clashInfo ? true : false,
            'clash_message' => $clashInfo
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. DELETE CELL
    if ($ajax_action === 'delete_cell') {
        $rid    = intval($_POST['rid'] ?? 0);
        $period = intval($_POST['period'] ?? 0);
        $wday   = intval($_POST['wday'] ?? 0);
        $c_cls  = trim($_POST['cls'] ?? $cls);
        $c_sec  = trim($_POST['sec'] ?? $sec);
        $c_year = trim($_POST['year'] ?? $year);

        if ($rid > 0) {
            $stmt = $conn->prepare("DELETE FROM clsroutine WHERE id=? AND sccode=?");
            $stmt->bind_param("ii", $rid, $sccode);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND period=? AND wday=?");
            $stmt->bind_param("isssii", $sccode, $c_year, $c_cls, $c_sec, $period, $wday);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['status' => 'success', 'message' => 'Period assignment removed.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. COPY TO NEXT DAY / SPECIFIC DAY
    if ($ajax_action === 'copy_day') {
        $src_wday = intval($_POST['src_wday'] ?? 0);
        $tgt_wday = intval($_POST['tgt_wday'] ?? 0);
        $c_cls    = trim($_POST['cls'] ?? $cls);
        $c_sec    = trim($_POST['sec'] ?? $sec);
        $c_year   = trim($_POST['year'] ?? $year);
        $entryby  = $usr ?? 'admin';
        $tgtDayName = $days[$tgt_wday] ?? 'Sunday';

        if (!$src_wday || !$tgt_wday || !$c_cls || !$c_sec) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters for day copy.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $src_q = $conn->prepare("SELECT period, subcode, tid FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND wday=?");
        $src_q->bind_param("isssi", $sccode, $c_year, $c_cls, $c_sec, $src_wday);
        $src_q->execute();
        $src_res = $src_q->get_result();

        $rows = [];
        while ($r = $src_res->fetch_assoc()) {
            $rows[] = $r;
        }
        $src_q->close();

        if (empty($rows)) {
            echo json_encode(['status' => 'error', 'message' => "Source day ({$days[$src_wday]}) has no routine entries to copy."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Clear target day
        $del = $conn->prepare("DELETE FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND wday=?");
        $del->bind_param("isssi", $sccode, $c_year, $c_cls, $c_sec, $tgt_wday);
        $del->execute();
        $del->close();

        // Insert into target day
        $ins = $conn->prepare("INSERT INTO clsroutine (sccode, sessionyear, classname, sectionname, period, wday, day, subcode, tid, entryby, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $copied = 0;
        foreach ($rows as $row) {
            $ins->bind_param("isssiissis", $sccode, $c_year, $c_cls, $c_sec, $row['period'], $tgt_wday, $tgtDayName, $row['subcode'], $row['tid'], $entryby);
            $ins->execute();
            $copied++;
        }
        $ins->close();

        echo json_encode([
            'status' => 'success',
            'message' => "Successfully copied $copied period(s) from {$days[$src_wday]} to $tgtDayName.",
            'copied_count' => $copied
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. COPY TO ALL WORKING DAYS
    if ($ajax_action === 'copy_to_all_days') {
        $src_wday = intval($_POST['src_wday'] ?? 0);
        $c_cls    = trim($_POST['cls'] ?? $cls);
        $c_sec    = trim($_POST['sec'] ?? $sec);
        $c_year   = trim($_POST['year'] ?? $year);
        $entryby  = $usr ?? 'admin';

        if (!$src_wday || !$c_cls || !$c_sec) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $src_q = $conn->prepare("SELECT period, subcode, tid FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND wday=?");
        $src_q->bind_param("isssi", $sccode, $c_year, $c_cls, $c_sec, $src_wday);
        $src_q->execute();
        $src_res = $src_q->get_result();

        $rows = [];
        while ($r = $src_res->fetch_assoc()) {
            $rows[] = $r;
        }
        $src_q->close();

        if (empty($rows)) {
            echo json_encode(['status' => 'error', 'message' => "Source day ({$days[$src_wday]}) has no routine entries to duplicate."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $totalCopied = 0;
        $targetNames = [];
        foreach ($days as $wIndex => $wName) {
            if ($wIndex === $src_wday) continue;
            if (in_array($wName, $weekendDays)) continue;

            $targetNames[] = $wName;

            $del = $conn->prepare("DELETE FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND wday=?");
            $del->bind_param("isssi", $sccode, $c_year, $c_cls, $c_sec, $wIndex);
            $del->execute();
            $del->close();

            $ins = $conn->prepare("INSERT INTO clsroutine (sccode, sessionyear, classname, sectionname, period, wday, day, subcode, tid, entryby, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            foreach ($rows as $row) {
                $ins->bind_param("isssiissis", $sccode, $c_year, $c_cls, $c_sec, $row['period'], $wIndex, $wName, $row['subcode'], $row['tid'], $entryby);
                $ins->execute();
                $totalCopied++;
            }
            $ins->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => "Duplicated {$days[$src_wday]}'s routine to " . implode(', ', $targetNames) . " ($totalCopied period assignments).",
            'total_copied' => $totalCopied
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5. CLEAR DAY
    if ($ajax_action === 'clear_day') {
        $wday   = intval($_POST['wday'] ?? 0);
        $c_cls  = trim($_POST['cls'] ?? $cls);
        $c_sec  = trim($_POST['sec'] ?? $sec);
        $c_year = trim($_POST['year'] ?? $year);

        $del = $conn->prepare("DELETE FROM clsroutine WHERE sccode=? AND sessionyear=? AND classname=? AND sectionname=? AND wday=?");
        $del->bind_param("isssi", $sccode, $c_year, $c_cls, $c_sec, $wday);
        $del->execute();
        $affected = $del->affected_rows;
        $del->close();

        echo json_encode(['status' => 'success', 'message' => "Cleared $affected period(s) for {$days[$wday]}."], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

require_once 'header.php';

// ==========================================
// DATA FETCHING FOR GRID VIEW
// ==========================================

// 1. Periods list from classschedule
$periods_list = [];
$ps_sql = "SELECT period, timestart, timeend, duration FROM classschedule 
           WHERE sccode = '$sccode' AND sessionyear = '$year' AND slots = '$slot' 
           ORDER BY period ASC";
$ps_res = $conn->query($ps_sql);
if ($ps_res) {
    while ($ps_row = $ps_res->fetch_assoc()) {
        $periods_list[$ps_row['period']] = $ps_row;
    }
}

// 2. Routine Matrix
$routine = [];
if ($cls && $sec) {
    $res = $conn->query("SELECT r.*, t.tname, s.subject as subname, s.subben as subname_bn, s.subshname as shortname 
                        FROM clsroutine r 
                        LEFT JOIN teacher t ON (r.tid = t.tid AND t.sccode = r.sccode) 
                        LEFT JOIN subjects s ON (r.subcode = s.subcode AND (s.sccode = r.sccode OR s.sccode = 0) AND (s.sccategory = '$sctype' OR s.sccategory = '' OR s.sccategory IS NULL)) 
                        WHERE r.sccode='$sccode' AND r.classname='$cls' AND r.sectionname='$sec' AND r.sessionyear='$year'
                        ORDER BY r.wday ASC, r.period ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $routine[$row['wday']][$row['period']] = $row;
        }
    }
}

// 3. Subjects list for Class/Section (subsetup join with default teacher mapping and sccategory filter)
$subjectOptions = [];
$subjectTeacherMap = [];
if ($cls && $sec) {
    $sql_subs = "SELECT ss.subject as subcode, ss.tid, s.subject as subname, s.subshname as shortname, t.tname as default_teacher 
                 FROM subsetup ss 
                 LEFT JOIN subjects s ON (s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = '$sctype' OR s.sccategory = '' OR s.sccategory IS NULL))
                 LEFT JOIN teacher t ON (t.tid = ss.tid AND t.sccode = ss.sccode)
                 WHERE ss.sccode = '$sccode' 
                   AND ss.sessionyear = '$year' 
                   AND ss.classname = '$cls' 
                   AND (ss.sectionname = '$sec' OR ss.sectionname = 'All' OR ss.sectionname = '' OR ss.sectionname IS NULL)
                   AND ss.slot = '$slot'
                 ORDER BY ss.slno, s.subject";
    $subs_res = $conn->query($sql_subs);
    if ($subs_res && $subs_res->num_rows > 0) {
        $seenSubs = [];
        while ($s = $subs_res->fetch_assoc()) {
            $sc = $s['subcode'];
            if (isset($seenSubs[$sc])) continue;
            $seenSubs[$sc] = true;
            $subjectOptions[] = $s;
            $subjectTeacherMap[$sc] = $s['tid'] ? (string)$s['tid'] : '';
        }
    }
}
if (empty($subjectOptions)) {
    // Fallback if subsetup is not set yet
    $fallback_res = $conn->query("SELECT subcode, subject as subname, subshname as shortname FROM subjects WHERE (sccode='$sccode' OR sccode=0) AND (sccategory='$sctype' OR sccategory='' OR sccategory IS NULL) ORDER BY (sccode='$sccode') DESC, subcode ASC");
    if ($fallback_res) {
        while ($s = $fallback_res->fetch_assoc()) {
            $s['tid'] = '';
            $s['default_teacher'] = '';
            $subjectOptions[] = $s;
            $subjectTeacherMap[$s['subcode']] = '';
        }
    }
}

// 4. Teachers list
$teachersList = [];
$ts = $conn->query("SELECT tid, tname, position FROM teacher WHERE sccode='$sccode' AND (status='Active' OR status='1' OR status='' OR status IS NULL) ORDER BY tname ASC");
if ($ts) {
    while ($t = $ts->fetch_assoc()) {
        $teachersList[] = $t;
    }
}
?>

<style>
    .routine-grid-table th, .routine-grid-table td {
        vertical-align: middle;
    }
    .routine-cell {
        min-width: 140px;
        min-height: 85px;
        padding: 6px !important;
        position: relative;
        background: #fff;
        transition: all 0.2s ease;
    }
    .routine-cell:hover {
        background-color: #f8f9fa;
    }
    .routine-card {
        border-radius: 8px;
        padding: 8px 10px;
        border-left: 4px solid #696cff;
        background: #f4f5fb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        cursor: pointer;
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .routine-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(105, 108, 255, 0.15);
        background: #eef0fc;
    }
    .routine-empty-btn {
        border: 1px dashed #c4c7d0;
        border-radius: 8px;
        color: #8592a3;
        font-size: 11px;
        padding: 16px 8px;
        text-align: center;
        cursor: pointer;
        background: transparent;
        transition: all 0.2s ease;
    }
    .routine-empty-btn:hover {
        border-color: #696cff;
        color: #696cff;
        background: rgba(105, 108, 255, 0.05);
    }
    .day-row-weekend {
        background-color: #fcfcfd !important;
    }
    .routine-cell-weekend {
        background-color: #f8f9fa !important;
        cursor: not-allowed !important;
    }
    .weekend-disabled-placeholder {
        border: 1px dashed #d9dee3;
        border-radius: 8px;
        color: #a1acb8;
        font-size: 11px;
        padding: 14px 8px;
        text-align: center;
        background: rgba(0, 0, 0, 0.02);
        user-select: none;
        cursor: not-allowed;
    }
    .day-row-today {
        background-color: #fff9ea !important;
    }
    .day-row-today .day-title-cell {
        border-left: 4px solid #ffab00;
    }
    @media print {
        .navbar, .layout-navbar, .footer, .btn, .dropdown, #slot-tree-container, .slot-chain-bar, .no-print {
            display: none !important;
        }
        .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .routine-cell {
            border: 1px solid #ccc !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- ================= FILTER SELECTION BAR ================= -->
    <?php
    $chain_param = '-c 12 -t Choose Values -u -r -b View Routine ';
    include 'components/slot-tree-ui.php';
    ?>

    <?php if (!$cls || !$sec): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar3 text-primary mb-3" style="font-size: 48px;"></i>
                <h5 class="fw-bold">Select Academic Class & Section</h5>
                <p class="text-muted mb-0">Please choose Slot, Session, Class, and Section from the toolbar above to manage the class routine timetable.</p>
            </div>
        </div>
    <?php elseif (empty($periods_list)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-clock-history text-warning mb-3" style="font-size: 48px;"></i>
                <h5 class="fw-bold">No Periods Configured in Class Schedule</h5>
                <p class="text-muted mb-3">You need to set up periods (Start Time & End Time) in Class Schedule for <strong><?= htmlspecialchars($slot) ?> (<?= htmlspecialchars($year) ?>)</strong> first.</p>
                <a href="class-schedule.php" class="btn btn-primary">
                    <i class="bi bi-clock me-1"></i> Configure Class Schedule
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- ================= TIMETABLE HEADER ================= -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-md bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-grid-3x3-gap fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Class Routine — <?= htmlspecialchars($cls) ?> (<?= htmlspecialchars($sec) ?>)</h5>
                        <div class="text-muted small">
                            <span class="badge bg-label-info me-1"><?= htmlspecialchars($slot) ?></span>
                            <span class="badge bg-label-secondary me-1">Session <?= htmlspecialchars($year) ?></span>
                            <span><?= count($periods_list) ?> Periods / Day</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Timetable
                    </button>
                    <a href="class-schedule.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-clock me-1"></i> Edit Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- ================= TIMETABLE GRID ================= -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-bordered align-middle routine-grid-table mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 140px;" class="py-3 bg-light">
                                <span class="fw-bold">Day / Period</span>
                            </th>
                            <?php foreach ($periods_list as $p_num => $p_info): 
                                $start_lbl = date('h:i A', strtotime($p_info['timestart']));
                                $end_lbl   = date('h:i A', strtotime($p_info['timeend']));
                            ?>
                                <th class="py-2" style="min-width: 150px;">
                                    <div class="fw-bold text-primary">Period <?= $p_num ?></div>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">
                                        <?= $start_lbl ?> - <?= $end_lbl ?>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                            <th style="width: 110px;" class="py-3 text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days as $wIndex => $wName): 
                            $is_weekend = in_array($wName, $weekendDays);
                            $is_today   = ($wIndex === $today_wday);
                            $row_class  = $is_today ? 'day-row-today' : ($is_weekend ? 'day-row-weekend' : '');

                            // Resolve next working day for one-click fill
                            $next_wday = ($wIndex % 7) + 1;
                            $search_cnt = 0;
                            while (in_array($days[$next_wday], $weekendDays) && $search_cnt < 7) {
                                $next_wday = ($next_wday % 7) + 1;
                                $search_cnt++;
                            }
                            $nextDayName = $days[$next_wday] ?? 'Next Working Day';
                        ?>
                            <tr class="<?= $row_class ?>" id="day-row-<?= $wIndex ?>">
                                <!-- Day Column -->
                                <td class="day-title-cell ps-3">
                                    <div class="fw-bold text-dark fs-6"><?= $wName ?></div>
                                    <div class="d-flex gap-1 mt-1">
                                        <?php if ($is_today): ?>
                                            <span class="badge bg-warning text-dark px-2" style="font-size: 10px;">Today</span>
                                        <?php endif; ?>
                                        <?php if ($is_weekend): ?>
                                            <span class="badge bg-label-danger px-2" style="font-size: 10px;">Weekend</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Period Cells -->
                                <?php foreach ($periods_list as $p_num => $p_info): 
                                    $cell = $routine[$wIndex][$p_num] ?? null;
                                    $cell_id = "cell-{$wIndex}-{$p_num}";
                                ?>
                                    <td class="routine-cell <?= $is_weekend ? 'routine-cell-weekend' : '' ?>" id="<?= $cell_id ?>">
                                        <?php if ($is_weekend): ?>
                                            <div class="weekend-disabled-placeholder" title="Weekend — Class setting disabled">
                                                <i class="bi bi-slash-circle me-1 text-muted"></i>
                                                <span class="d-none d-md-inline">Weekend</span>
                                            </div>
                                        <?php elseif ($cell): ?>
                                            <div class="routine-card" onclick='openCellModal(<?= $wIndex ?>, <?= $p_num ?>, <?= htmlspecialchars(json_encode($cell), ENT_QUOTES, 'UTF-8') ?>)' title="Click to edit Period <?= $p_num ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="fw-bold text-primary text-truncate" style="max-width: 110px;">
                                                        <?= htmlspecialchars($cell['subname'] ?: ("Subject #" . $cell['subcode'])) ?>
                                                    </div>
                                                    <span class="badge bg-label-secondary p-1" style="font-size: 9px;">P<?= $p_num ?></span>
                                                </div>
                                                <div class="text-secondary small mt-1 text-truncate" style="font-size: 11px;">
                                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($cell['tname'] ?: 'Not Assigned') ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="routine-empty-btn" onclick="openCellModal(<?= $wIndex ?>, <?= $p_num ?>)">
                                                <i class="bi bi-plus-circle me-1"></i>Assign
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Day Level Action Column -->
                                <td class="text-center no-print">
                                    <?php if ($is_weekend): ?>
                                        <span class="badge bg-label-secondary text-muted" style="font-size: 11px;">
                                            <i class="bi bi-slash-circle me-1"></i>Off Day
                                        </span>
                                    <?php else: ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-light rounded-circle dropdown-toggle hide-arrow shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-6"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item py-2 text-primary" href="javascript:void(0)" onclick="quickCopyNextDay(<?= $wIndex ?>, <?= $next_wday ?>)">
                                                        <i class="bi bi-arrow-right-circle text-primary me-2"></i> Copy to <?= $nextDayName ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-info" href="javascript:void(0)" onclick="copyToAllWorkingDays(<?= $wIndex ?>)">
                                                        <i class="bi bi-copy text-info me-2"></i> Duplicate to All Days
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="clearDayRoutine(<?= $wIndex ?>)">
                                                        <i class="bi bi-trash text-danger me-2"></i> Clear <?= $wName ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- ================= QUICK CELL ASSIGNMENT MODAL ================= -->
<div class="modal fade" id="cellModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="cellModalTitle">Assign Class Period</h5>
                    <span class="badge bg-label-primary" id="cellModalSubtitle"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_rid" value="0">
                <input type="hidden" id="modal_wday" value="0">
                <input type="hidden" id="modal_period" value="0">

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Subject <span class="text-danger">*</span></label>
                    <select id="modal_subcode" class="form-select form-select-sm" required>
                        <option value="">-- Choose Subject --</option>
                        <?php foreach ($subjectOptions as $sub): ?>
                            <option value="<?= $sub['subcode'] ?>">
                                <?= htmlspecialchars($sub['subname']) ?> (<?= $sub['subcode'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Assigned Teacher</label>
                    <select id="modal_tid" class="form-select form-select-sm">
                        <option value="0">-- None / Not Assigned --</option>
                        <?php foreach ($teachersList as $tch): ?>
                            <option value="<?= $tch['tid'] ?>">
                                <?= htmlspecialchars($tch['tname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="modalAlert" class="small"></div>
            </div>
            <div class="modal-footer border-top py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btnModalDelete" style="display:none;" onclick="deleteCellFromModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnModalSave" onclick="saveCellFromModal()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ================= JAVASCRIPT ================= -->
<script>
    const daysMap = <?= json_encode($days) ?>;
    const weekendDays = <?= json_encode($weekendDays) ?>;
    const subjectTeacherMap = <?= json_encode($subjectTeacherMap) ?>;
    const currentCls  = <?= json_encode($cls) ?>;
    const currentSec  = <?= json_encode($sec) ?>;
    const currentYear = <?= json_encode($year) ?>;
    const currentSlot = <?= json_encode($slot) ?>;

    let cellModalInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('cellModal');
        if (modalEl) {
            cellModalInstance = new bootstrap.Modal(modalEl);
        }

        // Auto-select Teacher when Subject is changed
        const subSelect = document.getElementById('modal_subcode');
        if (subSelect) {
            subSelect.addEventListener('change', function() {
                const subcode = this.value;
                if (subcode && subjectTeacherMap[subcode] !== undefined) {
                    const defaultTid = subjectTeacherMap[subcode];
                    if (defaultTid) {
                        $('#modal_tid').val(defaultTid);
                    }
                }
            });
        }
    });

    function chainBtnFunc() {
        window.location.reload();
    }

    // Open Modal for Add or Edit
    function openCellModal(wday, period, data = null) {
        const dayName = daysMap[wday] || 'Day ' + wday;
        if (weekendDays && weekendDays.includes(dayName)) {
            Swal.fire({
                icon: 'info',
                title: 'Weekend',
                text: `${dayName} is configured as a weekend. Class periods cannot be scheduled on weekends.`
            });
            return;
        }

        document.getElementById('modal_wday').value   = wday;
        document.getElementById('modal_period').value = period;
        document.getElementById('modalAlert').innerHTML = '';

        document.getElementById('cellModalSubtitle').innerText = `${dayName} — Period ${period}`;

        const btnDelete = document.getElementById('btnModalDelete');

        if (data && data.id) {
            document.getElementById('cellModalTitle').innerText = 'Edit Period Assignment';
            document.getElementById('modal_rid').value     = data.id;
            document.getElementById('modal_subcode').value = data.subcode || '';
            document.getElementById('modal_tid').value     = data.tid || 0;
            btnDelete.style.display = 'inline-block';
        } else {
            document.getElementById('cellModalTitle').innerText = 'Assign Class Period';
            document.getElementById('modal_rid').value     = 0;
            document.getElementById('modal_subcode').value = '';
            document.getElementById('modal_tid').value     = 0;
            btnDelete.style.display = 'none';
        }

        cellModalInstance?.show();
    }

    // Save Cell via AJAX
    function saveCellFromModal() {
        const rid     = $('#modal_rid').val();
        const wday    = $('#modal_wday').val();
        const period  = $('#modal_period').val();
        const subcode = $('#modal_subcode').val();
        const tid     = $('#modal_tid').val();

        if (!subcode) {
            Swal.fire({
                icon: 'warning',
                title: 'Subject Required',
                text: 'Please select a subject to assign.'
            });
            return;
        }

        const btnSave = document.getElementById('btnModalSave');
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        $.post('class-routine.php', {
            ajax_action: 'save_cell',
            rid: rid,
            wday: wday,
            period: period,
            subcode: subcode,
            tid: tid,
            cls: currentCls,
            sec: currentSec,
            year: currentYear,
            slot: currentSlot
        }, function(res) {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';

            if (res.status === 'success') {
                cellModalInstance?.hide();

                // Live Update Cell Content
                const cellId = `cell-${wday}-${period}`;
                const cellEl = document.getElementById(cellId);
                if (cellEl) {
                    const cellData = {
                        id: res.id,
                        wday: wday,
                        period: period,
                        subcode: res.subcode,
                        subname: res.subname,
                        tid: res.tid,
                        tname: res.tname
                    };
                    const jsonStr = JSON.stringify(cellData).replace(/'/g, "&apos;");
                    cellEl.innerHTML = `
                        <div class="routine-card" onclick='openCellModal(${wday}, ${period}, ${jsonStr})' title="Click to edit Period ${period}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-bold text-primary text-truncate" style="max-width: 110px;">
                                    ${res.subname}
                                </div>
                                <span class="badge bg-label-secondary p-1" style="font-size: 9px;">P${period}</span>
                            </div>
                            <div class="text-secondary small mt-1 text-truncate" style="font-size: 11px;">
                                <i class="bi bi-person me-1"></i>${res.tname}
                            </div>
                        </div>
                    `;
                }

                if (res.clash) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Saved with Teacher Clash',
                        text: res.clash_message
                    });
                } else {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Period assigned successfully'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    text: res.message || 'Could not save routine period.'
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error("AJAX Save Cell Error:", status, error, xhr.responseText);
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save';
            if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed && parsed.status === 'success') {
                        cellModalInstance?.hide();
                        location.reload();
                        return;
                    }
                } catch(e) {}
            }
            Swal.fire({ 
                icon: 'error', 
                title: 'Network Error', 
                text: 'Server communication failed.' 
            });
        });
    }

    // Delete Cell from Modal
    function deleteCellFromModal() {
        const rid    = $('#modal_rid').val();
        const wday   = $('#modal_wday').val();
        const period = $('#modal_period').val();

        Swal.fire({
            title: 'Delete Period Assignment?',
            text: 'Are you sure you want to remove this period entry?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('class-routine.php', {
                    ajax_action: 'delete_cell',
                    rid: rid,
                    wday: wday,
                    period: period,
                    cls: currentCls,
                    sec: currentSec,
                    year: currentYear
                }, function(res) {
                    if (res.status === 'success') {
                        cellModalInstance?.hide();
                        const cellId = `cell-${wday}-${period}`;
                        const cellEl = document.getElementById(cellId);
                        if (cellEl) {
                            cellEl.innerHTML = `
                                <div class="routine-empty-btn" onclick="openCellModal(${wday}, ${period})">
                                    <i class="bi bi-plus-circle me-1"></i>Assign
                                </div>
                            `;
                        }
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1200
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Period assignment removed'
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Delete Failed', text: res.message });
                    }
                }, 'json');
            }
        });
    }

    // One-Click Quick Copy to Next Day
    function quickCopyNextDay(srcWday, tgtWday) {
        const srcName = daysMap[srcWday] || 'Day ' + srcWday;
        const tgtName = daysMap[tgtWday] || 'Day ' + tgtWday;

        Swal.fire({
            title: `Copy ${srcName} to ${tgtName}?`,
            text: `This will duplicate all period assignments from ${srcName} into ${tgtName}. Any existing entries on ${tgtName} will be replaced.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: `Yes, Copy to ${tgtName}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Copying...',
                    text: 'Please wait while copying routine periods.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.post('class-routine.php', {
                    ajax_action: 'copy_day',
                    src_wday: srcWday,
                    tgt_wday: tgtWday,
                    cls: currentCls,
                    sec: currentSec,
                    year: currentYear
                }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Routine Copied!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Copy Failed', text: res.message });
                    }
                }, 'json');
            }
        });
    }

    // Duplicate Day to All Working Days
    function copyToAllWorkingDays(srcWday) {
        const srcName = daysMap[srcWday] || 'Day ' + srcWday;

        Swal.fire({
            title: `Duplicate ${srcName} to All Working Days?`,
            text: `This will replicate ${srcName}'s full schedule across all other non-weekend working days for this class. Existing schedules will be overwritten.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, Duplicate to All',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Duplicating...',
                    text: 'Applying schedule to all working days.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.post('class-routine.php', {
                    ajax_action: 'copy_to_all_days',
                    src_wday: srcWday,
                    cls: currentCls,
                    sec: currentSec,
                    year: currentYear
                }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Duplicated Successfully!',
                            text: res.message,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Duplicate Failed', text: res.message });
                    }
                }, 'json');
            }
        });
    }

    // Clear Routine for a Single Day
    function clearDayRoutine(wday) {
        const dayName = daysMap[wday] || 'Day ' + wday;

        Swal.fire({
            title: `Clear ${dayName}'s Routine?`,
            text: `Are you sure you want to remove all period assignments for ${dayName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, Clear Day',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('class-routine.php', {
                    ajax_action: 'clear_day',
                    wday: wday,
                    cls: currentCls,
                    sec: currentSec,
                    year: currentYear
                }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cleared!',
                            text: res.message,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Clear Failed', text: res.message });
                    }
                }, 'json');
            }
        });
    }
</script>
</body>
</html>