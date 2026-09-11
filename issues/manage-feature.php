<?php
/**
 * EIMBox Issue Tracker - Manage Features
 * Location: issues/manage-feature.php
 * Handles CRUD operations for `features` table and syncs with `issues_tracker`
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';

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
            $basename = !empty($route) ? basename($route) : '';
            $chk = $conn->prepare("SELECT id FROM issues_tracker WHERE feature_id = ? OR (route = ? AND route != '') OR (route = ? AND route != '') LIMIT 1");
            $chk->bind_param("iss", $newFeatureId, $route, $basename);
            $chk->execute();
            $existingTracker = $chk->get_result()->fetch_assoc();
            $chk->close();

            if ($existingTracker) {
                $upTracker = $conn->prepare("UPDATE issues_tracker SET feature_id = ?, title = ?, route = ?, modifieddate = NOW() WHERE route = ? OR route = ? OR feature_id = ?");
                $upTracker->bind_param("issssi", $newFeatureId, $featureName, $route, $route, $basename, $newFeatureId);
                $upTracker->execute();
                $upTracker->close();
            } else {
                $insTracker = $conn->prepare("INSERT INTO issues_tracker (`feature_id`, `title`, `route`, `platform`, `ui`, `light`, `dark`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `dropdown`, `modal`, `print`, `pdf`, `permission`, `documentation`, `faq`, `youtube_video`, `created_at`, `modifieddate`) VALUES (?, ?, ?, 'All', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', NOW(), NOW())");
                $insTracker->bind_param("iss", $newFeatureId, $featureName, $route);
                $insTracker->execute();
                $insTracker->close();
            }

            // Sync eimbox_features tickets
            if (!empty($route)) {
                $syncIssues = $conn->prepare("UPDATE eimbox_features SET feature_id = ?, feature = ?, module = ? WHERE script = ? OR script = ?");
                $syncIssues->bind_param("issss", $newFeatureId, $featureName, $moduleName, $route, $basename);
                $syncIssues->execute();
                $syncIssues->close();
            }
        }

        api_response('success', 'Feature created successfully', [
            'id' => $newFeatureId,
            'feature_name' => $featureName,
            'module_name' => $moduleName
        ], 201);
        break;

    case 'link_screen':
    case 'assign':
        $route = trim($input['route'] ?? $input['script'] ?? '');
        $featureId = (int)($input['feature_id'] ?? 0);
        if (empty($route)) {
            api_error('Route or script parameter is required', 422);
        }
        if ($featureId <= 0) {
            api_error('Valid feature_id is required to link screen', 422);
        }

        // Fetch feature
        $fStmt = $conn->prepare("SELECT id, feature_name, module_name FROM features WHERE id = ? LIMIT 1");
        $fStmt->bind_param("i", $featureId);
        $fStmt->execute();
        $feat = $fStmt->get_result()->fetch_assoc();
        $fStmt->close();

        if (!$feat) {
            api_error('Selected feature does not exist', 404);
        }

        $basename = basename($route);

        // Update all issues_tracker rows matching route or basename
        $upStmt = $conn->prepare("UPDATE issues_tracker SET feature_id = ?, title = ?, modifieddate = NOW() WHERE (route = ? OR route = ?) AND (route != '')");
        $upStmt->bind_param("isss", $featureId, $feat['feature_name'], $route, $basename);
        $upStmt->execute();
        $affected = $upStmt->affected_rows;
        $upStmt->close();

        // If no issues_tracker rows existed for this route yet, create a baseline row
        $chk = $conn->prepare("SELECT id FROM issues_tracker WHERE (route = ? OR route = ?) AND (route != '') LIMIT 1");
        $chk->bind_param("ss", $route, $basename);
        $chk->execute();
        $hasAny = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$hasAny) {
            $ins = $conn->prepare("INSERT INTO issues_tracker (`feature_id`, `title`, `route`, `platform`, `ui`, `light`, `dark`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `dropdown`, `modal`, `print`, `pdf`, `permission`, `documentation`, `faq`, `youtube_video`, `created_at`, `modifieddate`) VALUES (?, ?, ?, 'All', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', NOW(), NOW())");
            $ins->bind_param("iss", $featureId, $feat['feature_name'], $route);
            $ins->execute();
            $ins->close();
        }

        // Also sync eimbox_features tickets
        $syncIssues = $conn->prepare("UPDATE eimbox_features SET feature_id = ?, feature = ?, module = ? WHERE script = ? OR script = ?");
        $syncIssues->bind_param("issss", $featureId, $feat['feature_name'], $feat['module_name'], $route, $basename);
        $syncIssues->execute();
        $syncIssues->close();

        api_response('success', "Screen successfully linked to {$feat['feature_name']} ({$feat['module_name']})", [
            'feature_id' => (int)$feat['id'],
            'feature_name' => $feat['feature_name'],
            'module_name' => $feat['module_name']
        ]);
        break;

    case 'update':
    case 'edit':
        $id = (int)($input['id'] ?? $input['feature_id'] ?? 0);
        $featureName = trim($input['feature_name'] ?? '');
        $moduleName = trim($input['module_name'] ?? 'General');
        $description = trim($input['description'] ?? '');
        $route = trim($input['route'] ?? '');

        if ($id <= 0 && empty($route) && empty($featureName)) {
            api_error('Valid feature ID, route, or name is required for update', 422);
        }
        if (empty($featureName)) {
            api_error('Feature name is required', 422);
        }

        // 1. Check if record exists in `features` table
        $featRecord = null;
        if ($id > 0) {
            $chkF = $conn->prepare("SELECT * FROM features WHERE id = ? LIMIT 1");
            $chkF->bind_param("i", $id);
            $chkF->execute();
            $featRecord = $chkF->get_result()->fetch_assoc();
            $chkF->close();
        }

        // If not found by ID, try matching by feature_name
        if (!$featRecord && !empty($featureName)) {
            $chkName = $conn->prepare("SELECT * FROM features WHERE LOWER(feature_name) = LOWER(?) LIMIT 1");
            $chkName->bind_param("s", $featureName);
            $chkName->execute();
            $featRecord = $chkName->get_result()->fetch_assoc();
            $chkName->close();
        }

        if ($featRecord) {
            $realFeatureId = (int)$featRecord['id'];
            $stmt = $conn->prepare("UPDATE features SET `feature_name` = ?, `module_name` = ?, `description` = ?, `modifieddate` = NOW() WHERE `id` = ?");
            $stmt->bind_param("sssi", $featureName, $moduleName, $description, $realFeatureId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Feature does not exist in `features` table yet (e.g. came from tracked screen or issues_tracker)
            // Insert it into `features` table
            $stmt = $conn->prepare("INSERT INTO features (`feature_name`, `module_name`, `description`, `created_at`, `modifieddate`) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("sss", $featureName, $moduleName, $description);
            $stmt->execute();
            $realFeatureId = $conn->insert_id;
            $stmt->close();
        }

        // 2. Synchronize ALL matching rows in `issues_tracker` table
        // Matches by: old feature_id, new feature_id, issues_tracker.id (if passed as id), or route
        $syncSql = "UPDATE issues_tracker 
                    SET `feature_id` = ?, `title` = ?, `route` = ?, `modifieddate` = NOW() 
                    WHERE `feature_id` = ? 
                       OR `feature_id` = ? 
                       OR `id` = ? 
                       OR (`route` = ? AND `route` != '')";
        $syncStmt = $conn->prepare($syncSql);
        $syncStmt->bind_param("issiiis", $realFeatureId, $featureName, $route, $id, $realFeatureId, $id, $route);
        $syncStmt->execute();
        $affectedTrackers = $syncStmt->affected_rows;
        $syncStmt->close();

        // 3. If no tracker existed at all, insert a baseline tracker row for this feature
        $chkAny = $conn->prepare("SELECT id FROM issues_tracker WHERE feature_id = ? LIMIT 1");
        $chkAny->bind_param("i", $realFeatureId);
        $chkAny->execute();
        $hasAny = $chkAny->get_result()->fetch_assoc();
        $chkAny->close();

        if (!$hasAny) {
            $insTracker = $conn->prepare("INSERT INTO issues_tracker (`feature_id`, `title`, `route`, `platform`, `ui`, `light`, `dark`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `dropdown`, `modal`, `print`, `pdf`, `permission`, `documentation`, `faq`, `youtube_video`, `created_at`, `modifieddate`) VALUES (?, ?, ?, 'All', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', 'Not Tested', NOW(), NOW())");
            $insTracker->bind_param("iss", $realFeatureId, $featureName, $route);
            $insTracker->execute();
            $insTracker->close();
        }

        // 4. Also synchronize eimbox_features table (issues tickets)
        if (!empty($featureName)) {
            $syncIssues = $conn->prepare("UPDATE eimbox_features SET `feature` = ?, `feature_id` = ?, `module` = ? WHERE `feature_id` = ? OR `feature_id` = ? OR (`script` = ? AND `script` != '')");
            $syncIssues->bind_param("sisiis", $featureName, $realFeatureId, $moduleName, $id, $realFeatureId, $route);
            $syncIssues->execute();
            $syncIssues->close();
        }

        api_response('success', 'Feature updated successfully', [
            'id' => $realFeatureId,
            'feature_name' => $featureName,
            'module_name' => $moduleName,
            'route' => $route
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

        // Also clean up issues_tracker rows
        $up = $conn->prepare("DELETE FROM issues_tracker WHERE feature_id = ? OR id = ?");
        $up->bind_param("ii", $id, $id);
        $up->execute();
        $up->close();

        // Unlink any issues attached in eimbox_features
        $upIss = $conn->prepare("UPDATE eimbox_features SET feature_id = NULL WHERE feature_id = ?");
        $upIss->bind_param("i", $id);
        $upIss->execute();
        $upIss->close();

        api_response('success', 'Feature deleted successfully', ['id' => $id], 200);
        break;

    default:
        api_error("Unknown action '$action'", 400);
        break;
}
