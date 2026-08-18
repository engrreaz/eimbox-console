<?php
ob_start(); // Start output buffering
require_once '../core/init.php';

$stid = $_GET['stid'] ?? null;
$exam2 = $_GET['exam'] ?? 'SSC'; // Assuming exam is passed from frontend
$sccode = $_SESSION['sccode'] ?? null; // Get sccode from session

if (!$stid || !$sccode) {
    echo "Error: Missing parameters.";
    exit;
}

// Fetch student data
$sql_student = "SELECT stnameeng, stnameben, fname, mname, rollno, regdno, gpa, gla, sscpassyear, gender, dob FROM students WHERE stid = ? AND sccode = ?";
$stmt_student = $conn->prepare($sql_student);
if (!$stmt_student) {
    echo "Error preparing student data query: " . $conn->error;
    exit;
}
$stmt_student->bind_param("ss", $stid, $sccode);
$stmt_student->execute();
$student_data = $stmt_student->get_result()->fetch_assoc();
$stmt_student->close();

if (!$student_data) {
    echo "Student not found.";
    exit;
}

// Check if testimonial is issued
$is_issued_query = "SELECT id FROM testimonial WHERE stid=? AND sccode=? AND pubexam = ?";
$stmt_issued = $conn->prepare($is_issued_query);
if (!$stmt_issued) {
    echo "Error preparing testimonial status query: " . $conn->error;
    exit;
}
$stmt_issued->bind_param("sss", $stid, $sccode, $exam2);
$stmt_issued->execute();
$is_printable = $stmt_issued->get_result()->num_rows > 0;
$stmt_issued->close();

// Condition: Data is updated (regdno, sscroll, and gpa are present)
$is_data_updated = !empty($student_data['regdno']) && !empty($student_data['rollno']) && $student_data['gpa'] > 0;

// Render the action cell HTML
ob_end_clean(); // Clean (discard) the buffer from init.php

// The only output from this script should be the HTML below
?>
<div class="dropdown">
    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <a class="dropdown-item" href="javascript:void(0);" onclick="openModifyModal('<?= $stid ?>', '<?= addslashes($student_data['stnameeng']) ?>', '<?= addslashes($student_data['stnameben']) ?>', '<?= addslashes($student_data['fname']) ?>', '<?= addslashes($student_data['mname']) ?>', '<?= $student_data['rollno'] ?>', '<?= $student_data['regdno'] ?>', '<?= $student_data['gpa'] ?>', '<?= $student_data['sscpassyear'] ?>', '<?= $student_data['gender'] ?>', '<?= $student_data['dob'] ?>')"><i class="bi bi-pencil-square me-2"></i> Update Info</a>
        <?php if ($is_printable): ?>
            <a class="dropdown-item" href="javascript:void(0);" onclick="resultEntry('<?= $student_data['rollno'] ?>')"><i class="bi bi-card-list me-2"></i> Update Result</a>
            <a class="dropdown-item" href="javascript:void(0);" onclick="issue('<?= $stid ?>')"><i class="bi bi-arrow-repeat me-2"></i> Re-issue Testimonial</a>
            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="printSingle('<?= $stid ?>')"><i class="bi bi-printer me-2"></i> Print</a>
        <?php elseif ($is_data_updated): ?>
            <a class="dropdown-item" href="javascript:void(0);" onclick="resultEntry('<?= $student_data['rollno'] ?>')"><i class="bi bi-card-list me-2"></i> Update Result</a>
            <a class="dropdown-item" href="javascript:void(0);" onclick="issue('<?= $stid ?>')"><i class="bi bi-file-earmark-check me-2"></i> Issue Testimonial</a>
        <?php endif; ?>
    </div>
</div>
