<?php
/**
 * EIMBox REST API — Catchment Area & Address Translations Endpoint
 * Route: GET /api/v1/settings/catchment-area.php?sccode={sccode}&field={field}
 * Route: POST /api/v1/settings/catchment-area.php
 * Route: DELETE /api/v1/settings/catchment-area.php?id={id}&sccode={sccode}
 * 1:1 Parity with address-list-eng-ben.php & save-address-str.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller token if present
$user = authenticate_token($conn);

$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// ============================================================================
// 2. Handle POST / PUT: Save / Update Single or Batch Address Translations
// ============================================================================
if ($method === 'POST' || $method === 'PUT') {
    // 2.1 Batch Save
    if (isset($input['items']) && is_array($input['items'])) {
        $savedCount = 0;
        foreach ($input['items'] as $item) {
            $eng_str = trim($item['eng_str'] ?? $item['eng'] ?? '');
            $ben_str = trim($item['ben_str'] ?? $item['str'] ?? '');
            $field_type = trim($item['field_type'] ?? 'previll');
            $quota_pct = intval($item['quota_pct'] ?? 0);
            $remarks = trim($item['remarks'] ?? '');

            if (!empty($eng_str)) {
                $stmt = $conn->prepare("
                    INSERT INTO ben_address (sccode, eng_str, ben_str, field_type, quota_pct, remarks, modifieddate)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        ben_str = VALUES(ben_str),
                        field_type = VALUES(field_type),
                        quota_pct = VALUES(quota_pct),
                        remarks = VALUES(remarks),
                        modifieddate = NOW()
                ");
                $stmt->bind_param('isssis', $sccode, $eng_str, $ben_str, $field_type, $quota_pct, $remarks);
                if ($stmt->execute()) {
                    $savedCount++;
                }
                $stmt->close();
            }
        }

        api_response('success', "Batch saved {$savedCount} address translations.", [
            'saved_count' => $savedCount,
            'sccode' => $sccode
        ]);
    }

    // 2.2 Single Save / Update
    $id = intval($input['id'] ?? $input['getid'] ?? 0);
    $eng_str = trim($input['eng_str'] ?? $input['eng'] ?? '');
    $ben_str = trim($input['ben_str'] ?? $input['str'] ?? '');
    $field_type = trim($input['field_type'] ?? 'previll');
    $quota_pct = intval($input['quota_pct'] ?? 0);
    $remarks = trim($input['remarks'] ?? '');

    if (empty($eng_str)) {
        api_response('error', 'English address string (eng_str) is required.', null, 400);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE ben_address 
            SET ben_str = ?, field_type = ?, quota_pct = ?, remarks = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?
        ");
        $stmt->bind_param('ssisii', $ben_str, $field_type, $quota_pct, $remarks, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        $savedId = $id;
    } else {
        $stmt = $conn->prepare("
            INSERT INTO ben_address (sccode, eng_str, ben_str, field_type, quota_pct, remarks, modifieddate)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                ben_str = VALUES(ben_str),
                field_type = VALUES(field_type),
                quota_pct = VALUES(quota_pct),
                remarks = VALUES(remarks),
                modifieddate = NOW()
        ");
        $stmt->bind_param('isssis', $sccode, $eng_str, $ben_str, $field_type, $quota_pct, $remarks);
        $stmt->execute();
        $savedId = $stmt->insert_id ?: $id;
        $stmt->close();
    }

    api_response('success', 'Address translation saved successfully.', [
        'id' => $savedId,
        'sccode' => $sccode,
        'eng_str' => $eng_str,
        'ben_str' => $ben_str
    ]);
}

// ============================================================================
// 3. Handle DELETE: Remove Address Translation Record
// ============================================================================
if ($method === 'DELETE' || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid record ID is required for deletion.', null, 400);
    }

    $stmt = $conn->prepare("DELETE FROM ben_address WHERE id = ? AND sccode = ?");
    $stmt->bind_param('ii', $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    api_response('success', "Address record {$id} deleted successfully.", [
        'id' => $id,
        'affected_rows' => $affected
    ]);
}

// ============================================================================
// 4. Handle GET: Fetch Address Field List + ben_address Mappings
// ============================================================================
$allowed_fields = ['previll', 'prepo', 'preps', 'predist', 'pervill', 'perpo', 'perps', 'perdist'];
$field = isset($_GET['field']) && in_array($_GET['field'], $allowed_fields) ? $_GET['field'] : 'previll';

// 4.1 Fetch all translations from ben_address
$stmt_ben = $conn->prepare("SELECT id, sccode, eng_str, ben_str, field_type, quota_pct, remarks, modifieddate FROM ben_address WHERE sccode = ? ORDER BY id ASC");
$stmt_ben->bind_param('i', $sccode);
$stmt_ben->execute();
$res_ben = $stmt_ben->get_result();

$ben_map = [];
$ben_raw = [];
while ($row = $res_ben->fetch_assoc()) {
    $ben_raw[] = $row;
    $ben_map[strtolower(trim($row['eng_str']))] = $row;
}
$stmt_ben->close();

// 4.2 Fetch distinct student addresses from students table
// Safe column interpolation using whitelist
$field_clean = in_array($field, $allowed_fields) ? $field : 'previll';
$query_st = "SELECT `{$field_clean}` as eng_str, COUNT(*) as student_count 
             FROM students 
             WHERE sccode = ? AND `{$field_clean}` IS NOT NULL AND TRIM(`{$field_clean}`) != '' 
             GROUP BY `{$field_clean}` 
             ORDER BY `{$field_clean}` ASC";

$stmt_st = $conn->prepare($query_st);
$stmt_st->bind_param('i', $sccode);
$stmt_st->execute();
$res_st = $stmt_st->get_result();

$items = [];
$seen = [];

while ($st = $res_st->fetch_assoc()) {
    $eng = trim($st['eng_str']);
    if (empty($eng)) continue;
    $key = strtolower($eng);
    $seen[$key] = true;

    $ben = $ben_map[$key] ?? null;
    $items[] = [
        'id' => $ben ? intval($ben['id']) : 0,
        'sccode' => $sccode,
        'eng_str' => $eng,
        'ben_str' => $ben ? ($ben['ben_str'] ?? '') : '',
        'field_type' => $ben && !empty($ben['field_type']) ? $ben['field_type'] : $field,
        'quota_pct' => $ben ? intval($ben['quota_pct']) : 0,
        'remarks' => $ben ? ($ben['remarks'] ?? '') : '',
        'student_count' => intval($st['student_count']),
        'is_mapped' => $ben && !empty($ben['ben_str']),
        'is_custom' => false
    ];
}
$stmt_st->close();

// 4.3 Include custom entries from ben_address not found in active students roster
foreach ($ben_raw as $ben) {
    $eng = trim($ben['eng_str']);
    if (empty($eng)) continue;
    $key = strtolower($eng);
    if (!isset($seen[$key])) {
        $items[] = [
            'id' => intval($ben['id']),
            'sccode' => $sccode,
            'eng_str' => $eng,
            'ben_str' => $ben['ben_str'] ?? '',
            'field_type' => !empty($ben['field_type']) ? $ben['field_type'] : $field,
            'quota_pct' => intval($ben['quota_pct']),
            'remarks' => $ben['remarks'] ?? '',
            'student_count' => 0,
            'is_mapped' => !empty($ben['ben_str']),
            'is_custom' => true
        ];
    }
}

// 4.4 Compute Summary
$mapped_count = 0;
$total_students = 0;
foreach ($items as $it) {
    if ($it['is_mapped']) $mapped_count++;
    $total_students += $it['student_count'];
}

api_response('success', 'Catchment area and address list retrieved.', [
    'sccode' => $sccode,
    'field' => $field,
    'summary' => [
        'total_zones' => count($items),
        'mapped_zones' => $mapped_count,
        'pending_zones' => count($items) - $mapped_count,
        'total_students' => $total_students
    ],
    'count' => count($items),
    'items' => $items
]);