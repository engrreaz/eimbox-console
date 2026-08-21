<?php
/**
 * EIMBox REST API — Send Single & Bulk SMS Endpoint
 * Route: POST /api/v1/sms/send-sms.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$message = trim($input['message'] ?? $input['text'] ?? '');
$recipients = $input['recipients'] ?? []; // array of mobile numbers or [{ mobile, name }]
$smsType = trim($input['sms_type'] ?? 'General');
$campaign = trim($input['campaign'] ?? 'Studio Broadcast');
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio SMS Gateway';

if ($sccode <= 0 || empty($message)) {
    api_response('error', 'Valid sccode and non-empty message text are required.', null, 400);
}

if (!is_array($recipients) || empty($recipients)) {
    api_response('error', 'Recipients array cannot be empty.', null, 400);
}

// 1. Calculate SMS Count per Message (Unicode / Bangla aware)
$isUnicode = preg_match('/[^\x00-\x7F]/', $message);
$msgLen = mb_strlen($message, 'UTF-8');
$smsPerPerson = 1;

if ($isUnicode) {
    if ($msgLen > 70) {
        $smsPerPerson = ceil($msgLen / 67);
    }
} else {
    if ($msgLen > 160) {
        $smsPerPerson = ceil($msgLen / 153);
    }
}

$sentCount = 0;
$failedCount = 0;
$sessionyear = date('Y');
$todayDate = date('Y-m-d');

$insStmt = $conn->prepare("INSERT INTO sms (
    sccode, sessionyear, date, mobile_number, sms_text, sms_len, count, sms_type, campaign, status, send_by, send_time
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Sent', ?, NOW())");

foreach ($recipients as $rec) {
    $mobile = is_array($rec) ? trim($rec['mobile'] ?? $rec['phone'] ?? '') : trim($rec);
    // Sanitize 11 digit BD number
    $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($cleanMobile) === 13 && str_starts_with($cleanMobile, '880')) {
        $cleanMobile = substr($cleanMobile, 2);
    }

    if (strlen($cleanMobile) !== 11 || !str_starts_with($cleanMobile, '01')) {
        $failedCount++;
        continue;
    }

    $insStmt->bind_param('issssiisss', 
        $sccode, $sessionyear, $todayDate, $cleanMobile, $message, $msgLen, $smsPerPerson, $smsType, $campaign, $entryby
    );
    if ($insStmt->execute()) {
        $sentCount++;
    } else {
        $failedCount++;
    }
}
$insStmt->close();

$totalCreditsUsed = $sentCount * $smsPerPerson;

api_response('success', 'SMS broadcast dispatched.', [
    'sccode' => $sccode,
    'total_recipients' => count($recipients),
    'successful_sent' => $sentCount,
    'failed_numbers' => $failedCount,
    'credits_per_message' => $smsPerPerson,
    'total_credits_deducted' => $totalCreditsUsed,
    'is_unicode' => (bool)$isUnicode
]);
