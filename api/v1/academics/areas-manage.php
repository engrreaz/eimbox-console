<?php
/**
 * EIMBox REST API - Academic Areas (Classes & Sections) Management
 * Endpoint: /api/v1/academics/areas-manage.php
 * Routes:
 *   GET /api/v1/academics/areas-manage.php?sccode={sccode}&sessionyear={year}&slot={slot}
 *   POST /api/v1/academics/areas-manage.php (Create / Update / Clone / Delete)
 *   DELETE /api/v1/academics/areas-manage.php?id={id}&sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';
global $conn;

// Authenticate Request
$user = api_authenticate_request();
$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $input['action'] ?? '';

// 1. Resolve School Code & User Identifier
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);
$userIdentifier = $user['email'] ?? $user['username'] ?? $user['profilename'] ?? 'admin';

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle DELETE (via DELETE method or POST with action=delete)
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid area ID is required for deletion.', null, 422);
    }

    $delStmt = $conn->prepare("DELETE FROM areas WHERE id = ? AND sccode = ?");
    $delStmt->bind_param("ii", $id, $sccode);
    $delStmt->execute();
    $affected = $delStmt->affected_rows;
    $delStmt->close();

    if ($affected > 0) {
        api_response('success', 'Class section removed successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Area not found or already deleted.', null, 404);
    }
}

// 3. Handle POST: Clone Session Structure
if ($method === 'POST' && ($action === 'clone_session' || $action === 'copy_session')) {
    $fromSession = trim($input['from_session'] ?? $input['from_year'] ?? '');
    $toSession = trim($input['to_session'] ?? $input['to_year'] ?? '');

    if (empty($fromSession) || empty($toSession)) {
        api_response('error', 'Both source (from_session) and target (to_session) session years are required.', null, 422);
    }

    if ($fromSession === $toSession) {
        api_response('error', 'Source and target session years cannot be the same.', null, 422);
    }

    // Fetch existing records from source session
    $srcStmt = $conn->prepare("SELECT idno, slot, areaname, subarea, classteacher FROM areas WHERE sccode = ? AND sessionyear = ?");
    $srcStmt->bind_param("is", $sccode, $fromSession);
    $srcStmt->execute();
    $srcRes = $srcStmt->get_result();

    $copiedCount = 0;
    $insStmt = $conn->prepare("INSERT INTO areas (sccode, sessionyear, slot, areaname, subarea, idno, classteacher, user, modifieddate)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                               ON DUPLICATE KEY UPDATE idno = VALUES(idno), slot = VALUES(slot), classteacher = VALUES(classteacher), modifieddate = NOW()");

    while ($row = $srcRes->fetch_assoc()) {
        $slotVal = $row['slot'] ?: 'School';
        $insStmt->bind_param("issssiss", $sccode, $toSession, $slotVal, $row['areaname'], $row['subarea'], $row['idno'], $row['classteacher'], $userIdentifier);
        $insStmt->execute();
        $copiedCount++;
    }
    $srcStmt->close();
    $insStmt->close();

    api_response('success', "Successfully cloned $copiedCount class/section structure records from $fromSession to $toSession.", [
        'from_session' => $fromSession,
        'to_session' => $toSession,
        'copied_count' => $copiedCount
    ]);
}

// 4. Handle POST / PUT: Create or Update Class/Section
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);
    $areaname = trim($input['classname'] ?? $input['areaname'] ?? '');
    $subarea = trim($input['sectionname'] ?? $input['subarea'] ?? '');
    $sessionyear = trim($input['sessionyear'] ?? $input['session'] ?? date('Y'));
    $slot = trim($input['slot'] ?? 'School');
    $idno = intval($input['idno'] ?? 1);
    $classteacher = (isset($input['classteacher']) && $input['classteacher'] !== '' && $input['classteacher'] !== null && $input['classteacher'] !== '0') ? intval($input['classteacher']) : 0;

    if (empty($areaname) || empty($subarea) || empty($sessionyear)) {
        api_response('error', 'Class name, section name, and session year are required.', null, 422);
    }

    if ($id > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE areas 
                                SET areaname = ?, subarea = ?, sessionyear = ?, slot = ?, idno = ?, classteacher = ?, user = ?, modifieddate = NOW() 
                                WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ssssiisii", $areaname, $subarea, $sessionyear, $slot, $idno, $classteacher, $userIdentifier, $id, $sccode);
        $stmt->execute();
        $stmt->close();

        api_response('success', "Class '$areaname' - Section '$subarea' updated successfully.", [
            'id' => $id,
            'sccode' => $sccode,
            'classname' => $areaname,
            'sectionname' => $subarea,
            'sessionyear' => $sessionyear,
            'slot' => $slot,
            'idno' => $idno,
            'classteacher' => $classteacher
        ]);
    } else {
        // Insert new record with user column supplied
        $stmt = $conn->prepare("INSERT INTO areas (sccode, sessionyear, slot, areaname, subarea, idno, classteacher, user, modifieddate)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE idno = VALUES(idno), slot = VALUES(slot), classteacher = VALUES(classteacher), user = VALUES(user), modifieddate = NOW()");
        $stmt->bind_param("issssiss", $sccode, $sessionyear, $slot, $areaname, $subarea, $idno, $classteacher, $userIdentifier);
        $stmt->execute();
        $insertId = $conn->insert_id ?: $id;
        $stmt->close();

        api_response('success', "Class '$areaname' - Section '$subarea' created successfully.", [
            'id' => $insertId,
            'sccode' => $sccode,
            'classname' => $areaname,
            'sectionname' => $subarea,
            'sessionyear' => $sessionyear,
            'slot' => $slot,
            'idno' => $idno,
            'classteacher' => $classteacher
        ], 201);
    }
}

// 5. GET: Fetch Class & Section Structure with Teachers, Slots, Sessions (from sessionyear) and Settings Classes
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');
    $slot = trim($_GET['slot'] ?? '');
    $classname = trim($_GET['classname'] ?? '');

    // 5a. Fetch Sessions from `sessionyear` table (syear column, active=1 prioritized)
    $sessions = [];
    $activeSession = '';
    $sessStmt = $conn->prepare("SELECT syear, active FROM sessionyear WHERE sccode = ? OR sccode = 0 ORDER BY active DESC, syear DESC");
    if ($sessStmt) {
        $sessStmt->bind_param("i", $sccode);
        $sessStmt->execute();
        $sessRes = $sessStmt->get_result();
        while ($sessRow = $sessRes->fetch_assoc()) {
            $sYear = trim(strval($sessRow['syear']));
            if (!empty($sYear) && !in_array($sYear, $sessions)) {
                $sessions[] = $sYear;
                if (intval($sessRow['active']) === 1 && empty($activeSession)) {
                    $activeSession = $sYear;
                }
            }
        }
        $sessStmt->close();
    }

    // Also collect distinct sessionyear from `areas` table if any extra
    $aYearStmt = $conn->prepare("SELECT DISTINCT sessionyear FROM areas WHERE sccode = ? ORDER BY sessionyear DESC");
    if ($aYearStmt) {
        $aYearStmt->bind_param("i", $sccode);
        $aYearStmt->execute();
        $aYearRes = $aYearStmt->get_result();
        while ($ayRow = $aYearRes->fetch_assoc()) {
            $ay = trim(strval($ayRow['sessionyear']));
            if (!empty($ay) && !in_array($ay, $sessions)) {
                $sessions[] = $ay;
            }
        }
        $aYearStmt->close();
    }

    if (empty($sessions)) {
        $sessions = [strval(date('Y')), strval(date('Y') - 1), strval(date('Y') + 1)];
    }
    if (empty($activeSession)) {
        $activeSession = $sessions[0] ?? strval(date('Y'));
    }
    if (empty($sessionyear) || strtolower($sessionyear) === 'all') {
        $sessionyear = $activeSession;
    }

    // 5b. Fetch Configured Classes from `settings` table (setting_title='Classes')
    $settingsClasses = [];
    $settStmt = $conn->prepare("SELECT settings_value FROM settings WHERE (sccode = ? OR sccode = 0) AND setting_title = 'Classes' ORDER BY (sccode = ?) DESC LIMIT 1");
    if ($settStmt) {
        $settStmt->bind_param("ii", $sccode, $sccode);
        $settStmt->execute();
        $settRes = $settStmt->get_result();
        if ($settRow = $settRes->fetch_assoc()) {
            $val = trim($settRow['settings_value'] ?? '');
            if (!empty($val)) {
                $parts = explode(',', $val);
                foreach ($parts as $p) {
                    $c = trim($p);
                    if (!empty($c) && !in_array($c, $settingsClasses)) {
                        $settingsClasses[] = $c;
                    }
                }
            }
        }
        $settStmt->close();
    }

    if (empty($settingsClasses)) {
        $settingsClasses = ['Play', 'Nursery', 'KG', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'SSC', 'Eleven', 'Twelve', 'HSC'];
    }

    // 5c. Fetch Active Teachers from `teacher` table
    $teachers = [];
    $tStmt = $conn->prepare("SELECT id, tid, tname, position, mobile, email 
                             FROM teacher 
                             WHERE sccode = ? OR sccode = 0
                             ORDER BY tname ASC");
    if ($tStmt) {
        $tStmt->bind_param("i", $sccode);
        $tStmt->execute();
        $tRes = $tStmt->get_result();
        while ($tRow = $tRes->fetch_assoc()) {
            $teachers[] = [
                'id' => intval($tRow['id']),
                'tid' => (string)$tRow['tid'],
                'name' => $tRow['tname'],
                'tname' => $tRow['tname'],
                'designation' => $tRow['position'] ?: '',
                'mobile' => $tRow['mobile'] ?: '',
                'email' => $tRow['email'] ?: ''
            ];
        }
        $tStmt->close();
    }

    // 5d. Fetch Areas Query
    $sql = "SELECT a.id, a.idno, a.sccode, a.slot, a.sessionyear, a.areaname, a.subarea, 
                   a.areaname AS classname, a.subarea AS sectionname,
                   a.classteacher, t.tid AS teacher_tid, t.tname as teacher_name, t.mobile as teacher_mobile, t.position as teacher_designation,
                   (SELECT COUNT(*) FROM sessioninfo s 
                    WHERE s.sccode = a.sccode 
                      AND s.sessionyear = a.sessionyear 
                      AND s.classname = a.areaname 
                      AND s.sectionname = a.subarea 
                      AND s.status = 1) as student_count
            FROM areas a
            LEFT JOIN teacher t ON ((t.sccode = a.sccode OR t.sccode = 0) AND a.classteacher IS NOT NULL AND a.classteacher != 0 AND a.classteacher != '' AND (t.tid = a.classteacher OR t.id = a.classteacher OR CAST(t.tid AS CHAR) = CAST(a.classteacher AS CHAR)))
            WHERE a.sccode = ?";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($sessionyear) && strtolower($sessionyear) !== 'all') {
        $sql .= " AND a.sessionyear = ?";
        $params[] = $sessionyear;
        $types .= "s";
    }

    if (!empty($slot) && $slot !== 'All') {
        $sql .= " AND (a.slot = ? OR a.slot = 'School')";
        $params[] = $slot;
        $types .= "s";
    }

    if (!empty($classname) && $classname !== 'All') {
        $sql .= " AND a.areaname = ?";
        $params[] = $classname;
        $types .= "s";
    }

    $sql .= " ORDER BY a.sessionyear DESC, a.idno ASC, a.areaname ASC, a.subarea ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $areas = [];
    $uniqueClasses = [];
    $totalStudents = 0;
    $assignedTeachers = 0;

    while ($row = $result->fetch_assoc()) {
        $cName = $row['areaname'];
        if (!in_array($cName, $uniqueClasses)) {
            $uniqueClasses[] = $cName;
        }

        $stCount = intval($row['student_count'] ?? 0);
        $totalStudents += $stCount;

        $cTeacher = $row['classteacher'] ? trim((string)$row['classteacher']) : '';
        if (!empty($cTeacher) && $cTeacher !== '0') {
            $assignedTeachers++;
        }

        $areas[] = [
            'id' => intval($row['id']),
            'idno' => intval($row['idno']),
            'sccode' => intval($row['sccode']),
            'slot' => $row['slot'] ?: 'School',
            'sessionyear' => strval($row['sessionyear']),
            'areaname' => $row['areaname'],
            'subarea' => $row['subarea'],
            'classname' => $row['areaname'],
            'sectionname' => $row['subarea'],
            'classteacher' => $cTeacher,
            'teacher_name' => $row['teacher_name'] ?: 'Not Assigned',
            'teacher_mobile' => $row['teacher_mobile'] ?: '',
            'teacher_designation' => $row['teacher_designation'] ?: '',
            'student_count' => $stCount
        ];
    }
    $stmt->close();

    // 5e. Fetch Slots strictly from `slots` table for the institution
    $slots = [];
    $slotStmt = $conn->prepare("SELECT id, slotname FROM slots WHERE sccode = ? ORDER BY id ASC");
    if ($slotStmt) {
        $slotStmt->bind_param("i", $sccode);
        $slotStmt->execute();
        $slotRes = $slotStmt->get_result();
        while ($sRow = $slotRes->fetch_assoc()) {
            $sName = trim($sRow['slotname']);
            if (!empty($sName)) {
                $slots[] = [
                    'id' => intval($sRow['id']),
                    'slotname' => $sName
                ];
            }
        }
        $slotStmt->close();
    }

    // If none found in slots table, check distinct slots in areas table
    if (empty($slots)) {
        $aSlotStmt = $conn->prepare("SELECT DISTINCT slot FROM areas WHERE sccode = ? AND slot IS NOT NULL AND slot != ''");
        if ($aSlotStmt) {
            $aSlotStmt->bind_param("i", $sccode);
            $aSlotStmt->execute();
            $aSlotRes = $aSlotStmt->get_result();
            while ($asRow = $aSlotRes->fetch_assoc()) {
                $asName = trim($asRow['slot']);
                if (!empty($asName)) {
                    $slots[] = [
                        'id' => count($slots) + 1,
                        'slotname' => $asName
                    ];
                }
            }
            $aSlotStmt->close();
        }
    }

    if (empty($slots)) {
        $slots = [
            ['id' => 1, 'slotname' => 'School']
        ];
    }

    api_response('success', 'Academic classes & sections fetched successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'active_session' => $activeSession,
        'summary' => [
            'total_classes' => count($uniqueClasses),
            'total_sections' => count($areas),
            'assigned_teachers' => $assignedTeachers,
            'total_students' => $totalStudents
        ],
        'areas' => $areas,
        'settings_classes' => $settingsClasses,
        'classes' => $settingsClasses,
        'slots' => $slots,
        'teachers' => $teachers,
        'sessions' => $sessions
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
