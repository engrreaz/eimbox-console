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
    $user_id = $_SESSION['user_id'] ?? 'User';
    $user_role = $_SESSION['user_role'] ?? 'staff';

    // -------------------------------------------------------------
    // HELPER: SYNC STFINANCE AFTER LEAVE APPROVAL
    // -------------------------------------------------------------
    function sync_leave_fine_waiver($sccode, $sessionyear, $stId, $dateFrom, $dateTo, $conn, $setupby) {
        $stId = intval($stId);
        if ($stId <= 0) return;

        // 1. Mark logs as waived for the approved leave dates
        $wStmt = $conn->prepare("UPDATE student_fine_logs SET status = 'waived', updated_at = NOW() 
                                 WHERE sccode = ? AND stid = ? AND fine_date BETWEEN ? AND ? AND status = 'posted'");
        $wStmt->bind_param('iiss', $sccode, $stId, $dateFrom, $dateTo);
        $wStmt->execute();
        $wStmt->close();

        // 2. Determine affected months
        $mStart = intval(date('m', strtotime($dateFrom)));
        $mEnd = intval(date('m', strtotime($dateTo)));

        $setStmt = $conn->prepare("SELECT itemcode FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND scope = 'global' AND status = 1 LIMIT 1");
        $setStmt->bind_param('is', $sccode, $sessionyear);
        $setStmt->execute();
        $sRow = $setStmt->get_result()->fetch_assoc();
        $itemcode = (!empty($sRow['itemcode']) && $sRow['itemcode'] !== 'FINE01') ? $sRow['itemcode'] : 'FINE01';
        $setStmt->close();

        $sYear = intval($sessionyear);

        for ($mon = $mStart; $mon <= $mEnd; $mon++) {
            // Calculate active month fine
            $sumStmt = $conn->prepare("SELECT COALESCE(SUM(fine_rate), 0) AS month_fine 
                                      FROM student_fine_logs 
                                      WHERE sccode = ? AND sessionyear = ? AND stid = ? AND month = ? AND status = 'posted'");
            $sumStmt->bind_param('isis', $sccode, $sessionyear, $stId, $mon);
            $sumStmt->execute();
            $monthFineRow = $sumStmt->get_result()->fetch_assoc();
            $totalMonthFine = intval(round(floatval($monthFineRow['month_fine'] ?? 0)));
            $sumStmt->close();

            // Check existing stfinance records
            $totalAlreadyPaid = 0;
            $unpaidRow = null;

            $chkFin = $conn->prepare("SELECT id, paid, pr1 FROM stfinance WHERE sccode = ? AND sessionyear = ? AND stid = ? AND itemcode = ? AND month = ? ORDER BY id ASC");
            $chkFin->bind_param('iiisi', $sccode, $sYear, $stId, $itemcode, $mon);
            $chkFin->execute();
            $finRes = $chkFin->get_result();
            while ($fRow = $finRes->fetch_assoc()) {
                $fPaid = intval($fRow['paid']);
                $fPr1 = intval($fRow['pr1']);
                $totalAlreadyPaid += $fPaid;
                if ($fPaid == 0 && $fPr1 == 0 && $unpaidRow === null) {
                    $unpaidRow = $fRow;
                }
            }
            $chkFin->close();

            $netRemainingFine = max(0, $totalMonthFine - $totalAlreadyPaid);

            if ($unpaidRow !== null) {
                $stFinId = intval($unpaidRow['id']);
                $upFin = $conn->prepare("UPDATE stfinance SET amount = ?, payableamt = ?, dues = ?, modifieddate = NOW(), modifiedby = ? WHERE id = ? AND sccode = ?");
                $upFin->bind_param('iiisii', $netRemainingFine, $netRemainingFine, $netRemainingFine, $setupby, $stFinId, $sccode);
                $upFin->execute();
                $upFin->close();
            }
        }
    }

    // -------------------------------------------------------------
    // ACTION: GET SECTIONS FOR CLASS
    // -------------------------------------------------------------
    if ($action === 'get_sections') {
        $classname = trim($_POST['classname'] ?? '');
        $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));

        $sections = [];
        if (!empty($classname)) {
            $stmt = $conn->prepare("SELECT DISTINCT sectionname FROM sessioninfo 
                                    WHERE sccode = ? AND classname = ? 
                                      AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL) 
                                      AND sectionname IS NOT NULL AND sectionname != '' 
                                    ORDER BY sectionname ASC");
            $stmt->bind_param('iss', $sccode, $classname, $sessionyear);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $sections[] = $r['sectionname'];
            }
            $stmt->close();
        }

        echo json_encode(['status' => 'success', 'sections' => $sections]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: GET ROLLS / STUDENTS FOR CLASS & SECTION
    // -------------------------------------------------------------
    if ($action === 'get_students_list' || $action === 'get_rolls') {
        $classname = trim($_POST['classname'] ?? '');
        $sectionname = trim($_POST['sectionname'] ?? '');
        $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));

        $students = [];
        if (!empty($classname)) {
            if (!empty($sectionname) && $sectionname !== 'All') {
                $stmt = $conn->prepare("SELECT si.stid, si.rollno, si.classname, si.sectionname,
                                               COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
                                               s.guarname, s.guarmobile
                                        FROM sessioninfo si
                                        LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
                                        WHERE si.sccode = ? AND si.classname = ? AND si.sectionname = ?
                                          AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
                                        ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC");
                $stmt->bind_param('isss', $sccode, $classname, $sectionname, $sessionyear);
            } else {
                $stmt = $conn->prepare("SELECT si.stid, si.rollno, si.classname, si.sectionname,
                                               COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
                                               s.guarname, s.guarmobile
                                        FROM sessioninfo si
                                        LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
                                        WHERE si.sccode = ? AND si.classname = ?
                                          AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
                                        ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC");
                $stmt->bind_param('iss', $sccode, $classname, $sessionyear);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $students[] = $r;
            }
            $stmt->close();
        }

        echo json_encode(['status' => 'success', 'students' => $students]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: GET STUDENT INFO BY ROLL OR STID
    // -------------------------------------------------------------
    if ($action === 'get_student_info') {
        $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
        $classname = trim($_POST['classname'] ?? '');
        $sectionname = trim($_POST['sectionname'] ?? '');
        $rollno = intval($_POST['rollno'] ?? 0);
        $stid = intval($_POST['stid'] ?? 0);

        if ($stid > 0) {
            $sql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, si.sessionyear,
                           COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
                           s.guarname, s.guarmobile
                    FROM sessioninfo si
                    LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
                    WHERE si.sccode = ? AND si.stid = ? AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
                    ORDER BY si.id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iis', $sccode, $stid, $sessionyear);
        } else {
            $sql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, si.sessionyear,
                           COALESCE(NULLIF(s.stnameeng, ''), NULLIF(s.stnameben, ''), '') AS stname,
                           s.guarname, s.guarmobile
                    FROM sessioninfo si
                    LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
                    WHERE si.sccode = ? AND si.classname = ? 
                      AND (si.sectionname = ? OR ? = '' OR ? = 'All' OR si.sectionname = '' OR si.sectionname IS NULL)
                      AND si.rollno = ?
                      AND (si.sessionyear = ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
                    ORDER BY si.id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('issssis', $sccode, $classname, $sectionname, $sectionname, $sectionname, $rollno, $sessionyear);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $student = $res->fetch_assoc();
        $stmt->close();

        if ($student) {
            echo json_encode(['status' => 'success', 'student' => $student]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No active student record found with these details.']);
        }
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: SUBMIT LEAVE APPLICATION
    // -------------------------------------------------------------
    if ($action === 'submit_leave_application') {
        $stid = intval($_POST['stid'] ?? 0);
        $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
        $classname = trim($_POST['classname'] ?? '');
        $sectionname = trim($_POST['sectionname'] ?? '');
        $rollno = intval($_POST['rollno'] ?? 0);
        $date_from = trim($_POST['date_from'] ?? '');
        $date_to = trim($_POST['date_to'] ?? $date_from);
        $leave_type = in_array($_POST['leave_type'] ?? '', ['advance', 'post_absence', 'sick', 'emergency', 'other']) ? $_POST['leave_type'] : 'advance';
        $reason = trim($_POST['reason'] ?? '');
        $apply_by = trim($_POST['apply_by'] ?? ($user_id ?: 'Student / Guardian'));

        if ($stid <= 0 || empty($classname) || empty($date_from) || empty($reason)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields (Student ID, Class, Dates, Reason).']);
            exit;
        }

        if (strtotime($date_from) > strtotime($date_to)) {
            echo json_encode(['status' => 'error', 'message' => 'Start date cannot be after end date.']);
            exit;
        }

        // Calculate days and comma-separated date list
        $dateListArray = [];
        $curD = strtotime($date_from);
        $endD = strtotime($date_to);
        while ($curD <= $endD) {
            $dateListArray[] = date('Y-m-d', $curD);
            $curD = strtotime('+1 day', $curD);
        }
        $days = count($dateListArray);
        $date_list = implode(',', $dateListArray);

        // Handle attachment upload if present
        $attachmentPath = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (in_array($ext, $allowedExts)) {
                $uploadDir = dirname(__DIR__) . '/uploads/leaves/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $fileName = 'leave_' . $sccode . '_' . $stid . '_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $attachmentPath = 'uploads/leaves/' . $fileName;
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO student_leave_app 
            (sccode, sessionyear, classname, sectionname, rollno, stid, leave_type, date_from, date_to, date_list, days, reason, attachment, apply_date, apply_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'pending')");
        $stmt->bind_param('isssiissssisss', 
            $sccode, $sessionyear, $classname, $sectionname, $rollno, $stid, 
            $leave_type, $date_from, $date_to, $date_list, $days, $reason, $attachmentPath, $apply_by);

        if ($stmt->execute()) {
            $newAppId = $conn->insert_id;
            echo json_encode([
                'status' => 'success', 
                'message' => 'Leave application submitted successfully. Application ID: #' . $newAppId,
                'application_id' => $newAppId
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save application: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: CLASS TEACHER RECOMMENDATION (TIER 1)
    // -------------------------------------------------------------
    if ($action === 'recommend_leave_application') {
        $app_id = intval($_POST['app_id'] ?? 0);
        $decision = trim($_POST['decision'] ?? ''); // 'recommend' or 'reject'
        $notes = trim($_POST['notes'] ?? '');
        $recommender = trim($_POST['recommender'] ?? ($user_id ?: 'Class Teacher'));

        if ($app_id <= 0 || !in_array($decision, ['recommend', 'reject'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid application ID or recommendation decision.']);
            exit;
        }

        if ($decision === 'reject' && empty($notes)) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide a reason for rejecting the application.']);
            exit;
        }

        $newStatus = ($decision === 'recommend') ? 'recommended' : 'rejected_by_teacher';

        $stmt = $conn->prepare("UPDATE student_leave_app SET 
            status = ?, 
            recommended_by = ?, 
            recommended_date = NOW(), 
            recommend_notes = ?, 
            response_date = NOW(), 
            response_by = ? 
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param('ssssii', $newStatus, $recommender, $notes, $recommender, $app_id, $sccode);

        if ($stmt->execute()) {
            $msg = ($decision === 'recommend') 
                ? 'Application recommended successfully and forwarded to Head Teacher / Principal for final approval.' 
                : 'Application rejected by Class Teacher with recorded reason.';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update recommendation: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: HEAD TEACHER / ADMIN FINAL DECISION (TIER 2)
    // -------------------------------------------------------------
    if ($action === 'final_decision_leave_application') {
        $app_id = intval($_POST['app_id'] ?? 0);
        $decision = trim($_POST['decision'] ?? ''); // 'approve' or 'reject'
        $notes = trim($_POST['notes'] ?? '');
        $approver = trim($_POST['approver'] ?? ($user_id ?: 'Head Teacher / Admin'));

        if ($app_id <= 0 || !in_array($decision, ['approve', 'reject'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid application ID or approval decision.']);
            exit;
        }

        if ($decision === 'reject' && empty($notes)) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide a reason note for rejecting the application.']);
            exit;
        }

        // Fetch application details
        $appStmt = $conn->prepare("SELECT * FROM student_leave_app WHERE id = ? AND sccode = ? LIMIT 1");
        $appStmt->bind_param('ii', $app_id, $sccode);
        $appStmt->execute();
        $app = $appStmt->get_result()->fetch_assoc();
        $appStmt->close();

        if (!$app) {
            echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
            exit;
        }

        if ($decision === 'approve') {
            $stmt = $conn->prepare("UPDATE student_leave_app SET 
                status = 'approved', 
                approved_by = ?, 
                approved_date = NOW(), 
                admin_notes = ?, 
                response_date = NOW(), 
                response_by = ? 
                WHERE id = ? AND sccode = ?");
            $stmt->bind_param('sssii', $approver, $notes, $approver, $app_id, $sccode);

            if ($stmt->execute()) {
                // Auto fine waiver and ledger re-sync
                sync_leave_fine_waiver($sccode, $app['sessionyear'], $app['stid'], $app['date_from'], $app['date_to'], $conn, 'Leave Approval Fine Waiver');
                echo json_encode(['status' => 'success', 'message' => 'Leave application approved & granted successfully. Any existing absence fines for these dates have been waived and adjusted.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to approve application: ' . $conn->error]);
            }
            $stmt->close();
            exit;
        } else {
            // Reject with reason note
            $stmt = $conn->prepare("UPDATE student_leave_app SET 
                status = 'rejected', 
                approved_by = ?, 
                approved_date = NOW(), 
                rejection_reason = ?, 
                admin_notes = ?, 
                response_date = NOW(), 
                response_by = ? 
                WHERE id = ? AND sccode = ?");
            $stmt->bind_param('ssssii', $approver, $notes, $notes, $approver, $app_id, $sccode);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Leave application declined / rejected with reason note recorded.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to reject application: ' . $conn->error]);
            }
            $stmt->close();
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
