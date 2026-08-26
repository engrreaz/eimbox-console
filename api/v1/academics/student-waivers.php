<?php
/**
 * EIMBox REST API — Student Waived List & Concessions Management
 * Endpoint: /api/v1/academics/student-waivers.php
 * Table: sessioninfo (sessioninfo.sql)
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

// 2. Handle DELETE: Revoke Waiver (Reset rate = 100, sector = '')
if ($method === 'DELETE' || ($method === 'POST' && $action === 'revoke')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    $stid = intval($_GET['stid'] ?? $input['stid'] ?? 0);
    $sessionyear = trim($_GET['sessionyear'] ?? $input['sessionyear'] ?? date('Y'));

    if ($id <= 0 && $stid <= 0) {
        api_response('error', 'Valid record ID or STID is required to revoke waiver.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = 100, sector = '', modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
    } else {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = 100, sector = '', modifieddate = NOW() WHERE stid = ? AND sessionyear = ? AND sccode = ?");
        $stmt->bind_param("isi", $stid, $sessionyear, $sccode);
    }
    
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Student fee waiver revoked successfully (Reset to 100% full pay).');
    } else {
        api_response('error', 'Record not found or already revoked.', null, 404);
    }
}

// 3. Handle POST: Assign / Update Fee Waiver
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $stid = intval($input['stid'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $rate = floatval($input['rate'] ?? 100);
    $sector = trim($input['sector'] ?? $input['reason'] ?? 'Special Quota');

    if ($rate < 0 || $rate > 100) {
        api_response('error', 'Fee rate must be between 0% and 100%.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = ?, sector = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("dsii", $rate, $sector, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Fee waiver updated successfully.', ['id' => $id, 'rate' => $rate, 'sector' => $sector]);
    } elseif ($stid > 0) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = ?, sector = ?, modifieddate = NOW() WHERE stid = ? AND sessionyear = ? AND sccode = ?");
        $stmt->bind_param("dsisi", $rate, $sector, $stid, $sessionyear, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            api_response('success', 'Fee waiver assigned to student.', ['stid' => $stid, 'rate' => $rate, 'sector' => $sector]);
        } else {
            api_response('error', "No enrollment found for Student ID $stid in Session $sessionyear.", null, 404);
        }
    } else {
        api_response('error', 'Provide Record ID or Student ID (stid).', null, 422);
    }
}

// 4. Handle GET: Query Waived Students List & KPI Statistics
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $sector = trim($_GET['sector'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $tier = trim($_GET['tier'] ?? ''); // '100', '75', '50', '25'

    $sql = "SELECT si.id, si.sccode, si.sessionyear, si.classname, si.sectionname, si.slot, si.rollno, si.stid,
                   si.rate, si.sector,
                   s.nameeng, s.nameben, s.fname, s.guar_mobile, s.gender, s.previll
            FROM sessioninfo si
            JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
            WHERE si.sccode = ? AND si.rate < 100";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && $sessionyear !== 'all') {
        $sql .= " AND si.sessionyear = ?";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($classname) && $classname !== 'all') {
        $sql .= " AND si.classname = ?";
        $params[] = $classname;
        $types .= "s";
    }

    if (!empty($sectionname) && $sectionname !== 'all') {
        $sql .= " AND si.sectionname = ?";
        $params[] = $sectionname;
        $types .= "s";
    }

    if (!empty($sector) && $sector !== 'all') {
        $sql .= " AND si.sector = ?";
        $params[] = $sector;
        $types .= "s";
    }

    if (!empty($tier)) {
        if ($tier === '100' || $tier === 'full_free') {
            $sql .= " AND si.rate = 0";
        } elseif ($tier === '75') {
            $sql .= " AND si.rate = 25";
        } elseif ($tier === '50') {
            $sql .= " AND si.rate = 50";
        } elseif ($tier === '25') {
            $sql .= " AND si.rate = 75";
        }
    }

    if (!empty($search)) {
        $sql .= " AND (s.nameeng LIKE ? OR s.nameben LIKE ? OR s.fname LIKE ? OR CAST(si.stid AS CHAR) LIKE ? OR s.guar_mobile LIKE ? OR si.sector LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
        $types .= "ssssss";
    }

    $sql .= " ORDER BY si.classname ASC, si.sectionname ASC, si.rollno ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $waivedList = [];
    $totalBeneficiaries = 0;
    $fullFreeCount = 0;
    $partialCount = 0;
    $totalWaiverPct = 0;

    while ($row = $res->fetch_assoc()) {
        $rate = floatval($row['rate']);
        $waiverPct = 100 - $rate; // e.g. rate = 50 means 50% waiver
        $row['waiver_percent'] = $waiverPct;
        $row['photo_url'] = "https://eimbox.com/students/{$row['stid']}.jpg";

        $totalBeneficiaries++;
        if ($rate == 0) {
            $fullFreeCount++;
        } else {
            $partialCount++;
        }
        $totalWaiverPct += $waiverPct;

        $waivedList[] = $row;
    }
    $stmt->close();

    $avgWaiverPct = ($totalBeneficiaries > 0) ? round($totalWaiverPct / $totalBeneficiaries, 1) : 0;

    api_response('success', 'Student fee waivers retrieved successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'kpis' => [
            'total_beneficiaries' => $totalBeneficiaries,
            'full_free_count' => $fullFreeCount,
            'partial_concessions' => $partialCount,
            'average_waiver_pct' => $avgWaiverPct
        ],
        'waived_students' => $waivedList
    ]);
}
