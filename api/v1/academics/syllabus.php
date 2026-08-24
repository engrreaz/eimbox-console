<?php
/**
 * EIMBox REST API — Academic Syllabus & Curriculum Planner
 * Endpoint: /api/v1/academics/syllabus.php
 * Routes:
 *   GET /api/v1/academics/syllabus.php?sccode={sccode}&sessionyear={year}&classname={class}&subcode={subcode}&examcode={term}
 *   POST /api/v1/academics/syllabus.php (Create / Update Chapter)
 *   DELETE /api/v1/academics/syllabus.php?id={id}&sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = api_authenticate_request();
$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $input['action'] ?? '';

// 1. Resolve School Code
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle DELETE
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid syllabus item ID required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM syllabus WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Syllabus chapter removed successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Syllabus chapter not found.', null, 404);
    }
}

// 3. Handle POST / PUT: Save / Update Chapter
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? 'All');
    $subcode = intval($input['subcode'] ?? 0);
    $examcode = trim($input['examcode'] ?? 'Half Yearly Term');
    $chapterNo = trim($input['chapter_no'] ?? '01');
    $chapterName = trim($input['chapter_name'] ?? '');
    $topics = trim($input['topics'] ?? '');
    $periodsAllotted = intval($input['periods_allotted'] ?? 1);
    $examMarks = trim($input['exam_marks'] ?? '');
    $targetDate = !empty($input['target_date']) ? $input['target_date'] : null;
    $status = trim($input['status'] ?? 'Pending');

    if (empty($classname) || empty($chapterName) || $subcode <= 0) {
        api_response('error', 'Class name, Subject, and Chapter title are required.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE syllabus SET 
            sessionyear = ?, classname = ?, sectionname = ?, subcode = ?, examcode = ?,
            chapter_no = ?, chapter_name = ?, topics = ?, periods_allotted = ?, exam_marks = ?,
            target_date = ?, status = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param("sssissssissiii",
            $sessionyear, $classname, $sectionname, $subcode, $examcode,
            $chapterNo, $chapterName, $topics, $periodsAllotted, $examMarks,
            $targetDate, $status,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Syllabus chapter updated successfully.', ['id' => $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO syllabus (
            sccode, sessionyear, slot, classname, sectionname, subcode, examcode,
            chapter_no, chapter_name, topics, periods_allotted, exam_marks, target_date, status, modifieddate
        ) VALUES (?, ?, 'School', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssissssisss",
            $sccode, $sessionyear, $classname, $sectionname, $subcode, $examcode,
            $chapterNo, $chapterName, $topics, $periodsAllotted, $examMarks, $targetDate, $status
        );
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Syllabus chapter created successfully.', ['id' => $insertId], 201);
    }
}

// 4. GET: Fetch Syllabus chapters
if ($method === 'GET') {
    $session = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $className = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $subcode = intval($_GET['subcode'] ?? 0);
    $examcode = trim($_GET['examcode'] ?? $_GET['term'] ?? '');

    // Fetch Classes & Subjects for dropdowns
    $classes = [];
    $aStmt = $conn->prepare("SELECT areaname FROM areas WHERE sccode = ? AND sessionyear = ? GROUP BY areaname ORDER BY MIN(idno) ASC, areaname ASC");
    if ($aStmt) {
        $aStmt->bind_param("is", $sccode, $session);
        $aStmt->execute();
        $aRes = $aStmt->get_result();
        while ($aRow = $aRes->fetch_assoc()) {
            $classes[] = $aRow['areaname'];
        }
        $aStmt->close();
    }
    if (empty($classes)) $classes = ['Six', 'Seven', 'Eight', 'Nine', 'Ten'];
    if (empty($className)) $className = $classes[0];

    // Fetch Subjects for this class
    $subjects = [];
    $sStmt = $conn->prepare("SELECT ss.subject as subcode, COALESCE(s.subject, CONCAT('Subject #', ss.subject)) as subname, COALESCE(s.subshname, '') as shortname 
                            FROM subsetup ss 
                            LEFT JOIN subjects s ON (s.subcode = ss.subject AND (s.sccode = 0 OR s.sccode = ss.sccode))
                            WHERE ss.sccode = ? AND ss.sessionyear = ? AND ss.classname = ?
                            ORDER BY ss.slno ASC");
    if ($sStmt) {
        $sStmt->bind_param("iss", $sccode, $session, $className);
        $sStmt->execute();
        $sRes = $sStmt->get_result();
        while ($sRow = $sRes->fetch_assoc()) {
            $subjects[] = [
                'subcode' => intval($sRow['subcode']),
                'subname' => $sRow['subname'],
                'shortname' => $sRow['shortname'] ?: $sRow['subname']
            ];
        }
        $sStmt->close();
    }
    if ($subcode <= 0 && !empty($subjects)) {
        $subcode = $subjects[0]['subcode'];
    }

    // Fetch Exam Terms
    $terms = [];
    $eStmt = $conn->prepare("SELECT examtitle FROM examlist WHERE sccode = ? AND sessionyear = ? ORDER BY id ASC");
    if ($eStmt) {
        $eStmt->bind_param("is", $sccode, $session);
        $eStmt->execute();
        $eRes = $eStmt->get_result();
        while ($eRow = $eRes->fetch_assoc()) {
            $terms[] = $eRow['examtitle'];
        }
        $eStmt->close();
    }
    if (empty($terms)) {
        $terms = ['Half Yearly Examination', 'Annual Examination', 'Pre-Test Examination', 'Test Examination'];
    }
    if (empty($examcode)) {
        $examcode = $terms[0];
    }

    // Query Syllabus items
    $chapters = [];
    $sql = "SELECT id, sccode, sessionyear, slot, classname, sectionname, subcode, examcode,
                   chapter_no, chapter_name, topics, periods_allotted, exam_marks, target_date, status, modifieddate
            FROM syllabus 
            WHERE sccode = ? AND sessionyear = ? AND classname = ?";
    $params = [$sccode, $session, $className];
    $types = "iss";

    if ($subcode > 0) {
        $sql .= " AND subcode = ?";
        $params[] = $subcode;
        $types .= "i";
    }

    if (!empty($examcode) && $examcode !== 'All') {
        $sql .= " AND examcode = ?";
        $params[] = $examcode;
        $types .= "s";
    }

    $sql .= " ORDER BY chapter_no ASC, id ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $chapters[] = [
                'id' => intval($r['id']),
                'chapter_no' => $r['chapter_no'] ?: '01',
                'chapter_name' => $r['chapter_name'],
                'topics' => $r['topics'] ?: '',
                'periods_allotted' => intval($r['periods_allotted'] ?: 1),
                'exam_marks' => $r['exam_marks'] ?: '',
                'target_date' => $r['target_date'] ?: '',
                'status' => $r['status'] ?: 'Pending',
                'classname' => $r['classname'],
                'subcode' => intval($r['subcode']),
                'examcode' => $r['examcode']
            ];
        }
        $stmt->close();
    }

    api_response('success', 'Syllabus chapters loaded successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $session,
        'classname' => $className,
        'subcode' => $subcode,
        'examcode' => $examcode,
        'classes' => $classes,
        'subjects' => $subjects,
        'terms' => $terms,
        'chapters' => $chapters
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
