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

    $chkStmt = $conn->prepare("SELECT id FROM teacherattnd WHERE sccode = ? AND tid = ? AND adate = ? LIMIT 1");
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

        $status = strtolower(trim($item['status'] ?? 'present'));
        $realin = !empty($item['realin']) ? trim($item['realin']) : null;
        $realout = !empty($item['realout']) ? trim($item['realout']) : null;
        $detect = !empty($item['detectin']) ? trim($item['detectin']) : 'Manual';

        // Normalize time strings to HH:MM:SS format if provided
        if (!empty($realin) && strlen($realin) == 5) {
            $realin .= ':00';
        }
        if (!empty($realout) && strlen($realout) == 5) {
            $realout .= ':00';
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

        // Check if existing record exists
        $chkStmt->bind_param("iis", $sccode, $tid, $adate);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result()->fetch_assoc();

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
        'message' => "Successfully saved attendance for {$saved_count} teachers.",
        'date' => $adate,
        'saved_count' => $saved_count
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
