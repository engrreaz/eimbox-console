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
  `particularben` varchar(200) DEFAULT 'অনুপস্থিতি ও বাঙ্ক জরিমানা',
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
  `app_type` enum('advance_leave','post_leave','fine_waiver') NOT NULL DEFAULT 'advance_leave',
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `total_days` int(11) DEFAULT 1,
  `stfinance_id` int(11) DEFAULT NULL,
  `claimed_fine_amt` decimal(10,2) DEFAULT 0.00,
  `waiver_requested_amt` decimal(10,2) DEFAULT 0.00,
  `waiver_approved_amt` decimal(10,2) DEFAULT 0.00,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `applied_by` varchar(50) DEFAULT 'Student',
  `applied_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `action_by` varchar(100) DEFAULT NULL,
  `action_at` datetime DEFAULT NULL,
  `action_remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lookup` (`sccode`, `sessionyear`, `stid`, `status`),
  KEY `idx_dates` (`sccode`, `from_date`, `to_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci");

$slot = $_COOKIE['slot'] ?? $_GET['slot'] ?? 'School';
$session = $_COOKIE['session'] ?? $_GET['session'] ?? $sessionyear;

// 1. Fetch Existing Global Settings
$global_stmt = $conn->prepare("SELECT * FROM fine_settings WHERE sccode = ? AND sessionyear = ? AND slot = ? AND scope = 'global' LIMIT 1");
$global_stmt->bind_param('iss', $sccode, $session, $slot);
$global_stmt->execute();
$global_res = $global_stmt->get_result();
$global = $global_res->fetch_assoc() ?: [
    'absent_rate' => 10.00,
    'bunk_rate' => 20.00,
    'bunk_rule_type' => 'flat_daily',
    'posting_mode' => 'manual',
    'daily_run_time' => '18:00:00',
    'monthly_run_day' => 1,
    'itemcode' => 'FINE01',
    'particulareng' => 'Absence / Bunk Fine',
    'particularben' => 'অনুপস্থিতি ও বাঙ্ক জরিমানা'
];
$global_stmt->close();

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
$cQuery = $conn->prepare("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = ? AND sessionyear = ? AND classname IS NOT NULL AND classname != '' ORDER BY id ASC");
$cQuery->bind_param('is', $sccode, $session);
$cQuery->execute();
$cRes = $cQuery->get_result();
while ($row = $cRes->fetch_assoc()) {
    $classes[] = $row['classname'];
}
$cQuery->close();

