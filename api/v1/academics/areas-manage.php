<?php
/**
 * EIMBox REST API - Academic Areas (Classes & Sections) Management
 * Endpoint: /api/v1/academics/areas-manage.php
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

// 1. GET: Fetch Class & Section Structure
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? date('Y'));
    $slot = trim($_GET['slot'] ?? '');

    $sql = "SELECT a.id, a.idno, a.sccode, a.slot, a.sessionyear, a.areaname, a.subarea, 
                   a.classteacher, t.tname as teacher_name, t.mobile as teacher_mobile,
                   (SELECT COUNT(*) FROM sessioninfo s 
                    WHERE s.sccode = a.sccode 
                      AND s.sessionyear = a.sessionyear 
                      AND s.classname = a.areaname 
                      AND s.sectionname = a.subarea 
                      AND s.status = 1) as student_count
            FROM areas a
            LEFT JOIN teacher t ON (t.sccode = a.sccode AND (t.tid = a.classteacher OR t.id = a.classteacher))
            WHERE a.sccode = ? AND a.sessionyear = ?";
    
    $params = [$sccode, $sessionyear];
    $types = "is";

    if (!empty($slot) && $slot !== 'All') {
        $sql .= " AND a.slot = ?";
        $params[] = $slot;
        $types .= "s";
    }

    $sql .= " ORDER BY a.idno ASC, a.areaname ASC, a.subarea ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $areas = [];
    while ($row = $result->fetch_assoc()) {
        $areas[] = [
            'id' => (int)$row['id'],
            'idno' => (int)$row['idno'],
            'slot' => $row['slot'] ?: 'General',
            'sessionyear' => $row['sessionyear'],
            'classname' => $row['areaname'],
            'sectionname' => $row['subarea'],
            'classteacher' => $row['classteacher'] ? (int)$row['classteacher'] : null,
            'teacher_name' => $row['teacher_name'] ?: 'Not Assigned',
            'teacher_mobile' => $row['teacher_mobile'] ?: '',
            'student_count' => (int)$row['student_count']
        ];
    }
    $stmt->close();

    // Fetch Slots for reference
    $slots = [];
    $slotStmt = $conn->prepare("SELECT id, slotname FROM slots WHERE sccode = ? ORDER BY slotname ASC");
    if ($slotStmt) {
        $slotStmt->bind_param("i", $sccode);
        $slotStmt->execute();
        $slotRes = $slotStmt->get_result();
        while ($sRow = $slotRes->fetch_assoc()) {
            $slots[] = $sRow;
        }
        $slotStmt->close();
    }

    api_send_response(200, true, "Academic areas fetched successfully.", [
        'sessionyear' => $sessionyear,
        'count' => count($areas),
        'areas' => $areas,
        'slots' => $slots
    ]);
}

// 2. POST: Create or Update Class/Section
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $areaname = trim($input['classname'] ?? $input['areaname'] ?? '');
    $subarea = trim($input['sectionname'] ?? $input['subarea'] ?? '');
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $slot = trim($input['slot'] ?? 'General');
    $idno = (int)($input['idno'] ?? 0);
    $classteacher = isset($input['classteacher']) && !empty($input['classteacher']) ? (int)$input['classteacher'] : null;

    if (empty($areaname) || empty($subarea) || empty($sessionyear)) {
        api_send_response(422, false, "Class name, section name, and session year are required.");
    }

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE areas 
                                SET areaname = ?, subarea = ?, sessionyear = ?, slot = ?, idno = ?, classteacher = ?, modifieddate = NOW() 
                                WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ssssiiii", $areaname, $subarea, $sessionyear, $slot, $idno, $classteacher, $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        api_send_response(200, true, "Class and section updated successfully.", [
            'id' => $id,
            'classname' => $areaname,
            'sectionname' => $subarea,
            'sessionyear' => $sessionyear,
            'slot' => $slot
        ]);
    } else {
        // Insert (or Upsert on Duplicate)
        $stmt = $conn->prepare("INSERT INTO areas (sccode, sessionyear, slot, areaname, subarea, idno, classteacher, modifieddate)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE idno = VALUES(idno), classteacher = VALUES(classteacher), modifieddate = NOW()");
        $stmt->bind_param("issssii", $sccode, $sessionyear, $slot, $areaname, $subarea, $idno, $classteacher);
        $stmt->execute();
        $insertId = $conn->insert_id ?: $id;
        $stmt->close();

        api_send_response(201, true, "Class and section created successfully.", [
            'id' => $insertId,
            'classname' => $areaname,
            'sectionname' => $subarea,
            'sessionyear' => $sessionyear,
            'slot' => $slot
        ]);
    }
}

// 3. DELETE: Remove Class/Section
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
    }

    if ($id <= 0) {
        api_send_response(422, false, "Valid area ID is required for deletion.");
    }

    $stmt = $conn->prepare("DELETE FROM areas WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_send_response(200, true, "Class section removed successfully.");
    } else {
        api_send_response(404, false, "Area not found or already deleted.");
    }
}

api_send_response(405, false, "Method not allowed.");
