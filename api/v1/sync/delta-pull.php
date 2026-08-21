<?php
/**
 * EIMBox REST API — Delta Sync Pull Endpoint
 * Route: GET /api/v1/sync/delta-pull.php
 * Query Params: ?sccode={sccode}&last_sync_timestamp={YYYY-MM-DD HH:MM:SS}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Bearer Token
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$lastSync = trim($_GET['last_sync_timestamp'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

if (empty($lastSync) || !strtotime($lastSync)) {
    // If no valid timestamp, default to beginning of today
    $lastSync = date('Y-m-d 00:00:00');
}

$currentSyncTimestamp = date('Y-m-d H:i:s');

// 1. Pull Delta Students
$studentsDelta = [];
$stStmt = $conn->prepare("SELECT s.stid, s.stnameeng, s.stnameben, s.guarmobile, s.modifieddate,
si.sessionyear, si.classname, si.sectionname, si.rollno
FROM students s
LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
WHERE s.sccode = ? AND (s.modifieddate >= ? OR s.doa >= ?)
ORDER BY s.modifieddate DESC LIMIT 500");
$stStmt->bind_param('iss', $sccode, $lastSync, $lastSync);
$stStmt->execute();
$stRes = $stStmt->get_result();
while ($row = $stRes->fetch_assoc()) {
    $studentsDelta[] = [
        'stid' => (string)$row['stid'],
        'sessionyear' => intval($row['sessionyear'] ?? date('Y')),
        'name_eng' => $row['stnameeng'],
        'name_ben' => $row['stnameben'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'rollno' => intval($row['rollno'] ?? 0),
        'guardian_mobile' => $row['guarmobile'],
        'modified_at' => $row['modifieddate']
    ];
}
$stStmt->close();

// 2. Pull Delta Marks
$marksDelta = [];
$mStmt = $conn->prepare("SELECT id, sessionyear, exam, classname, sectionname, subject AS subcode, fullmark, stid, subj, obj, pra, ca, markobt, on100, gp, gl, entrydate, modifieddate 
FROM stmark 
WHERE sccode = ? AND (modifieddate >= ? OR entrydate >= ?)
ORDER BY id DESC LIMIT 1000");
$mStmt->bind_param('iss', $sccode, $lastSync, $lastSync);
$mStmt->execute();
$mRes = $mStmt->get_result();
while ($row = $mRes->fetch_assoc()) {
    $marksDelta[] = [
        'id' => intval($row['id']),
        'sessionyear' => intval($row['sessionyear']),
        'exam' => $row['exam'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'subcode' => intval($row['subcode']),
        'fullmark' => intval($row['fullmark']),
        'stid' => (string)$row['stid'],
        'subj' => floatval($row['subj']),
        'obj' => floatval($row['obj']),
        'pra' => floatval($row['pra']),
        'ca' => floatval($row['ca']),
        'markobt' => floatval($row['markobt']),
        'on100' => floatval($row['on100']),
        'gp' => floatval($row['gp']),
        'gl' => $row['gl'],
        'updated_at' => $row['modifieddate'] ?: $row['entrydate']
    ];
}
$mStmt->close();

// 3. Pull Delta Payment Receipts
$paymentsDelta = [];
$pStmt = $conn->prepare("SELECT id, sessionyear, classname, sectionname, stid, rollno, prno, prdate, amount, entryby, entrytime, collection_media 
FROM stpr 
WHERE sccode = ? AND (entrytime >= ? OR modifieddate >= ?)
ORDER BY id DESC LIMIT 500");
$pStmt->bind_param('iss', $sccode, $lastSync, $lastSync);
$pStmt->execute();
$pRes = $pStmt->get_result();
while ($row = $pRes->fetch_assoc()) {
    $paymentsDelta[] = [
        'id' => intval($row['id']),
        'sessionyear' => intval($row['sessionyear']),
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'stid' => (string)$row['stid'],
        'rollno' => intval($row['rollno']),
        'prno' => (string)$row['prno'],
        'prdate' => $row['prdate'],
        'amount' => floatval($row['amount']),
        'entryby' => $row['entryby'],
        'entrytime' => $row['entrytime'],
        'collection_media' => $row['collection_media'] ?? 'Cash'
    ];
}
$pStmt->close();

// 4. Pull Delta Attendance
$attendanceDelta = [];
$attStmt = $conn->prepare("SELECT id, sessionyear, stid, adate, yn, intime, outtime, classname, sectionname, rollno, modifieddate, entrytime 
FROM stattnd 
WHERE sccode = ? AND (entrytime >= ? OR modifieddate >= ?)
ORDER BY id DESC LIMIT 1000");
$attStmt->bind_param('iss', $sccode, $lastSync, $lastSync);
$attStmt->execute();
$attRes = $attStmt->get_result();
while ($row = $attRes->fetch_assoc()) {
    $attendanceDelta[] = [
        'id' => intval($row['id']),
        'sessionyear' => intval($row['sessionyear']),
        'stid' => (string)$row['stid'],
        'date' => $row['adate'],
        'status' => intval($row['yn']),
        'in_time' => $row['intime'],
        'out_time' => $row['outtime'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'rollno' => intval($row['rollno']),
        'updated_at' => $row['modifieddate'] ?: $row['entrytime']
    ];
}
$attStmt->close();

api_response('success', 'Delta sync records pulled successfully.', [
    'sccode' => $sccode,
    'last_sync_timestamp' => $lastSync,
    'current_sync_timestamp' => $currentSyncTimestamp,
    'counts' => [
        'students' => count($studentsDelta),
        'marks' => count($marksDelta),
        'payments' => count($paymentsDelta),
        'attendance' => count($attendanceDelta)
    ],
    'changes' => [
        'students' => $studentsDelta,
        'marks' => $marksDelta,
        'payments' => $paymentsDelta,
        'attendance' => $attendanceDelta
    ]
]);
