<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$sccode = $_SESSION['sccode'] ?? null;
$stid = trim($_POST['stid'] ?? '');
$exam = trim($_POST['exam'] ?? 'SSC');
$session = trim($_POST['session'] ?? '');
$testdate = trim($_POST['testdate'] ?? '');

if (!$sccode || empty($stid)) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication or Student ID missing.']);
    exit;
}

if (empty($session) || empty($testdate)) {
    echo json_encode(['status' => 'error', 'message' => 'Session and Testimonial Date cannot be empty.']);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $testdate)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format. Please use YYYY-MM-DD.']);
    exit;
}

// Check if testimonial already exists for this student and exam
$check_stmt = $conn->prepare("SELECT id FROM testimonial WHERE stid = ? AND sccode = ? AND pubexam = ? LIMIT 1");
$check_stmt->bind_param("sss", $stid, $sccode, $exam);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($existing) {
    // Record exists, update session and testdate
    $update_stmt = $conn->prepare("UPDATE testimonial SET session = ?, testdate = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
    $update_stmt->bind_param("ssis", $session, $testdate, $existing['id'], $sccode);
    if ($update_stmt->execute()) {
        $update_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Testimonial info updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update testimonial record: ' . $conn->error]);
    }
} else {
    // If not yet issued, fetch student info and insert record with custom session and testdate
    $stmt_student = $conn->prepare("SELECT rollno, regdno, sscpassyear, gpa, gla FROM students WHERE stid = ? AND sccode = ? LIMIT 1");
    $stmt_student->bind_param("ss", $stid, $sccode);
    $stmt_student->execute();
    $student_data = $stmt_student->get_result()->fetch_assoc();
    $stmt_student->close();

    if (!$student_data) {
        echo json_encode(['status' => 'error', 'message' => 'Student record not found.']);
        exit;
    }

    $board_roll = $student_data['rollno'] ?? null;
    $regd_no = $student_data['regdno'] ?? null;
    $pass_year = !empty($student_data['sscpassyear']) ? intval($student_data['sscpassyear']) : intval(date('Y'));
    $gpa = $student_data['gpa'] ?? 0;
    $gla = $student_data['gla'] ?? 'F';

    $slno_res = $conn->query("SELECT slno FROM testimonial WHERE sccode = '$sccode' AND passyear='$pass_year' ORDER BY slno DESC LIMIT 1");
    $slno = ($slno_res && $slno_res->num_rows > 0) ? ($slno_res->fetch_assoc()['slno'] + 1) : 1;
    $sst = str_pad($slno, 2, '0', STR_PAD_LEFT);
    $testsl = $exam . '-' . ($sccode % 10000) . '-' . ($pass_year % 100) . '-' . $sst;
    $regdyear = $pass_year - 2;

    $center_stmt = $conn->prepare("SELECT center_name FROM scinfo WHERE sccode = ?");
    $center_stmt->bind_param("s", $sccode);
    $center_stmt->execute();
    $exam_center = $center_stmt->get_result()->fetch_assoc()['center_name'] ?? 'Default Center';
    $center_stmt->close();

    $entryby = $_SESSION['user_email'] ?? 'system';
    $issue_time = date('Y-m-d H:i:s');
    $group = '';

    $insert_stmt = $conn->prepare(
        "INSERT INTO testimonial (sccode, stid, pubexam, regdno, regdyear, rollno, passyear, session, gpa, grade, slno, testslno, testdate, groupsection, examcenter, issueby, issuetime) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if ($insert_stmt) {
        $insert_stmt->bind_param("ssssssssssdssssss", $sccode, $stid, $exam, $regd_no, $regdyear, $board_roll, $pass_year, $session, $gpa, $gla, $slno, $testsl, $testdate, $group, $exam_center, $entryby, $issue_time);
        if ($insert_stmt->execute()) {
            $insert_stmt->close();
            echo json_encode(['status' => 'success', 'message' => 'Testimonial created and info updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save testimonial info: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
    }
}
exit;
