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
    // If not issued, insert a new record
    $issue_date = date('Y-m-d');
    $insert_stmt = $conn->prepare(
        "INSERT INTO testimonial (stid, sccode, passyear, pubexam, testdate, issueby) VALUES (?, ?, ?, ?, ?, ?)"
    );
    if ($insert_stmt) {
        $insert_stmt->bind_param("ssssss", $stid, $sccode, $year, $exam, $issue_date, $entryby);
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