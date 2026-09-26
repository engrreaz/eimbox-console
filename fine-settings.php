<?php
require_once 'header.php';

// Safe check / create tables
$conn->query("CREATE TABLE IF NOT EXISTS `fine_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` varchar(10) NOT NULL,
  `slot` varchar(50) DEFAULT 'School',
  `scope` enum('global','class') NOT NULL DEFAULT 'global',
  `classname` varchar(50) DEFAULT NULL,
  `absent_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bunk_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bunk_rule_type` enum('flat_daily','per_period') NOT NULL DEFAULT 'flat_daily',
  `posting_mode` enum('daily','monthly','manual') NOT NULL DEFAULT 'manual',
  `daily_run_time` time DEFAULT '18:00:00',
  `monthly_run_day` tinyint(2) DEFAULT 1,
  `itemcode` varchar(30) DEFAULT 'FINE01',
  `particulareng` varchar(150) DEFAULT 'Absence / Bunk Fine',
  `particularben` varchar(200) DEFAULT 'Absence / Bunk Fine',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_generated_date` date DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fine_rule` (`sccode`, `sessionyear`, `slot`, `scope`, `classname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `student_leave_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` varchar(10) NOT NULL,
  `stid` varchar(20) NOT NULL,
  `rollno` int(11) DEFAULT NULL,
  `classname` varchar(50) NOT NULL,
  `sectionname` varchar(50) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `days` int(11) DEFAULT 1,
  `stfinance_id` int(11) DEFAULT NULL,
  `claimed_fine_amt` decimal(10,2) DEFAULT 0.00,
  `waiver_requested_amt` decimal(10,2) DEFAULT 0.00,
  `waiver_approved_amt` decimal(10,2) DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `apply_by` varchar(100) DEFAULT 'Student',
  `apply_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `response_by` varchar(100) DEFAULT NULL,
  `response_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lookup` (`sccode`, `sessionyear`, `stid`, `status`),
  KEY `idx_dates` (`sccode`, `date_from`, `date_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `student_fine_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sccode` int(11) NOT NULL,
  `sessionyear` varchar(10) NOT NULL,
  `classname` varchar(50) NOT NULL,
  `sectionname` varchar(50) DEFAULT NULL,
  `stid` bigint(20) NOT NULL,
  `rollno` int(11) DEFAULT NULL,
  `fine_date` date NOT NULL,
  `fine_type` enum('absent','bunk') NOT NULL DEFAULT 'absent',
  `fine_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stfinance_id` int(11) DEFAULT NULL,
  `month` tinyint(2) NOT NULL,
  `status` enum('posted','waived','cancelled') NOT NULL DEFAULT 'posted',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fine_entry` (`sccode`, `sessionyear`, `stid`, `fine_date`, `fine_type`),
  KEY `idx_lookup` (`sccode`, `sessionyear`, `stid`, `fine_date`),
  KEY `idx_month` (`sccode`, `sessionyear`, `month`),
  KEY `idx_status` (`sccode`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

$slot = $_COOKIE['slot'] ?? $_GET['slot'] ?? 'School';
$session = $_COOKIE['session'] ?? $_GET['session'] ?? $sessionyear;

// Fetch Available Sessions
$sessions_list = [];
$ses_stmt = $conn->prepare("SELECT DISTINCT syear FROM sessionyear WHERE sccode = ? AND active = 1 ORDER BY syear DESC");
$ses_stmt->bind_param('i', $sccode);
$ses_stmt->execute();
$ses_res = $ses_stmt->get_result();
while ($s_row = $ses_res->fetch_assoc()) {
    $sessions_list[] = $s_row['syear'];
}
$ses_stmt->close();
if (empty($sessions_list)) {
    $sessions_list[] = $session;
}

// 1. Fetch Existing Global Settings
$global_stmt = $conn->prepare("SELECT * FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'global' LIMIT 1");
$global_stmt->bind_param('iss', $sccode, $session, $slot);
$global_stmt->execute();
$global_res = $global_stmt->get_result();
$global = $global_res->fetch_assoc() ?: [
    'absent_rate' => 10.00,
    'bunk_rate' => 20.00,
    'bunk_rule_type' => 'flat_daily',
    'absent_detection_mode' => 'attendance_based',
    'weekly_off' => 'friday_saturday',
    'posting_mode' => 'manual',
    'daily_run_time' => '18:00:00',
    'monthly_run_day' => 1,
    'itemcode' => uniqid(),
    'particulareng' => 'Absence / Bunk Fine',
    'particularben' => 'Absence / Bunk Fine'
];
if (!isset($global['absent_detection_mode'])) $global['absent_detection_mode'] = 'attendance_based';
if (!isset($global['weekly_off'])) $global['weekly_off'] = 'friday_saturday';
$global_stmt->close();

$fine_itemcode = (!empty($global['itemcode']) && $global['itemcode'] !== 'FINE01') ? $global['itemcode'] : uniqid();

// 2. Fetch Class Overrides
$class_rates = [];
$cls_stmt = $conn->prepare("SELECT classname, absent_rate, bunk_rate, status FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'class'");
$cls_stmt->bind_param('iss', $sccode, $session, $slot);
$cls_stmt->execute();
$cls_res = $cls_stmt->get_result();
while ($cRow = $cls_res->fetch_assoc()) {
    $class_rates[$cRow['classname']] = $cRow;
}
$cls_stmt->close();

// 3. Fetch Available Classes from sessioninfo
$classes = [];
$cQuery = $conn->prepare("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = ? AND sessionyear = ? AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
$cQuery->bind_param('is', $sccode, $session);
$cQuery->execute();
$cRes = $cQuery->get_result();
while ($row = $cRes->fetch_assoc()) {
    $classes[] = $row['classname'];
}
$cQuery->close();

// Fetch Available Sections
$sections = [];
$secQuery = $conn->prepare("SELECT DISTINCT sectionname FROM sessioninfo WHERE sccode = ? AND sessionyear = ? AND sectionname IS NOT NULL AND sectionname != '' ORDER BY sectionname ASC");
$secQuery->bind_param('is', $sccode, $session);
$secQuery->execute();
$secRes = $secQuery->get_result();
while ($sRow = $secRes->fetch_assoc()) {
    $sections[] = $sRow['sectionname'];
}
$secQuery->close();

// Fetch Latest Attendance Date for smart default date range
$latest_att_res = $conn->query("SELECT MAX(adate) AS max_date FROM stattnd WHERE sccode = '$sccode'");
$latest_adate = ($latest_att_res && $row = $latest_att_res->fetch_assoc()) ? $row['max_date'] : null;
$default_from_date = $latest_adate ? date('Y-m-01', strtotime($latest_adate)) : date('Y-m-01');
$default_to_date = $latest_adate ? $latest_adate : date('Y-m-d');

// 4. Fetch Recent Disaster / Exemption Events (sccode = ? AND class = 0)
$exemptEvents = [];
$evStmt = $conn->prepare("SELECT id, title, start, end, color FROM events WHERE sccode = ? AND class = 0 ORDER BY start DESC LIMIT 20");
$evStmt->bind_param('i', $sccode);
$evStmt->execute();
$evRes = $evStmt->get_result();
while ($e = $evRes->fetch_assoc()) {
    $exemptEvents[] = $e;
}
$evStmt->close();

// 5. Fetch Institutional Weekends from settings table (setting_title = 'Weekends', dot-separated)
$weekendDays = [];
$rawWeekends = '';
$wStmt = $conn->prepare("SELECT settings_value FROM settings WHERE (sccode = ? OR sccode = 0) AND LOWER(setting_title) = 'weekends' ORDER BY (sccode = ?) DESC, id DESC LIMIT 1");
if ($wStmt) {
    $wStmt->bind_param('ii', $sccode, $sccode);
    $wStmt->execute();
    $wRes = $wStmt->get_result();
    if ($wRow = $wRes->fetch_assoc()) {
        $rawWeekends = trim($wRow['settings_value'] ?? '');
    }
    $wStmt->close();
}
if (!empty($rawWeekends)) {
    $weekendDays = array_values(array_filter(array_map('trim', preg_split('/[\.,\s]+/', $rawWeekends))));
}
if (empty($weekendDays)) {
    $weekendDays = ['Friday', 'Saturday'];
}
$weekends_display = implode(', ', array_map('ucfirst', $weekendDays));
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Quick Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-shield-slash-fill text-danger me-2"></i> Student Fine Settings & Automation
            </h4>
            <span class="text-muted">Configure absence and bunk (fleeing) fine rates, auto-posting schedules, and exemption rules</span>
        </div>
        <div class="d-flex gap-2">
            <a href="sync-payments.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cash-stack me-1"></i> Payment Sync
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="saveSettings()">
                <i class="bi bi-check2-circle me-1"></i> Save Settings
            </button>
        </div>
    </div>

    <!-- Filter Bar / Slot & Session -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row align-items-end g-2">
                <?php
                $chain_param = '-c 10 -t Choose Values -u -r -b View Settings -h class exam';
                include 'components/slot-tree-ui.php';
                ?>
            </div>
        </div>
    </div>

    <!-- Alert Box -->
    <div id="statusAlert" class="alert alert-dismissible d-none mb-4" role="alert">
        <span id="statusAlertText"></span>
        <button type="button" class="btn-close" onclick="$('#statusAlert').addClass('d-none')"></button>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-global">
                    <i class="bi bi-sliders2 me-1"></i> Global Policy & Scheduler
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-classes">
                    <i class="bi bi-diagram-3 me-1"></i> Class-wise Custom Rates 
                    <span class="badge rounded-pill bg-label-primary ms-1"><?= count($classes) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-exemptions">
                    <i class="bi bi-cloud-sun me-1"></i> Disaster & Holiday Exemptions
                    <span class="badge rounded-pill bg-label-warning ms-1"><?= count($exemptEvents) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-generator">
                    <i class="bi bi-play-circle me-1"></i> Instant Fine Generator
                </button>
            </li>
        </ul>

        <div class="tab-content border-top-0 p-4">

            <!-- TAB 1: GLOBAL POLICY & SCHEDULER -->
            <div class="tab-pane fade show active" id="tab-global" role="tabpanel">
                <form id="globalSettingsForm">
                    <input type="hidden" name="sccode" value="<?= htmlspecialchars($sccode) ?>">
                    <input type="hidden" name="sessionyear" value="<?= htmlspecialchars($session) ?>">
                    <input type="hidden" name="slot" value="<?= htmlspecialchars($slot) ?>">

                    <div class="row g-4">
                        <!-- Rates Card -->
                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-currency-dollar text-primary me-2"></i>Default Fine Rates</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Absence Fine Rate (Per Day) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.5" class="form-control" name="absent_rate" id="absent_rate" value="<?= htmlspecialchars($global['absent_rate']) ?>" required>
                                            <span class="input-group-text">BDT</span>
                                        </div>
                                        <small class="text-muted">Charged per day when student is absent (yn = 0).</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Bunk / Fleeing Fine Rate <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.5" class="form-control" name="bunk_rate" id="bunk_rate" value="<?= htmlspecialchars($global['bunk_rate']) ?>" required>
                                            <span class="input-group-text">BDT</span>
                                        </div>
                                        <small class="text-muted">Charged when student bunks/flees during class hours (bunk = 1).</small>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-bold">Bunk Calculation Condition</label>
                                        <select class="form-select" name="bunk_rule_type" id="bunk_rule_type">
                                            <option value="flat_daily" <?= $global['bunk_rule_type'] === 'flat_daily' ? 'selected' : '' ?>>Flat Daily Rate (Once Per Day)</option>
                                            <option value="per_period" <?= $global['bunk_rule_type'] === 'per_period' ? 'selected' : '' ?>>Per Period Bunked (Separately calculated)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduler Card -->
                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-info me-2"></i>Posting & Automation Schedule</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Fine Posting Mechanism</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_manual" value="manual" <?= $global['posting_mode'] === 'manual' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_manual">
                                                <strong>Manual Only</strong>
                                                <div class="text-muted small">Admin manually triggers posting from the 'Instant Fine Generator' tab.</div>
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_daily" value="daily" <?= $global['posting_mode'] === 'daily' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_daily">
                                                <strong>Daily Auto-Posting</strong>
                                                <div class="text-muted small">System automatically posts fines daily at the specified time.</div>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_monthly" value="monthly" <?= $global['posting_mode'] === 'monthly' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_monthly">
                                                <strong>Monthly Consolidated Posting</strong>
                                                <div class="text-muted small">Consolidated fine for the entire month is posted on a specific day of the month.</div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Daily Options -->
                                    <div id="dailyOptionBox" class="mb-3 <?= $global['posting_mode'] === 'daily' ? '' : 'd-none' ?>">
                                        <label class="form-label fw-bold">Daily Run Time</label>
                                        <input type="time" class="form-control" name="daily_run_time" id="daily_run_time" value="<?= htmlspecialchars($global['daily_run_time']) ?>">
                                    </div>

                                    <!-- Monthly Options -->
                                    <div id="monthlyOptionBox" class="mb-3 <?= $global['posting_mode'] === 'monthly' ? '' : 'd-none' ?>">
                                        <label class="form-label fw-bold">Monthly Run Day</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Day</span>
                                            <input type="number" min="1" max="28" class="form-control" name="monthly_run_day" id="monthly_run_day" value="<?= htmlspecialchars($global['monthly_run_day']) ?>">
                                            <span class="input-group-text">of every month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Absent Detection Strategy Card -->
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-x text-danger me-2"></i>Absent Detection Policy (অনুপস্থিতি নির্ধারণ পদ্ধতি)</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold">Calculation Strategy</label>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="absent_detection_mode" id="mode_att_based" value="attendance_based" <?= $global['absent_detection_mode'] === 'attendance_based' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="mode_att_based">
                                                    <strong>Option A: Smart Active Attendance Days (হাজিরা গ্রহণ ভিত্তিক)</strong>
                                                    <div class="text-muted small">শুধুমাত্র যেসব তারিখে ক্লাসে অন্তত ১ জন শিক্ষার্থীর হাজিরা রয়েছে (`yn = 1`), সেদিন অনুপস্থিতদের জরিমানা হিসাব করবে। (ডিজিটাল বা বায়োমেট্রিক হাজিরা চালুর জন্য)।</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="absent_detection_mode" id="mode_cal_based" value="calendar_based" <?= $global['absent_detection_mode'] === 'calendar_based' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="mode_cal_based">
                                                    <strong>Option B: Calendar Working Days (ক্যালেন্ডার কর্মদিবস ভিত্তিক)</strong>
                                                    <div class="text-muted small">নির্বাচিত তারিখ রেঞ্জের প্রতিটি কর্মদিবসে (সাপ্তাহিক ছুটি ও বিশেষ ছুটি বাদে) যাদের `yn = 1` নেই, তাদের সবাইকে অনুপস্থিত ধরে জরিমানা হিসাব করবে।</div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">Institutional Weekends (সাপ্তাহিক ছুটি)</label>
                                            <div class="p-3 border rounded bg-light">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="badge bg-label-info fs-6 px-3 py-2">
                                                        <i class="bi bi-calendar2-week me-1"></i> <?= htmlspecialchars($weekends_display) ?>
                                                    </span>
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-database-check text-success me-1"></i> <code>settings</code> টেবিল থেকে রিড করা হয়েছে (<code>setting_title = 'Weekends'</code>, মান: <code><?= htmlspecialchars($rawWeekends ?: implode('.', $weekendDays)) ?></code>)।
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-info-circle text-primary me-1"></i> <strong>Option B</strong> মোডে এই সাপ্তাহিক ছুটির দিনগুলোতে স্বয়ংক্রিয়ভাবে শিক্ষার্থীদের অনুপস্থিতি জরিমানা ছাড়া বিবেচনা করা হবে।
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finance Ledger Mapping Card -->
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 text-success me-2"></i>Student Finance Ledger Mapping (`stfinance`)</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">Fee Item Code</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="itemcode" id="itemcode" value="<?= htmlspecialchars($fine_itemcode) ?>" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="regenerateItemCode()" title="Generate New Unique Code">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Unique finance identifier</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Particular Title (English)</label>
                                            <input type="text" class="form-control" name="particulareng" id="particulareng" value="<?= htmlspecialchars($global['particulareng']) ?>" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">Particular Title (Secondary)</label>
                                            <input type="text" class="form-control" name="particularben" id="particularben" value="<?= htmlspecialchars($global['particularben']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-primary px-4" onclick="saveSettings()">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: CLASS-WISE CUSTOM RATES -->
            <div class="tab-pane fade" id="tab-classes" role="tabpanel">
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle me-1"></i> Enable <strong>"Custom Rate"</strong> only for classes that require specific rates. Otherwise, the global default rates will automatically apply.
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Class Name</th>
                                <th style="width: 180px;">Policy Mode</th>
                                <th style="width: 200px;">Absence Fine (৳)</th>
                                <th style="width: 200px;">Bunk Fine (৳)</th>
                                <th style="width: 140px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($classes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                        No classes found for the active session and shift.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($classes as $idx => $cName): 
                                    $isCustom = isset($class_rates[$cName]);
                                    $cAbsent = $isCustom ? $class_rates[$cName]['absent_rate'] : $global['absent_rate'];
                                    $cBunk = $isCustom ? $class_rates[$cName]['bunk_rate'] : $global['bunk_rate'];
                                ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-mortarboard me-1 text-primary"></i> <?= htmlspecialchars($cName) ?>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input class-toggle" type="checkbox" id="toggle_<?= $idx ?>" 
                                                       data-class="<?= htmlspecialchars($cName) ?>" <?= $isCustom ? 'checked' : '' ?> 
                                                       onchange="toggleClassCustom('<?= htmlspecialchars($cName) ?>', this.checked)">
                                                <label class="form-check-label" for="toggle_<?= $idx ?>">
                                                    <span id="label_<?= htmlspecialchars($cName) ?>" class="badge <?= $isCustom ? 'bg-label-primary' : 'bg-label-secondary' ?>">
                                                        <?= $isCustom ? 'Custom Rate' : 'Global Default' ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="0.5" class="form-control class-absent-input" 
                                                       id="absent_<?= htmlspecialchars($cName) ?>" 
                                                       value="<?= htmlspecialchars($cAbsent) ?>" 
                                                       <?= $isCustom ? '' : 'disabled' ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="0.5" class="form-control class-bunk-input" 
                                                       id="bunk_<?= htmlspecialchars($cName) ?>" 
                                                       value="<?= htmlspecialchars($cBunk) ?>" 
                                                       <?= $isCustom ? '' : 'disabled' ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($isCustom): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active Override</span>
                                            <?php else: ?>
                                                <span class="badge bg-label-secondary"><i class="bi bi-dash-circle me-1"></i>Default Rate</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-primary px-4" onclick="saveSettings()">
                        <i class="bi bi-save me-1"></i> Save All Rates
                    </button>
                </div>
            </div>

            <!-- TAB 3: DISASTER & HOLIDAY EXEMPTIONS -->
            <div class="tab-pane fade" id="tab-exemptions" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-success me-1"></i> Disaster & Holiday Exemptions</h6>
                        <small class="text-muted">On these listed dates, no student absence or bunk fines will be applied automatically.</small>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addExemptionModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Disaster / Special Exemption
                    </button>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Reason / Exemption Title</th>
                                <th style="width: 150px;">Start Date</th>
                                <th style="width: 150px;">End Date</th>
                                <th style="width: 140px;">Effect</th>
                                <th style="width: 100px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($exemptEvents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-calendar-x fs-2 d-block mb-1"></i>
                                        No disaster or holiday exemptions recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($exemptEvents as $idx => $ev): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td class="fw-bold">
                                            <span class="badge rounded-circle p-1 me-1" style="background-color: <?= htmlspecialchars($ev['color'] ?: '#FF5722') ?>"> </span>
                                            <?= htmlspecialchars($ev['title']) ?>
                                        </td>
                                        <td><?= date('d M, Y', strtotime($ev['start'])) ?></td>
                                        <td><?= $ev['end'] ? date('d M, Y', strtotime($ev['end'])) : date('d M, Y', strtotime($ev['start'])) ?></td>
                                        <td>
                                            <span class="badge bg-label-success">
                                                <i class="bi bi-check2-all me-1"></i> Fine Exempted
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="deleteExemption(<?= $ev['id'] ?>)" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: INSTANT FINE GENERATOR & SYNC -->
            <div class="tab-pane fade" id="tab-generator" role="tabpanel">
                <div class="card border shadow-none mb-4">
                    <div class="card-header border-bottom bg-light py-2">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Fine Calculation & Sync Console</h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Class Filter</label>
                                <select class="form-select" id="gen_class" onchange="onGeneratorClassChange()">
                                    <option value="all">All Classes</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Section Filter</label>
                                <select class="form-select" id="gen_section">
                                    <option value="all">All Sections</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Calculation Mode</label>
                                <select class="form-select" id="gen_calc_mode">
                                    <option value="policy">Use Policy Setting (<?= $global['absent_detection_mode'] === 'calendar_based' ? 'Option B: Calendar Days' : 'Option A: Attendance Days' ?>)</option>
                                    <option value="attendance_based">Option A: Attendance Taken Days (হাজিরা এন্ট্রি থাকলে)</option>
                                    <option value="calendar_based">Option B: Calendar Working Days (প্রতি কর্মদিবস)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="gen_from_date" value="<?= htmlspecialchars($default_from_date) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="gen_to_date" value="<?= htmlspecialchars($default_to_date) ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-info w-100 px-1" id="btnPreviewFines" onclick="previewFines()" title="Preview Calculation">
                                    <i class="bi bi-search"></i> Preview
                                </button>
                            </div>
                        </div>
                        <?php if ($latest_adate): ?>
                            <div class="mt-2 text-muted small d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle text-primary"></i> 
                                <span>Latest attendance in database: <strong class="text-dark"><?= date('d M, Y', strtotime($latest_adate)) ?></strong></span>
                                <a href="javascript:void(0)" class="text-primary text-decoration-underline" onclick="$('#gen_from_date').val('<?= date('Y-m-01', strtotime($latest_adate)) ?>'); $('#gen_to_date').val('<?= $latest_adate ?>'); previewFines();">
                                    Use This Month
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview KPI Summary -->
                <div id="previewSummaryBox" class="d-none mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center h-100">
                                <div class="text-muted small">Total Impacted Students</div>
                                <h4 class="mb-0 text-primary fw-bold" id="kpiStudents">0</h4>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center h-100">
                                <div class="text-muted small">New Fine to Post</div>
                                <h4 class="mb-0 text-success fw-bold">৳ <span id="kpiTotalFine">0.00</span></h4>
                                <small class="text-muted" id="kpiNewDaysBreakdown">0 Absent, 0 Bunk</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center h-100">
                                <div class="text-muted small">Already Billed / Logged</div>
                                <h4 class="mb-0 text-info fw-bold">৳ <span id="kpiAlreadyBilledAmount">0.00</span></h4>
                                <small class="text-muted"><span id="kpiAlreadyBilledDays">0</span> days previously logged</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center h-100">
                                <div class="text-muted small">Active School Days</div>
                                <h4 class="mb-0 text-dark fw-bold" id="kpiActiveDays">0</h4>
                                <small class="text-muted" id="kpiExemptInfo">0 exempted</small>
                            </div>
                        </div>
                    </div>

                    <!-- Post Button Bar -->
                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded border flex-wrap gap-2">
                        <div>
                            <span class="fw-bold text-dark" id="previewRangeLabel"></span>
                            <div class="text-muted small">
                                <i class="bi bi-shield-check text-success me-1"></i>
                                New fines will be recorded in <code>student_fine_logs</code> and consolidated automatically into <code>stfinance</code> without affecting paid balances.
                            </div>
                        </div>
                        <button type="button" class="btn btn-success px-4" id="btnPostFines" onclick="postFines()">
                            <i class="bi bi-send-check me-1"></i> Post Fines to Ledger
                        </button>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-1"></i> Class-wise Fine Summary</h6>
                        <div class="table-responsive border rounded mb-4">
                            <table class="table table-sm table-striped align-middle mb-0" id="previewBreakdownTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class Name</th>
                                        <th class="text-center">Students</th>
                                        <th class="text-center">New Absent</th>
                                        <th class="text-center">New Bunk</th>
                                        <th class="text-center">Already Billed</th>
                                        <th class="text-end">New Fine (৳)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <!-- Detailed Student Breakdown Table -->
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <h6 class="fw-bold mb-0"><i class="bi bi-people me-1"></i> Student-wise Fine Details</h6>
                            <input type="text" id="filterStudentPreview" class="form-control form-control-sm" style="max-width: 250px;" placeholder="Search student name / roll / ID..." onkeyup="filterStudentTable()">
                        </div>
                        <div class="table-responsive border rounded" style="max-height: 420px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" id="previewStudentTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 60px;">Roll</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th class="text-center">New Absent</th>
                                        <th class="text-center">New Bunk</th>
                                        <th class="text-center">Already Billed</th>
                                        <th class="text-end">New Fine (৳)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="previewLoader" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Checking attendance records and computing fines...</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Add Disaster / Weather Exemption -->
<div class="modal fade" id="addExemptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="bi bi-cloud-lightning-rain me-1"></i> Add Disaster / Special Exemption</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddExemption">
                    <input type="hidden" name="sccode" value="<?= htmlspecialchars($sccode) ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Exemption Title / Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. Severe Cold Wave / Heavy Rainfall Shutdown" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i> On these dates, all student absence and bunk fines will be waived automatically.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitExemption()">Save Exemption</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Configure SweetAlert Toast for quick status notifications
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

function regenerateItemCode() {
    const newCode = Math.floor(Date.now() / 1000).toString(16) + Math.random().toString(16).substring(2, 7);
    $('#itemcode').val(newCode);
    Toast.fire({
        icon: 'info',
        title: 'New Item Code Generated: ' + newCode
    });
}

function togglePostingOptions() {
    const mode = $('input[name="posting_mode"]:checked').val();
    if (mode === 'daily') {
        $('#dailyOptionBox').removeClass('d-none');
        $('#monthlyOptionBox').addClass('d-none');
    } else if (mode === 'monthly') {
        $('#monthlyOptionBox').removeClass('d-none');
        $('#dailyOptionBox').addClass('d-none');
    } else {
        $('#dailyOptionBox').addClass('d-none');
        $('#monthlyOptionBox').addClass('d-none');
    }
}

function toggleClassCustom(className, isChecked) {
    const inputAbsent = $(`#absent_${className}`);
    const inputBunk = $(`#bunk_${className}`);
    const label = $(`#label_${className}`);

    if (isChecked) {
        inputAbsent.prop('disabled', false);
        inputBunk.prop('disabled', false);
        label.removeClass('bg-label-secondary').addClass('bg-label-primary').text('Custom Rate');
    } else {
        inputAbsent.prop('disabled', true).val($('#absent_rate').val());
        inputBunk.prop('disabled', true).val($('#bunk_rate').val());
        label.removeClass('bg-label-primary').addClass('bg-label-secondary').text('Global Default');
    }
}

function showAlert(type, message) {
    const alertBox = $('#statusAlert');
    alertBox.removeClass('d-none alert-success alert-danger alert-warning alert-info')
            .addClass(`alert-${type}`);
    $('#statusAlertText').html(message);
    
    Toast.fire({
        icon: type === 'danger' ? 'error' : (type === 'success' ? 'success' : 'info'),
        title: message.replace(/<[^>]*>?/gm, '')
    });
}

function saveSettings() {
    const form = $('#globalSettingsForm');
    const formData = form.serializeArray();

    // Collect Class-wise rates
    $('.class-toggle').each(function() {
        const cName = $(this).data('class');
        const isCustom = $(this).is(':checked') ? 1 : 0;
        const absentRate = $(`#absent_${cName}`).val();
        const bunkRate = $(`#bunk_${cName}`).val();

        formData.push({ name: `class_rates[${cName}][enabled]`, value: isCustom });
        formData.push({ name: `class_rates[${cName}][absent_rate]`, value: absentRate });
        formData.push({ name: `class_rates[${cName}][bunk_rate]`, value: bunkRate });
    });

    Swal.fire({
        title: 'Saving Settings...',
        text: 'Please wait while fine policy is being updated.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'ajax/save-fine-settings.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved Successfully',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Save Failed',
                    text: res.message || 'Error occurred while saving settings.'
                });
                showAlert('danger', `<i class="bi bi-exclamation-triangle me-1"></i> ${res.message || 'Error occurred while saving settings.'}`);
            }
        },
        error: function(xhr, status, error) {
            let errorMsg = error;
            try {
                if (xhr.responseText) {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) errorMsg = parsed.message;
                }
            } catch (e) {
                if (xhr.responseText) {
                    const plain = $('<div>').html(xhr.responseText).text().trim();
                    if (plain) errorMsg = plain.substring(0, 150);
                }
            }
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: errorMsg
            });
            showAlert('danger', `<i class="bi bi-x-circle me-1"></i> ${errorMsg}`);
        }
    });
}

