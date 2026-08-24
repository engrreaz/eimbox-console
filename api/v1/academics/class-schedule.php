<?php
/**
 * EIMBox REST API — Academic Schedule & Period Bell Management
 * Endpoint: /api/v1/academics/class-schedule.php
 * Routes:
 *   GET /api/v1/academics/class-schedule.php?sccode={sccode}&sessionyear={year}&shift={shift}&slot={slot}
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

// 3. Handle POST: Bulk Save / Clone Shift Schedule
if ($method === 'POST' && ($action === 'bulk_save' || isset($input['periods']))) {
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $shift = trim($input['shift'] ?? 'Morning');
    $slot = trim($input['slot'] ?? $input['slots'] ?? 'School');
    $periodsList = is_array($input['periods']) ? $input['periods'] : [];

    // Optional: Clear existing for this shift & session if requested
    if (!empty($input['replace_all'])) {
        $del = $conn->prepare("DELETE FROM classschedule WHERE sccode = ? AND sessionyear = ? AND shift = ?");
        $del->bind_param("iss", $sccode, $sessionyear, $shift);
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

        $stmt->bind_param("isssissi", $sccode, $sessionyear, $slot, $shift, $periodNum, $timeStart, $timeEnd, $duration);
        $stmt->execute();
        $savedCount++;
    }
    $stmt->close();

    api_response('success', "Saved $savedCount period bell timings for $shift Shift ($sessionyear).", [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'shift' => $shift,
        'count' => $savedCount
    ]);
}

// 4. Handle POST / PUT: Save Single Period Record
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $shift = trim($input['shift'] ?? 'Morning');
    $slot = trim($input['slot'] ?? $input['slots'] ?? 'School');
    $period = intval($input['period'] ?? 1);
    $timeStart = trim($input['timestart'] ?? $input['start'] ?? '08:00');
    $timeEnd = trim($input['timeend'] ?? $input['end'] ?? '08:45');
    $duration = intval($input['duration'] ?? 45);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE classschedule SET sessionyear = ?, slots = ?, shift = ?, period = ?, timestart = ?, timeend = ?, duration = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("sssisssiii", $sessionyear, $slot, $shift, $period, $timeStart, $timeEnd, $duration, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Period schedule updated successfully.', ['id' => $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO classschedule (sccode, sessionyear, slots, shift, period, timestart, timeend, duration, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssissi", $sccode, $sessionyear, $slot, $shift, $period, $timeStart, $timeEnd, $duration);
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Period schedule created successfully.', ['id' => $insertId], 201);
    }
}

// 5. GET: Fetch Period Schedule for Session & Shift
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $shift = trim($_GET['shift'] ?? '');
    $slot = trim($_GET['slot'] ?? '');

    $sql = "SELECT id, sccode, sessionyear, slots as slot, shift, period, timestart, timeend, duration 
            FROM classschedule 
            WHERE sccode = ?";
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && strtolower($sessionyear) !== 'all') {
        $sql .= " AND (sessionyear = ? OR sessionyear = '')";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($shift) && $shift !== 'All') {
        $sql .= " AND shift = ?";
        $params[] = $shift;
        $types .= "s";
    }

    $sql .= " ORDER BY shift ASC, period ASC, id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $periods = [];
    while ($row = $res->fetch_assoc()) {
        $periods[] = [
            'id' => intval($row['id']),
            'period' => intval($row['period']),
            'shift' => $row['shift'] ?: 'Morning',
            'slot' => $row['slot'] ?: 'School',
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
            ['id' => 1, 'period' => 1, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '07:30:00', 'timeend' => '07:50:00', 'start' => '07:30', 'end' => '07:50', 'duration' => 20, 'name' => 'Assembly', 'type' => 'Assembly'],
            ['id' => 2, 'period' => 2, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '07:50:00', 'timeend' => '08:35:00', 'start' => '07:50', 'end' => '08:35', 'duration' => 45, 'name' => '1st Period', 'type' => 'Class'],
            ['id' => 3, 'period' => 3, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '08:35:00', 'timeend' => '09:20:00', 'start' => '08:35', 'end' => '09:20', 'duration' => 45, 'name' => '2nd Period', 'type' => 'Class'],
            ['id' => 4, 'period' => 4, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '09:20:00', 'timeend' => '10:05:00', 'start' => '09:20', 'end' => '10:05', 'duration' => 45, 'name' => '3rd Period', 'type' => 'Class'],
            ['id' => 5, 'period' => 5, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '10:05:00', 'timeend' => '10:35:00', 'start' => '10:05', 'end' => '10:35', 'duration' => 30, 'name' => 'Tiffin Break', 'type' => 'Break'],
            ['id' => 6, 'period' => 6, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '10:35:00', 'timeend' => '11:15:00', 'start' => '10:35', 'end' => '11:15', 'duration' => 40, 'name' => '4th Period', 'type' => 'Class'],
            ['id' => 7, 'period' => 7, 'shift' => 'Morning', 'slot' => 'School', 'timestart' => '11:15:00', 'timeend' => '11:55:00', 'start' => '11:15', 'end' => '11:55', 'duration' => 40, 'name' => '5th Period', 'type' => 'Class']
        ];
    }

    api_response('success', 'Class schedule period bell timings loaded.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'shift' => $shift ?: 'Morning',
        'periods' => $periods
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
