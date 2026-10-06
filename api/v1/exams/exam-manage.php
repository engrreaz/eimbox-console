<?php
/**
 * EIMBox REST API - Exam List & Schedule Management
 * Endpoint: /api/v1/exams/exam-manage.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request
$user = api_authenticate_request();
$sccode = (int)($user['sccode'] ?? 0);

if ($sccode <= 0 && (!isset($_GET['sccode']) || (int)$_GET['sccode'] <= 0)) {
    api_send_response(400, false, "Invalid school institution code.");
}

$conn = api_get_db_connection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$activeSccode = isset($_GET['sccode']) && (int)$_GET['sccode'] > 0 ? (int)$_GET['sccode'] : $sccode;

// 1. GET: Fetch Exam Configurations
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');
    
    $sql = "SELECT e.id, e.sccode, e.sessionyear, e.slot, e.examtitle, e.examcode, e.linkedexam, e.exam_group, e.exam_type, 
                   e.classname, e.sectionname, e.datestart, e.result_publish, e.status, e.hall_code, e.modifieddate,
                   p.examtitle AS parent_exam_title
            FROM examlist e
            LEFT JOIN examlist p ON e.linkedexam = p.id AND (p.sccode = ? OR p.sccode = 0)
            WHERE (e.sccode = ? OR e.sccode = 0)";
    $params = [$activeSccode, $activeSccode];
    $types = "ii";

    if (!empty($sessionyear) && $sessionyear !== 'All') {
        $sql .= " AND (e.sessionyear = ? OR e.sessionyear IS NULL OR e.sessionyear = '')";
        $params[] = $sessionyear;
        $types .= "s";
    }

    $sql .= " ORDER BY e.sessionyear DESC, CASE WHEN e.linkedexam = 0 OR e.linkedexam IS NULL THEN e.id ELSE e.linkedexam END ASC, e.linkedexam ASC, e.datestart ASC, e.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $exams = [];
    while ($row = $result->fetch_assoc()) {
        $exams[] = [
            'id' => (int)$row['id'],
            'sccode' => (int)$row['sccode'],
            'sessionyear' => $row['sessionyear'] ?: date('Y'),
            'slot' => $row['slot'] ?: 'School',
            'examtitle' => $row['examtitle'],
            'examname' => $row['examtitle'],
            'examcode' => $row['examcode'] ?: (string)$row['id'],
            'linkedexam' => (int)($row['linkedexam'] ?? 0),
            'parent_exam_title' => $row['parent_exam_title'] ?: '',
            'exam_type' => strtoupper(trim($row['exam_type'] ?? 'PE')) ?: 'PE',
            'exam_group' => $row['exam_group'] ?: '',
            'datestart' => $row['datestart'],
            'examdate' => $row['datestart'],
            'result_publish' => $row['result_publish'],
            'pubdate' => $row['result_publish'],
            'status' => (int)$row['status'],
            'hall_code' => $row['hall_code'] ?: '',
            'classname' => $row['classname'] ?: '',
            'sectionname' => $row['sectionname'] ?: '',
            'modifieddate' => $row['modifieddate']
        ];
    }
    $stmt->close();

    api_send_response(200, true, "Exams retrieved successfully.", $exams);
}

// 2. POST: Create or Update Exam
if ($method === 'POST') {
    $input = get_api_input();
    $id = (int)($input['id'] ?? 0);
    $examtitle = trim($input['examtitle'] ?? $input['examname'] ?? '');
    $sessionyear = trim($input['sessionyear'] ?? $input['session'] ?? date('Y'));
    $slot = trim($input['slot'] ?? 'School');
    $exam_type = strtoupper(trim($input['exam_type'] ?? 'PE')) ?: 'PE';
    $linkedexam = ($exam_type === 'PE') ? 0 : (int)($input['linkedexam'] ?? 0);
    $exam_group = trim($input['exam_group'] ?? '');
    $examcode = trim($input['examcode'] ?? ($id > 0 ? (string)$id : ''));
    $datestart = !empty($input['datestart']) ? trim($input['datestart']) : (!empty($input['examdate']) ? trim($input['examdate']) : null);
    $resultPublish = !empty($input['result_publish']) ? trim($input['result_publish']) : (!empty($input['pubdate']) ? trim($input['pubdate']) : null);
    $status = isset($input['status']) ? (int)$input['status'] : 1;
    $hallCode = trim($input['hall_code'] ?? '');
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? '');
    $createdby = $user['profilename'] ?? $user['username'] ?? 'Admin';

    if (empty($examtitle)) {
        api_send_response(422, false, "Exam title/name is required.");
    }

    if ($id > 0) {
        if (empty($examcode)) {
            $examcode = (string)$id;
        }
        $stmt = $conn->prepare("
            UPDATE examlist 
            SET examtitle = ?, sessionyear = ?, slot = ?, examcode = ?, exam_type = ?, linkedexam = ?, exam_group = ?, datestart = ?, result_publish = ?, status = ?, hall_code = ?, classname = ?, sectionname = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?
        ");
        $stmt->bind_param("sssssississsiii", $examtitle, $sessionyear, $slot, $examcode, $exam_type, $linkedexam, $exam_group, $datestart, $resultPublish, $status, $hallCode, $classname, $sectionname, $id, $activeSccode);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("
            INSERT INTO examlist (sccode, sessionyear, slot, examtitle, examcode, exam_type, linkedexam, exam_group, datestart, result_publish, createdby, createtime, status, hall_code, classname, sectionname, modifieddate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isssssissssisss", $activeSccode, $sessionyear, $slot, $examtitle, $examcode, $exam_type, $linkedexam, $exam_group, $datestart, $resultPublish, $createdby, $status, $hallCode, $classname, $sectionname);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();

        if (empty($examcode)) {
            $examcode = (string)$id;
            $conn->query("UPDATE examlist SET examcode = '$examcode' WHERE id = $id AND sccode = $activeSccode");
        }
    }

    api_send_response(200, true, "Exam saved successfully.", [
        'id' => $id,
        'sccode' => $activeSccode,
        'sessionyear' => $sessionyear,
        'slot' => $slot,
        'examtitle' => $examtitle,
        'examcode' => $examcode,
        'exam_type' => $exam_type,
        'linkedexam' => $linkedexam,
        'exam_group' => $exam_group,
        'datestart' => $datestart,
        'result_publish' => $resultPublish,
        'status' => $status,
        'hall_code' => $hallCode,
        'classname' => $classname,
        'sectionname' => $sectionname
    ]);
}

// 3. DELETE: Remove Exam
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        api_send_response(422, false, "Valid exam ID is required.");
    }

    $stmt = $conn->prepare("DELETE FROM examlist WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $activeSccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_send_response(200, true, "Exam deleted successfully.");
    } else {
        api_send_response(404, false, "Exam not found or already deleted.");
    }
}
