<?php
/**
 * EIMBox Feature & Platform Status Matrix / Tracker
 * File: feature-tracker.php
 * Author: EIMBox Team
 * Purpose: Track features, issues, developer responses, and platform status across
 *          Dashboard, Console, Android Lite, Android Premium, Desktop without page reloads.
 */

// Available Platforms Metadata
$platforms = [
    'Dashboard' => ['icon' => 'ri-dashboard-line', 'badge' => 'bg-label-primary', 'color' => '#696cff'],
    'Console' => ['icon' => 'ri-terminal-box-line', 'badge' => 'bg-label-info', 'color' => '#03c3ec'],
    'Android Lite' => ['icon' => 'ri-android-line', 'badge' => 'bg-label-success', 'color' => '#71dd37'],
    'Android Premium' => ['icon' => 'ri-smartphone-line', 'badge' => 'bg-label-warning', 'color' => '#ffab00'],
    'Desktop' => ['icon' => 'ri-computer-line', 'badge' => 'bg-label-secondary', 'color' => '#8592a3'],
    'API' => ['icon' => 'ri-code-s-slash-line', 'badge' => 'bg-label-dark', 'color' => '#233446'],
    'General' => ['icon' => 'ri-global-line', 'badge' => 'bg-label-info', 'color' => '#03c3ec']
];

// Status Colors & Badges Metadata
$status_meta = [
    'Open' => ['badge' => 'bg-danger text-white', 'badge_soft' => 'bg-label-danger', 'icon' => 'ri-error-warning-line', 'color' => '#ff3e1d'],
    'Pending' => ['badge' => 'bg-warning text-dark', 'badge_soft' => 'bg-label-warning', 'icon' => 'ri-time-line', 'color' => '#ffab00'],
    'Ongoing' => ['badge' => 'bg-info text-white', 'badge_soft' => 'bg-label-info', 'icon' => 'ri-loader-4-line', 'color' => '#03c3ec'],
    'Testing' => ['badge' => 'bg-primary text-white', 'badge_soft' => 'bg-label-primary', 'icon' => 'ri-test-tube-line', 'color' => '#696cff'],
    'Completed' => ['badge' => 'bg-success text-white', 'badge_soft' => 'bg-label-success', 'icon' => 'ri-checkbox-circle-line', 'color' => '#71dd37'],
    'Closed' => ['badge' => 'bg-secondary text-white', 'badge_soft' => 'bg-label-secondary', 'icon' => 'ri-close-circle-line', 'color' => '#8592a3'],
    'On Hold' => ['badge' => 'bg-dark text-white', 'badge_soft' => 'bg-label-dark', 'icon' => 'ri-pause-circle-line', 'color' => '#233446']
];

// Priority Colors Metadata
$priority_meta = [
    'Critical' => ['badge' => 'badge bg-danger', 'icon' => 'ri-alarm-warning-fill'],
    'High' => ['badge' => 'badge bg-warning text-dark', 'icon' => 'ri-arrow-up-circle-fill'],
    'Medium' => ['badge' => 'badge bg-info', 'icon' => 'ri-subtract-fill'],
    'Low' => ['badge' => 'badge bg-label-secondary', 'icon' => 'ri-arrow-down-circle-line']
];

/**
 * Reusable HTML Renderer for Table Rows & Expandable Detail Drawers
 */
