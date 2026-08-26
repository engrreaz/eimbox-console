<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'core/config.php';
require_once 'core/db.php';

$_GET['dataset_id'] = 119;
$_SESSION['sccode'] = '103187';

echo "--- Testing get_student_report.php ---\n";
try {
    include 'analytics/get_student_report.php';
} catch (Throwable $e) {
    echo "Exception in get_student_report: " . $e->getMessage() . "\n";
}

echo "\n--- Testing get_at_risk_students_report.php ---\n";
try {
    include 'analytics/get_at_risk_students_report.php';
} catch (Throwable $e) {
    echo "Exception in get_at_risk_students_report: " . $e->getMessage() . "\n";
}
?>
