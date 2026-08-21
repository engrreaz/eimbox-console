<?php
/**
 * EIMBox REST API — Monthly Attendance Summary & Analytics Endpoint
 * Route: GET /api/v1/attendance/monthly-summary.php
 * Query Params: ?sccode={sccode}&session={session}&month={1-12}&year={YYYY}&class={class}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));
$className = trim($_GET['class'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

$startDate = sprintf("%04d-%02d-01", $year, $month);
$endDate = date("Y-m-t", strtotime($startDate));

// Aggregate attendance from stattnd
$where = "sccode = ? AND adate BETWEEN ? AND ?";
$types = "iss";
$params = [$sccode, $startDate, $endDate];

if (!empty($className) && strtolower($className) !== 'all') {
    $where .= " AND classname = ?";
    $types .= "s";
    $params[] = $className;
}

$sql = "SELECT 
    stid, 
    classname, 
    sectionname, 
    rollno,
    COUNT(id) AS total_marked_days,
    SUM(CASE WHEN yn = 1 THEN 1 ELSE 0 END) AS present_days,
    SUM(CASE WHEN yn = 0 THEN 1 ELSE 0 END) AS absent_days
FROM stattnd
WHERE $where
GROUP BY stid, classname, sectionname, rollno
ORDER BY classname ASC, sectionname ASC, rollno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$summary = [];
$totalClassPresent = 0;
$totalClassAbsent = 0;

while ($row = $res->fetch_assoc()) {
    $present = intval($row['present_days']);
    $absent = intval($row['absent_days']);
    $totalDays = intval($row['total_marked_days']);
    $rate = $totalDays > 0 ? round(($present / $totalDays) * 100, 2) : 0;

    $totalClassPresent += $present;
    $totalClassAbsent += $absent;

    $summary[] = [
        'stid' => (string)$row['stid'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'rollno' => intval($row['rollno']),
        'total_working_days' => $totalDays,
        'present_days' => $present,
        'absent_days' => $absent,
        'attendance_percentage' => $rate
    ];
}
$stmt->close();

$grandTotal = $totalClassPresent + $totalClassAbsent;
$overallRate = $grandTotal > 0 ? round(($totalClassPresent / $grandTotal) * 100, 2) : 0;

api_response('success', 'Monthly attendance summary loaded.', [
    'sccode' => $sccode,
    'month' => $month,
    'year' => $year,
    'class' => $className ?: 'All',
    'overall_attendance_rate' => $overallRate,
    'total_students_tracked' => count($summary),
    'student_summaries' => $summary
]);