// 4. Fetch Recent Disaster / Exemption Events
$exemptEvents = [];
$evStmt = $conn->prepare("SELECT id, title, start, end, color FROM events WHERE sccode = ? AND event_type IN ('holiday', 'other') ORDER BY start DESC LIMIT 20");
$evStmt->bind_param('i', $sccode);
$evStmt->execute();
$evRes = $evStmt->get_result();
while ($e = $evRes->fetch_assoc()) {
    $exemptEvents[] = $e;
}
$evStmt->close();
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Quick Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-shield-slash-fill text-danger me-2"></i> শিক্ষার্থী জরিমানা সেটিংস ও অটোমেশন
            </h4>
            <span class="text-muted">অনুপস্থিতি ও বাঙ্ক (পলায়ন) জরিমানার হার, পোস্টিং শিডিউল এবং দুর্যোগ/ছুটি ছাড় কনফিগারেশন</span>
        </div>
        <div class="d-flex gap-2">
            <a href="sync-payments.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cash-stack me-1"></i> পেমেন্ট সিঙ্ক
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="saveSettings()">
                <i class="bi bi-check2-circle me-1"></i> সেটিংস সংরক্ষণ করুন
            </button>
        </div>
    </div>

    <!-- Filter Bar / Slot & Session -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row align-items-end g-2">
                <?php
                $chain_param = '-c 10 -t নির্বাচন করুন -u -r -b সেটিংস দেখুন -h class exam';
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
                    <i class="bi bi-sliders2 me-1"></i> গ্লোবাল পলিসি ও শিডিউলার
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-classes">
                    <i class="bi bi-diagram-3 me-1"></i> শ্রেণিভিত্তিক কাস্টম রেট 
                    <span class="badge rounded-pill bg-label-primary ms-1"><?= count($classes) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-exemptions">
                    <i class="bi bi-cloud-sun me-1"></i> দুর্যোগ ও ছুটির ছাড়
                    <span class="badge rounded-pill bg-label-warning ms-1"><?= count($exemptEvents) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-generator">
                    <i class="bi bi-play-circle me-1"></i> তাৎক্ষণিক জরিমানা জেনারেশন
                </button>
            </li>
        </ul>

        <div class="tab-content border-top-0 p-4">

            <!-- TAB 1: GLOBAL POLICY & SCHEDULER -->
            <div class="tab-pane fade show active" id="tab-global" role="tabpanel">
                <form id="globalSettingsForm">
                    <input type="hidden" name="sessionyear" value="<?= htmlspecialchars($session) ?>">
                    <input type="hidden" name="slot" value="<?= htmlspecialchars($slot) ?>">

                    <div class="row g-4">
                        <!-- Rates Card -->
                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-currency-dollar text-primary me-2"></i>ডিফল্ট জরিমানা রেট</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">অনুপস্থিতি জরিমানা (প্রতিদিন) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.5" class="form-control" name="absent_rate" id="absent_rate" value="<?= htmlspecialchars($global['absent_rate']) ?>" required>
                                            <span class="input-group-text">টাকা</span>
                                        </div>
                                        <small class="text-muted">শিক্ষার্থী উপস্থিত না থাকলে (yn = 0) এই রেটে জরিমানা ধার্য হবে।</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">বাঙ্ক / পলায়ন জরিমানা <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.5" class="form-control" name="bunk_rate" id="bunk_rate" value="<?= htmlspecialchars($global['bunk_rate']) ?>" required>
                                            <span class="input-group-text">টাকা</span>
                                        </div>
                                        <small class="text-muted">ক্লাস চলাকালীন পলায়ন (bunk = 1) করলে ধার্যকৃত জরিমানা।</small>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-bold">বাঙ্ক জরিমানা গণনা পদ্ধতি</label>
                                        <select class="form-select" name="bunk_rule_type" id="bunk_rule_type">
                                            <option value="flat_daily" <?= $global['bunk_rule_type'] === 'flat_daily' ? 'selected' : '' ?>>দিনে একবার নির্দিষ্ট রেট (Flat Daily Rate)</option>
                                            <option value="per_period" <?= $global['bunk_rule_type'] === 'per_period' ? 'selected' : '' ?>>প্রতি পিরিয়ড বাঙ্কের জন্য আলাদা (Per Period)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduler Card -->
                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-info me-2"></i>পোস্টিং ও অটোমেশন শিডিউল</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">জরিমানা পোস্টিং মেকানিজম</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_manual" value="manual" <?= $global['posting_mode'] === 'manual' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_manual">
                                                <strong>শুধুমাত্র ম্যানুয়াল (Manual Only)</strong>
                                                <div class="text-muted small">অ্যাডমিন যখন 'তাৎক্ষণিক জরিমানা জেনারেশন' থেকে রান করবেন তখনই লেজারে পোস্ট হবে।</div>
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_daily" value="daily" <?= $global['posting_mode'] === 'daily' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_daily">
                                                <strong>দৈনিক স্বয়ংক্রিয় পোস্টিং (Daily Auto-Posting)</strong>
                                                <div class="text-muted small">প্রতিদিন নির্দিষ্ট সময়ে স্বয়ংক্রিয়ভাবে শিক্ষার্থীদের অ্যাকাউন্টে ফাইন যুক্ত হবে।</div>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="posting_mode" id="mode_monthly" value="monthly" <?= $global['posting_mode'] === 'monthly' ? 'checked' : '' ?> onchange="togglePostingOptions()">
                                            <label class="form-check-label" for="mode_monthly">
                                                <strong>মাসিক এককালীন পোস্টিং (Monthly Consolidated)</strong>
                                                <div class="text-muted small">পুরো মাসের পুঞ্জীভূত জরিমানা মাসের নির্দিষ্ট তারিখে এককালীন ধার্য হবে।</div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Daily Options -->
                                    <div id="dailyOptionBox" class="mb-3 <?= $global['posting_mode'] === 'daily' ? '' : 'd-none' ?>">
                                        <label class="form-label fw-bold">প্রতিদিন কয়টায় পোস্টিং রান হবে?</label>
                                        <input type="time" class="form-control" name="daily_run_time" id="daily_run_time" value="<?= htmlspecialchars($global['daily_run_time']) ?>">
                                    </div>

                                    <!-- Monthly Options -->
                                    <div id="monthlyOptionBox" class="mb-3 <?= $global['posting_mode'] === 'monthly' ? '' : 'd-none' ?>">
                                        <label class="form-label fw-bold">মাসের কত তারিখে রান হবে?</label>
                                        <div class="input-group">
                                            <span class="input-group-text">প্রতি মাসের</span>
                                            <input type="number" min="1" max="28" class="form-control" name="monthly_run_day" id="monthly_run_day" value="<?= htmlspecialchars($global['monthly_run_day']) ?>">
                                            <span class="input-group-text">তারিখে</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finance Ledger Mapping Card -->
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header border-bottom bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 text-success me-2"></i>স্টুডেন্ট ফাইন্যান্স লেজার ম্যাপিং (`stfinance`)</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold">ফি আইটেম কোড (Item Code)</label>
                                            <input type="text" class="form-control" name="itemcode" id="itemcode" value="<?= htmlspecialchars($global['itemcode']) ?>" required>
                                            <small class="text-muted">উদাঃ FINE01</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">ইংরেজি বিবরণ (Particular Eng)</label>
                                            <input type="text" class="form-control" name="particulareng" id="particulareng" value="<?= htmlspecialchars($global['particulareng']) ?>" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">বাংলা বিবরণ (Particular Ben)</label>
                                            <input type="text" class="form-control" name="particularben" id="particularben" value="<?= htmlspecialchars($global['particularben']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-primary px-4" onclick="saveSettings()">
                            <i class="bi bi-save me-1"></i> সেটিংস সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: CLASS-WISE CUSTOM RATES -->
            <div class="tab-pane fade" id="tab-classes" role="tabpanel">
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle me-1"></i> যেসকল শ্রেণির জন্য পৃথক কাস্টম রেট প্রয়োজন, কেবল সেগুলোতে <strong>"কাস্টম রেট"</strong> সুইচ অন করুন। অন্যথায় ঐ শ্রেণির জন্য গ্লোবাল ডিফল্ট রেট স্বয়ংক্রিয়ভাবে প্রযোজ্য হবে।
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>শ্রেণির নাম</th>
                                <th style="width: 180px;">পলিসি মোড</th>
                                <th style="width: 200px;">অনুপস্থিতি জরিমানা (৳)</th>
                                <th style="width: 200px;">বাঙ্ক জরিমানা (৳)</th>
                                <th style="width: 140px;">স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($classes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                        চলতি সেশন ও শিফটে কোনো শ্রেণি পাওয়া যায়নি।
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
                                                        <?= $isCustom ? 'কাস্টম রেট' : 'গ্লোবাল ডিফল্ট' ?>
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
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>সক্রিয় ওভাররাইড</span>
                                            <?php else: ?>
                                                <span class="badge bg-label-secondary"><i class="bi bi-dash-circle me-1"></i>ডিফল্ট রেট</span>
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
                        <i class="bi bi-save me-1"></i> সকল রেট সংরক্ষণ করুন
                    </button>
                </div>
            </div>

            <!-- TAB 3: DISASTER & HOLIDAY EXEMPTIONS -->
            <div class="tab-pane fade" id="tab-exemptions" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-success me-1"></i> দুর্যোগ ও সংরক্ষিত ছুটিসমূহ</h6>
                        <small class="text-muted">এই তালিকাভুক্ত দিনগুলোতে স্বয়ংক্রিয়ভাবে শিক্ষার্থীদের কোনো অনুপস্থিতি বা বাঙ্ক জরিমানা হবে না।</small>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addExemptionModal">
                        <i class="bi bi-plus-circle me-1"></i> নতুন দুর্যোগ / ছাড় যুক্ত করুন
                    </button>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>ছুটি / দুর্যোগের কারণ ও শিরোনাম</th>
                                <th style="width: 150px;">শুরুর তারিখ</th>
                                <th style="width: 150px;">শেষ তারিখ</th>
                                <th style="width: 130px;">প্রভাব</th>
                                <th style="width: 100px;" class="text-center">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($exemptEvents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-calendar-x fs-2 d-block mb-1"></i>
                                        কোনো বিশেষ দুর্যোগ বা সংরক্ষিত ছুটির রেকর্ড পাওয়া যায়নি।
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
                                                <i class="bi bi-check2-all me-1"></i> জরিমানা মওকুফ
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="deleteExemption(<?= $ev['id'] ?>)" title="মুছে ফেলুন">
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
                        <h6 class="mb-0 fw-bold"><i class="bi bi-gear-wide-connected text-primary me-2"></i>জরিমানা গণনা ও সিঙ্ক কনসোল</h6>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">শুরুর তারিখ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="gen_from_date" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">শেষ তারিখ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="gen_to_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">শ্রেণি ফিল্টার</label>
                                <select class="form-select" id="gen_class">
                                    <option value="all">সকল শ্রেণি (All Classes)</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info w-100" id="btnPreviewFines" onclick="previewFines()">
                                    <i class="bi bi-search me-1"></i> হিসাব প্রিভিউ করুন
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview KPI Summary -->
                <div id="previewSummaryBox" class="d-none mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center">
                                <div class="text-muted small">মোট জরিমানাভুক্ত শিক্ষার্থী</div>
                                <h4 class="mb-0 text-primary fw-bold" id="kpiStudents">0</h4>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center">
                                <div class="text-muted small">অনুপস্থিত দিন সংখ্যা</div>
                                <h4 class="mb-0 text-warning fw-bold" id="kpiAbsentDays">0</h4>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center">
                                <div class="text-muted small">বাঙ্ক (পলায়ন) সংখ্যা</div>
                                <h4 class="mb-0 text-danger fw-bold" id="kpiBunkDays">0</h4>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border shadow-none p-3 text-center">
                                <div class="text-muted small">মোট জরিমানা পরিমাণ</div>
                                <h4 class="mb-0 text-success fw-bold">৳ <span id="kpiTotalFine">0.00</span></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Post Button Bar -->
                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded border">
                        <div>
                            <span class="fw-bold text-dark" id="previewRangeLabel"></span>
                            <div class="text-muted small">হিসাব সঠিক থাকলে সরাসরি শিক্ষার্থীদের লেজারে পোস্ট করতে নিচের বাটনটি চাপুন।</div>
                        </div>
                        <button type="button" class="btn btn-success px-4" id="btnPostFines" onclick="postFines()">
                            <i class="bi bi-send-check me-1"></i> stfinance এ পোস্ট করুন
                        </button>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-1"></i> শ্রেণিভিত্তিক সারসংক্ষেপ</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-striped align-middle mb-0" id="previewBreakdownTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>শ্রেণি</th>
                                        <th class="text-center">শিক্ষার্থী সংখ্যা</th>
                                        <th class="text-center">অনুপস্থিত দিন</th>
                                        <th class="text-center">বাঙ্ক দিন</th>
                                        <th class="text-end">মোট জরিমানা</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="previewLoader" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">উপস্থিতি ডাটা যাচাই ও জরিমানা হিসাব করা হচ্ছে...</div>
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
                <h5 class="modal-title text-white"><i class="bi bi-cloud-lightning-rain me-1"></i> দুর্যোগ / বিশেষ ছাড় যুক্ত করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddExemption">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ছাড়ের কারণ বা শিরোনাম <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="উদাঃ তীব্র শৈত্যপ্রবাহ / অতিবৃষ্টির কারণে ছুটি" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">শুরুর তারিখ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">শেষ তারিখ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i> এই তারিখগুলোতে শিক্ষার্থীদের কোনো অনুপস্থিতি বা বাঙ্ক জরিমানা স্বয়ংক্রিয়ভাবে বাতিল থাকবে।
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
                <button type="button" class="btn btn-primary" onclick="submitExemption()">সংরক্ষণ করুন</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
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
        label.removeClass('bg-label-secondary').addClass('bg-label-primary').text('কাস্টম রেট');
    } else {
        inputAbsent.prop('disabled', true).val($('#absent_rate').val());
        inputBunk.prop('disabled', true).val($('#bunk_rate').val());
        label.removeClass('bg-label-primary').addClass('bg-label-secondary').text('গ্লোবাল ডিফল্ট');
    }
}

