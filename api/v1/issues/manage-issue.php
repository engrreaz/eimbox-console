<?php
/**
 * EIMBox Issue Tracker API - Manage Issues
 * Handles CRUD operations for eimbox_features table
 */
require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$action = strtolower(trim($input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'create'));

switch ($action) {
    case 'create':
    case 'add':
        $module = trim($input['module'] ?? 'General');
        $feature = trim($input['feature'] ?? $input['title'] ?? 'Feature Issue');
        $featureId = !empty($input['feature_id']) ? (int)$input['feature_id'] : null;
        $platform = trim($input['platform'] ?? 'Dashboard');
        $screenTitle = trim($input['screen_title'] ?? $feature);
        $script = trim($input['script'] ?? $input['route'] ?? '');
        $topic = trim($input['topic'] ?? '');
        $issues = trim($input['issues'] ?? $input['description'] ?? '');
        $response = trim($input['response'] ?? '');
        $status = trim($input['status'] ?? 'Open');
        $priority = trim($input['priority'] ?? 'Medium');
        $progress = (int)($input['progress_percent'] ?? 0);
        $assignedTo = trim($input['assigned_to'] ?? '');
        $createdBy = trim($input['created_by'] ?? 'Admin');
        $possibleClosingAt = !empty($input['possible_closing_at']) ? $input['possible_closing_at'] : null;

        if (empty($issues) && empty($topic)) {
            api_error('Issue description or topic is required', 422);
        }

        $insSql = "INSERT INTO eimbox_features (
            `module`, `feature`, `feature_id`, `platform`, `screen_title`, 
            `script`, `topic`, `issues`, `response`, `status`, 
            `priority`, `progress_percent`, `assigned_to`, `created_by`, `possible_closing_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insSql);
        $stmt->bind_param(
            "ssissssssssisss",
            $module, $feature, $featureId, $platform, $screenTitle,
            $script, $topic, $issues, $response, $status,
            $priority, $progress, $assignedTo, $createdBy, $possibleClosingAt
        );
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();

        api_response('success', 'Issue registered successfully', ['id' => $newId, 'status' => $status], 201);
        break;

    case 'update':
    case 'edit':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for update', 422);
        }

        $fields = [
            'module', 'feature', 'feature_id', 'platform', 'screen_title',
            'script', 'topic', 'issues', 'response', 'status',
            'priority', 'progress_percent', 'assigned_to', 'possible_closing_at'
        ];

        $setParts = [];
        $params = [];
        $types = "";

        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $setParts[] = "`$field` = ?";
                if ($field === 'feature_id' || $field === 'progress_percent') {
                    $params[] = (int)$input[$field];
                    $types .= "i";
                } else {
                    $params[] = $input[$field];
                    $types .= "s";
                }
            }
        }

        if (empty($setParts)) {
            api_error('No update fields provided', 400);
        }

        $sql = "UPDATE eimbox_features SET " . implode(", ", $setParts) . ", updated_at = NOW() WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $upStmt = $conn->prepare($sql);
        $upStmt->bind_param($types, ...$params);
        $upStmt->execute();
        $upStmt->close();

        api_response('success', 'Issue updated successfully', ['id' => $id], 200);
        break;

    case 'delete':
    case 'remove':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for deletion', 422);
        }

        $delStmt = $conn->prepare("DELETE FROM eimbox_features WHERE id = ?");
        $delStmt->bind_param("i", $id);
        $delStmt->execute();
        $affected = $delStmt->affected_rows;
        $delStmt->close();

        api_response('success', 'Issue deleted successfully', ['id' => $id, 'affected' => $affected], 200);
        break;

    case 'quick_status':
        $id = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $progress = isset($input['progress_percent']) ? (int)$input['progress_percent'] : null;

        if ($id <= 0 || empty($status)) {
            api_error('ID and Status are required', 422);
        }

        if ($progress !== null) {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, `progress_percent` = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $status, $progress, $id);
        } else {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }
        $stmt->execute();
        $stmt->close();

        api_response('success', 'Status updated successfully', ['id' => $id, 'status' => $status], 200);
        break;

    default:
        api_error("Unknown action: $action", 400);
}
