<?php
require_once '../core/init.php'; // Includes config, db, global_values, functions
header('Content-Type: application/json'); // Change header to JSON

// Get data from POST request
$stid = $_POST['stid'] ?? null;
$year = $_POST['year'] ?? null;
$sec = $_POST['sec'] ?? null;
$exam = $_POST['exam'] ?? 'SSC'; // Default to SSC if not provided

$sccode = $_SESSION['sccode'] ?? null;
$entryby = $_SESSION['user_email'] ?? 'system';

if (!$stid || !$sccode || !$year) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}
/*
// Check if a testimonial has already been issued for this student and exam
$check_stmt = $conn->prepare("SELECT id FROM testimonial WHERE stid = ? AND sccode = ? AND pubexam = ?");
$check_stmt->bind_param("sss", $stid, $sccode, $exam);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
*/
// Fetch current student data from the 'students' table
$student_data_sql = "
    SELECT 
        s.rollno, s.regdno, s.sscpassyear, s.gpa, s.gla
    FROM students s
    WHERE s.stid = ? AND s.sccode = ?
    LIMIT 1
";
$stmt_student = $conn->prepare($student_data_sql);
$stmt_student->bind_param("ss", $stid, $sccode);
$stmt_student->execute();
$student_data = $stmt_student->get_result()->fetch_assoc();
$stmt_student->close();

if (!$student_data) {
    echo json_encode(['status' => 'error', 'message' => "Student data not found for STID: $stid. Cannot issue testimonial."]);
    exit;
}

// Prepare new data based on your logic
$board_roll = $student_data['rollno'] ?? null;
$regd_no = $student_data['regdno'] ?? null;
$pass_year = $student_data['sscpassyear'] ?? $year;
$gpa = $student_data['gpa'] ?? 0;
$gla = $student_data['gla'] ?? 'F'; // Use GLA directly from students table

// Check if a testimonial already exists
$check_stmt = $conn->prepare("SELECT * FROM testimonial WHERE stid = ? AND sccode = ? AND pubexam = ? LIMIT 1");
$check_stmt->bind_param("sss", $stid, $sccode, $exam);
$check_stmt->execute();
$existing_testimonial = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($existing_testimonial) {
    // Record exists, check for changes
    $is_changed = (
        $existing_testimonial['rollno'] != $board_roll ||
        $existing_testimonial['regdno'] != $regd_no ||
        (float)$existing_testimonial['gpa'] != (float)$gpa ||
        $existing_testimonial['grade'] != $gla
    );

    if ($is_changed) {
        // Data has changed, UPDATE the existing record
        $update_stmt = $conn->prepare(
            "UPDATE testimonial SET rollno = ?, regdno = ?, gpa = ?, grade = ?, modifieddate = NOW() WHERE id = ?"
        );
        $update_stmt->bind_param("ssdsi", $board_roll, $regd_no, $gpa, $gla, $existing_testimonial['id']);
        $update_stmt->execute();
        $update_stmt->close();
        $message = "Testimonial information updated.";
    } else {
        // No changes, do nothing
        $message = "Already issued with the same information. No changes were made.";
    }
} else {
    // Record does not exist, INSERT a new one
    $slno_res = $conn->query("SELECT slno FROM testimonial WHERE sccode = '$sccode' AND passyear='$pass_year' ORDER BY slno DESC LIMIT 1");
    $slno = ($slno_res && $slno_res->num_rows > 0) ? ($slno_res->fetch_assoc()['slno'] + 1) : 1;
    $sst = str_pad($slno, 2, '0', STR_PAD_LEFT);
    $testsl = 'SSC-' . ($sccode % 10000) . '-' . ($pass_year % 100) . '-' . $sst;

    $regdyear = $pass_year - 2;
    $session_str = $regdyear . '-' . (($pass_year - 1) % 100);
    $group = $sec;
    $exam_center = 'Bancha-2';
    $issue_date = date('Y-m-d');
    $issue_time = date('Y-m-d H:i:s');

    $insert_stmt = $conn->prepare(
        "INSERT INTO testimonial (sccode, stid, pubexam, regdno, regdyear, rollno, passyear, session, gpa, grade, slno, testslno, testdate, groupsection, examcenter, issueby, issuetime) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if ($insert_stmt) {
        $insert_stmt->bind_param("ssssssssssdssssss", $sccode, $stid, $exam, $regd_no, $regdyear, $board_roll, $pass_year, $session_str, $gpa, $gla, $slno, $testsl, $issue_date, $group, $exam_center, $entryby, $issue_time);
        $insert_stmt->execute();
        $insert_stmt->close();
        $message = "Testimonial issued successfully.";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare insert statement.']);
        exit;
    }
}

// After issuing (or if already issued), fetch and return the updated action cell HTML.
$_GET['stid'] = $stid;
$_GET['exam'] = $exam;

ob_start();
include 'get-testimonial-action-cell.php';
$action_html = ob_get_clean();

echo json_encode([
    'status' => 'success',
    'message' => $message,
    'action_html' => $action_html
]);

?>