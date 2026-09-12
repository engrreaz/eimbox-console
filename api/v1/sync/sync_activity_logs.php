<?php
/**
 * EIMBox REST API — Client Activity Logs Sync Endpoint
 * Route: POST /api/v1/sync/sync_activity_logs.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Request
$user = function_exists('api_authenticate_request') ? api_authenticate_request() : authenticate_token($conn);
$tokenSccode = (int)($user['sccode'] ?? 0);

if (!isset($conn) || !$conn) {
    $conn = function_exists('api_get_db_connection') ? api_get_db_connection() : db_connect();
}

$input = get_api_input();

$headerSccode = (int)($_SERVER['HTTP_X_SCCODE'] ?? 0);
$sccode = $headerSccode > 0 ? $headerSccode : $tokenSccode;

if ($sccode <= 0) {
    api_response('error', 'Valid institution code (sccode) is required.', null, 400);
}

// Auto-create app_activity_logs table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS `app_activity_logs` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `sccode` INT NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `action` VARCHAR(100) NOT NULL,
        `module` VARCHAR(100) DEFAULT NULL,
        `details` TEXT DEFAULT NULL,
        `device_id` VARCHAR(128) DEFAULT NULL,
        `client_timestamp` BIGINT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_sccode_time` (`sccode`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$logs = $input['logs'] ?? [];
$insertedCount = 0;

if (!empty($logs) && is_array($logs)) {
    $stmt = $conn->prepare("INSERT INTO app_activity_logs (sccode, email, action, module, details, device_id, client_timestamp, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        foreach ($logs as $log) {
            $email = trim($log['email'] ?? $user['email'] ?? '');
            $action = trim($log['action'] ?? 'UNKNOWN');
            $module = !empty($log['module']) ? trim($log['module']) : null;
            $details = !empty($log['details']) ? (is_array($log['details']) ? json_encode($log['details']) : trim($log['details'])) : null;
            $deviceId = !empty($log['device_id']) ? trim($log['device_id']) : null;
            $timestamp = isset($log['timestamp']) ? (int)$log['timestamp'] : null;

            $stmt->bind_param('isssssi', $sccode, $email, $action, $module, $details, $deviceId, $timestamp);
            if ($stmt->execute()) {
                $insertedCount++;
            }
        }
        $stmt->close();
    }
}

api_response('success', "Successfully synced {$insertedCount} activity log entries.", ['synced_count' => $insertedCount], 200);
