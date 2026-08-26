<?php
/**
 * EIMBox REST API — Subject Marks Distribution & Pass Policy Engine
 * Endpoint: /api/v1/exams/marks-distribution.php
 * Table: subsetup (subsetup.sql)
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

// 2. Handle POST: Batch Update Marks Distribution Matrix
if ($method === 'POST' && ($action === 'batch_save' || isset($input['records']))) {
    $records = is_array($input['records']) ? $input['records'] : [];
    if (empty($records)) {
        api_response('error', 'No marks distribution records provided.', null, 422);
    }

    $savedCount = 0;
    $stmt = $conn->prepare("UPDATE subsetup SET 
                                fullmarks = ?, subj = ?, obj = ?, pra = ?, ca = ?, 
                                ctest = ?, mtest = ?, pass_algorithm = ?, fourth = ?,
                                combind_1 = ?, combind_2 = ?, modifieddate = NOW()
                            WHERE id = ? AND sccode = ?");

    foreach ($records as $r) {
        $id = intval($r['id'] ?? 0);
        if ($id <= 0) continue;

        $fullmarks = floatval($r['fullmarks'] ?? 100);
        $subj = floatval($r['subj'] ?? 0);
        $obj = floatval($r['obj'] ?? 0);
        $pra = floatval($r['pra'] ?? 0);
        $ca = floatval($r['ca'] ?? 0);
        $ctest = floatval($r['ctest'] ?? 0);
        $mtest = floatval($r['mtest'] ?? 0);
        $passAlgo = intval($r['pass_algorithm'] ?? 0);
        $fourth = intval($r['fourth'] ?? 0);
        $combind1 = intval($r['combind_1'] ?? 0);
        $combind2 = intval($r['combind_2'] ?? 0);

        $stmt->bind_param("ddddddiiiiiii",
            $fullmarks, $subj, $obj, $pra, $ca,
            $ctest, $mtest, $passAlgo, $fourth,
            $combind1, $combind2,
            $id, $sccode
        );
        $stmt->execute();
        $savedCount++;
    }
    $stmt->close();

    api_response('success', "Updated $savedCount subject marks distribution & pass policies.", [
        'sccode' => $sccode,
        'count' => $savedCount
    ]);
}

// 3. Handle POST: Update Single Subject Marks Breakdown
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid subsetup ID is required.', null, 422);
    }

    $fullmarks = floatval($input['fullmarks'] ?? 100);
    $subj = floatval($input['subj'] ?? 0);
    $obj = floatval($input['obj'] ?? 0);
    $pra = floatval($input['pra'] ?? 0);
    $ca = floatval($input['ca'] ?? 0);
    $ctest = floatval($input['ctest'] ?? 0);
    $mtest = floatval($input['mtest'] ?? 0);
    $passAlgo = intval($input['pass_algorithm'] ?? 0);
    $fourth = intval($input['fourth'] ?? 0);
    $combind1 = intval($input['combind_1'] ?? 0);
    $combind2 = intval($input['combind_2'] ?? 0);

    $stmt = $conn->prepare("UPDATE subsetup SET 
                                fullmarks = ?, subj = ?, obj = ?, pra = ?, ca = ?, 
                                ctest = ?, mtest = ?, pass_algorithm = ?, fourth = ?,
                                combind_1 = ?, combind_2 = ?, modifieddate = NOW()
                            WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ddddddiiiiiii",
        $fullmarks, $subj, $obj, $pra, $ca,
        $ctest, $mtest, $passAlgo, $fourth,
        $combind1, $combind2,
        $id, $sccode
    );
    $stmt->execute();
    $stmt->close();

    api_response('success', 'Marks distribution saved successfully.', ['id' => $id]);
}

// 4. Handle GET: Query Class Marks Distribution Matrix
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $slot = trim($_GET['slot'] ?? '');

    $sql = "SELECT ss.id, ss.sccode, ss.sessionyear, ss.classname, ss.sectionname, ss.slot, ss.slno,
                   ss.subject as subcode, ss.fullmarks, ss.subj, ss.obj, ss.pra, ss.ca, ss.camanual,
                   ss.ctest, ss.mtest, ss.pass_algorithm, ss.fourth, ss.combind_1, ss.combind_2,
                   s.subject as subject_name, s.subben as subject_ben, s.subshname as short_code,
                   t.tname as teacher_name
            FROM subsetup ss
            LEFT JOIN subjects s ON (s.subcode = ss.subject AND (s.sccode = 0 OR s.sccode = ss.sccode))
            LEFT JOIN teacher t ON (t.tid = ss.tid AND t.sccode = ss.sccode)
            WHERE ss.sccode = ?";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && $sessionyear !== 'all') {
        $sql .= " AND ss.sessionyear = ?";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($classname) && $classname !== 'all') {
        $sql .= " AND ss.classname = ?";
        $params[] = $classname;
        $types .= "s";
    }

    if (!empty($sectionname) && $sectionname !== 'all') {
        $sql .= " AND ss.sectionname = ?";
        $params[] = $sectionname;
        $types .= "s";
    }

    $sql .= " ORDER BY ss.classname ASC, ss.sectionname ASC, ss.slno ASC, ss.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $matrix = [];
    $totalFullmarks = 0;
    $balancedCount = 0;
    $mismatchCount = 0;

    while ($r = $result->fetch_assoc()) {
        $fm = floatval($r['fullmarks']);
        $sumComponents = floatval($r['subj']) + floatval($r['obj']) + floatval($r['pra']) + floatval($r['ca']);
        $isBalanced = (abs($fm - $sumComponents) < 0.01) || ($sumComponents == 0 && $fm > 0);

        $r['calculated_sum'] = $sumComponents;
        $r['is_balanced'] = $isBalanced;

        if ($isBalanced) {
            $balancedCount++;
        } else {
            $mismatchCount++;
        }
        $totalFullmarks += $fm;

        $matrix[] = $r;
    }
    $stmt->close();

    api_response('success', 'Marks distribution matrix retrieved.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'kpis' => [
            'total_subjects' => count($matrix),
            'total_fullmarks' => $totalFullmarks,
            'balanced_count' => $balancedCount,
            'mismatch_count' => $mismatchCount
        ],
        'distribution' => $matrix
    ]);
}
