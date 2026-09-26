<?php
// EIMBox Daily Fine Auto-Posting Background Worker
// Can be run via Server Crontab (* * * * * or every 10 mins), Windows Task Scheduler, or Web Dispatcher

if (php_sapi_name() !== 'cli' && !isset($_GET['run_now']) && !isset($_GET['api_key'])) {
    // Web trigger allowed with ?run_now=1 or key
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';

// Concurrency Lock Check
$lock_file = sys_get_temp_dir() . '/eimbox_fine_auto_poster.lock';
if (file_exists($lock_file) && (time() - filemtime($lock_file) < 300)) {
    exit("Fine Auto-Poster already running. Skipping duplicate execution.\n");
}
touch($lock_file);

$nowTime = date('H:i:s');
$today = date('Y-m-d');
$currentYear = date('Y');
$currentMonth = intval(date('m'));

echo "====================================================\n";
echo "EIMBox Daily Fine Auto-Poster Running at " . date('Y-m-d H:i:s') . "\n";
echo "====================================================\n";

$is_force = false;
$forced_sccode = 0;

// Check CLI arguments
if (isset($argv)) {
    foreach ($argv as $arg) {
        if ($arg === '--force' || $arg === '-f') {
            $is_force = true;
        }
        if (strpos($arg, '--sccode=') === 0) {
            $forced_sccode = intval(substr($arg, 9));
        }
    }
}
// Check GET parameters
if (isset($_GET['force']) && $_GET['force'] == 1) {
    $is_force = true;
}
if (isset($_GET['sccode'])) {
    $forced_sccode = intval($_GET['sccode']);
}

// 1. Fetch institutions configured for Daily Auto Posting (or forced target)
if ($is_force) {
    $whereForce = ($forced_sccode > 0) ? "AND fs.sccode = $forced_sccode" : "";
    $sql = "SELECT fs.*, sc.scname 
            FROM fine_settings fs
            LEFT JOIN scinfo sc ON fs.sccode = sc.sccode
            WHERE fs.status = 1 
              AND fs.scope = 'global'
              $whereForce
            ORDER BY fs.sccode ASC";
    echo "[MODE: FORCE TEST RUN ENABLED]\n";
} else {
    $sql = "SELECT fs.*, sc.scname 
            FROM fine_settings fs
            LEFT JOIN scinfo sc ON fs.sccode = sc.sccode
            WHERE fs.status = 1 
              AND fs.scope = 'global'
              AND (
                -- 1. Daily Mode: Time reached and not posted today
                (
                  fs.posting_mode = 'daily'
                  AND (fs.last_generated_date IS NULL OR fs.last_generated_date < CURDATE())
                  AND CURTIME() >= fs.daily_run_time
                )
                OR
                -- 2. Monthly Mode: Day of month reached and not posted this month
                (
                  fs.posting_mode = 'monthly'
                  AND DAY(CURDATE()) >= COALESCE(fs.monthly_run_day, 1)
                  AND (
                    fs.last_generated_date IS NULL 
                    OR fs.last_generated_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
                  )
                  AND CURTIME() >= fs.daily_run_time
                )
              )
            ORDER BY fs.sccode ASC";
}

$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "No pending daily or monthly fine posting tasks found for current time ($nowTime).\n";
    echo "Tip: To test-run immediately regardless of posting_mode, run: php fine-auto-poster.php --force\n";
    @unlink($lock_file);
    exit;
}

$processedInstitutions = 0;
$totalLogsInserted = 0;

