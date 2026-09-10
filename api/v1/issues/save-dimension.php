<?php
/**
 * EIMBox Issue Tracker API - Save Dimensions
 * Updates or creates issues_tracker records for a route/script
 */
require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$route = trim($input['route'] ?? $input['script'] ?? '');
$title = trim($input['title'] ?? basename($route));
$featureId = !empty($input['feature_id']) ? (int)$input['feature_id'] : null;
$notes = trim($input['notes'] ?? '');

if (empty($route)) {
    api_error('Route or script parameter is required', 422);
}

$allowedDimensions = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 
    'delete', 'cache', 'push', 'pull', 'dropdown', 
    'modal', 'print', 'pdf', 'permission'
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

// Check if record exists
$checkStmt = $conn->prepare("SELECT id FROM issues_tracker WHERE route = ? OR route = ? LIMIT 1");
$basename = basename($route);
$checkStmt->bind_param("ss", $route, $basename);
$checkStmt->execute();
$res = $checkStmt->get_result();
$existing = $res->fetch_assoc();
$checkStmt->close();

if ($existing) {
    // Update existing record
    $recordId = (int)$existing['id'];
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
    $cols = ['`title`', '`route`'];
    $placeholders = ['?', '?'];
    $params = [$title, $route];
    $types = "ss";

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

// Fetch fresh row
$getStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE id = ?");
$getStmt->bind_param("i", $recordId);
$getStmt->execute();
$updatedData = $getStmt->get_result()->fetch_assoc();
$getStmt->close();

api_response('success', 'Dimensions saved successfully', [
    'id' => $recordId,
    'route' => $route,
    'dimensions' => $updatedData
], 200);
