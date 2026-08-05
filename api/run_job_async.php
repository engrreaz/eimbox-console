<?php
/**
 * File: api/run_job_async.php
 * Purpose: Triggers the background job runner asynchronously.
 * This script is designed to be called via a "fire-and-forget" AJAX request.
 */

// Ensure the script doesn't time out and continues running even if the user navigates away.
ignore_user_abort(true);
set_time_limit(0);

// Immediately close the connection to the client and send a response.
ob_start();
echo json_encode(['status' => 'success', 'message' => 'Job runner triggered.']);
header('Connection: close');
header('Content-Length: ' . ob_get_length());
ob_end_flush();
ob_flush();
flush();

// Now, run the actual long-running task.
// The client has already received the response and is not waiting.

// Define a base path to locate the jobs directory correctly.
define('BASE_PATH', dirname(__DIR__));

// Include and execute the pending jobs runner.
// Note: This script will handle its own database connections.
include BASE_PATH . '/jobs/run_pending_jobs.php';

exit;
?>