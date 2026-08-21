<?php
/**
 * EIMBox REST API — Attendance Push Endpoint (Biometric & Manual Daily Attendance)
 * Route: POST /api/v1/attendance/attendance-push.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$sessionyear = intval($input['sessionyear'] ?? date('Y'));
$adate = trim($input['date'] ?? $input['adate'] ?? date('Y-m-d'));
$classname = trim($input['classname'] ?? '');
$sectionname = trim($input['sectionname'] ?? '');
$records = $input['records'] ?? [];
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Attendance Bridge';

if ($sccode <= 0 || empty($adate)) {
    api_response('error', 'sccode and date are required.', null, 400);
}

if (!is_array($records) || empty($records)) {
    api_response('error', 'Records array cannot be empty.', null, 400);
}

$conn->begin_transaction();

try {
    $recordedCount = 0;

    foreach ($records as $rec) {
        $stid = trim($rec['stid'] ?? '');
        if (empty($stid)) continue;

        $yn = isset($rec['status']) ? intval($rec['status']) : (isset($rec['yn']) ? intval($rec['yn']) : 1);
        $intime = !empty($rec['in_time']) ? trim($rec['in_time']) : (!empty($rec['intime']) ? trim($rec['intime']) : null);
        $outtime = !empty($rec['out_time']) ? trim($rec['out_time']) : (!empty($rec['outtime']) ? trim($rec['outtime']) : null);
        $rollno = intval($rec['rollno'] ?? 0);
        $recClass = trim($rec['classname'] ?? $classname);
        $recSec = trim($rec['sectionname'] ?? $sectionname);

        // Check if attendance already recorded for this student on this date
        $chkStmt = $conn->prepare("SELECT id FROM stattnd WHERE sccode = ? AND stid = ? AND adate = ? LIMIT 1");
        $chkStmt->bind_param('iss', $sccode, $stid, $adate);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($chkRes) {
            // Update
            $upStmt = $conn->prepare("UPDATE stattnd SET 
                yn = ?, 
                intime = COALESCE(?, intime), 
                outtime = COALESCE(?, outtime), 
                classname = COALESCE(NULLIF(?, ''), classname), 
                sectionname = COALESCE(NULLIF(?, ''), sectionname), 
                rollno = COALESCE(NULLIF(?, 0), rollno), 
                entryby = ?, 
                modifieddate = NOW()
                WHERE id = ?");
            $upStmt->bind_param('issssisi', $yn, $intime, $outtime, $recClass, $recSec, $rollno, $entryby, $chkRes['id']);
            $upStmt->execute();
            $upStmt->close();
        } else {
            // Insert
            $insStmt = $conn->prepare("INSERT INTO stattnd (
                sccode, sessionyear, stid, adate, yn, intime, outtime, classname, sectionname, rollno, entryby, entrytime
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insStmt->bind_param('iississssds', 
                $sccode, $sessionyear, $stid, $adate, $yn, $intime, $outtime, $recClass, $recSec, $rollno, $entryby
            );
            $insStmt->execute();
            $insStmt->close();
        }

        $recordedCount++;
    }

    $conn->commit();

    api_response('success', 'Attendance recorded successfully.', [
        'recorded_count' => $recordedCount,
        'sccode' => $sccode,
        'date' => $adate,
        'sessionyear' => $sessionyear
    ]);

} catch (Exception $e) {
    $conn->rollback();
    api_response('error', 'Attendance push failed: ' . $e->getMessage(), null, 500);
}
