<?php
/**
 * EIMBox Multi-Platform Feature & Issue Matrix Tracker
 * File: feature-tracker.php
 * Author: EIMBox Team
 * Purpose: Track features, scripts, pending tasks, issues, developer responses,
 *          and progress across 5 distinct platforms:
 *          1. Dashboard (Web Old Version)
 *          2. Console (Web New Version)
 *          3. Android Lite (Android Lite Version)
 *          4. Premium (Offline Version)
 *          5. Desktop (Windows 10/11 Desktop Version)
 */

// Platform Definitions & Metadata (Bootstrap 5 & Bootstrap Icons)
$platforms_meta = [
    'dashboard' => [
        'key' => 'dashboard',
        'title' => 'Dashboard',
        'sub' => 'Web Old Version',
        'icon' => 'bi bi-speedometer2',
        'badge' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'badge_solid' => 'bg-primary text-white',
        'color' => '#0d6efd'
    ],
    'console' => [
        'key' => 'console',
        'title' => 'Console',
        'sub' => 'Web New Version',
        'icon' => 'bi bi-terminal',
        'badge' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
        'badge_solid' => 'bg-info text-dark',
        'color' => '#0dcaf0'
    ],
    'android_lite' => [
        'key' => 'android_lite',
        'title' => 'Android Lite',
        'sub' => 'Android Lite Version',
        'icon' => 'bi bi-android2',
        'badge' => 'bg-success-subtle text-success border border-success-subtle',
        'badge_solid' => 'bg-success text-white',
        'color' => '#198754'
    ],
    'premium' => [
        'key' => 'premium',
        'title' => 'Premium',
        'sub' => 'Offline Version',
        'icon' => 'bi bi-phone',
        'badge' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'badge_solid' => 'bg-warning text-dark',
        'color' => '#ffc107'
    ],
    'desktop' => [
        'key' => 'desktop',
        'title' => 'Desktop',
        'sub' => 'Win 10/11 Desktop Version',
        'icon' => 'bi bi-display',
        'badge' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        'badge_solid' => 'bg-secondary text-white',
        'color' => '#6c757d'
    ]
];

// Status Definitions & Styling (Bootstrap 5 & Bootstrap Icons)
$status_meta = [
    'Completed' => [
        'title' => 'Completed',
        'badge' => 'bg-success text-white',
        'badge_soft' => 'bg-success-subtle text-success border border-success-subtle',
        'icon' => 'bi bi-check-circle-fill',
        'color' => '#198754',
        'desc' => 'সম্পূর্ণ ও আপডেটেড'
    ],
    'Testing' => [
        'title' => 'Testing',
        'badge' => 'bg-primary text-white',
        'badge_soft' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'icon' => 'bi bi-eyedropper',
        'color' => '#0d6efd',
        'desc' => 'পরীক্ষামূলক চলছে'
    ],
    'Ongoing' => [
        'title' => 'Ongoing',
        'badge' => 'bg-info text-dark',
        'badge_soft' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
        'icon' => 'bi bi-play-circle-fill',
        'color' => '#0dcaf0',
        'desc' => 'কাজ চলমান'
    ],
    'Need Update' => [
        'title' => 'Need Update',
        'badge' => 'bg-warning text-dark',
        'badge_soft' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'icon' => 'bi bi-arrow-repeat',
        'color' => '#ffc107',
        'desc' => 'আপডেট প্রয়োজন'
    ],
    'Issue' => [
        'title' => 'Issue / Bug',
        'badge' => 'bg-danger text-white',
        'badge_soft' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'icon' => 'bi bi-exclamation-triangle-fill',
        'color' => '#dc3545',
        'desc' => 'ইস্যু / বাগ আছে'
    ],
    'Customization' => [
        'title' => 'Customization',
        'badge' => 'bg-dark text-white',
        'badge_soft' => 'bg-dark-subtle text-dark border border-dark-subtle',
        'icon' => 'bi bi-gear-wide-connected',
        'color' => '#212529',
        'desc' => 'ইউজার কাস্টমাইজেশন'
    ],
    'Planned' => [
        'title' => 'Planned',
        'badge' => 'bg-secondary text-white',
        'badge_soft' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        'icon' => 'bi bi-hourglass-split',
        'color' => '#6c757d',
        'desc' => 'পরিকল্পনাধীন'
    ],
    'Not Implemented' => [
        'title' => 'Not Implemented',
        'badge' => 'bg-light text-muted border',
        'badge_soft' => 'bg-light text-muted border',
        'icon' => 'bi bi-dash-circle',
        'color' => '#adb5bd',
        'desc' => 'এখনো তৈরি হয়নি'
    ],
    'On Hold' => [
        'title' => 'On Hold',
        'badge' => 'bg-secondary text-white',
        'badge_soft' => 'bg-secondary-subtle text-secondary border',
        'icon' => 'bi bi-pause-circle-fill',
        'color' => '#6c757d',
        'desc' => 'স্থগিত'
    ]
];

// Priority Styling (Bootstrap 5 & Bootstrap Icons)
$priority_meta = [
    'Critical' => ['badge' => 'badge bg-danger text-white', 'icon' => 'bi bi-shield-fill-exclamation'],
    'High' => ['badge' => 'badge bg-warning text-dark', 'icon' => 'bi bi-arrow-up-circle-fill'],
    'Medium' => ['badge' => 'badge bg-info text-dark', 'icon' => 'bi bi-dash-circle-fill'],
    'Low' => ['badge' => 'badge bg-secondary-subtle text-secondary border', 'icon' => 'bi bi-arrow-down-circle']
];

/**
 * Ensure Schema helper
 */
