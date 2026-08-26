<?php
/**
 * EIMBox REST API — Events & Academic Calendar Management
 * Endpoint: /api/v1/settings/events-manage.php
 * Table: events (events.sql)
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

// 2. Handle DELETE: Delete an Event
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid Event ID is required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND (sccode = 0 OR sccode = ?)");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Event deleted successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Event not found or permission denied.', null, 404);
    }
}

// 3. Handle POST / PUT: Create or Update Event
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $eventTitle = trim($input['event_title'] ?? $input['title'] ?? '');
    $eventType = trim($input['event_type'] ?? $input['type'] ?? 'General');
    $description = trim($input['description'] ?? '');
    $startDate = trim($input['start_date'] ?? date('Y-m-d'));
    $endDate = trim($input['end_date'] ?? $startDate);
    $color = trim($input['color'] ?? '#3b82f6');
    $audience = trim($input['audience'] ?? 'all');
    $targetClasses = trim($input['target_classes'] ?? '');
    $targetSlots = trim($input['target_slots'] ?? '');
    $allDay = intval($input['all_day'] ?? 1);
    $status = trim($input['status'] ?? 'Scheduled');
    $createdBy = trim($input['created_by'] ?? $user['email'] ?? 'admin');
    $parentEventId = isset($input['parent_event_id']) ? intval($input['parent_event_id']) : null;

    if (empty($eventTitle)) {
        api_response('error', 'Event title is required.', null, 422);
    }

    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE events SET 
                    event_title = ?, event_type = ?, description = ?, 
                    start_date = ?, end_date = ?, color = ?, 
                    audience = ?, target_classes = ?, target_slots = ?, 
                    all_day = ?, status = ?, modifieddate = NOW()
                WHERE id = ? AND (sccode = 0 OR sccode = ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssiisii",
            $eventTitle, $eventType, $description,
            $startDate, $endDate, $color,
            $audience, $targetClasses, $targetSlots,
            $allDay, $status,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();

        api_response('success', 'Event updated successfully.', ['id' => $id]);
    } else {
        // INSERT
        $sql = "INSERT INTO events (
                    sccode, event_title, event_type, description, 
                    start_date, end_date, color, audience, 
                    target_classes, target_slots, all_day, status, 
                    created_by, parent_event_id, modifieddate
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssssissi",
            $sccode, $eventTitle, $eventType, $description,
            $startDate, $endDate, $color, $audience,
            $targetClasses, $targetSlots, $allDay, $status,
            $createdBy, $parentEventId
        );
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();

        api_response('success', 'Event created successfully.', ['id' => $insertId], 201);
    }
}

// 4. Handle GET: Query Events List
if ($method === 'GET') {
    $year = trim($_GET['year'] ?? '');
    $month = trim($_GET['month'] ?? '');
    $type = trim($_GET['type'] ?? '');
    $audience = trim($_GET['audience'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $sql = "SELECT id, sccode, event_title, event_type, description, start_date, end_date, 
                   color, audience, target_classes, target_slots, all_day, status, 
                   created_by, parent_event_id, modifieddate
            FROM events 
            WHERE (sccode = 0 OR sccode = ?)";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($year)) {
        $sql .= " AND (YEAR(start_date) = ? OR YEAR(end_date) = ?)";
        $params[] = intval($year);
        $params[] = intval($year);
        $types .= "ii";
    }

    if (!empty($month)) {
        $sql .= " AND (MONTH(start_date) = ? OR MONTH(end_date) = ?)";
        $params[] = intval($month);
        $params[] = intval($month);
        $types .= "ii";
    }

    if (!empty($type) && $type !== 'all') {
        $sql .= " AND event_type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if (!empty($audience) && $audience !== 'all') {
        $sql .= " AND (audience = ? OR audience = 'all')";
        $params[] = $audience;
        $types .= "s";
    }

    if (!empty($status) && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (event_title LIKE ? OR description LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm;
        $params[] = $sTerm;
        $types .= "ss";
    }

    $sql .= " ORDER BY start_date ASC, id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();

    api_response('success', 'Events retrieved successfully.', [
        'sccode' => $sccode,
        'total' => count($events),
        'events' => $events
    ]);
}
