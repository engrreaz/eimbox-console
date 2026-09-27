<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$conn = db_connect();

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$entryby = $_SESSION['user_id'] ?? 'Admin';

// Read JSON input or POST form data
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

$adate = trim($json_data['date'] ?? ($_POST['date'] ?? date('Y-m-d')));
$records = $json_data['records'] ?? ($_POST['records'] ?? []);

if (empty($adate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $adate)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid attendance date.']);
    exit;
}

if (!is_array($records) || empty($records)) {
    echo json_encode(['status' => 'error', 'message' => 'No attendance records provided.']);
    exit;
}

try {
    $conn->begin_transaction();

    $saved_count = 0;

    $chkStmt = $conn->prepare("SELECT id, realin, realout FROM teacherattnd WHERE sccode = ? AND tid = ? AND adate = ? LIMIT 1");
    $upStmt  = $conn->prepare("UPDATE teacherattnd SET 
                                realin = ?, 
                                realout = ?, 
                                statusin = ?, 
                                statusout = ?, 
                                detectin = ?, 
                                detectout = ?, 
                                entryby = ?, 
                                modifieddate = NOW() 
                                WHERE id = ? AND sccode = ?");
    $insStmt = $conn->prepare("INSERT INTO teacherattnd (
                                sccode, tid, adate, realin, realout, statusin, statusout, detectin, detectout, entryby, entrytime, modifieddate
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    foreach ($records as $item) {
        $tid = intval($item['tid'] ?? 0);
        if ($tid <= 0) continue;

        $status = strtolower(trim($item['status'] ?? 'unmarked'));
        $raw_realin = trim($item['realin'] ?? '');
        $raw_realout = trim($item['realout'] ?? '');
        $detect = !empty($item['detectin']) ? trim($item['detectin']) : 'Manual';

        // Check if existing record exists in DB
        $chkStmt->bind_param("iis", $sccode, $tid, $adate);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();

        // If status is 'unmarked' / empty and no realin/realout:
        if ($status === 'unmarked' || $status === 'none') {
            if ($chkRes) {
                // Remove or mark as absent if unmarked
                $delStmt = $conn->prepare("DELETE FROM teacherattnd WHERE id = ? AND sccode = ?");
                $delStmt->bind_param("ii", $chkRes['id'], $sccode);
                $delStmt->execute();
                $delStmt->close();
            }
            continue;
        }

        // Normalize time strings to HH:MM:SS format
        $realin = !empty($raw_realin) ? (strlen($raw_realin) == 5 ? ($raw_realin . ':00') : $raw_realin) : null;
        $realout = !empty($raw_realout) ? (strlen($raw_realout) == 5 ? ($raw_realout . ':00') : $raw_realout) : null;

        // If updating afternoon Out-Time and user didn't change/provide In-Time, preserve existing In-Time from DB
        if (empty($realin) && $chkRes && !empty($chkRes['realin']) && ($status === 'present' || $status === 'late')) {
            $realin = $chkRes['realin'];
        }
        // If updating morning In-Time and user didn't provide Out-Time, preserve existing Out-Time from DB if any
        if (empty($realout) && $chkRes && !empty($chkRes['realout']) && ($status === 'present' || $status === 'late')) {
            $realout = $chkRes['realout'];
        }

        // Determine status labels
        if ($status === 'absent') {
            $statusin = 'Absent';
            $statusout = 'Absent';
            $realin = null;
            $realout = null;
        } elseif ($status === 'leave') {
            $statusin = 'Leave';
            $statusout = 'Leave';
            $realin = null;
            $realout = null;
        } elseif ($status === 'late') {
            $statusin = 'Late';
            $statusout = !empty($realout) ? 'Normal' : '';
        } else { // present / on-time
            $statusin = 'Normal';
            $statusout = !empty($realout) ? 'Normal' : '';
        }

        if ($chkRes) {
            $rec_id = intval($chkRes['id']);
            $upStmt->bind_param(
                "sssssssii",
                $realin,
                $realout,
                $statusin,
                $statusout,
                $detect,
                $detect,
                $entryby,
                $rec_id,
                $sccode
            );
            $upStmt->execute();
        } else {
            $insStmt->bind_param(
                "iissssssss",
                $sccode,
                $tid,
                $adate,
                $realin,
                $realout,
                $statusin,
                $statusout,
                $detect,
                $detect,
                $entryby
            );
            $insStmt->execute();
        }

        $saved_count++;
    }

    $chkStmt->close();
    $upStmt->close();
    $insStmt->close();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => "Attendance updated successfully for {$saved_count} records.",
        'date' => $adate,
        'saved_count' => $saved_count
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