function submitExemption() {
    const form = $('#formAddExemption');
    const title = form.find('input[name="title"]').val().trim();
    if (!title) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Field',
            text: 'Please provide a title or reason for the exemption.'
        });
        return;
    }

    const data = form.serialize() + '&action=add_disaster_exemption';

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#addExemptionModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Exemption Added',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'Failed to add exemption.'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Failed to connect to the server.'
            });
        }
    });
}

function deleteExemption(eventId) {
    Swal.fire({
        title: 'Delete Exemption?',
        text: 'Are you sure you want to remove this exemption record? Fines may apply on this date.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/fine-actions.php',
                type: 'POST',
                data: { 
                    action: 'delete_disaster_exemption', 
                    sccode: '<?= htmlspecialchars($sccode) ?>',
                    event_id: eventId 
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Server communication error during delete.'
                    });
                }
            });
        }
    });
}

function onGeneratorClassChange() {
    const selectedClass = $('#gen_class').val();
    const sectionSelect = $('#gen_section');
    sectionSelect.prop('disabled', true);

    $.ajax({
        url: 'ajax/fetch-academic-cascading.php',
        type: 'POST',
        data: {
            sccode: '<?= htmlspecialchars($sccode) ?>',
            sessionyear: '<?= htmlspecialchars($session) ?>',
            slot: '<?= htmlspecialchars($slot) ?>',
            classname: selectedClass === 'all' ? '' : selectedClass
        },
        dataType: 'json',
        success: function(res) {
            sectionSelect.empty().append('<option value="all">All Sections</option>');
            if (res.status === 'success' && res.sections && res.sections.length > 0) {
                res.sections.forEach(sec => {
                    sectionSelect.append(`<option value="${sec}">${sec}</option>`);
                });
            }
            sectionSelect.prop('disabled', false);
        },
        error: function() {
            sectionSelect.prop('disabled', false);
        }
    });
}

