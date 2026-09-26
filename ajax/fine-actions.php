<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

try {
    $sccode = (int)($_POST['sccode'] ?? $_SESSION['sccode'] ?? 0);
    if ($sccode <= 0) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Session expired or invalid institution code. Please log in again.']);
        exit;
    }

$action = trim($_POST['action'] ?? '');

// -------------------------------------------------------------
// HELPER FUNCTION: RECONCILE & SYNC STFINANCE FROM STUDENT_FINE_LOGS
// -------------------------------------------------------------
function sync_student_month_fines($sccode, $sessionyear, $stId, $month, $conn, $setupby = 'System Exemption Sync') {
    $stId = intval($stId);
    $month = intval($month);
    $sYear = intval($sessionyear);
    if ($stId <= 0 || $month <= 0) return;

    // 1. Get fine settings itemcode
    $setStmt = $conn->prepare("SELECT itemcode, particulareng, particularben FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND scope = 'global' AND status = 1 LIMIT 1");
    $setStmt->bind_param('is', $sccode, $sessionyear);
    $setStmt->execute();
    $sRes = $setStmt->get_result();
    $sRow = $sRes->fetch_assoc();
    $itemcode = (!empty($sRow['itemcode']) && $sRow['itemcode'] !== 'FINE01') ? $sRow['itemcode'] : 'FINE01';
    $particulareng = $sRow['particulareng'] ?? 'Absence / Bunk Fine';
    $particularben = $sRow['particularben'] ?? 'Absence / Bunk Fine';
    $setStmt->close();

    // 2. Calculate cumulative un-waived fine
    $sumStmt = $conn->prepare("SELECT COALESCE(SUM(fine_rate), 0) AS month_fine 
                              FROM student_fine_logs 
                              WHERE sccode = ? AND sessionyear = ? AND stid = ? AND month = ? AND status = 'posted'");
    $sumStmt->bind_param('isis', $sccode, $sessionyear, $stId, $month);
    $sumStmt->execute();
    $sumRes = $sumStmt->get_result();
    $monthFineRow = $sumRes->fetch_assoc();
    $totalMonthFine = intval(round(floatval($monthFineRow['month_fine'] ?? 0)));
    $sumStmt->close();

    // 3. Fetch student meta
    $siStmt = $conn->prepare("SELECT classname, sectionname, rollno FROM sessioninfo WHERE sccode = ? AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL) AND stid = ? LIMIT 1");
    $siStmt->bind_param('isi', $sccode, $sessionyear, $stId);
    $siStmt->execute();
    $siRow = $siStmt->get_result()->fetch_assoc();
    $cName = $siRow['classname'] ?? 'Unknown';
    $secName = $siRow['sectionname'] ?? 'All';
    $rollNo = intval($siRow['rollno'] ?? 0);
    $siStmt->close();

    // 4. Fetch existing stfinance rows
    $finRows = [];
    $totalAlreadyPaid = 0;
    $unpaidRow = null;

    $chkFin = $conn->prepare("SELECT id, amount, payableamt, paid, dues, pr1, idmon FROM stfinance WHERE sccode = ? AND sessionyear = ? AND stid = ? AND itemcode = ? AND month = ? ORDER BY id ASC");
    $chkFin->bind_param('iiisi', $sccode, $sYear, $stId, $itemcode, $month);
    $chkFin->execute();
    $finRes = $chkFin->get_result();
    while ($fRow = $finRes->fetch_assoc()) {
        $finRows[] = $fRow;
        $fPaid = intval($fRow['paid']);
        $fPr1 = intval($fRow['pr1']);
        $totalAlreadyPaid += $fPaid;

        if ($fPaid == 0 && $fPr1 == 0 && $unpaidRow === null) {
            $unpaidRow = $fRow;
        }
    }
    $chkFin->close();

    $netRemainingFine = max(0, $totalMonthFine - $totalAlreadyPaid);
    $stFinId = 0;

    if ($netRemainingFine > 0) {
        if ($unpaidRow !== null) {
            $stFinId = intval($unpaidRow['id']);
            $upFin = $conn->prepare("UPDATE stfinance SET amount = ?, payableamt = ?, dues = ?, modifieddate = NOW(), modifiedby = ? WHERE id = ? AND sccode = ?");
            $upFin->bind_param('iiisii', $netRemainingFine, $netRemainingFine, $netRemainingFine, $setupby, $stFinId, $sccode);
            $upFin->execute();
            $upFin->close();
        } else {
            $splitSuffix = count($finRows) > 0 ? ('-' . (count($finRows) + 1)) : '';
            $idmon = $stId . '-' . $month . '-' . $itemcode . $splitSuffix;

            $inFin = $conn->prepare("INSERT INTO stfinance 
                (sccode, sessionyear, classname, sectionname, stid, rollno, itemcode, particulareng, particularben, amount, payableamt, dues, month, idmon, setupdate, setupby)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $inFin->bind_param('iissiisssiiiiss', 
                $sccode, $sYear, $cName, $secName, $stId, $rollNo, 
                $itemcode, $particulareng, $particularben, $netRemainingFine, $netRemainingFine, $netRemainingFine, 
                $month, $idmon, $setupby);
            $inFin->execute();
            $stFinId = $conn->insert_id;
            $inFin->close();
        }
    } else {
        if ($unpaidRow !== null) {
            $upFin = $conn->prepare("UPDATE stfinance SET amount = 0, payableamt = 0, dues = 0, modifieddate = NOW(), modifiedby = ? WHERE id = ? AND sccode = ?");
            $upFin->bind_param('sii', $setupby, $unpaidRow['id'], $sccode);
            $upFin->execute();
            $upFin->close();
        }
    }

    if ($stFinId > 0) {
        $conn->query("UPDATE student_fine_logs SET stfinance_id = $stFinId WHERE sccode = $sccode AND sessionyear = '$sessionyear' AND stid = $stId AND month = $month AND (stfinance_id IS NULL OR stfinance_id = 0)");
    }
}

// -------------------------------------------------------------
// ACTION: ADD DISASTER / WEATHER EXEMPTION EVENT
// -------------------------------------------------------------
if ($action === 'add_disaster_exemption') {
    $title = trim($_POST['title'] ?? 'Natural Disaster / Special Exemption');
    $start_date = trim($_POST['start_date'] ?? date('Y-m-d'));
    $end_date = trim($_POST['end_date'] ?? $start_date);
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));

    if (empty($title) || empty($start_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Title and start date are required.']);
        exit;
    }

    $start_dt = $start_date . ' 00:00:00';
    $end_dt = $end_date . ' 23:59:59';
    $event_type = 'holiday';
    $color = '#FF5722';

    $stmt = $conn->prepare("INSERT INTO events (sccode, user_id, title, start, end, all_day, class, work, color, event_type, scope) VALUES (?, ?, ?, ?, ?, 1, 0, 0, ?, ?, 'institution')");
    $stmt->bind_param('iisssss', $sccode, $user_id, $title, $start_dt, $end_dt, $color, $event_type);
    
    if ($stmt->execute()) {
        $stmt->close();

        // AUTO-RECONCILIATION: Waive any fines already posted for these dates
        $impactedStudents = [];
        $fStmt = $conn->prepare("SELECT DISTINCT stid, month FROM student_fine_logs WHERE sccode = ? AND fine_date BETWEEN ? AND ? AND status = 'posted'");
        $fStmt->bind_param('iss', $sccode, $start_date, $end_date);
        $fStmt->execute();
        $fRes = $fStmt->get_result();
        while ($r = $fRes->fetch_assoc()) {
            $impactedStudents[] = $r;
        }
        $fStmt->close();

        if (!empty($impactedStudents)) {
            $wStmt = $conn->prepare("UPDATE student_fine_logs SET status = 'waived', updated_at = NOW() WHERE sccode = ? AND fine_date BETWEEN ? AND ? AND status = 'posted'");
            $wStmt->bind_param('iss', $sccode, $start_date, $end_date);
            $wStmt->execute();
            $wStmt->close();

            // Re-sync stfinance for impacted students
            foreach ($impactedStudents as $st) {
                sync_student_month_fines($sccode, $sessionyear, $st['stid'], $st['month'], $conn, 'Disaster Exemption Auto-Waive');
            }
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Exemption date saved successfully. Any previously posted fines for this period have been automatically waived and dues adjusted.'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save event: ' . $conn->error]);
    }
    exit;
}

