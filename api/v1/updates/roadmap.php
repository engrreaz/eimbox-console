<?php
/**
 * EIMBox REST API — Upcoming Release Roadmap & Notes Endpoint
 * Route: GET /api/v1/updates/roadmap.php (Fetch upcoming roadmap items)
 * Route: POST /api/v1/updates/roadmap.php (Admin add/update roadmap item)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller (optional for GET, required for POST)
$user = null;
try {
    $user = authenticate_token($conn);
} catch (Exception $e) {
    // Public read allowed for desktop clients checking roadmap
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    if (!$user) {
        api_response('error', 'Authentication required to post roadmap updates.', null, 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'feature'); // feature, module, bugfix, optimization
    $targetVersion = trim($input['target_version'] ?? 'v1.1.0');
    $status = trim($input['status'] ?? 'In Progress'); // Planning, In Progress, Testing, Ready
    $description = trim($input['description'] ?? '');
    $progressPct = intval($input['progress_pct'] ?? 0);

    if (empty($title)) {
        api_response('error', 'Roadmap item title is required.', null, 400);
    }

    // Auto-create roadmap table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS release_roadmap (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(50) DEFAULT 'feature',
        target_version VARCHAR(50) DEFAULT 'v1.1.0',
        status VARCHAR(50) DEFAULT 'In Progress',
        description TEXT,
        progress_pct INT DEFAULT 0,
        created_by VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $conn->prepare("INSERT INTO release_roadmap (title, category, target_version, status, description, progress_pct, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $author = $user['email'] ?? 'admin';
    $stmt->bind_param('sssssis', $title, $category, $targetVersion, $status, $description, $progressPct, $author);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    api_response('success', 'Roadmap item created successfully.', [
        'id' => $insertId,
        'title' => $title,
        'target_version' => $targetVersion,
        'status' => $status
    ]);
}

// GET: Return roadmap items
$roadmapItems = [];

// Try to fetch from database
$hasTable = $conn->query("SHOW TABLES LIKE 'release_roadmap'")->num_rows > 0;
if ($hasTable) {
    $res = $conn->query("SELECT * FROM release_roadmap ORDER BY target_version ASC, id DESC LIMIT 50");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $roadmapItems[] = [
                'id' => intval($row['id']),
                'title' => $row['title'],
                'category' => $row['category'],
                'target_version' => $row['target_version'],
                'status' => $row['status'],
                'description' => $row['description'],
                'progress_pct' => intval($row['progress_pct']),
                'created_at' => $row['created_at']
            ];
        }
    }
}

// Default standard roadmap fallback if empty
if (empty($roadmapItems)) {
    $roadmapItems = [
        [
            'id' => 1,
            'title' => 'Exam Seat Plan Generator & Hall Management',
            'category' => 'module',
            'target_version' => 'v1.1.0',
            'status' => 'In Progress',
            'description' => 'Automated roll distribution, room capacity allocation, and PDF bench card printing.',
            'progress_pct' => 75,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'title' => 'Instant QR Code Mobile App Quick Login',
            'category' => 'feature',
            'target_version' => 'v1.1.0',
            'status' => 'In Progress',
            'description' => 'Fast login using mobile scanner without entering credentials manually.',
            'progress_pct' => 60,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'title' => 'Windows 11 Fluent Glassmorphism Mica Theme & Role-Based Workspaces',
            'category' => 'optimization',
            'target_version' => 'v1.1.0',
            'status' => 'Ready',
            'description' => 'Tailored dashboards for Teachers, Accountants, Head Teachers, and Staff.',
            'progress_pct' => 90,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 4,
            'title' => 'Biometric Fingerprint Machine (ZKTeco) Cloud Polling Sync Engine',
            'category' => 'module',
            'target_version' => 'v1.2.0',
            'status' => 'Planning',
            'description' => 'Direct hardware integration for real-time punch-in auto SMS notification.',
            'progress_pct' => 30,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

api_response('success', 'Roadmap loaded successfully.', [
    'next_release_version' => 'v1.1.0',
    'estimated_release_date' => 'September 2026',
    'total_items' => count($roadmapItems),
    'roadmap' => $roadmapItems
]);
