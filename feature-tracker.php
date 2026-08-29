<?php
ob_start();
/**
 * EIMBox Multi-Platform Feature & Issue Tracker
 * Advanced Multi-Task & Multi-Issue Architecture
 * Pure Bootstrap 5 Implementation with 100% Reactive AJAX Engine
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

        // Migration 2: Drop UNIQUE index safely if it exists
        $chk_idx = $conn->query("SHOW INDEX FROM `eimbox_platform_tracker` WHERE Key_name = 'idx_feature_platform' AND Non_unique = 0");
        if ($chk_idx && $chk_idx->num_rows > 0) {
            $conn->query("ALTER TABLE `eimbox_platform_tracker` ADD INDEX `idx_feat_plat_temp` (`feature_id`, `platform`)");
            $conn->query("ALTER TABLE `eimbox_platform_tracker` DROP INDEX `idx_feature_platform`");
            $conn->query("ALTER TABLE `eimbox_platform_tracker` ADD INDEX `idx_feature_platform` (`feature_id`, `platform`)");
            $conn->query("ALTER TABLE `eimbox_platform_tracker` DROP INDEX `idx_feat_plat_temp`");
        }
    } catch (Throwable $e) {
        error_log("Schema ensure notice: " . $e->getMessage());
    }
}

ensure_tracker_schema($conn);

// ==============================================================
// 2. HELPER FUNCTIONS & DATA RETRIEVAL LOGIC
// ==============================================================
function fetch_matrix_data_array($conn, $f_module = 'all', $f_platform = 'all', $f_status = 'all', $f_search = '', $f_issues = false) {
    try {
        $where_clauses = ["1=1"];
        if ($f_module !== 'all' && !empty($f_module)) {
            $where_clauses[] = "m.module = '" . $conn->real_escape_string($f_module) . "'";
        }
        if (!empty($f_search)) {
            $s_term = $conn->real_escape_string($f_search);
            $where_clauses[] = "(m.feature_name LIKE '%$s_term%' OR m.module LIKE '%$s_term%' OR m.description LIKE '%$s_term%' OR EXISTS (
                SELECT 1 FROM eimbox_platform_tracker pt WHERE pt.feature_id = m.id AND (pt.script_path LIKE '%$s_term%' OR pt.task_title LIKE '%$s_term%' OR pt.issue_notes LIKE '%$s_term%' OR pt.dev_response LIKE '%$s_term%')
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
        $sql = "SELECT m.* FROM eimbox_features_master m WHERE $where_sql ORDER BY m.id DESC";
        $features_res = $conn->query($sql);
        $features = [];
        $feature_ids = [];

        if ($features_res && $features_res->num_rows > 0) {
            while ($row = $features_res->fetch_assoc()) {
                $features[$row['id']] = [
                    'master' => $row,
                    'platforms' => [
                        'dashboard' => [],
                        'console' => [],
                        'android_lite' => [],
                        'premium' => [],
                        'desktop' => []
                    ]
                ];
                $feature_ids[] = intval($row['id']);
            }
        }

        if (!empty($feature_ids)) {
            $f_ids_str = implode(',', $feature_ids);
            $plat_res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id IN ($f_ids_str) ORDER BY id ASC");
            if ($plat_res) {
                while ($prow = $plat_res->fetch_assoc()) {
                    $fid = intval($prow['feature_id']);
                    $plat = $prow['platform'];
                    if (isset($features[$fid]['platforms'][$plat])) {
                        $features[$fid]['platforms'][$plat][] = $prow;
                    }
                }
            }
        }

        // Global counts
        $total_features_count = 0;
        $c_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_features_master");
        if ($c_res) $total_features_count = intval($c_res->fetch_assoc()['c'] ?? 0);

        $total_issues_count = 0;
        $i_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_platform_tracker WHERE status = 'Issue' OR (issue_notes IS NOT NULL AND TRIM(issue_notes) != '')");
        if ($i_res) $total_issues_count = intval($i_res->fetch_assoc()['c'] ?? 0);

        $plat_stats = [
            'dashboard'    => ['total' => 0, 'completed' => 0, 'percent' => 0],
            'console'      => ['total' => 0, 'completed' => 0, 'percent' => 0],
            'android_lite' => ['total' => 0, 'completed' => 0, 'percent' => 0],
            'premium'      => ['total' => 0, 'completed' => 0, 'percent' => 0],
            'desktop'      => ['total' => 0, 'completed' => 0, 'percent' => 0]
        ];

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
                $pk = $sp['platform'];
                if (isset($plat_stats[$pk])) {
                    $plat_stats[$pk] = [
                        'total' => intval($sp['total_tasks']),
                        'completed' => intval($sp['completed_tasks']),
                        'percent' => round(floatval($sp['avg_progress'] ?? 0))
                    ];
                }
            }
        }

        return [
            'features' => array_values($features),
            'plat_stats' => $plat_stats,
            'total_features_count' => $total_features_count,
            'total_issues_count' => $total_issues_count
        ];
    } catch (Throwable $e) {
        error_log("fetch_matrix_data_array error: " . $e->getMessage());
        return [
            'features' => [],
            'plat_stats' => [
                'dashboard'    => ['total' => 0, 'completed' => 0, 'percent' => 0],
                'console'      => ['total' => 0, 'completed' => 0, 'percent' => 0],
                'android_lite' => ['total' => 0, 'completed' => 0, 'percent' => 0],
                'premium'      => ['total' => 0, 'completed' => 0, 'percent' => 0],
                'desktop'      => ['total' => 0, 'completed' => 0, 'percent' => 0]
            ],
            'total_features_count' => 0,
            'total_issues_count' => 0
        ];
    }
}

// ==============================================================
// 3. BACKEND CRUD & AJAX API HANDLERS
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'];

    // 3.1 Get Filtered Matrix Data (AJAX)
    if ($action === 'get_matrix_data') {
        try {
            $f_module = $_POST['module'] ?? 'all';
            $f_platform = $_POST['platform'] ?? 'all';
            $f_status = $_POST['status'] ?? 'all';
            $f_search = trim($_POST['search'] ?? '');
            $f_issues = isset($_POST['issues_only']) && ($_POST['issues_only'] == '1' || $_POST['issues_only'] == 'true');

            $data = fetch_matrix_data_array($conn, $f_module, $f_platform, $f_status, $f_search, $f_issues);

            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3.2 Add Feature
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

                echo json_encode(['status' => 'success', 'message' => 'New feature created successfully!', 'feature_id' => $new_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create feature: ' . $conn->error]);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3.3 Edit Master Feature
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

    // 3.4 Delete Feature
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

    // 3.5 Get All Tasks & Details for a Platform
    if ($action === 'get_platform_tasks') {
        try {
            $feature_id = intval($_POST['feature_id'] ?? 0);
            $platform = trim($_POST['platform'] ?? '');

            $f_res = $conn->query("SELECT id, feature_name, module, description FROM eimbox_features_master WHERE id = $feature_id LIMIT 1");
            $f_data = $f_res ? $f_res->fetch_assoc() : ['id' => $feature_id, 'feature_name' => 'Feature #' . $feature_id, 'module' => '', 'description' => ''];

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
                'platform' => $platform,
                'tasks' => $tasks
            ]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3.6 Save Platform Task (Create or Update)
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
                $stmt = $conn->prepare("
                    UPDATE eimbox_platform_tracker
                    SET task_title = ?, script_path = ?, status = ?, priority = ?, progress_percent = ?,
                        issue_notes = ?, dev_response = ?, assigned_to = ?, estimated_deadline = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssissssi", $task_title, $script_path, $status, $priority, $progress, $issue_notes, $dev_response, $assigned_to, $deadline, $task_id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(['status' => 'success', 'message' => 'Task updated successfully!', 'task_id' => $task_id]);
            } else {
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
                $stmt->execute();
                $new_task_id = $stmt->insert_id;
                $stmt->close();
                echo json_encode(['status' => 'success', 'message' => 'New task / issue added successfully!', 'task_id' => $new_task_id]);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3.7 Delete Individual Platform Task
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

    // 3.8 Seed Realistic Default Data
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
                            ['title' => 'Offline Biometric Punch Sync Engine', 'path' => 'core/offline_sync.php', 'status' => 'In Progress', 'pct' => 45, 'prio' => 'High', 'date' => '2026-09-18', 'issue' => 'Conflict resolution on duplicate RFID punches', 'dev' => 'Added timestamp reconciliation window']
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
                            ['title' => 'Mobile Camera OMR Bubble Detection', 'path' => 'lib/scanner/omr_vision.dart', 'status' => 'In Progress', 'pct' => 70, 'prio' => 'Critical', 'date' => '2026-09-12', 'issue' => 'Skewed perspective correction needed for angled capture', 'dev' => 'Added OpenCV warp perspective matrix filter']
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
                            ['title' => 'Auto Differential Cloud Sync Daemon', 'path' => 'desktop/sync_daemon.py', 'status' => 'In Progress', 'pct' => 50, 'prio' => 'Critical', 'date' => '2026-09-20', 'issue' => 'Delta sync conflict when two devices edit same student record simultaneously', 'dev' => 'Implementing last-write-wins CRDT structure']
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
// 4. INITIAL FRONTEND DATA PREPARATION
// ==============================================================
$platforms = [
    'dashboard'    => ['title' => 'Dashboard',    'sub' => 'Web App',       'color' => 'primary', 'icon' => 'bi bi-laptop'],
    'console'      => ['title' => 'Console',      'sub' => 'Superadmin',    'color' => 'dark',    'icon' => 'bi bi-terminal'],
    'android_lite' => ['title' => 'Android Lite', 'sub' => 'Mobile App',    'color' => 'success', 'icon' => 'bi bi-phone'],
    'premium'      => ['title' => 'Offline Prem', 'sub' => 'Offline Bundle','color' => 'warning', 'icon' => 'bi bi-hdd-network'],
    'desktop'      => ['title' => 'Desktop',      'sub' => 'Windows Exec',  'color' => 'info',    'icon' => 'bi bi-display']
];

$plat_color_hexes = [
    'primary'   => '#696cff',
    'dark'      => '#233446',
    'success'   => '#71dd37',
    'warning'   => '#ffab00',
    'info'      => '#03c3ec',
    'danger'    => '#ff3e1d',
    'secondary' => '#8592a3'
];

$status_badges = [
    'Completed'   => 'bg-success',
    'In Progress' => 'bg-primary',
    'Testing'     => 'bg-info',
    'Planned'     => 'bg-secondary',
    'Issue'       => 'bg-danger',
    'On Hold'     => 'bg-warning'
];

$priority_badges = [
    'Critical' => 'bg-danger text-white',
    'High'     => 'bg-warning text-dark',
    'Medium'   => 'bg-primary text-white',
    'Low'      => 'bg-secondary text-white'
];

// Fetch Master Modules for Filter Dropdown (Safe fallback query)
$modules_list = [];
try {
    $mod_query = $conn->query("SELECT module_name, core FROM modulelist GROUP BY module_name, core ORDER BY MIN(slno) ASC, module_name ASC");
    if ($mod_query && $mod_query->num_rows > 0) {
        while ($m_row = $mod_query->fetch_assoc()) {
            $modules_list[] = $m_row;
        }
    }
} catch (Throwable $e) {
    error_log("Modulelist fetch notice: " . $e->getMessage());
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

// Initial Filter States
$f_module = $_GET['module'] ?? 'all';
$f_platform = $_GET['platform'] ?? 'all';
$f_status = $_GET['status'] ?? 'all';
$f_search = trim($_GET['search'] ?? '');
$f_issues = isset($_GET['issues_only']) && $_GET['issues_only'] == '1';

// Initial Data Load
$initial_data = fetch_matrix_data_array($conn, $f_module, $f_platform, $f_status, $f_search, $f_issues);
$features = $initial_data['features'];
$plat_stats = $initial_data['plat_stats'];
$total_features_count = $initial_data['total_features_count'];
$total_issues_count = $initial_data['total_issues_count'];

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
        min-height: 76px;
        transition: all 0.18s ease-in-out;
        border-radius: 8px;
    }
    .platform-cell-box:hover {
        background-color: rgba(105, 108, 255, 0.08);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .platform-cell-box.has-issue {
        background-color: rgba(255, 62, 29, 0.04);
        border: 1px dashed rgba(255, 62, 29, 0.3);
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
        border-radius: 8px;
    }
    .task-card.task-has-issue {
        border-left: 4px solid #ff3e1d !important;
        background-color: #fff9f8;
    }
    .task-card.task-completed {
        border-left: 4px solid #71dd37;
    }
    .task-card.task-testing {
        border-left: 4px solid #03c3ec;
    }
    .tracker-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 10999;
        min-width: 280px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    }
    .search-spinner {
        display: none;
    }
    .search-spinner.active {
        display: inline-block;
    }
    .table th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid container-p-y">

    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>EIMBox Multi-Platform Feature Tracker</h4>
            <p class="text-muted small mb-0">Multi-issue & multi-task matrix with reactive background AJAX processing across Dashboard, Console, Android Lite, Offline Premium & Desktop</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnIssuesOnly" class="btn btn-sm <?= $f_issues ? 'btn-danger' : 'btn-outline-danger' ?>" onclick="toggleIssuesOnly()">
                <i class="bi bi-bug-fill me-1"></i> Issues Only (<span id="countIssuesHeader"><?= $total_issues_count ?></span>)
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showModal('addFeatureModal')">
                <i class="bi bi-plus-lg me-1"></i> Add New Feature
            </button>
        </div>
    </div>

    <!-- Platform KPI Cards -->
    <div class="row g-2 mb-3" id="kpiCardsContainer">
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
                        <div class="my-1" id="kpi_circ_<?= $pk ?>">
                            <?= render_circular_progress($st_pct, $kpi_color, 46, 3.4, '0.72rem') ?>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;" id="kpi_text_<?= $pk ?>"><?= $st_comp ?>/<?= $st_total ?> Tasks Done</small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 col-md-4 col-lg-2">
            <div class="card text-center h-100 bg-primary text-white shadow-sm border-0">
                <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                    <div class="small fw-semibold mb-1"><i class="bi bi-list-task me-1"></i> All Features</div>
                    <div class="fs-4 fw-bold my-1" id="totalFeaturesCard"><?= $total_features_count ?></div>
                    <a href="javascript:void(0)" onclick="resetFiltersAjax()" class="text-white-50 text-decoration-none d-block small" style="font-size: 0.7rem;"><i class="bi bi-arrow-clockwise me-1"></i>Reset All Filters</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Live Search Toolbar -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body p-3">
            <form id="filterForm" onsubmit="event.preventDefault(); applyFiltersAjax();" class="row g-2 align-items-center">
                <!-- Search Input with Debounce -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search" id="searchIcon"></i>
                            <div class="spinner-border spinner-border-sm text-primary search-spinner" id="searchSpinner" role="status"></div>
                        </span>
                        <input type="text" class="form-control" id="filter_search" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Search feature, module, script, issues..." oninput="onSearchInput(this.value)">
                    </div>
                </div>

                <!-- Module Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter_module" name="module" onchange="applyFiltersAjax()">
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
                    <select class="form-select form-select-sm" id="filter_platform" name="platform" onchange="applyFiltersAjax()">
                        <option value="all">All Platforms</option>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <option value="<?= $pk ?>" <?= $f_platform === $pk ? 'selected' : '' ?>><?= $pinfo['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter_status" name="status" onchange="applyFiltersAjax()">
                        <option value="all">All Statuses</option>
                        <?php foreach ($status_badges as $st_key => $st_badge): ?>
                            <option value="<?= $st_key ?>" <?= $f_status === $st_key ? 'selected' : '' ?>><?= $st_key ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action buttons -->
                <div class="col-lg-3 col-md-9 col-6 text-end">
                    <button type="button" class="btn btn-sm btn-primary me-1" onclick="applyFiltersAjax()"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFiltersAjax()"><i class="bi bi-arrow-clockwise me-1"></i> Reset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Features Matrix Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0" id="featuresMatrixTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 220px;">Module & Feature Name</th>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <th class="text-center" style="min-width: 130px;">
                                <div class="text-<?= $pinfo['color'] ?> fw-bold">
                                    <i class="<?= $pinfo['icon'] ?> me-1"></i> <?= $pinfo['title'] ?>
                                </div>
                                <span class="text-muted font-monospace" style="font-size: 0.65rem;"><?= $pinfo['sub'] ?></span>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="matrixTableBody">
                    <?php if (empty($features)): ?>
                        <tr id="emptyMatrixRow">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No records found!</h5>
                                <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="seedDemoData()"><i class="bi bi-cloud-arrow-down me-1"></i> Load Demo Data</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($features as $f_item): 
                            $m = $f_item['master'];
                            $p_data = $f_item['platforms'];
                        ?>
                            <tr id="feature_row_<?= $m['id'] ?>">
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
                                        <td class="text-center p-2" style="cursor: pointer;" onclick="openPlatformWorkspace(<?= (int)$m['id'] ?>, '<?= $pk ?>')" title="Click to configure tasks for <?= $pinfo['title'] ?>">
                                            <div class="platform-cell-box d-flex flex-column align-items-center justify-content-center p-1">
                                                <span class="badge na-badge px-2 py-1 mb-1">
                                                    <i class="bi bi-slash-circle me-1"></i>N/A
                                                </span>
                                                <span class="text-muted" style="font-size: 0.65rem;">Not Configured</span>
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
                                        <td class="text-center p-2" style="cursor: pointer;" onclick="openPlatformWorkspace(<?= (int)$m['id'] ?>, '<?= $pk ?>')" title="Click to view & manage <?= $task_count ?> task(s)">
                                            <div class="platform-cell-box <?= $issue_count > 0 ? 'has-issue' : '' ?> d-flex flex-column align-items-center justify-content-center p-1">
                                                <div class="mb-1">
                                                    <?= render_circular_progress($avg_progress, $prog_color, 36, 3.2, '0.65rem') ?>
                                                </div>
                                                <span class="badge <?= $badge_class ?> px-2 py-1 mb-1" style="font-size: 0.62rem;"><?= htmlspecialchars($composite_status) ?></span>
                                                <span class="text-muted" style="font-size: 0.65rem; font-weight: 600; line-height: 1.1;"><?= $completed_tasks ?>/<?= $task_count ?> Tasks</span>
                                                <?php if ($issue_count > 0): ?>
                                                    <span class="badge bg-danger text-white px-1 mt-1 shadow-sm" style="font-size: 0.60rem;" title="<?= $issue_count ?> task(s) have pending issues">
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
                                        <button type="button" class="btn btn-outline-primary" title="Edit Feature" onclick='openMasterEditModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)'><i class="bi bi-pencil-square"></i></button>
                                        <button type="button" class="btn btn-outline-danger" title="Delete Feature" onclick='deleteFeature(<?= $m['id'] ?>, <?= json_encode($m['feature_name']) ?>)'><i class="bi bi-trash3"></i></button>
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

<!-- Toast Notification Container -->
<div id="toastNotification" class="toast align-items-center text-white bg-primary border-0 tracker-toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
        <div class="toast-body d-flex align-items-center" id="toastMessage">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> Operation completed successfully.
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<!-- ============================================================== -->
<!-- 5. BOOTSTRAP 5 MODALS                                          -->
<!-- ============================================================== -->

<!-- 5.1 Add Master Feature Modal -->
<div class="modal fade" id="addFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
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
                            <span class="text-muted small d-block mb-2">Select the platforms where this feature is applicable:</span>
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
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnAddSubmit"><i class="bi bi-check-lg me-1"></i>Save Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5.2 Edit Master Feature Modal -->
<div class="modal fade" id="editFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Feature Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFeatureForm" onsubmit="submitEditMasterFeature(event)">
                <input type="hidden" name="action" value="edit_master_feature">
                <input type="hidden" name="id" id="edit_feature_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold"><i class="bi bi-folder2-open me-1 text-primary"></i>Module <span class="text-danger">*</span></label>
                            <select class="form-select" name="module" id="edit_feature_module" required>
                                <option value="">-- Select Module --</option>
                                <?php foreach ($modules_list as $mod): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>"><?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold"><i class="bi bi-tag-fill me-1 text-primary"></i>Feature Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="feature_name" id="edit_feature_name" placeholder="Enter feature name..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold"><i class="bi bi-card-text me-1 text-primary"></i>Description</label>
                            <textarea class="form-control" name="description" id="edit_feature_description" rows="3" placeholder="Brief description of the feature..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnEditSubmit"><i class="bi bi-check-lg me-1"></i>Update Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5.3 Platform Multi-Task & Issue Workspace Modal -->
<div class="modal fade" id="platformWorkspaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <!-- Modal Header -->
            <div class="modal-header bg-light py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="pw_modal_title">
                        <i class="bi bi-gear-wide-connected text-primary me-2"></i>Platform Workspace
                    </h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-primary-subtle text-primary border" id="pw_modal_module">Module: ...</span>
                        <span class="text-dark fw-semibold small" id="pw_modal_sub">Feature: ...</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Workspace KPI & Action Banner -->
            <div class="p-3 bg-primary-subtle border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div id="pw_summary_circ"></div>
                    <div>
                        <div class="fw-bold fs-6 text-dark" id="pw_summary_text">0 / 0 Tasks Completed</div>
                        <div class="small text-muted d-flex align-items-center gap-2">
                            <span id="pw_summary_deadline"><i class="bi bi-calendar-event me-1 text-primary"></i>Target Deadline: N/A</span>
                            <span id="pw_summary_issues" class="badge bg-danger text-white d-none"><i class="bi bi-bug-fill me-1"></i>0 Issues</span>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="toggleNewTaskForm()">
                        <i class="bi bi-plus-lg me-1"></i> Add Task / Issue
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3" style="max-height: 75vh; overflow-y: auto;">
                
                <!-- Add New Task / Issue Form (Collapsible) -->
                <div id="pw_new_task_box" class="card mb-3 border-primary shadow-sm" style="display: none;">
                    <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small"><i class="bi bi-plus-circle-fill me-1"></i> Add New Task or Issue Item</span>
                        <button type="button" class="btn-close btn-close-white btn-sm" onclick="hideNewTaskForm()"></button>
                    </div>
                    <div class="card-body p-3 bg-light">
                        <form id="pwNewTaskForm" onsubmit="submitNewTask(event)">
                            <input type="hidden" name="action" value="save_platform_task">
                            <input type="hidden" name="task_id" value="0">
                            <input type="hidden" name="feature_id" id="nt_feature_id">
                            <input type="hidden" name="platform" id="nt_platform">

                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-muted mb-1">Task / Issue Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="task_title" id="nt_task_title" placeholder="e.g. Implement camera scanner / Fix sync bug" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Script / File Path</label>
                                    <input type="text" class="form-control form-control-sm font-monospace" name="script_path" id="nt_script_path" placeholder="e.g. attendance-daily.php or lib/scanner.dart">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                                    <select class="form-select form-select-sm" name="status" id="nt_status" onchange="onNewTaskStatusChange(this.value)">
                                        <?php foreach ($status_badges as $sn => $sb): ?>
                                            <option value="<?= $sn ?>"><?= $sn ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Progress (<span id="nt_prog_label">0</span>%)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" class="form-range" min="0" max="100" value="0" id="nt_progress_range" oninput="document.getElementById('nt_progress_percent').value = this.value; document.getElementById('nt_prog_label').innerText = this.value;">
                                        <input type="number" class="form-control form-control-sm text-center" style="width: 65px;" name="progress_percent" id="nt_progress_percent" min="0" max="100" value="0" oninput="document.getElementById('nt_progress_range').value = this.value; document.getElementById('nt_prog_label').innerText = this.value;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                                    <select class="form-select form-select-sm" name="priority" id="nt_priority">
                                        <option value="Critical">Critical</option>
                                        <option value="High">High</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Target Deadline</label>
                                    <input type="date" class="form-control form-control-sm" name="estimated_deadline" id="nt_deadline">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Assigned To</label>
                                    <input type="text" class="form-control form-control-sm" name="assigned_to" id="nt_assigned" placeholder="Developer / Lead">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-danger mb-1"><i class="bi bi-bug-fill me-1"></i>Issue Description / Bug Notes</label>
                                    <textarea class="form-control form-control-sm border-danger-subtle" name="issue_notes" id="nt_issue_notes" rows="2" placeholder="Describe any bug, blocker or issue encountered..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-success mb-1"><i class="bi bi-check-circle-fill me-1"></i>Developer Fix / Resolution Notes</label>
                                    <textarea class="form-control form-control-sm border-success-subtle" name="dev_response" id="nt_dev_response" rows="2" placeholder="Describe developer response or solution implemented..."></textarea>
                                </div>

                                <div class="col-12 text-end pt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="hideNewTaskForm()">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-primary" id="btnSaveNewTask"><i class="bi bi-save me-1"></i>Save Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tasks & Issues Container -->
                <div id="pw_tasks_container">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span>Loading platform tasks...</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 6. JAVASCRIPT REACTIVE AJAX ENGINE                             -->
<!-- ============================================================== -->
<script>
    const platformsInfo = <?= json_encode($platforms) ?>;
    const statusBadgesList = <?= json_encode($status_badges) ?>;
    const priorityBadgesList = <?= json_encode($priority_badges) ?>;
    const platColorHexes = <?= json_encode($plat_color_hexes) ?>;

    let currentWorkspaceFeatureId = 0;
    let currentWorkspacePlatform = '';
    let isIssuesOnlyActive = <?= $f_issues ? 'true' : 'false' ?>;
    let searchDebounceTimer = null;

    // Guaranteed dynamic endpoint matching the current script path
    const TRACKER_API_ENDPOINT = window.location.href.split('?')[0];

    async function postTrackerApi(fd) {
        try {
            const resp = await fetch(TRACKER_API_ENDPOINT, {
                method: 'POST',
                body: fd
            });
            if (!resp.ok) {
                throw new Error(`HTTP ${resp.status}: ${resp.statusText}`);
            }
            const text = await resp.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Tracker API Non-JSON response:", text);
                throw new Error("Invalid server response. See browser console for details.");
            }
        } catch (netErr) {
            console.error("Network / API Error:", netErr);
            throw netErr;
        }
    }

    // -------------------------------------------------------------
    // Utility & Modal Helpers
    // -------------------------------------------------------------
    function showModal(id) { 
        const modalEl = document.getElementById(id);
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show(); 
        }
    }
    
    function hideModal(id) { 
        const modalEl = document.getElementById(id);
        if (modalEl && typeof bootstrap !== 'undefined') {
            const m = bootstrap.Modal.getInstance(modalEl); 
            if (m) m.hide(); 
        }
    }

    function showToast(message, isError = false) {
        const toastEl = document.getElementById('toastNotification');
        const toastMsg = document.getElementById('toastMessage');
        if (toastEl && toastMsg && typeof bootstrap !== 'undefined') {
            toastEl.className = `toast align-items-center text-white ${isError ? 'bg-danger' : 'bg-success'} border-0 tracker-toast`;
            toastMsg.innerHTML = `<i class="bi ${isError ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'} me-2 fs-5"></i> ${escapeHtml(message)}`;
            const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 });
            toast.show();
        }
    }

    function escapeHtml(str) { 
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m])); 
    }

    function getProgressColorJs(pct, status) {
        if (status === 'Issue') return '#ff3e1d';
        if (status === 'Completed' || pct >= 100) return '#71dd37';
        if (pct >= 70) return '#03c3ec';
        if (pct >= 30) return '#696cff';
        if (pct > 0) return '#ffab00';
        return '#8592a3';
    }

    function renderCircularProgressSvg(pct, color = '#696cff', size = 40, stroke = 3.5, fontSize = '0.7rem') {
        pct = Math.max(0, Math.min(100, parseInt(pct || 0)));
        const radius = (size - stroke) / 2;
        const circ = 2 * Math.PI * radius;
        const offset = circ - (pct / 100) * circ;
        return `
        <div class="circular-progress-box position-relative d-inline-flex align-items-center justify-content-center" style="width: ${size}px; height: ${size}px;">
            <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" class="d-block" style="transform: rotate(-90deg);">
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="#e7e7e7" stroke-width="${stroke}" />
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="${color}" stroke-width="${stroke}" 
                        stroke-dasharray="${circ}" stroke-dashoffset="${offset}" stroke-linecap="round" />
            </svg>
            <span class="position-absolute fw-bold" style="font-size: ${fontSize}; color: ${color}; line-height: 1; user-select: none;">
                ${pct}%
            </span>
        </div>`;
    }

    // -------------------------------------------------------------
    // Live AJAX Filtering & Real-time Search
    // -------------------------------------------------------------
    function onSearchInput(val) {
        const spinner = document.getElementById('searchSpinner');
        const icon = document.getElementById('searchIcon');
        if (spinner && icon) {
            spinner.classList.add('active');
            icon.style.display = 'none';
        }
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            applyFiltersAjax();
        }, 320);
    }

    function toggleIssuesOnly() {
        isIssuesOnlyActive = !isIssuesOnlyActive;
        const btn = document.getElementById('btnIssuesOnly');
        if (btn) {
            btn.className = isIssuesOnlyActive ? 'btn btn-sm btn-danger' : 'btn btn-sm btn-outline-danger';
        }
        applyFiltersAjax();
    }

    function resetFiltersAjax() {
        document.getElementById('filter_search').value = '';
        document.getElementById('filter_module').value = 'all';
        document.getElementById('filter_platform').value = 'all';
        document.getElementById('filter_status').value = 'all';
        isIssuesOnlyActive = false;
        const btn = document.getElementById('btnIssuesOnly');
        if (btn) btn.className = 'btn btn-sm btn-outline-danger';
        applyFiltersAjax();
    }

    function applyFiltersAjax() {
        const sVal = document.getElementById('filter_search').value.trim();
        const mVal = document.getElementById('filter_module').value;
        const pVal = document.getElementById('filter_platform').value;
        const stVal = document.getElementById('filter_status').value;

        const spinner = document.getElementById('searchSpinner');
        const icon = document.getElementById('searchIcon');

        const fd = new FormData();
        fd.append('action', 'get_matrix_data');
        fd.append('search', sVal);
        fd.append('module', mVal);
        fd.append('platform', pVal);
        fd.append('status', stVal);
        fd.append('issues_only', isIssuesOnlyActive ? '1' : '0');

        postTrackerApi(fd)
            .then(res => {
                if (spinner && icon) {
                    spinner.classList.remove('active');
                    icon.style.display = 'inline-block';
                }
                if (res.status === 'success' && res.data) {
                    renderMatrixTable(res.data.features || []);
                    updateKpiStats(res.data.plat_stats || {}, res.data.total_features_count || 0, res.data.total_issues_count || 0);
                } else {
                    showToast(res.message || 'Failed to apply filters.', true);
                }
            })
            .catch(err => {
                if (spinner && icon) {
                    spinner.classList.remove('active');
                    icon.style.display = 'inline-block';
                }
                console.error('Filter error:', err);
            });
    }

    function updateKpiStats(platStats, totalFeatures, totalIssues) {
        document.getElementById('totalFeaturesCard').innerText = totalFeatures;
        document.getElementById('countIssuesHeader').innerText = totalIssues;

        for (const pk in platformsInfo) {
            const pinfo = platformsInfo[pk];
            const pstat = platStats[pk] || { total: 0, completed: 0, percent: 0 };
            const kpiColor = platColorHexes[pinfo.color] || '#696cff';

            const circEl = document.getElementById(`kpi_circ_${pk}`);
            if (circEl) circEl.innerHTML = renderCircularProgressSvg(pstat.percent, kpiColor, 46, 3.4, '0.72rem');

            const textEl = document.getElementById(`kpi_text_${pk}`);
            if (textEl) textEl.innerText = `${pstat.completed}/${pstat.total} Tasks Done`;
        }
    }

    function renderMatrixTable(features) {
        const tbody = document.getElementById('matrixTableBody');
        if (!features || features.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyMatrixRow">
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        <h5>No records found!</h5>
                        <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="seedDemoData()"><i class="bi bi-cloud-arrow-down me-1"></i> Load Demo Data</button>
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        features.forEach(fItem => {
            const m = fItem.master;
            const pData = fItem.platforms || {};

            html += `
            <tr id="feature_row_${m.id}">
                <td class="text-center text-muted fw-bold">${m.id}</td>
                <td>
                    <span class="badge bg-primary-subtle text-primary border mb-1 font-monospace">
                        <i class="bi bi-folder2-open me-1"></i>${escapeHtml(m.module)}
                    </span>
                    <div class="fw-bold text-dark">${escapeHtml(m.feature_name)}</div>
                    ${m.description ? `<div class="text-muted small text-truncate" style="max-width: 280px;" title="${escapeHtml(m.description)}">${escapeHtml(m.description)}</div>` : ''}
                </td>`;

            for (const pk in platformsInfo) {
                const pinfo = platformsInfo[pk];
                const tasks = pData[pk] || [];
                const taskCount = tasks.length;

                if (taskCount === 0) {
                    html += `
                    <td class="text-center p-2" style="cursor: pointer;" onclick="openPlatformWorkspace(${m.id}, '${pk}')" title="Click to configure tasks for ${pinfo.title}">
                        <div class="platform-cell-box d-flex flex-column align-items-center justify-content-center p-1">
                            <span class="badge na-badge px-2 py-1 mb-1">
                                <i class="bi bi-slash-circle me-1"></i>N/A
                            </span>
                            <span class="text-muted" style="font-size: 0.65rem;">Not Configured</span>
                        </div>
                    </td>`;
                } else {
                    let completedTasks = 0;
                    let totalProgressSum = 0;
                    let issueCount = 0;
                    let latestDeadline = null;

                    tasks.forEach(t => {
                        const pct = parseInt(t.progress_percent || 0);
                        totalProgressSum += pct;
                        if (t.status === 'Completed' || pct >= 100) completedTasks++;
                        if (t.status === 'Issue' || (t.issue_notes && t.issue_notes.trim())) issueCount++;
                        if (t.estimated_deadline) {
                            if (!latestDeadline || t.estimated_deadline > latestDeadline) {
                                latestDeadline = t.estimated_deadline;
                            }
                        }
                    });

                    const avgProgress = Math.round(totalProgressSum / taskCount);
                    let compositeStatus = 'Planned';
                    if (issueCount > 0) compositeStatus = 'Issue';
                    else if (completedTasks === taskCount) compositeStatus = 'Completed';
                    else if (avgProgress > 0) compositeStatus = 'In Progress';

                    const badgeClass = statusBadgesList[compositeStatus] || 'bg-secondary';
                    const progColor = getProgressColorJs(avgProgress, compositeStatus);

                    html += `
                    <td class="text-center p-2" style="cursor: pointer;" onclick="openPlatformWorkspace(${m.id}, '${pk}')" title="Click to view & manage ${taskCount} task(s)">
                        <div class="platform-cell-box ${issueCount > 0 ? 'has-issue' : ''} d-flex flex-column align-items-center justify-content-center p-1">
                            <div class="mb-1">
                                ${renderCircularProgressSvg(avgProgress, progColor, 36, 3.2, '0.65rem')}
                            </div>
                            <span class="badge ${badgeClass} px-2 py-1 mb-1" style="font-size: 0.62rem;">${escapeHtml(compositeStatus)}</span>
                            <span class="text-muted" style="font-size: 0.65rem; font-weight: 600; line-height: 1.1;">${completedTasks}/${taskCount} Tasks</span>
                            ${issueCount > 0 ? `
                                <span class="badge bg-danger text-white px-1 mt-1 shadow-sm" style="font-size: 0.60rem;" title="${issueCount} task(s) have pending issues">
                                    <i class="bi bi-bug-fill me-1"></i>${issueCount} Issue${issueCount > 1 ? 's' : ''}
                                </span>` : (latestDeadline ? `
                                <span class="text-muted font-monospace mt-1" style="font-size: 0.62rem;" title="Target Deadline: ${escapeHtml(latestDeadline)}">
                                    <i class="bi bi-calendar-event me-1 text-primary"></i>${escapeHtml(latestDeadline)}
                                </span>` : '')
                            }
                        </div>
                    </td>`;
                }
            }

            html += `
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" title="Edit Feature" onclick='openMasterEditModal(${JSON.stringify(m)})'><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-outline-danger" title="Delete Feature" onclick='deleteFeature(${m.id}, "${escapeHtml(m.feature_name)}")'><i class="bi bi-trash3"></i></button>
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }

    // -------------------------------------------------------------
    // Master Feature Add / Edit / Delete (Pure AJAX)
    // -------------------------------------------------------------
    function submitAddFeature(e) {
        e.preventDefault();
        const btn = document.getElementById('btnAddSubmit');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`;

        const form = document.getElementById('addFeatureForm');
        const fd = new FormData(form);

        postTrackerApi(fd)
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Save Feature`;
                if (res.status === 'success') {
                    hideModal('addFeatureModal');
                    form.reset();
                    showToast(res.message);
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to save feature.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Save Feature`;
                alert(err.message || 'Network error while saving feature.');
            });
    }

    function openMasterEditModal(masterData) {
        if (!masterData) return;
        document.getElementById('edit_feature_id').value = masterData.id || 0;
        document.getElementById('edit_feature_module').value = masterData.module || '';
        document.getElementById('edit_feature_name').value = masterData.feature_name || '';
        document.getElementById('edit_feature_description').value = masterData.description || '';
        showModal('editFeatureModal');
    }

    function submitEditMasterFeature(e) {
        e.preventDefault();
        const btn = document.getElementById('btnEditSubmit');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...`;

        const form = document.getElementById('editFeatureForm');
        const fd = new FormData(form);

        postTrackerApi(fd)
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Update Feature`;
                if (res.status === 'success') {
                    hideModal('editFeatureModal');
                    showToast(res.message);
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to update feature.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Update Feature`;
                alert(err.message || 'Network error while updating feature.');
            });
    }

    function deleteFeature(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}" and all its platform tasks?`)) return;
        const fd = new FormData();
        fd.append('action', 'delete_feature');
        fd.append('id', id);

        postTrackerApi(fd)
            .then(res => {
                if (res.status === 'success') {
                    showToast(res.message);
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to delete feature.');
                }
            })
            .catch(err => alert(err.message || 'Network error while deleting feature.'));
    }

    function seedDemoData() {
        if (!confirm('Load sample multi-platform features & tasks?')) return;
        const fd = new FormData();
        fd.append('action', 'seed_default_data');

        postTrackerApi(fd)
            .then(res => {
                if (res.status === 'success') {
                    showToast(res.message);
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to load demo data.');
                }
            })
            .catch(err => alert(err.message || 'Network error loading demo data.'));
    }

    // -------------------------------------------------------------
    // Platform Workspace Modal & Task/Issue Management
    // -------------------------------------------------------------
    function openPlatformWorkspace(featureId, platformKey, fallbackFeatureName = '') {
        currentWorkspaceFeatureId = featureId;
        currentWorkspacePlatform = platformKey;

        const pinfo = platformsInfo[platformKey] || { title: platformKey, color: 'primary', icon: 'bi bi-laptop' };
        document.getElementById('pw_modal_title').innerHTML = `
            <i class="${pinfo.icon} text-${pinfo.color} me-2"></i> ${pinfo.title} Platform Workspace
        `;
        document.getElementById('pw_modal_sub').innerText = fallbackFeatureName ? `Feature: ${fallbackFeatureName}` : `Feature ID #${featureId}`;
        document.getElementById('pw_modal_module').innerText = `Loading...`;

        document.getElementById('nt_feature_id').value = featureId;
        document.getElementById('nt_platform').value = platformKey;

        hideNewTaskForm();
        loadPlatformTasks();
        showModal('platformWorkspaceModal');
    }

    function loadPlatformTasks() {
        const container = document.getElementById('pw_tasks_container');
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span>Loading tasks & issues...</span>
            </div>`;

        const fd = new FormData();
        fd.append('action', 'get_platform_tasks');
        fd.append('feature_id', currentWorkspaceFeatureId);
        fd.append('platform', currentWorkspacePlatform);

        postTrackerApi(fd)
            .then(res => {
                if (res.status === 'success') {
                    const f = res.feature || {};
                    document.getElementById('pw_modal_module').innerText = f.module ? `Module: ${f.module}` : '';
                    if (f.feature_name) {
                        document.getElementById('pw_modal_sub').innerText = `Feature: ${f.feature_name}`;
                    }
                  
                    renderPlatformTasks(res.tasks || []);
                } else {
                    container.innerHTML = `<div class="alert alert-danger py-2 small">${escapeHtml(res.message || 'Failed to load tasks.')}</div>`;
                }
            })
            .catch(err => {
                console.error('Error loading tasks:', err);
                container.innerHTML = `<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${escapeHtml(err.message || 'Error loading tasks from server.')}</div>`;
            });
    }

    function renderPlatformTasks(tasks) {

        const container = document.getElementById('pw_tasks_container');
        let completed = 0;
        let totalSum = 0;
        let latestDate = null;
        let issueCount = 0;

        tasks.forEach(t => {
            const pct = parseInt(t.progress_percent || 0);
            totalSum += pct;
            if (t.status === 'Completed' || pct >= 100) completed++;
            if (t.status === 'Issue' || (t.issue_notes && t.issue_notes.trim())) issueCount++;
            if (t.estimated_deadline) {
                if (!latestDate || t.estimated_deadline > latestDate) {
                    latestDate = t.estimated_deadline;
                }
            }
        });

        const total = tasks.length;
        const avg = total > 0 ? Math.round(totalSum / total) : 0;
        const circColor = getProgressColorJs(avg, issueCount > 0 ? 'Issue' : (completed === total ? 'Completed' : 'In Progress'));

        document.getElementById('pw_summary_circ').innerHTML = renderCircularProgressSvg(avg, circColor, 44, 3.5, '0.72rem');
        document.getElementById('pw_summary_text').innerText = `${completed} of ${total} Tasks Completed (${avg}%)`;
        document.getElementById('pw_summary_deadline').innerHTML = latestDate ? `<i class="bi bi-calendar-event me-1 text-primary"></i>Target: <strong>${escapeHtml(latestDate)}</strong>` : `<i class="bi bi-calendar-event me-1 text-muted"></i>No deadline set`;

        const issuesBadge = document.getElementById('pw_summary_issues');
        if (issueCount > 0) {
            issuesBadge.className = 'badge bg-danger text-white';
            issuesBadge.innerHTML = `<i class="bi bi-bug-fill me-1"></i>${issueCount} Active Issue${issueCount > 1 ? 's' : ''}`;
        } else {
            issuesBadge.className = 'badge bg-danger text-white d-none';
        }

        if (total === 0) {
            container.innerHTML = `
                <div class="text-center py-5 bg-light rounded border border-dashed">
                    <i class="bi bi-list-check fs-1 d-block mb-2 text-secondary"></i>
                    <h6 class="fw-bold text-dark">No tasks or issues configured for this platform yet!</h6>
                    <p class="text-muted small mb-3">Add implementation tasks, script paths, or bug reports to track platform progress.</p>
                    <button type="button" class="btn btn-sm btn-primary" onclick="showNewTaskForm()"><i class="bi bi-plus-lg me-1"></i>Add First Task / Issue</button>
                </div>`;
            return;
        }

        let html = '';
        tasks.forEach((t, idx) => {
            const hasIssue = (t.status === 'Issue' || (t.issue_notes && t.issue_notes.trim()));
            const isCompleted = (t.status === 'Completed' || parseInt(t.progress_percent) >= 100);
            const cardClass = hasIssue ? 'task-has-issue' : (isCompleted ? 'task-completed' : (t.status === 'Testing' ? 'task-testing' : ''));

            html += `
            <div class="card task-card ${cardClass} shadow-sm mb-3" id="task_card_${t.id}">
                <div class="card-header bg-white py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle text-secondary border font-monospace" style="font-size: 0.68rem;">#${idx + 1}</span>
                        <span class="fw-bold text-dark" style="font-size: 0.92rem;">${escapeHtml(t.task_title)}</span>
                        ${hasIssue ? `<span class="badge bg-danger text-white"><i class="bi bi-bug-fill me-1"></i>Bug / Issue</span>` : ''}
                        ${t.priority ? `<span class="badge ${priorityBadgesList[t.priority] || 'bg-secondary'}" style="font-size: 0.65rem;">${escapeHtml(t.priority)}</span>` : ''}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge ${statusBadgesList[t.status] || 'bg-secondary'} px-2 py-1">${escapeHtml(t.status)}</span>
                        <span class="fw-bold text-primary small">${t.progress_percent}%</span>
                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" title="Delete Task" onclick="deletePlatformTask(${t.id})"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>

                <div class="card-body p-3">
                    ${hasIssue && t.issue_notes && t.issue_notes.trim() ? `
                        <div class="alert alert-danger d-flex align-items-start gap-2 p-2 mb-3 border-danger-subtle bg-danger-subtle text-danger-emphasis rounded">
                            <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><i class="bi bi-bug-fill me-1"></i>Recorded Bug / Issue:</div>
                                <div class="small mt-1 text-break">${escapeHtml(t.issue_notes)}</div>
                            </div>
                        </div>
                    ` : ''}

                    ${t.dev_response && t.dev_response.trim() ? `
                        <div class="alert alert-success d-flex align-items-start gap-2 p-2 mb-3 border-success-subtle bg-success-subtle text-success-emphasis rounded">
                            <i class="bi bi-check-circle-fill fs-5 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><i class="bi bi-wrench-adjustable-circle me-1"></i>Developer Resolution / Response:</div>
                                <div class="small mt-1 text-break">${escapeHtml(t.dev_response)}</div>
                            </div>
                        </div>
                    ` : ''}

                    <form onsubmit="submitUpdateTask(event, ${t.id})" id="form_task_${t.id}">
                        <input type="hidden" name="action" value="save_platform_task">
                        <input type="hidden" name="task_id" value="${t.id}">
                        <input type="hidden" name="feature_id" value="${t.feature_id}">
                        <input type="hidden" name="platform" value="${t.platform}">

                        <div class="row g-2">
                            <!-- Title & Path -->
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold text-muted mb-1">Task Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="task_title" value="${escapeHtml(t.task_title)}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Script / File Path</label>
                                <input type="text" class="form-control form-control-sm font-monospace" name="script_path" value="${escapeHtml(t.script_path || '')}" placeholder="path/to/file.php">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                                <select class="form-select form-select-sm" name="status">
                                    ${Object.keys(statusBadgesList).map(st => `<option value="${st}" ${t.status === st ? 'selected' : ''}>${st}</option>`).join('')}
                                </select>
                            </div>

                            <!-- Progress & Range Slider -->
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Progress (<span id="task_prog_lbl_${t.id}">${t.progress_percent}</span>%)</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="range" class="form-range" id="task_prog_range_${t.id}" min="0" max="100" value="${t.progress_percent}" oninput="document.getElementById('task_prog_val_${t.id}').value = this.value; document.getElementById('task_prog_lbl_${t.id}').innerText = this.value;">
                                    <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" name="progress_percent" id="task_prog_val_${t.id}" min="0" max="100" value="${t.progress_percent}" oninput="document.getElementById('task_prog_range_${t.id}').value = this.value; document.getElementById('task_prog_lbl_${t.id}').innerText = this.value;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                                <select class="form-select form-select-sm" name="priority">
                                    <option value="Critical" ${t.priority === 'Critical' ? 'selected' : ''}>Critical</option>
                                    <option value="High" ${t.priority === 'High' ? 'selected' : ''}>High</option>
                                    <option value="Medium" ${t.priority === 'Medium' ? 'selected' : ''}>Medium</option>
                                    <option value="Low" ${t.priority === 'Low' ? 'selected' : ''}>Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Target Deadline</label>
                                <input type="date" class="form-control form-control-sm" name="estimated_deadline" value="${escapeHtml(t.estimated_deadline || '')}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Assigned To</label>
                                <input type="text" class="form-control form-control-sm" name="assigned_to" value="${escapeHtml(t.assigned_to || '')}" placeholder="Developer Name">
                            </div>

                            <!-- Issue Description & Fix Notes -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-danger mb-1"><i class="bi bi-bug-fill me-1"></i>Edit Issue / Bug Description</label>
                                <textarea class="form-control form-control-sm border-danger-subtle ${hasIssue ? 'bg-white' : ''}" name="issue_notes" rows="2" placeholder="Record bugs, exceptions, or blockers here...">${escapeHtml(t.issue_notes || '')}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-success mb-1"><i class="bi bi-check-circle-fill me-1"></i>Edit Solution / Developer Response</label>
                                <textarea class="form-control form-control-sm border-success-subtle" name="dev_response" rows="2" placeholder="Record resolution or fix notes here...">${escapeHtml(t.dev_response || '')}</textarea>
                            </div>

                            <div class="col-12 text-end pt-1">
                                <button type="submit" class="btn btn-sm btn-primary px-3" id="btn_update_task_${t.id}">
                                    <i class="bi bi-check2 me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>`;
        });

        container.innerHTML = html;
    }

    function toggleNewTaskForm() {
        const box = document.getElementById('pw_new_task_box');
        if (box.style.display === 'none' || box.style.display === '') {
            showNewTaskForm();
        } else {
            hideNewTaskForm();
        }
    }

    function showNewTaskForm() {
        const box = document.getElementById('pw_new_task_box');
        box.style.display = 'block';
        document.getElementById('nt_task_title').focus();
    }

    function hideNewTaskForm() {
        const box = document.getElementById('pw_new_task_box');
        box.style.display = 'none';
    }

    function onNewTaskStatusChange(val) {
        if (val === 'Completed') {
            document.getElementById('nt_progress_percent').value = 100;
            document.getElementById('nt_progress_range').value = 100;
            document.getElementById('nt_prog_label').innerText = 100;
        } else if (val === 'Issue') {
            document.getElementById('nt_priority').value = 'Critical';
        }
    }

    function submitNewTask(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveNewTask');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`;

        const form = document.getElementById('pwNewTaskForm');
        const fd = new FormData(form);

        postTrackerApi(fd)
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-save me-1"></i>Save Task`;
                if (res.status === 'success') {
                    showToast(res.message);
                    form.reset();
                    document.getElementById('nt_feature_id').value = currentWorkspaceFeatureId;
                    document.getElementById('nt_platform').value = currentWorkspacePlatform;
                    document.getElementById('nt_prog_label').innerText = 0;
                    hideNewTaskForm();
                    loadPlatformTasks();
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to add task.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-save me-1"></i>Save Task`;
                alert(err.message || 'Network error while saving task.');
            });
    }

    function submitUpdateTask(e, id) {
        e.preventDefault();
        const btn = document.getElementById(`btn_update_task_${id}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`;
        }

        const fd = new FormData(e.target);

        postTrackerApi(fd)
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-check2 me-1"></i> Save Changes`;
                }
                if (res.status === 'success') {
                    showToast(res.message);
                    loadPlatformTasks();
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to update task.');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-check2 me-1"></i> Save Changes`;
                }
                alert(err.message || 'Network error while updating task.');
            });
    }

    function deletePlatformTask(id) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_platform_task');
        fd.append('task_id', id);

        postTrackerApi(fd)
            .then(res => {
                if (res.status === 'success') {
                    showToast(res.message);
                    loadPlatformTasks();
                    applyFiltersAjax();
                } else {
                    alert(res.message || 'Failed to delete task.');
                }
            })
            .catch(err => alert(err.message || 'Network error while deleting task.'));
    }
</script>

<?php require_once 'footer.php'; ?>