function render_features_table_body($features, $platforms, $status_meta, $priority_meta) {
    if (empty($features)) {
        ?>
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                <div class="fw-semibold">কোনো ফিচার রেকর্ড পাওয়া যায়নি!</div>
                <small class="text-muted">ফিল্টার পরিবর্তন করুন অথবা নতুন ফিচার যুক্ত করুন।</small>
            </td>
        </tr>
        <?php
        return;
    }

    foreach ($features as $f): 
        $plat_info = $platforms[$f['platform']] ?? ['icon' => 'ri-device-line', 'badge' => 'bg-label-secondary', 'color' => '#8592a3'];
        $stat_info = $status_meta[$f['status']] ?? ['badge' => 'bg-secondary', 'badge_soft' => 'bg-label-secondary', 'icon' => 'ri-information-line', 'color' => '#8592a3'];
        $prio_info = $priority_meta[$f['priority']] ?? ['badge' => 'badge bg-secondary', 'icon' => 'ri-subtract-line'];
        $has_issues = !empty(trim($f['issues'] ?? ''));
        $has_resp   = !empty(trim($f['response'] ?? ''));
        $feature_json = htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8');
    ?>
        <!-- Main Clickable Table Row -->
        <tr class="feature-main-row" id="row-<?= $f['id'] ?>" onclick="toggleDrawer(<?= $f['id'] ?>, event)">
            <!-- ID + Expand Arrow -->
            <td class="col-id">
                <i class="ri-arrow-right-s-line expand-icon text-muted" id="icon-<?= $f['id'] ?>"></i>
                <span class="d-block small text-muted">#<?= $f['id'] ?></span>
            </td>

            <!-- Module & Platform -->
            <td class="col-module">
                <div class="fw-bold text-dark small mb-1"><?= htmlspecialchars($f['module']) ?></div>
                <span class="platform-pill <?= $plat_info['badge'] ?>">
                    <i class="<?= $plat_info['icon'] ?>"></i>
                    <?= htmlspecialchars($f['platform']) ?>
                </span>
            </td>

            <!-- Feature & Script -->
            <td class="col-feature">
                <div class="fw-semibold text-primary" style="font-size: 0.9rem;">
                    <?= htmlspecialchars($f['feature']) ?>
                </div>
                <?php if (!empty($f['topic'])): ?>
                    <div class="text-muted small"><i class="ri-hashtag text-secondary"></i> <?= htmlspecialchars($f['topic']) ?></div>
                <?php endif; ?>
                <?php if (!empty($f['script'])): ?>
                    <span class="code-script mt-1" title="<?= htmlspecialchars($f['script']) ?>">
                        <i class="ri-file-code-line me-1"></i><?= htmlspecialchars($f['script']) ?>
                    </span>
                <?php endif; ?>
            </td>

            <!-- Inline Fast Status Change -->
            <td class="col-status" onclick="event.stopPropagation()">
                <select class="form-select form-select-sm status-select-badge <?= $stat_info['badge'] ?>" 
                        onchange="quickChangeStatus(<?= $f['id'] ?>, this.value)"
                        title="স্ট্যাটাস দ্রুত পরিবর্তন করতে সিলেক্ট করুন">
                    <?php foreach ($status_meta as $st_key => $st_val): ?>
                        <option value="<?= $st_key ?>" <?= $f['status'] === $st_key ? 'selected' : '' ?> class="bg-white text-dark">
                            <?= $st_key ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <div class="progress progress-compact flex-grow-1 bg-light">
                        <div class="progress-bar <?= $f['progress_percent'] == 100 ? 'bg-success' : ($f['progress_percent'] > 50 ? 'bg-primary' : 'bg-warning') ?>" 
                             style="width: <?= intval($f['progress_percent']) ?>%"></div>
                    </div>
                    <span style="font-size: 0.7rem;" class="text-muted"><?= intval($f['progress_percent']) ?>%</span>
                </div>
            </td>

            <!-- Priority -->
            <td class="col-priority">
                <span class="<?= $prio_info['badge'] ?>">
                    <i class="<?= $prio_info['icon'] ?>"></i> <?= htmlspecialchars($f['priority']) ?>
                </span>
            </td>

            <!-- Issues & Response Badges -->
            <td class="col-issues">
                <div class="d-flex flex-column gap-1">
                    <?php if ($has_issues): ?>
                        <span class="badge bg-label-danger text-truncate d-block text-start" title="<?= htmlspecialchars($f['issues']) ?>">
                            <i class="ri-bug-line me-1"></i> ইশ্যু আছে
                        </span>
                    <?php endif; ?>
                    <?php if ($has_resp): ?>
                        <span class="badge bg-label-success text-truncate d-block text-start" title="<?= htmlspecialchars($f['response']) ?>">
                            <i class="ri-reply-line me-1"></i> রেসপন্স দেওয়া
                        </span>
                    <?php endif; ?>
                    <?php if (!$has_issues && !$has_resp): ?>
                        <span class="text-muted small fst-italic">কোনো ইস্যু নেই</span>
                    <?php endif; ?>
                </div>
            </td>

            <!-- Direct Action Buttons on Every Row -->
            <td class="col-actions" onclick="event.stopPropagation()">
                <div class="action-btn-group">
                    <!-- 1. Quick Response & Issue Note Button -->
                    <button type="button" class="btn btn-sm btn-outline-success act-btn" 
                            title="রেসপন্স ও সমস্যা নোট আপডেট করুন"
                            data-record="<?= $feature_json ?>"
                            onclick='openResponseModalFromBtn(this)'>
                        <i class="ri-chat-check-line"></i>
                    </button>

                    <!-- 2. Full Edit Button -->
                    <button type="button" class="btn btn-sm btn-outline-primary act-btn" 
                            title="ফিচার বিস্তারিত এডিট করুন"
                            data-record="<?= $feature_json ?>"
                            onclick='openEditModalFromBtn(this)'>
                        <i class="ri-pencil-line"></i>
                    </button>

                    <!-- 3. Duplicate to Platform Button -->
                    <button type="button" class="btn btn-sm btn-outline-info act-btn" 
                            title="অন্য প্ল্যাটফর্মে ডুপ্লিকেট করুন"
                            onclick='openDuplicateModal(<?= $f['id'] ?>, "<?= htmlspecialchars($f['feature'], ENT_QUOTES) ?>", "<?= htmlspecialchars($f['platform'], ENT_QUOTES) ?>")'>
                        <i class="ri-file-copy-2-line"></i>
                    </button>

                    <!-- 4. Delete Button -->
                    <button type="button" class="btn btn-sm btn-outline-danger act-btn" 
                            title="মুছে ফেলুন"
                            onclick="deleteFeature(<?= $f['id'] ?>)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        </tr>

        <!-- Expandable Details Row (Shown on Click) -->
        <tr class="feature-detail-row" id="drawer-<?= $f['id'] ?>">
            <td colspan="7" class="p-2">
                <div class="detail-drawer-box">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <strong class="text-primary fs-6"><?= htmlspecialchars($f['module']) ?> &raquo; <?= htmlspecialchars($f['feature']) ?></strong>
                            <span class="platform-pill <?= $plat_info['badge'] ?> ms-2"><?= htmlspecialchars($f['platform']) ?></span>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="text-muted small">
                                <i class="ri-user-line me-1"></i><?= htmlspecialchars($f['assigned_to'] ?: 'Unassigned') ?>
                            </span>
                            <span class="text-muted small">
                                <i class="ri-time-line me-1"></i><?= date('d M Y, h:i A', strtotime($f['updated_at'] ?: $f['created_at'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Issues / Bug description -->
                        <div class="col-md-6">
                            <div class="p-2 rounded bg-light border">
                                <strong class="text-danger d-block mb-1">
                                    <i class="ri-bug-line me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ (Issues):
                                </strong>
                                <p class="mb-0 text-dark small" style="white-space: pre-wrap;"><?= !empty($f['issues']) ? htmlspecialchars($f['issues']) : '<span class="text-muted fst-italic">কোনো সমস্যা উল্লেখ নেই।</span>' ?></p>
                            </div>
                        </div>

                        <!-- Developer Response / Solution -->
                        <div class="col-md-6">
                            <div class="p-2 rounded bg-light border">
                                <strong class="text-success d-block mb-1">
                                    <i class="ri-reply-line me-1"></i>ডেভেলপার রেসপন্স বা সমাধান বিবরণ (Response):
                                </strong>
                                <p class="mb-0 text-dark small" style="white-space: pre-wrap;"><?= !empty($f['response']) ? htmlspecialchars($f['response']) : '<span class="text-muted fst-italic">এখনও কোনো রেসপন্স যুক্ত করা হয়নি।</span>' ?></p>
                            </div>
                        </div>

                        <!-- Script & Quick Actions Bar inside drawer -->
                        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2 pt-1 border-top">
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted fw-semibold">স্ক্রিপ্ট / ফাইল পাথ:</span>
                                <span class="code-script"><?= htmlspecialchars($f['script'] ?: 'None') ?></span>
                                <?php if (!empty($f['script'])): ?>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0" onclick="copyToClipboard('<?= htmlspecialchars($f['script'], ENT_QUOTES) ?>')">
                                        <i class="ri-file-copy-line me-1"></i>কপি
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-success" data-record="<?= $feature_json ?>" onclick='openResponseModalFromBtn(this)'>
                                    <i class="ri-chat-check-line me-1"></i> রেসপন্স লিখুন
                                </button>
                                <button type="button" class="btn btn-xs btn-primary" data-record="<?= $feature_json ?>" onclick='openEditModalFromBtn(this)'>
                                    <i class="ri-pencil-line me-1"></i> এডিট
                                </button>
                                <button type="button" class="btn btn-xs btn-info text-white" onclick='openDuplicateModal(<?= $f['id'] ?>, "<?= htmlspecialchars($f['feature'], ENT_QUOTES) ?>", "<?= htmlspecialchars($f['platform'], ENT_QUOTES) ?>")'>
                                    <i class="ri-file-copy-line me-1"></i> ডুপ্লিকেট
                                </button>
                                <button type="button" class="btn btn-xs btn-danger" onclick="deleteFeature(<?= $f['id'] ?>)">
                                    <i class="ri-delete-bin-line me-1"></i> মুছুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    <?php endforeach;
}

// -------------------------------------------------------------
// 1. AJAX Backend Handlers (Executed before headers if POST/AJAX)
// -------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) || (isset($_GET['ajax']) && $_GET['ajax'] == 1)) {
    // Include core DB if not already initialized
    if (!isset($conn)) {
        require_once 'core/init.php';
    }

    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
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

    // 1.0 AJAX FETCH FILTERED DATA WITHOUT PAGE RELOAD
    if ($action === 'fetch_features' || (isset($_GET['ajax']) && $_GET['ajax'] == 1)) {
        $f_platform = $_POST['platform'] ?? $_GET['platform'] ?? 'all';
        $f_module   = $_POST['module'] ?? $_GET['module'] ?? 'all';
        $f_feature  = $_POST['feature'] ?? $_GET['feature'] ?? 'all';
        $f_status   = $_POST['status'] ?? $_GET['status'] ?? 'all';
        $f_priority = $_POST['priority'] ?? $_GET['priority'] ?? 'all';
        $f_search   = trim($_POST['search'] ?? $_GET['search'] ?? '');

        $where_clauses = [];
        if ($f_platform !== 'all' && !empty($f_platform)) {
            $clean = $conn->real_escape_string($f_platform);
            $where_clauses[] = "platform = '$clean'";
        }
        if ($f_module !== 'all' && !empty($f_module)) {
            $clean = $conn->real_escape_string($f_module);
            $where_clauses[] = "module = '$clean'";
        }
        if ($f_feature !== 'all' && !empty($f_feature)) {
            $clean = $conn->real_escape_string($f_feature);
            $where_clauses[] = "feature = '$clean'";
        }
        if ($f_status !== 'all' && !empty($f_status)) {
            $clean = $conn->real_escape_string($f_status);
            $where_clauses[] = "status = '$clean'";
        }
        if ($f_priority !== 'all' && !empty($f_priority)) {
            $clean = $conn->real_escape_string($f_priority);
            $where_clauses[] = "priority = '$clean'";
        }
        if (!empty($f_search)) {
            $clean = $conn->real_escape_string($f_search);
            $where_clauses[] = "(feature LIKE '%$clean%' OR module LIKE '%$clean%' OR topic LIKE '%$clean%' OR script LIKE '%$clean%' OR issues LIKE '%$clean%' OR response LIKE '%$clean%')";
        }

        $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

        // Fetch features
        $q = "SELECT * FROM eimbox_features $where_sql ORDER BY FIELD(priority, 'Critical', 'High', 'Medium', 'Low'), FIELD(status, 'Open', 'Pending', 'Ongoing', 'Testing', 'On Hold', 'Completed', 'Closed'), module ASC, feature ASC";
        $res = $conn->query($q);
        $fetched_features = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $fetched_features[] = $row;
            }
        }

        // Fetch dynamic distinct features list for dropdown based on module
        $feat_list = [];
        $feat_sql = "SELECT DISTINCT feature FROM eimbox_features";
        if ($f_module !== 'all' && !empty($f_module)) {
            $clean_m = $conn->real_escape_string($f_module);
            $feat_sql .= " WHERE module = '$clean_m'";
        }
        $feat_sql .= " ORDER BY feature ASC";
        $feat_res = $conn->query($feat_sql);
        if ($feat_res) {
            while ($ft = $feat_res->fetch_assoc()) {
                if (!empty($ft['feature'])) $feat_list[] = $ft['feature'];
            }
        }

        // Render HTML for table
        ob_start();
        render_features_table_body($fetched_features, $platforms, $status_meta, $priority_meta);
        $html_output = ob_get_clean();

        // Statistics
        $stats_q = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Testing' THEN 1 ELSE 0 END) as testing,
            SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN priority = 'Critical' AND status NOT IN ('Completed', 'Closed') THEN 1 ELSE 0 END) as critical_issues,
            SUM(CASE WHEN platform = 'Dashboard' THEN 1 ELSE 0 END) as p_dashboard,
            SUM(CASE WHEN platform = 'Console' THEN 1 ELSE 0 END) as p_console,
            SUM(CASE WHEN platform = 'Android Lite' THEN 1 ELSE 0 END) as p_android_lite,
            SUM(CASE WHEN platform = 'Android Premium' THEN 1 ELSE 0 END) as p_android_premium,
            SUM(CASE WHEN platform = 'Desktop' THEN 1 ELSE 0 END) as p_desktop
            FROM eimbox_features");
        $stats_data = $stats_q ? $stats_q->fetch_assoc() : [];

        echo json_encode([
            'status' => 'success',
            'count' => count($fetched_features),
            'html' => $html_output,
            'features_list' => $feat_list,
            'selected_feature' => $f_feature,
            'stats' => $stats_data
        ]);
        exit;
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
                ['Communication', 'SMS & Push Notification Engine', 'Console', 'sms-gateway.php', 'Bulk Masking SMS Delivery', 'Failed SMS queue needs auto-retry mechanism with exponential backoff.', 'Cron job added to re-attempt failed SMS twice.', 'Completed', 'High', 100, 'Reaz'],
                ['Routine & Schedule', 'Class Routine Builder', 'Dashboard', 'class-routine.php', 'Teacher Conflict Detection', 'Drag & drop slot swapping sometimes allows double booking.', 'Under development with client-side slot matrix validator.', 'Ongoing', 'High', 55, 'Dev Team']
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

// Get filter values from GET parameters (initial load)
$filter_platform = $_GET['platform'] ?? 'all';
$filter_module   = $_GET['module'] ?? 'all';
$filter_feature  = $_GET['feature'] ?? 'all';
$filter_status   = $_GET['status'] ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$filter_search   = trim($_GET['search'] ?? '');

// Build Initial Query
$where_clauses = [];
if ($filter_platform !== 'all' && !empty($filter_platform)) {
    $clean_platform = $conn->real_escape_string($filter_platform);
    $where_clauses[] = "platform = '$clean_platform'";
}
if ($filter_module !== 'all' && !empty($filter_module)) {
    $clean_module = $conn->real_escape_string($filter_module);
    $where_clauses[] = "module = '$clean_module'";
}
if ($filter_feature !== 'all' && !empty($filter_feature)) {
    $clean_feature = $conn->real_escape_string($filter_feature);
    $where_clauses[] = "feature = '$clean_feature'";
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

// Fetch Initial Filtered Features
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
    SUM(CASE WHEN priority = 'Critical' AND status NOT IN ('Completed', 'Closed') THEN 1 ELSE 0 END) as critical_issues,
    SUM(CASE WHEN platform = 'Dashboard' THEN 1 ELSE 0 END) as p_dashboard,
    SUM(CASE WHEN platform = 'Console' THEN 1 ELSE 0 END) as p_console,
    SUM(CASE WHEN platform = 'Android Lite' THEN 1 ELSE 0 END) as p_android_lite,
    SUM(CASE WHEN platform = 'Android Premium' THEN 1 ELSE 0 END) as p_android_premium,
    SUM(CASE WHEN platform = 'Desktop' THEN 1 ELSE 0 END) as p_desktop
    FROM eimbox_features";
$stats_res = $conn->query($stats_query);
$stats = $stats_res ? $stats_res->fetch_assoc() : [
    'total' => 0, 'completed' => 0, 'testing' => 0, 'ongoing' => 0, 'pending' => 0, 'open' => 0,
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

// Fetch Distinct Features for Dropdown
$features_list = [];
$feat_sql = "SELECT DISTINCT feature FROM eimbox_features";
if ($filter_module !== 'all' && !empty($filter_module)) {
    $clean_mod = $conn->real_escape_string($filter_module);
    $feat_sql .= " WHERE module = '$clean_mod'";
}
$feat_sql .= " ORDER BY feature ASC";
$feat_res = $conn->query($feat_sql);
if ($feat_res) {
    while ($ft = $feat_res->fetch_assoc()) {
        if (!empty($ft['feature'])) $features_list[] = $ft['feature'];
    }
}

$completion_rate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0;
?>

<!-- Optimized CSS: Fits 100% on Screen & Direct Action Controls -->
<style>
    .feature-tracker-container {
        width: 100%;
        max-width: 100%;
    }
    .feature-table-wrapper {
        width: 100%;
        position: relative;
        overflow-x: auto;
    }
    .feature-table {
        width: 100% !important;
        table-layout: fixed;
        border-collapse: collapse;
    }
    .feature-table th, .feature-table td {
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal !important;
        padding: 0.65rem 0.5rem;
    }

    /* Column Width Optimization */
    .col-id { width: 45px; text-align: center; }
    .col-module { width: 14%; }
    .col-feature { width: 28%; }
    .col-status { width: 14%; }
    .col-priority { width: 10%; }
    .col-issues { width: 18%; }
    .col-actions { width: 16%; text-align: center; }

    /* Interactive Row Hover & Click */
    .feature-main-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .feature-main-row:hover {
        background-color: rgba(105, 108, 255, 0.05) !important;
    }

    /* Expandable Detail Drawer */
    .feature-detail-row {
        background-color: #f8f9fa;
        display: none;
    }
    .feature-detail-row.show {
        display: table-row;
    }
    .detail-drawer-box {
        background: #ffffff;
        border: 1px solid #e7e7e8;
        border-left: 4px solid #696cff;
        border-radius: 8px;
        padding: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }

    /* Direct Action Button Styles */
    .action-btn-group {
        display: inline-flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
    }
    .act-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s;
    }
    .act-btn:hover {
        transform: scale(1.1);
    }

    .platform-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.75rem;
    }
    .code-script {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        font-size: 0.72rem;
        background: #f1f2f4;
        padding: 2px 5px;
        border-radius: 4px;
        color: #d63384;
        border: 1px solid #e2e4e8;
        max-width: 100%;
        display: inline-block;
        word-break: break-all;
    }
    .status-select-badge {
        cursor: pointer;
        border: none;
        outline: none;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 3px 6px;
        border-radius: 14px;
        width: 100%;
        text-align: center;
    }
    .status-select-badge:focus {
        box-shadow: 0 0 0 2px rgba(105, 108, 255, 0.3);
    }
    .progress-compact {
        height: 5px;
        border-radius: 6px;
    }
    .expand-icon {
        transition: transform 0.2s ease;
        display: inline-block;
    }
    .expand-icon.rotated {
        transform: rotate(90deg);
    }

    /* Loading Overlay */
    .table-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 8px;
    }
    .table-loading-overlay.active {
        display: flex;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y feature-tracker-container">

    <!-- Page Header & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="ri-dashboard-2-line text-primary fs-3"></i>
                EIMBox Feature & Platform Status Matrix
            </h4>
            <p class="text-muted mb-0 small">
                Dashboard, Console, Android Lite, Android Premium ও Desktop প্ল্যাটফর্মের লাইভ ট্র্যাকিং।
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($stats['total'] == 0): ?>
                <button type="button" class="btn btn-sm btn-outline-warning" id="btnSeedDemo">
                    <i class="ri-magic-line me-1"></i> ডেমো ডাটা লোড করুন
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="ri-printer-line me-1"></i> প্রিন্ট
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                <i class="ri-add-circle-line me-1"></i> নতুন ফিচার যোগ
            </button>
        </div>
    </div>

    <!-- KPI Summary Overview Cards -->
    <div class="row g-2 mb-3">
        <!-- Total Features -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-primary">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">মোট ফিচার</span>
                            <h4 class="fw-bold my-0 text-primary" id="kpi_total"><?= number_format($stats['total']) ?></h4>
                        </div>
                        <span class="badge bg-label-primary rounded-pill small" id="kpi_completion_rate"><?= $completion_rate ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Status -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-success">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Completed</span>
                            <h4 class="fw-bold my-0 text-success" id="kpi_completed"><?= number_format($stats['completed']) ?></h4>
                        </div>
                        <i class="ri-checkbox-circle-line text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Testing -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-info">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Testing</span>
                            <h4 class="fw-bold my-0 text-info" id="kpi_testing"><?= number_format($stats['testing']) ?></h4>
                        </div>
                        <i class="ri-test-tube-line text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Ongoing / Development -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-primary">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Ongoing</span>
                            <h4 class="fw-bold my-0 text-primary" id="kpi_ongoing"><?= number_format($stats['ongoing']) ?></h4>
                        </div>
                        <i class="ri-loader-4-line text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending / Open -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-warning">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Pending/Open</span>
                            <h4 class="fw-bold my-0 text-warning" id="kpi_pending"><?= number_format($stats['open'] + $stats['pending']) ?></h4>
                        </div>
                        <i class="ri-time-line text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Issues / Bugs -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 shadow-sm border-0 border-start border-3 border-danger">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Critical Issues</span>
                            <h4 class="fw-bold my-0 text-danger" id="kpi_critical"><?= number_format($stats['critical_issues']) ?></h4>
                        </div>
                        <i class="ri-alarm-warning-line text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Quick Switcher Bar -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-1">
                    <span class="fw-bold text-dark small"><i class="ri-stack-line text-primary me-1"></i>প্ল্যাটফর্ম:</span>
                </div>
                <div class="d-flex flex-wrap gap-1" id="platformQuickBadges">
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>" onclick="setPlatformFilter('all')">
                        All (<span id="p_count_all"><?= $stats['total'] ?></span>)
                    </button>
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'Dashboard' ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="setPlatformFilter('Dashboard')">
                        <i class="ri-dashboard-line me-1"></i>Dashboard (<span id="p_count_dashboard"><?= $stats['p_dashboard'] ?></span>)
                    </button>
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'Console' ? 'btn-info text-white' : 'btn-outline-info' ?>" onclick="setPlatformFilter('Console')">
                        <i class="ri-terminal-box-line me-1"></i>Console (<span id="p_count_console"><?= $stats['p_console'] ?></span>)
                    </button>
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'Android Lite' ? 'btn-success' : 'btn-outline-success' ?>" onclick="setPlatformFilter('Android Lite')">
                        <i class="ri-android-line me-1"></i>Android Lite (<span id="p_count_android_lite"><?= $stats['p_android_lite'] ?></span>)
                    </button>
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'Android Premium' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>" onclick="setPlatformFilter('Android Premium')">
                        <i class="ri-smartphone-line me-1"></i>Android Premium (<span id="p_count_android_premium"><?= $stats['p_android_premium'] ?></span>)
                    </button>
                    <button type="button" class="btn btn-xs platform-quick-btn <?= $filter_platform === 'Desktop' ? 'btn-secondary text-white' : 'btn-outline-secondary' ?>" onclick="setPlatformFilter('Desktop')">
                        <i class="ri-computer-line me-1"></i>Desktop (<span id="p_count_desktop"><?= $stats['p_desktop'] ?></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Parameter AJAX Filter Form (No Page Reloads) -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3">
            <form id="ajaxFilterForm" onsubmit="event.preventDefault(); fetchFeaturesAjax();">
                <div class="row g-2 align-items-end">
                    <!-- Platform Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">প্ল্যাটফর্ম (Platform)</label>
                        <select name="platform" id="filter_platform" class="form-select form-select-sm" onchange="fetchFeaturesAjax()">
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
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">মডিউল (Module)</label>
                        <select name="module" id="filter_module" class="form-select form-select-sm" onchange="fetchFeaturesAjax(true)">
                            <option value="all" <?= $filter_module === 'all' ? 'selected' : '' ?>>-- All Modules --</option>
                            <?php foreach ($modules_list as $mod): ?>
                                <option value="<?= htmlspecialchars($mod) ?>" <?= $filter_module === $mod ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mod) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Feature Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">ফিচার (Feature)</label>
                        <select name="feature" id="filter_feature" class="form-select form-select-sm" onchange="fetchFeaturesAjax()">
                            <option value="all" <?= $filter_feature === 'all' ? 'selected' : '' ?>>-- All Features --</option>
                            <?php foreach ($features_list as $feat): ?>
                                <option value="<?= htmlspecialchars($feat) ?>" <?= $filter_feature === $feat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($feat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">স্ট্যাটাস (Status)</label>
                        <select name="status" id="filter_status" class="form-select form-select-sm" onchange="fetchFeaturesAjax()">
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
                    <div class="col-6 col-sm-4 col-md-1">
                        <label class="form-label small fw-semibold text-muted mb-1">অগ্রাধিকার</label>
                        <select name="priority" id="filter_priority" class="form-select form-select-sm" onchange="fetchFeaturesAjax()">
                            <option value="all" <?= $filter_priority === 'all' ? 'selected' : '' ?>>All</option>
                            <option value="Critical" <?= $filter_priority === 'Critical' ? 'selected' : '' ?>>🔥 Critical</option>
                            <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>⚡ High</option>
                            <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>🔹 Medium</option>
                            <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>▫️ Low</option>
                        </select>
                    </div>

                    <!-- Live Keyword Search & Reset Button -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">লাইভ সার্চ</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="filter_search" class="form-control" placeholder="কীওয়ার্ড লিখলেই ফিল্টার হবে..." value="<?= htmlspecialchars($filter_search) ?>" oninput="onSearchInputDebounce()">
                            <button class="btn btn-primary" type="button" onclick="fetchFeaturesAjax()" title="খুঁজুন">
                                <i class="ri-search-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="resetFiltersAjax()" title="সব ফিল্টার রিসেট">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Feature Table Card -->
    <div class="card border-0 shadow-sm position-relative">
        <!-- Table Loading Overlay Spinner -->
        <div class="table-loading-overlay" id="tableLoadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">লোড হচ্ছে...</span>
            </div>
        </div>

        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
            <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="ri-list-check-2 text-primary"></i> ফিচার লিস্ট
                <span class="badge bg-label-primary rounded-pill" id="badgeRecordCount"><?= count($features) ?> টি</span>
            </h6>
            <div class="d-flex gap-1 align-items-center">
                <span class="text-muted small me-2 d-none d-sm-inline">
                    <i class="ri-information-line me-1"></i>রো-তে ক্লিক করলে বিস্তারিত ড্রয়ার খুলবে
                </span>
                <button type="button" class="btn btn-xs btn-outline-primary" id="btnExportCsv">
                    <i class="ri-file-excel-2-line me-1"></i> CSV
                </button>
            </div>
        </div>

        <div class="feature-table-wrapper">
            <table class="table table-hover align-middle mb-0 feature-table" id="featuresTable">
                <thead class="table-light">
                    <tr>
                        <th class="col-id">#</th>
                        <th class="col-module">মডিউল / প্ল্যাটফর্ম</th>
                        <th class="col-feature">ফিচার ও স্ক্রিপ্ট</th>
                        <th class="col-status">স্ট্যাটাস</th>
                        <th class="col-priority">অগ্রাধিকার</th>
                        <th class="col-issues">ইস্যু / রেসপন্স</th>
                        <th class="col-actions">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody id="featuresTableBody">
                    <?php render_features_table_body($features, $platforms, $status_meta, $priority_meta); ?>
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
        <div class="modal-content shadow-lg border-0">
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ফিচার নাম (Feature) <span class="text-danger">*</span></label>
                            <input type="text" name="feature" class="form-control" placeholder="যেমন: Student Attendance via QR Code" required>
                        </div>

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

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">টপিক / সাব-টপিক (Topic)</label>
                            <input type="text" name="topic" class="form-control" placeholder="যেমন: Camera QR Scanning & Sync">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">স্ক্রিপ্ট / ফাইল পাথ (Script)</label>
                            <input type="text" name="script" class="form-control font-monospace" placeholder="যেমন: attendance-register.php">
                        </div>

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

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রাধিকার (Priority)</label>
                            <select name="priority" class="form-select">
                                <option value="Critical">🔥 Critical</option>
                                <option value="High">⚡ High</option>
                                <option value="Medium" selected>🔹 Medium</option>
                                <option value="Low">▫️ Low</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">অগ্রগতি শতাংশ (Progress %)</label>
                            <input type="number" name="progress_percent" class="form-control" min="0" max="100" value="0">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">অ্যাসাইন করা হয়েছে (Assigned Developer / QA)</label>
                            <input type="text" name="assigned_to" class="form-control" placeholder="যেমন: Reaz, Dev Team">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-danger"><i class="ri-bug-line me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ (Issues / Bugs)</label>
                            <textarea name="issues" class="form-control" rows="3" placeholder="ফিচারটিতে কী সমস্যা রয়েছে বা কী কী কাজ বাকি আছে বিস্তারিত লিখুন..."></textarea>
                        </div>

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
        <div class="modal-content shadow-lg border-0">
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
        <div class="modal-content shadow-lg border-0">
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
                        <span class="platform-pill bg-label-primary" id="resp_platform_badge">Platform</span>
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
        <div class="modal-content shadow-lg border-0">
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
<!-- CLIENT-SIDE JS HANDLERS (SEAMLESS AJAX WITHOUT PAGE RELOADS) -->
<!-- ============================================================= -->
<script>
    let searchDebounceTimer = null;

    // Real-time Debounce for Keyword Search
    function onSearchInputDebounce() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchFeaturesAjax();
        }, 250);
    }

    // Quick Platform Switcher
    function setPlatformFilter(platform) {
        document.getElementById('filter_platform').value = platform;
        
        // Update active badge styles
        const buttons = document.querySelectorAll('.platform-quick-btn');
        buttons.forEach(btn => {
            btn.classList.remove('btn-primary', 'btn-info', 'btn-success', 'btn-warning', 'btn-secondary');
            if (!btn.classList.contains('btn-outline-secondary') && 
                !btn.classList.contains('btn-outline-primary') && 
                !btn.classList.contains('btn-outline-info') && 
                !btn.classList.contains('btn-outline-success') && 
                !btn.classList.contains('btn-outline-warning')) {
                btn.className = 'btn btn-xs platform-quick-btn btn-outline-secondary';
            }
        });

        fetchFeaturesAjax();
    }

    // Reset All Filters via AJAX
    function resetFiltersAjax() {
        document.getElementById('filter_platform').value = 'all';
        document.getElementById('filter_module').value = 'all';
        document.getElementById('filter_feature').value = 'all';
        document.getElementById('filter_status').value = 'all';
        document.getElementById('filter_priority').value = 'all';
        document.getElementById('filter_search').value = '';

        fetchFeaturesAjax(true);
    }

    // Main AJAX Fetch Function
    function fetchFeaturesAjax(moduleChanged = false) {
        const overlay = document.getElementById('tableLoadingOverlay');
        if (overlay) overlay.classList.add('active');

        const platform = document.getElementById('filter_platform').value;
        const module   = document.getElementById('filter_module').value;
        const feature  = document.getElementById('filter_feature').value;
        const status   = document.getElementById('filter_status').value;
        const priority = document.getElementById('filter_priority').value;
        const search   = document.getElementById('filter_search').value.trim();

        const formData = new FormData();
        formData.append('action', 'fetch_features');
        formData.append('platform', platform);
        formData.append('module', module);
        formData.append('feature', moduleChanged ? 'all' : feature);
        formData.append('status', status);
        formData.append('priority', priority);
        formData.append('search', search);

        // Update URL state without page reload
        const params = new URLSearchParams();
        if (platform !== 'all') params.set('platform', platform);
        if (module !== 'all') params.set('module', module);
        if (!moduleChanged && feature !== 'all') params.set('feature', feature);
        if (status !== 'all') params.set('status', status);
        if (priority !== 'all') params.set('priority', priority);
        if (search !== '') params.set('search', search);
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Update Table HTML
                document.getElementById('featuresTableBody').innerHTML = data.html;
                document.getElementById('badgeRecordCount').textContent = data.count + ' টি';

                // Update Feature Dropdown if module changed or on reload
                if (data.features_list) {
                    const featSelect = document.getElementById('filter_feature');
                    const curVal = moduleChanged ? 'all' : (data.selected_feature || 'all');
                    let optionsHtml = '<option value="all">-- All Features --</option>';
                    data.features_list.forEach(f => {
                        const sel = (f === curVal) ? 'selected' : '';
                        optionsHtml += `<option value="${escapeHtml(f)}" ${sel}>${escapeHtml(f)}</option>`;
                    });
                    featSelect.innerHTML = optionsHtml;
                }

                // Update KPI Cards if stats returned
                if (data.stats) {
                    const total = parseInt(data.stats.total || 0);
                    const comp = parseInt(data.stats.completed || 0);
                    const rate = total > 0 ? Math.round((comp / total) * 100) : 0;
                    
                    if (document.getElementById('kpi_total')) document.getElementById('kpi_total').textContent = total;
                    if (document.getElementById('kpi_completed')) document.getElementById('kpi_completed').textContent = comp;
                    if (document.getElementById('kpi_testing')) document.getElementById('kpi_testing').textContent = data.stats.testing || 0;
                    if (document.getElementById('kpi_ongoing')) document.getElementById('kpi_ongoing').textContent = data.stats.ongoing || 0;
                    if (document.getElementById('kpi_pending')) document.getElementById('kpi_pending').textContent = (parseInt(data.stats.open || 0) + parseInt(data.stats.pending || 0));
                    if (document.getElementById('kpi_critical')) document.getElementById('kpi_critical').textContent = data.stats.critical_issues || 0;
                    if (document.getElementById('kpi_completion_rate')) document.getElementById('kpi_completion_rate').textContent = rate + '%';

                    if (document.getElementById('p_count_all')) document.getElementById('p_count_all').textContent = total;
                    if (document.getElementById('p_count_dashboard')) document.getElementById('p_count_dashboard').textContent = data.stats.p_dashboard || 0;
                    if (document.getElementById('p_count_console')) document.getElementById('p_count_console').textContent = data.stats.p_console || 0;
                    if (document.getElementById('p_count_android_lite')) document.getElementById('p_count_android_lite').textContent = data.stats.p_android_lite || 0;
                    if (document.getElementById('p_count_android_premium')) document.getElementById('p_count_android_premium').textContent = data.stats.p_android_premium || 0;
                    if (document.getElementById('p_count_desktop')) document.getElementById('p_count_desktop').textContent = data.stats.p_desktop || 0;
                }
            } else {
                showToast('ত্রুটি!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('ত্রুটি!', 'সার্ভার থেকে ডাটা আনতে সমস্যা হয়েছে।', 'error');
        })
        .finally(() => {
            if (overlay) overlay.classList.remove('active');
        });
    }

    // Toggle Details Drawer for a Row
    function toggleDrawer(id, event) {
        const drawer = document.getElementById('drawer-' + id);
        const icon = document.getElementById('icon-' + id);
        if (!drawer) return;

        if (drawer.classList.contains('show')) {
            drawer.classList.remove('show');
            if (icon) icon.classList.remove('rotated');
        } else {
            drawer.classList.add('show');
            if (icon) icon.classList.add('rotated');
        }
    }

    // Quick Inline Status Change (Without Page Reload)
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
                fetchFeaturesAjax(); // Silent refresh via AJAX
            } else {
                showToast('ত্রুটি!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('ত্রুটি!', 'সার্ভারে অনুরোধ পাঠানো সম্ভব হয়নি।', 'error');
        });
    }

    // Helper functions for reading record data from buttons
    function openEditModalFromBtn(btn) {
        const data = JSON.parse(btn.getAttribute('data-record'));
        openEditModal(data);
    }

    function openResponseModalFromBtn(btn) {
        const data = JSON.parse(btn.getAttribute('data-record'));
        openResponseModal(data);
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
        
        const platforms = ['Dashboard', 'Console', 'Android Lite', 'Android Premium', 'Desktop'];
        const filtered = platforms.filter(p => p !== currentPlatform);
        if (filtered.length > 0) {
            document.getElementById('dup_target_platform').value = filtered[0];
        }

        const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
        modal.show();
    }

    // Delete Feature Record (Without Page Reload)
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
                fetchFeaturesAjax(); // Silent AJAX refresh
            } else {
                showToast('ত্রুটি!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('ত্রুটি!', 'রেকর্ড মুছতে সমস্যা হয়েছে।', 'error');
        });
    }

    // Copy to Clipboard Helper
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('কপি হয়েছে!', text, 'info');
        }).catch(err => {
            console.error(err);
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Form Submit Listeners (Modal Actions -> AJAX Refresh)
    document.addEventListener('DOMContentLoaded', function() {
        // Add Feature Form Submit
        document.getElementById('addFeatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addFeatureModal'))?.hide();
                    this.reset();
                    fetchFeaturesAjax();
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Edit Feature Form Submit
        document.getElementById('editFeatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editFeatureModal'))?.hide();
                    fetchFeaturesAjax();
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Quick Response Form Submit
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('responseModal'))?.hide();
                    fetchFeaturesAjax();
                } else {
                    showToast('ত্রুটি!', data.message, 'error');
                }
            });
        });

        // Duplicate Form Submit
        document.getElementById('duplicateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('feature-tracker.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('সফল!', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('duplicateModal'))?.hide();
                    fetchFeaturesAjax();
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
                    fetchFeaturesAjax();
                });
            });
        }

        // Export to CSV
        const exportBtn = document.getElementById('btnExportCsv');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                let csv = [];
                const rows = document.querySelectorAll('#featuresTable tr.feature-main-row');
                
                csv.push('"ID","Module","Feature","Platform","Status","Priority","Progress"');
                rows.forEach(r => {
                    let cols = r.querySelectorAll('td');
                    let id = cols[0].innerText.replace(/[^0-9]/g, '');
                    let mod = cols[1].innerText.replace(/\n/g, ' ');
                    let feat = cols[2].innerText.replace(/\n/g, ' ');
                    let status = cols[3].querySelector('select') ? cols[3].querySelector('select').value : cols[3].innerText;
                    let prio = cols[4].innerText.trim();
                    let prog = cols[3].querySelector('.progress-bar') ? cols[3].querySelector('.progress-bar').style.width : '0%';
                    csv.push(`"${id}","${mod}","${feat}","${cols[1].querySelector('.platform-pill')?.innerText || ''}","${status}","${prio}","${prog}"`);
                });
                
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

    // Toast helper
    function showToast(title, message, type) {
        if (window.toastr) {
            if (type === 'success') toastr.success(message, title);
            else if (type === 'error') toastr.error(message, title);
            else toastr.info(message, title);
        } else {
            console.log(title + ': ' + message);
        }
    }
</script>

<?php require_once 'footer.php'; ?>
