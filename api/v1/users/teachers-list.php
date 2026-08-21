<?php
/**
 * EIMBox REST API — Teachers & Staff Directory
 * Route: GET /api/v1/users/teachers-list.php
 * Query Params: ?sccode={sccode}&status={Active|All}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$status = trim($_GET['status'] ?? 'Active');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

$where = "sccode = ?";
$types = "i";
$params = [$sccode];

if (strtolower($status) !== 'all') {
    $where .= " AND (status = 'Active' OR status = '1' OR status = '' OR status IS NULL)";
}

$sql = "SELECT id, sl, tid, tname, tnameb, position, mobile, email, ranks, subjects, gender, dob, jdate, mpoindex, status 
FROM teacher 
WHERE $where 
ORDER BY sl ASC, ranks ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$teachers = [];
while ($row = $res->fetch_assoc()) {
    $tid = $row['tid'];
    $photoUrl = '';
    if (file_exists(__DIR__ . '/../../teacher/' . $tid . '.jpg')) {
        $photoUrl = 'teacher/' . $tid . '.jpg';
    }

    $teachers[] = [
        'id' => intval($row['id']),
        'sl' => intval($row['sl']),
        'tid' => (string)$row['tid'],
        'name_eng' => $row['tname'],
        'name_ben' => $row['tnameb'] ?? '',
        'designation' => $row['position'],
        'mobile' => $row['mobile'],
        'email' => $row['email'],
        'subject' => $row['subjects'],
        'rank' => intval($row['ranks'] ?? 0),
        'gender' => $row['gender'] ?? '',
        'join_date' => $row['jdate'] ?? '',
        'mpo_index' => $row['mpoindex'] ?? '',
        'photo_url' => $photoUrl,
        'status' => $row['status'] ?: 'Active'
    ];
}
$stmt->close();

api_response('success', 'Teachers list loaded successfully.', [
    'sccode' => $sccode,
    'total_count' => count($teachers),
    'teachers' => $teachers
]);