function ensure_tracker_schema($conn) {
    // 1. Master Features Table
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

    // 2. Platform Status & Tracker Table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `eimbox_platform_tracker` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `feature_id` INT(11) NOT NULL,
          `platform` ENUM('dashboard', 'console', 'android_lite', 'premium', 'desktop') NOT NULL,
          `script_path` VARCHAR(255) DEFAULT NULL,
          `status` ENUM('Not Implemented', 'Planned', 'Ongoing', 'Testing', 'Completed', 'Issue', 'Need Update', 'Customization', 'On Hold') NOT NULL DEFAULT 'Planned',
          `priority` ENUM('Critical', 'High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
          `progress_percent` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
          `issue_notes` TEXT DEFAULT NULL,
          `dev_response` TEXT DEFAULT NULL,
          `estimated_deadline` DATE DEFAULT NULL,
          `assigned_to` VARCHAR(100) DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_feature_platform` (`feature_id`, `platform`),
          KEY `idx_status` (`status`),
          KEY `idx_platform` (`platform`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Auto-sync existing entries from `features` table if missing
    $f_check = $conn->query("SHOW TABLES LIKE 'features'");
    if ($f_check && $f_check->num_rows > 0) {
        $f_res = $conn->query("SELECT * FROM features");
        if ($f_res) {
            while ($f = $f_res->fetch_assoc()) {
                $fname = trim($f['feature_name'] ?? '');
                if (empty($fname)) continue;
                $chk = $conn->query("SELECT id FROM eimbox_features_master WHERE feature_name = '" . $conn->real_escape_string($fname) . "'");
                if ($chk && $chk->num_rows == 0) {
                    $mname = !empty($f['module_name']) ? $f['module_name'] : 'General';
                    $desc = $f['description'] ?? '';
                    $s = $conn->prepare("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES (?, ?, ?)");
                    if ($s) {
                        $s->bind_param("sss", $mname, $fname, $desc);
                        $s->execute();
                        $nid = $s->insert_id;
                        $s->close();
                        $ps = $conn->prepare("INSERT INTO eimbox_platform_tracker (feature_id, platform, status, progress_percent, priority) VALUES (?, ?, 'Planned', 0, 'Medium') ON DUPLICATE KEY UPDATE updated_at = NOW()");
                        if ($ps) {
                            foreach (['dashboard', 'console', 'android_lite', 'premium', 'desktop'] as $pk) {
                                $ps->bind_param("is", $nid, $pk);
                                $ps->execute();
                            }
                            $ps->close();
                        }
                    }
                }
            }
        }
    }
}

/**
 * Render Table Rows for Matrix View (Bootstrap 5 & Bootstrap Icons)
 */
function render_matrix_table_body($rows, $platforms_meta, $status_meta, $priority_meta) {
    if (empty($rows)) {
        ?>
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                <div class="fw-semibold">কোনো ফিচার রেকর্ড পাওয়া যায়নি!</div>
                <small class="text-muted">ফিল্টার পরিবর্তন করুন অথবা নতুন ফিচার যুক্ত করুন।</small>
            </td>
        </tr>
        <?php
        return;
    }

    foreach ($rows as $item):
        $master = $item['master'];
        $platforms_data = $item['platforms'];
        $master_json = htmlspecialchars(json_encode($master), ENT_QUOTES, 'UTF-8');
        $all_platforms_json = htmlspecialchars(json_encode($platforms_data), ENT_QUOTES, 'UTF-8');
        ?>
        <!-- Master Feature Main Row -->
        <tr class="matrix-main-row" id="row-<?= $master['id'] ?>" onclick="toggleMatrixDrawer(<?= $master['id'] ?>, event)">
            <!-- ID & Expand -->
            <td class="col-id text-center" onclick="event.stopPropagation(); toggleMatrixDrawer(<?= $master['id'] ?>, event)">
                <i class="bi bi-chevron-right expand-icon text-muted" id="icon-<?= $master['id'] ?>"></i>
                <span class="d-block small text-muted">#<?= $master['id'] ?></span>
            </td>

            <!-- Module & Feature -->
            <td class="col-feature-info">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 mb-1 font-monospace" style="font-size: 0.72rem;">
                    <?= htmlspecialchars($master['module']) ?>
                </span>
                <div class="fw-bold text-dark feature-title" style="font-size: 0.92rem;">
                    <?= htmlspecialchars($master['feature_name']) ?>
                </div>
                <?php if (!empty($master['description'])): ?>
                    <div class="text-muted small text-truncate-2 mt-1" title="<?= htmlspecialchars($master['description']) ?>">
                        <?= htmlspecialchars($master['description']) ?>
                    </div>
                <?php endif; ?>
            </td>

            <!-- 5 Platform Columns -->
            <?php foreach ($platforms_meta as $pkey => $pinfo): 
                $pdata = $platforms_data[$pkey] ?? [
                    'feature_id' => $master['id'],
                    'platform' => $pkey,
                    'status' => 'Not Implemented',
                    'progress_percent' => 0,
                    'script_path' => '',
                    'issue_notes' => '',
                    'dev_response' => '',
                    'priority' => 'Medium',
                    'assigned_to' => '',
                    'estimated_deadline' => ''
                ];
                $st = $pdata['status'] ?? 'Not Implemented';
                $st_info = $status_meta[$st] ?? $status_meta['Not Implemented'];
                $has_issue = !empty(trim($pdata['issue_notes'] ?? ''));
                $has_resp = !empty(trim($pdata['dev_response'] ?? ''));
                $has_script = !empty(trim($pdata['script_path'] ?? ''));
                $pct = intval($pdata['progress_percent'] ?? 0);
                if ($pct >= 100) {
                    $pct_color = '#198754'; // BS5 Green
                } elseif ($pct >= 60) {
                    $pct_color = '#0dcaf0'; // BS5 Info
                } elseif ($pct >= 25) {
                    $pct_color = '#0d6efd'; // BS5 Primary
                } elseif ($pct > 0) {
                    $pct_color = '#ffc107'; // BS5 Warning
                } else {
                    $pct_color = '#ced4da'; // BS5 Gray
                }
                $p_json = htmlspecialchars(json_encode($pdata), ENT_QUOTES, 'UTF-8');
            ?>
                <td class="col-platform text-center platform-cell" onclick="event.stopPropagation(); openPlatformEditModal(<?= $master['id'] ?>, '<?= $pkey ?>', '<?= htmlspecialchars($master['feature_name'], ENT_QUOTES) ?>', <?= $p_json ?>)">
                    <div class="platform-cell-card <?= $st === 'Issue' ? 'border-danger bg-danger-subtle' : ($st === 'Completed' ? 'border-success' : '') ?>" title="ক্লিক করে <?= htmlspecialchars($pinfo['title']) ?> প্ল্যাটফর্মের স্ট্যাটাস ও তথ্য পরিবর্তন করুন">
                        <!-- 1. Circular Progress Meter with Centered Value -->
                        <div class="circle-progress" style="--pct: <?= $pct ?>; --pcolor: <?= $pct_color ?>;" title="অগ্রগতি: <?= $pct ?>%">
                            <span class="circle-progress-val"><?= $pct ?>%</span>
                        </div>

                        <!-- 2. Status Badge (Under Circular Progress) -->
                        <span class="badge <?= $st_info['badge'] ?> platform-status-badge d-inline-flex align-items-center gap-1" title="স্ট্যাটাস: <?= htmlspecialchars($st) ?>">
                            <i class="<?= $st_info['icon'] ?>" style="font-size: 0.65rem;"></i>
                            <span><?= htmlspecialchars($st) ?></span>
                        </span>

                        <!-- 3. Micro Badges for Issue / Dev Response / Deadline (if any) -->
                        <?php if ($has_issue || $has_resp || !empty($pdata['estimated_deadline'])): ?>
                            <div class="d-flex justify-content-center align-items-center gap-1 mt-1">
                                <?php if ($has_issue): ?>
                                    <span class="badge bg-danger p-1 rounded-circle" title="ইস্যু: <?= htmlspecialchars($pdata['issue_notes']) ?>">
                                        <i class="bi bi-bug-fill text-white" style="font-size: 0.65rem;"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if ($has_resp): ?>
                                    <span class="badge bg-success p-1 rounded-circle" title="রেসপন্স: <?= htmlspecialchars($pdata['dev_response']) ?>">
                                        <i class="bi bi-reply-fill text-white" style="font-size: 0.65rem;"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($pdata['estimated_deadline'])): ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-1 py-0 border" style="font-size: 0.6rem;" title="ডেডলাইন">
                                        <?= date('d M', strtotime($pdata['estimated_deadline'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            <?php endforeach; ?>

            <!-- Action Controls -->
            <td class="col-actions text-center" onclick="event.stopPropagation()">
                <div class="action-btn-group">
                    <!-- Edit Master Feature -->
                    <button type="button" class="btn btn-sm btn-outline-primary act-btn" 
                            title="মূল ফিচার তথ্য এডিট করুন"
                            onclick='openMasterEditModal(<?= $master_json ?>)'>
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <!-- Quick Configure All Platforms for this Feature -->
                    <button type="button" class="btn btn-sm btn-outline-info act-btn" 
                            title="একসাথে ৫টি প্ল্যাটফর্ম কনফিগার করুন"
                            onclick='openBulkPlatformsModal(<?= $master['id'] ?>, "<?= htmlspecialchars($master['feature_name'], ENT_QUOTES) ?>", <?= $all_platforms_json ?>)'>
                        <i class="bi bi-sliders"></i>
                    </button>

                    <!-- Delete Master Feature -->
                    <button type="button" class="btn btn-sm btn-outline-danger act-btn" 
                            title="ফিচার ও এর সব প্ল্যাটফর্ম ডাটা মুছুন"
                            onclick="deleteMasterFeature(<?= $master['id'] ?>, '<?= htmlspecialchars($master['feature_name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        </tr>

        <!-- Expandable Detail Drawer for Master Feature -->
        <tr class="matrix-detail-row" id="drawer-<?= $master['id'] ?>">
            <td colspan="7" class="p-3">
                <div class="detail-drawer-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace me-2"><?= htmlspecialchars($master['module']) ?></span>
                            <strong class="text-primary fs-5"><?= htmlspecialchars($master['feature_name']) ?></strong>
                            <?php if (!empty($master['description'])): ?>
                                <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($master['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-primary" onclick='openBulkPlatformsModal(<?= $master['id'] ?>, "<?= htmlspecialchars($master['feature_name'], ENT_QUOTES) ?>", <?= $all_platforms_json ?>)'>
                                <i class="bi bi-sliders me-1"></i> ৫টি প্ল্যাটফর্ম একসাথে এডিট
                            </button>
                        </div>
                    </div>

                    <!-- 5 Platform Cards Grid -->
                    <div class="row g-3">
                        <?php foreach ($platforms_meta as $pkey => $pinfo): 
                            $pdata = $platforms_data[$pkey] ?? [
                                'feature_id' => $master['id'],
                                'platform' => $pkey,
                                'status' => 'Not Implemented',
                                'progress_percent' => 0,
                                'script_path' => '',
                                'issue_notes' => '',
                                'dev_response' => '',
                                'priority' => 'Medium',
                                'assigned_to' => '',
                                'estimated_deadline' => ''
                            ];
                            $st = $pdata['status'] ?? 'Not Implemented';
                            $st_info = $status_meta[$st] ?? $status_meta['Not Implemented'];
                            $prio_info = $priority_meta[$pdata['priority'] ?? 'Medium'] ?? $priority_meta['Medium'];
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 border platform-drawer-box <?= $st === 'Issue' ? 'border-danger' : '' ?>">
                                    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="<?= $pinfo['icon'] ?> fs-5" style="color: <?= $pinfo['color'] ?>"></i>
                                            <div>
                                                <strong class="text-dark small d-block"><?= htmlspecialchars($pinfo['title']) ?></strong>
                                                <span class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($pinfo['sub']) ?></span>
                                            </div>
                                        </div>
                                        <span class="badge <?= $st_info['badge'] ?>"><?= htmlspecialchars($st) ?></span>
                                    </div>
                                    <div class="card-body p-3">
                                        <!-- Script info -->
                                        <div class="mb-2">
                                            <span class="text-muted small d-block">স্ক্রিপ্ট / ফাইল:</span>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <code class="code-script text-truncate flex-grow-1" title="<?= htmlspecialchars($pdata['script_path'] ?: 'None') ?>">
                                                    <?= htmlspecialchars($pdata['script_path'] ?: 'কোনো স্ক্রিপ্ট নেই') ?>
                                                </code>
                                                <?php if (!empty($pdata['script_path'])): ?>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" title="কপি করুন" onclick="copyToClipboard('<?= htmlspecialchars($pdata['script_path'], ENT_QUOTES) ?>')">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Progress -->
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between small text-muted mb-1">
                                                <span>অগ্রগতি:</span>
                                                <span class="fw-bold text-dark"><?= intval($pdata['progress_percent']) ?>%</span>
                                            </div>
                                            <div class="progress progress-mini bg-light">
                                                <div class="progress-bar <?= $pdata['progress_percent'] == 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= intval($pdata['progress_percent']) ?>%"></div>
                                            </div>
                                        </div>

                                        <!-- Issue Notes -->
                                        <div class="mb-2 p-2 rounded bg-light border">
                                            <strong class="text-danger small d-block mb-1">
                                                <i class="bi bi-bug-fill me-1"></i>ইস্যু / পেন্ডিং কাজ:
                                            </strong>
                                            <div class="text-dark small" style="white-space: pre-wrap; font-size: 0.82rem;">
                                                <?= !empty($pdata['issue_notes']) ? htmlspecialchars($pdata['issue_notes']) : '<span class="text-muted fst-italic">কোনো সমস্যা নেই</span>' ?>
                                            </div>
                                        </div>

                                        <!-- Developer Response -->
                                        <div class="mb-2 p-2 rounded bg-light border">
                                            <strong class="text-success small d-block mb-1">
                                                <i class="bi bi-reply-fill me-1"></i>ডেভেলপার রেসপন্স:
                                            </strong>
                                            <div class="text-dark small" style="white-space: pre-wrap; font-size: 0.82rem;">
                                                <?= !empty($pdata['dev_response']) ? htmlspecialchars($pdata['dev_response']) : '<span class="text-muted fst-italic">এখনও রেসপন্স যুক্ত হয়নি</span>' ?>
                                            </div>
                                        </div>

                                        <!-- Meta tags -->
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 small text-muted pt-2 border-top">
                                            <div>
                                                <span class="<?= $prio_info['badge'] ?> me-1"><?= htmlspecialchars($pdata['priority'] ?? 'Medium') ?></span>
                                                <?php if (!empty($pdata['assigned_to'])): ?>
                                                    <span><i class="bi bi-person"></i> <?= htmlspecialchars($pdata['assigned_to']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="openPlatformEditModal(<?= $master['id'] ?>, '<?= $pkey ?>', '<?= htmlspecialchars($master['feature_name'], ENT_QUOTES) ?>', <?= $p_json ?>)">
                                                <i class="bi bi-pencil-square me-1"></i> এডিট
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
    ensure_tracker_schema($conn);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $user_name = $_SESSION['user'] ?? $_SESSION['user_name'] ?? 'Admin';

    // 1.0 FETCH MATRIX DATA
    if ($action === 'fetch_matrix_data' || (isset($_GET['ajax']) && $_GET['ajax'] == 1)) {
        $f_module   = $_POST['module'] ?? $_GET['module'] ?? 'all';
        $f_status   = $_POST['status'] ?? $_GET['status'] ?? 'all';
        $f_platform = $_POST['platform'] ?? $_GET['platform'] ?? 'all';
        $f_priority = $_POST['priority'] ?? $_GET['priority'] ?? 'all';
        $f_search   = trim($_POST['search'] ?? $_GET['search'] ?? '');
        $f_issues_only = intval($_POST['issues_only'] ?? $_GET['issues_only'] ?? 0);

        // Build search & filter clauses for master table
        $master_where = [];
        if ($f_module !== 'all' && !empty($f_module)) {
            $clean_m = $conn->real_escape_string($f_module);
            $master_where[] = "m.module = '$clean_m'";
        }

        if (!empty($f_search)) {
            $clean_s = $conn->real_escape_string($f_search);
            $master_where[] = "(m.module LIKE '%$clean_s%' OR m.feature_name LIKE '%$clean_s%' OR m.description LIKE '%$clean_s%' OR t.script_path LIKE '%$clean_s%' OR t.issue_notes LIKE '%$clean_s%' OR t.dev_response LIKE '%$clean_s%')";
        }

        if ($f_status !== 'all' && !empty($f_status)) {
            $clean_st = $conn->real_escape_string($f_status);
            $master_where[] = "t.status = '$clean_st'";
        }

        if ($f_platform !== 'all' && !empty($f_platform)) {
            $clean_p = $conn->real_escape_string($f_platform);
            $master_where[] = "t.platform = '$clean_p'";
        }

        if ($f_priority !== 'all' && !empty($f_priority)) {
            $clean_pr = $conn->real_escape_string($f_priority);
            $master_where[] = "t.priority = '$clean_pr'";
        }

        if ($f_issues_only == 1) {
            $master_where[] = "(t.status = 'Issue' OR (t.issue_notes IS NOT NULL AND TRIM(t.issue_notes) != ''))";
        }

        $where_sql = count($master_where) > 0 ? "WHERE " . implode(" AND ", $master_where) : "";

        // Query distinct master feature IDs that match filter
        $query_ids = "SELECT DISTINCT m.id, m.module, m.display_order, m.feature_name 
                      FROM eimbox_features_master m 
                      LEFT JOIN eimbox_platform_tracker t ON m.id = t.feature_id 
                      $where_sql 
                      ORDER BY m.module ASC, m.display_order ASC, m.feature_name ASC";
        $res_ids = $conn->query($query_ids);
        $master_ids = [];
        if ($res_ids) {
            while ($r = $res_ids->fetch_assoc()) {
                $master_ids[] = intval($r['id']);
            }
        }

        $matrix_data = [];
        if (!empty($master_ids)) {
            $ids_str = implode(',', $master_ids);
            // Fetch masters
            $masters_res = $conn->query("SELECT * FROM eimbox_features_master WHERE id IN ($ids_str) ORDER BY module ASC, display_order ASC, feature_name ASC");
            $masters_map = [];
            while ($m = $masters_res->fetch_assoc()) {
                $masters_map[$m['id']] = $m;
            }

            // Fetch platforms data for these masters
            $platforms_res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id IN ($ids_str)");
            $platforms_by_feature = [];
            while ($pt = $platforms_res->fetch_assoc()) {
                $platforms_by_feature[$pt['feature_id']][$pt['platform']] = $pt;
            }

            foreach ($master_ids as $mid) {
                if (isset($masters_map[$mid])) {
                    $matrix_data[] = [
                        'master' => $masters_map[$mid],
                        'platforms' => $platforms_by_feature[$mid] ?? []
                    ];
                }
            }
        }

        // Global Statistics
        $stats_q = $conn->query("
            SELECT 
                (SELECT COUNT(*) FROM eimbox_features_master) as total_features,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Completed') as total_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Issue' OR (issue_notes IS NOT NULL AND TRIM(issue_notes) != '')) as total_issues,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Ongoing') as total_ongoing,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Customization') as total_customization,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'dashboard' AND status = 'Completed') as p_dashboard_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'dashboard') as p_dashboard_total,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'console' AND status = 'Completed') as p_console_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'console') as p_console_total,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'android_lite' AND status = 'Completed') as p_android_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'android_lite') as p_android_total,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'premium' AND status = 'Completed') as p_premium_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'premium') as p_premium_total,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'desktop' AND status = 'Completed') as p_desktop_completed,
                (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'desktop') as p_desktop_total
        ");
        $stats_data = $stats_q ? $stats_q->fetch_assoc() : [];

        // Dynamic modules list from modulelist table
        $mod_list = [];
        $m_res = $conn->query("SELECT module_name, is_public, core FROM modulelist WHERE module_name IS NOT NULL AND TRIM(module_name) != '' ORDER BY slno ASC, module_name ASC");
        if ($m_res && $m_res->num_rows > 0) {
            while ($mrow = $m_res->fetch_assoc()) {
                $mod_list[] = $mrow['module_name'];
            }
        } else {
            $m_res2 = $conn->query("SELECT DISTINCT module FROM eimbox_features_master ORDER BY module ASC");
            if ($m_res2) {
                while ($mrow = $m_res2->fetch_assoc()) {
                    if (!empty($mrow['module'])) $mod_list[] = $mrow['module'];
                }
            }
        }

        // Render HTML for matrix table
        ob_start();
        render_matrix_table_body($matrix_data, $platforms_meta, $status_meta, $priority_meta);
        $html_output = ob_get_clean();

        echo json_encode([
            'status' => 'success',
            'count' => count($matrix_data),
            'html' => $html_output,
            'modules_list' => $mod_list,
            'selected_module' => $f_module,
            'stats' => $stats_data
        ]);
        exit;
    }

    // 1.1 ADD MASTER FEATURE (and optionally init platforms)
    if ($action === 'add_master_feature') {
        $module = trim($_POST['module'] ?? '');
        $feature_name = trim($_POST['feature_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Core');

        if (empty($module) || empty($feature_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Module এবং Feature Name উভয় ফিল্ডই আবশ্যক!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO eimbox_features_master (module, feature_name, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $module, $feature_name, $description, $category);
        
        if ($stmt->execute()) {
            $new_feature_id = $stmt->insert_id;
            $stmt->close();

            // Auto-initialize 5 platforms for this master feature with Planned status
            $init_stmt = $conn->prepare("INSERT INTO eimbox_platform_tracker (feature_id, platform, status, progress_percent, priority) VALUES (?, ?, 'Planned', 0, 'Medium') ON DUPLICATE KEY UPDATE updated_at = NOW()");
            foreach (array_keys($platforms_meta) as $pkey) {
                $init_stmt->bind_param("is", $new_feature_id, $pkey);
                $init_stmt->execute();
            }
            $init_stmt->close();

            echo json_encode(['status' => 'success', 'message' => 'নতুন ফিচার সফলভাবে তৈরি হয়েছে এবং ৫টি প্ল্যাটফর্ম ইনিশিয়ালাইজ করা হয়েছে!', 'id' => $new_feature_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ডাটাবেজে সেভ করতে সমস্যা হয়েছে: ' . $conn->error]);
        }
        exit;
    }

    // 1.2 EDIT MASTER FEATURE
    if ($action === 'edit_master_feature') {
        $id = intval($_POST['id'] ?? 0);
        $module = trim($_POST['module'] ?? '');
        $feature_name = trim($_POST['feature_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Core');

        if ($id <= 0 || empty($module) || empty($feature_name)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ আইডি বা ফিল্ড খালি আছে!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE eimbox_features_master SET module=?, feature_name=?, description=?, category=? WHERE id=?");
        $stmt->bind_param("ssssi", $module, $feature_name, $description, $category, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'মূল ফিচার তথ্য সফলভাবে আপডেট হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট করতে ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.3 UPDATE SPECIFIC PLATFORM STATUS / SCRIPT / ISSUE / RESPONSE
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

        if ($feature_id <= 0 || !isset($platforms_meta[$platform])) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ ফিচার আইডি অথবা প্ল্যাটফর্ম!']);
            exit;
        }

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
            echo json_encode(['status' => 'success', 'message' => "প্ল্যাটফর্ম '$platform'-এর স্ট্যাটাস ও ইস্যু নোট আপডেট সম্পন্ন হয়েছে!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'আপডেট করতে ব্যর্থ হয়েছে: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.4 GET PLATFORM DATA FOR MODAL
    if ($action === 'get_platform_details') {
        $feature_id = intval($_GET['feature_id'] ?? $_POST['feature_id'] ?? 0);
        $platform = trim($_GET['platform'] ?? $_POST['platform'] ?? '');

        if ($feature_id <= 0 || empty($platform)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ রিকোয়েস্ট!']);
            exit;
        }

        $m = $conn->query("SELECT * FROM eimbox_features_master WHERE id = $feature_id")->fetch_assoc();
        $pt = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id = $feature_id AND platform = '$platform'")->fetch_assoc();

        if (!$pt) {
            $pt = [
                'feature_id' => $feature_id,
                'platform' => $platform,
                'status' => 'Planned',
                'progress_percent' => 0,
                'script_path' => '',
                'issue_notes' => '',
                'dev_response' => '',
                'priority' => 'Medium',
                'assigned_to' => '',
                'estimated_deadline' => ''
            ];
        }

        echo json_encode([
            'status' => 'success',
            'master' => $m,
            'platform_data' => $pt
        ]);
        exit;
    }

    // 1.5 BULK UPDATE ALL 5 PLATFORMS AT ONCE
    if ($action === 'bulk_update_platforms') {
        $feature_id = intval($_POST['feature_id'] ?? 0);
        $platforms_input = $_POST['platforms'] ?? [];

        if ($feature_id <= 0 || !is_array($platforms_input)) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ ডাটা প্রেরিত হয়েছে!']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO eimbox_platform_tracker (feature_id, platform, script_path, status, priority, progress_percent, issue_notes, dev_response, assigned_to)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                script_path = VALUES(script_path),
                status = VALUES(status),
                priority = VALUES(priority),
                progress_percent = VALUES(progress_percent),
                issue_notes = VALUES(issue_notes),
                dev_response = VALUES(dev_response),
                assigned_to = VALUES(assigned_to),
                updated_at = NOW()
        ");

        foreach ($platforms_input as $pkey => $pdata) {
            if (!isset($platforms_meta[$pkey])) continue;
            $script = trim($pdata['script_path'] ?? '');
            $st = trim($pdata['status'] ?? 'Planned');
            $prio = trim($pdata['priority'] ?? 'Medium');
            $prog = intval($pdata['progress_percent'] ?? 0);
            $iss = trim($pdata['issue_notes'] ?? '');
            $resp = trim($pdata['dev_response'] ?? '');
            $assign = trim($pdata['assigned_to'] ?? '');

            if ($st === 'Completed' && $prog < 100) $prog = 100;

            $stmt->bind_param("issssisss", $feature_id, $pkey, $script, $st, $prio, $prog, $iss, $resp, $assign);
            $stmt->execute();
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => '৫টি প্ল্যাটফর্মের তথ্য একসাথে সফলভাবে আপডেট হয়েছে!']);
        exit;
    }

    // 1.6 DELETE MASTER FEATURE
    if ($action === 'delete_master_feature') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'অবৈধ আইডি!']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM eimbox_features_master WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'ফিচার ও এর সাথে যুক্ত সকল প্ল্যাটফর্ম রেকর্ড মুছে ফেলা হয়েছে!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'মুছে ফেলতে ব্যর্থ: ' . $conn->error]);
        }
        $stmt->close();
        exit;
    }

    // 1.7 SEED DEFAULT DEMO DATA (If tables are empty)
    if ($action === 'seed_default_data') {
        $cnt = $conn->query("SELECT COUNT(*) as c FROM eimbox_features_master")->fetch_assoc()['c'];
        if ($cnt == 0) {
            $demo_features = [
                [
                    'module' => 'Attendance',
                    'feature' => 'Daily Student Attendance',
                    'desc' => 'শ্রেণি ও শাখা ভিত্তিক শিক্ষার্থীদের দৈনিক উপস্থিতি গ্রহণ ও SMS নোটিফিকেশন',
                    'platforms' => [
                        'dashboard' => ['script' => 'attendance-register.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Bulk attendance with fast ajax save implemented.', 'prio' => 'High', 'assign' => 'Reaz'],
                        'console' => ['script' => 'views/attendance/live-stream.php', 'status' => 'Testing', 'progress' => 90, 'issue' => 'Needs real-time socket sync with biometric punch.', 'resp' => 'Webhook listener ready.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'android_lite' => ['script' => 'lib/screens/attendance_quick.dart', 'status' => 'Issue', 'progress' => 65, 'issue' => 'SQLite offline sync conflict when back online.', 'resp' => 'Queue sync retry added.', 'prio' => 'Critical', 'assign' => 'Reaz'],
                        'premium' => ['script' => 'lib/offline/attendance_offline_engine.dart', 'status' => 'Ongoing', 'progress' => 50, 'issue' => 'Fingerprint USB OTG driver support for Morpho.', 'resp' => 'Testing Java JNI wrapper.', 'prio' => 'Medium', 'assign' => 'Dev Team'],
                        'desktop' => ['script' => 'AttendanceDesktopSync.exe / C# App', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'ZKTeco Push SDK integrated smoothly.', 'prio' => 'High', 'assign' => 'Dev Team']
                    ]
                ],
                [
                    'module' => 'Accounts & POS',
                    'feature' => 'Student Fee Collection & Receipt',
                    'desc' => 'ফি আদায়, বকেয়া হিসাব, রসিদ প্রিন্ট এবং অনলাইন পেমেন্ট গেটওয়ে (bKash/PGW)',
                    'platforms' => [
                        'dashboard' => ['script' => 'payments-collection.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => '80mm POS Thermal receipt print format verified.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'console' => ['script' => 'controllers/accounts/pos-engine.php', 'status' => 'Testing', 'progress' => 85, 'issue' => 'Customization request: Discount voucher code system.', 'resp' => 'UI model added.', 'prio' => 'Medium', 'assign' => 'Reaz'],
                        'android_lite' => ['script' => 'lib/screens/fee_collection_mobile.dart', 'status' => 'Need Update', 'progress' => 80, 'issue' => 'Bluetooth POS printer font layout truncation in Bangla.', 'resp' => 'Switched to raster bitmap mode.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'premium' => ['script' => 'lib/screens/finance_offline_pos.dart', 'status' => 'Ongoing', 'progress' => 45, 'issue' => 'Encrypted local SQLite audit database required.', 'resp' => 'SQLCipher integrated.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'desktop' => ['script' => 'AccountsPOSModule.exe', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Multi-counter cash drawer kick pulse working.', 'prio' => 'Medium', 'assign' => 'Reaz']
                    ]
                ],
                [
                    'module' => 'Exam & Result',
                    'feature' => 'OMR Sheet Scanner & Processing',
                    'desc' => 'মডেল টেস্ট ও চূড়ান্ত পরীক্ষার OMR শিট অটোম্যাটিক স্ক্যান ও রেজাল্ট প্রসেসিং',
                    'platforms' => [
                        'dashboard' => ['script' => 'omr-mapping.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Web upload & batch verification complete.', 'prio' => 'High', 'assign' => 'Reaz'],
                        'console' => ['script' => 'api/v1/omr_processor_api.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Fast parallel batch processing endpoint.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'android_lite' => ['script' => 'lib/camera/omr_camera_scanner.dart', 'status' => 'Issue', 'progress' => 70, 'issue' => 'Low-light mobile camera edge detection skewed.', 'resp' => 'OpenCV perspective transform algorithm updated.', 'prio' => 'Critical', 'assign' => 'Reaz'],
                        'premium' => ['script' => 'lib/screens/omr_bulk_importer.dart', 'status' => 'Planned', 'progress' => 10, 'issue' => 'Bulk folder watcher implementation pending.', 'resp' => '', 'prio' => 'Low', 'assign' => 'Dev Team'],
                        'desktop' => ['script' => 'EIMBoxOMREngine.exe (C++ OpenCV)', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'High-speed flatbed ADF scanner support verified.', 'prio' => 'Critical', 'assign' => 'Reaz']
                    ]
                ],
                [
                    'module' => 'Analytics',
                    'feature' => 'Executive Performance Matrix',
                    'desc' => 'শিক্ষক, বিষয় ও শ্রেণিভিত্তিক TPI, TIA, SPI ও At-Risk শিক্ষার্থী সনাক্তকরণ',
                    'platforms' => [
                        'dashboard' => ['script' => 'analytics/teacher_subject_matrix.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Calculation engines 1-12 fully tuned.', 'prio' => 'High', 'assign' => 'Reaz'],
                        'console' => ['script' => 'views/analytics/executive_dashboard.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'ApexCharts live dynamic rendering.', 'prio' => 'Medium', 'assign' => 'Dev Team'],
                        'android_lite' => ['script' => 'lib/screens/principal_kpi_cards.dart', 'status' => 'Testing', 'progress' => 90, 'issue' => 'Need push notification on daily attendance dip.', 'resp' => 'FCM trigger logic connected.', 'prio' => 'Medium', 'assign' => 'Dev Team'],
                        'premium' => ['script' => 'lib/screens/offline_analytics_reports.dart', 'status' => 'Ongoing', 'progress' => 40, 'issue' => 'Local caching of 3-year historical comparative datasets.', 'resp' => 'Schema indexing ongoing.', 'prio' => 'Low', 'assign' => 'Dev Team'],
                        'desktop' => ['script' => 'ReportsViewer.exe', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Fast crystal report PDF export.', 'prio' => 'Medium', 'assign' => 'Dev Team']
                    ]
                ],
                [
                    'module' => 'Communication',
                    'feature' => 'SMS Gateway & Push Alerts',
                    'desc' => 'বাল্ক মাস্কিং SMS, অটোমেটেড ফি নোটিশ, এবং গার্ডিয়ান অ্যাপ নোটিফিকেশন',
                    'platforms' => [
                        'dashboard' => ['script' => 'sms-gateway.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Multi-operator gateway fallbacks added.', 'prio' => 'High', 'assign' => 'Reaz'],
                        'console' => ['script' => 'cron/sms_queue_worker.php', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Auto-retry mechanism with exponential backoff.', 'prio' => 'High', 'assign' => 'Dev Team'],
                        'android_lite' => ['script' => 'lib/services/fcm_push_handler.dart', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Background message channel active.', 'prio' => 'Medium', 'assign' => 'Dev Team'],
                        'premium' => ['script' => 'lib/services/offline_sms_modem.dart', 'status' => 'Need Update', 'progress' => 60, 'issue' => 'GSM SIM800C modem AT command timeout on USB hub.', 'resp' => 'Baud rate auto-negotiation added.', 'prio' => 'Medium', 'assign' => 'Dev Team'],
                        'desktop' => ['script' => 'SMSBroadcastTool.exe', 'status' => 'Completed', 'progress' => 100, 'issue' => '', 'resp' => 'Direct COM port modem support active.', 'prio' => 'Medium', 'assign' => 'Dev Team']
                    ]
                ]
            ];

            foreach ($demo_features as $df) {
                $stmt = $conn->prepare("INSERT INTO eimbox_features_master (module, feature_name, description) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $df['module'], $df['feature'], $df['desc']);
                $stmt->execute();
                $fid = $stmt->insert_id;
                $stmt->close();

                $pt_stmt = $conn->prepare("INSERT INTO eimbox_platform_tracker (feature_id, platform, script_path, status, progress_percent, issue_notes, dev_response, priority, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($df['platforms'] as $pkey => $pdata) {
                    $pt_stmt->bind_param("isssissss", $fid, $pkey, $pdata['script'], $pdata['status'], $pdata['progress'], $pdata['issue'], $pdata['resp'], $pdata['prio'], $pdata['assign']);
                    $pt_stmt->execute();
                }
                $pt_stmt->close();
            }

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
ensure_tracker_schema($conn);

// Fetch Initial Statistics
$stats_q = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM eimbox_features_master) as total_features,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Completed') as total_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Issue' OR (issue_notes IS NOT NULL AND TRIM(issue_notes) != '')) as total_issues,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Ongoing') as total_ongoing,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE status = 'Customization') as total_customization,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'dashboard' AND status = 'Completed') as p_dashboard_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'dashboard') as p_dashboard_total,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'console' AND status = 'Completed') as p_console_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'console') as p_console_total,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'android_lite' AND status = 'Completed') as p_android_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'android_lite') as p_android_total,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'premium' AND status = 'Completed') as p_premium_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'premium') as p_premium_total,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'desktop' AND status = 'Completed') as p_desktop_completed,
        (SELECT COUNT(*) FROM eimbox_platform_tracker WHERE platform = 'desktop') as p_desktop_total
");
$stats = $stats_q ? $stats_q->fetch_assoc() : [
    'total_features' => 0, 'total_completed' => 0, 'total_issues' => 0, 'total_ongoing' => 0, 'total_customization' => 0,
    'p_dashboard_completed' => 0, 'p_dashboard_total' => 0,
    'p_console_completed' => 0, 'p_console_total' => 0,
    'p_android_completed' => 0, 'p_android_total' => 0,
    'p_premium_completed' => 0, 'p_premium_total' => 0,
    'p_desktop_completed' => 0, 'p_desktop_total' => 0
];

// Fetch System Modules from `modulelist` table
$modules_list = [];
$mod_res = $conn->query("SELECT id, slno, module_name, module_icon, descrip, is_public, core FROM modulelist WHERE module_name IS NOT NULL AND TRIM(module_name) != '' ORDER BY slno ASC, module_name ASC");
if ($mod_res && $mod_res->num_rows > 0) {
    while ($m = $mod_res->fetch_assoc()) {
        $modules_list[] = $m;
    }
} else {
    $mod_res2 = $conn->query("SELECT DISTINCT module as module_name FROM eimbox_features_master ORDER BY module ASC");
    if ($mod_res2) {
        while ($m = $mod_res2->fetch_assoc()) {
            $modules_list[] = [
                'id' => 0, 'slno' => 99, 'module_name' => $m['module_name'], 'module_icon' => 'circle-square', 'descrip' => '', 'is_public' => 1, 'core' => 0
            ];
        }
    }
}

// Fetch Initial Matrix Data on Server-Side Load (Immediate render on page load)
$init_master_ids = [];
$res_init = $conn->query("SELECT id FROM eimbox_features_master ORDER BY module ASC, display_order ASC, feature_name ASC");
if ($res_init) {
    while ($r = $res_init->fetch_assoc()) {
        $init_master_ids[] = intval($r['id']);
    }
}
$initial_matrix_data = [];
if (!empty($init_master_ids)) {
    $ids_str = implode(',', $init_master_ids);
    $masters_res = $conn->query("SELECT * FROM eimbox_features_master WHERE id IN ($ids_str) ORDER BY module ASC, display_order ASC, feature_name ASC");
    $masters_map = [];
    while ($m = $masters_res->fetch_assoc()) {
        $masters_map[$m['id']] = $m;
    }
    $platforms_res = $conn->query("SELECT * FROM eimbox_platform_tracker WHERE feature_id IN ($ids_str)");
    $platforms_by_feature = [];
    while ($pt = $platforms_res->fetch_assoc()) {
        $platforms_by_feature[$pt['feature_id']][$pt['platform']] = $pt;
    }
    foreach ($init_master_ids as $mid) {
        if (isset($masters_map[$mid])) {
            $initial_matrix_data[] = [
                'master' => $masters_map[$mid],
                'platforms' => $platforms_by_feature[$mid] ?? []
            ];
        }
    }
}
?>

<!-- Optimized Modern CSS for Multi-Platform Matrix Tracker -->
<style>
    .matrix-tracker-container {
        /* width: 100%; */
        /* max-width: 1220px; */
        margin: 0 auto;
    }

    /* KPI Summary Cards */
    .kpi-card {
        border-radius: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .platform-kpi-chip {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 6px;
        background: #fff;
        border: 1px solid #e7e7e8;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Table Architecture */
   
    /* Column Widths */
    .col-id {  }
    .col-feature-info { }
    .col-platform { width: 13.8%; }
    .col-actions { }

    /* Interactive Row Hover */
    .matrix-main-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .matrix-main-row:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
    }

    /* Platform Cell Card inside Table */
    .platform-cell-card {
        background: #fafafa;
        border: 1px solid #e4e6eb;
        border-radius: 6px;
        padding: 4px 2px;
        min-height: 58px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .platform-cell-card:hover {
        background: #ffffff;
        border-color: #696cff;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.15);
        transform: translateY(-1px);
    }
    .platform-status-badge {
        font-size: 0.62rem;
        padding: 1px 5px;
        border-radius: 3px;
        font-weight: 600;
        white-space: nowrap;
        line-height: 1.15;
    }

    /* Circular Progress Meter */
    .circle-progress {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: conic-gradient(var(--pcolor, #696cff) calc(var(--pct, 0) * 1%), #e7e7e8 0);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    .circle-progress::before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #ffffff;
    }
    .circle-progress-val {
        position: relative;
        font-size: 0.55rem;
        font-weight: 700;
        line-height: 1;
        color: #384551;
        letter-spacing: -0.4px;
    }

    /* Mini Progress Bar (for Drawer) */
    .progress-mini {
        height: 5px;
        border-radius: 3px;
    }
    .progress-num {
        font-size: 0.68rem;
        color: #8592a3;
        font-weight: 600;
    }

    /* Expandable Drawer Row */
    .matrix-detail-row {
        background-color: #f8f9fa;
        display: none;
    }
    .matrix-detail-row.show {
        display: table-row;
    }
    .detail-drawer-card {
        background: #ffffff;
        border: 1px solid #d9dee3;
        border-left: 4px solid #696cff;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .platform-drawer-box {
        border-radius: 6px;
        transition: box-shadow 0.2s;
    }
    .platform-drawer-box:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Action Buttons */
    .act-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 0.95rem;
    }
    .action-btn-group {
        display: inline-flex;
        gap: 3px;
        align-items: center;
        justify-content: center;
    }
    .btn-xs {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
        border-radius: 4px;
    }

    .code-script {
        background: #f1f2f4;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.75rem;
        color: #566a7f;
        border: 1px solid #e0e2e6;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y matrix-tracker-container">

    <!-- Page Header & Action Bar (Bootstrap 5) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>EIMBox Multi-Platform Feature & Issue Tracker
            </h4>
            <p class="text-muted mb-0 small">
                Dashboard, Console, Android Lite, Offline Premium এবং Desktop ভার্সনের ফিচার, ইস্যু ও অগ্রগতির সেন্ট্রালাইজড প্যানেল
            </p>
        </div>
        <div class="d-flex gap-2">
            <!-- Show only issues toggle -->
            <button type="button" class="btn btn-outline-danger btn-sm" id="btn-toggle-issues" onclick="toggleIssuesOnly()">
                <i class="bi bi-bug-fill me-1"></i> শুধু ইস্যু ও সমস্যাসমূহ (<span id="stat-issues-count"><?= $stats['total_issues'] ?></span>)
            </button>
            <!-- Add New Master Feature -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openMasterAddModal()">
                <i class="bi bi-plus-lg me-1"></i> নতুন ফিচার যুক্ত করুন
            </button>
        </div>
    </div>

    <!-- 1. Platform Health & Summary Counters -->
    <div class="row g-2 mb-3">
        <!-- Dashboard (Old) -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card shadow-sm border-0" onclick="filterByPlatform('dashboard')">
                <div class="d-flex align-items-center justify-content-center gap-1 text-primary fw-semibold small">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </div>
                <div class="fs-5 fw-bold text-dark mt-1" id="stat-p-dashboard">
                    <?= $stats['p_dashboard_total'] > 0 ? round(($stats['p_dashboard_completed'] / $stats['p_dashboard_total']) * 100) : 0 ?>%
                </div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $stats['p_dashboard_completed'] ?>/<?= $stats['p_dashboard_total'] ?> Complete</small>
            </div>
        </div>

        <!-- Console (New) -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card shadow-sm border-0" onclick="filterByPlatform('console')">
                <div class="d-flex align-items-center justify-content-center gap-1 text-info fw-semibold small">
                    <i class="bi bi-terminal"></i> Console
                </div>
                <div class="fs-5 fw-bold text-dark mt-1" id="stat-p-console">
                    <?= $stats['p_console_total'] > 0 ? round(($stats['p_console_completed'] / $stats['p_console_total']) * 100) : 0 ?>%
                </div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $stats['p_console_completed'] ?>/<?= $stats['p_console_total'] ?> Complete</small>
            </div>
        </div>

        <!-- Android Lite -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card shadow-sm border-0" onclick="filterByPlatform('android_lite')">
                <div class="d-flex align-items-center justify-content-center gap-1 text-success fw-semibold small">
                    <i class="bi bi-android2"></i> Android Lite
                </div>
                <div class="fs-5 fw-bold text-dark mt-1" id="stat-p-android">
                    <?= $stats['p_android_total'] > 0 ? round(($stats['p_android_completed'] / $stats['p_android_total']) * 100) : 0 ?>%
                </div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $stats['p_android_completed'] ?>/<?= $stats['p_android_total'] ?> Complete</small>
            </div>
        </div>

        <!-- Premium (Offline) -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card shadow-sm border-0" onclick="filterByPlatform('premium')">
                <div class="d-flex align-items-center justify-content-center gap-1 text-warning fw-semibold small">
                    <i class="bi bi-phone"></i> Premium
                </div>
                <div class="fs-5 fw-bold text-dark mt-1" id="stat-p-premium">
                    <?= $stats['p_premium_total'] > 0 ? round(($stats['p_premium_completed'] / $stats['p_premium_total']) * 100) : 0 ?>%
                </div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $stats['p_premium_completed'] ?>/<?= $stats['p_premium_total'] ?> Complete</small>
            </div>
        </div>

        <!-- Desktop (Win) -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card shadow-sm border-0" onclick="filterByPlatform('desktop')">
                <div class="d-flex align-items-center justify-content-center gap-1 text-secondary fw-semibold small">
                    <i class="bi bi-display"></i> Desktop
                </div>
                <div class="fs-5 fw-bold text-dark mt-1" id="stat-p-desktop">
                    <?= $stats['p_desktop_total'] > 0 ? round(($stats['p_desktop_completed'] / $stats['p_desktop_total']) * 100) : 0 ?>%
                </div>
                <small class="text-muted" style="font-size: 0.7rem;"><?= $stats['p_desktop_completed'] ?>/<?= $stats['p_desktop_total'] ?> Complete</small>
            </div>
        </div>

        <!-- Total Features & Quick Reset -->
        <div class="col-md-4 col-lg-2">
            <div class="card p-2 text-center kpi-card bg-primary text-white shadow-sm border-0" onclick="resetFilters()">
                <div class="fw-semibold small"><i class="bi bi-list-task"></i> All Features</div>
                <div class="fs-5 fw-bold mt-1" id="stat-total-features"><?= $stats['total_features'] ?></div>
                <small style="font-size: 0.7rem;" class="text-white-50">ক্লিক করে রিসেট করুন</small>
            </div>
        </div>
    </div>

    <!-- 2. Interactive Filter & Live Search Toolbar -->
    <div class="card mb-3 shadow-none border">
        <div class="card-body p-2">
            <div class="row g-2 align-items-center">
                <!-- Search -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" id="filter-search" placeholder="ফিচার, স্ক্রিপ্ট বা সমস্যা খুঁজুন..." oninput="debounceFetch()">
                    </div>
                </div>

                <!-- Module Dropdown -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter-module" onchange="fetchMatrixData()">
                        <option value="all">📁 সকল মডিউল</option>
                        <?php foreach ($modules_list as $mod): ?>
                            <option value="<?= htmlspecialchars($mod['module_name']) ?>">
                                <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '★' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Platform Filter -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter-platform" onchange="fetchMatrixData()">
                        <option value="all">🌐 সকল প্ল্যাটফর্ম</option>
                        <?php foreach ($platforms_meta as $pkey => $pinfo): ?>
                            <option value="<?= $pkey ?>"><?= $pinfo['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter-status" onchange="fetchMatrixData()">
                        <option value="all">⚡ সকল স্ট্যাটাস</option>
                        <?php foreach ($status_meta as $skey => $sinfo): ?>
                            <option value="<?= $skey ?>"><?= $sinfo['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm" id="filter-priority" onchange="fetchMatrixData()">
                        <option value="all">🎯 সকল প্রায়োরিটি</option>
                        <?php foreach ($priority_meta as $prkey => $prinfo): ?>
                            <option value="<?= $prkey ?>"><?= $prkey ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1 col-md-12 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilters()" title="ফিল্টার রিসেট করুন">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Multi-Platform Matrix Table -->
    <div class="matrix-table-wrapper">
        <table class="table table-responsive table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="col-id text-center">#</th>
                    <th class="col-feature-info">মডিউল ও ফিচার নাম</th>
                    <th class="col-platform text-center">
                        <span class="d-inline-flex align-items-center gap-1 text-primary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </span>
                        <div class="text-muted font-normal" style="font-size: 0.65rem;">Web Old</div>
                    </th>
                    <th class="col-platform text-center">
                        <span class="d-inline-flex align-items-center gap-1 text-info">
                            <i class="bi bi-terminal"></i> Console
                        </span>
                        <div class="text-muted font-normal" style="font-size: 0.65rem;">Web New</div>
                    </th>
                    <th class="col-platform text-center">
                        <span class="d-inline-flex align-items-center gap-1 text-success">
                            <i class="bi bi-android2"></i> Android Lite
                        </span>
                        <div class="text-muted font-normal" style="font-size: 0.65rem;">Lite Mobile</div>
                    </th>
                    <th class="col-platform text-center">
                        <span class="d-inline-flex align-items-center gap-1 text-warning">
                            <i class="bi bi-phone"></i> Premium
                        </span>
                        <div class="text-muted font-normal" style="font-size: 0.65rem;">Offline App</div>
                    </th>
                    <th class="col-platform text-center">
                        <span class="d-inline-flex align-items-center gap-1 text-secondary">
                            <i class="bi bi-display"></i> Desktop
                        </span>
                        <div class="text-muted font-normal" style="font-size: 0.65rem;">Win 10/11</div>
                    </th>
                    <th class="col-actions text-center">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody id="matrix-tbody">
                <?php render_matrix_table_body($initial_matrix_data, $platforms_meta, $status_meta, $priority_meta); ?>
            </tbody>
        </table>
    </div>

    <!-- Empty Database Banner with 1-click Demo Seeder -->
    <?php if ($stats['total_features'] == 0): ?>
        <div class="card mt-3 border-dashed text-center p-4 bg-light" id="seed-banner">
            <i class="bi bi-database fs-1 text-primary mb-2"></i>
            <h5 class="fw-bold">ডাটাবেজে বর্তমানে কোনো ফিচার এন্ট্রি নেই!</h5>
            <p class="text-muted small max-w-500 mx-auto">
                আপনি সরাসরি নতুন ফিচার যোগ করতে পারেন অথবা সিস্টেম টেস্ট করার জন্য ৫টি প্ল্যাটফর্মের ডেমো ডাটা ইনিশিয়ালাইজ করতে পারেন।
            </p>
            <div>
                <button type="button" class="btn btn-success btn-sm me-2" onclick="seedDefaultData()">
                    <i class="bi bi-stars me-1"></i> ডেমো ডাটা লোড করুন (Seed Sample Data)
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="openMasterAddModal()">
                    <i class="bi bi-plus-lg me-1"></i> ম্যানুয়ালি ফিচার যোগ করুন
                </button>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ============================================================== -->
<!-- 4. MODALS & DIALOGS                                            -->
<!-- ============================================================== -->

<!-- 4.1 ADD MASTER FEATURE MODAL -->
<div class="modal fade" id="modal-add-master" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>নতুন মাস্টার ফিচার যোগ করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-add-master" onsubmit="submitMasterAdd(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মডিউল নির্বাচন করুন (Module) <span class="text-danger">*</span></label>
                        <select class="form-select" name="module" id="add-module" required>
                            <option value="">-- মডিউল নির্বাচন করুন --</option>
                            <optgroup label="📋 Public / Academic Modules">
                                <?php foreach ($modules_list as $mod): if ($mod['is_public'] == 1): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>" data-desc="<?= htmlspecialchars($mod['descrip'] ?? '') ?>">
                                        <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </optgroup>
                            <optgroup label="⚙️ Backend / Administrative Modules">
                                <?php foreach ($modules_list as $mod): if ($mod['is_public'] == 0): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>" data-desc="<?= htmlspecialchars($mod['descrip'] ?? '') ?>">
                                        <?= htmlspecialchars($mod['module_name']) ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফিচার নাম (Feature Name) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="feature_name" id="add-feature-name" placeholder="যেমন: Daily Student Attendance, OMR Scanner..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">সংক্ষিপ্ত বিবরণ (Description)</label>
                        <textarea class="form-control" name="description" id="add-feature-desc" rows="3" placeholder="ফিচারের মূল কার্যপ্রণালী বা বর্ণনা..."></textarea>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> ফিচারটি যোগ করার সাথে সাথে ৫টি প্ল্যাটফর্ম (Dashboard, Console, Android Lite, Premium, Desktop)-এ স্বয়ংক্রিয়ভাবে এটি ইনিশিয়ালাইজ হয়ে যাবে।
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-master">
                        <i class="bi bi-check-lg me-1"></i> ফিচার সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.2 EDIT MASTER FEATURE MODAL -->
<div class="modal fade" id="modal-edit-master" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>মাস্টার ফিচার এডিট</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-master" onsubmit="submitMasterEdit(event)">
                <input type="hidden" name="id" id="edit-master-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">মডিউল নাম (Module) <span class="text-danger">*</span></label>
                        <select class="form-select" name="module" id="edit-master-module" required>
                            <optgroup label="📋 Public / Academic Modules">
                                <?php foreach ($modules_list as $mod): if ($mod['is_public'] == 1): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>">
                                        <?= htmlspecialchars($mod['module_name']) ?> <?= $mod['core'] == 1 ? '(Core)' : '' ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </optgroup>
                            <optgroup label="⚙️ Backend / Administrative Modules">
                                <?php foreach ($modules_list as $mod): if ($mod['is_public'] == 0): ?>
                                    <option value="<?= htmlspecialchars($mod['module_name']) ?>">
                                        <?= htmlspecialchars($mod['module_name']) ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ফিচার নাম (Feature Name) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="feature_name" id="edit-master-feature" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">সংক্ষিপ্ত বিবরণ (Description)</label>
                        <textarea class="form-control" name="description" id="edit-master-desc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-update-master">
                        <i class="bi bi-check-lg me-1"></i> পরিবর্তন সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.3 SPECIFIC PLATFORM EDIT & ISSUE MODAL -->
<div class="modal fade" id="modal-edit-platform" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="platform-modal-title">
                        <i class="bi bi-phone text-primary me-2"></i>প্ল্যাটফর্ম স্ট্যাটাস ও ইস্যু আপডেট
                    </h5>
                    <span class="text-muted small" id="platform-modal-subtitle">Feature: Attendance</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-platform" onsubmit="submitPlatformEdit(event)">
                <input type="hidden" name="feature_id" id="pe-feature-id">
                <input type="hidden" name="platform" id="pe-platform">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Script Path -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">স্ক্রিপ্ট / ফাইল পাথ (Script / File Path)</label>
                            <input type="text" class="form-control font-monospace" name="script_path" id="pe-script-path" placeholder="যেমন: attendance.php বা lib/screens/attnd.dart">
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">বর্তমান স্ট্যাটাস (Status) <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="pe-status" onchange="onPlatformStatusChange(this.value)">
                                <?php foreach ($status_meta as $skey => $sinfo): ?>
                                    <option value="<?= $skey ?>"><?= $sinfo['title'] ?> (<?= $sinfo['desc'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Progress Slider -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-flex justify-content-between">
                                <span>কাজের অগ্রগতি (Progress %)</span>
                                <span class="text-primary fw-bold" id="pe-progress-val">0%</span>
                            </label>
                            <input type="range" class="form-range" name="progress_percent" id="pe-progress" min="0" max="100" step="5" oninput="updateProgressDisplay(this.value)">
                        </div>

                        <!-- Priority -->
                        <div class="col-md-3 col-6">
                            <label class="form-label fw-semibold">অগ্রাধিকার (Priority)</label>
                            <select class="form-select" name="priority" id="pe-priority">
                                <?php foreach ($priority_meta as $prkey => $prinfo): ?>
                                    <option value="<?= $prkey ?>"><?= $prkey ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Deadline -->
                        <div class="col-md-3 col-6">
                            <label class="form-label fw-semibold">টার্গেট ডেট (Deadline)</label>
                            <input type="date" class="form-control" name="estimated_deadline" id="pe-deadline">
                        </div>

                        <!-- Issue / Pending Description -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-danger">
                                <i class="bi bi-bug-fill me-1"></i>শনাক্তকৃত সমস্যা বা পেন্ডিং কাজ (Issue / Customization)
                            </label>
                            <textarea class="form-control border-danger-subtle" name="issue_notes" id="pe-issue-notes" rows="4" placeholder="কোনো বাগ আছে? ইউজার কি পরিবর্তন চায়? কি বাকি আছে?"></textarea>
                        </div>

                        <!-- Developer Response / Solution -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-success">
                                <i class="bi bi-reply-fill me-1"></i>ডেভেলপার রেসপন্স বা সমাধান (Dev Response)
                            </label>
                            <textarea class="form-control border-success-subtle" name="dev_response" id="pe-dev-response" rows="4" placeholder="সমস্যা সমাধানে কি কাজ হয়েছে বা ডেভেলপারের নোট..."></textarea>
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">দায়িত্বপ্রাপ্ত ডেভেলপার (Assigned To)</label>
                            <input type="text" class="form-control" name="assigned_to" id="pe-assigned-to" placeholder="নাম / টিম">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-platform">
                        <i class="bi bi-check-lg me-1"></i> প্ল্যাটফর্ম তথ্য সংরক্ষণ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4.4 BULK ALL 5 PLATFORMS MODAL -->
<div class="modal fade" id="modal-bulk-platforms" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-sliders text-primary me-2"></i>একসাথে ৫টি প্ল্যাটফর্ম কনফিগারেশন
                    </h5>
                    <span class="text-muted small" id="bulk-modal-feature-title">Feature: Attendance</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-bulk-platforms" onsubmit="submitBulkPlatforms(event)">
                <input type="hidden" name="feature_id" id="bulk-feature-id">
                <div class="modal-body p-3">
                    <div class="row g-3" id="bulk-platforms-container">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-bulk">
                        <i class="bi bi-check2-circle me-1"></i> সব প্ল্যাটফর্ম একসাথে সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="tracker-toast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="tracker-toast-body">
                অ্যাকশন সম্পন্ন হয়েছে।
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 5. JAVASCRIPT LOGIC & LIVE AJAX ENGINE                         -->
<!-- ============================================================== -->
<script>
    // Platforms Definition for JS
    const platformsMeta = <?= json_encode($platforms_meta) ?>;
    const statusMeta = <?= json_encode($status_meta) ?>;
    let debounceTimer = null;
    let issuesOnlyMode = false;

    // Live Fetch Matrix Data
    function fetchMatrixData() {
        const searchVal = document.getElementById('filter-search').value.trim();
        const moduleVal = document.getElementById('filter-module').value;
        const platformVal = document.getElementById('filter-platform').value;
        const statusVal = document.getElementById('filter-status').value;
        const priorityVal = document.getElementById('filter-priority').value;

        const formData = new FormData();
        formData.append('action', 'fetch_matrix_data');
        formData.append('search', searchVal);
        formData.append('module', moduleVal);
        formData.append('platform', platformVal);
        formData.append('status', statusVal);
        formData.append('priority', priorityVal);
        formData.append('issues_only', issuesOnlyMode ? 1 : 0);

        const tbody = document.getElementById('matrix-tbody');

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                tbody.innerHTML = data.html;
                updateStatsUI(data.stats);
            } else {
                showToast('ডাটা লোড করতে সমস্যা হয়েছে: ' + (data.message || ''), 'bg-danger');
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
        });
    }

    function debounceFetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchMatrixData();
        }, 300);
    }

    function toggleIssuesOnly() {
        issuesOnlyMode = !issuesOnlyMode;
        const btn = document.getElementById('btn-toggle-issues');
        if (issuesOnlyMode) {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-danger');
        } else {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-danger');
        }
        fetchMatrixData();
    }

    function filterByPlatform(platKey) {
        document.getElementById('filter-platform').value = platKey;
        fetchMatrixData();
    }

    function resetFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-module').value = 'all';
        document.getElementById('filter-platform').value = 'all';
        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-priority').value = 'all';
        issuesOnlyMode = false;
        const btn = document.getElementById('btn-toggle-issues');
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
        fetchMatrixData();
    }

    function updateStatsUI(stats) {
        if (!stats) return;
        document.getElementById('stat-total-features').innerText = stats.total_features || 0;
        document.getElementById('stat-issues-count').innerText = stats.total_issues || 0;

        const calcPercent = (comp, tot) => tot > 0 ? Math.round((comp / tot) * 100) : 0;
        document.getElementById('stat-p-dashboard').innerText = calcPercent(stats.p_dashboard_completed, stats.p_dashboard_total) + '%';
        document.getElementById('stat-p-console').innerText = calcPercent(stats.p_console_completed, stats.p_console_total) + '%';
        document.getElementById('stat-p-android').innerText = calcPercent(stats.p_android_completed, stats.p_android_total) + '%';
        document.getElementById('stat-p-premium').innerText = calcPercent(stats.p_premium_completed, stats.p_premium_total) + '%';
        document.getElementById('stat-p-desktop').innerText = calcPercent(stats.p_desktop_completed, stats.p_desktop_total) + '%';
    }

    // Toggle Matrix Drawer Row
    function toggleMatrixDrawer(featureId, e) {
        if (e && e.target.closest('button, select, input, a, .platform-cell-card')) return;
        const drawer = document.getElementById(`drawer-${featureId}`);
        const icon = document.getElementById(`icon-${featureId}`);
        if (!drawer) return;

        if (drawer.classList.contains('show')) {
            drawer.classList.remove('show');
            if (icon) icon.className = 'bi bi-chevron-right expand-icon text-muted';
        } else {
            drawer.classList.add('show');
            if (icon) icon.className = 'bi bi-chevron-down expand-icon text-primary';
        }
    }

    // Robust Modal Helpers (Works with Bootstrap 5, jQuery, or Vanilla DOM)
    function openModal(modalId) {
        const el = document.getElementById(modalId);
        if (!el) return;
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getOrCreateInstance(el);
                if (inst) {
                    inst.show();
                    return;
                }
            }
        } catch (e) {
            console.warn('Bootstrap modal open error:', e);
        }
        if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
            $(el).modal('show');
            return;
        }
        el.classList.add('show');
        el.style.display = 'block';
        document.body.classList.add('modal-open');
    }

    function closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (!el) return;
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
                if (inst) inst.hide();
            }
        } catch (e) {
            console.warn('Bootstrap modal hide error:', e);
        }
        if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
            $(el).modal('hide');
        }
        setTimeout(() => {
            el.classList.remove('show');
            el.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        }, 150);
    }

    // 5.1 MASTER FEATURE MODAL HANDLERS
    function openMasterAddModal() {
        document.getElementById('form-add-master').reset();
        openModal('modal-add-master');
    }

    function submitMasterAdd(e) {
        e.preventDefault();
        const form = document.getElementById('form-add-master');
        const formData = new FormData(form);
        formData.append('action', 'add_master_feature');

        const btn = document.getElementById('btn-save-master');
        btn.disabled = true;

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                closeModal('modal-add-master');
                showToast(data.message, 'bg-success');
                fetchMatrixData();
            } else {
                showToast(data.message, 'bg-danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            showToast('সংরক্ষণ ব্যর্থ হয়েছে!', 'bg-danger');
        });
    }

    function openMasterEditModal(masterData) {
        document.getElementById('edit-master-id').value = masterData.id;
        document.getElementById('edit-master-module').value = masterData.module || '';
        document.getElementById('edit-master-feature').value = masterData.feature_name || '';
        document.getElementById('edit-master-desc').value = masterData.description || '';

        openModal('modal-edit-master');
    }

    function submitMasterEdit(e) {
        e.preventDefault();
        const form = document.getElementById('form-edit-master');
        const formData = new FormData(form);
        formData.append('action', 'edit_master_feature');

        const btn = document.getElementById('btn-update-master');
        btn.disabled = true;

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                closeModal('modal-edit-master');
                showToast(data.message, 'bg-success');
                fetchMatrixData();
            } else {
                showToast(data.message, 'bg-danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            showToast('আপডেট ব্যর্থ হয়েছে!', 'bg-danger');
        });
    }

    function deleteMasterFeature(id, name) {
        if (!confirm(`আপনি কি নিশ্চিতভাবে "${name}" ফিচারটি এবং এর সাথে যুক্ত ৫টি প্ল্যাটফর্মের সকল তথ্য মুছে ফেলতে চান?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete_master_feature');
        formData.append('id', id);

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'bg-success');
                fetchMatrixData();
            } else {
                showToast(data.message, 'bg-danger');
            }
        })
        .catch(err => {
            showToast('মুছে ফেলতে ব্যর্থ হয়েছে!', 'bg-danger');
        });
    }

    // 5.2 SPECIFIC PLATFORM EDIT MODAL
    function openPlatformEditModal(featureId, platformKey, featureName, platformData = null) {
        const platInfo = platformsMeta[platformKey] || { title: platformKey, icon: 'ri-smartphone-line' };
        document.getElementById('pe-feature-id').value = featureId;
        document.getElementById('pe-platform').value = platformKey;
        document.getElementById('platform-modal-title').innerHTML = `<i class="${platInfo.icon} me-2" style="color:${platInfo.color || '#696cff'}"></i> ${platInfo.title} প্ল্যাটফর্ম কনফিগারেশন`;
        document.getElementById('platform-modal-subtitle').innerText = `ফিচার: ${featureName}`;

        function populateForm(pt) {
            document.getElementById('pe-script-path').value = pt.script_path || '';
            document.getElementById('pe-status').value = pt.status || 'Planned';
            document.getElementById('pe-progress').value = pt.progress_percent || 0;
            document.getElementById('pe-progress-val').innerText = (pt.progress_percent || 0) + '%';
            document.getElementById('pe-priority').value = pt.priority || 'Medium';
            document.getElementById('pe-deadline').value = pt.estimated_deadline || '';
            document.getElementById('pe-issue-notes').value = pt.issue_notes || '';
            document.getElementById('pe-dev-response').value = pt.dev_response || '';
            document.getElementById('pe-assigned-to').value = pt.assigned_to || '';
            openModal('modal-edit-platform');
        }

        if (platformData && typeof platformData === 'object') {
            populateForm(platformData);
            return;
        }

        // Fallback via POST AJAX if data not already passed
        const formData = new FormData();
        formData.append('action', 'get_platform_details');
        formData.append('feature_id', featureId);
        formData.append('platform', platformKey);

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                populateForm(data.platform_data);
            } else {
                populateForm({ feature_id: featureId, platform: platformKey });
            }
        })
        .catch(err => {
            console.error('Platform fetch error:', err);
            populateForm({ feature_id: featureId, platform: platformKey });
        });
    }

    function updateProgressDisplay(val) {
        document.getElementById('pe-progress-val').innerText = val + '%';
        if (val == 100) {
            document.getElementById('pe-status').value = 'Completed';
        }
    }

    function onPlatformStatusChange(st) {
        if (st === 'Completed') {
            document.getElementById('pe-progress').value = 100;
            document.getElementById('pe-progress-val').innerText = '100%';
        }
    }

    function submitPlatformEdit(e) {
        e.preventDefault();
        const form = document.getElementById('form-edit-platform');
        const formData = new FormData(form);
        formData.append('action', 'update_platform_status');

        const btn = document.getElementById('btn-save-platform');
        btn.disabled = true;

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                closeModal('modal-edit-platform');
                showToast(data.message, 'bg-success');
                fetchMatrixData();
            } else {
                showToast(data.message, 'bg-danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            showToast('আপডেট ব্যর্থ হয়েছে!', 'bg-danger');
        });
    }

    // 5.3 BULK ALL 5 PLATFORMS MODAL
    function openBulkPlatformsModal(featureId, featureName, platformsData) {
        document.getElementById('bulk-feature-id').value = featureId;
        document.getElementById('bulk-modal-feature-title').innerText = `ফিচার: ${featureName}`;

        const container = document.getElementById('bulk-platforms-container');
        let html = '';

        for (const [pkey, pinfo] of Object.entries(platformsMeta)) {
            const pdata = platformsData[pkey] || {
                script_path: '',
                status: 'Planned',
                progress_percent: 0,
                priority: 'Medium',
                issue_notes: '',
                dev_response: '',
                assigned_to: ''
            };

            html += `
                <div class="col-lg-6">
                    <div class="card border h-100 p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <strong class="text-dark"><i class="${pinfo.icon} me-1" style="color:${pinfo.color}"></i> ${pinfo.title} (${pinfo.sub})</strong>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="small text-muted mb-1">স্ক্রিপ্ট পাথ</label>
                                <input type="text" class="form-control form-control-sm font-monospace" name="platforms[${pkey}][script_path]" value="${escapeHtml(pdata.script_path || '')}" placeholder="script file...">
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted mb-1">স্ট্যাটাস</label>
                                <select class="form-select form-select-sm" name="platforms[${pkey}][status]">
                                    ${Object.keys(statusMeta).map(st => `<option value="${st}" ${pdata.status === st ? 'selected' : ''}>${st}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">অগ্রগতি (%)</label>
                                <input type="number" class="form-control form-control-sm" name="platforms[${pkey}][progress_percent]" min="0" max="100" value="${pdata.progress_percent || 0}">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">অগ্রাধিকার</label>
                                <select class="form-select form-select-sm" name="platforms[${pkey}][priority]">
                                    <option value="Critical" ${pdata.priority === 'Critical' ? 'selected' : ''}>Critical</option>
                                    <option value="High" ${pdata.priority === 'High' ? 'selected' : ''}>High</option>
                                    <option value="Medium" ${pdata.priority === 'Medium' || !pdata.priority ? 'selected' : ''}>Medium</option>
                                    <option value="Low" ${pdata.priority === 'Low' ? 'selected' : ''}>Low</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="small text-danger mb-1"><i class="bi bi-bug-fill"></i> সমস্যা / কাস্টমাইজেশন নোট</label>
                                <textarea class="form-control form-control-sm" name="platforms[${pkey}][issue_notes]" rows="2" placeholder="সমস্যা বর্ণনা...">${escapeHtml(pdata.issue_notes || '')}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="small text-success mb-1"><i class="bi bi-reply-fill"></i> সমাধান / রেসপন্স</label>
                                <textarea class="form-control form-control-sm" name="platforms[${pkey}][dev_response]" rows="2" placeholder="ডেভেলপার নোট...">${escapeHtml(pdata.dev_response || '')}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
        openModal('modal-bulk-platforms');
    }

    function submitBulkPlatforms(e) {
        e.preventDefault();
        const form = document.getElementById('form-bulk-platforms');
        const formData = new FormData(form);
        formData.append('action', 'bulk_update_platforms');

        const btn = document.getElementById('btn-save-bulk');
        btn.disabled = true;

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                closeModal('modal-bulk-platforms');
                showToast(data.message, 'bg-success');
                fetchMatrixData();
            } else {
                showToast(data.message, 'bg-danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            showToast('বাল্ক আপডেট ব্যর্থ হয়েছে!', 'bg-danger');
        });
    }

    // 5.4 DEMO SEED DATA
    function seedDefaultData() {
        const formData = new FormData();
        formData.append('action', 'seed_default_data');

        fetch('feature-tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message, 'bg-success');
            const banner = document.getElementById('seed-banner');
            if (banner) banner.style.display = 'none';
            fetchMatrixData();
        })
        .catch(err => {
            showToast('সিড করতে সমস্যা হয়েছে!', 'bg-danger');
        });
    }

    // Utility Helpers
    function showToast(msg, bgClass = 'bg-primary') {
        const toastEl = document.getElementById('tracker-toast');
        const bodyEl = document.getElementById('tracker-toast-body');
        toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
        bodyEl.innerText = msg;
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
    }

    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            showToast(`কপি করা হয়েছে: ${text}`, 'bg-dark');
        }).catch(() => {
            showToast('কপিতে ব্যর্থ!', 'bg-danger');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php require_once 'footer.php'; ?>
