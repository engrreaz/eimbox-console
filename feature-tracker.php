<?php
/**
 * EIMBox Multi-Platform Feature & Issue Tracker
 * Pure Bootstrap 5 Implementation
 */

require_once 'core/init.php';

// ==============================================================
// 1. DATABASE SCHEMA SETUP
// ==============================================================
function ensure_tracker_schema($conn) {
    $conn->query("
        CREATE TABLE IF NOT EXISTS `eimbox_features_master` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `module` VARCHAR(100) NOT NULL,
          `feature_name` VARCHAR(150) NOT NULL,
          `description` TEXT DEFAULT NULL,
          `category` VARCHAR(50) DEFAULT 'Core',
          `display_order` INT(11) DEFAULT 0,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_module` (`module`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS `eimbox_platform_tracker` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `feature_id` INT(11) NOT NULL,
          `platform` ENUM('dashboard','console','android_lite','premium','desktop') NOT NULL,
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
          UNIQUE KEY `idx_feature_platform` (`feature_id`,`platform`),
          KEY `idx_platform` (`platform`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
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
        $module = trim($_POST['module'] ?? '');
        $feature_name = trim($_POST['feature_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($module) || empty($feature_name)) {
            echo json_encode(['status' => 'error', 'message' => 'মডিউল এবং ফিচার নাম অবশ্যই পূরণ করতে হবে!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $module, $feature_name, $description);

        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $stmt->close();

            $p_stmt = $conn->prepare("INSERT INTO eimbox_platform_tracker (feature_id, platform, status, progress_percent, priority, script_path) VALUES (?, ?, 'Planned', 0, 'Medium', '') ON DUPLICATE KEY UPDATE updated_at = NOW()");
            foreach (['dashboard', 'console', 'android_lite', 'premium', 'desktop'] as $pk) {
                $p_stmt->bind_param("is", $new_id, $pk);
                $p_stmt->execute();
            }
            $p_stmt->close();

            echo json_encode(['status' => 'success', 'message' => 'নতুন ফিচার সফলভাবে তৈরি হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'সংরক্ষণ ব্যর্থ: ' . $conn->error]);
        }
        exit;
    }

    // 2.2 Edit Master Feature
    if ($action === 'edit_master_feature') {
        $id = intval($_POST['id'] ?? 0);
        $module = trim($_POST['module'] ?? '');
        $feature_name = trim($_POST['feature_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || empty($module) || empty($feature_name)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ তথ্য বা খালি ফিল্ড!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE eimbox_features_master SET module=?, feature_name=?, description=? WHERE id=?");
        $stmt->bind_param("sssi", $module, $feature_name, $description, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'মূল ফিচার তথ্য সফলভাবে আপডেট হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 2.3 Delete Feature
    if ($action === 'delete_feature') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->query("DELETE FROM eimbox_platform_tracker WHERE feature_id = $id");
            $conn->query("DELETE FROM eimbox_features_master WHERE id = $id");
            echo json_encode(['status' => 'success', 'message' => 'ফিচারটি সফলভাবে মুছে ফেলা হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ আইডি!']);
        }
        exit;
    }

    // 2.4 Get Platform Details for Modal
    if ($action === 'get_platform_details') {
        $feature_id = intval($_POST['feature_id'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');

        $res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id = $feature_id AND platform = '" . $conn->real_escape_string($platform) . "' LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;

        if (!$row) {
            $row = [
                'feature_id' => $feature_id,
                'platform' => $platform,
                'status' => 'Planned',
                'progress_percent' => 0,
                'script_path' => '',
                'priority' => 'Medium',
                'issue_notes' => '',
                'dev_response' => '',
                'assigned_to' => '',
                'estimated_deadline' => ''
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $row]);
        exit;
    }

    // 2.5 Update Single Platform
    if ($action === 'update_platform_status') {
        $feature_id = intval($_POST['feature_id'] ?? 0);
        $platform = trim($_POST['platform'] ?? '');
        $script_path = trim($_POST['script_path'] ?? '');
        $status = trim($_POST['status'] ?? 'Planned');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $progress = intval($_POST['progress_percent'] ?? 0);
        $issue_notes = trim($_POST['issue_notes'] ?? '');
        $dev_response = trim($_POST['dev_response'] ?? '');
        $assigned_to = trim($_POST['assigned_to'] ?? '');
        $deadline = !empty($_POST['estimated_deadline']) ? $_POST['estimated_deadline'] : null;

        if ($status === 'Completed' && $progress < 100) {
            $progress = 100;
        }

        $stmt = $conn->prepare("
            INSERT INTO eimbox_platform_tracker (feature_id, platform, script_path, status, priority, progress_percent, issue_notes, dev_response, assigned_to, estimated_deadline)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                script_path = VALUES(script_path),
                status = VALUES(status),
                priority = VALUES(priority),
                progress_percent = VALUES(progress_percent),
                issue_notes = VALUES(issue_notes),
                dev_response = VALUES(dev_response),
                assigned_to = VALUES(assigned_to),
                estimated_deadline = VALUES(estimated_deadline),
                updated_at = NOW()
        ");
        $stmt->bind_param("issssissss", $feature_id, $platform, $script_path, $status, $priority, $progress, $issue_notes, $dev_response, $assigned_to, $deadline);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "প্ল্যাটফর্ম '$platform'-এর তথ্য সফলভাবে আপডেট হয়েছে!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 2.6 Bulk Update All 5 Platforms
    if ($action === 'bulk_update_platforms') {
        $feature_id = intval($_POST['feature_id'] ?? 0);
        $platforms = $_POST['platforms'] ?? [];

        if ($feature_id > 0 && is_array($platforms)) {
            $stmt = $conn->prepare("
                INSERT INTO eimbox_platform_tracker (feature_id, platform, script_path, status, priority, progress_percent, issue_notes, dev_response)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    script_path = VALUES(script_path),
                    status = VALUES(status),
                    priority = VALUES(priority),
                    progress_percent = VALUES(progress_percent),
                    issue_notes = VALUES(issue_notes),
                    dev_response = VALUES(dev_response),
                    updated_at = NOW()
            ");
            foreach ($platforms as $pk => $pdata) {
                $s_path = trim($pdata['script_path'] ?? '');
                $st = trim($pdata['status'] ?? 'Planned');
                $prio = trim($pdata['priority'] ?? 'Medium');
                $pct = intval($pdata['progress_percent'] ?? 0);
                if ($st === 'Completed' && $pct < 100) $pct = 100;
                $inotes = trim($pdata['issue_notes'] ?? '');
                $dresp = trim($pdata['dev_response'] ?? '');

                $stmt->bind_param("isssisss", $feature_id, $pk, $s_path, $st, $prio, $pct, $inotes, $dresp);
                $stmt->execute();
            }
            $stmt->close();
            echo json_encode(['status' => 'success', 'message' => '৫টি প্ল্যাটফর্মের তথ্য একসাথে সংরক্ষিত হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'কোনো প্ল্যাটফর্ম ডাটা পাওয়া যায়নি!']);
        }
        exit;
    }

    // 2.7 Seed Default Data
    if ($action === 'seed_default_data') {
        $sample_features = [
            ['Student', 'Daily Student Attendance', 'Student daily attendance taking via web & mobile app'],
            ['Student', 'Student Admission & Registration', 'New student online/offline application process'],
            ['Exam', 'OMR Sheet Scanner & Evaluator', 'Automated exam paper grading with high-speed camera'],
            ['Accounts', 'Student Fee Collection & Receipt', 'Monthly tuition fees collection with instant SMS alert'],
            ['HR/Payroll', 'Staff Leave & Attendance Management', 'Teacher and staff check-in, leave approval hierarchy']
        ];

        foreach ($sample_features as $item) {
            $m = $item[0]; $f = $item[1]; $d = $item[2];
            $chk = $conn->query("SELECT id FROM eimbox_features_master WHERE feature_name = '$f'");
            if ($chk && $chk->num_rows == 0) {
                $conn->query("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES ('$m', '$f', '$d')");
                $nid = $conn->insert_id;
                foreach (['dashboard', 'console', 'android_lite', 'premium', 'desktop'] as $pk) {
                    $conn->query("INSERT INTO eimbox_platform_tracker (feature_id, platform, status, progress_percent, priority) VALUES ($nid, '$pk', 'Planned', 0, 'Medium')");
                }
            }
        }
        echo json_encode(['status' => 'success', 'message' => 'ডেমো ডাটা সফলভাবে তৈরি হয়েছে!']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'অবৈধ অ্যাকশন!']);
    exit;
}

// ==============================================================
// 3. FRONTEND DATA LOADING & METADATA
// ==============================================================

// Platform Constants
$platforms = [
    'dashboard' => ['title' => 'Dashboard', 'sub' => 'Web Old', 'icon' => 'bi bi-speedometer2', 'color' => 'primary'],
    'console' => ['title' => 'Console', 'sub' => 'Web New', 'icon' => 'bi bi-terminal', 'color' => 'info'],
    'android_lite' => ['title' => 'Android Lite', 'sub' => 'Lite App', 'icon' => 'bi bi-android2', 'color' => 'success'],
    'premium' => ['title' => 'Premium', 'sub' => 'Offline', 'icon' => 'bi bi-phone', 'color' => 'warning'],
    'desktop' => ['title' => 'Desktop', 'sub' => 'Windows', 'icon' => 'bi bi-display', 'color' => 'secondary']
];

$status_badges = [
    'Completed' => 'bg-success',
    'Testing' => 'bg-primary',
    'Ongoing' => 'bg-info text-dark',
    'Need Update' => 'bg-warning text-dark',
    'Issue' => 'bg-danger',
    'Customization' => 'bg-dark',
    'Planned' => 'bg-secondary',
    'Not Implemented' => 'bg-light text-muted border',
    'On Hold' => 'bg-secondary'
];

// Load modules from modulelist
$modules_list = [];
$mq = $conn->query("SELECT id, module_name, descrip, is_public, core FROM modulelist WHERE is_public >= 0 ORDER BY is_public DESC, slno ASC, module_name ASC");
if ($mq && $mq->num_rows > 0) {
    while ($m = $mq->fetch_assoc()) {
        $modules_list[] = $m;
    }
}

// Filter params for GET requests
$f_module = $_GET['module'] ?? 'all';
$f_status = $_GET['status'] ?? 'all';
$f_platform = $_GET['platform'] ?? 'all';
$f_search = trim($_GET['search'] ?? '');
$f_issues = isset($_GET['issues_only']) && $_GET['issues_only'] == '1';

$where_clauses = [];
if ($f_module !== 'all' && !empty($f_module)) {
    $where_clauses[] = "m.module = '" . $conn->real_escape_string($f_module) . "'";
}
if (!empty($f_search)) {
    $clean_s = $conn->real_escape_string($f_search);
    $where_clauses[] = "(m.module LIKE '%$clean_s%' OR m.feature_name LIKE '%$clean_s%' OR m.description LIKE '%$clean_s%' OR t.script_path LIKE '%$clean_s%' OR t.issue_notes LIKE '%$clean_s%')";
}
if ($f_status !== 'all' && !empty($f_status)) {
    $where_clauses[] = "t.status = '" . $conn->real_escape_string($f_status) . "'";
}
if ($f_platform !== 'all' && !empty($f_platform)) {
    $where_clauses[] = "t.platform = '" . $conn->real_escape_string($f_platform) . "'";
}
if ($f_issues) {
    $where_clauses[] = "(t.status = 'Issue' OR (t.issue_notes IS NOT NULL AND TRIM(t.issue_notes) != ''))";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch Features & Platform Records
$sql = "
    SELECT DISTINCT m.id, m.module, m.display_order, m.feature_name, m.description, m.created_at, m.updated_at
    FROM eimbox_features_master m
    LEFT JOIN eimbox_platform_tracker t ON m.id = t.feature_id
    $where_sql
    ORDER BY m.module ASC, m.display_order ASC, m.feature_name ASC
";
$feat_res = $conn->query($sql);
$features = [];
$feature_ids = [];

if ($feat_res && $feat_res->num_rows > 0) {
    while ($row = $feat_res->fetch_assoc()) {
        $feature_ids[] = $row['id'];
        $features[$row['id']] = [
            'master' => $row,
            'platforms' => []
        ];
    }

    if (count($feature_ids) > 0) {
        $id_str = implode(',', $feature_ids);
        $plat_res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id IN ($id_str)");
        if ($plat_res) {
            while ($prow = $plat_res->fetch_assoc()) {
                $features[$prow['feature_id']]['platforms'][$prow['platform']] = $prow;
            }
        }
    }
}

// Summary Statistics
$total_features_count = 0;
$c_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_features_master");
if ($c_res) $total_features_count = intval($c_res->fetch_assoc()['c'] ?? 0);

$total_issues_count = 0;
$i_res = $conn->query("SELECT COUNT(*) as c FROM eimbox_platform_tracker WHERE status = 'Issue' OR (issue_notes IS NOT NULL AND TRIM(issue_notes) != '')");
if ($i_res) $total_issues_count = intval($i_res->fetch_assoc()['c'] ?? 0);

$plat_stats = [];
$ps_res = $conn->query("
    SELECT platform, 
           COUNT(*) as total_count,
           SUM(CASE WHEN status = 'Completed' OR progress_percent = 100 THEN 1 ELSE 0 END) as completed_count
    FROM eimbox_platform_tracker
    GROUP BY platform
");
if ($ps_res) {
    while ($sp = $ps_res->fetch_assoc()) {
        $plat_stats[$sp['platform']] = [
            'total' => intval($sp['total_count']),
            'completed' => intval($sp['completed_count']),
            'percent' => $sp['total_count'] > 0 ? round(($sp['completed_count'] / $sp['total_count']) * 100) : 0
        ];
    }
}

require_once 'header.php';
?>

<div class="container-fluid container-p-y">

    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>EIMBox Multi-Platform Feature Tracker</h4>
            <p class="text-muted small mb-0">Dashboard, Console, Android Lite, Offline Premium এবং Desktop প্ল্যাটফর্মের সেন্ট্রাল ট্র্যাকার</p>
        </div>
        <div class="d-flex gap-2">
            <a href="feature-tracker.php?issues_only=<?= $f_issues ? '0' : '1' ?>" class="btn btn-sm <?= $f_issues ? 'btn-danger' : 'btn-outline-danger' ?>">
                <i class="bi bi-bug-fill me-1"></i> শুধু সমস্যাসমূহ (<?= $total_issues_count ?>)
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                <i class="bi bi-plus-lg me-1"></i> নতুন ফিচার যোগ করুন
            </button>
        </div>
    </div>

    <!-- Platform KPI Cards (Pure Bootstrap 5 Grid & Cards) -->
    <div class="row g-2 mb-3">
        <?php foreach ($platforms as $pk => $pinfo): 
            $st_total = $plat_stats[$pk]['total'] ?? 0;
            $st_comp = $plat_stats[$pk]['completed'] ?? 0;
            $st_pct = $plat_stats[$pk]['percent'] ?? 0;
        ?>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card text-center h-100 shadow-sm border-0">
                    <div class="card-body p-2">
                        <div class="text-<?= $pinfo['color'] ?> fw-semibold small">
                            <i class="<?= $pinfo['icon'] ?> me-1"></i> <?= $pinfo['title'] ?>
                        </div>
                        <div class="fs-5 fw-bold text-dark mt-1"><?= $st_pct ?>%</div>
                        <div class="progress mt-1 mb-1" style="height: 4px;">
                            <div class="progress-bar bg-<?= $pinfo['color'] ?>" style="width: <?= $st_pct ?>%;"></div>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;"><?= $st_comp ?>/<?= $st_total ?> Complete</small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Total Master Features Counter -->
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card text-center h-100 bg-primary text-white shadow-sm border-0">
                <div class="card-body p-2">
                    <div class="small fw-semibold"><i class="bi bi-list-task me-1"></i> All Features</div>
                    <div class="fs-5 fw-bold mt-1"><?= $total_features_count ?></div>
                    <a href="feature-tracker.php" class="text-white-50 text-decoration-none d-block small" style="font-size: 0.7rem;">সকল ফিল্টার রিসেট</a>
                </div>
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
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="ফিচার বা স্ক্রিপ্ট খুঁজুন...">
                    </div>
                </div>

                <!-- Module Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="module" onchange="this.form.submit()">
                        <option value="all">📁 সকল মডিউল</option>
                        <?php foreach ($modules_list as $mod): ?>
                            <option value="<?= htmlspecialchars($mod['module_name']) ?>" <?= $f_module === $mod['module_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '★' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Platform Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="platform" onchange="this.form.submit()">
                        <option value="all">🌐 সকল প্ল্যাটফর্ম</option>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <option value="<?= $pk ?>" <?= $f_platform === $pk ? 'selected' : '' ?>><?= $pinfo['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Select -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="all">⚡ সকল স্ট্যাটাস</option>
                        <?php foreach ($status_badges as $st_key => $st_badge): ?>
                            <option value="<?= $st_key ?>" <?= $f_status === $st_key ? 'selected' : '' ?>><?= $st_key ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action buttons -->
                <div class="col-lg-3 col-md-9 col-6 text-end">
                    <button type="submit" class="btn btn-sm btn-primary me-1"><i class="bi bi-funnel-fill me-1"></i> ফিল্টার</button>
                    <a href="feature-tracker.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> রিসেট</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Features Matrix Table (Pure Bootstrap 5 Table) -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-responsive table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th style="min-width: 200px;">মডিউল ও ফিচার নাম</th>
                        <?php foreach ($platforms as $pk => $pinfo): ?>
                            <th class="text-center" >
                                <div class="text-<?= $pinfo['color'] ?> fw-bold">
                                    <i class="<?= $pinfo['icon'] ?>"></i> <?= $pinfo['title'] ?>
                                </div>
                                <span class="text-muted font-monospace" style="font-size: 0.65rem;"><?= $pinfo['sub'] ?></span>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-center" style="width: 100px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($features)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <h5>কোনো ফিচার রেকর্ড পাওয়া যায়নি!</h5>
                                <p class="small text-muted mb-3">নতুন ফিচার যোগ করতে উপরের বাটনে ক্লিক করুন অথবা ডেমো ডাটা লোড করুন।</p>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="seedDemoData()">
                                    <i class="bi bi-stars me-1"></i> ডেমো ডাটা লোড করুন
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($features as $f_item): 
                            $m = $f_item['master'];
                            $p_data = $f_item['platforms'];
                            $m_json = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
                            $all_p_json = htmlspecialchars(json_encode($p_data), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <!-- ID -->
                                <td class="text-center text-muted fw-bold"><?= $m['id'] ?></td>

                                <!-- Feature Info -->
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1 font-monospace">
                                        <?= htmlspecialchars($m['module']) ?>
                                    </span>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($m['feature_name']) ?></div>
                                    <?php if (!empty($m['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($m['description']) ?>">
                                            <?= htmlspecialchars($m['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- 5 Platform Columns -->
                                <?php foreach ($platforms as $pk => $pinfo): 
                                    $pt = $p_data[$pk] ?? [
                                        'feature_id' => $m['id'],
                                        'platform' => $pk,
                                        'status' => 'Planned',
                                        'progress_percent' => 0,
                                        'script_path' => '',
                                        'issue_notes' => '',
                                        'dev_response' => '',
                                        'priority' => 'Medium'
                                    ];
                                    $st = $pt['status'] ?? 'Planned';
                                    $pct = intval($pt['progress_percent'] ?? 0);
                                    $badge_class = $status_badges[$st] ?? 'bg-secondary';
                                    $bar_class = $pct >= 100 ? 'bg-success' : ($pct >= 60 ? 'bg-info' : ($pct >= 25 ? 'bg-primary' : 'bg-warning'));
                                    $pt_json = htmlspecialchars(json_encode($pt), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <td class="text-center p-2" style="cursor: pointer;" onclick='openPlatformEditModal(<?= $m['id'] ?>, "<?= $pk ?>", "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>", <?= $pt_json ?>)' title="ক্লিক করে এডিট করুন">
                                        <!-- Progress Bar & Text -->
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge <?= $badge_class ?> p-1" style="font-size: 0.65rem;"><?= htmlspecialchars($st) ?></span>
                                            <span class="fw-bold small"><?= $pct ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar <?= $bar_class ?>" style="width: <?= $pct ?>%;"></div>
                                        </div>

                                        <!-- Script or Issue Preview -->
                                        <?php if (!empty($pt['issue_notes'])): ?>
                                            <div class="text-danger small mt-1 text-truncate" style="font-size: 0.68rem;" title="ইস্যু: <?= htmlspecialchars($pt['issue_notes']) ?>">
                                                <i class="bi bi-bug-fill"></i> <?= htmlspecialchars($pt['issue_notes']) ?>
                                            </div>
                                        <?php elseif (!empty($pt['script_path'])): ?>
                                            <div class="text-muted font-monospace mt-1 text-truncate" style="font-size: 0.65rem;" title="<?= htmlspecialchars($pt['script_path']) ?>">
                                                <?= htmlspecialchars($pt['script_path']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Edit Master -->
                                        <button type="button" class="btn btn-outline-primary" title="মূল ফিচার এডিট" onclick='openMasterEditModal(<?= $m_json ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <!-- Bulk 5 Platforms -->
                                        <button type="button" class="btn btn-outline-info" title="৫টি প্ল্যাটফর্ম একসাথে এডিট" onclick='openBulkModal(<?= $m['id'] ?>, "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>", <?= $all_p_json ?>)'>
                                            <i class="bi bi-sliders"></i>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-outline-danger" title="ফিচার মুছুন" onclick='deleteFeature(<?= $m['id'] ?>, "<?= htmlspecialchars($m['feature_name'], ENT_QUOTES) ?>")'>
                                            <i class="bi bi-trash3"></i>
                                        </button>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন মাস্টার ফিচার যোগ করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFeatureForm" onsubmit="submitAddFeature(event)">
                <input type="hidden" name="action" value="add_feature">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মডিউল <span class="text-danger">*</span></label>
                        <select class="form-select" name="module" required>
                            <option value="">-- মডিউল নির্বাচন করুন --</option>
                            <?php foreach ($modules_list as $mod): ?>
                                <option value="<?= htmlspecialchars($mod['module_name']) ?>">
                                    <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফিচার নাম <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="feature_name" placeholder="ফিচারের নাম লিখুন..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">সংক্ষিপ্ত বিবরণ</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="ফিচারের কাজের বিবরণ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary" id="btnAddSubmit">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.2 Edit Master Feature Modal -->
<div class="modal fade" id="editMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>মাস্টার ফিচার এডিট</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMasterForm" onsubmit="submitEditMaster(event)">
                <input type="hidden" name="action" value="edit_master_feature">
                <input type="hidden" name="id" id="edit_m_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মডিউল <span class="text-danger">*</span></label>
                        <select class="form-select" name="module" id="edit_m_module" required>
                            <?php foreach ($modules_list as $mod): ?>
                                <option value="<?= htmlspecialchars($mod['module_name']) ?>">
                                    <?= htmlspecialchars($mod['module_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফিচার নাম <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="feature_name" id="edit_m_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">সংক্ষিপ্ত বিবরণ</label>
                        <textarea class="form-control" name="description" id="edit_m_desc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary" id="btnEditSubmit">আপডেট করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.3 Edit Single Platform Modal -->
<div class="modal fade" id="editPlatformModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="plat_modal_title">
                        <i class="bi bi-phone text-primary me-2"></i>প্ল্যাটফর্ম কনফিগারেশন
                    </h5>
                    <span class="text-muted small" id="plat_modal_sub">Feature: ...</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPlatformForm" onsubmit="submitEditPlatform(event)">
                <input type="hidden" name="action" value="update_platform_status">
                <input type="hidden" name="feature_id" id="pe_feature_id">
                <input type="hidden" name="platform" id="pe_platform">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Script Path -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">স্ক্রিপ্ট / ফাইল পাথ</label>
                            <input type="text" class="form-control font-monospace" name="script_path" id="pe_script_path" placeholder="যেমন: attendance.php বা lib/screens/attnd.dart">
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">বর্তমান স্ট্যাটাস <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="pe_status" onchange="onStatusChange(this.value)">
                                <?php foreach ($status_badges as $st_name => $st_bg): ?>
                                    <option value="<?= $st_name ?>"><?= $st_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Progress Slider -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex justify-content-between">
                                <span>কাজের অগ্রগতি (%)</span>
                                <span class="text-primary fw-bold" id="pe_progress_display">0%</span>
                            </label>
                            <input type="range" class="form-range" name="progress_percent" id="pe_progress" min="0" max="100" step="5" oninput="document.getElementById('pe_progress_display').innerText = this.value + '%'">
                        </div>

                        <!-- Priority -->
                        <div class="col-md-3 col-6">
                            <label class="form-label fw-semibold">অগ্রাধিকার</label>
                            <select class="form-select" name="priority" id="pe_priority">
                                <option value="Critical">Critical</option>
                                <option value="High">High</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <!-- Deadline -->
                        <div class="col-md-3 col-6">
                            <label class="form-label fw-semibold">টার্গেট ডেট</label>
                            <input type="date" class="form-control" name="estimated_deadline" id="pe_deadline">
                        </div>

                        <!-- Issue Notes -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-danger">
                                <i class="bi bi-bug-fill me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ
                            </label>
                            <textarea class="form-control" name="issue_notes" id="pe_issue_notes" rows="4" placeholder="সমস্যার বিবরণ বা পেন্ডিং কাজ..."></textarea>
                        </div>

                        <!-- Developer Response -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-success">
                                <i class="bi bi-reply-fill me-1"></i>ডেভেলপার রেসপন্স বা সমাধান
                            </label>
                            <textarea class="form-control" name="dev_response" id="pe_dev_response" rows="4" placeholder="ডেভেলপার নোট বা সমাধান..."></textarea>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">দায়িত্বপ্রাপ্ত ডেভেলপার</label>
                            <input type="text" class="form-control" name="assigned_to" id="pe_assigned_to" placeholder="নাম / টিম">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary" id="btnPlatformSubmit">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.4 Bulk Edit All 5 Platforms Modal -->
<div class="modal fade" id="bulkPlatformsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="bi bi-sliders text-primary me-2"></i>একসাথে ৫টি প্ল্যাটফর্ম কনফিগারেশন</h5>
                    <span class="text-muted small" id="bulk_modal_title">Feature: ...</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkPlatformsForm" onsubmit="submitBulkPlatforms(event)">
                <input type="hidden" name="action" value="bulk_update_platforms">
                <input type="hidden" name="feature_id" id="bulk_feature_id">
                <div class="modal-body p-3">
                    <div class="row g-3" id="bulk_container">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary" id="btnBulkSubmit">সব সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 5. JAVASCRIPT & AJAX (PURE BOOTSTRAP 5 DOM)                   -->
<!-- ============================================================== -->
<script>
    const platformsInfo = <?= json_encode($platforms) ?>;
    const statusBadgesList = <?= json_encode($status_badges) ?>;

    // Helper: Open Modal via Bootstrap 5
    function showModal(id) {
        const el = document.getElementById(id);
        if (el) {
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
        }
    }

    function hideModal(id) {
        const el = document.getElementById(id);
        if (el) {
            const modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        }
    }

    // Add Feature
    function submitAddFeature(e) {
        e.preventDefault();
        const form = document.getElementById('addFeatureForm');
        const formData = new FormData(form);
        const btn = document.getElementById('btnAddSubmit');
        btn.disabled = true;

        fetch('feature-tracker.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res.status === 'success') {
                hideModal('addFeatureModal');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('সফল!', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                alert(res.message || 'ত্রুটি হয়েছে!');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('সার্ভার রিকোয়েস্ট ব্যর্থ হয়েছে!');
        });
    }

    // Master Feature Edit Modal
    function openMasterEditModal(masterData) {
        document.getElementById('edit_m_id').value = masterData.id;
        document.getElementById('edit_m_module').value = masterData.module || '';
        document.getElementById('edit_m_name').value = masterData.feature_name || '';
        document.getElementById('edit_m_desc').value = masterData.description || '';
        showModal('editMasterModal');
    }

    function submitEditMaster(e) {
        e.preventDefault();
        const form = document.getElementById('editMasterForm');
        const formData = new FormData(form);
        const btn = document.getElementById('btnEditSubmit');
        btn.disabled = true;

        fetch('feature-tracker.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res.status === 'success') {
                hideModal('editMasterModal');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('আপডেট হয়েছে!', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                alert(res.message || 'আপডেট ব্যর্থ!');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('সার্ভার রিকোয়েস্ট ব্যর্থ!');
        });
    }

    // Delete Feature
    function deleteFeature(id, name) {
        const doDelete = () => {
            const formData = new FormData();
            formData.append('action', 'delete_feature');
            formData.append('id', id);

            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('মুছে ফেলা হয়েছে!', res.message, 'success').then(() => location.reload());
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                } else {
                    alert(res.message || 'মুছে ফেলতে ব্যর্থ!');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `"${name}" মুছে ফেলবেন?`,
                text: "এই ফিচারের ৫টি প্ল্যাটফর্মের সকল তথ্য মুছে যাবে!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "হ্যাঁ, মুছুন",
                cancelButtonText: "বাতিল"
            }).then(r => {
                if (r.isConfirmed) doDelete();
            });
        } else {
            if (confirm(`আপনি কি "${name}" মুছে ফেলতে চান?`)) {
                doDelete();
            }
        }
    }

    // Platform Single Edit Modal
    function openPlatformEditModal(featureId, platformKey, featureName, platformData = null) {
        const pinfo = platformsInfo[platformKey] || { title: platformKey };
        document.getElementById('pe_feature_id').value = featureId;
        document.getElementById('pe_platform').value = platformKey;
        document.getElementById('plat_modal_title').innerHTML = `<i class="${pinfo.icon} text-${pinfo.color} me-2"></i> ${pinfo.title} (${pinfo.sub}) প্ল্যাটফর্ম`;
        document.getElementById('plat_modal_sub').innerText = `ফিচার: ${featureName}`;

        function populate(pt) {
            document.getElementById('pe_script_path').value = pt.script_path || '';
            document.getElementById('pe_status').value = pt.status || 'Planned';
            document.getElementById('pe_progress').value = pt.progress_percent || 0;
            document.getElementById('pe_progress_display').innerText = (pt.progress_percent || 0) + '%';
            document.getElementById('pe_priority').value = pt.priority || 'Medium';
            document.getElementById('pe_deadline').value = pt.estimated_deadline || '';
            document.getElementById('pe_issue_notes').value = pt.issue_notes || '';
            document.getElementById('pe_dev_response').value = pt.dev_response || '';
            document.getElementById('pe_assigned_to').value = pt.assigned_to || '';
            showModal('editPlatformModal');
        }

        if (platformData) {
            populate(platformData);
        } else {
            const fd = new FormData();
            fd.append('action', 'get_platform_details');
            fd.append('feature_id', featureId);
            fd.append('platform', platformKey);

            fetch('feature-tracker.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                populate(res.data || {});
            })
            .catch(() => {
                populate({ feature_id: featureId, platform: platformKey });
            });
        }
    }

    function onStatusChange(st) {
        if (st === 'Completed') {
            document.getElementById('pe_progress').value = 100;
            document.getElementById('pe_progress_display').innerText = '100%';
        }
    }

    function submitEditPlatform(e) {
        e.preventDefault();
        const form = document.getElementById('editPlatformForm');
        const formData = new FormData(form);
        const btn = document.getElementById('btnPlatformSubmit');
        btn.disabled = true;

        fetch('feature-tracker.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res.status === 'success') {
                hideModal('editPlatformModal');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('সংরক্ষিত!', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                alert(res.message || 'সংরক্ষণ ব্যর্থ!');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('সার্ভার রিকোয়েস্ট ব্যর্থ!');
        });
    }

    // Bulk All 5 Platforms Edit Modal
    function openBulkModal(featureId, featureName, platformsData) {
        document.getElementById('bulk_feature_id').value = featureId;
        document.getElementById('bulk_modal_title').innerText = `ফিচার: ${featureName}`;

        const container = document.getElementById('bulk_container');
        let html = '';

        for (const [pk, pinfo] of Object.entries(platformsInfo)) {
            const pt = platformsData[pk] || {};
            html += `
                <div class="col-lg-6">
                    <div class="card border h-100 p-3 bg-light">
                        <strong class="text-${pinfo.color} mb-2 d-block">
                            <i class="${pinfo.icon} me-1"></i> ${pinfo.title} (${pinfo.sub})
                        </strong>
                        <div class="row g-2">
                            <div class="col-8">
                                <label class="small text-muted mb-1">স্ক্রিপ্ট পাথ</label>
                                <input type="text" class="form-control form-control-sm font-monospace" name="platforms[${pk}][script_path]" value="${escapeHtml(pt.script_path || '')}" placeholder="file.php">
                            </div>
                            <div class="col-4">
                                <label class="small text-muted mb-1">স্ট্যাটাস</label>
                                <select class="form-select form-select-sm" name="platforms[${pk}][status]">
                                    ${Object.keys(statusBadgesList).map(st => `<option value="${st}" ${pt.status === st ? 'selected' : ''}>${st}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">অগ্রগতি (%)</label>
                                <input type="number" class="form-control form-control-sm" name="platforms[${pk}][progress_percent]" min="0" max="100" value="${pt.progress_percent || 0}">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">অগ্রাধিকার</label>
                                <select class="form-select form-select-sm" name="platforms[${pk}][priority]">
                                    <option value="Critical" ${pt.priority === 'Critical' ? 'selected' : ''}>Critical</option>
                                    <option value="High" ${pt.priority === 'High' ? 'selected' : ''}>High</option>
                                    <option value="Medium" ${!pt.priority || pt.priority === 'Medium' ? 'selected' : ''}>Medium</option>
                                    <option value="Low" ${pt.priority === 'Low' ? 'selected' : ''}>Low</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="small text-danger mb-1">ইস্যু / পেন্ডিং কাজ</label>
                                <textarea class="form-control form-control-sm" name="platforms[${pk}][issue_notes]" rows="2" placeholder="সমস্যার বিবরণ...">${escapeHtml(pt.issue_notes || '')}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="small text-success mb-1">সমাধান / রেসপন্স</label>
                                <textarea class="form-control form-control-sm" name="platforms[${pk}][dev_response]" rows="2" placeholder="ডেভেলপারের নোট...">${escapeHtml(pt.dev_response || '')}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
        showModal('bulkPlatformsModal');
    }

    function submitBulkPlatforms(e) {
        e.preventDefault();
        const form = document.getElementById('bulkPlatformsForm');
        const formData = new FormData(form);
        const btn = document.getElementById('btnBulkSubmit');
        btn.disabled = true;

        fetch('feature-tracker.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res.status === 'success') {
                hideModal('bulkPlatformsModal');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('সংরক্ষিত!', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            } else {
                alert(res.message || 'সংরক্ষণ ব্যর্থ!');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('সার্ভার রিকোয়েস্ট ব্যর্থ!');
        });
    }

    // Seed Demo Data
    function seedDemoData() {
        const fd = new FormData();
        fd.append('action', 'seed_default_data');

        fetch('feature-tracker.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('সফল!', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
</script>

<?php require_once 'footer.php'; ?>
