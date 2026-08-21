<?php
/**
 * EIMBox REST API — Tabulation Sheet & Result Analytics Endpoint
 * Route: GET /api/v1/exams/tabulation-data.php
 * Query Params: ?sccode={sccode}&session={session}&exam={exam}&class={class}&section={section}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$exam = trim($_GET['exam'] ?? '');
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');

if ($sccode <= 0 || empty($exam) || empty($className)) {
    api_response('error', 'sccode, exam, and class are required.', null, 400);
}

// 1. Fetch Tabulation Sheet Data
$where = "sccode = ? AND sessionyear LIKE ? AND exam = ? AND classname = ?";
$types = "isss";
$params = [$sccode, "%$session%", $exam, $className];

if (!empty($sectionName) && strtolower($sectionName) !== 'all') {
    $where .= " AND sectionname = ?";
    $types .= "s";
    $params[] = $sectionName;
}

$sql = "SELECT id, sccode, sessionyear, exam, classname, sectionname, stid, rollno, totalmarks, gpa, gla, avgrate, totalfail, meritnum 
FROM tabulatingsheet 
WHERE $where 
ORDER BY CAST(meritnum AS UNSIGNED) ASC, totalmarks DESC, rollno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$results = [];
$totalStudents = 0;
$passedCount = 0;
$failedCount = 0;
$gpaCounts = ['A+' => 0, 'A' => 0, 'A-' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];

while ($row = $res->fetch_assoc()) {
    $totalStudents++;
    $gla = $row['gla'] ?: 'F';
    $failed = intval($row['totalfail'] ?? 0);

    if ($failed > 0 || $gla === 'F') {
        $failedCount++;
        $gpaCounts['F']++;
    } else {
        $passedCount++;
        if (isset($gpaCounts[$gla])) $gpaCounts[$gla]++;
    }

    $results[] = [
        'id' => intval($row['id']),
        'stid' => (string)$row['stid'],
        'rollno' => intval($row['rollno']),
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'total_marks' => floatval($row['totalmarks']),
        'gpa' => floatval($row['gpa']),
        'grade_letter' => $gla,
        'average_rate' => floatval($row['avgrate']),
        'failed_subjects' => $failed,
        'merit_position' => intval($row['meritnum'])
    ];
}
$stmt->close();

$passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 2) : 0;

api_response('success', 'Tabulation data loaded successfully.', [
    'sccode' => $sccode,
    'session' => $session,
    'exam' => $exam,
    'class' => $className,
    'section' => $sectionName ?: 'All',
    'analytics' => [
        'total_students' => $totalStudents,
        'passed' => $passedCount,
        'failed' => $failedCount,
        'pass_rate' => $passRate,
        'grade_distribution' => $gpaCounts
    ],
    'tabulation_sheet' => $results
]);
