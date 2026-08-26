<?php
/**
 * EIMBox REST API — Student Admission, Enrollment & Profile Studio
 * Endpoint: /api/v1/academics/student-enroll.php
 * Tables: students (students.sql), sessioninfo (sessioninfo.sql)
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

// 2. Handle Action: Auto-Generate Next Student ID (STID)
if ($action === 'next_stid' || ($method === 'GET' && isset($_GET['next_stid']))) {
    $res = $conn->query("SELECT MAX(stid) AS max_stid FROM students WHERE sccode = $sccode");
    $row = $res ? $res->fetch_assoc() : null;
    $maxStid = intval($row['max_stid'] ?? 0);

    $nextStid = ($maxStid > 0) ? ($maxStid + 1) : (($sccode * 10000) + 1);

    api_response('success', 'Next STID generated.', [
        'sccode' => $sccode,
        'next_stid' => $nextStid
    ]);
}

// 3. Handle POST: Save / Enroll Student (Dual Table: students + sessioninfo)
if ($method === 'POST') {
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? '');
    $slot = trim($input['slot'] ?? $input['slots'] ?? 'School');
    $rollno = intval($input['rollno'] ?? $input['roll'] ?? 1);
    $stid = intval($input['stid'] ?? 0);

    // Primary Identity
    $nameeng = trim($input['nameeng'] ?? $input['stname'] ?? '');
    $nameben = trim($input['nameben'] ?? '');
    $dob = trim($input['dob'] ?? '') ?: null;
    $gender = trim($input['gender'] ?? 'Male');
    $religion = trim($input['religion'] ?? 'Islam');
    $bgroup = trim($input['bgroup'] ?? '');
    $brn = trim($input['brn'] ?? '');
    $nid = trim($input['nid'] ?? '');
    $disables = trim($input['disables'] ?? '0');
    $mothertongue = trim($input['mothertongue'] ?? 'Bangla');
    $nationality = trim($input['nationality'] ?? 'Bangladeshi');

    // Parental Details
    $fname = trim($input['fname'] ?? '');
    $fnameb = trim($input['fnameb'] ?? '');
    $fnid = trim($input['fnid'] ?? '');
    $fmobile = trim($input['fmobile'] ?? '');
    $foccup = trim($input['foccup'] ?? '');
    $fdeath = trim($input['fdeath'] ?? '0');

    $mname = trim($input['mname'] ?? '');
    $mnameb = trim($input['mnameb'] ?? '');
    $mnid = trim($input['mnid'] ?? '');
    $mmobile = trim($input['mmobile'] ?? '');
    $moccup = trim($input['moccup'] ?? '');
    $mdeath = trim($input['mdeath'] ?? '0');

    // Guardian
    $gname = trim($input['gname'] ?? '');
    $gnameb = trim($input['gnameb'] ?? '');
    $gnid = trim($input['gnid'] ?? '');
    $guar_mobile = trim($input['guar_mobile'] ?? $input['mobile'] ?? $fmobile ?: $mmobile);
    $goccup = trim($input['goccup'] ?? '');
    $grel = trim($input['grel'] ?? 'Father');

    // Address Details
    $preadd = trim($input['preadd'] ?? '');
    $previll = trim($input['previll'] ?? '');
    $prepo = trim($input['prepo'] ?? '');
    $preps = trim($input['preps'] ?? '');
    $predist = trim($input['predist'] ?? '');

    $peradd = trim($input['peradd'] ?? '');
    $pervill = trim($input['pervill'] ?? '');
    $perpo = trim($input['perpo'] ?? '');
    $perps = trim($input['perps'] ?? '');
    $perdist = trim($input['perdist'] ?? '');

    // Academic & TC
    $prev_school = trim($input['prev_school'] ?? '');
    $prev_class = trim($input['prev_class'] ?? '');
    $tc_no = trim($input['tc_no'] ?? '');
    $tc_date = trim($input['tc_date'] ?? '') ?: null;
    $adm_date = trim($input['adm_date'] ?? date('Y-m-d'));
    $groupx = trim($input['groupx'] ?? 'General');
    $status = trim($input['status'] ?? 'Active');

    if (empty($nameeng)) {
        api_response('error', 'Student full name is required.', null, 422);
    }

    // Auto-generate STID if missing
    if ($stid <= 0) {
        $maxRes = $conn->query("SELECT MAX(stid) AS max_stid FROM students WHERE sccode = $sccode");
        $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
        $maxVal = intval($maxRow['max_stid'] ?? 0);
        $stid = ($maxVal > 0) ? ($maxVal + 1) : (($sccode * 10000) + 1);
    }

    // 1. Upsert into master `students` table
    $sqlStudent = "INSERT INTO students (
        sccode, stid, nameeng, nameben, dob, gender, religion, bgroup, brn, nid, disables, mothertongue, nationality,
        fname, fnameb, fnid, fmobile, foccup, fdeath,
        mname, mnameb, mnid, mmobile, moccup, mdeath,
        gname, gnameb, gnid, guar_mobile, goccup, grel,
        preadd, previll, prepo, preps, predist,
        peradd, pervill, perpo, perps, perdist,
        prev_school, prev_class, tc_no, tc_date, adm_date, status, modifieddate
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, NOW()
    ) ON DUPLICATE KEY UPDATE
        nameeng = VALUES(nameeng), nameben = VALUES(nameben), dob = VALUES(dob), gender = VALUES(gender), religion = VALUES(religion),
        bgroup = VALUES(bgroup), brn = VALUES(brn), nid = VALUES(nid), disables = VALUES(disables),
        fname = VALUES(fname), fnameb = VALUES(fnameb), fnid = VALUES(fnid), fmobile = VALUES(fmobile), foccup = VALUES(foccup), fdeath = VALUES(fdeath),
        mname = VALUES(mname), mnameb = VALUES(mnameb), mnid = VALUES(mnid), mmobile = VALUES(mmobile), moccup = VALUES(moccup), mdeath = VALUES(mdeath),
        gname = VALUES(gname), gnameb = VALUES(gnameb), gnid = VALUES(gnid), guar_mobile = VALUES(guar_mobile), goccup = VALUES(goccup), grel = VALUES(grel),
        preadd = VALUES(preadd), previll = VALUES(previll), prepo = VALUES(prepo), preps = VALUES(preps), predist = VALUES(predist),
        peradd = VALUES(peradd), pervill = VALUES(pervill), perpo = VALUES(perpo), perps = VALUES(perps), perdist = VALUES(perdist),
        prev_school = VALUES(prev_school), prev_class = VALUES(prev_class), tc_no = VALUES(tc_no), tc_date = VALUES(tc_date),
        status = VALUES(status), modifieddate = NOW()";

    $stmtSt = $conn->prepare($sqlStudent);
    $stmtSt->bind_param(
        "iisssssssssssssssssssssssssssssssssssssssssssss",
        $sccode, $stid, $nameeng, $nameben, $dob, $gender, $religion, $bgroup, $brn, $nid, $disables, $mothertongue, $nationality,
        $fname, $fnameb, $fnid, $fmobile, $foccup, $fdeath,
        $mname, $mnameb, $mnid, $mmobile, $moccup, $mdeath,
        $gname, $gnameb, $gnid, $guar_mobile, $goccup, $grel,
        $preadd, $previll, $prepo, $preps, $predist,
        $peradd, $pervill, $perpo, $perps, $perdist,
        $prev_school, $prev_class, $tc_no, $tc_date, $adm_date, $status
    );
    $stmtSt->execute();
    $stmtSt->close();

    // 2. Upsert into active `sessioninfo` table
    $rate = floatval($input['rate'] ?? 100);
    $sector = trim($input['sector'] ?? '');

    $sqlSession = "INSERT INTO sessioninfo (
        sccode, sessionyear, classname, sectionname, slot, rollno, stid, groupx, rate, sector, status, modifieddate
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    ) ON DUPLICATE KEY UPDATE
        classname = VALUES(classname), sectionname = VALUES(sectionname), slot = VALUES(slot), rollno = VALUES(rollno),
        groupx = VALUES(groupx), rate = VALUES(rate), sector = VALUES(sector), status = VALUES(status), modifieddate = NOW()";

    $stmtSess = $conn->prepare($sqlSession);
    $stmtSess->bind_param(
        "issssiisdss",
        $sccode, $sessionyear, $classname, $sectionname, $slot, $rollno, $stid, $groupx, $rate, $sector, $status
    );
    $stmtSess->execute();
    $stmtSess->close();

    api_response('success', 'Student profile & enrollment saved successfully.', [
        'sccode' => $sccode,
        'stid' => $stid,
        'nameeng' => $nameeng,
        'sessionyear' => $sessionyear,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'rollno' => $rollno
    ]);
}

// 4. Handle GET: Student Lookup (by STID or Class/Section/Roll)
if ($method === 'GET') {
    $stid = intval($_GET['stid'] ?? 0);
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
    $classname = trim($_GET['classname'] ?? $_GET['class'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? $_GET['section'] ?? '');
    $rollno = intval($_GET['rollno'] ?? $_GET['roll'] ?? 0);

    if ($stid > 0) {
        $sql = "SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.slot, si.rollno, si.groupx, si.rate, si.sector 
                FROM students s 
                LEFT JOIN sessioninfo si ON (si.stid = s.stid AND si.sccode = s.sccode AND si.sessionyear = ?)
                WHERE s.sccode = ? AND s.stid = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $sessionyear, $sccode, $stid);
    } elseif (!empty($classname) && !empty($sectionname) && $rollno > 0) {
        $sql = "SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.slot, si.rollno, si.groupx, si.rate, si.sector 
                FROM sessioninfo si 
                JOIN students s ON (s.stid = si.stid AND s.sccode = si.sccode)
                WHERE si.sccode = ? AND si.sessionyear = ? AND si.classname = ? AND si.sectionname = ? AND si.rollno = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $sccode, $sessionyear, $classname, $sectionname, $rollno);
    } else {
        api_response('error', 'Provide STID or Class, Section & Roll for student lookup.', null, 422);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $student = $res->fetch_assoc();
    $stmt->close();

    if ($student) {
        $student['photo_url'] = "https://eimbox.com/students/{$student['stid']}.jpg";
        api_response('success', 'Student record found.', $student);
    } else {
        api_response('error', 'Student not found.', null, 404);
    }
}
