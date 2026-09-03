<?php
/**
 * EIMBox REST API — Issue Tracker & Screen Status Audit Matrix Engine
 * Endpoint: /api/v1/support/issues.php
 * 
 * Supports:
 *   - GET  [?route=X&status=Y&search=Z&limit=N&offset=M] : Fetch issues list with filtering
 *   - GET  ?action=stats                                : Get aggregate status breakdown metrics
 *   - GET  ?action=get&id=X                             : Fetch single issue by ID
 *   - GET  ?action=get&route=X                          : Fetch single issue by route
 *   - POST [action=save|create]                         : Create or update issue audit entry
 *   - POST action=batch                                 : Batch upsert multiple issue records
 *   - PUT                                               : Update existing issue audit entry
 *   - DELETE [?id=X] / POST [action=delete]             : Delete issue entry by ID
 */

require_once __DIR__ . '/../bootstrap.php';

// 1. Ensure `issues_tracker` & `screen_issues` tables exist in MySQL with complete schema
$conn->query("CREATE TABLE IF NOT EXISTS `issues_tracker` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(50) DEFAULT NULL,
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

// Ensure missing columns exist in existing MySQL issues_tracker
$newCols = ['dropdown', 'modal', 'print', 'pdf'];
foreach ($newCols as $col) {
    $colCheck = $conn->query("SHOW COLUMNS FROM `issues_tracker` LIKE '$col'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE `issues_tracker` ADD COLUMN `$col` ENUM('Not Tested', 'OK', 'Error', 'warning', 'Issues') NOT NULL DEFAULT 'Not Tested'");
    }
}

