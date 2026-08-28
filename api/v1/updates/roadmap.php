<?php
/**
 * EIMBox REST API — Upcoming Release Roadmap & Notes Endpoint
 * Route: GET /api/v1/updates/roadmap.php?platform=Desktop|Android|Web
 * Route: POST /api/v1/updates/roadmap.php (Admin add/update roadmap item)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller (optional for GET, required for POST)
$user = null;
try {
    $user = authenticate_token($conn);
} catch (Exception $e) {
    // Public read allowed for clients checking roadmap
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestedPlatform = trim($_GET['platform'] ?? 'Desktop');

if ($method === 'POST') {
    if (!$user) {
        api_response('error', 'Authentication required to post roadmap updates.', null, 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $platform = trim($input['platform'] ?? 'Desktop');
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'feature'); // feature, module, bugfix, optimization, security
    $targetVersion = trim($input['target_version'] ?? 'v1.1.0');
    $status = trim($input['status'] ?? 'In Progress'); // Planned, In Progress, Testing, Ready, Completed
    $description = trim($input['description'] ?? '');
    $progressPct = intval($input['progress_pct'] ?? 0);
    $estimatedDate = trim($input['estimated_date'] ?? 'Upcoming');

    if (empty($title)) {
        api_response('error', 'Roadmap item title is required.', null, 400);
    }

    // Auto-create app_roadmap table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS `app_roadmap` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `platform` ENUM('Android', 'Desktop', 'Web', 'All') NOT NULL DEFAULT 'Desktop',
        `title` VARCHAR(255) NOT NULL,
        `category` ENUM('module', 'feature', 'optimization', 'security', 'bugfix') DEFAULT 'feature',
        `target_version` VARCHAR(30) NOT NULL DEFAULT 'v1.1.0',
        `status` ENUM('Planned', 'In Progress', 'Testing', 'Ready', 'Completed') DEFAULT 'In Progress',
        `description` TEXT,
        `progress_pct` INT DEFAULT 0,
        `estimated_date` VARCHAR(50) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `display_order` INT DEFAULT 0,
        `created_by` VARCHAR(100),
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $conn->prepare("INSERT INTO `app_roadmap` (`platform`, `title`, `category`, `target_version`, `status`, `description`, `progress_pct`, `estimated_date`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $author = $user['email'] ?? 'admin';
    $stmt->bind_param('ssssssiss', $platform, $title, $category, $targetVersion, $status, $description, $progressPct, $estimatedDate, $author);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    api_response('success', 'Roadmap item created successfully.', [
        'id' => $insertId,
        'platform' => $platform,
        'title' => $title,
        'target_version' => $targetVersion,
        'status' => $status
    ]);
}

// GET: Return roadmap items filtered by platform
$roadmapItems = [];

// Try to fetch from database
$hasTable = $conn->query("SHOW TABLES LIKE 'app_roadmap'")->num_rows > 0;
if ($hasTable) {
    $stmt = $conn->prepare("SELECT * FROM `app_roadmap` WHERE `is_active` = 1 AND (`platform` = ? OR `platform` = 'All') ORDER BY `display_order` ASC, `id` DESC LIMIT 50");
    $stmt->bind_param('s', $requestedPlatform);
    $stmt->execute();
    $res = $stmt->get_result();
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
                'created_at' => $row['created_at']
            ];
        }
    }
    $stmt->close();
}

// Fallback if empty
if (empty($roadmapItems)) {
    $roadmapItems = [
        [
            'id' => 1,
            'platform' => 'Desktop',
            'title' => 'Exam Seat Plan Generator & Hall Management',
            'category' => 'module',
            'target_version' => 'v1.1.0',
            'status' => 'In Progress',
            'description' => 'Automated roll distribution, room capacity allocation, and PDF bench card printing.',
            'progress_pct' => 75,
            'estimated_date' => 'September 2026',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'platform' => 'Desktop',
            'title' => 'Instant QR Code Mobile App Quick Login',
            'category' => 'feature',
            'target_version' => 'v1.1.0',
            'status' => 'In Progress',
            'description' => 'Fast login using mobile scanner without entering credentials manually.',
            'progress_pct' => 60,
            'estimated_date' => 'September 2026',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
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