function filterStudentTable() {
    const filter = $('#filterStudentPreview').val().toLowerCase();
    $('#previewStudentTable tbody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(filter) > -1);
    });
}

function previewFines() {
    const fromDate = $('#gen_from_date').val();
    const toDate = $('#gen_to_date').val();
    const className = $('#gen_class').val();
    const sectionName = $('#gen_section').val() || 'all';
    const sessionYear = '<?= htmlspecialchars($session) ?>';
    const slot = '<?= htmlspecialchars($slot) ?>';
    const absentRate = $('#absent_rate').val();
    const bunkRate = $('#bunk_rate').val();

    const calcMode = $('#gen_calc_mode').val() || 'policy';

    if (!fromDate || !toDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Dates',
            text: 'Please select both start and end dates to compute fines.'
        });
        return;
    }

    $('#previewSummaryBox').addClass('d-none');
    $('#previewLoader').removeClass('d-none');

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: {
            action: 'preview_fines',
            sccode: '<?= htmlspecialchars($sccode) ?>',
            sessionyear: sessionYear,
            slot: slot,
            from_date: fromDate,
            to_date: toDate,
            classname: className,
            sectionname: sectionName,
            calc_mode: calcMode,
            temp_absent_rate: absentRate,
            temp_bunk_rate: bunkRate
        },
        dataType: 'json',
        success: function(res) {
            $('#previewLoader').addClass('d-none');
            if (res.status === 'success') {
                $('#previewSummaryBox').removeClass('d-none');
                $('#kpiStudents').text(res.summary.total_students);
                $('#kpiTotalFine').text(Number(res.summary.total_new_fine_amount || 0).toFixed(2));
                $('#kpiNewDaysBreakdown').text(`${res.summary.new_absent_days || 0} Absent, ${res.summary.new_bunk_days || 0} Bunk`);
                $('#kpiAlreadyBilledAmount').text(Number(res.summary.already_billed_amount || 0).toFixed(2));
                $('#kpiAlreadyBilledDays').text(res.summary.already_billed_days || 0);
                $('#kpiActiveDays').text(res.summary.active_school_days || 0);
                $('#kpiExemptInfo').text(`${res.summary.exempt_days_found || 0} exempted day(s)`);

                const modeLabel = res.summary.calc_mode === 'calendar_based' ? '<span class="badge bg-label-info ms-1">Calendar Working Days</span>' : '<span class="badge bg-label-primary ms-1">Attendance Days</span>';
                const weekendsTxt = res.summary.weekends_setting ? ` | Weekends: <strong class="text-dark">${res.summary.weekends_setting}</strong>` : '';
                $('#previewRangeLabel').html(`Calculated Range: ${res.summary.date_range} (Active: ${res.summary.active_school_days}, Exempted: ${res.summary.exempt_days_found}${weekendsTxt}) ${modeLabel}`);

                // 1. Render class-wise breakdown table
                const tbody = $('#previewBreakdownTable tbody');
                tbody.empty();
                if (!res.class_breakdown || res.class_breakdown.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center text-muted py-2">No fine records found for this period.</td></tr>');
                } else {
                    res.class_breakdown.forEach(item => {
                        tbody.append(`
                            <tr>
                                <td class="fw-bold">${item.classname}</td>
                                <td class="text-center">${item.students_count}</td>
                                <td class="text-center"><span class="badge bg-label-warning">${item.new_absent_days || 0}</span></td>
                                <td class="text-center"><span class="badge bg-label-danger">${item.new_bunk_days || 0}</span></td>
                                <td class="text-center"><span class="badge bg-label-info">${item.already_posted_days || 0} days</span></td>
                                <td class="text-end fw-bold text-success">৳ ${Number(item.total_new_fine || 0).toFixed(2)}</td>
                            </tr>
                        `);
                    });
                }

                // 2. Render student-wise breakdown table
                const stBody = $('#previewStudentTable tbody');
                stBody.empty();
                if (!res.student_preview || res.student_preview.length === 0) {
                    stBody.append('<tr><td colspan="9" class="text-center text-muted py-3"><i class="bi bi-info-circle me-1"></i> No absent or bunk student records found for the selected dates.</td></tr>');
                } else {
                    res.student_preview.forEach(st => {
                        stBody.append(`
                            <tr>
                                <td class="fw-bold">${st.rollno || '—'}</td>
                                <td><code>${st.stid}</code></td>
                                <td class="fw-bold text-dark">${st.stname || '—'}</td>
                                <td>${st.classname || '—'}</td>
                                <td>${st.sectionname || '—'}</td>
                                <td class="text-center"><span class="badge bg-label-warning">${st.new_absent_days || 0}</span></td>
                                <td class="text-center"><span class="badge bg-label-danger">${st.new_bunk_days || 0}</span></td>
                                <td class="text-center">${st.already_posted_days > 0 ? `<span class="badge bg-label-info">${st.already_posted_days} days (৳${Number(st.already_posted_amount).toFixed(0)})</span>` : '<span class="text-muted">—</span>'}</td>
                                <td class="text-end fw-bold text-success">৳ ${Number(st.new_fine_amount || 0).toFixed(2)}</td>
                            </tr>
                        `);
                    });
                }

                if (res.summary.total_students > 0) {
                    Toast.fire({
                        icon: 'success',
                        title: `Calculation complete: ${res.summary.total_students} student(s) found with fines.`
                    });
                } else {
                    let zeroMsg = 'No fine records generated for the selected date range.';
                    if (res.summary.active_school_days === 0) {
                        zeroMsg = 'No attendance entries were found in the database for the selected date range.';
                    } else {
                        zeroMsg = `Found ${res.summary.active_school_days} active school day(s), but all students were Present (100% attendance).`;
                    }
                    Toast.fire({
                        icon: 'info',
                        title: zeroMsg
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Preview Failed',
                    text: res.message || 'Error occurred while generating preview.'
                });
            }
        },
        error: function() {
            $('#previewLoader').addClass('d-none');
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Unable to communicate with the server.'
            });
        }
    });
}

