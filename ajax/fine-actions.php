<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/init.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$sccode = (int)($_SESSION['sccode'] ?? $sccode ?? 0);
if ($sccode <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid institution code.']);
    exit;
}

$action = trim($_POST['action'] ?? '');

// -------------------------------------------------------------
// ACTION: ADD DISASTER / WEATHER EXEMPTION EVENT
// -------------------------------------------------------------
if ($action === 'add_disaster_exemption') {
    $title = trim($_POST['title'] ?? 'প্রাকৃতিক দুর্যোগ / বিশেষ ছাড়');
    $start_date = trim($_POST['start_date'] ?? date('Y-m-d'));
    $end_date = trim($_POST['end_date'] ?? $start_date);
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    if (empty($title) || empty($start_date)) {
        echo json_encode(['status' => 'error', 'message' => 'শিরোনাম ও তারিখ প্রদান আবশ্যক।']);
        exit;
    }

    $start_dt = $start_date . ' 00:00:00';
    $end_dt = $end_date . ' 23:59:59';
    $event_type = 'holiday';
    $color = '#FF5722'; // distinct warning/orange color

    $stmt = $conn->prepare("INSERT INTO events (sccode, user_id, title, start, end, all_day, color, event_type, scope) VALUES (?, ?, ?, ?, ?, 1, ?, ?, 'institution')");
    $stmt->bind_param('iisssss', $sccode, $user_id, $title, $start_dt, $end_dt, $color, $event_type);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'দুর্যোগ / বিশেষ ছুটির দিন সফলভাবে সংরক্ষিত হয়েছে।']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ইভেন্ট সংরক্ষণ করা যায়নি: ' . $conn->error]);
    }
    $stmt->close();
    exit;
}