// -------------------------------------------------------------
// ACTION: DELETE DISASTER EXEMPTION EVENT
// -------------------------------------------------------------
if ($action === 'delete_disaster_exemption') {
    $event_id = intval($_POST['event_id'] ?? 0);
    if ($event_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid event ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND sccode = ?");
    $stmt->bind_param('ii', $event_id, $sccode);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Exemption record deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete record.']);
    }
    $stmt->close();
    exit;
}

// -------------------------------------------------------------
// ACTION: PREVIEW FINES / CALCULATE FINES
// -------------------------------------------------------------
if ($action === 'preview_fines' || $action === 'post_fines') {
    $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
    $slot = trim($_POST['slot'] ?? 'School');
    $from_date = trim($_POST['from_date'] ?? date('Y-m-01'));
    $to_date = trim($_POST['to_date'] ?? date('Y-m-d'));
    $target_class = trim($_POST['classname'] ?? '');
    $target_section = trim($_POST['sectionname'] ?? '');
    if ($target_section === 'all') {
        $target_section = '';
    }

    $chkCol = $conn->query("SHOW COLUMNS FROM fine_settings LIKE 'absent_detection_mode'");
    if ($chkCol && $chkCol->num_rows == 0) {
        $conn->query("ALTER TABLE fine_settings ADD COLUMN `absent_detection_mode` enum('attendance_based','calendar_based') NOT NULL DEFAULT 'attendance_based' AFTER `bunk_rule_type`");
    }
    $chkCol2 = $conn->query("SHOW COLUMNS FROM fine_settings LIKE 'weekly_off'");
    if ($chkCol2 && $chkCol2->num_rows == 0) {
        $conn->query("ALTER TABLE fine_settings ADD COLUMN `weekly_off` varchar(30) NOT NULL DEFAULT 'friday_saturday' AFTER `absent_detection_mode`");
    }

    // 0. Ensure student_fine_logs table exists
    $conn->query("CREATE TABLE IF NOT EXISTS `student_fine_logs` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `sccode` int(11) NOT NULL,
      `sessionyear` varchar(10) NOT NULL,
      `classname` varchar(50) NOT NULL,
      `sectionname` varchar(50) DEFAULT NULL,
      `stid` bigint(20) NOT NULL,
      `rollno` int(11) DEFAULT NULL,
      `fine_date` date NOT NULL,
      `fine_type` enum('absent','bunk') NOT NULL DEFAULT 'absent',
      `fine_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
      `stfinance_id` int(11) DEFAULT NULL,
      `month` tinyint(2) NOT NULL,
      `status` enum('posted','waived','cancelled') NOT NULL DEFAULT 'posted',
      `created_by` varchar(100) DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_fine_entry` (`sccode`, `sessionyear`, `stid`, `fine_date`, `fine_type`),
      KEY `idx_lookup` (`sccode`, `sessionyear`, `stid`, `fine_date`),
      KEY `idx_month` (`sccode`, `sessionyear`, `month`),
      KEY `idx_status` (`sccode`, `status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

    // 1. Fetch Fine Settings (Global and Class-wise overrides)
    $setStmt = $conn->prepare("SELECT scope, classname, absent_rate, bunk_rate, bunk_rule_type, absent_detection_mode, weekly_off, itemcode, particulareng, particularben FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND status = 1");
    $setStmt->bind_param('iss', $sccode, $sessionyear, $slot);
    $setStmt->execute();
    $setRes = $setStmt->get_result();

    $globalSetting = [
        'absent_rate' => 10.00,
        'bunk_rate' => 20.00,
        'bunk_rule_type' => 'flat_daily',
        'absent_detection_mode' => 'attendance_based',
        'weekly_off' => 'friday_saturday',
        'itemcode' => uniqid(),
        'particulareng' => 'Absence / Bunk Fine',
        'particularben' => 'Absence / Bunk Fine'
    ];
    $classSettings = [];

    while ($sRow = $setRes->fetch_assoc()) {
        if ($sRow['scope'] === 'global') {
            $globalSetting = array_merge($globalSetting, $sRow);
        } else if ($sRow['scope'] === 'class' && !empty($sRow['classname'])) {
            $classSettings[$sRow['classname']] = $sRow;
        }
    }
    $setStmt->close();

    $temp_absent = floatval($_POST['temp_absent_rate'] ?? 0);
    $temp_bunk = floatval($_POST['temp_bunk_rate'] ?? 0);
    if ($globalSetting['absent_rate'] <= 0 && $temp_absent > 0) {
        $globalSetting['absent_rate'] = $temp_absent;
    }
    if ($globalSetting['bunk_rate'] <= 0 && $temp_bunk > 0) {
        $globalSetting['bunk_rate'] = $temp_bunk;
    }

    $calc_mode = trim($_POST['calc_mode'] ?? '');
    if (empty($calc_mode) || $calc_mode === 'policy') {
        $calc_mode = $globalSetting['absent_detection_mode'] ?: 'attendance_based';
    }

    // Fetch Weekends from settings table (setting_title = 'Weekends', dot-separated)
    $weekendsMap = [];
    $rawWeekends = '';
    $wStmt = $conn->prepare("SELECT settings_value FROM settings WHERE (sccode = ? OR sccode = 0) AND LOWER(setting_title) = 'weekends' ORDER BY (sccode = ?) DESC, id DESC LIMIT 1");
    if ($wStmt) {
        $wStmt->bind_param('ii', $sccode, $sccode);
        $wStmt->execute();
        $wRes = $wStmt->get_result();
        if ($wRow = $wRes->fetch_assoc()) {
            $rawWeekends = trim($wRow['settings_value'] ?? '');
        }
        $wStmt->close();
    }
    if (empty($rawWeekends)) {
        $rawWeekends = 'Friday.Saturday';
    }

    $wParts = preg_split('/[\.,\s]+/', $rawWeekends);
    $wList = [];
    foreach ($wParts as $wp) {
        $wp = trim($wp);
        if (!empty($wp)) {
            $weekendsMap[strtolower($wp)] = true;
            $wList[] = ucfirst(strtolower($wp));
        }
    }
    $weekendsDisplay = !empty($wList) ? implode(', ', $wList) : 'Friday, Saturday';

    // 2. Fetch Exempted Dates (Events & Holidays: sccode = ? AND class = 0)
    $exemptDates = [];
    $evStmt = $conn->prepare("SELECT DATE(start) as sdate, DATE(end) as edate FROM events WHERE sccode = ? AND class = 0 AND ((start BETWEEN ? AND ?) OR (end BETWEEN ? AND ?) OR (start <= ? AND end >= ?))");
    $fStart = $from_date . ' 00:00:00';
    $tEnd = $to_date . ' 23:59:59';
    $evStmt->bind_param('issssss', $sccode, $fStart, $tEnd, $fStart, $tEnd, $fStart, $tEnd);
    $evStmt->execute();
    $evRes = $evStmt->get_result();

    while ($eRow = $evRes->fetch_assoc()) {
        $curDate = strtotime($eRow['sdate']);
        $endDate = strtotime($eRow['edate'] ?: $eRow['sdate']);
        while ($curDate <= $endDate) {
            $exemptDates[date('Y-m-d', $curDate)] = true;
            $curDate = strtotime('+1 day', $curDate);
        }
    }
    $evStmt->close();

    // 3. Fetch Approved Leaves from student_leave_app (if table exists)
    $approvedLeaves = [];
    $leaveRes = $conn->query("SHOW TABLES LIKE 'student_leave_app'");
    if ($leaveRes && $leaveRes->num_rows > 0) {
        $cols = [];
        $cRes = $conn->query("SHOW COLUMNS FROM student_leave_app");
        if ($cRes) {
            while ($cr = $cRes->fetch_assoc()) {
                $cols[$cr['Field']] = true;
            }
        }
        $colFrom = isset($cols['date_from']) ? 'date_from' : (isset($cols['from_date']) ? 'from_date' : '');
        $colTo = isset($cols['date_to']) ? 'date_to' : (isset($cols['to_date']) ? 'to_date' : '');

        if ($colFrom && $colTo) {
            $lvSql = "SELECT stid, $colFrom AS fdate, $colTo AS tdate 
                      FROM student_leave_app 
                      WHERE sccode = ? 
                        AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL) 
                        AND (LOWER(status) = 'approved' OR LOWER(status) = 'granted' OR status = '1') 
                        AND (($colFrom <= ? AND $colTo >= ?))";
            $lvStmt = $conn->prepare($lvSql);
            if ($lvStmt) {
                $lvStmt->bind_param('isss', $sccode, $sessionyear, $to_date, $from_date);
                $lvStmt->execute();
                $lvRes = $lvStmt->get_result();

                while ($lRow = $lvRes->fetch_assoc()) {
                    $sId = (string)$lRow['stid'];
                    if (!empty($lRow['fdate'])) {
                        $curD = strtotime($lRow['fdate']);
                        $endD = strtotime($lRow['tdate'] ?: $lRow['fdate']);
                        while ($curD <= $endD) {
                            $approvedLeaves[$sId][date('Y-m-d', $curD)] = true;
                            $curD = strtotime('+1 day', $curD);
                        }
                    }
                }
                $lvStmt->close();
            }
        }
    }

    // 4. Fetch Already Logged Fines from student_fine_logs for this Date Range
    $existingLogs = [];
    $exStmt = $conn->prepare("SELECT stid, fine_date, fine_type, status, fine_rate FROM student_fine_logs WHERE sccode = ? AND sessionyear = ? AND fine_date BETWEEN ? AND ?");
    if ($exStmt) {
        $exStmt->bind_param('isss', $sccode, $sessionyear, $from_date, $to_date);
        $exStmt->execute();
        $exRes = $exStmt->get_result();
        while ($exRow = $exRes->fetch_assoc()) {
            $sKey = (string)$exRow['stid'];
            $dKey = $exRow['fine_date'];
            $tKey = $exRow['fine_type'];
            $existingLogs[$sKey][$dKey][$tKey] = $exRow;
        }
        $exStmt->close();
    }

    // 5. Query Enrolled Students from sessioninfo
    $whereClassSi = "";
    if ($target_class !== '' && $target_class !== 'all') {
        $whereClassSi .= " AND si.classname = '" . $conn->real_escape_string($target_class) . "'";
    }
    if ($target_section !== '' && $target_section !== 'all') {
        $whereClassSi .= " AND si.sectionname = '" . $conn->real_escape_string($target_section) . "'";
    }

    $classStudents = [];
    $siSql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, 
                     COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname
              FROM sessioninfo si
              LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
              WHERE si.sccode = ? 
                AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL OR si.sessionyear = 0)
                AND (si.status = 1 OR si.status IS NULL)
                AND si.stid IS NOT NULL AND si.stid > 0
                $whereClassSi
              ORDER BY si.classname ASC, si.rollno ASC";
    $siStmt = $conn->prepare($siSql);
    $siStmt->bind_param('is', $sccode, $sessionyear);
    $siStmt->execute();
    $siRes = $siStmt->get_result();

    while ($siRow = $siRes->fetch_assoc()) {
        $cName = $siRow['classname'];
        $classStudents[$cName][] = $siRow;
    }
    $siStmt->close();

    // 6. Query Attendance records from stattnd
    $whereClassSql = "";
    if ($target_class !== '' && $target_class !== 'all') {
        $whereClassSql .= " AND classname = '" . $conn->real_escape_string($target_class) . "'";
    }
    if ($target_section !== '' && $target_section !== 'all') {
        $whereClassSql .= " AND sectionname = '" . $conn->real_escape_string($target_section) . "'";
    }

    $attSql = "SELECT stid, rollno, classname, sectionname, stname, adate, yn, bunk 
               FROM stattnd 
               WHERE sccode = ? 
                 AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL OR sessionyear = 0 OR sessionyear = YEAR(adate))
                 AND adate BETWEEN ? AND ? 
                 $whereClassSql
               ORDER BY adate ASC";

    $attStmt = $conn->prepare($attSql);
    $attStmt->bind_param('isss', $sccode, $sessionyear, $from_date, $to_date);
    $attStmt->execute();
    $attRes = $attStmt->get_result();

    $attendanceMap = [];
    $classActiveDates = [];

    while ($r = $attRes->fetch_assoc()) {
        $cName = $r['classname'];
        $aDate = $r['adate'];
        $sId = (string)$r['stid'];
        $classActiveDates[$cName][$aDate] = true;
        $attendanceMap[$cName][$aDate][$sId] = [
            'yn' => intval($r['yn']),
            'bunk' => intval($r['bunk']),
            'rollno' => $r['rollno'],
            'sectionname' => $r['sectionname'],
            'stname' => $r['stname']
        ];
    }
    $attStmt->close();

    // If Calendar Working Days mode: expand all non-holiday calendar days for enrolled classes
    if ($calc_mode === 'calendar_based') {
        $curD = strtotime($from_date);
        $endD = strtotime($to_date);

        $classesInScope = [];
        if (!empty($target_class) && $target_class !== 'all') {
            $classesInScope[] = $target_class;
        } else {
            $classesInScope = array_keys($classStudents);
        }

        while ($curD <= $endD) {
            $dateStr = date('Y-m-d', $curD);
            $dayOfWeek = strtolower(date('l', $curD)); // e.g. friday, saturday, sunday...

            $isWeeklyOff = isset($weekendsMap[$dayOfWeek]);

            // Exclude weekly off and events table exemptions (class = 0)
            if (!$isWeeklyOff && empty($exemptDates[$dateStr])) {
                foreach ($classesInScope as $cName) {
                    $classActiveDates[$cName][$dateStr] = true;
                }
            }
            $curD = strtotime('+1 day', $curD);
        }
    }

    $studentSummary = [];
    $classBreakdown = [];
    $newFineItems = [];
    $totalNewAbsentCount = 0;
    $totalNewBunkCount = 0;
    $totalNewFineAmount = 0.00;
    $totalAlreadyBilledCount = 0;
    $totalAlreadyBilledAmount = 0.00;

    // 7. Compute Fines with Smart Absent Detection (Biometric / Card / Manual Roll Call)
    foreach ($classActiveDates as $cName => $dates) {
        $rateAbsent = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['absent_rate']) : floatval($globalSetting['absent_rate']);
        $rateBunk = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['bunk_rate']) : floatval($globalSetting['bunk_rate']);

        $enrolledStudents = $classStudents[$cName] ?? [];

        // Fallback to students present in stattnd if class not found in sessioninfo
        if (empty($enrolledStudents)) {
            $fallbackStudents = [];
            foreach ($dates as $aDate => $_val) {
                if (!empty($attendanceMap[$cName][$aDate])) {
                    foreach ($attendanceMap[$cName][$aDate] as $sId => $attInfo) {
                        $numStid = intval($sId);
                        if ($numStid <= 0) continue;
                        $fallbackStudents[$numStid] = [
                            'stid' => $numStid,
                            'rollno' => intval($attInfo['rollno']),
                            'classname' => $cName,
                            'sectionname' => $attInfo['sectionname'],
                            'stname' => $attInfo['stname']
                        ];
                    }
                }
            }
            $enrolledStudents = array_values($fallbackStudents);
        }

        foreach ($dates as $aDate => $_true) {
            // Check holiday / disaster exemption
            if (!empty($exemptDates[$aDate])) {
                continue;
            }

            $dateMonth = intval(date('m', strtotime($aDate)));

            foreach ($enrolledStudents as $st) {
                $numStid = intval($st['stid'] ?? 0);
                if ($numStid <= 0) {
                    continue;
                }
                $sId = (string)$numStid;

                // Check approved student leave
                if (!empty($approvedLeaves[$sId][$aDate])) {
                    continue;
                }

                $attInfo = $attendanceMap[$cName][$aDate][$sId] ?? null;
                $isAbsent = false;
                $isBunk = false;

                if ($attInfo === null) {
                    // Student was not punched/present on an active school day -> ABSENT
                    $isAbsent = true;
                } else {
                    if ($attInfo['yn'] === 0) {
                        $isAbsent = true;
                    }
                    if ($attInfo['bunk'] === 1) {
                        $isBunk = true;
                    }
                }

                if (!$isAbsent && !$isBunk) {
                    continue;
                }

                if (!isset($studentSummary[$sId])) {
                    $studentSummary[$sId] = [
                        'stid' => $numStid,
                        'rollno' => $st['rollno'],
                        'classname' => $cName,
                        'sectionname' => $st['sectionname'],
                        'stname' => $st['stname'] ?: ($attInfo['stname'] ?? ''),
                        'new_absent_days' => 0,
                        'new_bunk_days' => 0,
                        'already_posted_days' => 0,
                        'new_fine_amount' => 0.00,
                        'already_posted_amount' => 0.00,
                        'total_fine' => 0.00
                    ];
                }

                if (!isset($classBreakdown[$cName])) {
                    $classBreakdown[$cName] = [
                        'classname' => $cName,
                        'students_count' => 0,
                        'new_absent_days' => 0,
                        'new_bunk_days' => 0,
                        'already_posted_days' => 0,
                        'total_new_fine' => 0.00
                    ];
                }

                // Check Absent Status in Existing Logs
                if ($isAbsent) {
                    $exAbsent = $existingLogs[$sId][$aDate]['absent'] ?? null;
                    if ($exAbsent) {
                        if ($exAbsent['status'] === 'waived') {
                            // Waived - do not fine
                        } else if ($exAbsent['status'] === 'posted') {
                            // Already posted in a previous run
                            $studentSummary[$sId]['already_posted_days']++;
                            $studentSummary[$sId]['already_posted_amount'] += floatval($exAbsent['fine_rate']);
                            $classBreakdown[$cName]['already_posted_days']++;
                            $totalAlreadyBilledCount++;
                            $totalAlreadyBilledAmount += floatval($exAbsent['fine_rate']);
                        }
                    } else {
                        // Brand New Absent Fine
                        $studentSummary[$sId]['new_absent_days']++;
                        $studentSummary[$sId]['new_fine_amount'] += $rateAbsent;
                        $studentSummary[$sId]['total_fine'] += $rateAbsent;

                        $classBreakdown[$cName]['new_absent_days']++;
                        $classBreakdown[$cName]['total_new_fine'] += $rateAbsent;
                        $totalNewAbsentCount++;
                        $totalNewFineAmount += $rateAbsent;

                        $newFineItems[] = [
                            'stid' => $numStid,
                            'rollno' => $st['rollno'],
                            'classname' => $cName,
                            'sectionname' => $st['sectionname'] ?: 'All',
                            'fine_date' => $aDate,
                            'fine_type' => 'absent',
                            'fine_rate' => $rateAbsent,
                            'month' => $dateMonth
                        ];
                    }
                }

                // Check Bunk Status in Existing Logs
                if ($isBunk) {
                    $exBunk = $existingLogs[$sId][$aDate]['bunk'] ?? null;
                    if ($exBunk) {
                        if ($exBunk['status'] === 'waived') {
                            // Waived
                        } else if ($exBunk['status'] === 'posted') {
                            // Already posted
                            $studentSummary[$sId]['already_posted_days']++;
                            $studentSummary[$sId]['already_posted_amount'] += floatval($exBunk['fine_rate']);
                            $classBreakdown[$cName]['already_posted_days']++;
                            $totalAlreadyBilledCount++;
                            $totalAlreadyBilledAmount += floatval($exBunk['fine_rate']);
                        }
                    } else {
                        // Brand New Bunk Fine
                        $studentSummary[$sId]['new_bunk_days']++;
                        $studentSummary[$sId]['new_fine_amount'] += $rateBunk;
                        $studentSummary[$sId]['total_fine'] += $rateBunk;

                        $classBreakdown[$cName]['new_bunk_days']++;
                        $classBreakdown[$cName]['total_new_fine'] += $rateBunk;
                        $totalNewBunkCount++;
                        $totalNewFineAmount += $rateBunk;

                        $newFineItems[] = [
                            'stid' => $numStid,
                            'rollno' => $st['rollno'],
                            'classname' => $cName,
                            'sectionname' => $st['sectionname'] ?: 'All',
                            'fine_date' => $aDate,
                            'fine_type' => 'bunk',
                            'fine_rate' => $rateBunk,
                            'month' => $dateMonth
                        ];
                    }
                }
            }
        }
    }

    // Count distinct students per class
    $distinctPerClass = [];
    foreach ($studentSummary as $st) {
        $distinctPerClass[$st['classname']][$st['stid']] = true;
    }
    foreach ($distinctPerClass as $cName => $stids) {
        if (isset($classBreakdown[$cName])) {
            $classBreakdown[$cName]['students_count'] = count($stids);
        }
    }

    $allActiveDates = [];
    foreach ($classActiveDates as $cName => $dList) {
        foreach ($dList as $d => $_v) {
            $allActiveDates[$d] = true;
        }
    }

    // -------------------------------------------------------------
    // ACTION: PREVIEW FINES
    // -------------------------------------------------------------
    if ($action === 'preview_fines') {
        echo json_encode([
            'status' => 'success',
            'summary' => [
                'total_students' => count($studentSummary),
                'new_absent_days' => $totalNewAbsentCount,
                'new_bunk_days' => $totalNewBunkCount,
                'total_new_fine_amount' => round($totalNewFineAmount, 2),
                'already_billed_days' => $totalAlreadyBilledCount,
                'already_billed_amount' => round($totalAlreadyBilledAmount, 2),
                'exempt_days_found' => count($exemptDates),
                'active_school_days' => count($allActiveDates),
                'calc_mode' => $calc_mode,
                'weekends_setting' => $weekendsDisplay,
                'weekends_raw' => $rawWeekends,
                'date_range' => "$from_date to $to_date"
            ],
            'class_breakdown' => array_values($classBreakdown),
            'student_preview' => array_slice(array_values($studentSummary), 0, 100)
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: POST FINES TO STUDENT_FINE_LOGS & CONSOLIDATE INTO STFINANCE
    // -------------------------------------------------------------
    if ($action === 'post_fines') {
        if (empty($newFineItems) && empty($studentSummary)) {
            echo json_encode(['status' => 'error', 'message' => 'No fine records found for the selected date range.']);
            exit;
        }

        $itemcode = (!empty($globalSetting['itemcode']) && $globalSetting['itemcode'] !== 'FINE01') ? $globalSetting['itemcode'] : uniqid();
        $particulareng = $globalSetting['particulareng'] ?: 'Absence / Bunk Fine';
        $particularben = $globalSetting['particularben'] ?: 'Absence / Bunk Fine';
        $setupby = $_SESSION['user_id'] ?? 'Admin Fine Generator';
        $sYear = intval($sessionyear);

        // Step A: Insert new daily records into student_fine_logs
        $insertedLogsCount = 0;
        if (!empty($newFineItems)) {
            $inLog = $conn->prepare("INSERT INTO student_fine_logs 
                (sccode, sessionyear, classname, sectionname, stid, rollno, fine_date, fine_type, fine_rate, month, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'posted', ?)
                ON DUPLICATE KEY UPDATE 
                  classname = VALUES(classname),
                  sectionname = VALUES(sectionname),
                  rollno = VALUES(rollno),
                  fine_rate = VALUES(fine_rate),
                  status = 'posted'");

            foreach ($newFineItems as $item) {
                $inLog->bind_param('isssiisssds', 
                    $sccode, $sessionyear, $item['classname'], $item['sectionname'], 
                    $item['stid'], $item['rollno'], $item['fine_date'], $item['fine_type'], 
                    $item['fine_rate'], $item['month'], $setupby);
                $inLog->execute();
                $insertedLogsCount++;
            }
            $inLog->close();
        }

        // Step B: Identify all impacted (stid, month) pairs across the date range
        $affectedPairs = [];
        foreach ($newFineItems as $item) {
            $affectedPairs[$item['stid']][$item['month']] = [
                'classname' => $item['classname'],
                'sectionname' => $item['sectionname'],
                'rollno' => $item['rollno']
            ];
        }
        foreach ($studentSummary as $st) {
            $mStart = intval(date('m', strtotime($from_date)));
            $mEnd = intval(date('m', strtotime($to_date)));
            for ($m = $mStart; $m <= $mEnd; $m++) {
                if (!isset($affectedPairs[$st['stid']][$m])) {
                    $affectedPairs[$st['stid']][$m] = [
                        'classname' => $st['classname'],
                        'sectionname' => $st['sectionname'],
                        'rollno' => $st['rollno']
                    ];
                }
            }
        }

        // Step C: Consolidate active fines from student_fine_logs into stfinance ledger
        $ledgerPostedCount = 0;
        $ledgerUpdatedCount = 0;

        foreach ($affectedPairs as $stId => $months) {
            $stId = intval($stId);
            if ($stId <= 0) continue;

            foreach ($months as $mon => $meta) {
                $mon = intval($mon);
                if ($mon <= 0) continue;

                // 1. Calculate cumulative un-waived fine for this student and month from student_fine_logs
                $sumStmt = $conn->prepare("SELECT COALESCE(SUM(fine_rate), 0) AS month_fine 
                                          FROM student_fine_logs 
                                          WHERE sccode = ? AND sessionyear = ? AND stid = ? AND month = ? AND status = 'posted'");
                $sumStmt->bind_param('isis', $sccode, $sessionyear, $stId, $mon);
                $sumStmt->execute();
                $sumRes = $sumStmt->get_result();
                $monthFineRow = $sumRes->fetch_assoc();
                $totalMonthFine = intval(round(floatval($monthFineRow['month_fine'] ?? 0)));
                $sumStmt->close();

                if ($totalMonthFine <= 0) {
                    continue;
                }

                $cName = $meta['classname'];
                $secName = $meta['sectionname'] ?: 'All';
                $rollNo = intval($meta['rollno']);

                // 2. Fetch all existing fine records for this student and month in stfinance
                $finRows = [];
                $totalAlreadyPaid = 0;
                $unpaidRow = null;

                $chkFin = $conn->prepare("SELECT id, amount, payableamt, paid, dues, pr1, idmon FROM stfinance WHERE sccode = ? AND sessionyear = ? AND stid = ? AND itemcode = ? AND month = ? ORDER BY id ASC");
                $chkFin->bind_param('iiisi', $sccode, $sYear, $stId, $itemcode, $mon);
                $chkFin->execute();
                $finRes = $chkFin->get_result();
                while ($fRow = $finRes->fetch_assoc()) {
                    $finRows[] = $fRow;
                    $fPaid = intval($fRow['paid']);
                    $fPr1 = intval($fRow['pr1']);
                    $totalAlreadyPaid += $fPaid;

                    // An unpaid row is one where no money was received yet and no receipt issued
                    if ($fPaid == 0 && $fPr1 == 0 && $unpaidRow === null) {
                        $unpaidRow = $fRow;
                    }
                }
                $chkFin->close();

                // Net remaining unpaid fine that needs to be represented in stfinance
                $netRemainingFine = max(0, $totalMonthFine - $totalAlreadyPaid);

                $stFinId = 0;

                if ($netRemainingFine > 0) {
                    if ($unpaidRow !== null) {
                        // Update the existing UNPAID row (leaving all paid rows 100% untouched)
                        $stFinId = intval($unpaidRow['id']);
                        $upFin = $conn->prepare("UPDATE stfinance SET 
                            amount = ?, payableamt = ?, dues = ?, modifieddate = NOW(), modifiedby = ?
                            WHERE id = ? AND sccode = ?");
                        $upFin->bind_param('iiisii', $netRemainingFine, $netRemainingFine, $netRemainingFine, $setupby, $stFinId, $sccode);
                        $upFin->execute();
                        $upFin->close();
                        $ledgerUpdatedCount++;
                    } else {
                        // All existing rows are already paid (or no row existed yet) -> Insert a NEW clean record for the unpaid fine!
                        $splitSuffix = count($finRows) > 0 ? ('-' . (count($finRows) + 1)) : '';
                        $idmon = $stId . '-' . $mon . '-' . $itemcode . $splitSuffix;

                        $inFin = $conn->prepare("INSERT INTO stfinance 
                            (sccode, sessionyear, classname, sectionname, stid, rollno, itemcode, particulareng, particularben, amount, payableamt, dues, month, idmon, setupdate, setupby)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                        $inFin->bind_param('iissiisssiiiiss', 
                            $sccode, $sYear, $cName, $secName, $stId, $rollNo, 
                            $itemcode, $particulareng, $particularben, $netRemainingFine, $netRemainingFine, $netRemainingFine, 
                            $mon, $idmon, $setupby);
                        $inFin->execute();
                        $stFinId = $conn->insert_id;
                        $inFin->close();
                        $ledgerPostedCount++;
                    }
                } else {
                    // Net remaining fine is 0 (everything already paid): If there was an open 0-due unpaid row, set it to 0
                    if ($unpaidRow !== null) {
                        $upFin = $conn->prepare("UPDATE stfinance SET amount = 0, payableamt = 0, dues = 0, modifieddate = NOW(), modifiedby = ? WHERE id = ? AND sccode = ?");
                        $upFin->bind_param('sii', $setupby, $unpaidRow['id'], $sccode);
                        $upFin->execute();
                        $upFin->close();
                    }
                }

                // 3. Link stfinance_id in student_fine_logs
                if ($stFinId > 0) {
                    $conn->query("UPDATE student_fine_logs SET stfinance_id = $stFinId WHERE sccode = $sccode AND sessionyear = '$sessionyear' AND stid = $stId AND month = $mon AND (stfinance_id IS NULL OR stfinance_id = 0)");
                }
            }
        }

        // Update last_generated_date in fine_settings
        $conn->query("UPDATE fine_settings SET last_generated_date = '$to_date' WHERE sccode = '$sccode' AND sessionyear = '$sessionyear' AND slot = '$slot' AND scope = 'global'");

        echo json_encode([
            'status' => 'success',
            'message' => "Successfully recorded $insertedLogsCount daily fine log(s) in student_fine_logs and synced to stfinance monthly ledger. (Ledgers New: $ledgerPostedCount, Updated: $ledgerUpdatedCount)"
        ]);
        exit;
    }
}

    echo json_encode(['status' => 'error', 'message' => 'Unknown action request.']);
    exit;

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
