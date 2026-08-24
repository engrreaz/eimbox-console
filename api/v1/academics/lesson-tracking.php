<?php
/**
 * EIMBox REST API — Lesson Tracking & Teacher Diary Management
 * Endpoint: /api/v1/academics/lesson-tracking.php
 * Routes:
 *   GET /api/v1/academics/lesson-tracking.php?sccode={sccode}&sessionyear={year}&classname={class}&sectionname={sec}&tid={tid}
 *   POST /api/v1/academics/lesson-tracking.php (Create / Update Lesson Log)
 *   DELETE /api/v1/academics/lesson-tracking.php?id={id}&sccode={sccode}
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
        api_response('error', 'Valid lesson log ID required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM lesson_tracking WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Lesson log removed successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Lesson entry not found.', null, 404);
    }
}

// 3. Handle POST / PUT: Save / Update Lesson Entry
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? 'A');
    $subcode = intval($input['subcode'] ?? 0);
    $tid = intval($input['tid'] ?? 0);
    $period = intval($input['period'] ?? 1);
    $lessonDate = trim($input['lesson_date'] ?? date('Y-m-d'));
    $topicCovered = trim($input['topic_covered'] ?? '');
    $homework = trim($input['homework'] ?? '');
    $attendanceCount = intval($input['attendance_count'] ?? 0);
    $remarks = trim($input['remarks'] ?? '');
    $status = trim($input['status'] ?? 'Completed');

    if (empty($classname) || empty($topicCovered) || empty($lessonDate)) {
        api_response('error', 'Class, Topic covered, and Lesson date are required.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE lesson_tracking SET 
            sessionyear = ?, classname = ?, sectionname = ?, subcode = ?, tid = ?,
            period = ?, lesson_date = ?, topic_covered = ?, homework = ?,
            attendance_count = ?, remarks = ?, status = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param("sssiisississii",
            $sessionyear, $classname, $sectionname, $subcode, $tid,
            $period, $lessonDate, $topicCovered, $homework,
            $attendanceCount, $remarks, $status,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Lesson log updated successfully.', ['id' => $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO lesson_tracking (
            sccode, sessionyear, classname, sectionname, subcode, tid,
            period, lesson_date, topic_covered, homework, attendance_count, remarks, status, modifieddate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssiisississ",
            $sccode, $sessionyear, $classname, $sectionname, $subcode, $tid,
            $period, $lessonDate, $topicCovered, $homework, $attendanceCount, $remarks, $status
        );
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Lesson log recorded successfully.', ['id' => $insertId], 201);
    }
}

// 4. GET: Fetch Lesson Tracking Logs & Stats
if ($method === 'GET') {
    $session = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $className = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionName = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $tid = intval($_GET['tid'] ?? 0);
    $limit = intval($_GET['limit'] ?? 100);

    // Fetch Classes & Sections
    $classes = [];
    $sectionsMap = [];
    $aStmt = $conn->prepare("SELECT areaname, subarea FROM areas WHERE sccode = ? AND sessionyear = ? GROUP BY areaname, subarea ORDER BY MIN(idno) ASC, areaname ASC, subarea ASC");
    if ($aStmt) {
        $aStmt->bind_param("is", $sccode, $session);
        $aStmt->execute();
        $aRes = $aStmt->get_result();
        while ($aRow = $aRes->fetch_assoc()) {
            $cName = $aRow['areaname'];
            $sName = $aRow['subarea'];
            if (!in_array($cName, $classes)) $classes[] = $cName;
            if (!isset($sectionsMap[$cName])) $sectionsMap[$cName] = [];
            if (!in_array($sName, $sectionsMap[$cName])) $sectionsMap[$cName][] = $sName;
        }
        $aStmt->close();
    }
    if (empty($classes)) $classes = ['Six', 'Seven', 'Eight', 'Nine', 'Ten'];

    // Fetch Teachers
    $teachers = [];
    $tStmt = $conn->prepare("SELECT id, tid, tname, position FROM teacher WHERE sccode = ? AND (status = 'Active' OR status = '1' OR status = '' OR status IS NULL) ORDER BY tname ASC");
    if ($tStmt) {
        $tStmt->bind_param("i", $sccode);
        $tStmt->execute();
        $tRes = $tStmt->get_result();
        while ($tRow = $tRes->fetch_assoc()) {
            $teachers[] = [
                'id' => intval($tRow['id']),
                'tid' => (string)$tRow['tid'],
                'tname' => $tRow['tname'],
                'position' => $tRow['position'] ?: ''
            ];
        }
        $tStmt->close();
    }

    // Fetch Subjects
    $subjects = [];
    $sStmt = $conn->prepare("SELECT subcode, subject FROM subjects WHERE sccode = 0 OR sccode = ? ORDER BY subcode ASC");
    if ($sStmt) {
        $sStmt->bind_param("i", $sccode);
        $sStmt->execute();
        $sRes = $sStmt->get_result();
        while ($sRow = $sRes->fetch_assoc()) {
            $subjects[] = [
                'subcode' => intval($sRow['subcode']),
                'subject' => $sRow['subject']
            ];
        }
        $sStmt->close();
    }

    // Query Lesson Logs
    $where = "l.sccode = ? AND l.sessionyear = ?";
    $params = [$sccode, $session];
    $types = "is";

    if (!empty($className) && $className !== 'All') {
        $where .= " AND l.classname = ?";
        $params[] = $className;
        $types .= "s";
    }
    if (!empty($sectionName) && $sectionName !== 'All') {
        $where .= " AND l.sectionname = ?";
        $params[] = $sectionName;
        $types .= "s";
    }
    if ($tid > 0) {
        $where .= " AND l.tid = ?";
        $params[] = $tid;
        $types .= "i";
    }

    $sql = "SELECT l.id, l.sccode, l.sessionyear, l.classname, l.sectionname, l.subcode, l.tid,
                   l.period, l.lesson_date, l.topic_covered, l.homework, l.attendance_count, l.remarks, l.status,
                   s.subject AS subject_name,
                   t.tname AS teacher_name
            FROM lesson_tracking l
            LEFT JOIN subjects s ON (s.subcode = l.subcode AND (s.sccode = l.sccode OR s.sccode = 0))
            LEFT JOIN teacher t ON (t.tid = l.tid AND t.sccode = l.sccode)
            WHERE $where
            ORDER BY l.lesson_date DESC, l.period ASC, l.id DESC
            LIMIT $limit";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $logs = [];
    $totalLessons = 0;
    $homeworkCount = 0;
    $activeTeachersSet = [];

    while ($r = $res->fetch_assoc()) {
        $totalLessons++;
        if (!empty($r['homework'])) $homeworkCount++;
        if (!empty($r['teacher_name'])) $activeTeachersSet[$r['teacher_name']] = true;

        $logs[] = [
            'id' => intval($r['id']),
            'lesson_date' => $r['lesson_date'],
            'classname' => $r['classname'],
            'sectionname' => $r['sectionname'],
            'period' => intval($r['period']),
            'period_label' => "Period {$r['period']}",
            'subcode' => intval($r['subcode']),
            'subject_name' => $r['subject_name'] ?: ($r['subcode'] ? "Subject #{$r['subcode']}" : 'General Class'),
            'tid' => intval($r['tid']),
            'teacher_name' => $r['teacher_name'] ?: 'Faculty',
            'topic_covered' => $r['topic_covered'],
            'homework' => $r['homework'] ?: '—',
            'attendance_count' => intval($r['attendance_count']),
            'remarks' => $r['remarks'] ?: '',
            'status' => $r['status'] ?: 'Completed'
        ];
    }
    $stmt->close();

    $summary = [
        'total_lessons' => $totalLessons,
        'homeworks_assigned' => $homeworkCount,
        'active_teachers' => count($activeTeachersSet),
        'syllabus_completion_rate' => $totalLessons > 0 ? min(100, round(($totalLessons / 150) * 100, 1)) . '%' : '0%'
    ];

    api_response('success', 'Lesson tracking entries loaded successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $session,
        'classes' => $classes,
        'sections_map' => $sectionsMap,
        'teachers' => $teachers,
        'subjects' => $subjects,
        'summary' => $summary,
        'logs' => $logs
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
