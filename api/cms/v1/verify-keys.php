<?php
/**
 * EIMBOX Central Cloud CMS API v1 — Key & Credential Verification Endpoint
 * Route: /api/cms/v1/verify-keys
 */

require_once __DIR__ . '/bootstrap.php';

global $conn;

// 1. Authenticate Request
$auth = authenticate_cms_request($conn);

// 2. Query Institution Details
$sccode = $auth['eiin'];
$stmt = $conn->prepare("SELECT id, sccode, school_name, status, last_used_at, created_at FROM `school_api_keys` WHERE sccode = ? LIMIT 1");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$school = $stmt->get_result()->fetch_assoc();

cms_api_response('success', 'এপিআই ক্রেডেনশিয়াল ও সিক্রেট কী সফলভাবে যাচাই করা হয়েছে। সংযোগ সক্রিয় আছে।', [
    'verified'      => true,
    'sccode'        => $sccode,
    'school_name'   => $school['school_name'] ?? 'EIMBOX Registered Institution',
    'status'        => $school['status'] ?? 'active',
    'auth_mode'     => 'api_key_and_secret_pair',
    'server_time'   => date('Y-m-d H:i:s'),
    'capabilities'  => [
        'faculty_sync'      => true,
        'notices_sync'      => true,
        'backup_push'       => true,
        'full_data_pull'    => true,
    ]
], 200);
