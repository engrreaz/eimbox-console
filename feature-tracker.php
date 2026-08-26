<?php
/**
 * EIMBox Feature & Platform Status Matrix / Tracker
 * File: feature-tracker.php
 * Author: EIMBox Team
 * Purpose: Track features, issues, developer responses, and platform status across
 *          Dashboard, Console, Android Lite, Android Premium, Desktop.
 */

// -------------------------------------------------------------
// 1. AJAX Backend Handlers (Executed before headers if POST/AJAX)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Include core DB if not already initialized
    if (!isset($conn)) {
        require_once 'core/init.php';
    }

    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';
    $user_name = $_SESSION['user'] ?? $_SESSION['user_name'] ?? 'Admin';

    // Auto-create table if missing
    $table_check = $conn->query("SHOW TABLES LIKE 'eimbox_features'");
    if ($table_check->num_rows == 0) {
        $conn->query("
            CREATE TABLE IF NOT EXISTS `eimbox_features` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `module` VARCHAR(100) NOT NULL,
              `feature` VARCHAR(150) NOT NULL,
              `platform` ENUM('Dashboard', 'Console', 'Android Lite', 'Android Premium', 'Desktop', 'API', 'General') NOT NULL DEFAULT 'Dashboard',
              `script` VARCHAR(255) DEFAULT NULL,
              `topic` VARCHAR(255) DEFAULT NULL,
              `issues` TEXT DEFAULT NULL,
              `response` TEXT DEFAULT NULL,
              `status` ENUM('Open', 'Pending', 'Ongoing', 'Testing', 'Completed', 'Closed', 'On Hold') NOT NULL DEFAULT 'Open',
              `priority` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
              `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
              `assigned_to` VARCHAR(100) DEFAULT NULL,
              `created_by` VARCHAR(100) DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_platform` (`platform`),
              KEY `idx_module` (`module`),
              KEY `idx_status` (`status`),
              KEY `idx_priority` (`priority`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // 1.1 ADD NEW FEATURE
    if ($action === 'add_feature') {
        $module = trim($_POST['module'] ?? '');
        $feature = trim($_POST['feature'] ?? '');
        $platform = trim($_POST['platform'] ?? 'Dashboard');
        $script = trim($_POST['script'] ?? '');
        $topic = trim($_POST['topic'] ?? '');
        $issues = trim($_POST['issues'] ?? '');
        $response = trim($_POST['response'] ?? '');
        $status = trim($_POST['status'] ?? 'Open');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $progress = intval($_POST['progress_percent'] ?? 0);
        $assigned_to = trim($_POST['assigned_to'] ?? '');

        if (empty($module) || empty($feature)) {
            echo json_encode(['status' => 'error', 'message' => 'Module এবং Feature নাম অবশ্যই পূরণ করতে হবে!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO eimbox_features (module, feature, platform, script, topic, issues, response, status, priority, progress_percent, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssiss", $module, $feature, $platform, $script, $topic, $issues, $response, $status, $priority, $progress, $assigned_to, $user_name);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'নতুন ফিচার সফলভাবে যোগ করা হয়েছে!', 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ডাটাবেজে সেভ করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.2 EDIT FEATURE
    if ($action === 'edit_feature') {
        $id = intval($_POST['id'] ?? 0);
        $module = trim($_POST['module'] ?? '');
        $feature = trim($_POST['feature'] ?? '');
        $platform = trim($_POST['platform'] ?? 'Dashboard');
        $script = trim($_POST['script'] ?? '');
        $topic = trim($_POST['topic'] ?? '');
        $issues = trim($_POST['issues'] ?? '');
        $response = trim($_POST['response'] ?? '');
        $status = trim($_POST['status'] ?? 'Open');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $progress = intval($_POST['progress_percent'] ?? 0);
        $assigned_to = trim($_POST['assigned_to'] ?? '');

        if ($id <= 0 || empty($module) || empty($feature)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ আইডি অথবা ফিল্ড খালি আছে!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE eimbox_features SET module=?, feature=?, platform=?, script=?, topic=?, issues=?, response=?, status=?, priority=?, progress_percent=?, assigned_to=? WHERE id=?");
        $stmt->bind_param("sssssssssisi", $module, $feature, $platform, $script, $topic, $issues, $response, $status, $priority, $progress, $assigned_to, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'ফিচার তথ্য সফলভাবে আপডেট হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট করতে ব্যর্থ হয়েছে: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.3 QUICK STATUS UPDATE
    if ($action === 'quick_update_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        
        $valid_statuses = ['Open', 'Pending', 'Ongoing', 'Testing', 'Completed', 'Closed', 'On Hold'];
        if ($id <= 0 || !in_array($status, $valid_statuses)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ স্ট্যাটাস ভ্যালু!']);
            exit;
        }

        // Auto calculate progress if status is Completed
        $progress_sql = "";
        if ($status === 'Completed') {
            $progress_sql = ", progress_percent = 100";
        } elseif ($status === 'Open') {
            $progress_sql = ", progress_percent = 0";
        }

        $stmt = $conn->prepare("UPDATE eimbox_features SET status = ? $progress_sql WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "স্ট্যাটাস পরিবর্তন করে '$status' করা হয়েছে!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'স্ট্যাটাস আপডেট ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.4 QUICK UPDATE DEVELOPER RESPONSE & ISSUES
    if ($action === 'quick_update_response') {
        $id = intval($_POST['id'] ?? 0);
        $issues = trim($_POST['issues'] ?? '');
        $response = trim($_POST['response'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $progress = intval($_POST['progress_percent'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ রেকর্ড আইডি!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE eimbox_features SET issues=?, response=?, status=?, progress_percent=? WHERE id=?");
        $stmt->bind_param("sssii", $issues, $response, $status, $progress, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'রেসপন্স ও সমস্যা বিবরণ আপডেট সম্পন্ন হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.5 DUPLICATE TO ANOTHER PLATFORM
    if ($action === 'duplicate_platform') {
        $id = intval($_POST['id'] ?? 0);
        $target_platform = trim($_POST['target_platform'] ?? '');

        if ($id <= 0 || empty($target_platform)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ প্ল্যাটফর্ম বা আইডি!']);
            exit;
        }

        $orig = $conn->query("SELECT * FROM eimbox_features WHERE id = $id")->fetch_assoc();
        if (!$orig) {
            echo json_encode(['status' => 'error', 'message' => 'মূল রেকর্ড পাওয়া যায়নি!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO eimbox_features (module, feature, platform, script, topic, issues, response, status, priority, progress_percent, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open', ?, 0, ?, ?)");
        $empty_response = '';
        $stmt->bind_param("ssssssssss", $orig['module'], $orig['feature'], $target_platform, $orig['script'], $orig['topic'], $orig['issues'], $empty_response, $orig['priority'], $orig['assigned_to'], $user_name);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "ফিচারটি '$target_platform' প্ল্যাটফর্মে সফলভাবে ডুপ্লিকেট করা হয়েছে!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ডুপ্লিকেট করতে ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.6 DELETE FEATURE
    if ($action === 'delete_feature') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ আইডি!']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM eimbox_features WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'ফিচার রেকর্ড সফলভাবে মুছে ফেলা হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'রেকর্ড মুছতে ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.7 SEED DEFAULT DATA IF TABLE IS EMPTY
    if ($action === 'seed_default_data') {
        $check = $conn->query("SELECT COUNT(*) as cnt FROM eimbox_features")->fetch_assoc();
        if ($check['cnt'] == 0) {
            $samples = [
                ['Attendance', 'Daily Student Attendance', 'Dashboard', 'attendance-register.php', 'Fast Batch Attendance', 'UI becomes slow when class size exceeds 120 students.', 'Implemented pagination and bulk ajax update.', 'Testing', 'High', 85, 'Reaz'],
                ['Attendance', 'Daily Student Attendance', 'Android Lite', 'lib/screens/attendance_quick.dart', 'Offline Sync & QR Scan', 'Local SQLite cache sync conflict when reconnected to network.', 'Pending review on background sync queue.', 'Ongoing', 'Critical', 60, 'Reaz'],
                ['Attendance', 'Teacher Biometric Log', 'Console', 'api/biometric_sync.php', 'ZKTeco Device Webhook Integration', 'Device time offset causes wrong punch log in peak hours.', 'Added server-side timestamp validation buffer.', 'Completed', 'High', 100, 'Dev Team'],
                ['Accounts', 'Student Fee Collection', 'Dashboard', 'payments-collection.php', 'bKash / PGW & Cash Receipt', 'Print receipt layout alignment issue on thermal POS printers.', 'Adjusted 80mm thermal printer CSS styles.', 'Completed', 'High', 100, 'Dev Team'],
                ['Accounts', 'Daily Collection Summary', 'Android Premium', 'lib/screens/finance_dashboard.dart', 'Visual Revenue Charts', 'Charts need weekly and monthly trend toggle.', 'Integrated syncfusion fl_chart component.', 'Testing', 'Medium', 90, 'Dev Team'],
                ['Exam & Result', 'OMR Sheet Processor', 'Desktop', 'omr-processor.exe / omr-mapping.php', 'Camera & Flatbed Scanner OMR Sync', 'Image contrast threshold adjustment needed for faint pencil marks.', 'Added automated histogram equalization algorithm.', 'Testing', 'Critical', 90, 'Reaz'],
                ['Exam & Result', 'Mark Entry & Tabulation', 'Dashboard', 'mark-entry.php', 'Grade Sheet & GPA Calculator', 'Merge marks formula needs support for 4th subject optional exemptions.', 'Updated core calculation engine in result-processor.php.', 'Ongoing', 'High', 70, 'Dev Team'],
                ['Analytics', 'Subject-wise Performance Report', 'Dashboard', 'analytics/get_detailed_subject_report.php', 'Comparative Section Analytics', 'Filter by shift and section needs instant AJAX refresh.', 'Added AJAX fetch listener and cached queries.', 'Completed', 'Medium', 100, 'Reaz'],
                ['Analytics', 'Executive Summary Dashboard', 'Android Lite', 'lib/screens/principal_summary.dart', 'Principal KPI Cards', 'Needs push notifications for daily attendance & fee alerts.', 'FCM setup pending backend trigger cron.', 'Pending', 'Medium', 40, 'Dev Team'],
                ['Communication', 'SMS & Push Notification Engine', 'Console', 'sms-gateway.php', 'Bulk Masking SMS Delivery', 'Failed SMS queue needs auto-retry mechanism with exponential backoff.', 'Cron job added to re-attempt failed SMS twice.', 'Completed', 'High', 100, 'Reaz']
            ];

            $stmt = $conn->prepare("INSERT INTO eimbox_features (module, feature, platform, script, topic, issues, response, status, priority, progress_percent, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'System')");
            foreach ($samples as $s) {
                $stmt->bind_param("sssssssssis", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10]);
                $stmt->execute();
            }
            $stmt->close();
            echo json_encode(['status' => 'success', 'message' => 'ডেমো ডাটা সফলভাবে সেটআপ করা হয়েছে!']);
        } else {
            echo json_encode(['status' => 'info', 'message' => 'টেবিলে ইতিমধ্যে ডাটা বিদ্যমান আছে।']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action request!']);
    exit;
}

// -------------------------------------------------------------
// 2. Main Page Render (HTML / PHP UI)
// -------------------------------------------------------------
require_once 'header.php';

// Check / Create table if missing
$table_exists = false;
$table_check = $conn->query("SHOW TABLES LIKE 'eimbox_features'");
if ($table_check && $table_check->num_rows > 0) {
    $table_exists = true;
} else {
    // Auto create table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `eimbox_features` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `module` VARCHAR(100) NOT NULL,
          `feature` VARCHAR(150) NOT NULL,
          `platform` ENUM('Dashboard', 'Console', 'Android Lite', 'Android Premium', 'Desktop', 'API', 'General') NOT NULL DEFAULT 'Dashboard',
          `script` VARCHAR(255) DEFAULT NULL,
          `topic` VARCHAR(255) DEFAULT NULL,
          `issues` TEXT DEFAULT NULL,
          `response` TEXT DEFAULT NULL,
          `status` ENUM('Open', 'Pending', 'Ongoing', 'Testing', 'Completed', 'Closed', 'On Hold') NOT NULL DEFAULT 'Open',
          `priority` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
          `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
          `assigned_to` VARCHAR(100) DEFAULT NULL,
          `created_by` VARCHAR(100) DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_platform` (`platform`),
          KEY `idx_module` (`module`),
          KEY `idx_status` (`status`),
          KEY `idx_priority` (`priority`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $table_exists = true;
}

// Get filter values from GET parameters
$filter_platform = $_GET['platform'] ?? 'all';
$filter_module   = $_GET['module'] ?? 'all';
$filter_status   = $_GET['status'] ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$filter_search   = trim($_GET['search'] ?? '');

// Build Query
$where_clauses = [];
if ($filter_platform !== 'all' && !empty($filter_platform)) {
    $clean_platform = $conn->real_escape_string($filter_platform);
    $where_clauses[] = "platform = '$clean_platform'";
}
if ($filter_module !== 'all' && !empty($filter_module)) {
    $clean_module = $conn->real_escape_string($filter_module);
    $where_clauses[] = "module = '$clean_module'";
}
if ($filter_status !== 'all' && !empty($filter_status)) {
    $clean_status = $conn->real_escape_string($filter_status);
    $where_clauses[] = "status = '$clean_status'";
}
if ($filter_priority !== 'all' && !empty($filter_priority)) {
    $clean_priority = $conn->real_escape_string($filter_priority);
    $where_clauses[] = "priority = '$clean_priority'";
}
if (!empty($filter_search)) {
    $clean_search = $conn->real_escape_string($filter_search);
    $where_clauses[] = "(feature LIKE '%$clean_search%' OR module LIKE '%$clean_search%' OR topic LIKE '%$clean_search%' OR script LIKE '%$clean_search%' OR issues LIKE '%$clean_search%' OR response LIKE '%$clean_search%')";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch Filtered Features
$features_query = "SELECT * FROM eimbox_features $where_sql ORDER BY FIELD(priority, 'Critical', 'High', 'Medium', 'Low'), FIELD(status, 'Open', 'Pending', 'Ongoing', 'Testing', 'On Hold', 'Completed', 'Closed'), module ASC, feature ASC";
$features_res = $conn->query($features_query);
$features = [];
if ($features_res) {
    while ($row = $features_res->fetch_assoc()) {
        $features[] = $row;
    }
}

// Fetch Global Statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'Testing' THEN 1 ELSE 0 END) as testing,
    SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold,
    SUM(CASE WHEN priority = 'Critical' AND status NOT IN ('Completed', 'Closed') THEN 1 ELSE 0 END) as critical_issues,
    SUM(CASE WHEN platform = 'Dashboard' THEN 1 ELSE 0 END) as p_dashboard,
    SUM(CASE WHEN platform = 'Console' THEN 1 ELSE 0 END) as p_console,
    SUM(CASE WHEN platform = 'Android Lite' THEN 1 ELSE 0 END) as p_android_lite,
    SUM(CASE WHEN platform = 'Android Premium' THEN 1 ELSE 0 END) as p_android_premium,
    SUM(CASE WHEN platform = 'Desktop' THEN 1 ELSE 0 END) as p_desktop
    FROM eimbox_features";
$stats_res = $conn->query($stats_query);
$stats = $stats_res ? $stats_res->fetch_assoc() : [
    'total' => 0, 'completed' => 0, 'testing' => 0, 'ongoing' => 0, 'pending' => 0, 'open' => 0, 'on_hold' => 0,
    'critical_issues' => 0, 'p_dashboard' => 0, 'p_console' => 0, 'p_android_lite' => 0, 'p_android_premium' => 0, 'p_desktop' => 0
];

// Fetch Distinct Modules for Dropdown
$modules_list = [];
$mod_res = $conn->query("SELECT DISTINCT module FROM eimbox_features ORDER BY module ASC");
if ($mod_res) {
    while ($m = $mod_res->fetch_assoc()) {
        if (!empty($m['module'])) $modules_list[] = $m['module'];
    }
}

// Available Platforms
$platforms = [
    'Dashboard' => ['icon' => 'ri-dashboard-line', 'badge' => 'bg-label-primary', 'color' => '#696cff'],
    'Console' => ['icon' => 'ri-terminal-box-line', 'badge' => 'bg-label-info', 'color' => '#03c3ec'],
    'Android Lite' => ['icon' => 'ri-android-line', 'badge' => 'bg-label-success', 'color' => '#71dd37'],
    'Android Premium' => ['icon' => 'ri-smartphone-line', 'badge' => 'bg-label-warning', 'color' => '#ffab00'],
    'Desktop' => ['icon' => 'ri-computer-line', 'badge' => 'bg-label-secondary', 'color' => '#8592a3']
];

// Status Colors & Badges
$status_meta = [
    'Open' => ['badge' => 'bg-danger text-white', 'icon' => 'ri-error-warning-line', 'color' => '#ff3e1d'],
    'Pending' => ['badge' => 'bg-warning text-dark', 'icon' => 'ri-time-line', 'color' => '#ffab00'],
    'Ongoing' => ['badge' => 'bg-info text-white', 'icon' => 'ri-loader-4-line', 'color' => '#03c3ec'],
    'Testing' => ['badge' => 'bg-primary text-white', 'icon' => 'ri-test-tube-line', 'color' => '#696cff'],
    'Completed' => ['badge' => 'bg-success text-white', 'icon' => 'ri-checkbox-circle-line', 'color' => '#71dd37'],
    'Closed' => ['badge' => 'bg-secondary text-white', 'icon' => 'ri-close-circle-line', 'color' => '#8592a3'],
    'On Hold' => ['badge' => 'bg-dark text-white', 'icon' => 'ri-pause-circle-line', 'color' => '#233446']
];

// Priority Colors
$priority_meta = [
    'Critical' => ['badge' => 'badge bg-danger', 'icon' => 'ri-alarm-warning-fill'],
    'High' => ['badge' => 'badge bg-warning text-dark', 'icon' => 'ri-arrow-up-circle-fill'],
    'Medium' => ['badge' => 'badge bg-info', 'icon' => 'ri-subtract-fill'],
    'Low' => ['badge' => 'badge bg-label-secondary', 'icon' => 'ri-arrow-down-circle-line']
];

$completion_rate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0;
?>

<!-- Custom CSS for Modern Material Aesthetic -->
<style>
    .feature-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .platform-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    .code-script {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.75rem;
        background: #f4f5f7;
        padding: 2px 6px;
        border-radius: 4px;
        color: #e83e8c;
        border: 1px solid #e1e4e8;
    }
    .status-select-badge {
        cursor: pointer;
        border: none;
        outline: none;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 20px;
        transition: all 0.2s;
    }
    .status-select-badge:focus {
        box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.25);
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .filter-bar {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(67, 89, 113, 0.06);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.02) !important;
    }
    .modal-content {
        border-radius: 16px;
    }
    .progress-compact {
        height: 6px;
        border-radius: 10px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="ri-dashboard-2-line text-primary fs-3"></i>
                EIMBox Feature & Platform Status Matrix
            </h4>
            <p class="text-muted mb-0">
                Dashboard, Console, Android Lite, Android Premium ও Desktop প্ল্যাটফর্মের সমস্ত ফিচারের সামগ্রিক ট্র্যাকিং ও অবস্থা।
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($stats['total'] == 0): ?>
                <button type="button" class="btn btn-outline-warning" id="btnSeedDemo">
                    <i class="ri-magic-line me-1"></i> ডেমো ডাটা লোড করুন
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="ri-printer-line me-1"></i> প্রিন্ট রিপোর্ট
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                <i class="ri-add-circle-line me-1"></i> নতুন ফিচার যোগ করুন
            </button>
        </div>
    </div>

    <!-- KPI Summary Overview Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Features -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">মোট ফিচার</span>
                            <h3 class="fw-bold my-1 text-primary"><?= number_format($stats['total']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ri-apps-2-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <span class="text-success fw-bold"><?= $completion_rate ?>%</span> সম্পন্ন
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Status -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Completed</span>
                            <h3 class="fw-bold my-1 text-success"><?= number_format($stats['completed']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success"><i class="ri-checkbox-circle-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        রেডি ফর প্রোডাকশন
                    </div>
                </div>
            </div>
        </div>

        <!-- In Testing -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Testing</span>
                            <h3 class="fw-bold my-1 text-info"><?= number_format($stats['testing']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info"><i class="ri-test-tube-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        QA & ভেরিফিকেশন রানিং
                    </div>
                </div>
            </div>
        </div>

        <!-- In Ongoing / Development -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Ongoing</span>
                            <h3 class="fw-bold my-1 text-primary"><?= number_format($stats['ongoing']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ri-loader-4-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        সক্রিয় ডেভেলপমেন্ট চলছে
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending / Open -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Pending / Open</span>
                            <h3 class="fw-bold my-1 text-warning"><?= number_format($stats['open'] + $stats['pending']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning"><i class="ri-time-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        পরিকল্পিত / কিউতে আছে
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Issues / Bugs -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 feature-card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Critical Issues</span>
                            <h3 class="fw-bold my-1 text-danger"><?= number_format($stats['critical_issues']) ?></h3>
                        </div>
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-danger"><i class="ri-bug-line"></i></span>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        উচ্চ অগ্রাধিকারযুক্ত সমস্যা
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Breakdown Quick Bar -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark"><i class="ri-stack-line text-primary me-1"></i>প্ল্যাটফর্ম ডিস্ট্রিবিউশন:</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="?platform=all" class="badge <?= $filter_platform === 'all' ? 'bg-primary text-white' : 'bg-label-secondary' ?> text-decoration-none py-2 px-3">
                        <i class="ri-apps-line me-1"></i> সমস্ত প্ল্যাটফর্ম (<?= $stats['total'] ?>)
                    </a>
                    <a href="?platform=Dashboard" class="badge <?= $filter_platform === 'Dashboard' ? 'bg-primary text-white' : 'bg-label-primary' ?> text-decoration-none py-2 px-3">
                        <i class="ri-dashboard-line me-1"></i> Dashboard (<?= $stats['p_dashboard'] ?>)
                    </a>
                    <a href="?platform=Console" class="badge <?= $filter_platform === 'Console' ? 'bg-info text-white' : 'bg-label-info' ?> text-decoration-none py-2 px-3">
                        <i class="ri-terminal-box-line me-1"></i> Console (<?= $stats['p_console'] ?>)
                    </a>
                    <a href="?platform=Android+Lite" class="badge <?= $filter_platform === 'Android Lite' ? 'bg-success text-white' : 'bg-label-success' ?> text-decoration-none py-2 px-3">
                        <i class="ri-android-line me-1"></i> Android Lite (<?= $stats['p_android_lite'] ?>)
                    </a>
                    <a href="?platform=Android+Premium" class="badge <?= $filter_platform === 'Android Premium' ? 'bg-warning text-dark' : 'bg-label-warning' ?> text-decoration-none py-2 px-3">
                        <i class="ri-smartphone-line me-1"></i> Android Premium (<?= $stats['p_android_premium'] ?>)
                    </a>
                    <a href="?platform=Desktop" class="badge <?= $filter_platform === 'Desktop' ? 'bg-secondary text-white' : 'bg-label-secondary' ?> text-decoration-none py-2 px-3">
                        <i class="ri-computer-line me-1"></i> Desktop (<?= $stats['p_desktop'] ?>)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter Filter & Search Box -->
    <div class="card filter-bar mb-4 border-0">
        <div class="card-header bg-transparent pb-1 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="ri-filter-3-line text-primary"></i> প্যারামিটার ফিল্টার ও সার্চ অপশন
            </h6>
            <?php if ($filter_platform !== 'all' || $filter_module !== 'all' || $filter_status !== 'all' || $filter_priority !== 'all' || !empty($filter_search)): ?>
                <a href="feature-tracker.php" class="btn btn-sm btn-outline-danger">
                    <i class="ri-refresh-line me-1"></i> ফিল্টার রিসেট করুন
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body pt-2">
            <form method="GET" action="feature-tracker.php" id="filterForm">
                <div class="row g-2">
                    <!-- Platform Filter -->
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">প্ল্যাটফর্ম (Platform)</label>
                        <select name="platform" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $filter_platform === 'all' ? 'selected' : '' ?>>-- All Platforms --</option>
                            <option value="Dashboard" <?= $filter_platform === 'Dashboard' ? 'selected' : '' ?>>Dashboard</option>
                            <option value="Console" <?= $filter_platform === 'Console' ? 'selected' : '' ?>>Console</option>
                            <option value="Android Lite" <?= $filter_platform === 'Android Lite' ? 'selected' : '' ?>>Android Lite</option>
                            <option value="Android Premium" <?= $filter_platform === 'Android Premium' ? 'selected' : '' ?>>Android Premium</option>
                            <option value="Desktop" <?= $filter_platform === 'Desktop' ? 'selected' : '' ?>>Desktop</option>
                            <option value="API" <?= $filter_platform === 'API' ? 'selected' : '' ?>>API</option>
                            <option value="General" <?= $filter_platform === 'General' ? 'selected' : '' ?>>General</option>
                        </select>
                    </div>

                    <!-- Module Filter -->
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">মডিউল (Module)</label>
                        <select name="module" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $filter_module === 'all' ? 'selected' : '' ?>>-- All Modules --</option>
                            <?php foreach ($modules_list as $mod): ?>
                                <option value="<?= htmlspecialchars($mod) ?>" <?= $filter_module === $mod ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mod) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">স্ট্যাটাস (Status)</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>-- All Status --</option>
                            <option value="Open" <?= $filter_status === 'Open' ? 'selected' : '' ?>>🔴 Open</option>
                            <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>🟡 Pending</option>
                            <option value="Ongoing" <?= $filter_status === 'Ongoing' ? 'selected' : '' ?>>🔵 Ongoing</option>
                            <option value="Testing" <?= $filter_status === 'Testing' ? 'selected' : '' ?>>🟣 Testing</option>
                            <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>🟢 Completed</option>
                            <option value="On Hold" <?= $filter_status === 'On Hold' ? 'selected' : '' ?>>⚪ On Hold</option>
                            <option value="Closed" <?= $filter_status === 'Closed' ? 'selected' : '' ?>>⚫ Closed</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">অগ্রাধিকার (Priority)</label>
                        <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $filter_priority === 'all' ? 'selected' : '' ?>>-- All Priorities --</option>
                            <option value="Critical" <?= $filter_priority === 'Critical' ? 'selected' : '' ?>>🔥 Critical</option>
                            <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>⚡ High</option>
                            <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>🔹 Medium</option>
                            <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>▫️ Low</option>
                        </select>
                    </div>

                    <!-- Keyword Search -->
                    <div class="col-12 col-md-12 col-lg-4">
                        <label class="form-label small fw-semibold text-muted mb-1">কীওয়ার্ড সার্চ (Search)</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="ফিচার, স্ক্রিপ্ট, ইস্যু বা রেসপন্স খুঁজুন..." value="<?= htmlspecialchars($filter_search) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line me-1"></i> খুঁজুন
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Feature Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="ri-list-check-2 text-primary"></i> ফিচার ও স্ট্যাটাস তালিকা
                <span class="badge bg-label-primary rounded-pill"><?= count($features) ?> টি রেকর্ড</span>
            </h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnExportCsv">
                    <i class="ri-file-excel-2-line me-1"></i> CSV এক্সপোর্ট
                </button>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0" id="featuresTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#ID</th>
                        <th>মডিউল ও বিষয়</th>
                        <th>ফিচার নাম ও স্ক্রিপ্ট</th>
                        <th>প্ল্যাটফর্ম</th>
                        <th style="width: 140px;">স্ট্যাটাস</th>
                        <th>অগ্রাধিকার</th>
                        <th style="width: 120px;">অগ্রগতি (%)</th>
                        <th style="min-width: 250px;">ইস্যু ও রেসপন্স সামারি</th>
                        <th class="text-center" style="width: 100px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php if (empty($features)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                                <span>কোনো ফিচার রেকর্ড পাওয়া যায়নি! ফিল্টার পরিবর্তন করুন অথবা নতুন ফিচার যোগ করুন।</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($features as $f): 
                            $plat_info = $platforms[$f['platform']] ?? ['icon' => 'ri-device-line', 'badge' => 'bg-label-secondary', 'color' => '#8592a3'];
                            $stat_info = $status_meta[$f['status']] ?? ['badge' => 'bg-secondary', 'icon' => 'ri-information-line', 'color' => '#8592a3'];
                            $prio_info = $priority_meta[$f['priority']] ?? ['badge' => 'badge bg-secondary', 'icon' => 'ri-subtract-line'];
                        ?>
                            <tr id="row-<?= $f['id'] ?>">
                                <!-- ID -->
                                <td>
                                    <span class="fw-bold text-muted small">#<?= $f['id'] ?></span>
                                </td>

                                <!-- Module & Topic -->
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($f['module']) ?></div>
                                    <?php if (!empty($f['topic'])): ?>
                                        <small class="text-muted d-block"><i class="ri-hashtag me-1"></i><?= htmlspecialchars($f['topic']) ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Feature & Script -->
                                <td>
                                    <div class="fw-semibold text-primary"><?= htmlspecialchars($f['feature']) ?></div>
                                    <?php if (!empty($f['script'])): ?>
                                        <span class="code-script mt-1 d-inline-block" title="<?= htmlspecialchars($f['script']) ?>">
                                            <i class="ri-file-code-line me-1"></i><?= htmlspecialchars($f['script']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Platform Badge -->
                                <td>
                                    <span class="platform-pill <?= $plat_info['badge'] ?>">
                                        <i class="<?= $plat_info['icon'] ?>"></i>
                                        <?= htmlspecialchars($f['platform']) ?>
                                    </span>
                                </td>

                                <!-- Inline Status Switcher -->
                                <td>
                                    <select class="form-select form-select-sm status-select-badge <?= $stat_info['badge'] ?>" 
                                            onchange="quickChangeStatus(<?= $f['id'] ?>, this.value)"
                                            title="স্ট্যাটাস দ্রুত পরিবর্তন করুন">
                                        <?php foreach ($status_meta as $st_key => $st_val): ?>
                                            <option value="<?= $st_key ?>" <?= $f['status'] === $st_key ? 'selected' : '' ?> class="bg-white text-dark">
                                                <?= $st_key ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <!-- Priority -->
                                <td>
                                    <span class="<?= $prio_info['badge'] ?>">
                                        <i class="<?= $prio_info['icon'] ?> me-1"></i><?= htmlspecialchars($f['priority']) ?>
                                    </span>
                                </td>

                                <!-- Progress Bar -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress progress-compact flex-grow-1 bg-light">
                                            <div class="progress-bar <?= $f['progress_percent'] == 100 ? 'bg-success' : ($f['progress_percent'] > 50 ? 'bg-primary' : 'bg-warning') ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= intval($f['progress_percent']) ?>%">
                                            </div>
                                        </div>
                                        <span class="small fw-semibold text-muted"><?= intval($f['progress_percent']) ?>%</span>
                                    </div>
                                </td>

                                <!-- Issues & Response Summary -->
                                <td>
                                    <div style="max-width: 320px; white-space: normal;">
                                        <?php if (!empty($f['issues'])): ?>
                                            <div class="small mb-1 text-truncate-2" title="<?= htmlspecialchars($f['issues']) ?>">
                                                <strong class="text-danger"><i class="ri-bug-line me-1"></i>ইস্যু:</strong> 
                                                <?= htmlspecialchars($f['issues']) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($f['response'])): ?>
                                            <div class="small text-muted text-truncate-2" title="<?= htmlspecialchars($f['response']) ?>">
                                                <strong class="text-success"><i class="ri-reply-line me-1"></i>রেসপন্স:</strong> 
                                                <?= htmlspecialchars($f['response']) ?>
                                            </div>
                                        <?php elseif (empty($f['issues'])): ?>
                                            <span class="text-muted small fst-italic">কোনো ইস্যু নথিভুক্ত নেই</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-fill fs-5"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow">
                                            <a class="dropdown-item" href="javascript:void(0);" onclick='openResponseModal(<?= json_encode($f) ?>)'>
                                                <i class="ri-chat-check-line text-success me-2"></i> রেসপন্স ও ইস্যু আপডেট
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick='openEditModal(<?= json_encode($f) ?>)'>
                                                <i class="ri-pencil-line text-primary me-2"></i> বিস্তারিত এডিট
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0);" onclick='openDuplicateModal(<?= $f['id'] ?>, "<?= htmlspecialchars($f['feature']) ?>", "<?= htmlspecialchars($f['platform']) ?>")'>
                                                <i class="ri-file-copy-2-line text-info me-2"></i> অন্য প্ল্যাটফর্মে ডুপ্লিকেট
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteFeature(<?= $f['id'] ?>)">
                                                <i class="ri-delete-bin-line me-2"></i> মুছে ফেলুন
                                            </a>
                                        </div>
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

<!-- ============================================================= -->
<!-- MODAL: ADD NEW FEATURE -->
<!-- ============================================================= -->
<div class="modal fade" id="addFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="ri-add-circle-line text-primary me-1"></i> নতুন ফিচার ট্র্যাকিং এন্ট্রি যোগ করুন
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addFeatureForm">
                <input type="hidden" name="action" value="add_feature">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Module -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">মডিউল (Module) <span class="text-danger">*</span></label>
                            <input type="text" name="module" class="form-control" list="moduleSuggestions" placeholder="যেমন: Attendance, Accounts, Exam" required>
                            <datalist id="moduleSuggestions">
                                <?php foreach ($modules_list as $mod): ?>
                                    <option value="<?= htmlspecialchars($mod) ?>">
                                <?php endforeach; ?>
                                <option value="Attendance">
                                <option value="Accounts">
                                <option value="Academics">
                                <option value="Exam & Result">
                                <option value="Analytics">
                                <option value="Routine & Schedule">
                                <option value="Communication">
                                <option value="Admission">
                                <option value="Settings & Security">
                            </datalist>
                        </div>

                        <!-- Feature Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ফিচার নাম (Feature) <span class="text-danger">*</span></label>
                            <input type="text" name="feature" class="form-control" placeholder="যেমন: Student Attendance via QR Code" required>
                        </div>

                        <!-- Platform -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">প্ল্যাটফর্ম (Platform) <span class="text-danger">*</span></label>
                            <select name="platform" class="form-select" required>
                                <option value="Dashboard">Dashboard</option>
                                <option value="Console">Console</option>
                                <option value="Android Lite">Android Lite</option>
                                <option value="Android Premium">Android Premium</option>
                                <option value="Desktop">Desktop</option>
                                <option value="API">API</option>
                                <option value="General">General</option>
                            </select>
                        </div>

                        <!-- Topic / Sub-feature -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">টপিক / সাব-টপিক (Topic)</label>
                            <input type="text" name="topic" class="form-control" placeholder="যেমন: Camera QR Scanning & Sync">
                        </div>

                        <!-- Script / Route / File Path -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">স্ক্রিপ্ট / ফাইল পাথ (Script)</label>
                            <input type="text" name="script" class="form-control font-monospace" placeholder="যেমন: attendance-register.php">
                        </div>

                        <!-- Initial Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">প্রাথমিক স্ট্যাটাস (Status)</label>
                            <select name="status" class="form-select">
                                <option value="Open" selected>🔴 Open</option>
                                <option value="Pending">🟡 Pending</option>
                                <option value="Ongoing">🔵 Ongoing</option>
                                <option value="Testing">🟣 Testing</option>
                                <option value="Completed">🟢 Completed</option>
                                <option value="On Hold">⚪ On Hold</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রাধিকার (Priority)</label>
                            <select name="priority" class="form-select">
                                <option value="Critical">🔥 Critical</option>
                                <option value="High">⚡ High</option>
                                <option value="Medium" selected>🔹 Medium</option>
                                <option value="Low">▫️ Low</option>
                            </select>
                        </div>

                        <!-- Progress (%) -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রগতি শতাংশ (Progress %)</label>
                            <input type="number" name="progress_percent" class="form-control" min="0" max="100" value="0">
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">অ্যাসাইন করা হয়েছে (Assigned Developer / QA)</label>
                            <input type="text" name="assigned_to" class="form-control" placeholder="যেমন: Reaz, Dev Team">
                        </div>

                        <!-- Issues / Bug Details -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-danger"><i class="ri-bug-line me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ (Issues / Bugs)</label>
                            <textarea name="issues" class="form-control" rows="3" placeholder="ফিচারটিতে কী সমস্যা রয়েছে বা কী কী কাজ বাকি আছে বিস্তারিত লিখুন..."></textarea>
                        </div>

                        <!-- Initial Response / Notes -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-success"><i class="ri-reply-line me-1"></i>ডেভেলপার রেসপন্স বা সমাধান নোট (Response)</label>
                            <textarea name="response" class="form-control" rows="2" placeholder="সমাধানের জন্য কী পদক্ষেপ নেওয়া হয়েছে বা মতামত..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> ডাটাবেজে সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDIT FEATURE -->
<!-- ============================================================= -->
<div class="modal fade" id="editFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="ri-edit-2-line text-primary me-1"></i> ফিচার বিস্তারিত এডিট করুন
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFeatureForm">
                <input type="hidden" name="action" value="edit_feature">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">মডিউল (Module) <span class="text-danger">*</span></label>
                            <input type="text" name="module" id="edit_module" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ফিচার নাম (Feature) <span class="text-danger">*</span></label>
                            <input type="text" name="feature" id="edit_feature" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">প্ল্যাটফর্ম (Platform) <span class="text-danger">*</span></label>
                            <select name="platform" id="edit_platform" class="form-select" required>
                                <option value="Dashboard">Dashboard</option>
                                <option value="Console">Console</option>
                                <option value="Android Lite">Android Lite</option>
                                <option value="Android Premium">Android Premium</option>
                                <option value="Desktop">Desktop</option>
                                <option value="API">API</option>
                                <option value="General">General</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">টপিক (Topic)</label>
                            <input type="text" name="topic" id="edit_topic" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">স্ক্রিপ্ট / ফাইল পাথ (Script)</label>
                            <input type="text" name="script" id="edit_script" class="form-control font-monospace">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">স্ট্যাটাস (Status)</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Open">🔴 Open</option>
                                <option value="Pending">🟡 Pending</option>
                                <option value="Ongoing">🔵 Ongoing</option>
                                <option value="Testing">🟣 Testing</option>
                                <option value="Completed">🟢 Completed</option>
                                <option value="Closed">⚫ Closed</option>
                                <option value="On Hold">⚪ On Hold</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রাধিকার (Priority)</label>
                            <select name="priority" id="edit_priority" class="form-select">
                                <option value="Critical">🔥 Critical</option>
                                <option value="High">⚡ High</option>
                                <option value="Medium">🔹 Medium</option>
                                <option value="Low">▫️ Low</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রগতি শতাংশ (Progress %)</label>
                            <input type="number" name="progress_percent" id="edit_progress" class="form-control" min="0" max="100">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">অ্যাসাইন করা হয়েছে (Assigned To)</label>
                            <input type="text" name="assigned_to" id="edit_assigned_to" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-danger"><i class="ri-bug-line me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ (Issues / Bugs)</label>
                            <textarea name="issues" id="edit_issues" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-success"><i class="ri-reply-line me-1"></i>ডেভেলপার রেসপন্স বা সমাধান নোট (Response)</label>
                            <textarea name="response" id="edit_response" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line me-1"></i> পরিবর্তন সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: QUICK DEVELOPER RESPONSE & ISSUES -->
<!-- ============================================================= -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="ri-chat-check-line text-success me-1"></i> রেসপন্স ও ইস্যু আপডেট
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="responseForm">
                <input type="hidden" name="action" value="quick_update_response">
                <input type="hidden" name="id" id="resp_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-semibold text-dark d-block mb-1" id="resp_feature_title">ফিচার নাম</label>
                        <span class="badge bg-label-primary" id="resp_platform_badge">Platform</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger">ইস্যু বিবরণ (Issues / Bugs)</label>
                        <textarea name="issues" id="resp_issues" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-success">ডেভেলপার রেসপন্স ও সমাধান নোট (Response)</label>
                        <textarea name="response" id="resp_response" class="form-control" rows="3" placeholder="বর্তমান অগ্রগতি বা সমাধান বিবরণ লিখুন..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">স্ট্যাটাস (Status)</label>
                            <select name="status" id="resp_status" class="form-select form-select-sm">
                                <option value="Open">🔴 Open</option>
                                <option value="Pending">🟡 Pending</option>
                                <option value="Ongoing">🔵 Ongoing</option>
                                <option value="Testing">🟣 Testing</option>
                                <option value="Completed">🟢 Completed</option>
                                <option value="On Hold">⚪ On Hold</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">অগ্রগতি (%)</label>
                            <input type="number" name="progress_percent" id="resp_progress" class="form-control form-select-sm" min="0" max="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-check-double-line me-1"></i> রেসপন্স আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: DUPLICATE TO ANOTHER PLATFORM -->
<!-- ============================================================= -->
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="ri-file-copy-2-line text-info me-1"></i> অন্য প্ল্যাটফর্মে ডুপ্লিকেট করুন
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="duplicateForm">
                <input type="hidden" name="action" value="duplicate_platform">
                <input type="hidden" name="id" id="dup_id">
                <div class="modal-body">
                    <p class="text-muted small">
                        বর্তমান ফিচারটি নির্বাচিত অন্য একটি প্ল্যাটফর্মে নতুন ট্র্যাকিং আইটেম হিসেবে যুক্ত হবে।
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফিচার নাম</label>
                        <input type="text" id="dup_feature_name" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">টার্গেট প্ল্যাটফর্ম (Target Platform) <span class="text-danger">*</span></label>
                        <select name="target_platform" id="dup_target_platform" class="form-select" required>
                            <option value="Dashboard">Dashboard</option>
                            <option value="Console">Console</option>
                            <option value="Android Lite">Android Lite</option>
                            <option value="Android Premium">Android Premium</option>
                            <option value="Desktop">Desktop</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="ri-file-copy-line me-1"></i> ডুপ্লিকেট তৈরি করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- CLIENT-SIDE JS HANDLERS (AJAX & FILTER INTERACTION) -->
<!-- ============================================================= -->
<script>
    // Quick Inline Status Change
    function quickChangeStatus(id, newStatus) {
        const formData = new FormData();
        formData.append('action', 'quick_update_status');
        formData.append('id', id);
        formData.append('status', newStatus);

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('সফল!', data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast('ত্রুটি!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('ত্রুটি!', 'সার্ভারে অনুরোধ পাঠানো সম্ভব হয়নি।', 'error');
        });
    }

    // Open Edit Modal with Pre-populated Data
    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_module').value = data.module;
        document.getElementById('edit_feature').value = data.feature;
        document.getElementById('edit_platform').value = data.platform;
        document.getElementById('edit_topic').value = data.topic || '';
        document.getElementById('edit_script').value = data.script || '';
        document.getElementById('edit_status').value = data.status;
        document.getElementById('edit_priority').value = data.priority;
        document.getElementById('edit_progress').value = data.progress_percent || 0;
        document.getElementById('edit_assigned_to').value = data.assigned_to || '';
        document.getElementById('edit_issues').value = data.issues || '';
        document.getElementById('edit_response').value = data.response || '';

        const modal = new bootstrap.Modal(document.getElementById('editFeatureModal'));
        modal.show();
    }

    // Open Response Modal
    function openResponseModal(data) {
        document.getElementById('resp_id').value = data.id;
        document.getElementById('resp_feature_title').textContent = data.module + ' » ' + data.feature;
        document.getElementById('resp_platform_badge').textContent = data.platform;
        document.getElementById('resp_issues').value = data.issues || '';
        document.getElementById('resp_response').value = data.response || '';
        document.getElementById('resp_status').value = data.status;
        document.getElementById('resp_progress').value = data.progress_percent || 0;

        const modal = new bootstrap.Modal(document.getElementById('responseModal'));
        modal.show();
    }

    // Open Duplicate Modal
    function openDuplicateModal(id, featureName, currentPlatform) {
        document.getElementById('dup_id').value = id;
        document.getElementById('dup_feature_name').value = featureName + ' (' + currentPlatform + ')';
        
        // Auto select a different platform default
        const platforms = ['Dashboard', 'Console', 'Android Lite', 'Android Premium', 'Desktop'];
        const filtered = platforms.filter(p => p !== currentPlatform);
        if (filtered.length > 0) {
            document.getElementById('dup_target_platform').value = filtered[0];
        }

        const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
        modal.show();
    }

    // Delete Feature Record
    function deleteFeature(id) {
        if (!confirm('আপনি কি নিশ্চিত যে এই ফিচার রেকর্ডটি মুছে ফেলতে চান?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_feature');
        formData.append('id', id);

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('সফল!', data.message, 'success');
                const row = document.getElementById('row-' + id);
                if (row) row.remove();
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast('ত্রুটি!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('ত্রুটি!', 'রেকর্ড মুছতে সমস্যা হয়েছে।', 'error');
        });
    }

    // Form Submit Handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Add Feature Form
        document.getElementById('addFeatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Edit Feature Form
        document.getElementById('editFeatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Quick Response Form
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Duplicate Form
        document.getElementById('duplicateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Seed Demo Button
        const seedBtn = document.getElementById('btnSeedDemo');
        if (seedBtn) {
            seedBtn.addEventListener('click', function() {
                const formData = new FormData();
                formData.append('action', 'seed_default_data');
                fetch('feature-tracker.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    showToast('ডেমো সেটআপ!', data.message, 'success');
                    setTimeout(() => window.location.reload(), 700);
                });
            });
        }

        // Export to CSV
        const exportBtn = document.getElementById('btnExportCsv');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                let csv = [];
                const rows = document.querySelectorAll('#featuresTable tr');
                
                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll('td, th');
                    for (let j = 0; j < cols.length - 1; j++) {
                        let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/(\s\s+)/gm, ' ');
                        data = data.replace(/"/g, '""');
                        row.push('"' + data + '"');
                    }
                    csv.push(row.join(','));
                }
                
                const csvFile = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
                const downloadLink = document.createElement('a');
                downloadLink.download = 'EIMBox_Feature_Matrix_' + new Date().toISOString().slice(0,10) + '.csv';
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        }
    });

    // Simple Toast notification helper
    function showToast(title, message, type) {
        if (window.toastr) {
            if (type === 'success') toastr.success(message, title);
            else if (type === 'error') toastr.error(message, title);
            else toastr.info(message, title);
        } else {
            alert(title + '\n' + message);
        }
    }
</script>

<?php require_once 'footer.php'; ?>
