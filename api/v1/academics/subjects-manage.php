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
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) {
            api_response('success', 'Custom master subject removed successfully.', ['deleted_id' => $id]);
        } else {
            api_response('error', 'Subject not found or cannot delete global subject.', null, 404);
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

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET subcode = ?, subject = ?, subben = ?, subshname = ?, fourth = ?, sup_class = ?, sccategory = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
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
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');

    // Fetch Master Subjects (sccode=0 or sccode=$sccode)
    $masterSubjects = [];
    $mStmt = $conn->prepare("SELECT id, sccode, subcode, subject, subben, subshname, fourth, sup_class 
                             FROM subjects 
                             WHERE sccode = 0 OR sccode = ? 
                             ORDER BY subcode ASC");
    if ($mStmt) {
        $mStmt->bind_param("i", $sccode);
        $mStmt->execute();
        $mRes = $mStmt->get_result();
        while ($row = $mRes->fetch_assoc()) {
            $masterSubjects[] = [
                'id' => intval($row['id']),
                'sccode' => intval($row['sccode']),
                'subcode' => intval($row['subcode']),
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

    // Fetch Classes & Sections for this session
    $classes = [];
    $sectionsMap = [];
    $aStmt = $conn->prepare("SELECT areaname, subarea FROM areas WHERE sccode = ? AND sessionyear = ? GROUP BY areaname, subarea ORDER BY MIN(idno) ASC, areaname ASC, subarea ASC");
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
                       COALESCE(s.subject, CONCAT('Subject #', ss.subject)) as subject_name,
                       COALESCE(s.subben, '') as subject_ben,
                       COALESCE(s.subshname, '') as shortname,
                       t.tname as teacher_name
                FROM subsetup ss
                LEFT JOIN subjects s ON (s.subcode = ss.subject AND (s.sccode = 0 OR s.sccode = ss.sccode))
                LEFT JOIN teacher t ON (t.sccode = ss.sccode AND (t.tid = ss.tid OR t.id = ss.tid))
                WHERE ss.sccode = ? AND ss.sessionyear = ? AND ss.classname = ?";
        
        $params = [$sccode, $sessionyear, $classname];
        $types = "iss";

        if (!empty($sectionname) && $sectionname !== 'All') {
            $sql .= " AND (ss.sectionname = ? OR ss.sectionname = 'All' OR ss.sectionname = '')";
            $params[] = $sectionname;
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
        'classname' => $classname,
        'sectionname' => $sectionname,
        'classes' => $classes,
        'sections_map' => $sectionsMap,
        'master_subjects' => $masterSubjects,
        'setup_list' => $setupList,
        'teachers' => $teachers
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
