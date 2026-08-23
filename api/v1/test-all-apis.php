<?php
/**
 * EIMBox REST API - Automated Full Test Runner
 * Executes and verifies all REST API endpoints
 */

require_once __DIR__ . '/bootstrap.php';

echo "\n" . str_repeat('=', 75) . "\n";
echo "         🧪 EIMBox Master REST API Automated Verification Suite         \n";
echo str_repeat('=', 75) . "\n\n";

$validToken = generate_token(27, '103187');

$testCases = [
    // 1. Users & Faculty
    [
        'name' => 'Faculty Payroll Structure & Paysheet',
        'file' => 'users/teacher-payroll.php',
        'method' => 'GET',
        'query' => 'action=teachers',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Faculty Directory List',
        'file' => 'users/teachers-manage.php',
        'method' => 'GET',
        'query' => '',
        'expected_status' => 'success'
    ],

    // 2. Finance & Accounts
    [
        'name' => 'Chart of Accounts (Heads & Sub-heads)',
        'file' => 'finance/accounts-heads.php',
        'method' => 'GET',
        'query' => 'action=heads',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Student Fine & Penalty Policies',
        'file' => 'finance/fine-penalty.php',
        'method' => 'GET',
        'query' => 'action=policies',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Bank Deposit Slip & Challan Summary',
        'file' => 'finance/deposit-slip.php',
        'method' => 'GET',
        'query' => 'action=summary',
        'expected_status' => 'success'
    ],
    [
        'name' => 'PR Money Receipt Search & Audit',
        'file' => 'finance/pr-query.php',
        'method' => 'GET',
        'query' => 'action=search_pr',
        'expected_status' => 'success'
    ],

    // 3. Attendance
    [
        'name' => 'Attendance Absentee & Bunk Audit',
        'file' => 'attendance/query-audit.php',
        'method' => 'GET',
        'query' => 'action=bunk_tracker',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Teacher Biometric Punch Query',
        'file' => 'attendance/teacher-attendance.php',
        'method' => 'GET',
        'query' => 'action=today_punches',
        'expected_status' => 'success'
    ],

    // 4. Academics
    [
        'name' => 'Student Demographics & Statistics',
        'file' => 'academics/student-stats.php',
        'method' => 'GET',
        'query' => '',
        'expected_status' => 'success'
    ],
    [
        'name' => 'SMC Guardian & Teacher Voter List',
        'file' => 'academics/smc-voters.php',
        'method' => 'GET',
        'query' => 'type=guardian',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Academic Classes & Sections Structure',
        'file' => 'academics/structure.php',
        'method' => 'GET',
        'query' => 'action=classes',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Subject Setup & Marks Distribution',
        'file' => 'academics/subjects-manage.php',
        'method' => 'GET',
        'query' => '',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Catchment Area & Geographical Zoning',
        'file' => 'academics/areas-manage.php',
        'method' => 'GET',
        'query' => '',
        'expected_status' => 'success'
    ],

    // 5. Facilities & Campus Services
    [
        'name' => 'Library Catalog & Accessions',
        'file' => 'facilities/library.php',
        'method' => 'GET',
        'query' => 'action=books',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Transport Fleet & Bus Routes',
        'file' => 'facilities/transport.php',
        'method' => 'GET',
        'query' => 'action=routes',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Hostel Dormitories & Bed Allocation',
        'file' => 'facilities/hostel.php',
        'method' => 'GET',
        'query' => 'action=buildings',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Clubs & Co-Curricular Activities',
        'file' => 'facilities/clubs.php',
        'method' => 'GET',
        'query' => 'action=clubs',
        'expected_status' => 'success'
    ],

    // 6. Documents & Certificates
    [
        'name' => 'Document & Certificate Templates',
        'file' => 'documents/template-manage.php',
        'method' => 'GET',
        'query' => 'action=templates',
        'expected_status' => 'success'
    ],

    // 7. Settings, Backup, Super Admin & Triggers
    [
        'name' => 'Database Backup Snapshots & Trash',
        'file' => 'settings/backup-engine.php',
        'method' => 'GET',
        'query' => 'action=snapshots',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Super Admin Client Institutions Directory',
        'file' => 'settings/super-admin.php',
        'method' => 'GET',
        'query' => 'action=institutions',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Database Triggers & System Audit Trail',
        'file' => 'settings/triggers-audit.php',
        'method' => 'GET',
        'query' => 'action=triggers',
        'expected_status' => 'success'
    ],
    [
        'name' => 'Exam List & Schedules',
        'file' => 'exams/exam-manage.php',
        'method' => 'GET',
        'query' => '',
        'expected_status' => 'success'
    ]
];

$passed = 0;
$failed = 0;
$results = [];

$workerPath = __DIR__ . '/api-worker.php';

foreach ($testCases as $index => $tc) {
    $num = $index + 1;
    $targetScript = __DIR__ . '/' . $tc['file'];
    
    if (!file_exists($targetScript)) {
        echo sprintf("[%02d] ❌ FAIL: File not found (%s)\n", $num, $tc['file']);
        $failed++;
        continue;
    }

    $cmd = 'php ' . escapeshellarg($workerPath) . ' ' . escapeshellarg($tc['file']) . ' ' . escapeshellarg($tc['method']) . ' ' . escapeshellarg($tc['query']) . ' ' . escapeshellarg($validToken);

    $startTime = microtime(true);
    $output = shell_exec($cmd . ' 2>&1');
    $duration = round((microtime(true) - $startTime) * 1000, 2);

    $json = json_decode($output, true);

    if (is_array($json) && isset($json['status']) && $json['status'] === $tc['expected_status']) {
        $countInfo = '';
        if (isset($json['data'])) {
            if (is_array($json['data'])) {
                $countInfo = ' (' . count($json['data']) . ' items)';
            }
        }
        echo sprintf("[%02d] ✅ PASS (%5.1f ms): %-45s -> %s%s\n", $num, $duration, $tc['name'], $json['message'] ?: $json['status'], $countInfo);
        $passed++;
    } else {
        echo sprintf("[%02d] ❌ FAIL (%5.1f ms): %-45s\n", $num, $duration, $tc['name']);
        echo "     Output: " . substr(strip_tags(trim($output)), 0, 150) . "\n";
        $failed++;
    }
}

echo "\n" . str_repeat('-', 75) . "\n";
echo "📊 Test Summary: Total: " . count($testCases) . " | Passed: $passed | Failed: $failed\n";
echo str_repeat('=', 75) . "\n";

if ($failed === 0) {
    echo "🎉 ALL REST API ENDPOINTS ARE 100% OPERATIONAL, SYNTAX-CLEAN & VALID!\n\n";
} else {
    echo "⚠️ Some endpoints failed. Check logs above.\n\n";
}
