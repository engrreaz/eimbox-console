<?php
/**
 * EIMBox REST API — Teacher & Staff Attendance Push Endpoint
 * Route: POST /api/v1/attendance/teacher-push.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$adate = trim($input['date'] ?? date('Y-m-d'));
$records = $input['records'] ?? [];
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Staff Bridge';

if ($sccode <= 0 || empty($adate)) {
    api_response('error', 'sccode and date are required.', null, 400);
}

if (!is_array($records) || empty($records)) {
    api_response('error', 'Records array cannot be empty.', null, 400);
}

$conn->begin_transaction();

try {
    $processedCount = 0;

    foreach ($records as $rec) {
        $rawTid = $rec['tid'] ?? '';
        $tid = intval(preg_replace('/[^0-9]/', '', (string)$rawTid));
        if ($tid <= 0) continue;

        $realin = !empty($rec['realin']) ? trim($rec['realin']) : (!empty($rec['in_time']) ? trim($rec['in_time']) : null);
        $realout = !empty($rec['realout']) ? trim($rec['realout']) : (!empty($rec['out_time']) ? trim($rec['out_time']) : null);
        $statusin = trim($rec['statusin'] ?? 'Present');
        $statusout = trim($rec['statusout'] ?? '');

        // Check existing record
        $chkStmt = $conn->prepare("SELECT id FROM teacherattnd WHERE sccode = ? AND tid = ? AND adate = ? LIMIT 1");
        $chkStmt->bind_param('iis', $sccode, $tid, $adate);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($chkRes) {
            $upStmt = $conn->prepare("UPDATE teacherattnd SET 
                realin = COALESCE(?, realin), 
                realout = COALESCE(?, realout), 
                statusin = ?, 
                statusout = ?, 
                entryby = ? 
                WHERE id = ?");
            $upStmt->bind_param('sssssi', $realin, $realout, $statusin, $statusout, $entryby, $chkRes['id']);
            $upStmt->execute();
            $upStmt->close();
        } else {
            $insStmt = $conn->prepare("INSERT INTO teacherattnd (
                tid, sccode, adate, realin, realout, statusin, statusout, entryby, entrytime
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insStmt->bind_param('iissssss', 
                $tid, $sccode, $adate, $realin, $realout, $statusin, $statusout, $entryby
            );
            $insStmt->execute();
            $insStmt->close();
        }

        $processedCount++;
    }

    $conn->commit();

    api_response('success', 'Teacher attendance recorded successfully.', [
        'processed_count' => $processedCount,
        'sccode' => $sccode,
        'date' => $adate
    ]);

} catch (Exception $e) {
    $conn->rollback();
    api_response('error', 'Teacher attendance recording failed: ' . $e->getMessage(), null, 500);
}
