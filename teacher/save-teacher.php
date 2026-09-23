<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tid'])) {
    
    $tid      = trim($_POST['tid'] ?? '');
    $tname    = trim($_POST['tname'] ?? '');
    $position = trim($_POST['position'] ?? 'Asstt. Teacher');
    $ranks    = intval($_POST['ranks'] ?? 22);
    $slots    = trim($_POST['slots'] ?? 'School');
    $mobile   = trim($_POST['mobile'] ?? '');

    if (empty($tid) || empty($tname) || empty($sccode)) {
        echo json_encode(['status' => 'error', 'message' => 'Teacher ID, Name and School are required']);
        exit();
    }

    // Check if tid already exists for this school
    $chk_stmt = $conn->prepare("SELECT id FROM teacher WHERE tid = ? AND sccode = ?");
    $chk_stmt->bind_param("si", $tid, $sccode);
    $chk_stmt->execute();
    if ($chk_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => "Teacher ID {$tid} already exists!"]);
        exit();
    }

    // SL বের করা
    $sl_query = "SELECT MAX(sl) as max_sl FROM teacher WHERE sccode = '$sccode'";
    $sl_res = $conn->query($sl_query);
    $sl_row = $sl_res->fetch_assoc();
    $next_sl = ($sl_row['max_sl'] ?? 0) + 1;

    $modifieddate = date('Y-m-d H:i:s');
    $curin = '09:00:00';
    $curout = '16:00:00';
    $status = '1';

    $stmt = $conn->prepare("INSERT INTO teacher (sl, tid, tname, position, ranks, slots, mobile, curin, curout, sccode, status, modifieddate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssissssiis", $next_sl, $tid, $tname, $position, $ranks, $slots, $mobile, $curin, $curout, $sccode, $status, $modifieddate);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Teacher saved successfully', 'tid' => $tid]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
exit();