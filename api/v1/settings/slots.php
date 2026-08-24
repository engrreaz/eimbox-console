<?php
/**
 * EIMBox REST API — Slots / Shifts / Units CRUD Endpoint
 * Route: GET /api/v1/settings/slots.php?sccode={sccode}
 * Route: POST /api/v1/settings/slots.php (Create / Update / Delete / Set Default)
 * Route: DELETE /api/v1/settings/slots.php?id={id}&sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle DELETE (via DELETE method or POST with action=delete)
$action = $_GET['action'] ?? $input['action'] ?? '';

if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Slot ID is required for deletion.', null, 400);
    }

    $delStmt = $conn->prepare("DELETE FROM slots WHERE id = ? AND sccode = ?");
    $delStmt->bind_param('ii', $id, $sccode);
    $delStmt->execute();
    $affected = $delStmt->affected_rows;
    $delStmt->close();

    if ($affected > 0) {
        api_response('success', 'Slot deleted successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Slot not found or already deleted.', null, 404);
    }
}

// 3. Handle POST: Set Default Slot
if ($method === 'POST' && $action === 'set_default') {
    $slotname = trim($input['slotname'] ?? 'School');
    if (empty($slotname)) $slotname = 'School';

    $chk = $conn->prepare("SELECT id FROM slots WHERE sccode = ? AND slotname = ? LIMIT 1");
    $chk->bind_param('is', $sccode, $slotname);
    $chk->execute();
    $hasSlot = $chk->get_result()->fetch_assoc();
    $chk->close();

    if (!$hasSlot) {
        $ins = $conn->prepare("INSERT INTO slots (sccode, slotname, merit, decimal_mark, parents, trans_name_eng, trans_name_ben) VALUES (?, ?, 1, 0, 'DOSO', 1, 1)");
        $ins->bind_param('is', $sccode, $slotname);
        $ins->execute();
        $ins->close();
    }

    api_response('success', 'Default slot created successfully.', ['slotname' => $slotname]);
}

// 4. Handle POST / PUT: Create or Update Slot
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $slotname = trim($input['slotname'] ?? '');

    if (empty($slotname)) {
        api_response('error', 'Slot Name is required.', null, 400);
    }

    $merit = intval($input['merit'] ?? 1); // 0 = Total, 1 = GPA
    $decimalMark = intval($input['decimal_mark'] ?? 0); // 0 = Integer, 1 = Decimal, 2 = Round
    $parents = in_array(strtoupper(trim($input['parents'] ?? '')), ['FM', 'DOSO']) ? strtoupper(trim($input['parents'])) : 'DOSO';
    $cusReport = trim($input['cus_report'] ?? '');
    $dispEntryMark = intval($input['disp_entry_mark'] ?? 0);
    $transNameEng = intval($input['trans_name_eng'] ?? 1);
    $transNameBen = intval($input['trans_name_ben'] ?? 1);

    if ($id > 0) {
        // UPDATE existing slot
        $stmt = $conn->prepare("UPDATE slots SET slotname = ?, merit = ?, decimal_mark = ?, parents = ?, cus_report = ?, disp_entry_mark = ?, trans_name_eng = ?, trans_name_ben = ? WHERE id = ? AND sccode = ?");
        $stmt->bind_param('siissiiiii', $slotname, $merit, $decimalMark, $parents, $cusReport, $dispEntryMark, $transNameEng, $transNameBen, $id, $sccode);
        $stmt->execute();
        $stmt->close();

        api_response('success', 'Slot updated successfully.', [
            'id' => $id,
            'sccode' => $sccode,
            'slotname' => $slotname,
            'merit' => $merit,
            'decimal_mark' => $decimalMark,
            'parents' => $parents,
            'cus_report' => $cusReport,
            'disp_entry_mark' => $dispEntryMark,
            'trans_name_eng' => $transNameEng,
            'trans_name_ben' => $transNameBen
        ]);
    } else {
        // CREATE new slot
        $stmt = $conn->prepare("INSERT INTO slots (sccode, slotname, merit, decimal_mark, parents, cus_report, disp_entry_mark, trans_name_eng, trans_name_ben) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isiissiii', $sccode, $slotname, $merit, $decimalMark, $parents, $cusReport, $dispEntryMark, $transNameEng, $transNameBen);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        api_response('success', 'Slot created successfully.', [
            'id' => $newId,
            'sccode' => $sccode,
            'slotname' => $slotname,
            'merit' => $merit,
            'decimal_mark' => $decimalMark,
            'parents' => $parents,
            'cus_report' => $cusReport,
            'disp_entry_mark' => $dispEntryMark,
            'trans_name_eng' => $transNameEng,
            'trans_name_ben' => $transNameBen
        ]);
    }
}

// 5. Handle GET: List all Slots with student counts
$countMap = [];
$cStmt = $conn->prepare("SELECT slot, COUNT(*) as cnt FROM sessioninfo WHERE sccode = ? GROUP BY slot");
if ($cStmt) {
    $cStmt->bind_param('i', $sccode);
    $cStmt->execute();
    $cRes = $cStmt->get_result();
    while ($cRow = $cRes->fetch_assoc()) {
        $countMap[$cRow['slot']] = intval($cRow['cnt']);
    }
    $cStmt->close();
}

$stmt = $conn->prepare("SELECT * FROM slots WHERE sccode = ? ORDER BY id ASC");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$res = $stmt->get_result();

$slots = [];
while ($row = $res->fetch_assoc()) {
    $sName = $row['slotname'] ?? '';
    $slots[] = [
        'id' => intval($row['id']),
        'sccode' => intval($row['sccode']),
        'slotname' => $sName,
        'merit' => intval($row['merit'] ?? 1),
        'decimal_mark' => intval($row['decimal_mark'] ?? 0),
        'parents' => $row['parents'] ?? 'DOSO',
        'cus_report' => $row['cus_report'] ?? '',
        'disp_entry_mark' => intval($row['disp_entry_mark'] ?? 0),
        'trans_name_eng' => intval($row['trans_name_eng'] ?? 1),
        'trans_name_ben' => intval($row['trans_name_ben'] ?? 1),
        'students_count' => $countMap[$sName] ?? 0
    ];
}
$stmt->close();

api_response('success', 'Slots retrieved successfully.', [
    'sccode' => $sccode,
    'total_slots' => count($slots),
    'slots' => $slots
]);
