<?php
/**
 * EIMBox REST API — Weekly Class Routine & Timetable Builder
 * Endpoint: /api/v1/academics/class-routine.php
 * Routes:
 *   GET /api/v1/academics/class-routine.php?sccode={sccode}&sessionyear={year}&classname={class}&sectionname={sec}&tid={tid}
 *   POST /api/v1/academics/class-routine.php (Save Cell / Bulk Save / Clash Detection)
 *   DELETE /api/v1/academics/class-routine.php?id={id}&sccode={sccode}
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

// 2. Handle DELETE (Clear Routine cell or bulk clear)
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM clsroutine WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            api_response('success', 'Routine period cell cleared successfully.', ['deleted_id' => $id]);
        } else {
            api_response('error', 'Routine entry not found.', null, 404);
        }
    } else {
        // Bulk clear for class & section
        $sessionyear = trim($_GET['sessionyear'] ?? $input['sessionyear'] ?? date('Y'));
        $classname = trim($_GET['classname'] ?? $input['classname'] ?? '');
        $sectionname = trim($_GET['sectionname'] ?? $input['sectionname'] ?? '');

        if (!empty($classname) && !empty($sectionname)) {
            $stmt = $conn->prepare("DELETE FROM clsroutine WHERE sccode = ? AND sessionyear = ? AND classname = ? AND sectionname = ?");
            $stmt->bind_param("isss", $sccode, $sessionyear, $classname, $sectionname);
            $stmt->execute();
            $deletedCount = $stmt->affected_rows;
            $stmt->close();
            api_response('success', "Cleared $deletedCount routine periods for $classname ($sectionname).");
        } else {
            api_response('error', 'Valid ID or class/section required for routine deletion.', null, 422);
        }
    }
}

// 3. Handle POST: Save Single Cell or Bulk Matrix
if ($method === 'POST' || $method === 'PUT') {
    $sessionyear = trim($input['sessionyear'] ?? $input['session'] ?? date('Y'));
    $classname = trim($input['classname'] ?? $input['class'] ?? '');
    $sectionname = trim($input['sectionname'] ?? $input['section'] ?? '');
    $period = intval($input['period'] ?? 0);
    $wday = intval($input['wday'] ?? 0);
    $subcode = intval($input['subcode'] ?? 0);
    $tid = intval($input['tid'] ?? 0);
    $entryby = trim($input['entryby'] ?? $user['email'] ?? 'admin');

    $wdayNames = [
        1 => 'Saturday',
        2 => 'Sunday',
        3 => 'Monday',
        4 => 'Tuesday',
        5 => 'Wednesday',
        6 => 'Thursday',
        7 => 'Friday'
    ];
    $dayName = $wdayNames[$wday] ?? 'Saturday';

    // Check for Teacher Clash if teacher assigned
    if ($tid > 0 && $wday > 0 && $period > 0) {
        $chk = $conn->prepare("SELECT r.id, r.classname, r.sectionname, t.tname 
                               FROM clsroutine r 
                               LEFT JOIN teacher t ON (t.tid = r.tid AND t.sccode = r.sccode)
                               WHERE r.sccode = ? AND r.sessionyear = ? AND r.wday = ? AND r.period = ? AND r.tid = ? 
                               AND NOT (r.classname = ? AND r.sectionname = ?)");
        $chk->bind_param("isiiiss", $sccode, $sessionyear, $wday, $period, $tid, $classname, $sectionname);
        $chk->execute();
        $chkRes = $chk->get_result();
        if ($clash = $chkRes->fetch_assoc()) {
            $tName = $clash['tname'] ?: "Teacher #$tid";
            api_response('error', "Teacher Clash Warning: $tName is already scheduled in {$clash['classname']} ({$clash['sectionname']}) on $dayName Period $period.", [
                'clash' => true,
                'clashing_class' => $clash['classname'],
                'clashing_section' => $clash['sectionname']
            ], 409);
        }
        $chk->close();
    }

    if (empty($classname) || empty($sectionname) || $wday <= 0 || $period <= 0) {
        api_response('error', 'Class, Section, Day (wday), and Period number are required.', null, 422);
    }

    // Upsert into clsroutine
    $stmt = $conn->prepare("INSERT INTO clsroutine (sccode, sessionyear, classname, sectionname, period, wday, day, subcode, tid, entryby, modifieddate)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                            ON DUPLICATE KEY UPDATE 
                                subcode = VALUES(subcode), tid = VALUES(tid), entryby = VALUES(entryby), modifieddate = NOW()");
    $stmt->bind_param("isssiisiss", $sccode, $sessionyear, $classname, $sectionname, $period, $wday, $dayName, $subcode, $tid, $entryby);
    $stmt->execute();
    $insertId = $conn->insert_id;
    $stmt->close();

    api_response('success', "Routine cell saved for $dayName (Period $period).", [
        'id' => $insertId,
        'sccode' => $sccode,
        'sessionyear' => $sessionyear,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'wday' => $wday,
        'day' => $dayName,
        'period' => $period,
        'subcode' => $subcode,
        'tid' => $tid
    ]);
}

// 4. GET: Fetch Complete Routine Grid & Reference Lists
if ($method === 'GET') {
    $session = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $className = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionName = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $tid = trim($_GET['tid'] ?? '');

    // Fetch Classes & Sections
    $classes = [];
    $sectionsMap = [];
    $aStmt = $conn->prepare("SELECT areaname, subarea FROM areas WHERE sccode = ? AND sessionyear = ? GROUP BY areaname, subarea ORDER BY MIN(idno) ASC, areaname ASC, subarea ASC");
    if ($aStmt) {
        $aStmt->bind_param("is", $sccode, $session);
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
    if (empty($classes)) $classes = ['Six', 'Seven', 'Eight', 'Nine', 'Ten'];
    if (empty($className) && !empty($classes)) $className = $classes[0];
    if (empty($sectionName) && !empty($sectionsMap[$className])) $sectionName = $sectionsMap[$className][0];

    // Fetch Periods from classschedule
    $periods = [];
    $pStmt = $conn->prepare("SELECT period, timestart, timeend, slots, duration FROM classschedule WHERE sccode = ? AND (sessionyear = ? OR sessionyear = '') ORDER BY period ASC");
    if ($pStmt) {
        $pStmt->bind_param('is', $sccode, $session);
        $pStmt->execute();
        $pRes = $pStmt->get_result();
        while ($p = $pRes->fetch_assoc()) {
            $periods[] = [
                'period' => intval($p['period']),
                'start_time' => substr($p['timestart'] ?: '08:00', 0, 5),
                'end_time' => substr($p['timeend'] ?: '08:45', 0, 5),
                'slot' => $p['slots'] ?: 'School',
                'duration' => intval($p['duration'] ?: 45)
            ];
        }
        $pStmt->close();
    }

    if (empty($periods)) {
        $periods = [
            ['period' => 1, 'start_time' => '07:50', 'end_time' => '08:35', 'slot' => 'School', 'duration' => 45],
            ['period' => 2, 'start_time' => '08:35', 'end_time' => '09:20', 'slot' => 'School', 'duration' => 45],
            ['period' => 3, 'start_time' => '09:20', 'end_time' => '10:05', 'slot' => 'School', 'duration' => 45],
            ['period' => 4, 'start_time' => '10:35', 'end_time' => '11:15', 'slot' => 'School', 'duration' => 40],
            ['period' => 5, 'start_time' => '11:15', 'end_time' => '11:55', 'slot' => 'School', 'duration' => 40],
            ['period' => 6, 'start_time' => '11:55', 'end_time' => '12:35', 'slot' => 'School', 'duration' => 40]
        ];
    }

    // Fetch Teachers
    $teachers = [];
    $tStmt = $conn->prepare("SELECT id, tid, tname, position FROM teacher WHERE sccode = ? AND (status = 'Active' OR status = '1' OR status = '' OR status IS NULL) ORDER BY tname ASC");
    if ($tStmt) {
        $tStmt->bind_param("i", $sccode);
        $tStmt->execute();
        $tRes = $tStmt->get_result();
        while ($tRow = $tRes->fetch_assoc()) {
            $teachers[] = [
                'id' => intval($tRow['id']),
                'tid' => (string)$tRow['tid'],
                'tname' => $tRow['tname'],
                'position' => $tRow['position'] ?: ''
            ];
        }
        $tStmt->close();
    }

    // Fetch School Category from scinfo
    $sccategory = 'School';
    $scStmt = $conn->prepare("SELECT sccategory FROM scinfo WHERE sccode = ? OR sccode = 0 ORDER BY (sccode = ?) DESC LIMIT 1");
    if ($scStmt) {
        $scStmt->bind_param("ii", $sccode, $sccode);
        $scStmt->execute();
        $scRes = $scStmt->get_result();
        if ($scRow = $scRes->fetch_assoc()) {
            $sccategory = trim($scRow['sccategory'] ?? 'School');
        }
        $scStmt->close();
    }

    // Fetch Subjects for Class from subsetup with fallback to subjects table
    $subjects = [];
    $sStmt = $conn->prepare("SELECT ss.subject as subcode, ss.tid,
                                    COALESCE(s.subject, '') as subname,
                                    COALESCE(s.subben, '') as subben,
                                    COALESCE(s.subshname, '') as shortname,
                                    t.tname as teacher_name
                            FROM subsetup ss 
                            LEFT JOIN (
                              SELECT s1.*,
                                     ROW_NUMBER() OVER (
                                       PARTITION BY s1.subcode 
                                       ORDER BY (s1.sccode = ?) DESC,
                                                (CASE WHEN s1.sccategory = ? THEN 1 WHEN s1.sccategory IS NULL OR s1.sccategory = '' THEN 2 ELSE 3 END) ASC,
                                                s1.id DESC
                                     ) AS rn
                              FROM subjects s1
                              WHERE (s1.sccode = ? OR s1.sccode = 0)
                                AND (s1.sccategory = ? OR s1.sccategory = '' OR s1.sccategory IS NULL OR s1.sccode = ?)
                            ) s ON s.subcode = ss.subject AND s.rn = 1
                            LEFT JOIN teacher t ON (t.tid = ss.tid OR t.id = ss.tid) AND (t.sccode = ss.sccode OR t.sccode = 0)
                            WHERE (ss.sccode = ? OR ss.sccode = 0) 
                              AND (ss.sessionyear = ? OR ss.sessionyear = '' OR ss.sessionyear IS NULL) 
                              AND ss.classname = ?
                              AND (ss.sectionname = ? OR ss.sectionname = 'All' OR ss.sectionname = '' OR ss.sectionname IS NULL OR ? = 'All' OR ? = '')
                            ORDER BY CAST(COALESCE(ss.slno, 999) AS UNSIGNED) ASC, ss.subject ASC");
    if ($sStmt) {
        $sStmt->bind_param("isisssisssss", $sccode, $sccategory, $sccode, $sccategory, $sccode, $sccode, $session, $className, $sectionName, $sectionName, $sectionName);
        $sStmt->execute();
        $sRes = $sStmt->get_result();
        while ($sRow = $sRes->fetch_assoc()) {
            $subjects[] = [
                'subcode' => intval($sRow['subcode']),
                'subname' => $sRow['subname'],
                'subben' => $sRow['subben'] ?: '',
                'shortname' => $sRow['shortname'] ?: $sRow['subname'],
                'tid' => $sRow['tid'] ? (string)$sRow['tid'] : '',
                'teacher_name' => $sRow['teacher_name'] ?: ''
            ];
        }
        $sStmt->close();
    }

    // If no subsetup allocated, fallback to master subjects catalog
    if (empty($subjects)) {
        $mStmt = $conn->prepare("SELECT s1.subcode, s1.subject as subname, s1.subben, s1.subshname as shortname
                                 FROM subjects s1
                                 WHERE (s1.sccode = ? OR s1.sccode = 0)
                                   AND (s1.sccategory = ? OR s1.sccategory = '' OR s1.sccategory IS NULL OR s1.sccode = ?)
                                 ORDER BY s1.subcode ASC");
        if ($mStmt) {
            $mStmt->bind_param("isi", $sccode, $sccategory, $sccode);
            $mStmt->execute();
            $mRes = $mStmt->get_result();
            $seen = [];
            while ($mRow = $mRes->fetch_assoc()) {
                $c = intval($mRow['subcode']);
                if (isset($seen[$c])) continue;
                $seen[$c] = true;
                $subjects[] = [
                    'subcode' => $c,
                    'subname' => $mRow['subname'],
                    'subben' => $mRow['subben'] ?: '',
                    'shortname' => $mRow['shortname'] ?: $mRow['subname'],
                    'tid' => '',
                    'teacher_name' => ''
                ];
            }
            $mStmt->close();
        }
    }

    // Fetch Routine Entries
    $where = "r.sccode = ? AND r.sessionyear = ?";
    $types = "is";
    $params = [$sccode, $session];

    if (!empty($className)) {
        $where .= " AND r.classname = ?";
        $types .= "s";
        $params[] = $className;
    }
    if (!empty($sectionName)) {
        $where .= " AND r.sectionname = ?";
        $types .= "s";
        $params[] = $sectionName;
    }
    if (!empty($tid)) {
        $where .= " AND r.tid = ?";
        $types .= "s";
        $params[] = $tid;
    }

    $sql = "SELECT r.id, r.classname, r.sectionname, r.period, r.wday, r.day, r.subcode, r.tid,
                   s.subject AS subname, s.subben AS subname_bn, s.subshname AS shortname,
                   t.tname AS teacher_name, t.position AS teacher_designation
            FROM clsroutine r
            LEFT JOIN subjects s ON (s.subcode = r.subcode AND (s.sccode = r.sccode OR s.sccode = 0))
            LEFT JOIN teacher t ON (t.tid = r.tid AND t.sccode = r.sccode)
            WHERE $where
            ORDER BY r.wday ASC, r.period ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $wdayNames = [
        1 => 'Saturday',
        2 => 'Sunday',
        3 => 'Monday',
        4 => 'Tuesday',
        5 => 'Wednesday',
        6 => 'Thursday',
        7 => 'Friday'
    ];

    $routineEntries = [];
    while ($row = $res->fetch_assoc()) {
        $wday = intval($row['wday']);
        $routineEntries[] = [
            'id' => intval($row['id']),
            'classname' => $row['classname'],
            'sectionname' => $row['sectionname'],
            'wday' => $wday,
            'day_name' => $wdayNames[$wday] ?? $row['day'],
            'period' => intval($row['period']),
            'subcode' => intval($row['subcode']),
            'subname' => $row['subname'] ?: '',
            'shortname' => $row['shortname'] ?: '',
            'tid' => intval($row['tid']),
            'teacher_name' => $row['teacher_name'] ?: ''
        ];
    }
    $stmt->close();

    // Fetch Weekends from settings table
    $weekends = [];
    $wStmt = $conn->prepare("SELECT settings_value FROM settings WHERE (sccode = ? OR sccode = 0) AND setting_title = 'Weekends' LIMIT 1");
    if ($wStmt) {
        $wStmt->bind_param("i", $sccode);
        $wStmt->execute();
        $wRes = $wStmt->get_result();
        if ($wRow = $wRes->fetch_assoc()) {
            $wVal = trim($wRow['settings_value'] ?? '');
            if (!empty($wVal)) {
                $rawDays = preg_split('/[\s,.]+/', $wVal);
                foreach ($rawDays as $rd) {
                    $rd = trim($rd);
                    if (!empty($rd)) $weekends[] = ucfirst(strtolower($rd));
                }
            }
        }
        $wStmt->close();
    }
    if (empty($weekends)) {
        $weekends = ['Friday', 'Saturday'];
    }

    // Fetch Sessions from sessionyear
    $sessionsList = [];
    $activeSession = '';
    $sessStmt = $conn->prepare("SELECT syear, active FROM sessionyear WHERE sccode = ? OR sccode = 0 ORDER BY active DESC, syear DESC");
    if ($sessStmt) {
        $sessStmt->bind_param("i", $sccode);
        $sessStmt->execute();
        $sessRes = $sessStmt->get_result();
        while ($sRow = $sessRes->fetch_assoc()) {
            $yStr = strval($sRow['syear']);
            if (!in_array($yStr, $sessionsList)) $sessionsList[] = $yStr;
            if (intval($sRow['active']) === 1 && empty($activeSession)) $activeSession = $yStr;
        }
        $sessStmt->close();
    }
    if (empty($sessionsList)) {
        $sessionsList = [date('Y'), strval(date('Y') - 1)];
    }
    if (empty($session)) {
        $session = $activeSession ?: $sessionsList[0];
    }

    api_response('success', 'Class routine timetable loaded successfully.', [
        'sccode' => $sccode,
        'sessionyear' => $session,
        'active_session' => $activeSession,
        'sessions' => $sessionsList,
        'weekends' => $weekends,
        'classname' => $className,
        'sectionname' => $sectionName,
        'classes' => $classes,
        'sections_map' => $sectionsMap,
        'periods' => $periods,
        'teachers' => $teachers,
        'subjects' => $subjects,
        'routine' => $routineEntries
    ]);
}

api_response('error', 'Method not allowed.', null, 405);
