<?php
/**
 * EIMBox REST API — Academic Schedule & Period Bell Management
 * Endpoint: /api/v1/academics/class-schedule.php
 * Routes:
 *   GET /api/v1/academics/class-schedule.php?sccode={sccode}&sessionyear={year}&slot={slot}
 *   POST /api/v1/academics/class-schedule.php (Create / Update / Bulk Save / Clone)
 *   DELETE /api/v1/academics/class-schedule.php?id={id}&sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request
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
        api_response('error', 'Valid schedule ID is required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM classschedule WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Period schedule entry removed successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Period not found or already removed.', null, 404);
    }
}

// 3. Handle POST: Bulk Save / Clone Slot Schedule
if ($method === 'POST' && ($action === 'bulk_save' || isset($input['periods']))) {
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $slot = trim($input['slot'] ?? $input['slots'] ?? $input['shift'] ?? 'School');
    $periodsList = is_array($input['periods']) ? $input['periods'] : [];

    // Optional: Clear existing for this slot & session if requested
    if (!empty($input['replace_all'])) {
        $del = $conn->prepare("DELETE FROM classschedule WHERE sccode = ? AND sessionyear = ? AND (slots = ? OR slots = '' OR slots IS NULL)");
        $del->bind_param("iss", $sccode, $sessionyear, $slot);
        $del->execute();
        $del->close();
    }

    $savedCount = 0;
    $stmt = $conn->prepare("INSERT INTO classschedule (sccode, sessionyear, slots, shift, period, timestart, timeend, duration, modifieddate)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    foreach ($periodsList as $idx => $p) {
        $periodNum = intval($p['period'] ?? ($idx + 1));
        $timeStart = trim($p['timestart'] ?? $p['start'] ?? '08:00');
        $timeEnd = trim($p['timeend'] ?? $p['end'] ?? '08:45');
        $duration = intval($p['duration'] ?? 45);
        $shiftVal = $slot;

        $stmt->bind_param("isssissi", $sccode, $sessionyear, $slot, $shiftVal, $periodNum, $timeStart, $timeEnd, $duration);
        $stmt->execute();
        $savedCount++;
    }
    $stmt->close();

    api_response('success', "Saved $savedCount period bell timings for $slot Slot ($sessionyear).", [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'slot' => $slot,
        'count' => $savedCount
    ]);
}

// 4. Handle POST / PUT: Save Single Period Record
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $slot = trim($input['slot'] ?? $input['slots'] ?? $input['shift'] ?? 'School');
    $period = intval($input['period'] ?? 1);
    $timeStart = trim($input['timestart'] ?? $input['start'] ?? '08:00');
    $timeEnd = trim($input['timeend'] ?? $input['end'] ?? '08:45');
    $duration = intval($input['duration'] ?? 45);
    $shiftVal = $slot;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE classschedule SET sessionyear = ?, slots = ?, shift = ?, period = ?, timestart = ?, timeend = ?, duration = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("sssisssii", $sessionyear, $slot, $shiftVal, $period, $timeStart, $timeEnd, $duration, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Period schedule updated successfully.', ['id' => $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO classschedule (sccode, sessionyear, slots, shift, period, timestart, timeend, duration, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssissi", $sccode, $sessionyear, $slot, $shiftVal, $period, $timeStart, $timeEnd, $duration);
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Period schedule created successfully.', ['id' => $insertId], 201);
    }
}

// 5. GET: Fetch Period Schedule for Session & Slot
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');
    $slot = trim($_GET['slot'] ?? $_GET['slots'] ?? $_GET['shift'] ?? '');

    // Fetch Slots List
    $slotsList = [];
    $slotStmt = $conn->prepare("SELECT id, slotname FROM slots WHERE sccode = ? OR sccode = 0 ORDER BY id ASC");
    if ($slotStmt) {
        $slotStmt->bind_param("i", $sccode);
        $slotStmt->execute();
        $slotRes = $slotStmt->get_result();
        while ($sRow = $slotRes->fetch_assoc()) {
            $slotsList[] = $sRow['slotname'];
        }
        $slotStmt->close();
    }
    if (empty($slotsList)) {
        $slotsList = ['School', 'College', 'Morning', 'Day'];
    }

    // Fetch Sessions from sessionyear
    $sessionsList = [];
    $activeSession = '';
    $sessStmt = $conn->prepare("SELECT syear, active FROM sessionyear WHERE sccode = ? OR sccode = 0 ORDER BY active DESC, syear DESC");
    if ($sessStmt) {
        $sessStmt->bind_param("i", $sccode);
        $sessStmt->execute();
        $sessRes = $sessStmt->get_result();
        while ($sRow = $sessRes->fetch_assoc()) {
            $yStr = strval($sRow['syear']);
            if (!in_array($yStr, $sessionsList)) $sessionsList[] = $yStr;
            if (intval($sRow['active']) === 1 && empty($activeSession)) $activeSession = $yStr;
        }
        $sessStmt->close();
    }
    if (empty($sessionsList)) {
        $sessionsList = [date('Y'), strval(date('Y') - 1)];
    }
    if (empty($sessionyear)) {
        $sessionyear = $activeSession ?: $sessionsList[0];
    }
    if (empty($slot)) {
        $slot = $slotsList[0] ?? 'School';
    }

    $sql = "SELECT id, sccode, sessionyear, slots as slot, shift, period, timestart, timeend, duration 
            FROM classschedule 
            WHERE sccode = ?";
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && strtolower($sessionyear) !== 'all') {
        $sql .= " AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL)";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($slot) && $slot !== 'All') {
        $sql .= " AND (slots = ? OR (slots = '' AND ? = 'School') OR (slots IS NULL AND ? = 'School'))";
        $params[] = $slot;
        $params[] = $slot;
        $params[] = $slot;
        $types .= "sss";
    }

    $sql .= " ORDER BY period ASC, id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $periods = [];
    while ($row = $res->fetch_assoc()) {
        $periods[] = [
            'id' => intval($row['id']),
            'period' => intval($row['period']),
            'slot' => $row['slot'] ?: $slot,
            'timestart' => $row['timestart'] ?: '',
            'timeend' => $row['timeend'] ?: '',
            'start' => substr($row['timestart'] ?: '08:00', 0, 5),
            'end' => substr($row['timeend'] ?: '08:45', 0, 5),
            'duration' => intval($row['duration'] ?: 45)
        ];
    }
    $stmt->close();

    // Default seed if completely empty for this institution
    if (empty($periods)) {
        $periods = [
            ['id' => 1, 'period' => 1, 'slot' => $slot, 'timestart' => '07:30:00', 'timeend' => '07:50:00', 'start' => '07:30', 'end' => '07:50', 'duration' => 20, 'name' => 'Assembly', 'type' => 'Assembly'],
            ['id' => 2, 'period' => 2, 'slot' => $slot, 'timestart' => '07:50:00', 'timeend' => '08:35:00', 'start' => '07:50', 'end' => '08:35', 'duration' => 45, 'name' => '1st Period', 'type' => 'Class'],
            ['id' => 3, 'period' => 3, 'slot' => $slot, 'timestart' => '08:35:00', 'timeend' => '09:20:00', 'start' => '08:35', 'end' => '09:20', 'duration' => 45, 'name' => '2nd Period', 'type' => 'Class'],
            ['id' => 4, 'period' => 4, 'slot' => $slot, 'timestart' => '09:20:00', 'timeend' => '10:05:00', 'start' => '09:20', 'end' => '10:05', 'duration' => 45, 'name' => '3rd Period', 'type' => 'Class'],
            ['id' => 5, 'period' => 5, 'slot' => $slot, 'timestart' => '10:05:00', 'timeend' => '10:35:00', 'start' => '10:05', 'end' => '10:35', 'duration' => 30, 'name' => 'Tiffin Break', 'type' => 'Break'],
            ['id' => 6, 'period' => 6, 'slot' => $slot, 'timestart' => '10:35:00', 'timeend' => '11:15:00', 'start' => '10:35', 'end' => '11:15', 'duration' => 40, 'name' => '4th Period', 'type' => 'Class'],
            ['id' => 7, 'period' => 7, 'slot' => $slot, 'timestart' => '11:15:00', 'timeend' => '11:55:00', 'start' => '11:15', 'end' => '11:55', 'duration' => 40, 'name' => '5th Period', 'type' => 'Class']
        ];
    }

    api_response('success', 'Class schedule period bell timings loaded.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'active_session' => $activeSession,
        'slot' => $slot,
        'slots' => $slotsList,
        'sessions' => $sessionsList,
        'periods' => $periods
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