// Ensure `screen_issues` child table exists
$conn->query("CREATE TABLE IF NOT EXISTS `screen_issues` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `screen_id` INT DEFAULT 0,
  `route` VARCHAR(200) NOT NULL,
  `screen_title` VARCHAR(50) DEFAULT '',
  `issue_title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `dimension` VARCHAR(50) DEFAULT 'General',
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
    // Action A: Aggregate Summary & Status Stats
    if ($action === 'stats') {
        $statsSql = "SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN `ui` = 'Error' OR `view` = 'Error' OR `insert` = 'Error' OR `update` = 'Error' OR `delete` = 'Error' OR `cache` = 'Error' OR `push` = 'Error' OR `pull` = 'Error' THEN 1 ELSE 0 END) AS error_count,
            SUM(CASE WHEN (`ui` = 'Issues' OR `view` = 'Issues' OR `insert` = 'Issues' OR `update` = 'Issues' OR `delete` = 'Issues' OR `cache` = 'Issues' OR `push` = 'Issues' OR `pull` = 'Issues') AND NOT (`ui` = 'Error' OR `view` = 'Error' OR `insert` = 'Error' OR `update` = 'Error' OR `delete` = 'Error' OR `cache` = 'Error' OR `push` = 'Error' OR `pull` = 'Error') THEN 1 ELSE 0 END) AS issues_count,
            SUM(CASE WHEN (`ui` = 'warning' OR `view` = 'warning' OR `insert` = 'warning' OR `update` = 'warning' OR `delete` = 'warning' OR `cache` = 'warning' OR `push` = 'warning' OR `pull` = 'warning') AND NOT (`ui` = 'Error' OR `view` = 'Error' OR `insert` = 'Error' OR `update` = 'Error' OR `delete` = 'Error' OR `cache` = 'Error' OR `push` = 'Error' OR `pull` = 'Error' OR `ui` = 'Issues' OR `view` = 'Issues' OR `insert` = 'Issues' OR `update` = 'Issues' OR `delete` = 'Issues' OR `cache` = 'Issues' OR `push` = 'Issues' OR `pull` = 'Issues') THEN 1 ELSE 0 END) AS warning_count,
            SUM(CASE WHEN (`ui` = 'Not Tested' OR `view` = 'Not Tested' OR `insert` = 'Not Tested' OR `update` = 'Not Tested' OR `delete` = 'Not Tested' OR `cache` = 'Not Tested' OR `push` = 'Not Tested' OR `pull` = 'Not Tested') AND NOT (`ui` = 'Error' OR `view` = 'Error' OR `insert` = 'Error' OR `update` = 'Error' OR `delete` = 'Error' OR `cache` = 'Error' OR `push` = 'Error' OR `pull` = 'Error' OR `ui` = 'Issues' OR `view` = 'Issues' OR `insert` = 'Issues' OR `update` = 'Issues' OR `delete` = 'Issues' OR `cache` = 'Issues' OR `push` = 'Issues' OR `pull` = 'Issues' OR `ui` = 'warning' OR `view` = 'warning' OR `insert` = 'warning' OR `update` = 'warning' OR `delete` = 'warning' OR `cache` = 'warning' OR `push` = 'warning' OR `pull` = 'warning') THEN 1 ELSE 0 END) AS not_tested_count,
            SUM(CASE WHEN `ui` = 'OK' AND `view` = 'OK' AND `insert` = 'OK' AND `update` = 'OK' AND `delete` = 'OK' AND `cache` = 'OK' AND `push` = 'OK' AND `pull` = 'OK' THEN 1 ELSE 0 END) AS ok_count
        FROM `issues_tracker`";
        
        $res = $conn->query($statsSql);
        $row = $res ? $res->fetch_assoc() : [];
        $total = intval($row['total'] ?? 0);
        $error = intval($row['error_count'] ?? 0);
        $issues = intval($row['issues_count'] ?? 0);
        $warning = intval($row['warning_count'] ?? 0);
        $notTested = intval($row['not_tested_count'] ?? 0);
        $ok = intval($row['ok_count'] ?? 0);

        api_response('success', 'Issue statistics calculated successfully.', [
            'total' => $total,
            'notTested' => $notTested,
            'ok' => $ok,
            'error' => $error,
            'warning' => $warning,
            'issues' => $issues
        ]);
    }

    // Action B: Fetch Single Issue by ID or Route
    if ($action === 'get' || isset($_GET['id']) || (isset($_GET['route']) && $action === 'by_route')) {
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

    // Action C: List Issues with Filters
    $route = trim($_GET['route'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $limit = max(1, min(1000, intval($_GET['limit'] ?? 200)));
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
        $whereClauses[] = "(`ui` = ? OR `view` = ? OR `insert` = ? OR `update` = ? OR `delete` = ? OR `cache` = ? OR `push` = ? OR `pull` = ?)";
        for ($i = 0; $i < 8; $i++) {
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
// 2. POST / PUT Request Handler (Save / Create / Update / Batch)
// ----------------------------------------------------
if ($method === 'POST' || $method === 'PUT') {
    // Action A: Delete by POST
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

    // Action B: Batch Sync / Upsert
    if ($action === 'batch' || isset($input['items'])) {
        $items = $input['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            api_response('error', 'No items provided for batch upsert.', null, 400);
        }

        $upsertCount = 0;
        $stmt = $conn->prepare("INSERT INTO `issues_tracker` (`title`, `route`, `ui`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `notes`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                `notes` = VALUES(`notes`),
                `modifieddate` = CURRENT_TIMESTAMP");

        foreach ($items as $item) {
            $t = mb_substr(trim($item['title'] ?? ''), 0, 50);
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
            $notes = mb_substr(trim($item['notes'] ?? ''), 0, 500);

            $stmt->bind_param("sssssssssss", $t, $r, $ui, $view, $ins, $upd, $del, $cache, $push, $pull, $notes);
            if ($stmt->execute()) {
                $upsertCount++;
            }
        }

        api_response('success', "Batch sync completed. Processed $upsertCount items.", [
            'processed' => $upsertCount
        ]);
    }

    // Action C: Single Upsert (Insert or Update)
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $route = mb_substr(trim($input['route'] ?? ''), 0, 200);
    $title = mb_substr(trim($input['title'] ?? ''), 0, 50);

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
    $notes = mb_substr(trim($input['notes'] ?? ''), 0, 500);

    if ($id > 0) {
        // Update existing by ID
        $updateStmt = $conn->prepare("UPDATE `issues_tracker` 
            SET `title` = ?, `route` = ?, `ui` = ?, `view` = ?, `insert` = ?, `update` = ?, `delete` = ?, `cache` = ?, `push` = ?, `pull` = ?, `notes` = ?
            WHERE `id` = ?");
        $updateStmt->bind_param("sssssssssssi", $title, $route, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $notes, $id);
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
                SET `title` = ?, `ui` = ?, `view` = ?, `insert` = ?, `update` = ?, `delete` = ?, `cache` = ?, `push` = ?, `pull` = ?, `notes` = ?
                WHERE `id` = ?");
            $updateStmt->bind_param("ssssssssssi", $title, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $notes, $existingId);
            $updateStmt->execute();

            api_response('success', "Issue record updated for route '$route'.", [
                'id' => $existingId,
                'route' => $route,
                'title' => $title
            ]);
        } else {
            // Insert new record
            $insStmt = $conn->prepare("INSERT INTO `issues_tracker` (`title`, `route`, `ui`, `view`, `insert`, `update`, `delete`, `cache`, `push`, `pull`, `notes`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insStmt->bind_param("sssssssssss", $title, $route, $ui, $view, $insert, $update, $delete, $cache, $push, $pull, $notes);
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