// -------------------------------------------------------------
// ACTION: DELETE DISASTER EXEMPTION EVENT
// -------------------------------------------------------------
if ($action === 'delete_disaster_exemption') {
    $event_id = intval($_POST['event_id'] ?? 0);
    if ($event_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ভুল ইভেন্ট আইডি।']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND sccode = ?");
    $stmt->bind_param('ii', $event_id, $sccode);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'ছাড়ের রেকর্ড সফলভাবে মুছে ফেলা হয়েছে।']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'রেকর্ড মোছা সম্ভব হয়নি।']);
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

    // 1. Fetch Fine Settings (Global and Class-wise overrides)
    $setStmt = $conn->prepare("SELECT scope, classname, absent_rate, bunk_rate, bunk_rule_type, itemcode, particulareng, particularben FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND status = 1");
    $setStmt->bind_param('iss', $sccode, $sessionyear, $slot);
    $setStmt->execute();
    $setRes = $setStmt->get_result();

    $globalSetting = [
        'absent_rate' => 0.00,
        'bunk_rate' => 0.00,
        'bunk_rule_type' => 'flat_daily',
        'itemcode' => 'FINE01',
        'particulareng' => 'Absence / Bunk Fine',
        'particularben' => 'অনুপস্থিতি ও বাঙ্ক জরিমানা'
    ];
    $classSettings = [];

    while ($sRow = $setRes->fetch_assoc()) {
        if ($sRow['scope'] === 'global') {
            $globalSetting = $sRow;
        } else if ($sRow['scope'] === 'class' && !empty($sRow['classname'])) {
            $classSettings[$sRow['classname']] = $sRow;
        }
    }
    $setStmt->close();

    // 2. Fetch Exempted Dates (Events & Holidays)
    $exemptDates = [];
    $evStmt = $conn->prepare("SELECT DATE(start) as sdate, DATE(end) as edate FROM events WHERE (sccode = ? OR sccode = 0) AND event_type IN ('holiday', 'other') AND ((start BETWEEN ? AND ?) OR (end BETWEEN ? AND ?) OR (start <= ? AND end >= ?))");
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
    $approvedLeaves = []; // [stid => [date => true]]
    $hasLeaveTable = $conn->query("SHOW TABLES LIKE 'student_leave_app'")->num_rows > 0;
    if ($hasLeaveTable) {
        $lvStmt = $conn->prepare("SELECT stid, from_date, to_date FROM student_leave_app WHERE sccode = ? AND sessionyear = ? AND status = 'approved' AND ((from_date <= ? AND to_date >= ?))");
        $lvStmt->bind_param('isss', $sccode, $sessionyear, $to_date, $from_date);
        $lvStmt->execute();
        $lvRes = $lvStmt->get_result();

        while ($lRow = $lvRes->fetch_assoc()) {
            $sId = (string)$lRow['stid'];
            $curD = strtotime($lRow['from_date']);
            $endD = strtotime($lRow['to_date'] ?: $lRow['from_date']);
            while ($curD <= $endD) {
                $approvedLeaves[$sId][date('Y-m-d', $curD)] = true;
                $curD = strtotime('+1 day', $curD);
            }
        }
        $lvStmt->close();
    }

    // 4. Query Attendance records from stattnd
    $whereClassSql = "";
    if ($target_class !== '' && $target_class !== 'all') {
        $whereClassSql = " AND classname = '" . $conn->real_escape_string($target_class) . "'";
    }

    $attSql = "SELECT stid, rollno, classname, sectionname, stname, adate, yn, bunk 
               FROM stattnd 
               WHERE sccode = ? 
                 AND sessionyear = ? 
                 AND adate BETWEEN ? AND ? 
                 AND (yn = 0 OR bunk = 1) 
                 $whereClassSql
               ORDER BY classname ASC, rollno ASC, adate ASC";

    $attStmt = $conn->prepare($attSql);
    $attStmt->bind_param('isss', $sccode, $sessionyear, $from_date, $to_date);
    $attStmt->execute();
    $attRes = $attStmt->get_result();

    $studentSummary = [];
    $classBreakdown = [];
    $totalAbsentCount = 0;
    $totalBunkCount = 0;
    $totalFineAmount = 0.00;

    while ($r = $attRes->fetch_assoc()) {
        $aDate = $r['adate'];
        $sId = (string)$r['stid'];
        $cName = $r['classname'];

        // Check if date is exempt due to holiday/disaster
        if (!empty($exemptDates[$aDate])) {
            continue; // Skip holiday/disaster
        }

        // Check if student has approved leave on this date
        if (!empty($approvedLeaves[$sId][$aDate])) {
            continue; // Skip approved leave
        }

        // Determine rate
        $rateAbsent = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['absent_rate']) : floatval($globalSetting['absent_rate']);
        $rateBunk = isset($classSettings[$cName]) ? floatval($classSettings[$cName]['bunk_rate']) : floatval($globalSetting['bunk_rate']);

        if (!isset($studentSummary[$sId])) {
            $studentSummary[$sId] = [
                'stid' => $sId,
                'rollno' => $r['rollno'],
                'classname' => $cName,
                'sectionname' => $r['sectionname'],
                'stname' => $r['stname'],
                'absent_days' => 0,
                'bunk_days' => 0,
                'absent_fine' => 0.00,
                'bunk_fine' => 0.00,
                'total_fine' => 0.00
            ];
        }

        if (!isset($classBreakdown[$cName])) {
            $classBreakdown[$cName] = [
                'classname' => $cName,
                'students_count' => 0,
                'absent_days' => 0,
                'bunk_days' => 0,
                'total_fine' => 0.00
            ];
        }

        // Absence
        if (intval($r['yn']) === 0) {
            $studentSummary[$sId]['absent_days']++;
            $studentSummary[$sId]['absent_fine'] += $rateAbsent;
            $studentSummary[$sId]['total_fine'] += $rateAbsent;

            $classBreakdown[$cName]['absent_days']++;
            $classBreakdown[$cName]['total_fine'] += $rateAbsent;
            $totalAbsentCount++;
            $totalFineAmount += $rateAbsent;
        }

        // Bunk
        if (intval($r['bunk']) === 1) {
            $studentSummary[$sId]['bunk_days']++;
            $studentSummary[$sId]['bunk_fine'] += $rateBunk;
            $studentSummary[$sId]['total_fine'] += $rateBunk;

            $classBreakdown[$cName]['bunk_days']++;
            $classBreakdown[$cName]['total_fine'] += $rateBunk;
            $totalBunkCount++;
            $totalFineAmount += $rateBunk;
        }
    }
    $attStmt->close();

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

    // If action is preview only, return preview results
    if ($action === 'preview_fines') {
        echo json_encode([
            'status' => 'success',
            'summary' => [
                'total_students' => count($studentSummary),
                'total_absent_days' => $totalAbsentCount,
                'total_bunk_days' => $totalBunkCount,
                'total_fine_amount' => round($totalFineAmount, 2),
                'exempt_days_found' => count($exemptDates),
                'date_range' => "$from_date হতে $to_date"
            ],
            'class_breakdown' => array_values($classBreakdown),
            'student_preview' => array_slice(array_values($studentSummary), 0, 100) // Top 100 for fast rendering
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: POST FINES TO STFINANCE
    // -------------------------------------------------------------
    if ($action === 'post_fines') {
        if (empty($studentSummary)) {
            echo json_encode(['status' => 'error', 'message' => 'পোস্ট করার মতো কোনো জরিমানা হিসাব পাওয়া যায়নি।']);
            exit;
        }

        $itemcode = $globalSetting['itemcode'] ?: 'FINE01';
        $particulareng = $globalSetting['particulareng'] ?: 'Absence / Bunk Fine';
        $particularben = $globalSetting['particularben'] ?: 'অনুপস্থিতি ও বাঙ্ক জরিমানা';
        $curMonth = intval(date('m', strtotime($to_date)));
        $setupby = $_SESSION['user_id'] ?? 'Admin Fine Generator';

        $postedCount = 0;
        $updatedCount = 0;

        foreach ($studentSummary as $st) {
            $stId = $st['stid'];
            $fineAmt = intval(round($st['total_fine']));
            if ($fineAmt <= 0) continue;

            $cName = $st['classname'];
            $secName = $st['sectionname'] ?: 'All';
            $rollNo = intval($st['rollno']);
            $idmon = $stId . '-' . $curMonth . '-' . $itemcode;

            // Check if fine ledger entry already exists for this student & month
            $chkFin = $conn->prepare("SELECT id, amount, payableamt, paid FROM stfinance WHERE sccode = ? AND sessionyear = ? AND stid = ? AND itemcode = ? AND month = ? LIMIT 1");
            $chkFin->bind_param('isssi', $sccode, $sessionyear, $stId, $itemcode, $curMonth);
            $chkFin->execute();
            $finRes = $chkFin->get_result();

            if ($fRow = $finRes->fetch_assoc()) {
                // If paid > 0, don't overwrite paid amount, adjust payableamt & dues
                $paid = intval($fRow['paid']);
                $newPayable = $fineAmt;
                $newDues = max(0, $newPayable - $paid);

                $upFin = $conn->prepare("UPDATE stfinance SET 
                    amount = ?, payableamt = ?, dues = ?, modifieddate = NOW(), modifiedby = ?
                    WHERE id = ? AND sccode = ?");
                $upFin->bind_param('iiisii', $fineAmt, $newPayable, $newDues, $setupby, $fRow['id'], $sccode);
                $upFin->execute();
                $upFin->close();
                $updatedCount++;
            } else {
                // Insert new fine entry
                $inFin = $conn->prepare("INSERT INTO stfinance 
                    (sccode, sessionyear, classname, sectionname, stid, rollno, itemcode, particulareng, particularben, amount, payableamt, dues, month, idmon, setupdate, setupby)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                $inFin->bind_param('issssisssiiiiss', 
                    $sccode, $sessionyear, $cName, $secName, $stId, $rollNo, 
                    $itemcode, $particulareng, $particularben, $fineAmt, $fineAmt, $fineAmt, 
                    $curMonth, $idmon, $setupby);
                $inFin->execute();
                $inFin->close();
                $postedCount++;
            }
            $chkFin->close();
        }

        // Update last_generated_date in fine_settings
        $conn->query("UPDATE fine_settings SET last_generated_date = '$to_date' WHERE sccode = '$sccode' AND sessionyear = '$sessionyear' AND slot = '$slot' AND scope = 'global'");

        echo json_encode([
            'status' => 'success',
            'message' => "মোট " . count($studentSummary) . " জন শিক্ষার্থীর জরিমানা সফলভাবে stfinance লেজারে পোস্ট করা হয়েছে। (নতুন: $postedCount, আপডেট: $updatedCount)"
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'অজ্ঞাত অ্যাকশন অনুরোধ।']);
exit;
