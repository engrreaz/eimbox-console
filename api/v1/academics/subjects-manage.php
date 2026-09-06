<?php
/**
 * EIMBox REST API - Subject Master & Class-wise Setup Management
 * Endpoint: /api/v1/academics/subjects-manage.php
 * Routes:
 *   GET /api/v1/academics/subjects-manage.php?sccode={sccode}&sessionyear={year}&classname={class}&sectionname={sec}
 *   POST /api/v1/academics/subjects-manage.php (Create / Update / Clone / Master Save)
 *   DELETE /api/v1/academics/subjects-manage.php?id={id}&sccode={sccode}
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
    $type = trim($_GET['type'] ?? $input['type'] ?? 'setup');

    if ($id <= 0) {
        api_response('error', 'Valid record ID is required for deletion.', null, 422);
    }

    if ($type === 'master') {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ? AND sccode = ? AND subcode BETWEEN 401 AND 800");
        $stmt->bind_param("ii", $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            api_response('success', 'Custom master subject removed successfully.', ['deleted_id' => $id]);
        } else {
            api_response('error', 'Subject not found or cannot delete NCTB standard subject (Code outside 401-800).', null, 403);
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM subsetup WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            api_response('success', 'Subject setup removed successfully.', ['deleted_id' => $id]);
        } else {
            api_response('error', 'Setup record not found.', null, 404);
        }
    }
}

// 3. Handle POST: Clone Session Subject Setups
if ($method === 'POST' && ($action === 'clone_session' || $action === 'copy_session')) {
    $fromSession = trim($input['from_session'] ?? $input['from_year'] ?? '');
    $toSession = trim($input['to_session'] ?? $input['to_year'] ?? '');

    if (empty($fromSession) || empty($toSession)) {
        api_response('error', 'Both source and target session years are required.', null, 422);
    }

    $srcStmt = $conn->prepare("SELECT slno, slot, classname, sectionname, subject, fullmarks, ctest, mtest, subj, obj, pra, ca, camanual, ctmt, pass_algorithm, fourth, combind_1, combind_2, combind_3, combind_4 
                              FROM subsetup WHERE sccode = ? AND sessionyear = ?");
    $srcStmt->bind_param("is", $sccode, $fromSession);
    $srcStmt->execute();
    $srcRes = $srcStmt->get_result();

    $copiedCount = 0;
    $insStmt = $conn->prepare("INSERT INTO subsetup (
        sccode, sessionyear, slno, slot, classname, sectionname, subject, fullmarks,
        ctest, mtest, subj, obj, pra, ca, camanual, ctmt, pass_algorithm, fourth, combind_1, combind_2, combind_3, combind_4, modifieddate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE 
        fullmarks = VALUES(fullmarks), subj = VALUES(subj), obj = VALUES(obj), pra = VALUES(pra), ca = VALUES(ca), modifieddate = NOW()");

    while ($r = $srcRes->fetch_assoc()) {
        $insStmt->bind_param("isisssiidddddddiiiiiii",
            $sccode, $toSession, $r['slno'], $r['slot'], $r['classname'], $r['sectionname'], $r['subject'], $r['fullmarks'],
            $r['ctest'], $r['mtest'], $r['subj'], $r['obj'], $r['pra'], $r['ca'], $r['camanual'], $r['ctmt'], $r['pass_algorithm'], $r['fourth'],
            $r['combind_1'], $r['combind_2'], $r['combind_3'], $r['combind_4']
        );
        $insStmt->execute();
        $copiedCount++;
    }
    $srcStmt->close();
    $insStmt->close();

    api_response('success', "Successfully cloned $copiedCount subject setups from $fromSession to $toSession.", [
        'from_session' => $fromSession,
        'to_session' => $toSession,
        'copied_count' => $copiedCount
    ]);
}

// 4. Handle POST: Save / Create Master Subject
if ($method === 'POST' && ($action === 'save_master' || $action === 'save_master_subject')) {
    $id = intval($input['id'] ?? 0);
    $subcode = intval($input['subcode'] ?? 0);
    $subjectEn = trim($input['subject'] ?? $input['subname_en'] ?? '');
    $subjectBn = trim($input['subben'] ?? $input['subname_bn'] ?? '');
    $subshname = trim($input['subshname'] ?? $input['shortname'] ?? '');
    $fourth = intval($input['fourth'] ?? 0);
    $supClass = trim($input['sup_class'] ?? 'All');
    $category = trim($input['sccategory'] ?? 'School');

    if (empty($subjectEn) || $subcode <= 0) {
        api_response('error', 'Subject code and Subject name in English are required.', null, 422);
    }

    if ($subcode < 401 || $subcode > 800) {
        api_response('error', "Institutional custom subject code must strictly be between 401 and 800. Provided code ($subcode) is outside allowed range.", null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET subcode = ?, subject = ?, subben = ?, subshname = ?, fourth = ?, sup_class = ?, sccategory = ?, modifieddate = NOW() WHERE id = ? AND sccode = ? AND subcode BETWEEN 401 AND 800");
        $stmt->bind_param("isssissii", $subcode, $subjectEn, $subjectBn, $subshname, $fourth, $supClass, $category, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        api_response('success', "Master subject updated successfully.");
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (sccode, sccategory, subcode, subject, subben, subshname, fourth, sup_class, modifieddate)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isisssis", $sccode, $category, $subcode, $subjectEn, $subjectBn, $subshname, $fourth, $supClass);
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', "Master subject created successfully.", ['id' => $insertId], 201);
    }
}

// 5. Handle POST / PUT: Upsert Subject Setup Record
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $sessionyear = trim($input['sessionyear'] ?? $input['session'] ?? date('Y'));
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? 'All');
    $subcode = intval($input['subcode'] ?? $input['subject'] ?? 0);
    $fullmarks = intval($input['fullmarks'] ?? 100);
    $subj = floatval($input['subj'] ?? 0);
    $obj = floatval($input['obj'] ?? 0);
    $pra = floatval($input['pra'] ?? 0);
    $ca = floatval($input['ca'] ?? 0);
    $ctest = floatval($input['ctest'] ?? 0);
    $mtest = floatval($input['mtest'] ?? 0);
    $camanual = intval($input['camanual'] ?? 0);
    $ctmt = floatval($input['ctmt'] ?? 0);
    $passAlgorithm = intval($input['pass_algorithm'] ?? 0);
    $fourth = intval($input['fourth'] ?? 0);
    $combind1 = intval($input['combind_1'] ?? $input['comb_subcode'] ?? 0);
    $combind2 = intval($input['combind_2'] ?? 0);
    $combind3 = intval($input['combind_3'] ?? 0);
    $combind4 = intval($input['combind_4'] ?? 0);
    $slno = intval($input['slno'] ?? 1);
    $slot = trim($input['slot'] ?? 'School');
    $tid = intval($input['tid'] ?? 0);

    if (empty($classname) || $subcode <= 0 || empty($sessionyear)) {
        api_response('error', 'Class name, subject code, and session year are required.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE subsetup SET 
            fullmarks = ?, subj = ?, obj = ?, pra = ?, ca = ?, ctest = ?, mtest = ?,
            camanual = ?, ctmt = ?, pass_algorithm = ?, fourth = ?, combind_1 = ?, combind_2 = ?,
            combind_3 = ?, combind_4 = ?, slno = ?, slot = ?, tid = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param("iddddddiiiiiiisiisii", 
            $fullmarks, $subj, $obj, $pra, $ca, $ctest, $mtest,
            $camanual, $ctmt, $passAlgorithm, $fourth, $combind1, $combind2,
            $combind3, $combind4, $slno, $slot, $tid,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();

        api_response('success', "Subject setup updated successfully.", [
            'id' => $id,
            'sccode' => $sccode,
            'classname' => $classname,
            'sectionname' => $sectionname,
            'subcode' => $subcode
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO subsetup (
            sccode, sessionyear, slot, classname, sectionname, subject, fullmarks,
            ctest, mtest, subj, obj, pra, ca, camanual, ctmt, pass_algorithm, fourth,
            combind_1, combind_2, combind_3, combind_4, slno, tid, modifieddate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            fullmarks = VALUES(fullmarks), subj = VALUES(subj), obj = VALUES(obj), pra = VALUES(pra),
            ca = VALUES(ca), ctest = VALUES(ctest), mtest = VALUES(mtest), pass_algorithm = VALUES(pass_algorithm),
            fourth = VALUES(fourth), combind_1 = VALUES(combind_1), combind_2 = VALUES(combind_2),
            slno = VALUES(slno), tid = VALUES(tid), modifieddate = NOW()");
        
        $stmt->bind_param("issssiiidddddddiiiiiiiii",
            $sccode, $sessionyear, $slot, $classname, $sectionname, $subcode, $fullmarks,
            $ctest, $mtest, $subj, $obj, $pra, $ca, $camanual, $ctmt, $passAlgorithm, $fourth,
            $combind1, $combind2, $combind3, $combind4, $slno, $tid
        );
        $stmt->execute();
        $insertId = $conn->insert_id ?: $id;
        $stmt->close();

        api_response('success', "Subject assigned to class successfully.", [
            'id' => $insertId,
            'sccode' => $sccode,
            'classname' => $classname,
            'sectionname' => $sectionname,
            'subcode' => $subcode
        ], 201);
    }
}

// 6. GET: Fetch Subjects Master & Class Setup
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $slot = trim($_GET['slot'] ?? '');

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

    // Fetch Sessions from sessionyear table with active=1 prioritization
    $sessionsList = [];
    $activeSession = '';
    $sessStmt = $conn->prepare("SELECT syear, active FROM sessionyear WHERE sccode = ? OR sccode = 0 ORDER BY active DESC, syear DESC");
    if ($sessStmt) {
        $sessStmt->bind_param("i", $sccode);
        $sessStmt->execute();
        $sessRes = $sessStmt->get_result();
        while ($sRow = $sessRes->fetch_assoc()) {
            $yStr = strval($sRow['syear']);
            if (!in_array($yStr, $sessionsList)) {
                $sessionsList[] = $yStr;
            }
            if (intval($sRow['active']) === 1 && empty($activeSession)) {
                $activeSession = $yStr;
            }
        }
        $sessStmt->close();
    }
    if (empty($sessionsList)) {
        $sessionsList = [date('Y'), strval(date('Y') - 1)];
    }
    if (empty($sessionyear)) {
        $sessionyear = $activeSession ?: $sessionsList[0];
    }

    // Fetch School Category from scinfo
    $sccategory = 'School';
    $scStmt = $conn->prepare("SELECT sccategory FROM scinfo WHERE sccode = ? LIMIT 1");
    if ($scStmt) {
        $scStmt->bind_param("i", $sccode);
        $scStmt->execute();
        $scRes = $scStmt->get_result();
        if ($scRow = $scRes->fetch_assoc()) {
            $sccategory = trim($scRow['sccategory'] ?? 'School');
        }
        $scStmt->close();
    }

    // Fetch Master Subjects (deduplicated strictly so each subcode appears once)
    $masterSubjects = [];
    $seenCodes = [];
    $mStmt = $conn->prepare("SELECT id, sccode, sccategory, subcode, subject, subben, subshname, fourth, sup_class 
                             FROM subjects 
                             WHERE (sccode = 0 OR sccode = ?) 
                               AND (sccategory = ? OR sccategory = '' OR sccategory IS NULL OR sccode = ?)
                             ORDER BY subcode ASC, (sccode = ?) DESC");
    if ($mStmt) {
        $mStmt->bind_param("isii", $sccode, $sccategory, $sccode, $sccode);
        $mStmt->execute();
        $mRes = $mStmt->get_result();
        while ($row = $mRes->fetch_assoc()) {
            $code = intval($row['subcode']);
            if (isset($seenCodes[$code])) continue;
            $seenCodes[$code] = true;

            $masterSubjects[] = [
                'id' => intval($row['id']),
                'sccode' => intval($row['sccode']),
                'subcode' => $code,
                'subject' => $row['subject'],
                'subname_en' => $row['subject'],
                'subben' => $row['subben'] ?: '',
                'subname_bn' => $row['subben'] ?: '',
                'subshname' => $row['subshname'] ?: '',
                'shortname' => $row['subshname'] ?: '',
                'fourth' => intval($row['fourth']),
                'sup_class' => $row['sup_class'] ?: 'All',
                'is_custom' => intval($row['sccode']) > 0
            ];
        }
        $mStmt->close();
    }

    // Fetch Classes & Sections for this session from areas table
    $classes = [];
    $sectionsMap = [];
    $aStmt = $conn->prepare("SELECT areaname, subarea FROM areas WHERE (sccode = ? OR sccode = 0) AND sessionyear = ? GROUP BY areaname, subarea ORDER BY MIN(idno) ASC, areaname ASC, subarea ASC");
    if ($aStmt) {
        $aStmt->bind_param("is", $sccode, $sessionyear);
        $aStmt->execute();
        $aRes = $aStmt->get_result();
        while ($aRow = $aRes->fetch_assoc()) {
            $cName = $aRow['areaname'];
            $sName = $aRow['subarea'];
            if (!in_array($cName, $classes)) $classes[] = $cName;
            if (!isset($sectionsMap[$cName])) $sectionsMap[$cName] = [];
            if (!in_array($sName, $sectionsMap[$cName])) $sectionsMap[$cName][] = $sName;
        }
        $aStmt->close();
    }
    if (empty($classes)) {
        $classes = ['Six', 'Seven', 'Eight', 'Nine', 'Ten'];
    }

    // Default to first class if not specified
    if (empty($classname) && !empty($classes)) {
        $classname = $classes[0];
    }

    // Fetch Class-specific subsetup records
    $setupList = [];
    if (!empty($classname)) {
        $sql = "SELECT ss.id, ss.slno, ss.sccode, ss.sessionyear, ss.slot, ss.classname, ss.sectionname,
                       ss.subject as subcode, ss.fullmarks, ss.ctest, ss.mtest, ss.subj, ss.obj, ss.pra, ss.ca,
                       ss.camanual, ss.ctmt, ss.pass_algorithm, ss.fourth, ss.combind_1, ss.combind_2,
                       ss.combind_3, ss.combind_4, ss.tid,
                       COALESCE(
                         (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL OR s.sccode = ss.sccode) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
                         (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
                         ''
                       ) as subject_name,
                       COALESCE(
                         (SELECT s.subben FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL OR s.sccode = ss.sccode) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
                         ''
                       ) as subject_ben,
                       COALESCE(
                         (SELECT s.subshname FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL OR s.sccode = ss.sccode) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
                         ''
                       ) as shortname,
                       t.tname as teacher_name,
                       t.position as teacher_pos
                FROM subsetup ss
                LEFT JOIN teacher t ON (t.sccode = ss.sccode OR t.sccode = 0) AND (t.tid = ss.tid OR t.id = ss.tid)
                WHERE (ss.sccode = ? OR ss.sccode = 0) AND (ss.sessionyear = ? OR ss.sessionyear = '' OR ss.sessionyear IS NULL) AND ss.classname = ?";
        
        $params = [$sccategory, $sccategory, $sccategory, $sccode, $sessionyear, $classname];
        $types = "sssiss";

        if (!empty($sectionname) && $sectionname !== 'All') {
            $sql .= " AND (ss.sectionname = ? OR ss.sectionname = 'All' OR ss.sectionname = '' OR ss.sectionname IS NULL)";
            $params[] = $sectionname;
            $types .= "s";
        }

        if (!empty($slot) && $slot !== 'All') {
            $sql .= " AND (ss.slot = ? OR ss.slot = 'School' OR ss.slot = '' OR ss.slot IS NULL)";
            $params[] = $slot;
            $types .= "s";
        }

        $sql .= " ORDER BY ss.slno ASC, ss.subject ASC";

        $sStmt = $conn->prepare($sql);
        if ($sStmt) {
            $sStmt->bind_param($types, ...$params);
            $sStmt->execute();
            $sRes = $sStmt->get_result();
            while ($sRow = $sRes->fetch_assoc()) {
                $setupList[] = [
                    'id' => intval($sRow['id']),
                    'slno' => intval($sRow['slno']),
                    'subcode' => intval($sRow['subcode']),
                    'subject_name' => $sRow['subject_name'],
                    'subject_ben' => $sRow['subject_ben'],
                    'shortname' => $sRow['shortname'],
                    'classname' => $sRow['classname'],
                    'sectionname' => $sRow['sectionname'] ?: 'All',
                    'slot' => $sRow['slot'] ?: 'School',
                    'fullmarks' => intval($rFull = $sRow['fullmarks'] ?? 100),
                    'subj' => floatval($sRow['subj'] ?? 0),
                    'obj' => floatval($sRow['obj'] ?? 0),
                    'pra' => floatval($sRow['pra'] ?? 0),
                    'ca' => floatval($sRow['ca'] ?? 0),
                    'ctest' => floatval($sRow['ctest'] ?? 0),
                    'mtest' => floatval($sRow['mtest'] ?? 0),
                    'pass_algorithm' => intval($sRow['pass_algorithm'] ?? 0),
                    'fourth' => intval($sRow['fourth'] ?? 0),
                    'combind_1' => intval($sRow['combind_1'] ?? 0),
                    'combind_2' => intval($sRow['combind_2'] ?? 0),
                    'combind_3' => intval($sRow['combind_3'] ?? 0),
                    'combind_4' => intval($sRow['combind_4'] ?? 0),
                    'tid' => intval($sRow['tid'] ?? 0),
                    'teacher_name' => $sRow['teacher_name'] ?: ''
                ];
            }
            $sStmt->close();
        }
    }

    // Fetch Teachers for assignment
    $teachers = [];
    $tStmt = $conn->prepare("SELECT id, tid, tname, position, mobile FROM teacher WHERE sccode = ? AND (status = 'Active' OR status = '1' OR status = '' OR status IS NULL) ORDER BY tname ASC");
    if ($tStmt) {
        $tStmt->bind_param("i", $sccode);
        $tStmt->execute();
        $tRes = $tStmt->get_result();
        while ($tRow = $tRes->fetch_assoc()) {
            $teachers[] = [
                'id' => intval($tRow['id']),
                'tid' => (string)$tRow['tid'],
                'tname' => $tRow['tname'],
                'designation' => $tRow['position'] ?: ''
            ];
        }
        $tStmt->close();
    }

    api_response('success', 'Subjects data fetched successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'active_session' => $activeSession,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'slot' => $slot ?: ($slotsList[0] ?? 'School'),
        'slots' => $slotsList,
        'sessions' => $sessionsList,
        'classes' => $classes,
        'sections_map' => $sectionsMap,
        'master_subjects' => $masterSubjects,
        'setup_list' => $setupList,
        'teachers' => $teachers
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
