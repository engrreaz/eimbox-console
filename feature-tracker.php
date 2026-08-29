<?php
/**
 * EIMBox Multi-Platform Feature & Issue Tracker
 * Advanced Multi-Task & Multi-Issue Architecture
 * Pure Bootstrap 5 Implementation
 */

require_once 'core/init.php';

// ==============================================================
// 1. DATABASE SCHEMA SETUP & AUTO-MIGRATION
// ==============================================================
function ensure_tracker_schema($conn) {
    try {
        $conn->query("
            CREATE TABLE IF NOT EXISTS `eimbox_features_master` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `module` VARCHAR(100) NOT NULL,
              `feature_name` VARCHAR(150) NOT NULL,
              `description` TEXT DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `eimbox_platform_tracker` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `feature_id` INT(11) NOT NULL,
              `platform` ENUM('dashboard','console','android_lite','premium','desktop') NOT NULL,
              `task_title` VARCHAR(255) NOT NULL DEFAULT 'Main Implementation',
              `script_path` VARCHAR(255) DEFAULT '',
              `status` VARCHAR(50) NOT NULL DEFAULT 'Planned',
              `priority` VARCHAR(20) NOT NULL DEFAULT 'Medium',
              `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
              `issue_notes` TEXT DEFAULT NULL,
              `dev_response` TEXT DEFAULT NULL,
              `estimated_deadline` DATE DEFAULT NULL,
              `assigned_to` VARCHAR(100) DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_feature_platform` (`feature_id`,`platform`),
              KEY `idx_platform` (`platform`),
              KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Migration 1: Add task_title if missing
        $chk_col = $conn->query("SHOW COLUMNS FROM `eimbox_platform_tracker` LIKE 'task_title'");
        if ($chk_col && $chk_col->num_rows == 0) {
            $conn->query("ALTER TABLE `eimbox_platform_tracker` ADD COLUMN `task_title` VARCHAR(255) NOT NULL DEFAULT 'Main Implementation' AFTER `platform`");
        }

        // Migration 2: Drop UNIQUE index safely without violating foreign key constraints
        $chk_idx = $conn->query("SHOW INDEX FROM `eimbox_platform_tracker` WHERE Key_name = 'idx_feature_platform' AND Non_unique = 0");
        if ($chk_idx && $chk_idx->num_rows > 0) {
            // Add a new non-unique index first to satisfy any foreign key dependencies
            $conn->query("ALTER TABLE `eimbox_platform_tracker` ADD INDEX `idx_feat_plat_temp` (`feature_id`, `platform`)");
            // Drop the old unique index
            $conn->query("ALTER TABLE `eimbox_platform_tracker` DROP INDEX `idx_feature_platform`");
            // Rename the new index back to standard name
            $conn->query("ALTER TABLE `eimbox_platform_tracker` ADD INDEX `idx_feature_platform` (`feature_id`, `platform`)");
            $conn->query("ALTER TABLE `eimbox_platform_tracker` DROP INDEX `idx_feat_plat_temp`");
        }
    } catch (Throwable $e) {
        // Log schema setup notice if any without crashing the application
        error_log("Schema ensure notice: " . $e->getMessage());
    }
}

ensure_tracker_schema($conn);

// ==============================================================
// 2. BACKEND CRUD HANDLERS (AJAX POST)
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'];

    // 2.1 Add Feature
    if ($action === 'add_feature') {
        try {
            $module = trim($_POST['module'] ?? '');
            $feature_name = trim($_POST['feature_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $selected_platforms = $_POST['platforms'] ?? [];

            if (empty($module) || empty($feature_name)) {
                echo json_encode(['status' => 'error', 'message' => 'Module and Feature Name are required fields!']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $module, $feature_name, $description);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();

                // Only insert initial task for selected applicable platforms
                if (is_array($selected_platforms) && count($selected_platforms) > 0) {
                    $p_stmt = $conn->prepare("INSERT INTO eimbox_platform_tracker (feature_id, platform, task_title, status, progress_percent, priority, script_path) VALUES (?, ?, 'Main Implementation', 'Planned', 0, 'Medium', '')");
                    foreach ($selected_platforms as $pk) {
                        if (in_array($pk, ['dashboard', 'console', 'android_lite', 'premium', 'desktop'])) {
                            $p_stmt->bind_param("is", $new_id, $pk);
                            $p_stmt->execute();
                        }
                    }
                    $p_stmt->close();
                }

                echo json_encode(['status' => 'success', 'message' => 'New feature created successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create feature: ' . $conn->error]);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.2 Edit Master Feature
    if ($action === 'edit_master_feature') {
        try {
            $id = intval($_POST['id'] ?? 0);
            $module = trim($_POST['module'] ?? '');
            $feature_name = trim($_POST['feature_name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($id <= 0 || empty($module) || empty($feature_name)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid feature ID or required fields are missing!']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE eimbox_features_master SET module=?, feature_name=?, description=? WHERE id=?");
            $stmt->bind_param("sssi", $module, $feature_name, $description, $id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Feature details updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update feature: ' . $conn->error]);
            }
            $stmt->close();
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.3 Delete Feature
    if ($action === 'delete_feature') {
        try {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $conn->query("DELETE FROM eimbox_platform_tracker WHERE feature_id = $id");
                $conn->query("DELETE FROM eimbox_features_master WHERE id = $id");
                echo json_encode(['status' => 'success', 'message' => 'Feature deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid feature ID!']);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.4 Get All Tasks for a Platform
    if ($action === 'get_platform_tasks') {
        try {
            $feature_id = intval($_POST['feature_id'] ?? 0);
            $platform = trim($_POST['platform'] ?? '');

            // Get Feature master info
            $f_res = $conn->query("SELECT feature_name, module FROM eimbox_features_master WHERE id = $feature_id LIMIT 1");
            $f_data = $f_res ? $f_res->fetch_assoc() : ['feature_name' => 'Feature #' . $feature_id, 'module' => ''];

            $stmt = $conn->prepare("SELECT * FROM eimbox_platform_tracker WHERE feature_id = ? AND platform = ? ORDER BY id ASC");
            $stmt->bind_param("is", $feature_id, $platform);
            $stmt->execute();
            $res = $stmt->get_result();
            $tasks = [];
            while ($row = $res->fetch_assoc()) {
                $tasks[] = $row;
            }
            $stmt->close();

            echo json_encode([
                'status' => 'success',
                'feature' => $f_data,
                'tasks' => $tasks
            ]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.5 Save Platform Task (Create or Update)
    if ($action === 'save_platform_task') {
        try {
            $task_id = intval($_POST['task_id'] ?? 0);
            $feature_id = intval($_POST['feature_id'] ?? 0);
            $platform = trim($_POST['platform'] ?? '');
            $task_title = trim($_POST['task_title'] ?? '');
            if (empty($task_title)) $task_title = 'Main Implementation';

            $script_path = trim($_POST['script_path'] ?? '');
            $status = trim($_POST['status'] ?? 'Planned');
            $priority = trim($_POST['priority'] ?? 'Medium');
            $progress = max(0, min(100, intval($_POST['progress_percent'] ?? 0)));
            $issue_notes = trim($_POST['issue_notes'] ?? '');
            $dev_response = trim($_POST['dev_response'] ?? '');
            $assigned_to = trim($_POST['assigned_to'] ?? '');
            $raw_deadline = trim($_POST['estimated_deadline'] ?? '');
            $deadline = !empty($raw_deadline) ? $raw_deadline : null;

            if ($status === 'Completed' && $progress < 100) {
                $progress = 100;
            }

            if ($task_id > 0) {
                // Update Existing Task
                $stmt = $conn->prepare("
                    UPDATE eimbox_platform_tracker
                    SET task_title = ?, script_path = ?, status = ?, priority = ?, progress_percent = ?,
                        issue_notes = ?, dev_response = ?, assigned_to = ?, estimated_deadline = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssissssi", $task_title, $script_path, $status, $priority, $progress, $issue_notes, $dev_response, $assigned_to, $deadline, $task_id);
                $exec = $stmt->execute();
                $stmt->close();
                echo json_encode(['status' => 'success', 'message' => 'Task updated successfully!']);
            } else {
                // Insert New Task
                if ($feature_id <= 0 || empty($platform)) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid feature ID or platform.']);
                    exit;
                }

                $stmt = $conn->prepare("
                    INSERT INTO eimbox_platform_tracker 
                    (feature_id, platform, task_title, script_path, status, priority, progress_percent, issue_notes, dev_response, assigned_to, estimated_deadline, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->bind_param("isssssissss", $feature_id, $platform, $task_title, $script_path, $status, $priority, $progress, $issue_notes, $dev_response, $assigned_to, $deadline);
                $exec = $stmt->execute();
                $stmt->close();
                echo json_encode(['status' => 'success', 'message' => 'New task added successfully!']);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.6 Delete Individual Platform Task
    if ($action === 'delete_platform_task') {
        try {
            $task_id = intval($_POST['task_id'] ?? 0);
            if ($task_id > 0) {
                $conn->query("DELETE FROM eimbox_platform_tracker WHERE id = $task_id");
                echo json_encode(['status' => 'success', 'message' => 'Task deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid task ID!']);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 2.7 Seed Realistic Default Data
    if ($action === 'seed_default_data') {
        try {
            $sample_features = [
                [
                    'module' => 'Student',
                    'name' => 'Daily Student Attendance',
                    'desc' => 'Multi-channel student attendance taking with biometric & manual entry',
                    'platforms' => [
                        'dashboard' => [
                            ['title' => 'Web Class-wise Attendance Sheet', 'path' => 'attendance-daily.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Critical', 'date' => '2026-08-15'],
                            ['title' => 'Monthly Summary & SMS Queue', 'path' => 'attendance-report.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'High', 'date' => '2026-08-20']
                        ],
                        'console' => [
                            ['title' => 'Admin Attendance Overrides & Audit Log', 'path' => 'console/audit-attendance.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Medium', 'date' => '2026-08-25']
                        ],
                        'android_lite' => [
                            ['title' => 'Teacher Fast Swipe Attendance UI', 'path' => 'lib/screens/teacher_attnd.dart', 'status' => 'Testing', 'pct' => 85, 'prio' => 'Critical', 'date' => '2026-09-05', 'issue' => 'Background sync slow on 2G networks', 'dev' => 'Optimized payload size with gzip compression'],
                            ['title' => 'Guardian SMS Alert Trigger', 'path' => 'lib/services/sms_service.dart', 'status' => 'In Progress', 'pct' => 60, 'prio' => 'High', 'date' => '2026-09-10']
                        ],
                        'premium' => [
                            ['title' => 'Offline Biometric Punch Sync Engine', 'path' => 'core/offline_sync.php', 'status' => 'In Progress', 'pct' => 45, 'prio' => 'High', 'date' => '2026-09-18', 'issue' => 'Conflict resolution on duplicate RFID punches']
                        ],
                        'desktop' => [
                            ['title' => 'ZKTeco / Realtime USB Device Listener', 'path' => 'desktop/biometric_daemon.exe', 'status' => 'Issue', 'pct' => 30, 'prio' => 'Critical', 'date' => '2026-09-25', 'issue' => 'COM port disconnection on Windows 11 sleep mode', 'dev' => 'Investigating auto-reconnect watchdog daemon']
                        ]
                    ]
                ],
                [
                    'module' => 'Exam',
                    'name' => 'OMR Sheet Scanner & Evaluator',
                    'desc' => 'Automated MCQ paper grading via high-resolution camera & offline scanner',
                    'platforms' => [
                        'dashboard' => [
                            ['title' => 'Exam Result Master & Grade Matrix', 'path' => 'exam-matrix.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'High', 'date' => '2026-08-10']
                        ],
                        'android_lite' => [
                            ['title' => 'Mobile Camera OMR Bubble Detection', 'path' => 'lib/scanner/omr_vision.dart', 'status' => 'In Progress', 'pct' => 70, 'prio' => 'Critical', 'date' => '2026-09-12', 'issue' => 'Skewed perspective correction needed for angled capture']
                        ],
                        'premium' => [
                            ['title' => 'High-Speed Batch Scanner Driver', 'path' => 'premium/omr_driver.dll', 'status' => 'Testing', 'pct' => 90, 'prio' => 'High', 'date' => '2026-09-02']
                        ],
                        'desktop' => [
                            ['title' => 'Desktop Batch Processor UI', 'path' => 'desktop/omr_batch.py', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Critical', 'date' => '2026-08-28']
                        ]
                    ]
                ],
                [
                    'module' => 'Accounts',
                    'name' => 'Online Fee Payment & Instant SMS',
                    'desc' => 'bKash, Nagad, Rocket, card gateway payment collection with auto receipt',
                    'platforms' => [
                        'dashboard' => [
                            ['title' => 'SSLCommerz & bKash Direct Checkout', 'path' => 'payment-gateway.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Critical', 'date' => '2026-08-01'],
                            ['title' => 'Daily Accounts Reconciliation', 'path' => 'reconciliation.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'High', 'date' => '2026-08-12']
                        ],
                        'console' => [
                            ['title' => 'Payment Gateway Fee Config & API Keys', 'path' => 'console/pg-settings.php', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Critical', 'date' => '2026-08-05']
                        ],
                        'android_lite' => [
                            ['title' => 'Parent App Fee Due & In-App Pay', 'path' => 'lib/screens/fee_pay.dart', 'status' => 'Completed', 'pct' => 100, 'prio' => 'High', 'date' => '2026-08-22']
                        ]
                    ]
                ],
                [
                    'module' => 'Offline Core',
                    'name' => 'Offline SQLite & Local Sync Engine',
                    'desc' => 'Zero-internet offline operating mode with encrypted dual-sync',
                    'platforms' => [
                        'android_lite' => [
                            ['title' => 'Local SQLite Cache for Offline Attendance', 'path' => 'lib/database/sqlite_helper.dart', 'status' => 'Completed', 'pct' => 100, 'prio' => 'Critical', 'date' => '2026-08-18']
                        ],
                        'premium' => [
                            ['title' => 'Embedded Apache/MySQL Offline Server Bundle', 'path' => 'premium/installer.nsi', 'status' => 'Testing', 'pct' => 80, 'prio' => 'High', 'date' => '2026-09-08']
                        ],
                        'desktop' => [
                            ['title' => 'Auto Differential Cloud Sync Daemon', 'path' => 'desktop/sync_daemon.py', 'status' => 'In Progress', 'pct' => 50, 'prio' => 'Critical', 'date' => '2026-09-20', 'issue' => 'Delta sync conflict when two devices edit same student record simultaneously']
                        ]
                    ]
                ]
            ];

            foreach ($sample_features as $item) {
                $m = $item['module']; $f = $item['name']; $d = $item['desc'];
                $chk = $conn->query("SELECT id FROM eimbox_features_master WHERE feature_name = '" . $conn->real_escape_string($f) . "'");
                if ($chk && $chk->num_rows == 0) {
                    $conn->query("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES ('$m', '$f', '$d')");
                    $nid = $conn->insert_id;

                    foreach ($item['platforms'] as $pk => $tasks) {
                        foreach ($tasks as $t) {
                            $t_title = $conn->real_escape_string($t['title']);
                            $t_path = $conn->real_escape_string($t['path'] ?? '');
                            $t_status = $conn->real_escape_string($t['status'] ?? 'Planned');
                            $t_pct = intval($t['pct'] ?? 0);
                            $t_prio = $conn->real_escape_string($t['prio'] ?? 'Medium');
                            $t_date = !empty($t['date']) ? "'" . $conn->real_escape_string($t['date']) . "'" : "NULL";
                            $t_issue = !empty($t['issue']) ? "'" . $conn->real_escape_string($t['issue']) . "'" : "NULL";
                            $t_dev = !empty($t['dev']) ? "'" . $conn->real_escape_string($t['dev']) . "'" : "NULL";

                            $conn->query("
                                INSERT INTO eimbox_platform_tracker 
                                (feature_id, platform, task_title, script_path, status, progress_percent, priority, estimated_deadline, issue_notes, dev_response, created_at, updated_at)
                                VALUES ($nid, '$pk', '$t_title', '$t_path', '$t_status', $t_pct, '$t_prio', $t_date, $t_issue, $t_dev, NOW(), NOW())
                            ");
                        }
                    }
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Demo data loaded successfully!']);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action request!']);
    exit;
}

// ==============================================================
// 3. FRONTEND DATA LOADING & AGGREGATIONS
// ==============================================================

$platforms = [
    'dashboard'    => ['title' => 'Dashboard',    'sub' => 'Web App',       'color' => 'primary', 'icon' => 'bi bi-laptop'],
    'console'      => ['title' => 'Console',      'sub' => 'Superadmin',    'color' => 'dark',    'icon' => 'bi bi-terminal'],
    'android_lite' => ['title' => 'Android Lite', 'sub' => 'Mobile App',    'color' => 'success', 'icon' => 'bi bi-phone'],
    'premium'      => ['title' => 'Offline Prem', 'sub' => 'Offline Bundle','color' => 'warning', 'icon' => 'bi bi-hdd-network'],
    'desktop'      => ['title' => 'Desktop',      'sub' => 'Windows Exec',  'color' => 'info',    'icon' => 'bi bi-display']
];

$plat_color_hexes = [
    'primary' => '#696cff',
    'dark'    => '#233446',
    'success' => '#71dd37',
    'warning' => '#ffab00',
    'info'    => '#03c3ec',
    'danger'  => '#ff3e1d',
    'secondary'=> '#8592a3'
];

$status_badges = [
    'Completed'   => 'bg-success',
    'In Progress' => 'bg-primary',
    'Testing'     => 'bg-info',
    'Planned'     => 'bg-secondary',
    'Issue'       => 'bg-danger',
    'On Hold'     => 'bg-warning'
];

// Helper: Circular Progress SVG Generator
function render_circular_progress($pct, $color = '#696cff', $size = 40, $stroke = 3.5, $font_size = '0.7rem') {
    $pct = max(0, min(100, intval($pct)));
    $radius = ($size - $stroke) / 2;
    $circ = 2 * M_PI * $radius;
    $offset = $circ - ($pct / 100) * $circ;
    
    return "
    <div class='circular-progress-box position-relative d-inline-flex align-items-center justify-content-center' style='width: {$size}px; height: {$size}px;'>
        <svg width='{$size}' height='{$size}' viewBox='0 0 {$size} {$size}' class='d-block' style='transform: rotate(-90deg);'>
            <circle cx='" . ($size/2) . "' cy='" . ($size/2) . "' r='{$radius}' fill='none' stroke='#e7e7e7' stroke-width='{$stroke}' />
            <circle cx='" . ($size/2) . "' cy='" . ($size/2) . "' r='{$radius}' fill='none' stroke='{$color}' stroke-width='{$stroke}' 
                    stroke-dasharray='{$circ}' stroke-dashoffset='{$offset}' stroke-linecap='round' />
        </svg>
        <span class='position-absolute fw-bold' style='font-size: {$font_size}; color: {$color}; line-height: 1; user-select: none;'>
            {$pct}%
        </span>
    </div>";
}

function get_progress_color($pct, $status = '') {
    if ($status === 'Issue') return '#ff3e1d';
    if ($status === 'Completed' || $pct >= 100) return '#71dd37';
    if ($pct >= 70) return '#03c3ec';
    if ($pct >= 30) return '#696cff';
    if ($pct > 0) return '#ffab00';
    return '#8592a3';
}

// Fetch Master Modules for Filter Dropdown
$modules_list = [];
$mod_query = $conn->query("SELECT DISTINCT module_name, core FROM modulelist ORDER BY slno ASC, module_name ASC");
if ($mod_query && $mod_query->num_rows > 0) {
    while ($m_row = $mod_query->fetch_assoc()) {
        $modules_list[] = $m_row;
    }
}
if (empty($modules_list)) {
    $modules_list = [
        ['module_name' => 'Student', 'core' => 1],
        ['module_name' => 'Accounts', 'core' => 1],
        ['module_name' => 'Exam', 'core' => 1],
        ['module_name' => 'HR/Payroll', 'core' => 1],
        ['module_name' => 'Academic', 'core' => 1],
        ['module_name' => 'Library', 'core' => 0],
        ['module_name' => 'Offline Core', 'core' => 0],
        ['module_name' => 'Settings', 'core' => 1]
    ];
}

// Filters
$f_module = $_GET['module'] ?? 'all';
$f_platform = $_GET['platform'] ?? 'all';
$f_status = $_GET['status'] ?? 'all';
$f_search = trim($_GET['search'] ?? '');
$f_issues = isset($_GET['issues_only']) && $_GET['issues_only'] == '1';

// Build SQL Query for Master Features
$where_clauses = ["1=1"];
if ($f_module !== 'all' && !empty($f_module)) {
    $where_clauses[] = "m.module = '" . $conn->real_escape_string($f_module) . "'";
}
if (!empty($f_search)) {
    $s_term = $conn->real_escape_string($f_search);
    $where_clauses[] = "(m.feature_name LIKE '%$s_term%' OR m.module LIKE '%$s_term%' OR m.description LIKE '%$s_term%' OR EXISTS (
        SELECT 1 FROM eimbox_platform_tracker pt WHERE pt.feature_id = m.id AND (pt.script_path LIKE '%$s_term%' OR pt.task_title LIKE '%$s_term%' OR pt.issue_notes LIKE '%$s_term%')
    ))";
}
if ($f_issues) {
    $where_clauses[] = "EXISTS (
        SELECT 1 FROM eimbox_platform_tracker pt WHERE pt.feature_id = m.id AND (pt.status = 'Issue' OR (pt.issue_notes IS NOT NULL AND TRIM(pt.issue_notes) != ''))
    )";
}
if ($f_status !== 'all' && !empty($f_status)) {
    $st_term = $conn->real_escape_string($f_status);
    $where_clauses[] = "EXISTS (
        SELECT 1 FROM eimbox_platform_tracker pt WHERE pt.feature_id = m.id AND pt.status = '$st_term'
    )";
}
if ($f_platform !== 'all' && !empty($f_platform)) {
    $pl_term = $conn->real_escape_string($f_platform);
    $where_clauses[] = "EXISTS (
        SELECT 1 FROM eimbox_platform_tracker pt WHERE pt.feature_id = m.id AND pt.platform = '$pl_term'
    )";
}

$where_sql = implode(' AND ', $where_clauses);
$sql = "
    SELECT m.* 
    FROM eimbox_features_master m
    WHERE $where_sql
    ORDER BY m.id DESC
";

$features_res = $conn->query($sql);
$features = [];
$feature_ids = [];

if ($features_res && $features_res->num_rows > 0) {
    while ($row = $features_res->fetch_assoc()) {
        $features[$row['id']] = [
            'master' => $row,
            'platforms' => []
        ];
        $feature_ids[] = $row['id'];
    }
}

// Fetch Tasks for these Features
if (!empty($feature_ids)) {
    $f_ids_str = implode(',', $feature_ids);
    $plat_res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id IN ($f_ids_str) ORDER BY id ASC");
    if ($plat_res) {
        while ($prow = $plat_res->fetch_assoc()) {
            $features[$prow['feature_id']]['platforms'][$prow['platform']][] = $prow;
        }
    }
}

// Global Summary Statistics
$total_features_count = 0;
$c_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_features_master");
if ($c_res) $total_features_count = intval($c_res->fetch_assoc()['c'] ?? 0);

$total_issues_count = 0;
$i_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_platform_tracker WHERE status = 'Issue' OR (issue_notes IS NOT NULL AND TRIM(issue_notes) != '')");
if ($i_res) $total_issues_count = intval($i_res->fetch_assoc()['c'] ?? 0);

$plat_stats = [];
$ps_res = $conn->query("
    SELECT platform, 
           COUNT(*) as total_tasks,
           SUM(CASE WHEN status = 'Completed' OR progress_percent = 100 THEN 1 ELSE 0 END) as completed_tasks,
           AVG(progress_percent) as avg_progress
    FROM eimbox_platform_tracker
    GROUP BY platform
");
if ($ps_res) {
    while ($sp = $ps_res->fetch_assoc()) {
        $plat_stats[$sp['platform']] = [
            'total' => intval($sp['total_tasks']),
            'completed' => intval($sp['completed_tasks']),
            'percent' => round(floatval($sp['avg_progress'] ?? 0))
        ];
    }
}

require_once 'header.php';
?>

<style>
    .circular-progress-box {
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    td:hover .circular-progress-box {
        transform: scale(1.08);
    }
    .platform-cell-box {
        min-height: 72px;
        transition: background-color 0.15s ease;
        border-radius: 6px;
    }
    .platform-cell-box:hover {
        background-color: rgba(105, 108, 255, 0.06);
    }
    .na-badge {
        font-size: 0.72rem;
        background-color: #f1f2f4;
        color: #a1acb8;
        border: 1px dashed #d1d5db;
    }
    .task-card {
        transition: all 0.2s ease;
        border-left: 4px solid #696cff;
    }
    .task-card.task-issue {
        border-left-color: #ff3e1d;
        background-color: #fffaf9;
    }
    .task-card.task-completed {
        border-left-color: #71dd37;
    }
    .task-card.task-testing {
        border-left-color: #03c3ec;
    }
</style>

<div class="container-fluid container-p-y">

    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>EIMBox Multi-Platform Feature Tracker</h4>
            <p class="text-muted small mb-0">Multi-issue & multi-task matrix with dynamic platform applicability across Dashboard, Console, Android Lite, Offline Premium & Desktop</p>
        </div>
        <div class="d-flex gap-2">
            <a href="feature-tracker.php?issues_only=<?= $f_issues ? '0' : '1' ?>" class="btn btn-sm <?= $f_issues ? 'btn-danger' : 'btn-outline-danger' ?>">
                <i class="bi bi-bug-fill me-1"></i> Issues Only (<?= $total_issues_count ?>)
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Feature
            </button>
        </div>
    </div>

    <!-- Platform KPI Cards -->
    <div class="row g-2 mb-3">
        <?php foreach ($platforms as $pk => $pinfo): 
            $st_total = $plat_stats[$pk]['total'] ?? 0;
            $st_comp = $plat_stats[$pk]['completed'] ?? 0;
            $st_pct = $plat_stats[$pk]['percent'] ?? 0;
            $kpi_color = $plat_color_hexes[$pinfo['color']] ?? '#696cff';
        ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card text-center h-100 shadow-sm border-0">
                    <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                        <div class="text-<?= $pinfo['color'] ?> fw-semibold small mb-1">
                            <i class="<?= $pinfo['icon'] ?> me-1"></i> <?= $pinfo['title'] ?>
                        </div>
                        <div class="my-1">
                            <?= render_circular_progress($st_pct, $kpi_color, 46, 3.4, '0.72rem') ?>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;"><?= $st_comp ?>/<?= $st_total ?> Tasks Done</small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 col-md-4 col-lg-2">
            <div class="card text-center h-100 bg-primary text-white shadow-sm border-0">
                <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                    <div class="small fw-semibold mb-1"><i class="bi bi-list-task me-1"></i> All Features</div>
                    <div class="fs-4 fw-bold my-1"><?= $total_features_count ?></div>
                    <a href="feature-tracker.php" class="text-white-50 text-decoration-none d-block small" style="font-size: 0.7rem;">Reset All Filters</a>
                </div>
            </div>
        </div>
    <!-- Filter & Live Search Toolbar -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body p-3">
            <form method="get" action="feature-tracker.php" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Search feature, module or script...">
                    </div>
                </div>

                <!-- Module Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="module" onchange="this.form.submit()">
                        <option value="all">All Modules</option>
                        <?php foreach ($modules_list as $mod): ?>
                            <option value="<?= htmlspecialchars($mod['module_name']) ?>" <?= $f_module === $mod['module_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Platform Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="platform" onchange="this.form.submit()">
                        <option value="all">All Platforms</option>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <option value="<?= $pk ?>" <?= $f_platform === $pk ? 'selected' : '' ?>><?= $pinfo['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="all">All Statuses</option>
                        <?php foreach ($status_badges as $st_key => $st_badge): ?>
                            <option value="<?= $st_key ?>" <?= $f_status === $st_key ? 'selected' : '' ?>><?= $st_key ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action buttons -->
                <div class="col-lg-3 col-md-9 col-6 text-end">
                    <button type="submit" class="btn btn-sm btn-primary me-1"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    <a href="feature-tracker.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Features Matrix Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 45px;">#</th>
                        <th style="min-width: 220px;">Module & Feature Name</th>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <th class="text-center" style="min-width: 150px;">
                                <div class="text-<?= $pinfo['color'] ?> fw-bold">
                                    <i class="<?= $pinfo['icon'] ?> me-1"></i> <?= $pinfo['title'] ?>
                                </div>
                                <span class="text-muted font-monospace" style="font-size: 0.65rem;"><?= $pinfo['sub'] ?></span>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-center" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($features)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No records found!</h5>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="seedDemoData()">Load Demo Data</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($features as $f_item): 
                            $m = $f_item['master'];
                            $p_data = $f_item['platforms'];
                            $m_json = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $m['id'] ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border mb-1 font-monospace">
                                        <i class="bi bi-folder2-open me-1"></i><?= htmlspecialchars($m['module']) ?>
                                    </span>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($m['feature_name']) ?></div>
                                    <?php if (!empty($m['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($m['description']) ?>">
                                            <?= htmlspecialchars($m['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <?php foreach ($platforms as $pk => $pinfo): 
                                    $tasks = $p_data[$pk] ?? [];
                                    $task_count = count($tasks);
                                    
                                    if ($task_count === 0): ?>
                                        <td class="text-center p-2" style="cursor: pointer;" onclick='openPlatformWorkspace(<?= $m['id'] ?>, "<?= $pk ?>", "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>")' title="Click to configure or add tasks">
                                            <div class="platform-cell-box d-flex flex-column align-items-center justify-content-center p-1">
                                                <span class="badge na-badge px-2 py-1 mb-1">
                                                    <i class="bi bi-slash-circle me-1"></i>N/A
                                                </span>
                                                <span class="text-muted" style="font-size: 0.65rem;">Not Applicable</span>
                                            </div>
                                        </td>
                                    <?php else: 
                                        $completed_tasks = 0;
                                        $total_progress_sum = 0;
                                        $issue_count = 0;
                                        $latest_deadline = null;

                                        foreach ($tasks as $t) {
                                            $t_pct = intval($t['progress_percent'] ?? 0);
                                            $total_progress_sum += $t_pct;
                                            if ($t['status'] === 'Completed' || $t_pct >= 100) {
                                                $completed_tasks++;
                                            }
                                            if ($t['status'] === 'Issue' || (!empty($t['issue_notes']) && trim($t['issue_notes']) !== '')) {
                                                $issue_count++;
                                            }
                                            if (!empty($t['estimated_deadline'])) {
                                                if ($latest_deadline === null || $t['estimated_deadline'] > $latest_deadline) {
                                                    $latest_deadline = $t['estimated_deadline'];
                                                }
                                            }
                                        }

                                        $avg_progress = round($total_progress_sum / $task_count);
                                        if ($issue_count > 0) $composite_status = 'Issue';
                                        elseif ($completed_tasks === $task_count) $composite_status = 'Completed';
                                        elseif ($avg_progress > 0) $composite_status = 'In Progress';
                                        else $composite_status = 'Planned';

                                        $badge_class = $status_badges[$composite_status] ?? 'bg-secondary';
                                        $prog_color = get_progress_color($avg_progress, $composite_status);
                                    ?>
                                        <td class="text-center p-2" style="cursor: pointer;" onclick='openPlatformWorkspace(<?= $m['id'] ?>, "<?= $pk ?>", "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>")' title="Click to view & manage <?= $task_count ?> task(s)">
                                            <div class="platform-cell-box d-flex flex-column align-items-center justify-content-center p-1">
                                                <div class="mb-1">
                                                    <?= render_circular_progress($avg_progress, $prog_color, 36, 3.2, '0.65rem') ?>
                                                </div>
                                                <span class="badge <?= $badge_class ?> px-2 py-1 mb-1" style="font-size: 0.62rem;"><?= htmlspecialchars($composite_status) ?></span>
                                                <span class="text-muted" style="font-size: 0.65rem; font-weight: 600; line-height: 1.1;"><?= $completed_tasks ?>/<?= $task_count ?> Tasks</span>
                                                <?php if ($issue_count > 0): ?>
                                                    <span class="badge bg-danger-subtle text-danger border px-1 mt-1" style="font-size: 0.60rem;" title="<?= $issue_count ?> task(s) have pending issues">
                                                        <i class="bi bi-bug-fill me-1"></i><?= $issue_count ?> Issue<?= $issue_count > 1 ? 's' : '' ?>
                                                    </span>
                                                <?php elseif ($latest_deadline): ?>
                                                    <span class="text-muted font-monospace mt-1" style="font-size: 0.62rem;" title="Target Deadline: <?= htmlspecialchars($latest_deadline) ?>">
                                                        <i class="bi bi-calendar-event me-1 text-primary"></i><?= date('M d, y', strtotime($latest_deadline)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" title="Edit Feature" onclick='openMasterEditModal(<?= $m_json ?>)'><i class="bi bi-pencil-square"></i></button>
                                        <button type="button" class="btn btn-outline-danger" title="Delete Feature" onclick='deleteFeature(<?= $m['id'] ?>, "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>")'><i class="bi bi-trash3"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 4. BOOTSTRAP 5 MODALS                                          -->
<!-- ============================================================== -->

<!-- 4.1 Add Master Feature Modal -->
<div class="modal fade" id="addFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Add New Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFeatureForm" onsubmit="submitAddFeature(event)">
                <input type="hidden" name="action" value="add_feature">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold"><i class="bi bi-folder2-open me-1 text-primary"></i>Module <span class="text-danger">*</span></label>
                            <select class="form-select" name="module" required>
                                <option value="">-- Select Module --</option>
                                <?php foreach ($modules_list as $mod): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>"><?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold"><i class="bi bi-tag-fill me-1 text-primary"></i>Feature Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="feature_name" placeholder="Enter feature name..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold"><i class="bi bi-card-text me-1 text-primary"></i>Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Brief description of the feature..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block"><i class="bi bi-grid-3x3-gap-fill me-1 text-primary"></i>Applicable Platforms</label>
                            <span class="text-muted small d-block mb-2">Check the platforms where this feature applies.</span>
                            <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded border">
                                <?php foreach ($platforms as $pk => $pinfo): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="platforms[]" value="<?= $pk ?>" id="chk_plat_<?= $pk ?>" checked>
                                        <label class="form-check-label fw-semibold text-<?= $pinfo['color'] ?>" for="chk_plat_<?= $pk ?>"><i class="<?= $pinfo['icon'] ?> me-1"></i><?= $pinfo['title'] ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnAddSubmit">Save Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.3 Platform Multi-Task & Issue Workspace Modal -->
<div class="modal fade" id="platformWorkspaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="pw_modal_title"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Platform Workspace</h5>
                    <span class="text-muted small" id="pw_modal_sub">Feature: ...</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="p-3 bg-primary-subtle border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div id="pw_summary_circ"></div>
                    <div>
                        <div class="fw-bold fs-6 text-dark" id="pw_summary_text">0 / 0 Tasks Completed</div>
                        <small class="text-muted" id="pw_summary_deadline">Target Deadline: N/A</small>
                    </div>
                </div>
                <div><button type="button" class="btn btn-primary btn-sm" onclick="showNewTaskForm()"><i class="bi bi-plus-lg me-1"></i> Add Task</button></div>
            </div>
            <div class="modal-body p-3">
                <div id="pw_new_task_box" class="card mb-3 border-primary shadow-sm" style="display: none;">
                    <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small">Add New Task or Issue Item</span>
                        <button type="button" class="btn-close btn-close-white btn-sm" onclick="hideNewTaskForm()"></button>
                    </div>
                    <div class="card-body p-3">
                        <form id="pwNewTaskForm" onsubmit="submitNewTask(event)">
                            <input type="hidden" name="action" value="save_platform_task">
                            <input type="hidden" name="task_id" value="0">
                            <input type="hidden" name="feature_id" id="nt_feature_id">
                            <input type="hidden" name="platform" id="nt_platform">
                            <div class="row g-2">
                                <div class="col-md-5"><label class="small fw-semibold text-muted">Title <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="task_title" required></div>
                                <div class="col-md-4"><label class="small fw-semibold text-muted">Path</label><input type="text" class="form-control form-control-sm" name="script_path"></div>
                                <div class="col-md-3"><label class="small fw-semibold text-muted">Status</label><select class="form-select form-select-sm" name="status"><?php foreach ($status_badges as $sn => $sb) echo "<option value='$sn'>$sn</option>"; ?></select></div>
                                <div class="col-md-4"><label class="small fw-semibold text-muted">Progress (%)</label><input type="number" class="form-control form-control-sm" name="progress_percent" min="0" max="100" value="0"></div>
                                <div class="col-md-4"><label class="small fw-semibold text-muted">Priority</label><select class="form-select form-select-sm" name="priority"><option value="Critical">Critical</option><option value="High">High</option><option value="Medium" selected>Medium</option></select></div>
                                <div class="col-md-4"><label class="small fw-semibold text-muted">Deadline</label><input type="date" class="form-control form-control-sm" name="estimated_deadline"></div>
                                <div class="col-md-6"><label class="small fw-semibold text-danger">Issues</label><textarea class="form-control form-control-sm" name="issue_notes" rows="2"></textarea></div>
                                <div class="col-md-6"><label class="small fw-semibold text-success">Fix/Note</label><textarea class="form-control form-control-sm" name="dev_response" rows="2"></textarea></div>
                                <div class="col-12 text-end"><button type="button" class="btn btn-sm btn-secondary" onclick="hideNewTaskForm()">Cancel</button><button type="submit" class="btn btn-sm btn-primary" id="btnSaveNewTask">Save Task</button></div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="pw_tasks_container"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const platformsInfo = <?= json_encode($platforms) ?>;
    const statusBadgesList = <?= json_encode($status_badges) ?>;
    let currentWorkspaceFeatureId = 0, currentWorkspacePlatform = '';

    function showModal(id) { bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show(); }
    function hideModal(id) { const m = bootstrap.Modal.getInstance(document.getElementById(id)); if (m) m.hide(); }

    function submitAddFeature(e) {
        e.preventDefault();
        const fd = new FormData(document.getElementById('addFeatureForm'));
        fetch('feature-tracker.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => {
            if (res.status === 'success') location.reload(); else alert(res.message);
        });
    }

    function deleteFeature(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) return;
        const fd = new FormData(); fd.append('action', 'delete_feature'); fd.append('id', id);
        fetch('feature-tracker.php', { method: 'POST', body: fd }).then(r => r.json()).then(() => location.reload());
    }

    function openPlatformWorkspace(featureId, platformKey, featureName) {
        currentWorkspaceFeatureId = featureId; currentWorkspacePlatform = platformKey;
        const pinfo = platformsInfo[platformKey];
        document.getElementById('pw_modal_title').innerHTML = `<i class="${pinfo.icon} text-${pinfo.color} me-2"></i> ${pinfo.title} Workspace`;
        document.getElementById('pw_modal_sub').innerText = `Feature: ${featureName}`;
        document.getElementById('nt_feature_id').value = featureId; document.getElementById('nt_platform').value = platformKey;
        hideNewTaskForm(); loadPlatformTasks(); showModal('platformWorkspaceModal');
    }

    function loadPlatformTasks() {
        const fd = new FormData(); fd.append('action', 'get_platform_tasks'); fd.append('feature_id', currentWorkspaceFeatureId); fd.append('platform', currentWorkspacePlatform);
        fetch('feature-tracker.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') renderPlatformTasks(res.tasks || []); });
    }

    function renderPlatformTasks(tasks) {
        const container = document.getElementById('pw_tasks_container');
        let completed = 0, totalSum = 0, latestDate = null, issues = 0;
        tasks.forEach(t => {
            const pct = parseInt(t.progress_percent || 0); totalSum += pct;
            if (t.status === 'Completed' || pct >= 100) completed++;
            if (t.status === 'Issue' || (t.issue_notes && t.issue_notes.trim())) issues++;
            if (t.estimated_deadline) latestDate = (!latestDate || t.estimated_deadline > latestDate) ? t.estimated_deadline : latestDate;
        });
        const total = tasks.length, avg = total > 0 ? Math.round(totalSum / total) : 0;
        document.getElementById('pw_summary_text').innerText = `${completed} of ${total} Tasks Completed (Avg: ${avg}%)`;
        document.getElementById('pw_summary_deadline').innerHTML = latestDate ? `Target: <strong>${latestDate}</strong>` : `No deadline`;

        let html = '';
        tasks.forEach((t, idx) => {
            html += `<div class="card shadow-sm mb-3"><div class="card-body p-3"><form onsubmit="submitUpdateTask(event, ${t.id})"><input type="hidden" name="action" value="save_platform_task"><input type="hidden" name="task_id" value="${t.id}"><input type="hidden" name="feature_id" value="${t.feature_id}"><input type="hidden" name="platform" value="${t.platform}"><div class="row g-2"><div class="col-md-5"><input type="text" class="form-control form-control-sm" name="task_title" value="${escapeHtml(t.task_title)}" required></div><div class="col-md-3"><select class="form-select form-select-sm" name="status">${Object.keys(statusBadgesList).map(st => `<option value="${st}" ${t.status === st ? 'selected' : ''}>${st}</option>`).join('')}</select></div><div class="col-md-2"><div class="input-group input-group-sm"><input type="number" class="form-control" name="progress_percent" value="${t.progress_percent}"></div></div><div class="col-md-2 text-end"><button type="submit" class="btn btn-sm btn-primary">Update</button></div></div></form></div></div>`;
        });
        container.innerHTML = html;
    }

    function showNewTaskForm() { document.getElementById('pw_new_task_box').style.display = 'block'; }
    function hideNewTaskForm() { document.getElementById('pw_new_task_box').style.display = 'none'; }

    function submitNewTask(e) { e.preventDefault(); fetch('feature-tracker.php', { method: 'POST', body: new FormData(document.getElementById('pwNewTaskForm')) }).then(() => { hideNewTaskForm(); loadPlatformTasks(); }); }

    function submitUpdateTask(e, id) { e.preventDefault(); fetch('feature-tracker.php', { method: 'POST', body: new FormData(e.target) }).then(() => loadPlatformTasks()); }

    function deletePlatformTask(id) { if(confirm('Delete?')) { const fd = new FormData(); fd.append('action', 'delete_platform_task'); fd.append('task_id', id); fetch('feature-tracker.php', { method: 'POST', body: fd }).then(() => loadPlatformTasks()); } }

    function seedDemoData() {
        const fd = new FormData(); fd.append('action', 'seed_default_data');
        fetch('feature-tracker.php', { method: 'POST', body: fd }).then(() => location.reload());
    }

    document.getElementById('platformWorkspaceModal').addEventListener('hidden.bs.modal', () => location.reload());

    function escapeHtml(str) { return String(str).replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m])); }
</script>

<?php require_once 'footer.php'; ?>
