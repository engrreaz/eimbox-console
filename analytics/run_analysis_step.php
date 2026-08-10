<?php
// Keep the AJAX response valid JSON even if the step script emits notices/warnings.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Basic security and session start
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/global_values.php';
error_log("analytics/run_analysis_step.php loaded");

// Get parameters from AJAX POST request
$stepFile = $_POST['step_file'] ?? null;
$sccode = $_SESSION['sccode'] ?? null;
$slot = $_POST['slot'] ?? null;
$sessionyear = $_POST['sessionyear'] ?? null;
$examids_raw = $_POST['examid'] ?? null; // Changed to handle array

if (!$stepFile || !$sccode || !$slot || !$sessionyear || !$examids_raw) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

// Convert examid array to a comma-separated string for SQL IN() clause
$examids = is_array($examids_raw) ? $examids_raw : [$examids_raw];
$examid_list_str = implode(',', array_map('intval', $examids));

include 'common-analytics.php';

// Security: Prevent directory traversal attacks
// __DIR__ হলো run_analysis_step.php ফাইলটির ডিরেক্টরি (যেমন: .../eimbox-materio/analytics)
// আমরা ধাপের ফাইলগুলো analytics ফোল্ডারের ভেতর থেকেই খুঁজব।
$baseDir = __DIR__ . DIRECTORY_SEPARATOR;
$filePath = realpath($baseDir . $stepFile);

// Normalize slashes for comparison, especially on Windows
$baseDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $baseDir);
$filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

if (!$filePath || strpos($filePath, $baseDir) !== 0) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied to the requested file. Attempted path: ' . $filePath . ' (BaseDir: ' . $baseDir . ')']);
    exit;
}

if (file_exists($filePath)) {
    ob_start();
    try {
        // All variables like $sccode, $slot, $sessionyear, $examids, $examid_list_str are available.
        include($filePath);

        ob_end_clean();

        // If the included script runs without fatal errors, send success.
        echo json_encode(['status' => 'success', 'message' => "$stepFile completed."]);

    } catch (Throwable $e) {
        ob_end_clean();
        // Catch any fatal error from the included script (PHP 7+)
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Error in $stepFile: " . $e->getMessage() . " on line " . $e->getLine()]);
    }
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => "Step file '$stepFile' not found."]);
}
?>