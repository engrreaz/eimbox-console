<?php
/**
 * EIMBox REST API - GPA Grading Scale Policy Management
 * Endpoint: /api/v1/exams/gpa-manage.php
 * 
 * Rules:
 * - Default global grade scale: sccode = 0
 * - Custom institution grade scale: sccode = $sccode and slot = $slot
 * - If institution has no custom entries for a slot, returns sccode = 0 rows
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

// 1. GET: Retrieve GPA Scale
if ($method === 'GET') {
    $slot = trim($_GET['slot'] ?? 'School');
    $includeDefault = isset($_GET['include_default']) ? (int)$_GET['include_default'] : 1;

    // First check if custom GPA scale exists for active school and slot
    $stmt = $conn->prepare("
        SELECT id, sccode, slot, minvalues, maxvalues, gp, gl, remark, colorcode, modifieddate 
        FROM gpa 
        WHERE sccode = ? AND (slot = ? OR slot IS NULL OR slot = '')
        ORDER BY minvalues DESC
    ");
    $stmt->bind_param("is", $activeSccode, $slot);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$r['id'],
            'sccode' => (int)$r['sccode'],
            'slot' => $r['slot'] ?: 'School',
            'minvalues' => (float)$r['minvalues'],
            'maxvalues' => (float)$r['maxvalues'],
            'gp' => (float)$r['gp'],
            'gl' => (string)$r['gl'],
            'remark' => (string)$r['remark'],
            'colorcode' => (string)$r['colorcode'],
            'modifieddate' => $r['modifieddate']
        ];
    }
    $stmt->close();

    $isCustom = count($rows) > 0;

    // If no custom rows, fall back to global default (sccode = 0)
    if (!$isCustom && $includeDefault) {
        $defStmt = $conn->prepare("
            SELECT id, sccode, slot, minvalues, maxvalues, gp, gl, remark, colorcode, modifieddate 
            FROM gpa 
            WHERE sccode = 0 
            ORDER BY minvalues DESC
        ");
        $defStmt->execute();
        $defRes = $defStmt->get_result();
        while ($r = $defRes->fetch_assoc()) {
            $rows[] = [
                'id' => (int)$r['id'],
                'sccode' => 0,
                'slot' => $slot,
                'minvalues' => (float)$r['minvalues'],
                'maxvalues' => (float)$r['maxvalues'],
                'gp' => (float)$r['gp'],
                'gl' => (string)$r['gl'],
                'remark' => (string)$r['remark'],
                'colorcode' => (string)$r['colorcode'],
                'modifieddate' => $r['modifieddate']
            ];
        }
        $defStmt->close();
    }

    // Also get all distinct slots configured
    $slotStmt = $conn->prepare("SELECT DISTINCT slotname AS slot FROM slots WHERE sccode = ? OR sccode = 0 ORDER BY slotname ASC");
    $slotStmt->bind_param("i", $activeSccode);
    $slotStmt->execute();
    $slotRes = $slotStmt->get_result();
    $slots = [];
    while ($sr = $slotRes->fetch_assoc()) {
        if (!empty($sr['slot'])) {
            $slots[] = $sr['slot'];
        }
    }
    $slotStmt->close();

    if (empty($slots)) {
        $slots = ['School', 'College', 'Morning', 'Day'];
    }

    api_send_response(200, true, "GPA scale loaded.", [
        'sccode' => $activeSccode,
        'slot' => $slot,
        'is_custom' => $isCustom,
        'scale' => $rows,
        'slots' => $slots
    ]);
}

// 2. POST: Create or Update GPA Scale Entry
if ($method === 'POST') {
    $input = get_api_input();
    $id = (int)($input['id'] ?? 0);
    $targetSccode = (int)($input['sccode'] ?? $activeSccode);
    if ($targetSccode <= 0) $targetSccode = $activeSccode;
    $slot = trim($input['slot'] ?? 'School');
    $minvalues = (float)($input['minvalues'] ?? 0);
    $maxvalues = (float)($input['maxvalues'] ?? 100);
    $gp = (float)($input['gp'] ?? 0);
    $gl = trim($input['gl'] ?? 'F');
    $remark = trim($input['remark'] ?? '');
    $colorcode = ltrim(trim($input['colorcode'] ?? '00b33c'), '#');

    if ($minvalues < 0 || $maxvalues > 100 || $minvalues > $maxvalues) {
        api_send_response(422, false, "Invalid marks range ($minvalues - $maxvalues).");
    }

    if (empty($gl)) {
        api_send_response(422, false, "Letter grade (gl) is required.");
    }

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE gpa 
            SET sccode = ?, slot = ?, minvalues = ?, maxvalues = ?, gp = ?, gl = ?, remark = ?, colorcode = ?, modifieddate = NOW()
            WHERE id = ? AND (sccode = ? OR sccode = 0)
        ");
        $stmt->bind_param("isdddssiii", $targetSccode, $slot, $minvalues, $maxvalues, $gp, $gl, $remark, $colorcode, $id, $targetSccode);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("
            INSERT INTO gpa (sccode, slot, minvalues, maxvalues, gp, gl, remark, colorcode, modifieddate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isdddsss", $targetSccode, $slot, $minvalues, $maxvalues, $gp, $gl, $remark, $colorcode);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
    }

    api_send_response(200, true, "GPA grade interval saved successfully.", [
        'id' => $id,
        'sccode' => $targetSccode,
        'slot' => $slot,
        'minvalues' => $minvalues,
        'maxvalues' => $maxvalues,
        'gp' => $gp,
        'gl' => $gl,
        'remark' => $remark,
        'colorcode' => $colorcode
    ]);
}

// 3. DELETE: Remove Custom GPA Scale Entry or Reset to Default
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    $action = trim($_GET['action'] ?? '');
    $slot = trim($_GET['slot'] ?? 'School');

    if ($action === 'reset_to_default') {
        // Remove all custom overrides for this school & slot
        $stmt = $conn->prepare("DELETE FROM gpa WHERE sccode = ? AND slot = ?");
        $stmt->bind_param("is", $activeSccode, $slot);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        api_send_response(200, true, "Custom GPA scale reset to default NCTB standard.", ['deleted_count' => $deleted]);
    }

    if ($id <= 0) {
        api_send_response(422, false, "Valid GPA entry ID is required.");
    }

    // Safety: Do not allow deletion of sccode = 0 system master template entries
    $stmt = $conn->prepare("DELETE FROM gpa WHERE id = ? AND sccode = ? AND sccode != 0");
    $stmt->bind_param("ii", $id, $activeSccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_send_response(200, true, "GPA entry deleted successfully.");
    } else {
        api_send_response(403, false, "Cannot delete global system scale entry or entry not found.");
    }
}