function showAlert(type, message) {
    const alertBox = $('#statusAlert');
    alertBox.removeClass('d-none alert-success alert-danger alert-warning alert-info')
            .addClass(`alert-${type}`);
    $('#statusAlertText').html(message);
    window.scrollTo({ top: 0, behavior: 'smooth' });
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

    $.ajax({
        url: 'ajax/save-fine-settings.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
            } else {
                showAlert('danger', `<i class="bi bi-exclamation-triangle me-1"></i> ${res.message || 'সেটিংস সংরক্ষণে ত্রুটি হয়েছে।'}`);
            }
        },
        error: function(xhr, status, error) {
            showAlert('danger', `<i class="bi bi-x-circle me-1"></i> সার্ভারের সাথে সংযোগে ত্রুটি: ${error}`);
        }
    });
}

function submitExemption() {
    const form = $('#formAddExemption');
    const data = form.serialize() + '&action=add_disaster_exemption';

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#addExemptionModal').modal('hide');
                showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(res.message || 'ত্রুটি হয়েছে।');
            }
        },
        error: function() {
            alert('সার্ভার রেসপন্স পাওয়া যায়নি।');
        }
    });
}

function deleteExemption(eventId) {
    if (!confirm('আপনি কি নিশ্চিত যে এই ছাড়ের রেকর্ডটি মুছে ফেলতে চান?')) return;

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: { action: 'delete_disaster_exemption', event_id: eventId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', res.message);
            }
        }
    });
}

