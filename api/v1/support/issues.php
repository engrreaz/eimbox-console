<?php
/**
 * EIMBox REST API — Issue Tracker & Screen Status Audit Matrix Engine
 * Endpoint: /api/v1/support/issues.php (Alias: /api/v1/issues/index.php)
 * 
 * Supports 12 Core Dimensions:
 *   - ui, view, insert, update, delete, cache, push, pull, dropdown, modal, print, pdf
 * Supports Granular Child Issues:
 *   - screen_issues (multi-task tracking per route)
 */

require_once __DIR__ . '/../bootstrap.php';

// 1. Ensure `issues_tracker` table exists in MySQL with complete 12-dimension schema
$conn->query("CREATE TABLE IF NOT EXISTS `issues_tracker` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) DEFAULT NULL,
  `route` VARCHAR(200) DEFAULT NULL,
  `ui` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `view` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `insert` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `update` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `delete` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `cache` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `push` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `pull` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `dropdown` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `modal` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `print` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `pdf` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested',
  `notes` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `modifieddate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_route_unique` (`route`),
  INDEX `idx_modifieddate` (`modifieddate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Ensure all 12 dimensions exist in MySQL issues_tracker
$allDimCols = ['dropdown', 'modal', 'print', 'pdf'];
foreach ($allDimCols as $col) {
    $colCheck = $conn->query("SHOW COLUMNS FROM `issues_tracker` LIKE '$col'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE `issues_tracker` ADD COLUMN `$col` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested'");
    }
}

// 2. Ensure `screen_issues` child table exists in MySQL
$conn->query("CREATE TABLE IF NOT EXISTS `screen_issues` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `screen_id` INT DEFAULT 0,
  `route` VARCHAR(200) NOT NULL,
  `screen_title` VARCHAR(100) DEFAULT '',
  `issue_title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `dimension` VARCHAR(50) DEFAULT 'general',
  `priority` VARCHAR(50) DEFAULT 'Medium',
  `status` VARCHAR(50) DEFAULT 'Open',
  `progress_pct` INT DEFAULT 0,
  `assigned_to` VARCHAR(100) DEFAULT '',
  `created_by` VARCHAR(100) DEFAULT 'Admin',
  `resolved_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `modifieddate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_si_route` (`route`),
  INDEX `idx_si_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = get_api_input();
$action = $_GET['action'] ?? $input['action'] ?? '';

// Helper function to validate enum fields
function sanitize_issue_enum($val) {
    $allowed = ['Not Tested', 'OK', 'Error', 'warning', 'Issues'];
    return in_array($val, $allowed, true) ? $val : 'Not Tested';
}

// ----------------------------------------------------
// 1. GET Request Handler
// ----------------------------------------------------
if ($method === 'GET') {
    
    // Action A: Aggregate Summary & Health Statistics for 12 Dimensions
    if ($action === 'stats') {
        $allRows = $conn->query("SELECT * FROM `issues_tracker`");
        $total = 0;
        $okCount = 0;
        $warningCount = 0;
        $issuesCount = 0;
        $errorCount = 0;
        $notTestedCount = 0;
        $totalHealthSum = 0;
        $auditedCount = 0;

        $dimFields = ['ui', 'view', 'insert', 'update', 'delete', 'cache', 'push', 'pull', 'dropdown', 'modal', 'print', 'pdf'];

        if ($allRows) {
            while ($row = $allRows->fetch_assoc()) {
                $total++;
                $rowOk = 0;
                $rowWarn = 0;
                $rowIssues = 0;
                $rowNotTested = 0;

                foreach ($dimFields as $f) {
                    $v = $row[$f] ?? 'Not Tested';
                    if ($v === 'OK') $rowOk++;
                    else if ($v === 'warning') $rowWarn++;
                    else if ($v === 'Issues' || $v === 'Error') $rowIssues++;
                    else $rowNotTested++;
                }

                $tested = 12 - $rowNotTested;
                if ($tested === 0) {
                    $notTestedCount++;
                } else if ($rowIssues > 0) {
                    $issuesCount++;
                    $dimScore = (($rowOk * 100) + ($rowWarn * 50)) / 12;
                    $healthScore = max(10, min(85, round($dimScore - ($rowIssues * 10))));
                    $totalHealthSum += $healthScore;
                    $auditedCount++;
                } else if ($rowWarn > 0 || $rowNotTested > 0) {
                    $warningCount++;
                    $healthScore = max(50, min(95, round((($rowOk * 100) + ($rowWarn * 65) + ($rowNotTested * 50)) / 12)));
                    $totalHealthSum += $healthScore;
                    $auditedCount++;
                } else {
                    $okCount++;
                    $totalHealthSum += 100;
                    $auditedCount++;
                }
            }
        }

        $avgHealth = $auditedCount > 0 ? round($totalHealthSum / $auditedCount) : 0;

        api_response('success', 'Issue statistics calculated successfully.', [
            'total' => $total,
            'auditedCount' => $auditedCount,
            'avgHealth' => $avgHealth,
            'notTested' => $notTestedCount,
            'ok' => $okCount,
            'warning' => $warningCount,
            'issues' => $issuesCount,
            'error' => $errorCount
        ]);
    }

    // Action B: Granular Screen Issues Stats (Route specific or global)
    if ($action === 'screen_issues_stats') {
        $route = trim($_GET['route'] ?? $input['route'] ?? '');
        $sql = "SELECT * FROM `screen_issues`";
        $params = [];
        $types = '';

        if (!empty($route)) {
            $sql .= " WHERE `route` = ?";
            $params[] = $route;
            $types .= 's';
        }

        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $total = 0;
        $openCount = 0;
        $inProgressCount = 0;
        $resolvedCount = 0;
        $criticalCount = 0;
        $totalProgress = 0;

        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $total++;
                $st = strtolower(trim($r['status'] ?? ''));
                if ($st === 'resolved' || $st === 'closed') {
                    $resolvedCount++;
                } else if ($st === 'in progress' || $st === 'in review') {
                    $inProgressCount++;
                } else {
                    $openCount++;
                }

                if (($r['priority'] ?? '') === 'Critical') {
                    $criticalCount++;
                }

                $totalProgress += intval($r['progress_pct'] ?? 0);
            }
        }

        $avgProgress = $total > 0 ? round($totalProgress / $total) : 0;

        api_response('success', 'Screen child issues stats retrieved.', [
            'route' => $route,
            'total' => $total,
            'open' => $openCount,
            'inProgress' => $inProgressCount,
            'resolved' => $resolvedCount,
            'critical' => $criticalCount,
            'avgProgress' => $avgProgress
        ]);
    }

    // Action C: List Granular Child Screen Issues
    if ($action === 'screen_issues') {
        $route = trim($_GET['route'] ?? $input['route'] ?? '');
        $status = trim($_GET['status'] ?? $input['status'] ?? '');
        $priority = trim($_GET['priority'] ?? $input['priority'] ?? '');

        $where = [];
        $params = [];
        $types = '';

        if (!empty($route)) {
            $where[] = "`route` = ?";
            $params[] = $route;
            $types .= 's';
        }
        if (!empty($status) && $status !== 'all') {
            $where[] = "`status` = ?";
            $params[] = $status;
            $types .= 's';
        }
        if (!empty($priority) && $priority !== 'all') {
            $where[] = "`priority` = ?";
            $params[] = $priority;
            $types .= 's';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT * FROM `screen_issues` $whereSql ORDER BY `modifieddate` DESC, `id` DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();

        $items = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['id'] = intval($row['id']);
                $row['screen_id'] = intval($row['screen_id']);
                $row['progress_pct'] = intval($row['progress_pct']);
                $items[] = $row;
            }
        }

        api_response('success', 'Child screen issues loaded.', $items);
    }

    // Action D: Fetch Single Child Issue by ID
    if ($action === 'screen_issue_by_id') {
        $id = intval($_GET['id'] ?? $input['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid issue ID is required.', null, 400);
        }

        $stmt = $conn->prepare("SELECT * FROM `screen_issues` WHERE `id` = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $record = $res ? $res->fetch_assoc() : null;

        if ($record) {
            $record['id'] = intval($record['id']);
            $record['screen_id'] = intval($record['screen_id']);
            $record['progress_pct'] = intval($record['progress_pct']);
            api_response('success', 'Child screen issue retrieved.', $record);
        } else {
            api_response('error', 'Child screen issue not found.', null, 404);
        }
    }

    // Action E: Fetch Single Issue Matrix Entry by ID or Route
    if ($action === 'get' || (isset($_GET['id']) && $action !== 'screen_issue_by_id') || (isset($_GET['route']) && $action === 'by_route')) {
        $id = intval($_GET['id'] ?? $input['id'] ?? 0);
        $route = trim($_GET['route'] ?? $input['route'] ?? '');

        if ($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM `issues_tracker` WHERE `id` = ? LIMIT 1");
            $stmt->bind_param("i", $id);
        } elseif (!empty($route)) {
            $stmt = $conn->prepare("SELECT * FROM `issues_tracker` WHERE `route` = ? ORDER BY `modifieddate` DESC LIMIT 1");
            $stmt->bind_param("s", $route);
        } else {
            api_response('error', 'Please provide a valid ID or route parameter.', null, 400);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $record = $res ? $res->fetch_assoc() : null;

        if ($record) {
            $record['id'] = intval($record['id']);
            api_response('success', 'Issue entry retrieved successfully.', $record);
        } else {
            api_response('error', 'Issue entry not found.', null, 404);
        }
    }

    // Action F: List Issues Matrix with 12-dimension Filters
    $route = trim($_GET['route'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $limit = max(1, min(1000, intval($_GET['limit'] ?? 500)));
    $offset = max(0, intval($_GET['offset'] ?? 0));

    $whereClauses = [];
    $params = [];
    $types = '';

    if (!empty($route)) {
        $whereClauses[] = "`route` = ?";
        $params[] = $route;
        $types .= 's';
    }

    if (!empty($status) && $status !== 'all') {
        $cleanStatus = sanitize_issue_enum($status);
        $whereClauses[] = "(`ui` = ? OR `view` = ? OR `insert` = ? OR `update` = ? OR `delete` = ? OR `cache` = ? OR `push` = ? OR `pull` = ? OR `dropdown` = ? OR `modal` = ? OR `print` = ? OR `pdf` = ?)";
        for ($i = 0; $i < 12; $i++) {
            $params[] = $cleanStatus;
            $types .= 's';
        }
    }

    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $whereClauses[] = "(`title` LIKE ? OR `route` LIKE ? OR `notes` LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

    // Total filtered count
    $countSql = "SELECT COUNT(*) AS total FROM `issues_tracker` $whereSql";
    $countStmt = $conn->prepare($countSql);
    if (!empty($types)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $totalCount = ($countRes && $r = $countRes->fetch_assoc()) ? intval($r['total']) : 0;

    // Fetch records
    $dataSql = "SELECT * FROM `issues_tracker` $whereSql ORDER BY `modifieddate` DESC, `id` DESC LIMIT ? OFFSET ?";
    $dataStmt = $conn->prepare($dataSql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $dataStmt->bind_param($types . 'ii', ...$allParams);
    $dataStmt->execute();
    $dataRes = $dataStmt->get_result();

    $items = [];
    if ($dataRes) {
        while ($row = $dataRes->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $items[] = $row;
        }
    }

    api_response('success', 'Issues list loaded successfully.', [
        'count' => count($items),
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset,
        'items' => $items
    ]);
}

// ----------------------------------------------------
// 2. POST / PUT Request Handler
// ----------------------------------------------------
if ($method === 'POST' || $method === 'PUT') {

    // Action A: Delete by POST (issues_tracker or screen_issues)
    if ($action === 'delete') {
        $id = intval($input['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid issue ID is required to delete.', null, 400);
        }
        $delStmt = $conn->prepare("DELETE FROM `issues_tracker` WHERE `id` = ?");
        $delStmt->bind_param("i", $id);
        $delStmt->execute();

        if ($delStmt->affected_rows > 0) {
            api_response('success', "Issue #$id deleted successfully.");
        } else {
            api_response('error', "Issue #$id not found or already deleted.", null, 404);
        }
    }

    // Action B: Delete Child Screen Issue
    if ($action === 'delete_screen_issue') {
        $id = intval($input['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid child issue ID is required to delete.', null, 400);
        }
        $delStmt = $conn->prepare("DELETE FROM `screen_issues` WHERE `id` = ?");
        $delStmt->bind_param("i", $id);
        $delStmt->execute();

        if ($delStmt->affected_rows > 0) {
            api_response('success', "Child issue #$id deleted successfully.");
        } else {
            api_response('error', "Child issue #$id not found or already deleted.", null, 404);
        }
    }

    // Action C: Save / Upsert Single Child Screen Issue
    if ($action === 'save_screen_issue') {
        $id = intval($input['id'] ?? 0);
        $screenId = intval($input['screen_id'] ?? 0);
        $route = mb_substr(trim($input['route'] ?? ''), 0, 200);
        $screenTitle = mb_substr(trim($input['screen_title'] ?? ''), 0, 100);
        $issueTitle = mb_substr(trim($input['issue_title'] ?? ''), 0, 255);
        $description = trim($input['description'] ?? '');
        $dimension = mb_substr(trim($input['dimension'] ?? 'general'), 0, 50);
        $priority = mb_substr(trim($input['priority'] ?? 'Medium'), 0, 50);
        $status = mb_substr(trim($input['status'] ?? 'Open'), 0, 50);
        $progressPct = max(0, min(100, intval($input['progress_pct'] ?? 0)));
        $assignedTo = mb_substr(trim($input['assigned_to'] ?? ''), 0, 100);
        $createdBy = mb_substr(trim($input['created_by'] ?? 'Admin'), 0, 100);
        $resolvedAt = ($status === 'Resolved' || $status === 'Closed') ? date('Y-m-d H:i:s') : null;

        if (empty($route) || empty($issueTitle)) {
            api_response('error', 'Both route and issue_title are required.', null, 400);
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE `screen_issues` SET 
                `screen_id` = ?, `route` = ?, `screen_title` = ?, `issue_title` = ?, `description` = ?,
                `dimension` = ?, `priority` = ?, `status` = ?, `progress_pct` = ?, `assigned_to` = ?,
                `resolved_at` = ?, `modifieddate` = CURRENT_TIMESTAMP
                WHERE `id` = ?");
            $stmt->bind_param("isssssssissi", $screenId, $route, $screenTitle, $issueTitle, $description, $dimension, $priority, $status, $progressPct, $assignedTo, $resolvedAt, $id);
            $stmt->execute();

            api_response('success', "Child issue #$id updated successfully.", [
                'id' => $id,
                'route' => $route,
                'issue_title' => $issueTitle
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO `screen_issues` 
                (`screen_id`, `route`, `screen_title`, `issue_title`, `description`, `dimension`, `priority`, `status`, `progress_pct`, `assigned_to`, `created_by`, `resolved_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssisss", $screenId, $route, $screenTitle, $issueTitle, $description, $dimension, $priority, $status, $progressPct, $assignedTo, $createdBy, $resolvedAt);
            $stmt->execute();
            $newId = $conn->insert_id;

            api_response('success', "New child issue #$newId recorded successfully.", [
                'id' => $newId,
                'route' => $route,
                'issue_title' => $issueTitle
            ], 201);
        }
    }

    // Action D: Batch Sync Child Screen Issues
    if ($action === 'batch_screen_issues') {
        $items = $input['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            api_response('error', 'No child items provided for batch sync.', null, 400);
        }

        $processed = 0;
        $stmt = $conn->prepare("INSERT INTO `screen_issues` 
            (`id`, `screen_id`, `route`, `screen_title`, `issue_title`, `description`, `dimension`, `priority`, `status`, `progress_pct`, `assigned_to`, `created_by`, `resolved_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                `screen_id` = VALUES(`screen_id`),
                `route` = VALUES(`route`),
                `screen_title` = VALUES(`screen_title`),
                `issue_title` = VALUES(`issue_title`),
                `description` = VALUES(`description`),
                `dimension` = VALUES(`dimension`),
                `priority` = VALUES(`priority`),
                `status` = VALUES(`status`),
                `progress_pct` = VALUES(`progress_pct`),
                `assigned_to` = VALUES(`assigned_to`),
                `resolved_at` = VALUES(`resolved_at`),
                `modifieddate` = CURRENT_TIMESTAMP");

        foreach ($items as $c) {
            $id = intval($c['id'] ?? 0);
            $screenId = intval($c['screen_id'] ?? 0);
            $route = mb_substr(trim($c['route'] ?? ''), 0, 200);
            $screenTitle = mb_substr(trim($c['screen_title'] ?? ''), 0, 100);
            $issueTitle = mb_substr(trim($c['issue_title'] ?? ''), 0, 255);
            $description = trim($c['description'] ?? '');
            $dimension = mb_substr(trim($c['dimension'] ?? 'general'), 0, 50);
            $priority = mb_substr(trim($c['priority'] ?? 'Medium'), 0, 50);
            $status = mb_substr(trim($c['status'] ?? 'Open'), 0, 50);
            $progressPct = max(0, min(100, intval($c['progress_pct'] ?? 0)));
            $assignedTo = mb_substr(trim($c['assigned_to'] ?? ''), 0, 100);
            $createdBy = mb_substr(trim($c['created_by'] ?? 'Admin'), 0, 100);
            $resolvedAt = !empty($c['resolved_at']) ? $c['resolved_at'] : (($status === 'Resolved' || $status === 'Closed') ? date('Y-m-d H:i:s') : null);

            if (empty($route) || empty($issueTitle)) continue;

            $stmt->bind_param("iissssssissss", $id, $screenId, $route, $screenTitle, $issueTitle, $description, $dimension, $priority, $status, $progressPct, $assignedTo, $createdBy, $resolvedAt);
            if ($stmt->execute()) {
                $processed++;
            }
        }

        api_response('success', "Batch sync of child issues completed. Processed $processed items.", [
            'processed' => $processed
        ]);
    }

    // Action E: Batch Sync 12-Dimension Issues Matrix
    if ($action === 'batch' || isset($input['items'])) {
        $items = $input['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            api_response('error', 'No items provided for batch upsert.', null, 400);
        }

        $upsertCount = 0;
        $stmt = $conn->prepare("INSERT INTO `issues_tracker` 
            (`title`, `route`, `ui`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `dropdown`, `modal`, `print`, `pdf`, `notes`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                `title` = VALUES(`title`),
                `ui` = VALUES(`ui`),
                `view` = VALUES(`view`),
                `insert` = VALUES(`insert`),
                `update` = VALUES(`update`),
                `delete` = VALUES(`delete`),
                `cache` = VALUES(`cache`),
                `push` = VALUES(`push`),
                `pull` = VALUES(`pull`),
                `dropdown` = VALUES(`dropdown`),
                `modal` = VALUES(`modal`),
                `print` = VALUES(`print`),
                `pdf` = VALUES(`pdf`),
                `notes` = VALUES(`notes`),
                `modifieddate` = CURRENT_TIMESTAMP");

        foreach ($items as $item) {
            $t = mb_substr(trim($item['title'] ?? ''), 0, 100);
            $r = mb_substr(trim($item['route'] ?? ''), 0, 200);
            if (empty($r)) continue;

            $ui = sanitize_issue_enum($item['ui'] ?? 'Not Tested');
            $view = sanitize_issue_enum($item['view'] ?? 'Not Tested');
            $ins = sanitize_issue_enum($item['insert'] ?? 'Not Tested');
            $upd = sanitize_issue_enum($item['update'] ?? 'Not Tested');
            $del = sanitize_issue_enum($item['delete'] ?? 'Not Tested');
            $cache = sanitize_issue_enum($item['cache'] ?? 'Not Tested');
            $push = sanitize_issue_enum($item['push'] ?? 'Not Tested');
            $pull = sanitize_issue_enum($item['pull'] ?? 'Not Tested');
            $dropdown = sanitize_issue_enum($item['dropdown'] ?? 'Not Tested');
            $modal = sanitize_issue_enum($item['modal'] ?? 'Not Tested');
            $print = sanitize_issue_enum($item['print'] ?? 'Not Tested');
            $pdf = sanitize_issue_enum($item['pdf'] ?? 'Not Tested');
            $notes = mb_substr(trim($item['notes'] ?? ''), 0, 500);

            $stmt->bind_param("sssssssssssssss", $t, $r, $ui, $view, $ins, $upd, $del, $cache, $push, $pull, $dropdown, $modal, $print, $pdf, $notes);
            if ($stmt->execute()) {
                $upsertCount++;
            }
        }

        api_response('success', "Batch sync completed. Processed $upsertCount items.", [
            'processed' => $upsertCount
        ]);
    }

    // Action F: Single Upsert for 12-Dimension Matrix Entry
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $route = mb_substr(trim($input['route'] ?? ''), 0, 200);
    $title = mb_substr(trim($input['title'] ?? ''), 0, 100);

    if (empty($route)) {
        api_response('error', 'Route name is required.', null, 400);
    }
    if (empty($title)) {
        $title = $route;
    }

    $ui = sanitize_issue_enum($input['ui'] ?? 'Not Tested');
    $view = sanitize_issue_enum($input['view'] ?? 'Not Tested');
    $insert = sanitize_issue_enum($input['insert'] ?? 'Not Tested');
    $update = sanitize_issue_enum($input['update'] ?? 'Not Tested');
    $delete = sanitize_issue_enum($input['delete'] ?? 'Not Tested');
    $cache = sanitize_issue_enum($input['cache'] ?? 'Not Tested');
    $push = sanitize_issue_enum($input['push'] ?? 'Not Tested');
    $pull = sanitize_issue_enum($input['pull'] ?? 'Not Tested');
    $dropdown = sanitize_issue_enum($input['dropdown'] ?? 'Not Tested');
    $modal = sanitize_issue_enum($input['modal'] ?? 'Not Tested');
    $print = sanitize_issue_enum($input['print'] ?? 'Not Tested');
    $pdf = sanitize_issue_enum($input['pdf'] ?? 'Not Tested');
    $notes = mb_substr(trim($input['notes'] ?? ''), 0, 500);

    if ($id > 0) {
        // Update existing by ID
        $updateStmt = $conn->prepare("UPDATE `issues_tracker` 
            SET `title` = ?, `route` = ?, `ui` = ?, `view` = ?, `insert` = ?, `update` = ?, `delete` = ?, `cache` = ?, `push` = ?, `pull` = ?, `dropdown` = ?, `modal` = ?, `print` = ?, `pdf` = ?, `notes` = ?
            WHERE `id` = ?");
        $updateStmt->bind_param("sssssssssssssssi", $title, $route, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $dropdown, $modal, $print, $pdf, $notes, $id);
        $updateStmt->execute();

        api_response('success', "Issue record #$id updated successfully.", [
            'id' => $id,
            'route' => $route,
            'title' => $title
        ]);
    } else {
        // Check if route already exists
        $checkStmt = $conn->prepare("SELECT `id` FROM `issues_tracker` WHERE `route` = ? LIMIT 1");
        $checkStmt->bind_param("s", $route);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        $existing = $checkRes ? $checkRes->fetch_assoc() : null;

        if ($existing && !empty($existing['id'])) {
            $existingId = intval($existing['id']);
            $updateStmt = $conn->prepare("UPDATE `issues_tracker` 
                SET `title` = ?, `ui` = ?, `view` = ?, `insert` = ?, `update` = ?, `delete` = ?, `cache` = ?, `push` = ?, `pull` = ?, `dropdown` = ?, `modal` = ?, `print` = ?, `pdf` = ?, `notes` = ?
                WHERE `id` = ?");
            $updateStmt->bind_param("ssssssssssssssi", $title, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $dropdown, $modal, $print, $pdf, $notes, $existingId);
            $updateStmt->execute();

            api_response('success', "Issue record updated for route '$route'.", [
                'id' => $existingId,
                'route' => $route,
                'title' => $title
            ]);
        } else {
            // Insert new record
            $insStmt = $conn->prepare("INSERT INTO `issues_tracker` 
                (`title`, `route`, `ui`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `dropdown`, `modal`, `print`, `pdf`, `notes`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insStmt->bind_param("sssssssssssssss", $title, $route, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $dropdown, $modal, $print, $pdf, $notes);
            $insStmt->execute();
            $newId = $conn->insert_id;

            api_response('success', "New issue record #$newId created successfully.", [
                'id' => $newId,
                'route' => $route,
                'title' => $title
            ], 201);
        }
    }
}

// ----------------------------------------------------
// 3. DELETE Request Handler
// ----------------------------------------------------
if ($method === 'DELETE') {
    if ($action === 'delete_screen_issue') {
        $id = intval($_GET['id'] ?? $input['id'] ?? 0);
        if ($id <= 0) {
            api_response('error', 'Valid child issue ID is required to delete.', null, 400);
        }

        $delStmt = $conn->prepare("DELETE FROM `screen_issues` WHERE `id` = ?");
        $delStmt->bind_param("i", $id);
        $delStmt->execute();

        if ($delStmt->affected_rows > 0) {
            api_response('success', "Child issue #$id deleted successfully.");
        } else {
            api_response('error', "Child issue #$id not found or already deleted.", null, 404);
        }
    }

    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid issue ID is required to delete.', null, 400);
    }

    $delStmt = $conn->prepare("DELETE FROM `issues_tracker` WHERE `id` = ?");
    $delStmt->bind_param("i", $id);
    $delStmt->execute();

    if ($delStmt->affected_rows > 0) {
        api_response('success', "Issue #$id deleted successfully.");
    } else {
        api_response('error', "Issue #$id not found or already deleted.", null, 404);
    }
}

api_response('error', "Unsupported request method '$method'.", null, 405);
