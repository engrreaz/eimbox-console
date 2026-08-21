<?php
/**
 * EIMBox REST API — SMS Balance & Recent Dispatches Endpoint
 * Route: GET /api/v1/sms/balance.php
 * Query Params: ?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 1. Fetch SMS balance from scinfo
$scStmt = $conn->prepare("SELECT sms_gateway, valid_module, active_module FROM scinfo WHERE sccode = ? LIMIT 1");
$scStmt->bind_param('i', $sccode);
$scStmt->execute();
$sc = $scStmt->get_result()->fetch_assoc();
$scStmt->close();

// 2. Fetch SMS Usage Statistics from sms table
$statStmt = $conn->prepare("SELECT 
    COUNT(id) AS total_dispatches,
    COALESCE(SUM(count), 0) AS total_credits_spent,
    SUM(CASE WHEN send_time >= CURDATE() THEN count ELSE 0 END) AS today_credits_spent
FROM sms 
WHERE sccode = ?");
$statStmt->bind_param('i', $sccode);
$statStmt->execute();
$stats = $statStmt->get_result()->fetch_assoc();
$statStmt->close();

// 3. Fetch Recent Dispatches
$recStmt = $conn->prepare("SELECT id, mobile_number, sms_text, count, sms_type, campaign, status, send_time 
FROM sms 
WHERE sccode = ? 
ORDER BY id DESC LIMIT 10");
$recStmt->bind_param('i', $sccode);
$recStmt->execute();
$recRes = $recStmt->get_result();

$recent = [];
while ($r = $recRes->fetch_assoc()) {
    $recent[] = [
        'id' => intval($r['id']),
        'mobile' => $r['mobile_number'],
        'message_preview' => mb_substr($r['sms_text'], 0, 50, 'UTF-8') . (mb_strlen($r['sms_text']) > 50 ? '...' : ''),
        'credits' => intval($r['count']),
        'type' => $r['sms_type'],
        'campaign' => $r['campaign'],
        'status' => $r['status'],
        'sent_at' => $r['send_time']
    ];
}
$recStmt->close();

api_response('success', 'SMS balance loaded successfully.', [
    'sccode' => $sccode,
    'total_credits_used' => intval($stats['total_credits_spent'] ?? 0),
    'today_credits_used' => intval($stats['today_credits_spent'] ?? 0),
    'total_messages_sent' => intval($stats['total_dispatches'] ?? 0),
    'recent_dispatches' => $recent
]);
