<?php
/**
 * EIMBox REST API — High-Performance Bulk Telemetry Ingestion Endpoint
 * Route: POST /api/v1/telemetry/push.php
 * Handles batched push of user actions and logbook screen sessions from Desktop/Mobile clients.
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token or Hardware Signature
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$deviceId = trim($input['device_id'] ?? $_SERVER['HTTP_X_DEVICE_UUID'] ?? '');
$appVersion = trim($input['app_version'] ?? $_SERVER['HTTP_X_EIMBOX_VERSION'] ?? '2.4.0');
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$userActions = $input['user_actions'] ?? [];
$logbookEntries = $input['logbook_entries'] ?? [];

$syncedActions = 0;
$syncedSessions = 0;

// 1. Bulk Ingest User Actions
if (is_array($userActions) && !empty($userActions)) {
    $actionValues = [];
    $actionTypes = '';
    $actionParams = [];

    foreach ($userActions as $act) {
        $actSccode = intval($act['sccode'] ?? $sccode);
        $actEmail = trim($act['email'] ?? $user['email'] ?? 'system');
        $actUrl = substr(trim($act['screen_route'] ?? $act['url'] ?? ''), 0, 255);
        $actPage = substr(trim($act['screen_route'] ?? $act['page'] ?? ''), 0, 50);
        $actAction = substr(trim($act['action'] ?? 'User Action'), 0, 255);
        $actPoints = intval($act['points'] ?? 0);
        $actTimestamp = trim($act['timestamp'] ?? date('Y-m-d H:i:s'));
        $actIp = substr(trim($act['ip_address'] ?? $clientIp), 0, 45);
        $actBrowser = substr(trim($act['browser'] ?? 'EIMBox Desktop Electron'), 0, 255);
        $actPlatform = substr(trim($act['platform'] ?? 'Desktop-Electron'), 0, 20);
        $actDeviceId = substr(trim($act['device_id'] ?? $act['hw_uuid'] ?? $deviceId), 0, 100);
        $actSessionId = substr(trim($act['session_id'] ?? ''), 0, 64);
        $actAppVersion = substr(trim($act['app_version'] ?? $appVersion), 0, 20);
        $actEntityId = substr(trim($act['entity_id'] ?? ''), 0, 100);
        $actDetails = is_array($act['details_json'] ?? null) 
            ? json_encode($act['details_json']) 
            : (is_string($act['details_json'] ?? null) ? $act['details_json'] : (is_string($act['description'] ?? null) ? $act['description'] : null));

        $actionValues[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $actionTypes .= "issssississssss";
        array_push(
            $actionParams, 
            $actSccode, $actEmail, $actUrl, $actPage, $actAction, 
            $actPoints, $actTimestamp, $actIp, $actBrowser, $actPlatform, 
            $actDeviceId, $actSessionId, $actAppVersion, $actEntityId, $actDetails
        );
        $syncedActions++;
    }

    if (!empty($actionValues)) {
        $sqlActions = "INSERT INTO `user_actions` (
            `sccode`, `email`, `url`, `page`, `action`, 
            `points`, `timestamp`, `ip`, `browser`, `platform`, 
            `device_id`, `session_id`, `app_version`, `entity_id`, `details_json`
        ) VALUES " . implode(", ", $actionValues);

        $stmt = $conn->prepare($sqlActions);
        if ($stmt) {
            $stmt->bind_param($actionTypes, ...$actionParams);
            if (!$stmt->execute()) {
                api_log_error('SQL_ERROR', 'Failed to insert user_actions batch: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            api_log_error('SQL_PREPARE_ERROR', 'Failed to prepare user_actions statement: ' . $conn->error);
        }
    }
}

// 2. Bulk Ingest Logbook Screen Sessions
if (is_array($logbookEntries) && !empty($logbookEntries)) {
    $logValues = [];
    $logTypes = '';
    $logParams = [];

    foreach ($logbookEntries as $entry) {
        $logSccode = intval($entry['sccode'] ?? $sccode);
        $logEmail = trim($entry['email'] ?? $user['email'] ?? 'system');
        $logPage = substr(trim($entry['screen_route'] ?? $entry['pagename'] ?? ''), 0, 100);
        $logFileSize = floatval($entry['filesize'] ?? 0);
        $logIp = substr(trim($entry['ip_address'] ?? $entry['ipaddr'] ?? $clientIp), 0, 45);
        $logPlatform = substr(trim($entry['platform'] ?? 'Desktop-Electron'), 0, 120);
        $logBrowser = substr(trim($entry['browser'] ?? 'EIMBox Desktop Electron'), 0, 120);
        $logLocation = substr(trim($entry['location'] ?? ''), 0, 50);
        $logEntryTime = trim($entry['entered_at'] ?? $entry['entrytime'] ?? date('Y-m-d H:i:s'));
        $logModifiedDate = trim($entry['exited_at'] ?? $entry['modifieddate'] ?? date('Y-m-d H:i:s'));
        $logBandwidth = intval($entry['bandwidth'] ?? 0);
        $logDuration = intval($entry['duration_seconds'] ?? $entry['duration'] ?? 0);
        $logDeviceId = substr(trim($entry['device_id'] ?? $entry['hw_uuid'] ?? $deviceId), 0, 100);
        $logSessionId = substr(trim($entry['session_id'] ?? ''), 0, 64);
        $logAppVersion = substr(trim($entry['app_version'] ?? $appVersion), 0, 20);
        $logIsIdle = intval($entry['is_idle_detected'] ?? 0);

        $logValues[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $logTypes .= "isssdsssssiisssi";
        array_push(
            $logParams,
            $logSccode, $logEmail, $logPage, $logFileSize, $logIp,
            $logPlatform, $logBrowser, $logLocation, $logEntryTime, $logModifiedDate,
            $logBandwidth, $logDuration, $logDeviceId, $logSessionId, $logAppVersion, $logIsIdle
        );
        $syncedSessions++;
    }

    if (!empty($logValues)) {
        $sqlLogbook = "INSERT INTO `logbook` (
            `sccode`, `email`, `pagename`, `filesize`, `ipaddr`, 
            `platform`, `browser`, `location`, `entrytime`, `modifieddate`, 
            `bandwidth`, `duration`, `device_id`, `session_id`, `app_version`, `is_idle_detected`
        ) VALUES " . implode(", ", $logValues);

        $stmt = $conn->prepare($sqlLogbook);
        if ($stmt) {
            $stmt->bind_param($logTypes, ...$logParams);
            if (!$stmt->execute()) {
                api_log_error('SQL_ERROR', 'Failed to insert logbook batch: ' . $stmt->error);
            }
            $stmt->close();
        } else {
            api_log_error('SQL_PREPARE_ERROR', 'Failed to prepare logbook statement: ' . $conn->error);
        }
    }
}

api_response('success', 'Telemetry batch pushed successfully.', [
    'synced_actions' => $syncedActions,
    'synced_sessions' => $syncedSessions,
    'timestamp' => date('Y-m-d H:i:s')
], 200);
