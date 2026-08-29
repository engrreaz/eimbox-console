<?php
/**
 * EIMBox REST API - Contextual FAQ & Help Management Engine
 * Endpoint: /api/v1/support/faq.php
 * Supports: GET, POST, PUT, DELETE, and action=rate (helpful count)
 */

require_once __DIR__ . '/../bootstrap.php';

// Auto-create faq_desktop table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `faq_desktop` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sccode` INT NOT NULL DEFAULT 0 COMMENT '0 = All Institutes (Global), Specific code = Custom for Institute',
  `module_name` VARCHAR(100) NOT NULL DEFAULT 'general' COMMENT 'academics, attendance, finance, exam, admin, settings, etc.',
  `screen_key` VARCHAR(100) NOT NULL DEFAULT 'global' COMMENT 'Unique route/screen key: e.g., dashboard, mark-entry, student-profile, pos-counter',
  `page_title` VARCHAR(255) DEFAULT NULL COMMENT 'Screen/Page display title',
  `screen_description` TEXT DEFAULT NULL COMMENT 'Short description/guide for this screen',
  `question_bn` VARCHAR(500) NOT NULL COMMENT 'প্রশ্ন (বাংলা)',
  `question_en` VARCHAR(500) DEFAULT NULL COMMENT 'Question in English',
  `answer_bn` LONGTEXT NOT NULL COMMENT 'বিস্তারিত উত্তর ও গাইডলাইন (বাংলা)',
  `answer_en` LONGTEXT DEFAULT NULL COMMENT 'Detailed Answer/Guideline in English',
  `target_roles` VARCHAR(255) NOT NULL DEFAULT 'all' COMMENT 'Comma-separated roles: superadmin, admin, teacher, headteacher, student, guardian, all',
  `tags` VARCHAR(255) DEFAULT NULL COMMENT 'Search keywords (comma-separated)',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Sorting sequence within current screen',
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Pinned at top',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `view_count` INT NOT NULL DEFAULT 0,
  `helpful_count` INT NOT NULL DEFAULT 0,
  `created_by` VARCHAR(100) DEFAULT NULL,
  `updated_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifieddate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_faq_screen` (`screen_key`, `is_active`),
  INDEX `idx_faq_module` (`module_name`, `is_active`),
  INDEX `idx_faq_sccode` (`sccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Seed default initial records if table is empty
$countRes = $conn->query("SELECT COUNT(*) AS total FROM `faq_desktop`");
$totalRows = ($countRes && $r = $countRes->fetch_assoc()) ? intval($r['total']) : 0;

if ($totalRows === 0) {
    $seedQueries = [
        [
            'sccode' => 0,
            'module_name' => 'general',
            'screen_key' => 'academic-cache',
            'page_title' => 'কমান্ড সেন্টার ও প্রধান ড্যাশবোর্ড',
            'screen_description' => 'প্রতিষ্ঠানের সামগ্রিক শিক্ষার্থী, শিক্ষক, আর্থিক অবস্থা ও দৈনিক উপস্থিতির সারসংক্ষেপ স্ক্রিন।',
            'question_bn' => 'ড্যাশবোর্ডের ডেটা কতক্ষণ পর পর ক্লাউড সার্ভারের সাথে সিঙ্ক হয়?',
            'question_en' => 'How frequently does dashboard data sync with the cloud server?',
            'answer_bn' => 'ড্যাশবোর্ডের লাইভ অ্যানালিটিক্স এবং অফলাইন পরিবর্তনসমূহ প্রতি ৫ মিনিট পর পর ব্যাকগ্রাউন্ডে স্বয়ংক্রিয়ভাবে সিঙ্ক হয়। এছাড়া আপনি কীবোর্ডের F5 প্রেস করে অথবা স্ট্যাটাসবারের সিঙ্ক আইকনে ক্লিক করে তাৎক্ষণিক ফোর্স সিঙ্ক করতে পারেন।',
            'answer_en' => 'Dashboard live analytics and offline local changes automatically sync in the background every 5 minutes. You can also press F5 or click the status bar sync icon to force an immediate sync.',
            'target_roles' => 'all',
            'tags' => 'dashboard,sync,f5,analytics',
            'sort_order' => 1,
            'is_pinned' => 1
        ],
        [
            'sccode' => 0,
            'module_name' => 'academics',
            'screen_key' => 'student-profile',
            'page_title' => 'শিক্ষার্থী প্রোফাইল ও ভর্তি ফরম',
            'screen_description' => 'নতুন শিক্ষার্থী ভর্তি, তথ্য হালনাগাদ, ছবি আপলোড এবং অভিভাবকের তথ্য সংরক্ষণের স্ক্রিন।',
            'question_bn' => 'ইন্টারনেট সংযোগ না থাকলে কি নতুন শিক্ষার্থী ভর্তি করা যাবে?',
            'question_en' => 'Can new students be admitted without an active internet connection?',
            'answer_bn' => 'হ্যাঁ, EIMBox সম্পূর্ণ অফলাইন-ফার্স্ট আর্কিটেকচারে কাজ করে। অফলাইনে ভর্তি করলে ডেটা সরাসরি লোকাল SQLite ডেটাবেসে সংরক্ষিত হবে এবং স্ট্যাটাসবারে পেন্ডিং কিউ দেখাবে। পরবর্তীতে ইন্টারনেট পাওয়া মাত্রই তা ক্লাউড সার্ভারে সিঙ্ক হয়ে যাবে।',
            'answer_en' => 'Yes, EIMBox runs in offline-first mode. When offline, student profiles are saved directly to local SQLite database and queued. As soon as connectivity returns, they will automatically sync to cloud.',
            'target_roles' => 'superadmin,admin,headteacher,teacher',
            'tags' => 'student,admission,offline,sqlite',
            'sort_order' => 1,
            'is_pinned' => 1
        ],
        [
            'sccode' => 0,
            'module_name' => 'attendance',
            'screen_key' => 'live-attendance',
            'page_title' => 'লাইভ হাজিরা মনিটর',
            'screen_description' => 'ZKTeco বায়োমেট্রিক ডিভাইস, RFID কার্ড ও ম্যানুয়াল হাজিরার রিয়েল-টাইম মনিটরিং স্ক্রিন।',
            'question_bn' => 'বায়োমেট্রিক ডিভাইস থেকে সরাসরি কীভাবে অফলাইন পাঞ্চ ডেটা আনা যায়?',
            'question_en' => 'How to pull offline biometric punches from ZKTeco device?',
            'answer_bn' => 'টপ মেনুবারের Attendance -> "Pull Punches from ZKTeco K40" অপশনে ক্লিক করুন। অথবা পেনড্রাইভে নেওয়া attlog.dat ফাইল "Import Offline USB attlog.dat" দিয়ে সরাসরি ইমপোর্ট করতে পারেন।',
            'answer_en' => 'Go to Attendance menu -> click "Pull Punches from ZKTeco K40". Alternatively, you can import USB flash drive attlog.dat files via "Import Offline USB attlog.dat".',
            'target_roles' => 'superadmin,admin,headteacher,teacher',
            'tags' => 'attendance,zkteco,biometric,usb,attlog',
            'sort_order' => 1,
            'is_pinned' => 1
        ],
        [
            'sccode' => 0,
            'module_name' => 'exam',
            'screen_key' => 'marks-grid',
            'page_title' => 'নম্বর এন্ট্রি গ্রিড (Marks Entry Grid)',
            'screen_description' => 'ক্লাস, সেকশন ও বিষয় অনুযায়ী দ্রুত গতিতে নম্বর এন্ট্রি, ড্রাফট সংরক্ষণ এবং গ্রেড ক্যালকুলেশন স্ক্রিন।',
            'question_bn' => 'নম্বর এন্ট্রি করার সময় কি বারবার সেভ করতে হবে?',
            'question_en' => 'Do I need to save repeatedly during mark entry?',
            'answer_bn' => 'প্রতিটি সেলে নম্বর লিখে Enter বা Arrow Key চাপার সাথে সাথে তা লোকাল মেমোরি ও ড্রাফটে সেভ হতে থাকে। তবে সম্পূর্ণ এন্ট্রি শেষে উপরে ডানে থাকা "সংরক্ষণ করুন (Ctrl+S)" বাটনে ক্লিক করে ডেটাবেজে ফাইনাল সেভ নিশ্চিত করুন।',
            'answer_en' => 'Every cell entry is automatically captured into local fast draft. However, click "Save Marks (Ctrl+S)" button when finished to permanently persist records.',
            'target_roles' => 'superadmin,admin,headteacher,teacher',
            'tags' => 'marks,exam,grid,grading,save',
            'sort_order' => 1,
            'is_pinned' => 1
        ],
        [
            'sccode' => 0,
            'module_name' => 'finance',
            'screen_key' => 'pos-counter',
            'page_title' => 'দ্রুত ফি আদায় কাউন্টার (POS Counter)',
            'screen_description' => 'শিক্ষার্থীদের বেতন, সেশন ফি, পরীক্ষার ফি আদায় ও তাত্ক্ষণিক মানি রিসিট প্রিন্টিং স্ক্রিন।',
            'question_bn' => 'ভুল রিসিট বাতিল করে পুনরায় ফি আদায় করার নিয়ম কী?',
            'question_en' => 'How to cancel a wrong receipt and collect again?',
            'answer_bn' => 'Collection Query স্ক্রিনে গিয়ে রিসিট নম্বর বা Student ID দিয়ে সার্চ করুন। অ্যাডমিন রোল থাকলে "Void / Cancel Receipt" বাটনে ক্লিক করে কারণ লিখে বাতিল করা যাবে। এরপর POS কাউন্টারে সঠিক ফি আদায় করুন।',
            'answer_en' => 'Search the receipt in Collection Query. With Admin privileges, click "Void / Cancel Receipt" with reason. Then re-collect properly in POS Counter.',
            'target_roles' => 'superadmin,admin,headteacher,accountant',
            'tags' => 'pos,fees,receipt,cancel,money',
            'sort_order' => 1,
            'is_pinned' => 1
        ],
        [
            'sccode' => 0,
            'module_name' => 'general',
            'screen_key' => 'global',
            'page_title' => 'সাধারণ প্রশ্নোত্তর ও হেল্প গাইড',
            'screen_description' => 'EIMBox প্ল্যাটফর্ম ব্যবহারের সাধারণ নির্দেশিকা ও টিপস।',
            'question_bn' => 'সফটওয়্যার আপডেট এলে কীভাবে আপডেট গ্রহণ করবো?',
            'question_en' => 'How to update EIMBox software when a new version is released?',
            'answer_bn' => 'নতুন আপডেট উপলভ্য হলে অ্যাপ চালুর সময় নোটিফিকেশন আসবে। এছাড়া Help -> "Check for Updates" অপশনে ক্লিক করে যেকোনো সময় লাইভ ক্লাউড আপডেট চেক ও ইনস্টল করা যায়।',
            'answer_en' => 'A notification banner appears automatically when updates are available. You can also manually check via Help -> "Check for Updates" menu.',
            'target_roles' => 'all',
            'tags' => 'update,general,help,release',
            'sort_order' => 99,
            'is_pinned' => 0
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO `faq_desktop` (`sccode`, `module_name`, `screen_key`, `page_title`, `screen_description`, `question_bn`, `question_en`, `answer_bn`, `answer_en`, `target_roles`, `tags`, `sort_order`, `is_pinned`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    foreach ($seedQueries as $q) {
        $stmt->bind_param('issssssssssii', $q['sccode'], $q['module_name'], $q['screen_key'], $q['page_title'], $q['screen_description'], $q['question_bn'], $q['question_en'], $q['answer_bn'], $q['answer_en'], $q['target_roles'], $q['tags'], $q['sort_order'], $q['is_pinned']);
        $stmt->execute();
    }
    $stmt->close();
}

function get_auth_user_optional() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $parts = explode('.', $matches[1]);
        if (count($parts) === 2) {
            $payloadJson = base64_decode($parts[0]);
            $signature = $parts[1];
            $expectedSig = hash_hmac('sha256', $payloadJson, 'EIMBox_Secret_Key_2026_Studio');
            if (hash_equals($expectedSig, $signature)) {
                return json_decode($payloadJson, true) ?? null;
            }
        }
    }
    return null;
}

$user = get_auth_user_optional();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = get_api_input();

// -------------------------------------------------------------
// GET: Fetch FAQs (Filtered by screen_key, module, role, search)
// -------------------------------------------------------------
if ($method === 'GET') {
    $screenKey = trim($_GET['screen_key'] ?? '');
    $moduleName = trim($_GET['module_name'] ?? '');
    $sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
    $search = trim($_GET['search'] ?? '');
    $role = strtolower(trim($_GET['role'] ?? $user['user_role'] ?? 'all'));
    $includeInactive = isset($_GET['all']) && ($_GET['all'] == '1' || $_GET['all'] == 'true');

    $where = [];
    $params = [];
    $types = '';

    if (!$includeInactive) {
        $where[] = "is_active = 1";
    }

    if (!empty($screenKey)) {
        if ($screenKey === 'all') {
            // No screen filter
        } else {
            // Retrieve contextual screen FAQs as well as global FAQs
            $where[] = "(screen_key = ? OR screen_key = 'global')";
            $params[] = $screenKey;
            $types .= 's';
        }
    }

    if (!empty($moduleName) && $moduleName !== 'all') {
        $where[] = "(module_name = ? OR module_name = 'general')";
        $params[] = $moduleName;
        $types .= 's';
    }

    if ($sccode > 0) {
        $where[] = "(sccode = 0 OR sccode = ?)";
        $params[] = $sccode;
        $types .= 'i';
    }

    if (!empty($search)) {
        $where[] = "(question_bn LIKE ? OR question_en LIKE ? OR answer_bn LIKE ? OR answer_en LIKE ? OR tags LIKE ? OR page_title LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= 'ssssss';
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $sql = "SELECT * FROM `faq_desktop` $whereSql ORDER BY is_pinned DESC, sort_order ASC, id ASC";

    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($sql);
    }

    $items = [];
    while ($row = $res->fetch_assoc()) {
        // Filter by target_roles if role specified and not 'all'
        if ($role !== 'all' && $role !== 'superadmin' && $role !== 'admin') {
            $allowedRoles = array_map('trim', explode(',', strtolower($row['target_roles'] ?? 'all')));
            if (!in_array('all', $allowedRoles) && !in_array($role, $allowedRoles)) {
                continue;
            }
        }
        $items[] = $row;
    }

    // Extract active screen meta if screen_key requested
    $screenMeta = null;
    if (!empty($screenKey) && $screenKey !== 'all') {
        foreach ($items as $item) {
            if ($item['screen_key'] === $screenKey && !empty($item['page_title'])) {
                $screenMeta = [
                    'screen_key' => $screenKey,
                    'page_title' => $item['page_title'],
                    'screen_description' => $item['screen_description'] ?? '',
                    'module_name' => $item['module_name'] ?? 'general'
                ];
                break;
            }
        }
    }

    api_response('success', 'FAQ items retrieved successfully', [
        'screen_meta' => $screenMeta,
        'count' => count($items),
        'faqs' => $items
    ]);
}

// -------------------------------------------------------------
// POST: Create FAQ or Rate/Helpful
// -------------------------------------------------------------
if ($method === 'POST') {
    $action = trim($input['action'] ?? $_POST['action'] ?? 'create');

    // Handle Helpful rating
    if ($action === 'helpful' || $action === 'rate') {
        $faqId = intval($input['id'] ?? 0);
        if ($faqId <= 0) {
            api_response('error', 'Valid FAQ ID required for rating', null, 400);
        }
        $isHelpful = isset($input['is_helpful']) ? (bool)$input['is_helpful'] : true;
        if ($isHelpful) {
            $conn->query("UPDATE `faq_desktop` SET helpful_count = helpful_count + 1, view_count = view_count + 1 WHERE id = $faqId");
        } else {
            $conn->query("UPDATE `faq_desktop` SET view_count = view_count + 1 WHERE id = $faqId");
        }
        api_response('success', 'Thank you for your feedback.', ['id' => $faqId]);
    }

    // Handle Create FAQ
    $sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
    $moduleName = trim($input['module_name'] ?? 'general');
    $screenKey = trim($input['screen_key'] ?? 'global');
    $pageTitle = trim($input['page_title'] ?? '');
    $screenDescription = trim($input['screen_description'] ?? '');
    $questionBn = trim($input['question_bn'] ?? '');
    $questionEn = trim($input['question_en'] ?? '');
    $answerBn = trim($input['answer_bn'] ?? '');
    $answerEn = trim($input['answer_en'] ?? '');
    $targetRoles = trim($input['target_roles'] ?? 'all');
    $tags = trim($input['tags'] ?? '');
    $sortOrder = intval($input['sort_order'] ?? 0);
    $isPinned = intval($input['is_pinned'] ?? 0);
    $isActive = intval($input['is_active'] ?? 1);
    $createdBy = $user['email'] ?? $user['name'] ?? 'Admin';

    if (empty($questionBn) || empty($answerBn)) {
        api_response('error', 'Bangla Question and Answer are required fields.', null, 400);
    }

    $stmt = $conn->prepare("INSERT INTO `faq_desktop` (`sccode`, `module_name`, `screen_key`, `page_title`, `screen_description`, `question_bn`, `question_en`, `answer_bn`, `answer_en`, `target_roles`, `tags`, `sort_order`, `is_pinned`, `is_active`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssssssssiiis', $sccode, $moduleName, $screenKey, $pageTitle, $screenDescription, $questionBn, $questionEn, $answerBn, $answerEn, $targetRoles, $tags, $sortOrder, $isPinned, $isActive, $createdBy);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $stmt->close();
        api_response('success', 'FAQ created successfully', ['id' => $newId], 201);
    } else {
        $err = $stmt->error;
        $stmt->close();
        api_response('error', 'Failed to create FAQ: ' . $err, null, 500);
    }
}

// -------------------------------------------------------------
// PUT: Update FAQ
// -------------------------------------------------------------
if ($method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid FAQ ID required for update', null, 400);
    }

    $sccode = intval($input['sccode'] ?? 0);
    $moduleName = trim($input['module_name'] ?? 'general');
    $screenKey = trim($input['screen_key'] ?? 'global');
    $pageTitle = trim($input['page_title'] ?? '');
    $screenDescription = trim($input['screen_description'] ?? '');
    $questionBn = trim($input['question_bn'] ?? '');
    $questionEn = trim($input['question_en'] ?? '');
    $answerBn = trim($input['answer_bn'] ?? '');
    $answerEn = trim($input['answer_en'] ?? '');
    $targetRoles = trim($input['target_roles'] ?? 'all');
    $tags = trim($input['tags'] ?? '');
    $sortOrder = intval($input['sort_order'] ?? 0);
    $isPinned = intval($input['is_pinned'] ?? 0);
    $isActive = intval($input['is_active'] ?? 1);
    $updatedBy = $user['email'] ?? $user['name'] ?? 'Admin';

    if (empty($questionBn) || empty($answerBn)) {
        api_response('error', 'Bangla Question and Answer are required fields.', null, 400);
    }

    $stmt = $conn->prepare("UPDATE `faq_desktop` SET `sccode`=?, `module_name`=?, `screen_key`=?, `page_title`=?, `screen_description`=?, `question_bn`=?, `question_en`=?, `answer_bn`=?, `answer_en`=?, `target_roles`=?, `tags`=?, `sort_order`=?, `is_pinned`=?, `is_active`=?, `updated_by`=? WHERE `id`=?");
    $stmt->bind_param('issssssssssiiisi', $sccode, $moduleName, $screenKey, $pageTitle, $screenDescription, $questionBn, $questionEn, $answerBn, $answerEn, $targetRoles, $tags, $sortOrder, $isPinned, $isActive, $updatedBy, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        api_response('success', 'FAQ updated successfully', ['id' => $id]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        api_response('error', 'Failed to update FAQ: ' . $err, null, 500);
    }
}

// -------------------------------------------------------------
// DELETE: Delete FAQ
// -------------------------------------------------------------
if ($method === 'DELETE') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid FAQ ID required for delete', null, 400);
    }

    $stmt = $conn->prepare("DELETE FROM `faq_desktop` WHERE `id`=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $stmt->close();
        api_response('success', 'FAQ deleted successfully', ['id' => $id]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        api_response('error', 'Failed to delete FAQ: ' . $err, null, 500);
    }
}

api_response('error', 'Method not allowed', null, 405);
