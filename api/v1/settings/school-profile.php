<?php
/**
 * EIMBox REST API — Institute Profile & Configuration Endpoint
 * Route: GET /api/v1/settings/school-profile.php
 * Query Params: ?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller if token provided (allows graceful desktop client sync when sccode is provided)
$user = null;
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!empty($authHeader)) {
    try {
        $user = authenticate_token($conn);
    } catch (Exception $e) {
        // Continue if sccode provided
    }
}

$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code from Query, Body, or Token
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? ($user['sccode'] ?? 0));

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle POST / PUT: Update Institute Profile
if ($method === 'POST' || $method === 'PUT') {
    // Identity & Board info
    $scname = isset($input['name']) || isset($input['scname']) ? trim($input['name'] ?? $input['scname'] ?? '') : null;
    $sccategory = isset($input['category']) || isset($input['sccategory']) ? trim($input['category'] ?? $input['sccategory'] ?? '') : null;
    $short = isset($input['short']) ? trim($input['short']) : null;
    $ed_board = isset($input['ed_board']) ? trim($input['ed_board']) : null;
    $center_code = isset($input['center_code']) ? intval($input['center_code']) : null;
    $center_name = isset($input['center_name']) ? trim($input['center_name']) : null;
    $college_code = isset($input['college_code']) ? intval($input['college_code']) : null;

    // Address & Contact
    $scadd1 = isset($input['scadd1']) || isset($input['address_line1']) ? trim($input['scadd1'] ?? $input['address_line1'] ?? '') : null;
    $scadd2 = isset($input['scadd2']) || isset($input['address_line2']) ? trim($input['scadd2'] ?? $input['address_line2'] ?? '') : null;
    $ps = isset($input['ps']) || isset($input['upazila']) || isset($input['thana']) ? trim($input['ps'] ?? $input['upazila'] ?? $input['thana'] ?? '') : null;
    $dist = isset($input['dist']) || isset($input['district']) ? trim($input['dist'] ?? $input['district'] ?? '') : null;
    $postal_code = isset($input['postal_code']) || isset($input['zip']) ? intval($input['postal_code'] ?? $input['zip'] ?? 0) : null;
    $zone = isset($input['zone']) ? trim($input['zone']) : null;
    $mobile = isset($input['mobile']) || isset($input['phone']) ? trim($input['mobile'] ?? $input['phone'] ?? '') : null;
    $scmail = isset($input['scmail']) || isset($input['email']) ? trim($input['scmail'] ?? $input['email'] ?? '') : null;
    $scmail2 = isset($input['scmail2']) ? trim($input['scmail2']) : null;
    $scweb = isset($input['scweb']) || isset($input['website']) ? trim($input['scweb'] ?? $input['website'] ?? '') : null;

    // Leadership
    $headname = isset($input['head_name']) || isset($input['headname']) ? trim($input['head_name'] ?? $input['headname'] ?? '') : null;
    $headtitle = isset($input['head_title']) || isset($input['headtitle']) ? trim($input['head_title'] ?? $input['headtitle'] ?? '') : null;

    // Geo-Fence & Timings
    $geolat = isset($input['geolat']) || isset($input['lat']) ? trim($input['geolat'] ?? $input['lat'] ?? '') : null;
    $geolon = isset($input['geolon']) || isset($input['lon']) ? trim($input['geolon'] ?? $input['lon'] ?? '') : null;
    $dista_differ = isset($input['dista_differ']) ? intval($input['dista_differ']) : null;
    $time_differ = isset($input['time_differ']) ? intval($input['time_differ']) : null;
    $intime = isset($input['intime']) ? trim($input['intime']) : null;
    $outtime = isset($input['outtime']) ? trim($input['outtime']) : null;

    // Subscriptions & Service Flags
    $theme = isset($input['theme']) ? trim($input['theme']) : null;
    $progressguar = isset($input['progressguar']) ? intval($input['progressguar']) : null;
    $serviceattnd = isset($input['serviceattnd']) ? intval($input['serviceattnd']) : null;
    $servicefinance = isset($input['servicefinance']) ? intval($input['servicefinance']) : null;
    $servicestudent = isset($input['servicestudent']) ? intval($input['servicestudent']) : null;
    $app = isset($input['app']) ? intval($input['app']) : null;

    // Security & Backup
    $profile_track = isset($input['profile_track']) ? intval($input['profile_track']) : null;
    $self_control = isset($input['self_control']) ? intval($input['self_control']) : null;
    $daily_backup = isset($input['daily_backup']) ? intval($input['daily_backup']) : null;
    $monthly_backup = isset($input['monthly_backup']) ? intval($input['monthly_backup']) : null;
    $cloud_storage = isset($input['cloud_storage']) ? intval($input['cloud_storage']) : null;
    $backup_mail_2 = isset($input['backup_mail_2']) ? trim($input['backup_mail_2']) : null;
    $backup_mail_3 = isset($input['backup_mail_3']) ? trim($input['backup_mail_3']) : null;

    // Subscription packages
    $package_name = isset($input['package_name']) ? trim($input['package_name']) : null;
    $package_id = isset($input['package_id']) ? intval($input['package_id']) : null;
    $tier = isset($input['tier']) ? trim($input['tier']) : null;
    $pack = isset($input['pack']) ? intval($input['pack']) : null;
    $packdate = isset($input['packdate']) ? trim($input['packdate']) : null;
    $expire = isset($input['expire']) ? trim($input['expire']) : null;
    $billing_data = isset($input['billing_data']) ? trim($input['billing_data']) : null;
    $valid_module = isset($input['valid_module']) ? trim($input['valid_module']) : null;
    $active_module = isset($input['active_module']) ? trim($input['active_module']) : null;
    $valid_panel = isset($input['valid_panel']) ? trim($input['valid_panel']) : null;
    $active_panel = isset($input['active_panel']) ? trim($input['active_panel']) : null;

    // Account Status & Display
    $status = isset($input['status']) ? intval($input['status']) : null;
    $rootuser = isset($input['rootuser']) || isset($input['root_user']) ? trim($input['rootuser'] ?? $input['root_user'] ?? '') : null;
    $logo = isset($input['logo']) ? trim($input['logo']) : null;

    // Process base64 logo upload if provided
    if (!empty($input['logo_base64'])) {
        $rawBase64 = $input['logo_base64'];
        if (strpos($rawBase64, 'base64,') !== false) {
            $rawBase64 = substr($rawBase64, strpos($rawBase64, 'base64,') + 7);
        }
        $imgData = base64_decode($rawBase64);
        if ($imgData !== false && strlen($imgData) > 0) {
            $logoFileName = $sccode . '.png';
            $destDirs = [
                __DIR__ . '/../../../../eimbox-root/assets/images/logos',
                __DIR__ . '/../../../../eimbox-root/logo',
                __DIR__ . '/../../uploads/logos'
            ];
            foreach ($destDirs as $d) {
                if (!is_dir($d)) {
                    @mkdir($d, 0777, true);
                }
                @file_put_contents($d . '/' . $logoFileName, $imgData);
            }
            $logo = $logoFileName;
        }
    }

    // Sensitive Payment & SMS Gateways (If provided by authorized admin)
    $bkash = isset($input['bkash']) ? trim($input['bkash']) : null;
    $nagad = isset($input['nagad']) ? trim($input['nagad']) : null;
    $rocket = isset($input['rocket']) ? trim($input['rocket']) : null;
    $bank = isset($input['bank']) ? trim($input['bank']) : null;
    $sms_gateway = isset($input['sms_gateway']) ? trim($input['sms_gateway']) : null;
    $sms_cost = isset($input['sms_cost']) ? floatval($input['sms_cost']) : null;
    $sms_balance = isset($input['sms_balance']) ? floatval($input['sms_balance']) : null;
    $account_balance = isset($input['account_balance']) ? floatval($input['account_balance']) : null;
    $api_key = isset($input['api_key']) ? trim($input['api_key']) : null;
    $secret_key = isset($input['secret_key']) ? trim($input['secret_key']) : null;

    $setClauses = [];
    $params = [];
    $types = '';

    $addField = function($col, $val, $type = 's') use (&$setClauses, &$params, &$types) {
        if ($val !== null) {
            $setClauses[] = "`$col` = ?";
            $params[] = $val;
            $types .= $type;
        }
    };

    $addField('scname', $scname);
    $addField('sccategory', $sccategory);
    $addField('short', $short);
    $addField('ed_board', $ed_board);
    $addField('center_code', $center_code, 'i');
    $addField('center_name', $center_name);
    $addField('college_code', $college_code, 'i');
    $addField('scadd1', $scadd1);
    $addField('scadd2', $scadd2);
    $addField('ps', $ps);
    $addField('dist', $dist);
    $addField('postal_code', $postal_code, 'i');
    $addField('zone', $zone);
    $addField('mobile', $mobile);
    $addField('scmail', $scmail);
    $addField('scmail2', $scmail2);
    $addField('scweb', $scweb);
    $addField('headname', $headname);
    $addField('headtitle', $headtitle);
    $addField('geolat', $geolat);
    $addField('geolon', $geolon);
    $addField('dista_differ', $dista_differ, 'i');
    $addField('time_differ', $time_differ, 'i');
    $addField('intime', $intime);
    $addField('outtime', $outtime);
    $addField('progressguar', $progressguar, 'i');
    $addField('rootuser', $rootuser);
    $addField('logo', $logo);

    // Strict Role-Based Access Control: Last 3 Sections (Subscription, Security, Gateways) require is_admin >= 3
    $isAdminLevel = intval($user['is_admin'] ?? $user['admin'] ?? $user['admin_level'] ?? 0);
    $userLevel = trim($user['userlevel'] ?? $user['role'] ?? '');
    $canEditAdminSections = ($isAdminLevel >= 3) || in_array($userLevel, ['Super Administrator', 'Developer', 'Super Admin']);

    if ($canEditAdminSections) {
        $addField('theme', $theme);
        $addField('serviceattnd', $serviceattnd, 'i');
        $addField('servicefinance', $servicefinance, 'i');
        $addField('servicestudent', $servicestudent, 'i');
        $addField('app', $app, 'i');
        $addField('package_name', $package_name);
        $addField('package_id', $package_id, 'i');
        $addField('tier', $tier);
        $addField('pack', $pack, 'i');
        $addField('packdate', $packdate);
        $addField('expire', $expire);
        $addField('billing_data', $billing_data);
        $addField('valid_module', $valid_module);
        $addField('active_module', $active_module);
        $addField('valid_panel', $valid_panel);
        $addField('active_panel', $active_panel);
        $addField('profile_track', $profile_track, 'i');
        $addField('self_control', $self_control, 'i');
        $addField('daily_backup', $daily_backup, 'i');
        $addField('monthly_backup', $monthly_backup, 'i');
        $addField('cloud_storage', $cloud_storage, 'i');
        $addField('backup_mail_2', $backup_mail_2);
        $addField('backup_mail_3', $backup_mail_3);
        $addField('status', $status, 'i');
        $addField('display', $display, 'i');
        $addField('bkash', $bkash);
        $addField('nagad', $nagad);
        $addField('rocket', $rocket);
        $addField('bank', $bank);
        $addField('sms_gateway', $sms_gateway);
        $addField('sms_cost', $sms_cost, 'd');
        $addField('sms_balance', $sms_balance, 'd');
        $addField('account_balance', $account_balance, 'd');
        $addField('api_key', $api_key);
        $addField('secret_key', $secret_key);
    }

    if (!empty($setClauses)) {
        $setClauses[] = "`modifieddate` = NOW()";
        $updateSql = "UPDATE `scinfo` SET " . implode(', ', $setClauses) . " WHERE `sccode` = ?";
        $params[] = $sccode;
        $types .= 'i';

        $upStmt = $conn->prepare($updateSql);
        if ($upStmt) {
            $upStmt->bind_param($types, ...$params);
            if (!$upStmt->execute()) {
                api_response('error', 'Failed to update institute profile: ' . $conn->error, null, 500);
            }
            $upStmt->close();
        }
    }
}

// 3. Fetch latest institute profile from scinfo
$stmt = $conn->prepare("SELECT * FROM `scinfo` WHERE `sccode` = ? LIMIT 1");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$sc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sc) {
    api_response('error', 'Institute profile not found for sccode: ' . $sccode, null, 404);
}

// 4. Fetch active student & teacher counts
$stCount = 0;
$stCountStmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM `sessioninfo` WHERE `sccode` = ? AND `sessionyear` LIKE ?");
if ($stCountStmt) {
    $curYear = '%' . date('Y') . '%';
    $stCountStmt->bind_param('is', $sccode, $curYear);
    $stCountStmt->execute();
    $stCount = intval($stCountStmt->get_result()->fetch_assoc()['total_students'] ?? 0);
    $stCountStmt->close();
}

if ($stCount === 0) {
    $stCountStmt2 = $conn->prepare("SELECT COUNT(*) AS total_students FROM `sessioninfo` WHERE `sccode` = ?");
    if ($stCountStmt2) {
        $stCountStmt2->bind_param('i', $sccode);
        $stCountStmt2->execute();
        $stCount = intval($stCountStmt2->get_result()->fetch_assoc()['total_students'] ?? 0);
        $stCountStmt2->close();
    }
}

if ($stCount === 0) {
    $stCountStmt3 = $conn->prepare("SELECT COUNT(*) AS total_students FROM `students` WHERE `sccode` = ?");
    if ($stCountStmt3) {
        $stCountStmt3->bind_param('i', $sccode);
        $stCountStmt3->execute();
        $stCount = intval($stCountStmt3->get_result()->fetch_assoc()['total_students'] ?? 0);
        $stCountStmt3->close();
    }
}

$teaCount = 0;
$teaCountStmt = $conn->prepare("SELECT COUNT(*) AS total_teachers FROM `teacher` WHERE `sccode` = ?");
if ($teaCountStmt) {
    $teaCountStmt->bind_param('i', $sccode);
    $teaCountStmt->execute();
    $teaCount = intval($teaCountStmt->get_result()->fetch_assoc()['total_teachers'] ?? 0);
    $teaCountStmt->close();
}

if ($teaCount === 0) {
    $teaCountStmt2 = $conn->prepare("SELECT COUNT(*) AS total_teachers FROM `users` WHERE `sccode` = ? AND (`userlevel` LIKE '%Teacher%' OR `userlevel` LIKE '%Head%' OR `role` LIKE '%Teacher%')");
    if ($teaCountStmt2) {
        $teaCountStmt2->bind_param('i', $sccode);
        $teaCountStmt2->execute();
        $teaCount = intval($teaCountStmt2->get_result()->fetch_assoc()['total_teachers'] ?? 0);
        $teaCountStmt2->close();
    }
}

$actionMsg = ($method === 'POST' || $method === 'PUT') 
    ? 'Institute profile updated successfully.' 
    : 'Institute profile retrieved successfully.';

$logoUrl = "https://eimbox.com/logo/" . $sc['sccode'] . ".png";

api_response('success', $actionMsg, [
    'institute' => [
        'id' => intval($sc['id']),
        'sccode' => intval($sc['sccode']),
        'name' => $sc['scname'] ?? '',
        'scname' => $sc['scname'] ?? '',
        'category' => $sc['sccategory'] ?? 'School',
        'sccategory' => $sc['sccategory'] ?? 'School',
        'short' => $sc['short'] ?? '',
        'ed_board' => $sc['ed_board'] ?? '',
        'center_code' => $sc['center_code'] ? intval($sc['center_code']) : null,
        'center_name' => $sc['center_name'] ?? '',
        'college_code' => $sc['college_code'] ? intval($sc['college_code']) : null,
        
        // Address & Location
        'address' => trim(($sc['scadd1'] ?? '') . ', ' . ($sc['ps'] ?? '') . ', ' . ($sc['dist'] ?? '')),
        'scadd1' => $sc['scadd1'] ?? '',
        'scadd2' => $sc['scadd2'] ?? '',
        'ps' => $sc['ps'] ?? '',
        'dist' => $sc['dist'] ?? '',
        'postal_code' => intval($sc['postal_code'] ?? 0),
        'zone' => $sc['zone'] ?? '',

        // Contact
        'mobile' => $sc['mobile'] ?? '',
        'scmail' => $sc['scmail'] ?? '',
        'scmail2' => $sc['scmail2'] ?? '',
        'scweb' => $sc['scweb'] ?? '',

        // Chief / Leadership
        'root_user' => $sc['rootuser'] ?? '',
        'rootuser' => $sc['rootuser'] ?? '',
        'head_name' => $sc['headname'] ?? '',
        'headname' => $sc['headname'] ?? '',
        'head_title' => $sc['headtitle'] ?? 'Head Teacher',
        'headtitle' => $sc['headtitle'] ?? 'Head Teacher',
        'created_at' => $sc['created_at'] ?? '',
        'modifieddate' => $sc['modifieddate'] ?? '',
        'logo' => $sc['logo'] ?? '-',
        'logo_url' => $logoUrl,

        // Geo-Fence & Timings
        'geolat' => $sc['geolat'] ?? '23.72769',
        'geolon' => $sc['geolon'] ?? '90.41047',
        'dista_differ' => intval($sc['dista_differ'] ?? 50),
        'time_differ' => intval($sc['time_differ'] ?? 600),
        'intime' => $sc['intime'] ?? '09:45:00',
        'outtime' => $sc['outtime'] ?? '16:55:00',

        // Subscription & Packages
        'package_id' => intval($sc['package_id'] ?? 1),
        'package_name' => $sc['package_name'] ?? 'Standard',
        'tier' => $sc['tier'] ?? 'A',
        'pack' => intval($sc['pack'] ?? 0),
        'packdate' => $sc['packdate'] ?? '',
        'expire' => $sc['expire'] ?? '',
        'valid_module' => $sc['valid_module'] ?? '',
        'active_module' => $sc['active_module'] ?? '',
        'valid_panel' => $sc['valid_panel'] ?? '',
        'active_panel' => $sc['active_panel'] ?? '',
        'billing_data' => $sc['billing_data'] ?? '',
        'theme' => $sc['theme'] ?? 'Light',
        'progressguar' => intval($sc['progressguar'] ?? 1),
        'serviceattnd' => intval($sc['serviceattnd'] ?? 0),
        'servicefinance' => intval($sc['servicefinance'] ?? 0),
        'servicestudent' => intval($sc['servicestudent'] ?? 0),
        'app' => intval($sc['app'] ?? 0),

        // Security & Backup
        'profile_track' => intval($sc['profile_track'] ?? 0),
        'self_control' => intval($sc['self_control'] ?? 0),
        'backup' => intval($sc['backup'] ?? 0),
        'daily_backup' => intval($sc['daily_backup'] ?? 0),
        'monthly_backup' => intval($sc['monthly_backup'] ?? 0),
        'cloud_storage' => intval($sc['cloud_storage'] ?? 0),
        'last_backup_time' => $sc['last_backup_time'] ?? '',
        'backup_mail_2' => $sc['backup_mail_2'] ?? '',
        'backup_mail_3' => $sc['backup_mail_3'] ?? '',
        'active' => intval($sc['active'] ?? 0),
        'status' => intval($sc['status'] ?? 0),
        'display' => intval($sc['display'] ?? 1),
        'last_login_time' => $sc['last_login_time'] ?? '',

        // Sensitive Payment Gateways & SMS
        'bkash' => $sc['bkash'] ?? '',
        'bkash_token_expire' => $sc['bkash_token_expire'] ?? '',
        'rocket' => $sc['rocket'] ?? '',
        'nagad' => $sc['nagad'] ?? '',
        'bank' => $sc['bank'] ?? '',
        'sms_gateway' => $sc['sms_gateway'] ?? '',
        'sms_send' => intval($sc['sms_send'] ?? 0),
        'sms_success' => intval($sc['sms_success'] ?? 0),
        'sms_error' => intval($sc['sms_error'] ?? 0),
        'sms_cost' => floatval($sc['sms_cost'] ?? 0),
        'sms_balance' => floatval($sc['sms_balance'] ?? 0),
        'account_balance' => floatval($sc['account_balance'] ?? 0),
        'api_key' => $sc['api_key'] ?? '',
        'secret_key' => $sc['secret_key'] ?? '',
        'algorithm' => $sc['algorithm'] ?? '',
        'reg_hash' => $sc['reg_hash'] ?? '',

        'stats' => [
            'active_students' => intval($stCount),
            'teachers_count' => intval($teaCount)
        ]
    ],
    'raw' => $sc
]);