function previewFines() {
    const fromDate = $('#gen_from_date').val();
    const toDate = $('#gen_to_date').val();
    const className = $('#gen_class').val();
    const sessionYear = '<?= htmlspecialchars($session) ?>';
    const slot = '<?= htmlspecialchars($slot) ?>';

    if (!fromDate || !toDate) {
        alert('শুরুর ও শেষ তারিখ নির্বাচন করুন।');
        return;
    }

    $('#previewSummaryBox').addClass('d-none');
    $('#previewLoader').removeClass('d-none');

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: {
            action: 'preview_fines',
            sessionyear: sessionYear,
            slot: slot,
            from_date: fromDate,
            to_date: toDate,
            classname: className
        },
        dataType: 'json',
        success: function(res) {
            $('#previewLoader').addClass('d-none');
            if (res.status === 'success') {
                $('#previewSummaryBox').removeClass('d-none');
                $('#kpiStudents').text(res.summary.total_students);
                $('#kpiAbsentDays').text(res.summary.total_absent_days);
                $('#kpiBunkDays').text(res.summary.total_bunk_days);
                $('#kpiTotalFine').text(Number(res.summary.total_fine_amount).toFixed(2));
                $('#previewRangeLabel').text(`হিসাবকৃত সময়সীমা: ${res.summary.date_range} (ছুটি/দুর্যোগ দিন: ${res.summary.exempt_days_found} টি)`);

                // Render breakdown table
                const tbody = $('#previewBreakdownTable tbody');
                tbody.empty();
                if (res.class_breakdown.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center text-muted py-2">কোনো জরিমানা হিসাব পাওয়া যায়নি।</td></tr>');
                } else {
                    res.class_breakdown.forEach(item => {
                        tbody.append(`
                            <tr>
                                <td class="fw-bold">${item.classname}</td>
                                <td class="text-center">${item.students_count}</td>
                                <td class="text-center">${item.absent_days}</td>
                                <td class="text-center">${item.bunk_days}</td>
                                <td class="text-end fw-bold text-success">৳ ${Number(item.total_fine).toFixed(2)}</td>
                            </tr>
                        `);
                    });
                }
            } else {
                showAlert('danger', res.message || 'প্রিভিউ তৈরিতে সমস্যা হয়েছে।');
            }
        },
        error: function() {
            $('#previewLoader').addClass('d-none');
            showAlert('danger', 'সার্ভারের সাথে সংযোগ স্থাপন করা সম্ভব হয়নি।');
        }
    });
}

