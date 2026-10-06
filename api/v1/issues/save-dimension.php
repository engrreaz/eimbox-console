<?php
/**
 * EIMBox Issue Tracker API - Save Dimensions
 * Updates or creates issues_tracker records for a route/script and logs changes into issues_dimension_logs
 */
require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$route = trim($input['route'] ?? $input['script'] ?? '');
$title = trim($input['title'] ?? basename($route));
$featureId = !empty($input['feature_id']) ? (int)$input['feature_id'] : null;
$platform = trim($input['platform'] ?? 'All');
if (empty($platform)) $platform = 'All';
$notes = trim($input['notes'] ?? '');
$userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['updated_by'] ?? $input['user'] ?? 'Admin';

if (empty($route)) {
    api_error('Route or script parameter is required', 422);
}

$allowedDimensions = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 
    'delete', 'cache', 'push', 'pull', 'dropdown', 
    'modal', 'print', 'pdf', 'permission',
    'documentation', 'faq', 'youtube_video'
];

$validStatuses = ['Not Tested', 'Not applicable', 'On Progress', 'bug', 'error', 'OK', 'ok'];

// Extract dimension changes
$dimUpdates = [];
if (isset($input['dimensions']) && is_array($input['dimensions'])) {
    foreach ($input['dimensions'] as $dim => $status) {
        if (in_array(strtolower($dim), $allowedDimensions)) {
            $dimUpdates[strtolower($dim)] = $status;
        }
    }
} elseif (isset($input['dimension']) && isset($input['status'])) {
    $d = strtolower(trim($input['dimension']));
    if (in_array($d, $allowedDimensions)) {
        $dimUpdates[$d] = $input['status'];
    }
}

// Ensure issues_dimension_logs table exists
$conn->query("CREATE TABLE IF NOT EXISTS `issues_dimension_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tracker_id` INT(11) DEFAULT NULL,
  `feature_id` INT(11) DEFAULT NULL,
  `route` VARCHAR(255) NOT NULL,
  `platform` VARCHAR(50) NOT NULL DEFAULT 'All',
  `dimension` VARCHAR(50) NOT NULL,
  `old_status` VARCHAR(50) DEFAULT 'Not Tested',
  `new_status` VARCHAR(50) NOT NULL,
  `updated_by` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tracker` (`tracker_id`),
  INDEX `idx_route` (`route`),
  INDEX `idx_platform` (`platform`),
  INDEX `idx_dimension` (`dimension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Check if record exists
$basename = basename($route);
$checkStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR route = ?) AND (platform = ? OR platform = 'All') ORDER BY (platform = ?) DESC LIMIT 1");
$checkStmt->bind_param("ssss", $route, $basename, $platform, $platform);
$checkStmt->execute();
$res = $checkStmt->get_result();
$existing = $res->fetch_assoc();
$checkStmt->close();

$recordId = 0;
$oldValues = [];

if ($existing) {
    $recordId = (int)$existing['id'];
    $oldValues = $existing;
    if (!$featureId && !empty($existing['feature_id'])) {
        $featureId = (int)$existing['feature_id'];
    }

    $setParts = [];
    $params = [];
    $types = "";

    if (!empty($title)) {
        $setParts[] = "`title` = ?";
        $params[] = $title;
        $types .= "s";
    }
    if ($featureId !== null) {
        $setParts[] = "`feature_id` = ?";
        $params[] = $featureId;
        $types .= "i";
    }
    if (isset($input['notes'])) {
        $setParts[] = "`notes` = ?";
        $params[] = $notes;
        $types .= "s";
    }

    foreach ($dimUpdates as $dim => $status) {
        $setParts[] = "`$dim` = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($setParts)) {
        $sql = "UPDATE issues_tracker SET " . implode(", ", $setParts) . ", modifieddate = NOW() WHERE id = ?";
        $params[] = $recordId;
        $types .= "i";

        $upStmt = $conn->prepare($sql);
        $upStmt->bind_param($types, ...$params);
        $upStmt->execute();
        $upStmt->close();
    }
} else {
    // Insert new record
    $cols = ['`title`', '`route`', '`platform`'];
    $placeholders = ['?', '?', '?'];
    $params = [$title, $route, $platform];
    $types = "sss";

    if ($featureId !== null) {
        $cols[] = '`feature_id`';
        $placeholders[] = '?';
        $params[] = $featureId;
        $types .= "i";
    }
    if (!empty($notes)) {
        $cols[] = '`notes`';
        $placeholders[] = '?';
        $params[] = $notes;
        $types .= "s";
    }

    foreach ($allowedDimensions as $dim) {
        $cols[] = "`$dim`";
        $placeholders[] = '?';
        $val = $dimUpdates[$dim] ?? 'Not Tested';
        $params[] = $val;
        $types .= "s";
    }

    $sql = "INSERT INTO issues_tracker (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $placeholders) . ")";
    $insStmt = $conn->prepare($sql);
    $insStmt->bind_param($types, ...$params);
    $insStmt->execute();
    $recordId = $conn->insert_id;
    $insStmt->close();
}

// Log changes to issues_dimension_logs
if (!empty($dimUpdates)) {
    $logInsertStmt = $conn->prepare("INSERT INTO issues_dimension_logs (`tracker_id`, `feature_id`, `route`, `platform`, `dimension`, `old_status`, `new_status`, `updated_by`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    foreach ($dimUpdates as $dim => $newStatus) {
        $oldStatus = $oldValues[$dim] ?? 'Not Tested';
        if (strtolower((string)$oldStatus) !== strtolower((string)$newStatus)) {
            $fId = $featureId ?: null;
            $logInsertStmt->bind_param("iissssss", $recordId, $fId, $route, $platform, $dim, $oldStatus, $newStatus, $userEmail);
            $logInsertStmt->execute();
        }
    }
    $logInsertStmt->close();
}

// Fetch fresh row
$getStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE id = ?");
$getStmt->bind_param("i", $recordId);
$getStmt->execute();
$updatedData = $getStmt->get_result()->fetch_assoc();
$getStmt->close();

api_response('success', 'Dimensions saved successfully', [
    'id' => $recordId,
    'route' => $route,
    'platform' => $platform,
    'dimensions' => $updatedData
], 200);
