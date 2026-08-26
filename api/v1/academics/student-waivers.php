<?php
/**
 * EIMBox REST API — Student Waived List & Concessions Management
 * Endpoint: /api/v1/academics/student-waivers.php
 * Tables: sessioninfo (rate < 100, sector), students (name, address, contacts)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = api_authenticate_request();
$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $input['action'] ?? '';

// 1. Resolve School Code (Strict check: sccode must be > 0)
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);
if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle Action: Student Lookup / Search (for Assign/Edit Modal Live Preview)
if ($action === 'lookup' || $action === 'search_student') {
    $stid = trim($_GET['stid'] ?? $input['stid'] ?? '');
    $sessionyear = trim($_GET['sessionyear'] ?? $input['sessionyear'] ?? date('Y'));
    $search = trim($_GET['search'] ?? $input['search'] ?? '');

    if (empty($stid) && empty($search)) {
        api_response('error', 'Please provide a Student ID (stid) or search query.', null, 422);
    }

    if (!empty($stid)) {
        $sql = "SELECT si.id AS sessioninfo_id, si.stid, si.sccode, si.sessionyear, si.classname, si.sectionname, si.rollno,
                       si.slot, si.rate, si.sector, si.amount, si.real_tution,
                       s.stnameeng, s.stnameben, s.fname, s.mname, s.guarmobile, s.gender,
                       s.previll, s.prepo, s.preps, s.predist, s.pervill, s.perpo, s.perps, s.perdist,
                       s.photo, s.photo_id
                FROM sessioninfo si
                LEFT JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
                WHERE si.sccode = ? 
                  AND (si.sessionyear = ? OR ? = '' OR ? = 'all')
                  AND si.stid = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $sccode, $sessionyear, $sessionyear, $sessionyear, $stid);
        $stmt->execute();
        $res = $stmt->get_result();
        $student = $res->fetch_assoc();
        $stmt->close();

        if (!$student && !empty($sessionyear) && $sessionyear !== 'all') {
            // Fallback search across any session for this student (e.g. latest enrolled)
            $fallbackSql = "SELECT si.id AS sessioninfo_id, si.stid, si.sccode, si.sessionyear, si.classname, si.sectionname, si.rollno,
                           si.slot, si.rate, si.sector, si.amount, si.real_tution,
                           s.stnameeng, s.stnameben, s.fname, s.mname, s.guarmobile, s.gender,
                           s.previll, s.prepo, s.preps, s.predist, s.pervill, s.perpo, s.perps, s.perdist,
                           s.photo, s.photo_id
                    FROM sessioninfo si
                    LEFT JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
                    WHERE si.sccode = ? AND si.stid = ?
                    ORDER BY si.sessionyear DESC, si.id DESC
                    LIMIT 1";
            $fStmt = $conn->prepare($fallbackSql);
            $fStmt->bind_param("is", $sccode, $stid);
            $fStmt->execute();
            $fRes = $fStmt->get_result();
            $student = $fRes->fetch_assoc();
            $fStmt->close();
        }

        if ($student) {
            $rate = floatval($student['rate'] ?? 100);
            $student['rate'] = $rate;
            $student['waiver_percent'] = max(0, 100 - $rate);
            
            // Build formatted address
            $addrParts = array_filter([
                $student['previll'] ?? '',
                $student['prepo'] ?? '',
                $student['preps'] ?? '',
                $student['predist'] ?? ''
            ], function($v) { return trim($v) !== ''; });
            $student['present_address'] = !empty($addrParts) ? implode(', ', $addrParts) : 'N/A';
            
            $name = $student['stnameeng'] ?: ($student['stnameben'] ?: "Student {$student['rollno']}");
            $student['display_name'] = $name;

            api_response('success', 'Student found.', $student);
        } else {
            api_response('error', "No enrollment found for Student ID '$stid' in Session $sessionyear.", null, 404);
        }
    } else {
        // Broad search across session students
        $sql = "SELECT si.id AS sessioninfo_id, si.stid, si.sccode, si.sessionyear, si.classname, si.sectionname, si.rollno,
                       si.rate, si.sector,
                       s.stnameeng, s.stnameben, s.guarmobile, s.previll, s.predist
                FROM sessioninfo si
                LEFT JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
                WHERE si.sccode = ?
                  AND (si.sessionyear = ? OR ? = '' OR ? = 'all')
                  AND (s.stnameeng LIKE ? OR s.stnameben LIKE ? OR CAST(si.stid AS CHAR) LIKE ? OR CAST(si.rollno AS CHAR) LIKE ?)
                ORDER BY si.classname ASC, si.sectionname ASC, CAST(si.rollno AS UNSIGNED) ASC
                LIMIT 20";

        $sTerm = "%$search%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssss", $sccode, $sessionyear, $sessionyear, $sessionyear, $sTerm, $sTerm, $sTerm, $sTerm);
        $stmt->execute();
        $res = $stmt->get_result();
        $students = [];
        while ($r = $res->fetch_assoc()) {
            $r['waiver_percent'] = max(0, 100 - floatval($r['rate'] ?? 100));
            $students[] = $r;
        }
        $stmt->close();

        api_response('success', 'Students retrieved.', ['students' => $students]);
    }
}

// 3. Handle DELETE or action=revoke: Revoke Fee Waiver (Reset rate = 100, sector = '')
if ($method === 'DELETE' || ($method === 'POST' && $action === 'revoke')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    $stid = trim($_GET['stid'] ?? $input['stid'] ?? '');
    $sessionyear = trim($_GET['sessionyear'] ?? $input['sessionyear'] ?? date('Y'));

    if ($id <= 0 && empty($stid)) {
        api_response('error', 'Valid record ID or STID is required to revoke waiver.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = 100, sector = '', modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
    } else {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = 100, sector = '', modifieddate = NOW() WHERE stid = ? AND (sessionyear = ? OR ? = 'all') AND sccode = ?");
        $stmt->bind_param("sssi", $stid, $sessionyear, $sessionyear, $sccode);
    }
    
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Student fee waiver revoked successfully. Normal 100% rate restored.');
    } else {
        api_response('error', 'Record not found or waiver already revoked.', null, 404);
    }
}

// 4. Handle POST or PUT: Assign / Update Fee Waiver
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $stid = trim($input['stid'] ?? '');
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    
    // Calculate rate (either explicit 'rate' or derived from 'waiver_percent')
    if (isset($input['waiver_percent']) && !isset($input['rate'])) {
        $waiverPct = floatval($input['waiver_percent']);
        $rate = max(0, min(100, 100 - $waiverPct));
    } else {
        $rate = floatval($input['rate'] ?? 100);
        $rate = max(0, min(100, $rate));
    }

    $sector = trim($input['sector'] ?? $input['reason'] ?? 'Special Quota');
    if ($rate >= 100 && empty($sector)) {
        $sector = '';
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = ?, sector = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("dsii", $rate, $sector, $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        api_response('success', 'Fee waiver updated successfully.', [
            'id' => $id,
            'rate' => $rate,
            'waiver_percent' => (100 - $rate),
            'sector' => $sector
        ]);
    } elseif (!empty($stid)) {
        $stmt = $conn->prepare("UPDATE sessioninfo SET rate = ?, sector = ?, modifieddate = NOW() WHERE stid = ? AND (sessionyear = ? OR ? = 'all') AND sccode = ?");
        $stmt->bind_param("dssssi", $rate, $sector, $stid, $sessionyear, $sessionyear, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            api_response('success', 'Fee waiver assigned to student successfully.', [
                'stid' => $stid,
                'sessionyear' => $sessionyear,
                'rate' => $rate,
                'waiver_percent' => (100 - $rate),
                'sector' => $sector
            ]);
        } else {
            api_response('error', "No active enrollment found for Student ID '$stid' in Session $sessionyear.", null, 404);
        }
    } else {
        api_response('error', 'Provide Record ID or Student ID (stid).', null, 422);
    }
}

// 5. Handle GET: Query Waived Students List, KPIs, & Dropdown Metadata
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $sector = trim($_GET['sector'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $tier = trim($_GET['tier'] ?? $_GET['slab'] ?? '');

    // 5.1 Load Distinct Filter Options (Sessions, Classes, Sectors)
    $sessionsList = [];
    $sQ = $conn->prepare("SELECT DISTINCT sessionyear FROM sessioninfo WHERE sccode = ? AND sessionyear != '' ORDER BY sessionyear DESC");
    $sQ->bind_param("i", $sccode);
    $sQ->execute();
    $sRes = $sQ->get_result();
    while ($sRow = $sRes->fetch_assoc()) {
        $sessionsList[] = $sRow['sessionyear'];
    }
    $sQ->close();

    if (empty($sessionyear) && !empty($sessionsList)) {
        $sessionyear = $sessionsList[0];
    } elseif (empty($sessionyear)) {
        $sessionyear = date('Y');
    }

    $sectorsList = [];
    $secQ = $conn->prepare("SELECT DISTINCT sector FROM sessioninfo WHERE sccode = ? AND sector IS NOT NULL AND sector != '' AND rate < 100 ORDER BY sector ASC");
    $secQ->bind_param("i", $sccode);
    $secQ->execute();
    $secRes = $secQ->get_result();
    while ($secRow = $secRes->fetch_assoc()) {
        $sectorsList[] = $secRow['sector'];
    }
    $secQ->close();

    $classesList = [];
    $clsQ = $conn->prepare("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = ? AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
    $clsQ->bind_param("i", $sccode);
    $clsQ->execute();
    $clsRes = $clsQ->get_result();
    while ($clsRow = $clsRes->fetch_assoc()) {
        $classesList[] = $clsRow['classname'];
    }
    $clsQ->close();

    // 5.2 Build Query for Waived Students (rate < 100)
    $sql = "SELECT si.id AS sessioninfo_id, si.sccode, si.sessionyear, si.classname, si.sectionname, si.slot, si.rollno, si.stid,
                   si.rate, si.sector, si.amount, si.real_tution,
                   s.stnameeng, s.stnameben, s.fname, s.mname, s.guarmobile, s.gender,
                   s.previll, s.prepo, s.preps, s.predist, s.pervill, s.perpo, s.perps, s.perdist,
                   s.photo, s.photo_id
            FROM sessioninfo si
            LEFT JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
            WHERE si.sccode = ? AND si.rate < 100";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && strtolower($sessionyear) !== 'all') {
        $sql .= " AND si.sessionyear = ?";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($classname) && strtolower($classname) !== 'all') {
        $sql .= " AND si.classname = ?";
        $params[] = $classname;
        $types .= "s";
    }

    if (!empty($sectionname) && strtolower($sectionname) !== 'all') {
        $sql .= " AND si.sectionname = ?";
        $params[] = $sectionname;
        $types .= "s";
    }

    if (!empty($sector) && strtolower($sector) !== 'all') {
        $sql .= " AND si.sector = ?";
        $params[] = $sector;
        $types .= "s";
    }

    if (!empty($tier) && strtolower($tier) !== 'all') {
        if ($tier === '100' || $tier === 'full_free') {
            $sql .= " AND si.rate = 0";
        } elseif ($tier === '75') {
            $sql .= " AND (si.rate > 0 AND si.rate <= 25)";
        } elseif ($tier === '50') {
            $sql .= " AND (si.rate > 25 AND si.rate <= 50)";
        } elseif ($tier === '25') {
            $sql .= " AND (si.rate > 50 AND si.rate < 100)";
        }
    }

    if (!empty($search)) {
        $sql .= " AND (
            s.stnameeng LIKE ? OR 
            s.stnameben LIKE ? OR 
            s.fname LIKE ? OR 
            CAST(si.stid AS CHAR) LIKE ? OR 
            CAST(si.rollno AS CHAR) LIKE ? OR 
            s.guarmobile LIKE ? OR 
            si.sector LIKE ? OR 
            s.previll LIKE ? OR 
            s.predist LIKE ?
        )";
        $sTerm = "%$search%";
        for ($i = 0; $i < 9; $i++) {
            $params[] = $sTerm;
        }
        $types .= str_repeat("s", 9);
    }

    $sql .= " ORDER BY si.classname ASC, si.sectionname ASC, CAST(si.rollno AS UNSIGNED) ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        api_response('error', 'SQL query preparation failed: ' . $conn->error, null, 500);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $waivedList = [];
    $totalBeneficiaries = 0;
    $fullFreeCount = 0;
    $partialCount = 0;
    $totalWaiverPct = 0;

    while ($row = $res->fetch_assoc()) {
        $rate = floatval($row['rate'] ?? 100);
        $waiverPct = max(0, 100 - $rate);
        
        $row['rate'] = $rate;
        $row['waiver_percent'] = $waiverPct;
        $row['stname'] = $row['stnameeng'] ?: ($row['stnameben'] ?: "Student {$row['rollno']}");

        // Build formatted addresses
        $presentParts = array_filter([
            $row['previll'] ?? '',
            $row['prepo'] ?? '',
            $row['preps'] ?? '',
            $row['predist'] ?? ''
        ], function($v) { return trim($v) !== ''; });
        $row['present_address'] = !empty($presentParts) ? implode(', ', $presentParts) : '';

        $permanentParts = array_filter([
            $row['pervill'] ?? '',
            $row['perpo'] ?? '',
            $row['perps'] ?? '',
            $row['perdist'] ?? ''
        ], function($v) { return trim($v) !== ''; });
        $row['permanent_address'] = !empty($permanentParts) ? implode(', ', $permanentParts) : '';

        $row['address'] = $row['present_address'] ?: ($row['permanent_address'] ?: 'N/A');

        // Photo URL
        if (!empty($row['photo'])) {
            $row['photo_url'] = "students/{$row['photo']}";
        } elseif (!empty($row['photo_id'])) {
            $row['photo_url'] = "students/{$row['photo_id']}";
        } else {
            $row['photo_url'] = "https://eimbox.com/students/{$row['stid']}.jpg";
        }

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

error_log('Waived' . print_r($waivedList, true));

    api_response('success', 'Student fee waivers retrieved successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'filters' => [
            'sessions' => $sessionsList,
            'classes' => $classesList,
            'sectors' => $sectorsList
        ],
        'kpis' => [
            'total_beneficiaries' => $totalBeneficiaries,
            'full_free_count' => $fullFreeCount,
            'partial_concessions' => $partialCount,
            'average_waiver_pct' => $avgWaiverPct
        ],
        'waived_students' => $waivedList
    ]);
}
