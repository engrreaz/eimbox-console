<?php
/**
 * EIMBox REST API - Exam Routine & Timetable Builder
 * Endpoint: /api/v1/exams/exam-routine.php
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

// 1. GET: Fetch Routine Entries with Filters
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $examname = trim($_GET['examname'] ?? $_GET['exam'] ?? 'All');
    $clsname = trim($_GET['clsname'] ?? $_GET['class'] ?? 'All');
    $secname = trim($_GET['secname'] ?? $_GET['section'] ?? 'All');

    $where = ["sccode = ?"];
    $types = "i";
    $params = [$activeSccode];

    if (!empty($sessionyear) && $sessionyear !== 'All') {
        $where[] = "sessionyear = ?";
        $types .= "i";
        $params[] = (int)$sessionyear;
    }
    if (!empty($examname) && $examname !== 'All') {
        $where[] = "examname = ?";
        $types .= "s";
        $params[] = $examname;
    }
    if (!empty($clsname) && $clsname !== 'All') {
        $where[] = "clsname = ?";
        $types .= "s";
        $params[] = $clsname;
    }
    if (!empty($secname) && $secname !== 'All') {
        $where[] = "secname = ?";
        $types .= "s";
        $params[] = $secname;
    }

    $sql = "SELECT id, sessionyear, examname, sccode, date, time, clsname, secname, subcode, subj, progress, modifieddate 
            FROM examroutine 
            WHERE " . implode(" AND ", $where) . " 
            ORDER BY date ASC, time ASC, clsname ASC, secname ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$r['id'],
            'sessionyear' => (int)$r['sessionyear'],
            'examname' => $r['examname'],
            'sccode' => (int)$r['sccode'],
            'date' => $r['date'],
            'time' => $r['time'],
            'clsname' => $r['clsname'],
            'secname' => $r['secname'],
            'subcode' => $r['subcode'] !== null ? (int)$r['subcode'] : null,
            'subj' => $r['subj'],
            'progress' => (int)$r['progress'],
            'modifieddate' => $r['modifieddate']
        ];
    }
    $stmt->close();

    // Distinct dropdown filter items for active school & session
    $dStmt = $conn->prepare("
        SELECT DISTINCT examname, clsname, secname 
        FROM examroutine 
        WHERE sccode = ? AND sessionyear = ?
    ");
    $sessInt = (int)$sessionyear;
    $dStmt->bind_param("ii", $activeSccode, $sessInt);
    $dStmt->execute();
    $dRes = $dStmt->get_result();
    $exams = [];
    $classes = [];
    $sections = [];
    while ($dr = $dRes->fetch_assoc()) {
        if (!empty($dr['examname']) && !in_array($dr['examname'], $exams)) $exams[] = $dr['examname'];
        if (!empty($dr['clsname']) && !in_array($dr['clsname'], $classes)) $classes[] = $dr['clsname'];
        if (!empty($dr['secname']) && !in_array($dr['secname'], $sections)) $sections[] = $dr['secname'];
    }
    $dStmt->close();

    api_send_response(200, true, "Exam routine loaded.", [
        'rows' => $rows,
        'exams' => $exams,
        'classes' => $classes,
        'sections' => $sections,
        'count' => count($rows)
    ]);
}

// 2. POST: Create or Update Routine Entry or Bulk Save
if ($method === 'POST') {
    $input = get_api_input();

    // Check if bulk entries array provided
    if (isset($input['entries']) && is_array($input['entries'])) {
        $savedCount = 0;
        foreach ($input['entries'] as $entry) {
            $sess = (int)($entry['sessionyear'] ?? date('Y'));
            $exName = trim($entry['examname'] ?? '');
            $date = trim($entry['date'] ?? '');
            $time = trim($entry['time'] ?? '08:00:00');
            $cls = trim($entry['clsname'] ?? '');
            $sec = trim($entry['secname'] ?? 'General');
            $subcode = isset($entry['subcode']) && $entry['subcode'] !== '' ? (int)$entry['subcode'] : null;
            $subj = trim($entry['subj'] ?? '');
            $prog = (int)($entry['progress'] ?? 0);
            $entryId = (int)($entry['id'] ?? 0);

            if (empty($exName) || empty($date) || empty($subj)) continue;

            if ($entryId > 0) {
                $stmt = $conn->prepare("
                    UPDATE examroutine 
                    SET sessionyear = ?, examname = ?, date = ?, time = ?, clsname = ?, secname = ?, subcode = ?, subj = ?, progress = ?, modifieddate = NOW()
                    WHERE id = ? AND sccode = ?
                ");
                $stmt->bind_param("isssssisiii", $sess, $exName, $date, $time, $cls, $sec, $subcode, $subj, $prog, $entryId, $activeSccode);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO examroutine (sessionyear, examname, sccode, date, time, clsname, secname, subcode, subj, progress, modifieddate)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("isisssisis", $sess, $exName, $activeSccode, $date, $time, $cls, $sec, $subcode, $subj, $prog);
                $stmt->execute();
                $stmt->close();
            }
            $savedCount++;
        }
        api_send_response(200, true, "Bulk exam routine saved.", ['count' => $savedCount]);
    }

    // Single entry save
    $id = (int)($input['id'] ?? 0);
    $sessionyear = (int)($input['sessionyear'] ?? date('Y'));
    $examname = trim($input['examname'] ?? '');
    $date = trim($input['date'] ?? '');
    $time = trim($input['time'] ?? '08:00:00');
    $clsname = trim($input['clsname'] ?? '');
    $secname = trim($input['secname'] ?? 'General');
    $subcode = isset($input['subcode']) && $input['subcode'] !== '' ? (int)$input['subcode'] : null;
    $subj = trim($input['subj'] ?? '');
    $progress = (int)($input['progress'] ?? 0);

    if (empty($examname) || empty($date) || empty($subj)) {
        api_send_response(422, false, "Exam name, date, and subject are required.");
    }

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE examroutine 
            SET sessionyear = ?, examname = ?, date = ?, time = ?, clsname = ?, secname = ?, subcode = ?, subj = ?, progress = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?
        ");
        $stmt->bind_param("isssssisiii", $sessionyear, $examname, $date, $time, $clsname, $secname, $subcode, $subj, $progress, $id, $activeSccode);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("
            INSERT INTO examroutine (sessionyear, examname, sccode, date, time, clsname, secname, subcode, subj, progress, modifieddate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isisssisis", $sessionyear, $examname, $activeSccode, $date, $time, $clsname, $secname, $subcode, $subj, $progress);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
    }

    api_send_response(200, true, "Routine entry saved successfully.", [
        'id' => $id,
        'sessionyear' => $sessionyear,
        'examname' => $examname,
        'date' => $date,
        'time' => $time,
        'clsname' => $clsname,
        'secname' => $secname,
        'subcode' => $subcode,
        'subj' => $subj,
        'progress' => $progress
    ]);
}

// 3. DELETE: Remove Routine Entry
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        api_send_response(422, false, "Valid routine entry ID is required.");
    }

    $stmt = $conn->prepare("DELETE FROM examroutine WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $activeSccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_send_response(200, true, "Routine entry deleted successfully.");
    } else {
        api_send_response(404, false, "Entry not found or already deleted.");
    }
}
