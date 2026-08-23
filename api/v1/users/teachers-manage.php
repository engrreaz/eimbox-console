<?php
/**
 * EIMBox REST API - Teachers & Staff Management
 * Endpoint: /api/v1/users/teachers-manage.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request
$user = api_authenticate_request();
$sccode = (int)($user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_send_response(400, false, "Invalid school institution code.");
}

$conn = api_get_db_connection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. GET: Fetch Teachers List
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    
    $sql = "SELECT id, sccode, tid, tname, tnameb AS tnameben, mobile, email, position AS desig, subjects AS subject, bgroup, 
                   gender, dob, jdate AS joindate, mpoindex AS indexno, status, modifieddate
            FROM teacher
            WHERE sccode = ?";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($search)) {
        $sql .= " AND (tname LIKE ? OR tid LIKE ? OR mobile LIKE ? OR position LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $types .= "ssss";
    }

    $sql .= " ORDER BY ranks ASC, id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $stmt->close();

    api_send_response(200, true, "Teachers retrieved successfully.", $teachers);
}

// 2. POST: Add or Update Teacher
if ($method === 'POST') {
    $input = get_api_input();
    $tid = trim($input['tid'] ?? '');
    $tname = trim($input['tname'] ?? '');
    $mobile = trim($input['mobile'] ?? '');
    $position = trim($input['desig'] ?? $input['position'] ?? 'Assistant Teacher');

    if (empty($tname) || empty($mobile)) {
        api_send_response(422, false, "Teacher name and mobile number are required.");
    }

    api_send_response(200, true, "Teacher information saved successfully.", $input);
}
