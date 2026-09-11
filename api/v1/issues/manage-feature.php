<?php
/**
 * EIMBox Issue Tracker API - Manage Features
 * Handles CRUD operations for `features` table and syncs with `issues_tracker`
 */
require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$action = strtolower(trim($input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'create'));

switch ($action) {
    case 'create':
    case 'add':
        $featureName = trim($input['feature_name'] ?? '');
        $moduleName = trim($input['module_name'] ?? 'General');
        $description = trim($input['description'] ?? '');
        $route = trim($input['route'] ?? '');

        if (empty($featureName)) {
            api_error('Feature name is required', 422);
        }

        // Insert into features table
        $stmt = $conn->prepare("INSERT INTO features (`feature_name`, `module_name`, `description`, `created_at`, `modifieddate`) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sss", $featureName, $moduleName, $description);
        $stmt->execute();
        $newFeatureId = $conn->insert_id;
        $stmt->close();

        // If route or feature tracker is needed, create or link issues_tracker row
        if (!empty($route) || !empty($featureName)) {
            $chk = $conn->prepare("SELECT id FROM issues_tracker WHERE feature_id = ? OR (route = ? AND route != '') LIMIT 1");
            $chk->bind_param("is", $newFeatureId, $route);
            $chk->execute();
            $existingTracker = $chk->get_result()->fetch_assoc();
            $chk->close();

            if ($existingTracker) {
                $upTracker = $conn->prepare("UPDATE issues_tracker SET feature_id = ?, title = ?, route = ?, modifieddate = NOW() WHERE id = ?");
                $upTracker->bind_param("issi", $newFeatureId, $featureName, $route, $existingTracker['id']);
                $upTracker->execute();
                $upTracker->close();
            } else {
                $insTracker = $conn->prepare("INSERT INTO issues_tracker (`feature_id`, `title`, `route`, `created_at`, `modifieddate`) VALUES (?, ?, ?, NOW(), NOW())");
                $insTracker->bind_param("iss", $newFeatureId, $featureName, $route);
                $insTracker->execute();
                $insTracker->close();
            }
        }

        api_response('success', 'Feature created successfully', [
            'id' => $newFeatureId,
            'feature_name' => $featureName,
            'module_name' => $moduleName
        ], 201);
        break;

    case 'update':
    case 'edit':
        $id = (int)($input['id'] ?? $input['feature_id'] ?? 0);
        $featureName = trim($input['feature_name'] ?? '');
        $moduleName = trim($input['module_name'] ?? 'General');
        $description = trim($input['description'] ?? '');
        $route = trim($input['route'] ?? '');

        if ($id <= 0) {
            api_error('Valid feature ID is required for update', 422);
        }
        if (empty($featureName)) {
            api_error('Feature name is required', 422);
        }

        // Update features table
        $stmt = $conn->prepare("UPDATE features SET `feature_name` = ?, `module_name` = ?, `description` = ?, `modifieddate` = NOW() WHERE `id` = ?");
        $stmt->bind_param("sssi", $featureName, $moduleName, $description, $id);
        $stmt->execute();
        $stmt->close();

        // Sync or update issues_tracker table
        $chk = $conn->prepare("SELECT id FROM issues_tracker WHERE feature_id = ? LIMIT 1");
        $chk->bind_param("i", $id);
        $chk->execute();
        $existingTracker = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($existingTracker) {
            $upTracker = $conn->prepare("UPDATE issues_tracker SET title = ?, route = ?, modifieddate = NOW() WHERE id = ?");
            $upTracker->bind_param("ssi", $featureName, $route, $existingTracker['id']);
            $upTracker->execute();
            $upTracker->close();
        } else if (!empty($route) || !empty($featureName)) {
            $insTracker = $conn->prepare("INSERT INTO issues_tracker (`feature_id`, `title`, `route`, `created_at`, `modifieddate`) VALUES (?, ?, ?, NOW(), NOW())");
            $insTracker->bind_param("iss", $id, $featureName, $route);
            $insTracker->execute();
            $insTracker->close();
        }

        api_response('success', 'Feature updated successfully', [
            'id' => $id,
            'feature_name' => $featureName,
            'module_name' => $moduleName
        ], 200);
        break;

    case 'delete':
    case 'remove':
        $id = (int)($input['id'] ?? $input['feature_id'] ?? 0);
        if ($id <= 0) {
            api_error('Valid feature ID is required for deletion', 422);
        }

        // Delete from features table
        $stmt = $conn->prepare("DELETE FROM features WHERE `id` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Unlink or clean up issues_tracker
        $up = $conn->prepare("UPDATE issues_tracker SET feature_id = NULL WHERE feature_id = ?");
        $up->bind_param("i", $id);
        $up->execute();
        $up->close();

        api_response('success', 'Feature deleted successfully', ['id' => $id], 200);
        break;

    default:
        api_error("Unknown action '$action'", 400);
        break;
}
