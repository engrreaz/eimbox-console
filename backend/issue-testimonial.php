<?php
require_once '../core/init.php'; // Includes config, db, global_values, functions

header('Content-Type: text/html');

// Get data from POST request
$stid = $_POST['stid'] ?? null;
$year = $_POST['year'] ?? null;
$sec = $_POST['sec'] ?? null;
$exam = $_POST['exam'] ?? 'SSC'; // Default to SSC if not provided

$sccode = $_SESSION['sccode'] ?? null;
$entryby = $_SESSION['user_email'] ?? 'system';

if (!$stid || !$sccode || !$year) {
    echo "Error: Missing required parameters.";
    exit;
}

// Check if a testimonial has already been issued for this student and exam
$check_stmt = $conn->prepare("SELECT id FROM testimonial WHERE stid = ? AND sccode = ? AND pubexam = ?");
$check_stmt->bind_param("sss", $stid, $sccode, $exam);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    // Fetch additional student data required for the testimonial table
    $student_data_sql = "
        SELECT 
            s.rollno, s.regdno, s.sscpassyear, s.gpa, s.gla
        FROM students s
        LEFT JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode AND si.sessionyear = ?
        WHERE s.stid = ? AND s.sccode = ?
        LIMIT 1
    ";
    $stmt_student = $conn->prepare($student_data_sql);
    $stmt_student->bind_param("sss", $year, $stid, $sccode);
    $stmt_student->execute();
    $student_data = $stmt_student->get_result()->fetch_assoc();
    $stmt_student->close();

    // যদি ছাত্রের ডেটা না পাওয়া যায়, তাহলে একটি এরর মেসেজ দিয়ে প্রসেস বন্ধ করা হবে।
    if (!$student_data) {
        echo "Error: Student data not found for STID: $stid. Cannot issue testimonial.";
        exit;
    }

    // Prepare data for insertion
    $board_roll = $student_data['rollno'] ?? null;
    $regd_no = $student_data['regdno'] ?? null;
    $pass_year = $student_data['sscpassyear'] ?? $year;
    $gpa = $student_data['gpa'] ?? 0;
    $grade = $student_data['gla'] ?? 'F';
    $group = $sec; // Fallback to section if group not found

    $issue_date = date('Y-m-d');
    $issue_time = date('Y-m-d H:i:s');

    // If not issued, insert a new record with all the details
    $insert_stmt = $conn->prepare(
        "INSERT INTO testimonial (sccode, stid, pubexam, rollno, regdno, passyear, gpa, grade, testdate, groupsection, issueby, issuetime) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($insert_stmt) {
        $insert_stmt->bind_param("ssssssssssss", $sccode, $stid, $exam, $board_roll, $regd_no, $pass_year, $gpa, $grade, $issue_date, $group, $entryby, $issue_time);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}
$check_stmt->close();

// After issuing (or if already issued), fetch and return the updated action cell HTML.
// This reuses the logic from get-testimonial-action-cell.php to ensure consistency.
$_GET['stid'] = $stid;
$_GET['exam'] = $exam;

include 'get-testimonial-action-cell.php';

?>