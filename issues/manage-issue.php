<?php
/**
 * EIMBox Issue Tracker API - Manage Issues
 * Handles CRUD operations for eimbox_features table.
 * Relocated to issues/ directory.
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';

$input = get_api_input();
$action = strtolower(trim($input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'create'));

function log_issue_change($conn, $logData) {
    $tableExists = $conn->query("SHOW TABLES LIKE 'issues_change_logs'");
    if (!$tableExists || $tableExists->num_rows === 0) {
        $conn->query("CREATE TABLE IF NOT EXISTS `issues_change_logs` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `issue_id` INT(11) NOT NULL,
          `feature_id` INT(11) DEFAULT NULL,
          `module` VARCHAR(100) DEFAULT NULL,
          `feature` VARCHAR(150) DEFAULT NULL,
          `script` VARCHAR(255) DEFAULT NULL,
          `platform` VARCHAR(50) NOT NULL DEFAULT 'Dashboard',
          `action` VARCHAR(50) NOT NULL,
          `old_status` VARCHAR(50) DEFAULT NULL,
          `new_status` VARCHAR(50) DEFAULT NULL,
          `old_progress` INT(11) DEFAULT NULL,
          `new_progress` INT(11) DEFAULT NULL,
          `changes_summary` TEXT DEFAULT NULL,
          `updated_by` VARCHAR(100) NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX `idx_issue_id` (`issue_id`),
          INDEX `idx_script_plat` (`script`, `platform`),
          INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    $issueId = (int)($logData['issue_id'] ?? 0);
    $featureId = !empty($logData['feature_id']) ? (int)$logData['feature_id'] : null;
    $module = $logData['module'] ?? null;
    $feature = $logData['feature'] ?? null;
    $script = $logData['script'] ?? null;
    $platform = $logData['platform'] ?? 'Dashboard';
    $action = $logData['action'] ?? 'updated';
    $oldStatus = $logData['old_status'] ?? null;
    $newStatus = $logData['new_status'] ?? null;
    $oldProg = isset($logData['old_progress']) ? (int)$logData['old_progress'] : null;
    $newProg = isset($logData['new_progress']) ? (int)$logData['new_progress'] : null;
    $summary = $logData['changes_summary'] ?? null;
    $updatedBy = $logData['updated_by'] ?? 'Admin';

    $stmt = $conn->prepare("INSERT INTO issues_change_logs (`issue_id`, `feature_id`, `module`, `feature`, `script`, `platform`, `action`, `old_status`, `new_status`, `old_progress`, `new_progress`, `changes_summary`, `updated_by`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iisssssssiiss", $issueId, $featureId, $module, $feature, $script, $platform, $action, $oldStatus, $newStatus, $oldProg, $newProg, $summary, $updatedBy);
        $stmt->execute();
        $stmt->close();
    }
}

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
        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['created_by'] ?? $input['modified_by'] ?? 'Admin';
        $createdBy = trim($input['created_by'] ?? $userEmail);
        $modifiedBy = trim($input['modified_by'] ?? $userEmail);
        $possibleClosingAt = !empty($input['possible_closing_at']) ? $input['possible_closing_at'] : null;

        if (empty($issues) && empty($topic)) {
            api_error('Issue description or topic is required', 422);
        }

        $insSql = "INSERT INTO eimbox_features (
            `module`, `feature`, `feature_id`, `platform`, `screen_title`, 
            `script`, `topic`, `issues`, `response`, `status`, 
            `priority`, `progress_percent`, `assigned_to`, `created_by`, `modified_by`, `possible_closing_at`, `created_at`, `modifieddate`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $conn->prepare($insSql);
        if (!$stmt) {
            api_error('Database prepare error: ' . $conn->error, 500);
        }

        $stmt->bind_param(
            "ssissssssssissss",
            $module, $feature, $featureId, $platform, $screenTitle,
            $script, $topic, $issues, $response, $status,
            $priority, $progress, $assignedTo, $createdBy, $modifiedBy, $possibleClosingAt
        );
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();

        // Log Issue Change
        log_issue_change($conn, [
            'issue_id' => $newId,
            'feature_id' => $featureId,
            'module' => $module,
            'feature' => $feature,
            'script' => $script,
            'platform' => $platform,
            'action' => 'created',
            'old_status' => null,
            'new_status' => $status,
            'old_progress' => 0,
            'new_progress' => $progress,
            'changes_summary' => "Issue created with priority '$priority' and status '$status'",
            'updated_by' => $createdBy
        ]);

        api_response('success', 'Issue registered successfully', ['id' => $newId, 'status' => $status, 'created_by' => $createdBy, 'modified_by' => $modifiedBy], 201);
        break;

    case 'update':
    case 'edit':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for update', 422);
        }

        // Fetch old issue before update
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldIssue = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['modified_by'] ?? 'Admin';
        $modifiedBy = trim($input['modified_by'] ?? $userEmail);

        $fields = [
            'module', 'feature', 'feature_id', 'platform', 'screen_title',
            'script', 'topic', 'issues', 'response', 'status',
            'priority', 'progress_percent', 'assigned_to', 'possible_closing_at'
        ];

        $setParts = [];
        $params = [];
        $types = "";
        $changesDesc = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $setParts[] = "`$field` = ?";
                if ($field === 'feature_id' || $field === 'progress_percent') {
                    $val = (int)$input[$field];
                    $params[] = $val;
                    $types .= "i";
                } else {
                    $val = (string)$input[$field];
                    $params[] = $val;
                    $types .= "s";
                }

                if ($oldIssue && isset($oldIssue[$field]) && (string)$oldIssue[$field] !== (string)$val) {
                    $changesDesc[] = "$field: '{$oldIssue[$field]}' -> '$val'";
                }
            }
        }

        // Always update modified_by and modifieddate
        $setParts[] = "`modified_by` = ?";
        $params[] = $modifiedBy;
        $types .= "s";

        $sql = "UPDATE eimbox_features SET " . implode(", ", $setParts) . ", modifieddate = NOW() WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $upStmt = $conn->prepare($sql);
        if (!$upStmt) {
            api_error('Database prepare error: ' . $conn->error, 500);
        }
        $upStmt->bind_param($types, ...$params);
        $upStmt->execute();
        $upStmt->close();

        // Log Issue Change
        if (!empty($changesDesc)) {
            $newStatus = $input['status'] ?? ($oldIssue['status'] ?? 'Open');
            $newProg = isset($input['progress_percent']) ? (int)$input['progress_percent'] : ($oldIssue['progress_percent'] ?? 0);
            log_issue_change($conn, [
                'issue_id' => $id,
                'feature_id' => $input['feature_id'] ?? ($oldIssue['feature_id'] ?? null),
                'module' => $input['module'] ?? ($oldIssue['module'] ?? 'General'),
                'feature' => $input['feature'] ?? ($oldIssue['feature'] ?? 'Feature Issue'),
                'script' => $input['script'] ?? ($oldIssue['script'] ?? ''),
                'platform' => $input['platform'] ?? ($oldIssue['platform'] ?? 'Dashboard'),
                'action' => 'updated',
                'old_status' => $oldIssue['status'] ?? null,
                'new_status' => $newStatus,
                'old_progress' => $oldIssue['progress_percent'] ?? null,
                'new_progress' => $newProg,
                'changes_summary' => implode("; ", $changesDesc),
                'updated_by' => $modifiedBy
            ]);
        }

        api_response('success', 'Issue updated successfully', ['id' => $id, 'modified_by' => $modifiedBy], 200);
        break;

    case 'delete':
    case 'remove':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for deletion', 422);
        }

        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['updated_by'] ?? 'Admin';

        // Fetch old issue before deleting
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldIssue = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        $delStmt = $conn->prepare("DELETE FROM eimbox_features WHERE id = ?");
        if (!$delStmt) {
            api_error('Database prepare error: ' . $conn->error, 500);
        }
        $delStmt->bind_param("i", $id);
        $delStmt->execute();
        $affected = $delStmt->affected_rows;
        $delStmt->close();

        // Log Issue Change
        if ($oldIssue) {
            log_issue_change($conn, [
                'issue_id' => $id,
                'feature_id' => $oldIssue['feature_id'] ?? null,
                'module' => $oldIssue['module'] ?? 'General',
                'feature' => $oldIssue['feature'] ?? 'Feature Issue',
                'script' => $oldIssue['script'] ?? '',
                'platform' => $oldIssue['platform'] ?? 'Dashboard',
                'action' => 'deleted',
                'old_status' => $oldIssue['status'] ?? null,
                'new_status' => 'Deleted',
                'old_progress' => $oldIssue['progress_percent'] ?? null,
                'new_progress' => null,
                'changes_summary' => "Issue #$id deleted (Topic: '{$oldIssue['topic']}')",
                'updated_by' => $userEmail
            ]);
        }

        api_response('success', 'Issue deleted successfully', ['id' => $id, 'affected' => $affected], 200);
        break;

    case 'quick_status':
        $id = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $progress = isset($input['progress_percent']) ? (int)$input['progress_percent'] : null;
        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['modified_by'] ?? 'Admin';
        $modifiedBy = trim($input['modified_by'] ?? $userEmail);

        if ($id <= 0 || empty($status)) {
            api_error('ID and Status are required', 422);
        }

        // Fetch old issue
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ? LIMIT 1");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldIssue = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        if ($progress !== null) {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, `progress_percent` = ?, `modified_by` = ?, `modifieddate` = NOW() WHERE id = ?");
            if (!$stmt) {
                api_error('Database prepare error: ' . $conn->error, 500);
            }
            $stmt->bind_param("sisi", $status, $progress, $modifiedBy, $id);
        } else {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, `modified_by` = ?, `modifieddate` = NOW() WHERE id = ?");
            if (!$stmt) {
                api_error('Database prepare error: ' . $conn->error, 500);
            }
            $stmt->bind_param("ssi", $status, $modifiedBy, $id);
        }
        $stmt->execute();
        $stmt->close();

        // Log Issue Change
        if ($oldIssue) {
            $newProg = $progress !== null ? $progress : ($oldIssue['progress_percent'] ?? 0);
            log_issue_change($conn, [
                'issue_id' => $id,
                'feature_id' => $oldIssue['feature_id'] ?? null,
                'module' => $oldIssue['module'] ?? 'General',
                'feature' => $oldIssue['feature'] ?? 'Feature Issue',
                'script' => $oldIssue['script'] ?? '',
                'platform' => $oldIssue['platform'] ?? 'Dashboard',
                'action' => 'status_changed',
                'old_status' => $oldIssue['status'] ?? null,
                'new_status' => $status,
                'old_progress' => $oldIssue['progress_percent'] ?? null,
                'new_progress' => $newProg,
                'changes_summary' => "Quick status change: status '{$oldIssue['status']}' -> '$status', progress: '{$oldIssue['progress_percent']}%' -> '$newProg%'",
                'updated_by' => $modifiedBy
            ]);
        }

        api_response('success', 'Status updated successfully', ['id' => $id, 'status' => $status, 'modified_by' => $modifiedBy], 200);
        break;

    default:
        api_error("Unknown action: $action", 400);
}
