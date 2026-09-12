<?php
/**
 * EIMBox REST API — Unified Mobile & Desktop Client Data Sync Endpoint
 * Route: POST /api/v1/sync/sync_data.php
 * 
 * Handles incoming offline updates (attendance, marks, cashbook)
 * and returns delta updates for master and transactional tables scoped strictly
 * by institution (sccode) and active academic session (sessionyear).
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Request
$user = function_exists('api_authenticate_request') ? api_authenticate_request() : authenticate_token($conn);
$tokenSccode = (int)($user['sccode'] ?? 0);

if (!isset($conn) || !$conn) {
    $conn = function_exists('api_get_db_connection') ? api_get_db_connection() : db_connect();
}

$input = get_api_input();

// Resolve sccode (Header X-SCCode or token)
$headerSccode = (int)($_SERVER['HTTP_X_SCCODE'] ?? 0);
$sccode = $headerSccode > 0 ? $headerSccode : $tokenSccode;

if ($sccode <= 0) {
    api_response('error', 'Valid institution code (sccode) is required.', null, 400);
}

$lastSync = trim($input['lastSync'] ?? $input['last_sync'] ?? '2000-01-01 00:00:00');
if (!strtotime($lastSync)) {
    $lastSync = '2000-01-01 00:00:00';
}

$sessionYear = trim($input['sessionyear'] ?? $input['session'] ?? '');

// Auto-resolve active session from sessionyear table if not passed
if (empty($sessionYear)) {
    $syStmt = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode = ? AND active = 1 ORDER BY syear DESC LIMIT 1");
    if ($syStmt) {
        $syStmt->bind_param("i", $sccode);
        $syStmt->execute();
        $syRes = $syStmt->get_result();
        if ($syRow = $syRes->fetch_assoc()) {
            $sessionYear = trim($syRow['syear'] ?? '');
        }
        $syStmt->close();
    }
}
if (empty($sessionYear)) {
    $sessionYear = date('Y');
}

$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Mobile Sync Engine';
$serverTime = date('Y-m-d H:i:s');

// =========================================================================
// 1. PROCESS INCOMING OFFLINE ATTENDANCE UPDATES
// =========================================================================
$attendanceUpdates = $input['attendance_updates'] ?? [];
if (!empty($attendanceUpdates) && is_array($attendanceUpdates)) {
    foreach ($attendanceUpdates as $rec) {
        $stid = trim($rec['stid'] ?? '');
        $adate = trim($rec['adate'] ?? '');
        if (empty($stid) || empty($adate)) continue;

        $yn = isset($rec['yn']) ? (int)$rec['yn'] : 1;
        $bunk = isset($rec['bunk']) ? (int)$rec['bunk'] : 0;
        $p1 = (int)($rec['period1'] ?? 0);
        $p2 = (int)($rec['period2'] ?? 0);
        $p3 = (int)($rec['period3'] ?? 0);
        $p4 = (int)($rec['period4'] ?? 0);
        $p5 = (int)($rec['period5'] ?? 0);
        $p6 = (int)($rec['period6'] ?? 0);
        $p7 = (int)($rec['period7'] ?? 0);
        $p8 = (int)($rec['period8'] ?? 0);
        $cls = trim($rec['classname'] ?? '');
        $sec = trim($rec['sectionname'] ?? '');
        $roll = (int)($rec['rollno'] ?? 0);
        $stname = trim($rec['stname'] ?? '');
        $sy = !empty($rec['sessionyear']) ? trim($rec['sessionyear']) : $sessionYear;

        $chkStmt = $conn->prepare("SELECT id FROM stattnd WHERE sccode = ? AND stid = ? AND adate = ? LIMIT 1");
        $chkStmt->bind_param('iss', $sccode, $stid, $adate);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($chkRes) {
            $upStmt = $conn->prepare("UPDATE stattnd SET 
                yn = ?, bunk = ?, period1 = ?, period2 = ?, period3 = ?, period4 = ?,
                period5 = ?, period6 = ?, period7 = ?, period8 = ?,
                entryby = ?, modifieddate = NOW()
                WHERE id = ?");
            $upStmt->bind_param('iiiiiiiiiisi', $yn, $bunk, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $entryby, $chkRes['id']);
            $upStmt->execute();
            $upStmt->close();
        } else {
            $insStmt = $conn->prepare("INSERT INTO stattnd (
                sccode, sessionyear, stid, adate, yn, bunk, period1, period2, period3, period4,
                period5, period6, period7, period8, classname, sectionname, rollno, entryby, entrytime, modifieddate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $insStmt->bind_param('isssiiiiiiiiiissis', 
                $sccode, $sy, $stid, $adate, $yn, $bunk, $p1, $p2, $p3, $p4,
                $p5, $p6, $p7, $p8, $cls, $sec, $roll, $entryby
            );
            $insStmt->execute();
            $insStmt->close();
        }
    }
}

// =========================================================================
// 2. PROCESS INCOMING OFFLINE MARKS UPDATES
// =========================================================================
$marksUpdates = $input['marks_updates'] ?? [];
if (!empty($marksUpdates) && is_array($marksUpdates)) {
    foreach ($marksUpdates as $m) {
        $stid = trim($m['stid'] ?? '');
        $examid = (int)($m['examid'] ?? 0);
        $subcode = (int)($m['subject'] ?? 0);
        if (empty($stid) || $subcode <= 0) continue;

        $sy = !empty($m['sessionyear']) ? trim($m['sessionyear']) : $sessionYear;
        $slot = trim($m['slot'] ?? '');
        $cls = trim($m['classname'] ?? '');
        $sec = trim($m['sectionname'] ?? '');
        $fullmark = (float)($m['fullmark'] ?? 100);
        $ctest = (float)($m['ctest'] ?? 0);
        $mtest = (float)($m['mtest'] ?? 0);
        $subj = (float)($m['subj'] ?? 0);
        $obj = (float)($m['obj'] ?? 0);
        $pra = (float)($m['pra'] ?? 0);
        $ca = (float)($m['ca'] ?? 0);
        $markobt = (float)($m['markobt'] ?? ($subj + $obj + $pra + $ca));
        $on100 = (float)($m['on100'] ?? ($fullmark > 0 ? round(($markobt / $fullmark) * 100, 2) : 0));
        $gp = (float)($m['gp'] ?? 0);
        $gl = trim($m['gl'] ?? '');

        $chkStmt = $conn->prepare("SELECT id FROM stmark WHERE sccode = ? AND sessionyear = ? AND stid = ? AND exam = ? AND subject = ? LIMIT 1");
        $chkStmt->bind_param('issii', $sccode, $sy, $stid, $examid, $subcode);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($chkRes) {
            $upStmt = $conn->prepare("UPDATE stmark SET 
                slot = ?, fullmark = ?, ctest = ?, mtest = ?, subj = ?, obj = ?, pra = ?, ca = ?,
                markobt = ?, on100 = ?, gp = ?, gl = ?, entryby = ?, modifieddate = NOW()
                WHERE id = ?");
            $upStmt->bind_param('sdddddddddsdsi', 
                $slot, $fullmark, $ctest, $mtest, $subj, $obj, $pra, $ca,
                $markobt, $on100, $gp, $gl, $entryby, $chkRes['id']
            );
            $upStmt->execute();
            $upStmt->close();
        } else {
            $insStmt = $conn->prepare("INSERT INTO stmark (
                sccode, sessionyear, slot, exam, classname, sectionname, subject, fullmark,
                stid, ctest, mtest, subj, obj, pra, ca, markobt, on100, gp, gl, entrydate, entryby, modifieddate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())");
            $insStmt->bind_param('issssiisddddddddsss', 
                $sccode, $sy, $slot, $examid, $cls, $sec, $subcode, $fullmark,
                $stid, $ctest, $mtest, $subj, $obj, $pra, $ca, $markobt, $on100, $gp, $gl, $entryby
            );
            $insStmt->execute();
            $insStmt->close();
        }
    }
}

// =========================================================================
// 3. PROCESS INCOMING OFFLINE CASHBOOK UPDATES
// =========================================================================
$cashbookUpdates = $input['cashbook_updates'] ?? [];
if (!empty($cashbookUpdates) && is_array($cashbookUpdates)) {
    foreach ($cashbookUpdates as $cb) {
        $date = trim($cb['date'] ?? date('Y-m-d'));
        $category = trim($cb['category'] ?? 'General');
        $description = trim($cb['description'] ?? '');
        $amount = (float)($cb['amount'] ?? 0);
        $type = trim($cb['type'] ?? 'Income');
        $slots = trim($cb['slots'] ?? '');
        $sy = !empty($cb['sessionyear']) ? trim($cb['sessionyear']) : $sessionYear;
        $localId = (int)($cb['local_id'] ?? 0);

        $income = (strcasecmp($type, 'Income') === 0) ? $amount : 0;
        $expenditure = (strcasecmp($type, 'Expense') === 0 || strcasecmp($type, 'Expenditure') === 0) ? $amount : 0;

        $month = (int)date('m', strtotime($date));
        $year = (int)date('Y', strtotime($date));

        $insStmt = $conn->prepare("INSERT INTO cashbook (
            sccode, sessionyear, month, year, slots, date, category, particulars, income, expenditure, amount, type, entryby, entrytime, modifieddate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $insStmt->bind_param('isiissssdddss',
            $sccode, $sy, $month, $year, $slots, $date, $category, $description, $income, $expenditure, $amount, $type, $entryby
        );
        $insStmt->execute();
        $insStmt->close();
    }
}

// =========================================================================
// 4. FETCH OUTGOING DELTA UPDATES SCOPED BY sccode & sessionyear
// =========================================================================
$updates = [];

// Helper function to query rows modified since $lastSync
function fetchDeltaRows($conn, $sql, $types = "", $params = []) {
    $rows = [];
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
    return $rows;
}

// 1. Areas
$updates['areas'] = fetchDeltaRows($conn, 
    "SELECT * FROM areas WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 1000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 2. Students & SessionInfo
$updates['students'] = fetchDeltaRows($conn, 
    "SELECT s.*, si.classname, si.sectionname, si.rollno, si.sessionyear
     FROM students s
     INNER JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
     WHERE s.sccode = ? AND si.sessionyear = ? AND (s.modifieddate >= ? OR s.modifieddate IS NULL) LIMIT 2000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

$updates['sessioninfo'] = fetchDeltaRows($conn, 
    "SELECT * FROM sessioninfo WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 2000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 3. Class Schedule (allows sccode=0 for system defaults)
$updates['classschedule'] = fetchDeltaRows($conn, 
    "SELECT * FROM classschedule WHERE (sccode = ? OR sccode = 0) AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 500",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 4. Subsetups
$updates['subsetup'] = fetchDeltaRows($conn, 
    "SELECT * FROM subsetup WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 1000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 5. Exams (allows sccode=0 for default exam catalog)
$updates['exams'] = fetchDeltaRows($conn, 
    "SELECT * FROM examlist WHERE (sccode = ? OR sccode = 0) AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 500",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 6. Slots (STRICT: Never sccode=0)
$updates['slots'] = fetchDeltaRows($conn, 
    "SELECT * FROM slots WHERE sccode = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 200",
    "is", [$sccode, $lastSync]
);

// 7. Finance Setup (stfinance)
$updates['financesetup'] = fetchDeltaRows($conn, 
    "SELECT * FROM stfinance WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 2000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 8. Cashbook
$updates['cashbook'] = fetchDeltaRows($conn, 
    "SELECT * FROM cashbook WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 1000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 9. Class Routine (clsroutine)
$updates['clsroutine'] = fetchDeltaRows($conn, 
    "SELECT * FROM clsroutine WHERE sccode = ? AND sessionyear = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 1000",
    "iss", [$sccode, $sessionYear, $lastSync]
);

// 10. Teacher Biometric Attendance
$updates['teacherattnd'] = fetchDeltaRows($conn, 
    "SELECT * FROM teacherattnd WHERE sccode = ? AND (adate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR modifieddate >= ?) LIMIT 1000",
    "is", [$sccode, $lastSync]
);

// 11. Teachers Directory
$updates['teacher'] = fetchDeltaRows($conn, 
    "SELECT * FROM teacher WHERE sccode = ? AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 500",
    "is", [$sccode, $lastSync]
);

// 12. GPA Scale (allows sccode=0)
$updates['gpa'] = fetchDeltaRows($conn, 
    "SELECT * FROM gpa WHERE (sccode = ? OR sccode = 0) AND (modifieddate >= ? OR modifieddate IS NULL) LIMIT 200",
    "is", [$sccode, $lastSync]
);

$responsePayload = [
    'serverTime' => $serverTime,
    'sessionYear' => $sessionYear,
    'updates' => $updates
];

api_response('success', 'Data synchronized successfully.', $responsePayload, 200);
