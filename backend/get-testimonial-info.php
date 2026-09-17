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
$stid = $_GET['stid'] ?? null;
$exam = $_GET['exam'] ?? 'SSC';

if (!$sccode || !$stid) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication or required parameters missing.']);
    exit;
}

// Check student info
$stmt_st = $conn->prepare("SELECT stnameeng, sscpassyear FROM students WHERE stid = ? AND sccode = ? LIMIT 1");
$stmt_st->bind_param("ss", $stid, $sccode);
$stmt_st->execute();
$st_data = $stmt_st->get_result()->fetch_assoc();
$stmt_st->close();

$stname = $st_data['stnameeng'] ?? '';
$pass_year = !empty($st_data['sscpassyear']) ? intval($st_data['sscpassyear']) : intval(date('Y'));

// Check if testimonial exists
$stmt_t = $conn->prepare("SELECT id, session, testdate, testslno, passyear FROM testimonial WHERE stid = ? AND sccode = ? AND pubexam = ? LIMIT 1");
$stmt_t->bind_param("sss", $stid, $sccode, $exam);
$stmt_t->execute();
$t_data = $stmt_t->get_result()->fetch_assoc();
$stmt_t->close();

if ($t_data) {
    echo json_encode([
        'status' => 'success',
        'is_issued' => true,
        'session' => $t_data['session'] ?? '',
        'testdate' => $t_data['testdate'] ?? '',
        'testslno' => $t_data['testslno'] ?? '',
        'stname' => $stname
    ]);
} else {
    // Default session calculation if not issued yet
    $regdyear = $pass_year - 2;
    $default_session = $regdyear . '-' . (($pass_year - 1) % 100);
    $default_date = date('Y-m-d');

    echo json_encode([
        'status' => 'success',
        'is_issued' => false,
        'session' => $default_session,
        'testdate' => $default_date,
        'testslno' => '',
        'stname' => $stname
    ]);
}
exit;
