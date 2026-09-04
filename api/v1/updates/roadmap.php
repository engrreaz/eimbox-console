<?php
/**
 * EIMBox REST API — Upcoming Release Roadmap & Notes Endpoint
 * Routes:
 *   GET /api/v1/updates/roadmap.php?platform=Desktop|Android|Web|All&all=1
 *   POST /api/v1/updates/roadmap.php (Create roadmap item)
 *   PUT /api/v1/updates/roadmap.php (Update roadmap item)
 *   DELETE /api/v1/updates/roadmap.php (Delete roadmap item)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller (optional for GET, required for POST/PUT/DELETE)
$user = null;
try {
    $user = authenticate_token($conn);
} catch (Exception $e) {
    // Public read allowed for clients checking roadmap
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestedPlatform = trim($_GET['platform'] ?? 'All');
$includeAll = isset($_GET['all']) && ($_GET['all'] == '1' || $_GET['all'] == 'true');

// Auto-create app_roadmap table if not exists with all required sync columns
$conn->query("CREATE TABLE IF NOT EXISTS `app_roadmap` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `platform` VARCHAR(20) NOT NULL DEFAULT 'Desktop',
    `title` VARCHAR(255) NOT NULL,
    `category` ENUM('module', 'feature', 'optimization', 'security', 'bugfix') DEFAULT 'feature',
    `target_version` VARCHAR(30) NOT NULL DEFAULT 'v1.1.0',
    `status` ENUM('Planned', 'In Progress', 'Testing', 'Ready', 'Completed') DEFAULT 'In Progress',
    `description` TEXT,
    `progress_pct` INT DEFAULT 0,
    `estimated_date` VARCHAR(50) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `created_by` VARCHAR(100) DEFAULT 'admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `modifieddate` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($method === 'POST') {
    if (!$user) {
        api_response('error', 'Authentication required to post roadmap updates.', null, 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $platform = trim($input['platform'] ?? 'Desktop');
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'feature');
    $targetVersion = trim($input['target_version'] ?? 'v1.1.0');
    $status = trim($input['status'] ?? 'In Progress');
    $description = trim($input['description'] ?? '');
    $progressPct = intval($input['progress_pct'] ?? 0);
    $estimatedDate = trim($input['estimated_date'] ?? 'Upcoming');
    $displayOrder = intval($input['display_order'] ?? 0);
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;

    if (empty($title)) {
        api_response('error', 'Roadmap item title is required.', null, 400);
    }

    $stmt = $conn->prepare("INSERT INTO `app_roadmap` (`platform`, `title`, `category`, `target_version`, `status`, `description`, `progress_pct`, `estimated_date`, `display_order`, `is_active`, `created_by`, `modifieddate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $author = $user['email'] ?? $user['profilename'] ?? 'admin';
    $stmt->bind_param('ssssssisiss', $platform, $title, $category, $targetVersion, $status, $description, $progressPct, $estimatedDate, $displayOrder, $isActive, $author);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    api_response('success', 'Roadmap item created successfully.', [
        'id' => $insertId,
        'platform' => $platform,
        'title' => $title,
        'category' => $category,
        'target_version' => $targetVersion,
        'status' => $status,
        'progress_pct' => $progressPct,
        'estimated_date' => $estimatedDate,
        'is_active' => $isActive
    ]);
}

if ($method === 'PUT') {
    if (!$user) {
        api_response('error', 'Authentication required to update roadmap items.', null, 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        api_response('error', 'Valid roadmap item ID is required for update.', null, 400);
    }

    $platform = trim($input['platform'] ?? 'Desktop');
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'feature');
    $targetVersion = trim($input['target_version'] ?? 'v1.1.0');
    $status = trim($input['status'] ?? 'In Progress');
    $description = trim($input['description'] ?? '');
    $progressPct = intval($input['progress_pct'] ?? 0);
    $estimatedDate = trim($input['estimated_date'] ?? 'Upcoming');
    $displayOrder = intval($input['display_order'] ?? 0);
    $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;

    if (empty($title)) {
        api_response('error', 'Roadmap item title is required.', null, 400);
    }

    $stmt = $conn->prepare("UPDATE `app_roadmap` SET `platform` = ?, `title` = ?, `category` = ?, `target_version` = ?, `status` = ?, `description` = ?, `progress_pct` = ?, `estimated_date` = ?, `display_order` = ?, `is_active` = ?, `modifieddate` = NOW() WHERE `id` = ?");
    $stmt->bind_param('ssssssisiis', $platform, $title, $category, $targetVersion, $status, $description, $progressPct, $estimatedDate, $displayOrder, $isActive, $id);
    $stmt->execute();
    $stmt->close();

    api_response('success', 'Roadmap item updated successfully.', [
        'id' => $id,
        'platform' => $platform,
        'title' => $title,
        'category' => $category,
        'target_version' => $targetVersion,
        'status' => $status,
        'progress_pct' => $progressPct,
        'estimated_date' => $estimatedDate,
        'is_active' => $isActive
    ]);
}

if ($method === 'DELETE') {
    if (!$user) {
        api_response('error', 'Authentication required to delete roadmap items.', null, 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        api_response('error', 'Valid roadmap item ID is required for deletion.', null, 400);
    }

    $stmt = $conn->prepare("DELETE FROM `app_roadmap` WHERE `id` = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    api_response('success', "Roadmap item #{$id} deleted successfully.", ['id' => $id]);
}

// GET: Return roadmap items filtered by platform
$roadmapItems = [];

$hasTable = $conn->query("SHOW TABLES LIKE 'app_roadmap'")->num_rows > 0;
if ($hasTable) {
    if ($includeAll) {
        $sql = "SELECT * FROM `app_roadmap` ORDER BY `display_order` ASC, `id` DESC LIMIT 100";
        $res = $conn->query($sql);
    } else {
        if ($requestedPlatform === 'All' || empty($requestedPlatform)) {
            $sql = "SELECT * FROM `app_roadmap` WHERE `is_active` = 1 ORDER BY `display_order` ASC, `id` DESC LIMIT 100";
            $res = $conn->query($sql);
        } else {
            $stmt = $conn->prepare("SELECT * FROM `app_roadmap` WHERE `is_active` = 1 AND (`platform` = ? OR `platform` = 'All') ORDER BY `display_order` ASC, `id` DESC LIMIT 100");
            $stmt->bind_param('s', $requestedPlatform);
            $stmt->execute();
            $res = $stmt->get_result();
        }
    }

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $roadmapItems[] = [
                'id' => intval($row['id']),
                'platform' => $row['platform'],
                'title' => $row['title'],
                'category' => $row['category'],
                'target_version' => $row['target_version'],
                'status' => $row['status'],
                'description' => $row['description'],
                'progress_pct' => intval($row['progress_pct']),
                'estimated_date' => $row['estimated_date'] ?? 'Upcoming',
                'is_active' => intval($row['is_active'] ?? 1),
                'display_order' => intval($row['display_order'] ?? 0),
                'created_at' => $row['created_at']
            ];
        }
    }
}

// Seed initial roadmap if empty
if (empty($roadmapItems) && $hasTable) {
    $initials = [
        ['Desktop', 'Exam Seat Plan Generator & Hall Management', 'module', 'v1.1.0', 'In Progress', 'Automated roll distribution, room capacity allocation, and PDF bench card printing.', 75, 'September 2026', 1],
        ['Desktop', 'Instant QR Code Mobile App Quick Login', 'feature', 'v1.1.0', 'In Progress', 'Fast login using mobile scanner without entering credentials manually.', 60, 'September 2026', 2],
        ['Desktop', 'Windows 11 Fluent Glassmorphism Mica Theme & Role-Based Workspaces', 'optimization', 'v1.1.0', 'Ready', 'Tailored dashboards for Teachers, Accountants, Head Teachers, and Staff.', 90, 'September 2026', 3],
        ['Web', 'Central Multi-Tenant Subscription & Payment Gateway Gateway', 'module', 'v1.2.0', 'Planned', 'Automated bKash/Nagad/SSLCommerz subscription renewal gateway.', 30, 'October 2026', 4]
    ];
    foreach ($initials as $init) {
        $st = $conn->prepare("INSERT INTO `app_roadmap` (`platform`, `title`, `category`, `target_version`, `status`, `description`, `progress_pct`, `estimated_date`, `display_order`, `is_active`, `created_by`, `modifieddate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'system', NOW())");
        $st->bind_param('ssssssisi', $init[0], $init[1], $init[2], $init[3], $init[4], $init[5], $init[6], $init[7], $init[8]);
        $st->execute();
        $st->close();
    }
    // Re-fetch after seed
    $res = $conn->query("SELECT * FROM `app_roadmap` ORDER BY `display_order` ASC, `id` DESC LIMIT 100");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $roadmapItems[] = [
                'id' => intval($row['id']),
                'platform' => $row['platform'],
                'title' => $row['title'],
                'category' => $row['category'],
                'target_version' => $row['target_version'],
                'status' => $row['status'],
                'description' => $row['description'],
                'progress_pct' => intval($row['progress_pct']),
                'estimated_date' => $row['estimated_date'] ?? 'Upcoming',
                'is_active' => intval($row['is_active'] ?? 1),
                'display_order' => intval($row['display_order'] ?? 0),
                'created_at' => $row['created_at']
            ];
        }
    }
}

$nextVer = $roadmapItems[0]['target_version'] ?? 'v1.1.0';
$estDate = $roadmapItems[0]['estimated_date'] ?? 'September 2026';

api_response('success', 'Roadmap loaded successfully.', [
    'platform' => $requestedPlatform,
    'next_release_version' => $nextVer,
    'estimated_release_date' => $estDate,
    'total_items' => count($roadmapItems),
    'roadmap' => $roadmapItems
]);
