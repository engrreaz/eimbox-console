<?php
/**
 * EIMBox REST API — Notice Manager & Digital Bulletin Board Studio
 * Endpoint: /api/v1/notices/notices-manage.php
 * Table: notice (notice.sql)
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

// 2. Handle DELETE: Delete a Notice
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid Notice ID is required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM notice WHERE id = ? AND (sccode = 0 OR sccode = ?)");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Notice deleted successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Notice not found or permission denied.', null, 404);
    }
}

// 3. Handle POST / PUT: Create or Update Notice
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $category = trim($input['category'] ?? '');
    $title = trim($input['title'] ?? '');
    $descrip = trim($input['descrip'] ?? $input['description'] ?? '');
    $icon = trim($input['icon'] ?? 'bell-fill');
    $color = trim($input['color'] ?? 'black');
    $expdate = trim($input['expdate'] ?? '') ?: null;
    $displayTo = trim($input['display_to'] ?? '');
    $teacher = intval($input['teacher'] ?? 1);
    $smc = intval($input['smc'] ?? 1);
    $guardian = intval($input['guardian'] ?? 1);
    $sms = intval($input['sms'] ?? 0);
    $pushnoti = intval($input['pushnoti'] ?? 1);
    $email = intval($input['email'] ?? 0);
    $entryby = trim($input['entryby'] ?? $user['email'] ?? 'admin');

    if (empty($title) || empty($descrip)) {
        api_response('error', 'Notice Title and Body Content are required.', null, 422);
    }

    if ($id > 0) {
        // UPDATE Notice
        $sql = "UPDATE notice SET 
                    category = ?, title = ?, descrip = ?, icon = ?, color = ?, 
                    expdate = ?, display_to = ?, teacher = ?, smc = ?, guardian = ?, 
                    sms = ?, pushnoti = ?, email = ?, modifieddate = NOW()
                WHERE id = ? AND (sccode = 0 OR sccode = ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssiiiiiiii",
            $category, $title, $descrip, $icon, $color,
            $expdate, $displayTo, $teacher, $smc, $guardian,
            $sms, $pushnoti, $email,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();

        api_response('success', 'Notice updated successfully.', ['id' => $id]);
    } else {
        // INSERT Notice
        $sql = "INSERT INTO notice (
                    sccode, category, title, descrip, icon, color, 
                    expdate, display_to, teacher, smc, guardian, 
                    sms, pushnoti, email, entryby, entrytime, modifieddate
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssiiiiiis",
            $sccode, $category, $title, $descrip, $icon, $color,
            $expdate, $displayTo, $teacher, $smc, $guardian,
            $sms, $pushnoti, $email, $entryby
        );
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();

        api_response('success', 'Notice published successfully to bulletin board.', ['id' => $insertId], 201);
    }
}

// 4. Handle GET: Query Notices List or Single Notice
if ($method === 'GET') {
    $id = intval($_GET['id'] ?? 0);
    $category = trim($_GET['category'] ?? '');
    $audience = trim($_GET['audience'] ?? '');
    $status = trim($_GET['status'] ?? 'all'); // 'all', 'active', 'expired'
    $search = trim($_GET['search'] ?? '');

    // Single Notice lookup
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT * FROM notice WHERE id = ? AND (sccode = 0 OR sccode = ?) LIMIT 1");
        $stmt->bind_param("ii", $id, $sccode);
        $stmt->execute();
        $notice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($notice) {
            api_response('success', 'Notice retrieved.', $notice);
        } else {
            api_response('error', 'Notice not found.', null, 404);
        }
    }

    $sql = "SELECT id, sccode, category, title, descrip, icon, color, expdate, display_to,
                   teacher, smc, guardian, sms, pushnoti, email, entryby, entrytime, modifieddate
            FROM notice 
            WHERE (sccode = 0 OR sccode = ?)";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($category) && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }

    if (!empty($audience) && $audience !== 'all') {
        if ($audience === 'teacher') {
            $sql .= " AND teacher = 1";
        } elseif ($audience === 'smc') {
            $sql .= " AND smc = 1";
        } elseif ($audience === 'guardian') {
            $sql .= " AND guardian = 1";
        }
    }

    if ($status === 'active') {
        $sql .= " AND (expdate >= CURDATE() OR expdate IS NULL OR expdate = '0000-00-00')";
    } elseif ($status === 'expired') {
        $sql .= " AND expdate < CURDATE() AND expdate IS NOT NULL AND expdate != '0000-00-00'";
    }

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR descrip LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm;
        $params[] = $sTerm;
        $types .= "ss";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $notices = [];
    $totalActive = 0;
    $totalHoliday = 0;
    $totalBroadcast = 0;
    $curDate = date('Y-m-d');

    while ($row = $result->fetch_assoc()) {
        $isExp = (!empty($row['expdate']) && $row['expdate'] !== '0000-00-00' && $row['expdate'] < $curDate);
        if (!$isExp) $totalActive++;
        if (strtolower($row['category'] ?? '') === 'holiday') $totalHoliday++;
        if ($row['sms'] == 1 || $row['pushnoti'] == 1 || $row['email'] == 1) $totalBroadcast++;

        $row['is_expired'] = $isExp;
        $notices[] = $row;
    }
    $stmt->close();

    api_response('success', 'Notices retrieved successfully.', [
        'sccode' => $sccode,
        'kpis' => [
            'total_notices' => count($notices),
            'active_on_board' => $totalActive,
            'holiday_notices' => $totalHoliday,
            'broadcast_alerts' => $totalBroadcast
        ],
        'notices' => $notices
    ]);
}
