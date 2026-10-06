<?php
/**
 * EIMBox Issue Tracker API - Manage Issues
 * Handles CRUD operations for eimbox_features table and logs all changes into issues_change_logs.
 */
require_once __DIR__ . '/../bootstrap.php';

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
    $actionName = $logData['action'] ?? 'updated';
    $oldStatus = $logData['old_status'] ?? null;
    $newStatus = $logData['new_status'] ?? null;
    $oldProg = isset($logData['old_progress']) ? (int)$logData['old_progress'] : null;
    $newProg = isset($logData['new_progress']) ? (int)$logData['new_progress'] : null;
    $summary = $logData['changes_summary'] ?? null;
    $updatedBy = $logData['updated_by'] ?? 'Admin';

    $stmt = $conn->prepare("INSERT INTO issues_change_logs (`issue_id`, `feature_id`, `module`, `feature`, `script`, `platform`, `action`, `old_status`, `new_status`, `old_progress`, `new_progress`, `changes_summary`, `updated_by`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iisssssssiiss", $issueId, $featureId, $module, $feature, $script, $platform, $actionName, $oldStatus, $newStatus, $oldProg, $newProg, $summary, $updatedBy);
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

        // Audit Log
        log_issue_change($conn, [
            'issue_id' => $newId,
            'feature_id' => $featureId,
            'module' => $module,
            'feature' => $topic ?: $feature,
            'script' => $script,
            'platform' => $platform,
            'action' => 'created',
            'old_status' => null,
            'new_status' => $status,
            'old_progress' => null,
            'new_progress' => $progress,
            'changes_summary' => "Issue created: " . ($topic ?: $feature) . " [Priority: $priority, Status: $status]",
            'updated_by' => $createdBy
        ]);

        api_response('success', 'Issue registered successfully', [
            'id' => $newId, 
            'status' => $status, 
            'created_by' => $createdBy, 
            'modified_by' => $modifiedBy
        ], 201);
        break;

    case 'update':
    case 'edit':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for update', 422);
        }

        // Fetch old issue data for diffing
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ?");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRow = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        if (!$oldRow) {
            api_error('Issue not found', 404);
        }

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
        $diffChanges = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $setParts[] = "`$field` = ?";
                $newVal = $input[$field];
                $oldVal = $oldRow[$field] ?? null;

                if ($field === 'feature_id' || $field === 'progress_percent') {
                    $newValInt = (int)$newVal;
                    $params[] = $newValInt;
                    $types .= "i";
                    if ((int)$oldVal !== $newValInt) {
                        $diffChanges[] = "$field: $oldVal -> $newValInt";
                    }
                } else {
                    $params[] = $newVal;
                    $types .= "s";
                    if ((string)$oldVal !== (string)$newVal) {
                        $diffChanges[] = "$field: '$oldVal' -> '$newVal'";
                    }
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
        $upStmt->bind_param($types, ...$params);
        $upStmt->execute();
        $upStmt->close();

        // Audit Log
        $newStatus = $input['status'] ?? $oldRow['status'];
        $newProg = isset($input['progress_percent']) ? (int)$input['progress_percent'] : (int)$oldRow['progress_percent'];
        $actType = ($oldRow['status'] !== $newStatus) ? 'status_changed' : 'updated';

        log_issue_change($conn, [
            'issue_id' => $id,
            'feature_id' => $input['feature_id'] ?? $oldRow['feature_id'],
            'module' => $input['module'] ?? $oldRow['module'],
            'feature' => $input['topic'] ?? $input['feature'] ?? $oldRow['topic'] ?? $oldRow['feature'],
            'script' => $input['script'] ?? $oldRow['script'],
            'platform' => $input['platform'] ?? $oldRow['platform'],
            'action' => $actType,
            'old_status' => $oldRow['status'],
            'new_status' => $newStatus,
            'old_progress' => (int)$oldRow['progress_percent'],
            'new_progress' => $newProg,
            'changes_summary' => !empty($diffChanges) ? implode(", ", $diffChanges) : "Updated issue #$id",
            'updated_by' => $modifiedBy
        ]);

        api_response('success', 'Issue updated successfully', [
            'id' => $id, 
            'modified_by' => $modifiedBy,
            'changes' => $diffChanges
        ], 200);
        break;

    case 'delete':
    case 'remove':
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) {
            api_error('Invalid issue ID for deletion', 422);
        }

        // Fetch old row before delete
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ?");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRow = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['deleted_by'] ?? 'Admin';

        if ($oldRow) {
            log_issue_change($conn, [
                'issue_id' => $id,
                'feature_id' => $oldRow['feature_id'],
                'module' => $oldRow['module'],
                'feature' => $oldRow['topic'] ?: $oldRow['feature'],
                'script' => $oldRow['script'],
                'platform' => $oldRow['platform'],
                'action' => 'deleted',
                'old_status' => $oldRow['status'],
                'new_status' => 'Deleted',
                'old_progress' => (int)$oldRow['progress_percent'],
                'new_progress' => 0,
                'changes_summary' => "Issue #$id deleted: " . ($oldRow['topic'] ?: $oldRow['feature']),
                'updated_by' => $userEmail
            ]);
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
        $userEmail = $_SESSION['user_email'] ?? $_SESSION['email'] ?? $input['modified_by'] ?? 'Admin';
        $modifiedBy = trim($input['modified_by'] ?? $userEmail);

        if ($id <= 0 || empty($status)) {
            api_error('ID and Status are required', 422);
        }

        // Fetch old row
        $oldStmt = $conn->prepare("SELECT * FROM eimbox_features WHERE id = ?");
        $oldStmt->bind_param("i", $id);
        $oldStmt->execute();
        $oldRow = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        if ($progress !== null) {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, `progress_percent` = ?, `modified_by` = ?, `modifieddate` = NOW() WHERE id = ?");
            $stmt->bind_param("sisi", $status, $progress, $modifiedBy, $id);
        } else {
            $stmt = $conn->prepare("UPDATE eimbox_features SET `status` = ?, `modified_by` = ?, `modifieddate` = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $status, $modifiedBy, $id);
        }
        $stmt->execute();
        $stmt->close();

        if ($oldRow) {
            log_issue_change($conn, [
                'issue_id' => $id,
                'feature_id' => $oldRow['feature_id'],
                'module' => $oldRow['module'],
                'feature' => $oldRow['topic'] ?: $oldRow['feature'],
                'script' => $oldRow['script'],
                'platform' => $oldRow['platform'],
                'action' => 'status_changed',
                'old_status' => $oldRow['status'],
                'new_status' => $status,
                'old_progress' => (int)$oldRow['progress_percent'],
                'new_progress' => $progress !== null ? (int)$progress : (int)$oldRow['progress_percent'],
                'changes_summary' => "Status changed from '{$oldRow['status']}' to '$status'" . ($progress !== null ? ", Progress: {$progress}%" : ""),
                'updated_by' => $modifiedBy
            ]);
        }

        api_response('success', 'Status updated successfully', [
            'id' => $id, 
            'status' => $status, 
            'modified_by' => $modifiedBy
        ], 200);
        break;

    default:
        api_error("Unknown action: $action", 400);
}
