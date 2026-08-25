<?php
/**
 * EIMBox REST API — Users & Staff Directory List
 * Route: GET /api/v1/users/list.php
 * Query Params: ?sccode={sccode}&role={all|Teacher|Admin}&status={all|active|inactive|locked}&search={query}&sortBy={id}&sortOrder={ASC|DESC}
 */

require_once __DIR__ . '/../bootstrap.php';

// Extract input parameters
$sccode = isset($_GET['sccode']) ? intval($_GET['sccode']) : (isset($_POST['sccode']) ? intval($_POST['sccode']) : 0);
$role = isset($_GET['role']) ? trim($_GET['role']) : (isset($_GET['userlevel']) ? trim($_GET['userlevel']) : 'all');
$status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sortBy']) ? trim($_GET['sortBy']) : 'id';
$sortOrder = (isset($_GET['sortOrder']) && strtoupper($_GET['sortOrder']) === 'DESC') ? 'DESC' : 'ASC';

// Validate allowed sort fields
$allowedSort = [
    'id' => 'id',
    'profilename' => 'profilename',
    'userlevel' => 'userlevel',
    'email' => 'email',
    'lastlogin' => 'lastlogin',
    'modifieddate' => 'modifieddate'
];
$sortField = $allowedSort[$sortBy] ?? 'id';

$whereClauses = [];
$params = [];
$types = '';

if ($sccode > 0) {
    $whereClauses[] = "sccode = ?";
    $params[] = $sccode;
    $types .= 'i';
}

if ($role !== 'all' && $role !== '') {
    $whereClauses[] = "LOWER(userlevel) = LOWER(?)";
    $params[] = $role;
    $types .= 's';
}

if ($status === 'active') {
    $whereClauses[] = "(status = 1 AND (failed_attempts IS NULL OR failed_attempts < 5))";
} elseif ($status === 'inactive') {
    $whereClauses[] = "status = 0";
} elseif ($status === 'locked') {
    $whereClauses[] = "(failed_attempts >= 5 OR lock_until > NOW())";
}

if ($search !== '') {
    $searchWildcard = "%{$search}%";
    $whereClauses[] = "(profilename LIKE ? OR email LIKE ? OR mobile LIKE ? OR userid LIKE ? OR username LIKE ?)";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= 'sssss';
}

$whereSql = count($whereClauses) > 0 ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';
$query = "SELECT id, email, username, fixedpin, sccode, profilename, mobile, userlevel, admin, is_chief,
                 hiddenuser, status, active, two_factor, mfa_enabled, mfa_type, failed_attempts,
                 last_failed, lock_until, login_gmail, login_pass, login_token, login_qrcode,
                 photourl, userid, firstlogin, lastlogin, lastaccess, session, curexam, theme,
                 customcss, tour_enable, created_at, modifieddate
          FROM usersapp
          {$whereSql}
          ORDER BY {$sortField} {$sortOrder}";

$stmt = $conn->prepare($query);
if ($stmt && count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} else {
    $res = $conn->query($query);
    $users = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// Calculate Summary Statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN failed_attempts >= 5 OR lock_until > NOW() THEN 1 ELSE 0 END) as locked,
    SUM(CASE WHEN admin >= 3 OR LOWER(userlevel) IN ('super administrator', 'administrator') THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN LOWER(userlevel) IN ('head teacher', 'principal', 'headmaster') OR is_chief = 1 THEN 1 ELSE 0 END) as head_teachers,
    SUM(CASE WHEN LOWER(userlevel) IN ('teacher', 'assistant teacher', 'senior teacher', 'class teacher') THEN 1 ELSE 0 END) as teachers,
    SUM(CASE WHEN LOWER(userlevel) IN ('accountant', 'office assistant', 'librarian', 'staff', 'clerk') THEN 1 ELSE 0 END) as staff,
    SUM(CASE WHEN LOWER(userlevel) = 'student' THEN 1 ELSE 0 END) as students,
    SUM(CASE WHEN LOWER(userlevel) IN ('guardian', 'parent') THEN 1 ELSE 0 END) as guardians,
    SUM(CASE WHEN mfa_enabled = 1 OR two_factor = 1 THEN 1 ELSE 0 END) as mfa_enabled
FROM usersapp " . ($sccode > 0 ? "WHERE sccode = {$sccode}" : "");

$statsRes = $conn->query($statsQuery);
$stats = [
    'total' => 0, 'active' => 0, 'inactive' => 0, 'locked' => 0,
    'admins' => 0, 'head_teachers' => 0, 'teachers' => 0, 'staff' => 0,
    'students' => 0, 'guardians' => 0, 'mfa_enabled' => 0
];

if ($statsRes && $row = $statsRes->fetch_assoc()) {
    $stats = [
        'total' => intval($row['total'] ?? 0),
        'active' => intval($row['active'] ?? 0),
        'inactive' => intval($row['inactive'] ?? 0),
        'locked' => intval($row['locked'] ?? 0),
        'admins' => intval($row['admins'] ?? 0),
        'head_teachers' => intval($row['head_teachers'] ?? 0),
        'teachers' => intval($row['teachers'] ?? 0),
        'staff' => intval($row['staff'] ?? 0),
        'students' => intval($row['students'] ?? 0),
        'guardians' => intval($row['guardians'] ?? 0),
        'mfa_enabled' => intval($row['mfa_enabled'] ?? 0)
    ];
}

api_response('success', 'User directory fetched successfully.', [
    'sccode' => $sccode,
    'count' => count($users),
    'users' => $users,
    'stats' => $stats
]);