function postFines() {
    if (!confirm('আপনি কি নিশ্চিত যে এই জরিমানা শিক্ষার্থীদের stfinance লেজারে পোস্ট করতে চান?')) return;

    const fromDate = $('#gen_from_date').val();
    const toDate = $('#gen_to_date').val();
    const className = $('#gen_class').val();
    const sessionYear = '<?= htmlspecialchars($session) ?>';
    const slot = '<?= htmlspecialchars($slot) ?>';

    const btn = $('#btnPostFines');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> পোস্ট হচ্ছে...');

    $.ajax({
        url: 'ajax/fine-actions.php',
        type: 'POST',
        data: {
            action: 'post_fines',
            sessionyear: sessionYear,
            slot: slot,
            from_date: fromDate,
            to_date: toDate,
            classname: className
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i> stfinance এ পোস্ট করুন');
            if (res.status === 'success') {
                showAlert('success', `<i class="bi bi-check-circle me-1"></i> ${res.message}`);
                $('#previewSummaryBox').addClass('d-none');
            } else {
                showAlert('danger', res.message || 'পোস্টিং সম্পন্ন হয়নি।');
            }
        },
        error: function() {
            btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i> stfinance এ পোস্ট করুন');
            showAlert('danger', 'পোস্টিংয়ের সময় সংযোগ বিচ্ছিন্ন হয়েছে।');
        }
    });
}
</script>
