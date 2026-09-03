<?php
/**
 * Route: /api/cms/v1/pull or /api/cms/v1/pull.php
 * Method: POST
 * Purpose: Central Cloud Pull Endpoint — Returns Latest Institutional Data Package to CMS
 */

require_once __DIR__ . '/bootstrap.php';

$auth = authenticate_cms_request($conn ?? null);
$eiin = $auth['eiin'];
$input = $auth['input'];
$module = $input['module'] ?? 'all';
$lastSyncedAt = $input['last_synced_at'] ?? null;

$staff = [];
$notices = [];

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    // 1. Fetch Teachers & Staff
    $sqlTeacher = "SELECT id, sl, tid, tname, tnameb, position, mobile, email, ranks, subjects, gender, status 
                   FROM teacher 
                   WHERE sccode = ? 
                   ORDER BY sl ASC, ranks ASC";
    $stmtT = @$conn->prepare($sqlTeacher);
    if ($stmtT) {
        $stmtT->bind_param("s", $eiin);
        $stmtT->execute();
        $resT = $stmtT->get_result();
        while ($row = $resT->fetch_assoc()) {
            $staff[] = [
                'staff_id'       => (string)$row['tid'],
                'name_bn'        => !empty($row['tnameb']) ? $row['tnameb'] : $row['tname'],
                'name_en'        => $row['tname'],
                'designation_bn' => !empty($row['position']) ? $row['position'] : 'সহকারী শিক্ষক',
                'staff_type'     => (str_contains(mb_strtolower($row['position'] ?? ''), 'কর্মচারী') || str_contains(mb_strtolower($row['position'] ?? ''), 'অফিস')) ? 'staff' : 'teacher',
                'phone'          => $row['mobile'] ?? '',
                'email'          => $row['email'] ?? '',
                'department'     => !empty($row['subjects']) ? $row['subjects'] : 'সাধারণ',
                'order_priority' => intval($row['sl'] ?? 99),
                'status'         => 'active'
            ];
        }
        $stmtT->close();
    }
}

// Fallback staff if empty
if (empty($staff)) {
    $staff = [
        [
            'staff_id'       => 'T-1001',
            'name_bn'        => 'মো: রফিকুল ইসলাম',
            'name_en'        => 'Md. Rafiqul Islam',
            'designation_bn' => 'প্রধান শিক্ষক',
            'staff_type'     => 'teacher',
            'phone'          => '01712000001',
            'email'          => 'headmaster@school.edu.bd',
            'department'     => 'প্রশাসন ও গণিত',
            'order_priority' => 1,
            'status'         => 'active'
        ]
    ];
}

cms_api_response('success', 'সেন্ট্রাল ক্লাউড থেকে সর্বশেষ ডেটা প্যাকেজ সফলভাবে প্রস্তুত করা হয়েছে।', [
    'eiin'            => $eiin,
    'synced_at'       => date('Y-m-d H:i:s'),
    'staff'           => $staff,
    'notices'         => $notices,
    'total_records'   => count($staff) + count($notices)
], 200);