while ($setting = $res->fetch_assoc()) {
    $sccode = intval($setting['sccode']);
    $sessionyear = $setting['sessionyear'] ?: $currentYear;
    $slot = $setting['slot'] ?: 'School';
    $calc_mode = $setting['absent_detection_mode'] ?: 'attendance_based';
    $dailyTime = $setting['daily_run_time'] ?: '18:00:00';
    $postMode = $setting['posting_mode'] ?: 'manual';
    $schoolName = $setting['scname'] ?? "School #$sccode";

    echo "\nProcessing: [sccode: $sccode] $schoolName | Mode: " . strtoupper($postMode) . " | Session: $sessionyear | Slot: $slot | Time: $dailyTime\n";

    // Determine date range to process based on posting_mode
    $lastGen = $setting['last_generated_date'];
    
    if ($postMode === 'monthly') {
        // For monthly mode: Calculate for the previous completed month (or from last_generated_date + 1 up to end of last month)
        if (empty($lastGen)) {
            // If never run before, calculate for entire last month
            $fromDate = date('Y-m-01', strtotime('first day of last month'));
            $toDate = date('Y-m-t', strtotime('last month'));
        } else {
            $fromDate = date('Y-m-d', strtotime('+1 day', strtotime($lastGen)));
            $toDate = date('Y-m-t', strtotime('last month'));
            if (strtotime($fromDate) > strtotime($toDate)) {
                // Already caught up to last month, process current month up to yesterday
                $fromDate = date('Y-m-01', strtotime($today));
                $toDate = date('Y-m-d', strtotime('-1 day', strtotime($today)));
            }
        }
        if (strtotime($fromDate) > strtotime($toDate)) {
            $toDate = $today;
        }
    } else {
        // Daily mode: from (last_generated_date + 1) up to today
        $fromDate = $lastGen ? date('Y-m-d', strtotime('+1 day', strtotime($lastGen))) : $today;
        $toDate = $today;

        $monthStart = date('Y-m-01', strtotime($today));
        if (strtotime($fromDate) < strtotime($monthStart)) {
            $fromDate = $monthStart;
        }
        if (strtotime($fromDate) > strtotime($toDate)) {
            $fromDate = $toDate;
        }
    }

    echo " - Processing Date Range: $fromDate to $toDate\n";

    // 2. Fetch Weekends from settings table
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
    foreach ($wParts as $wp) {
        $wp = trim($wp);
        if (!empty($wp)) {
            $weekendsMap[strtolower($wp)] = true;
        }
    }

    // 3. Fetch Holidays / Disaster Exemptions for date range (class = 0)
    $exemptDates = [];
    $evStmt = $conn->prepare("SELECT DATE(start) as sdate, DATE(end) as edate FROM events WHERE sccode = ? AND class = 0 AND ((start BETWEEN ? AND ?) OR (end BETWEEN ? AND ?) OR (start <= ? AND end >= ?))");
    $fStart = $fromDate . ' 00:00:00';
    $tEnd = $today . ' 23:59:59';
    $evStmt->bind_param('issssss', $sccode, $fStart, $tEnd, $fStart, $tEnd, $fStart, $tEnd);
    $evStmt->execute();
    $evRes = $evStmt->get_result();
    while ($eRow = $evRes->fetch_assoc()) {
        $cD = strtotime($eRow['sdate']);
        $eD = strtotime($eRow['edate'] ?: $eRow['sdate']);
        while ($cD <= $eD) {
            $exemptDates[date('Y-m-d', $cD)] = true;
            $cD = strtotime('+1 day', $cD);
        }
    }
    $evStmt->close();

    // 4. Fetch Class-Wise Rate Overrides
    $classSettings = [];
    $csStmt = $conn->prepare("SELECT classname, absent_rate, bunk_rate FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'class' AND status = 1");
    $csStmt->bind_param('iss', $sccode, $sessionyear, $slot);
    $csStmt->execute();
    $csRes = $csStmt->get_result();
    while ($csRow = $csRes->fetch_assoc()) {
        $classSettings[$csRow['classname']] = $csRow;
    }
    $csStmt->close();

    $globalAbsentRate = floatval($setting['absent_rate'] ?: 10.00);
    $globalBunkRate = floatval($setting['bunk_rate'] ?: 20.00);
    $itemcode = (!empty($setting['itemcode']) && $setting['itemcode'] !== 'FINE01') ? $setting['itemcode'] : uniqid();
    $particulareng = $setting['particulareng'] ?: 'Absence / Bunk Fine';
    $particularben = $setting['particularben'] ?: 'Absence / Bunk Fine';

    // 5. Fetch Approved Student Leaves for Date Range
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
            $lvSql = "SELECT stid, $colFrom AS fdate, $colTo AS tdate FROM student_leave_app 
                      WHERE sccode = ? 
                        AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL) 
                        AND (LOWER(status) = 'approved' OR LOWER(status) = 'granted' OR status = '1') 
                        AND ($colFrom <= ? AND $colTo >= ?)";
            $lvStmt = $conn->prepare($lvSql);
            if ($lvStmt) {
                $lvStmt->bind_param('isss', $sccode, $sessionyear, $today, $fromDate);
                $lvStmt->execute();
                $lvRes = $lvStmt->get_result();
                while ($lRow = $lvRes->fetch_assoc()) {
                    $sId = (string)$lRow['stid'];
                    $cD = strtotime($lRow['fdate']);
                    $eD = strtotime($lRow['tdate'] ?: $lRow['fdate']);
                    while ($cD <= $eD) {
                        $approvedLeaves[$sId][date('Y-m-d', $cD)] = true;
                        $cD = strtotime('+1 day', $cD);
                    }
                }
                $lvStmt->close();
            }
        }
    }

    // 6. Query Enrolled Students from sessioninfo
    $classStudents = [];
    $siSql = "SELECT si.stid, si.rollno, si.classname, si.sectionname 
              FROM sessioninfo si 
              WHERE si.sccode = ? 
                AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL OR si.sessionyear = 0)
                AND (si.status = 1 OR si.status IS NULL)
                AND si.stid IS NOT NULL AND si.stid > 0
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

    // 7. Query Attendance across Date Range from stattnd
    $attSql = "SELECT stid, rollno, classname, sectionname, adate, yn, bunk 
               FROM stattnd 
               WHERE sccode = ? 
                 AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL OR sessionyear = 0 OR sessionyear = YEAR(adate))
                 AND adate BETWEEN ? AND ?";
    $attStmt = $conn->prepare($attSql);
    $attStmt->bind_param('isss', $sccode, $sessionyear, $fromDate, $today);
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
            'sectionname' => $r['sectionname']
        ];
    }
    $attStmt->close();

    // If calendar based detection, mark active days
    if ($calc_mode === 'calendar_based') {
        $cD = strtotime($fromDate);
        $eD = strtotime($today);
        while ($cD <= $eD) {
            $dStr = date('Y-m-d', $cD);
            $dDay = strtolower(date('l', $cD));
            if (!isset($weekendsMap[$dDay]) && empty($exemptDates[$dStr])) {
                foreach (array_keys($classStudents) as $cName) {
                    $classActiveDates[$cName][$dStr] = true;
                }
            }
            $cD = strtotime('+1 day', $cD);
        }
    }

    // 8. Identify Absent & Bunk Students Across the Date Range
    $newFineItems = [];
    $impactedStudents = [];

    foreach ($classActiveDates as $cName => $dates) {
        $rateAbsent = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['absent_rate']) : $globalAbsentRate;
        $rateBunk = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['bunk_rate']) : $globalBunkRate;

        $enrolled = $classStudents[$cName] ?? [];
        if (empty($enrolled)) {
            $fb = [];
            foreach ($dates as $aDate => $_) {
                if (!empty($attendanceMap[$cName][$aDate])) {
                    foreach ($attendanceMap[$cName][$aDate] as $sId => $attInfo) {
                        $numStid = intval($sId);
                        if ($numStid <= 0) continue;
                        $fb[$numStid] = [
                            'stid' => $numStid,
                            'rollno' => intval($attInfo['rollno']),
                            'classname' => $cName,
                            'sectionname' => $attInfo['sectionname']
                        ];
                    }
                }
            }
            $enrolled = array_values($fb);
        }

        foreach ($dates as $aDate => $_true) {
            $dDay = strtolower(date('l', strtotime($aDate)));
            if (isset($weekendsMap[$dDay])) continue;
            if (!empty($exemptDates[$aDate])) continue;

            $dMonth = intval(date('m', strtotime($aDate)));

            foreach ($enrolled as $st) {
                $numStid = intval($st['stid'] ?? 0);
                if ($numStid <= 0) continue;
                $sId = (string)$numStid;

                if (!empty($approvedLeaves[$sId][$aDate])) continue;

                $attInfo = $attendanceMap[$cName][$aDate][$sId] ?? null;
                $isAbsent = false;
                $isBunk = false;

                if ($attInfo === null) {
                    $isAbsent = true;
                } else {
                    if ($attInfo['yn'] === 0) $isAbsent = true;
                    if ($attInfo['bunk'] === 1) $isBunk = true;
                }

                if ($isAbsent && $rateAbsent > 0) {
                    $newFineItems[] = [
                        'stid' => $numStid,
                        'rollno' => intval($st['rollno']),
                        'classname' => $cName,
                        'sectionname' => $st['sectionname'] ?: 'All',
                        'fine_date' => $aDate,
                        'fine_type' => 'absent',
                        'fine_rate' => $rateAbsent,
                        'month' => $dMonth
                    ];
                    $impactedStudents[$numStid][$dMonth] = [
                        'classname' => $cName,
                        'sectionname' => $st['sectionname'] ?: 'All',
                        'rollno' => intval($st['rollno'])
                    ];
                }

                if ($isBunk && $rateBunk > 0) {
                    $newFineItems[] = [
                        'stid' => $numStid,
                        'rollno' => intval($st['rollno']),
                        'classname' => $cName,
                        'sectionname' => $st['sectionname'] ?: 'All',
                        'fine_date' => $aDate,
                        'fine_type' => 'bunk',
                        'fine_rate' => $rateBunk,
                        'month' => $dMonth
                    ];
                    $impactedStudents[$numStid][$dMonth] = [
                        'classname' => $cName,
                        'sectionname' => $st['sectionname'] ?: 'All',
                        'rollno' => intval($st['rollno'])
                    ];
                }
            }
        }
    }

    echo " - Calculated: " . count($newFineItems) . " fine incident(s) for " . count($impactedStudents) . " student(s).\n";

    // 9. Insert into student_fine_logs
    if (!empty($newFineItems)) {
        $setupby = 'Auto-Poster Cron';
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
            $totalLogsInserted++;
        }
        $inLog->close();
    }

    // 10. Consolidate into stfinance with Paid-Row Protection
    $sYear = intval($sessionyear);
    $setupby = 'Auto-Poster Cron';

    foreach ($impactedStudents as $stId => $months) {
        $stId = intval($stId);
        if ($stId <= 0) continue;

        foreach ($months as $mon => $meta) {
            $mon = intval($mon);
            if ($mon <= 0) continue;

            // Cumulative un-waived month fine from student_fine_logs
            $sumStmt = $conn->prepare("SELECT COALESCE(SUM(fine_rate), 0) AS month_fine 
                                      FROM student_fine_logs 
                                      WHERE sccode = ? AND sessionyear = ? AND stid = ? AND month = ? AND status = 'posted'");
            $sumStmt->bind_param('isis', $sccode, $sessionyear, $stId, $mon);
            $sumStmt->execute();
            $sumRes = $sumStmt->get_result();
            $monthFineRow = $sumRes->fetch_assoc();
            $totalMonthFine = intval(round(floatval($monthFineRow['month_fine'] ?? 0)));
            $sumStmt->close();

            if ($totalMonthFine <= 0) continue;

            // Fetch existing ledger records in stfinance
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

                if ($fPaid == 0 && $fPr1 == 0 && $unpaidRow === null) {
                    $unpaidRow = $fRow;
                }
            }
            $chkFin->close();

            $netRemainingFine = max(0, $totalMonthFine - $totalAlreadyPaid);
            $stFinId = 0;

            if ($netRemainingFine > 0) {
                if ($unpaidRow !== null) {
                    // Update unpaid row without touching paid rows
                    $stFinId = intval($unpaidRow['id']);
                    $upFin = $conn->prepare("UPDATE stfinance SET 
                        amount = ?, payableamt = ?, dues = ?, modifieddate = NOW(), modifiedby = ?
                        WHERE id = ? AND sccode = ?");
                    $upFin->bind_param('iiisii', $netRemainingFine, $netRemainingFine, $netRemainingFine, $setupby, $stFinId, $sccode);
                    $upFin->execute();
                    $upFin->close();
                } else {
                    // Insert fresh split row for the new unpaid fine
                    $splitSuffix = count($finRows) > 0 ? ('-' . (count($finRows) + 1)) : '';
                    $idmon = $stId . '-' . $mon . '-' . $itemcode . $splitSuffix;

                    $inFin = $conn->prepare("INSERT INTO stfinance 
                        (sccode, sessionyear, classname, sectionname, stid, rollno, itemcode, particulareng, particularben, amount, payableamt, dues, month, idmon, setupdate, setupby)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                    $inFin->bind_param('iissiisssiiiiss', 
                        $sccode, $sYear, $meta['classname'], $meta['sectionname'], $stId, $meta['rollno'], 
                        $itemcode, $particulareng, $particularben, $netRemainingFine, $netRemainingFine, $netRemainingFine, 
                        $mon, $idmon, $setupby);
                    $inFin->execute();
                    $stFinId = $conn->insert_id;
                    $inFin->close();
                }
            }

            if ($stFinId > 0) {
                $conn->query("UPDATE student_fine_logs SET stfinance_id = $stFinId WHERE sccode = $sccode AND sessionyear = '$sessionyear' AND stid = $stId AND month = $mon AND (stfinance_id IS NULL OR stfinance_id = 0)");
            }
        }
    }

    // 11. Mark last_generated_date as toDate
    $conn->query("UPDATE fine_settings SET last_generated_date = '$toDate' WHERE sccode = $sccode AND sessionyear = '$sessionyear' AND slot = '$slot' AND scope = 'global'");
    echo " -> Completed successfully for $schoolName. last_generated_date set to $toDate.\n";
    $processedInstitutions++;
}

// Release lock
@unlink($lock_file);

// 12. Save heartbeat status for diagnostics
$cronLog = __DIR__ . '/fine_cron_status.json';
file_put_contents($cronLog, json_encode([
    'last_run_time' => date('Y-m-d H:i:s'),
    'timestamp' => time(),
    'processed_institutions' => $processedInstitutions,
    'total_logs_inserted' => $totalLogsInserted,
    'status' => 'active'
], JSON_PRETTY_PRINT));

echo "\nFinished: Processed $processedInstitutions institution(s), recorded $totalLogsInserted fine incident(s).\n";
