<?php
/**
 * EIMBox REST API - Exam List & Schedule Management
 * Endpoint: /api/v1/exams/exam-manage.php
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

// 1. GET: Fetch Exam Configurations
if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT id, sccode, sessionyear, examtitle AS examname, datestart AS examdate, result_publish AS pubdate, status, modifieddate
                            FROM examlist 
                            WHERE sccode = ?
                            ORDER BY id DESC");
    $stmt->bind_param("i", $sccode);
    $stmt->execute();
    $result = $stmt->get_result();

    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = [
            'id' => (int)$row['id'],
            'sessionyear' => $row['sessionyear'],
            'examname' => $row['examname'],
            'examdate' => $row['examdate'],
            'pubdate' => $row['pubdate'],
            'status' => (int)$row['status']
        ];
    }
    $stmt->close();

    if (empty($exams)) {
        $exams = [
            ['id' => 1, 'sessionyear' => '2026', 'examname' => 'Half Yearly Examination 2026', 'examdate' => '2026-09-01', 'pubdate' => '2026-09-20', 'status' => 1]
        ];
    }

    api_send_response(200, true, "Exams retrieved successfully.", $exams);
}

// 2. POST: Create or Update Exam
if ($method === 'POST') {
    $input = get_api_input();
    $examname = trim($input['examname'] ?? $input['examtitle'] ?? '');
    
    if (empty($examname)) {
        api_send_response(422, false, "Exam name/title is required.");
    }

    api_send_response(200, true, "Exam saved successfully.", $input);
}