function postFines() {
    const fromDate = $('#gen_from_date').val();
    const toDate = $('#gen_to_date').val();
    const className = $('#gen_class').val();
    const sectionName = $('#gen_section').val() || 'all';
    const calcMode = $('#gen_calc_mode').val() || 'policy';
    const sessionYear = '<?= htmlspecialchars($session) ?>';
    const slot = '<?= htmlspecialchars($slot) ?>';
    const absentRate = $('#absent_rate').val();
    const bunkRate = $('#bunk_rate').val();

    Swal.fire({
        title: 'Confirm Fine Posting',
        html: `Are you sure you want to post these calculated fines to students' <strong>stfinance</strong> accounts?<br><small class="text-muted">This will update student ledgers for ${fromDate} to ${toDate}.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Post Now',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = $('#btnPostFines');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Posting in progress...');

            Swal.fire({
                title: 'Posting Fines...',
                text: 'Updating student finance ledgers, please wait.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'ajax/fine-actions.php',
                type: 'POST',
                data: {
                    action: 'post_fines',
                    sccode: '<?= htmlspecialchars($sccode) ?>',
                    sessionyear: sessionYear,
                    slot: slot,
                    from_date: fromDate,
                    to_date: toDate,
                    classname: className,
                    sectionname: sectionName,
                    calc_mode: calcMode,
                    temp_absent_rate: absentRate,
                    temp_bunk_rate: bunkRate
                },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i> Post to stfinance Ledger');
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Posting Completed',
                            text: res.message
                        });
                        showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
                        $('#previewSummaryBox').addClass('d-none');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Posting Failed',
                            text: res.message || 'Posting could not be completed.'
                        });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i> Post to stfinance Ledger');
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Connection was interrupted during posting.'
                    });
                }
            });
        }
    });
}
</script>

