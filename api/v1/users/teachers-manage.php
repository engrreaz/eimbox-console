<?php
/**
 * EIMBox REST API — Teachers & Faculty Master Management
 * Endpoint: /api/v1/users/teachers-manage.php
 * Table: teacher (teacher.sql)
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

// 2. Handle DELETE: Delete a Teacher
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    $tid = trim($_GET['tid'] ?? $input['tid'] ?? '');

    if ($id <= 0 && empty($tid)) {
        api_response('error', 'Valid Teacher ID is required for deletion.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM teacher WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $id, $sccode);
    } else {
        $stmt = $conn->prepare("DELETE FROM teacher WHERE tid = ? AND sccode = ?");
        $stmt->bind_param("si", $tid, $sccode);
    }
    
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Teacher record removed successfully.');
    } else {
        api_response('error', 'Teacher not found or already removed.', null, 404);
    }
}

// 3. Handle POST: Save / Update Teacher (Basic Profile + Bank & Salary Structure)
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $tid = trim($input['tid'] ?? '');
    $tname = trim($input['tname'] ?? '');
    $tnameb = trim($input['tnameb'] ?? '');
    $position = trim($input['position'] ?? $input['desig'] ?? 'Assistant Teacher');
    $slots = trim($input['slots'] ?? $input['slot'] ?? 'School');
    $jdate = trim($input['jdate'] ?? '') ?: null;
    $fjdate = trim($input['fjdate'] ?? '') ?: null;
    $ranks = intval($input['ranks'] ?? 0);
    $subjects = trim($input['subjects'] ?? $input['subject'] ?? '');
    $mobile = trim($input['mobile'] ?? '');
    $emergency = trim($input['emergency'] ?? '');
    $email = trim($input['email'] ?? '');
    $nid = trim($input['nid'] ?? '');
    $mpoindex = trim($input['mpoindex'] ?? '');
    $tin = trim($input['tin'] ?? '');
    $rfidtag = trim($input['rfidtag'] ?? '');
    $dob = trim($input['dob'] ?? '') ?: null;
    $gender = trim($input['gender'] ?? 'Male');
    $religion = trim($input['religion'] ?? 'Islam');
    $bgroup = trim($input['bgroup'] ?? '');
    $curin = trim($input['curin'] ?? '09:00:00');
    $curout = trim($input['curout'] ?? '16:00:00');
    $status = trim($input['status'] ?? '1');

    // Bank Details
    $bankname = trim($input['bankname'] ?? '');
    $branch = trim($input['branch'] ?? '');
    $accno = trim($input['accno'] ?? '');
    $routing = trim($input['routing'] ?? '');
    $bnamesch = trim($input['bnamesch'] ?? '');
    $bbrsch = trim($input['bbrsch'] ?? '');
    $accnosch = trim($input['accnosch'] ?? '');
    $routesch = trim($input['routesch'] ?? '');
    $bnamepf = trim($input['bnamepf'] ?? '');
    $bbrpf = trim($input['bbrpf'] ?? '');
    $accnopf = trim($input['accnopf'] ?? '');
    $routepf = trim($input['routepf'] ?? '');

    // Govt MPO Salary Structure
    $paycode = trim($input['paycode'] ?? '');
    $payscale = trim($input['payscale'] ?? '');
    $basic = floatval($input['basic'] ?? 0);
    $incentive = floatval($input['incentive'] ?? 0);
    $house = floatval($input['house'] ?? 0);
    $medical = floatval($input['medical'] ?? 0);
    $arrea = floatval($input['arrea'] ?? 0);
    $welfare = floatval($input['welfare'] ?? 0);
    $retire = floatval($input['retire'] ?? 0);
    $netamtgovt = floatval($input['netamtgovt'] ?? (($basic + $incentive + $house + $medical + $arrea) - ($welfare + $retire)));

    // School Internal Salary Structure
    $salary = floatval($input['salary'] ?? 0);
    $mobilevata = floatval($input['mobilevata'] ?? 0);
    $travel = floatval($input['travel'] ?? 0);
    $medical2 = floatval($input['medical2'] ?? 0);
    $exam = floatval($input['exam'] ?? 0);
    $festival = floatval($input['festival'] ?? 0);
    $pf = floatval($input['pf'] ?? 0);
    $net2 = floatval($input['net2'] ?? (($salary + $mobilevata + $travel + $medical2 + $exam + $festival) - $pf));

    if (empty($tname)) {
        api_response('error', 'Teacher full name is required.', null, 422);
    }

    // Auto-generate TID if new and missing
    if (empty($tid)) {
        $maxTidRes = $conn->query("SELECT MAX(CAST(tid AS UNSIGNED)) AS maxtid FROM teacher WHERE sccode = $sccode");
        $maxTidRow = $maxTidRes ? $maxTidRes->fetch_assoc() : null;
        $maxTid = intval($maxTidRow['maxtid'] ?? 0);
        $tid = strval($maxTid > 0 ? ($maxTid + 1) : (($sccode * 1000) + 1));
    }

    if ($id > 0) {
        // UPDATE Teacher
        $sql = "UPDATE teacher SET 
                    tid = ?, tname = ?, tnameb = ?, position = ?, slots = ?, 
                    jdate = ?, fjdate = ?, ranks = ?, subjects = ?, mobile = ?, 
                    emergency = ?, email = ?, nid = ?, mpoindex = ?, tin = ?, 
                    rfidtag = ?, dob = ?, gender = ?, religion = ?, bgroup = ?, 
                    curin = ?, curout = ?, status = ?,
                    bankname = ?, branch = ?, accno = ?, routing = ?,
                    bnamesch = ?, bbrsch = ?, accnosch = ?, routesch = ?,
                    bnamepf = ?, bbrpf = ?, accnopf = ?, routepf = ?,
                    paycode = ?, payscale = ?, basic = ?, incentive = ?, house = ?, medical = ?, arrea = ?, welfare = ?, retire = ?, netamtgovt = ?,
                    salary = ?, mobilevata = ?, travel = ?, medical2 = ?, exam = ?, festival = ?, pf = ?, net2 = ?,
                    modifieddate = NOW()
                WHERE id = ? AND sccode = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssisssssssssssssssssssssssssssddddddddddddddddii",
            $tid, $tname, $tnameb, $position, $slots,
            $jdate, $fjdate, $ranks, $subjects, $mobile,
            $emergency, $email, $nid, $mpoindex, $tin,
            $rfidtag, $dob, $gender, $religion, $bgroup,
            $curin, $curout, $status,
            $bankname, $branch, $accno, $routing,
            $bnamesch, $bbrsch, $accnosch, $routesch,
            $bnamepf, $bbrpf, $accnopf, $routepf,
            $paycode, $payscale, $basic, $incentive, $house, $medical, $arrea, $welfare, $retire, $netamtgovt,
            $salary, $mobilevata, $travel, $medical2, $exam, $festival, $pf, $net2,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();

        api_response('success', 'Teacher profile updated successfully.', ['id' => $id, 'tid' => $tid]);
    } else {
        // INSERT Teacher
        $sql = "INSERT INTO teacher (
                    sccode, tid, tname, tnameb, position, slots, 
                    jdate, fjdate, ranks, subjects, mobile, 
                    emergency, email, nid, mpoindex, tin, 
                    rfidtag, dob, gender, religion, bgroup, 
                    curin, curout, status,
                    bankname, branch, accno, routing,
                    bnamesch, bbrsch, accnosch, routesch,
                    bnamepf, bbrpf, accnopf, routepf,
                    paycode, payscale, basic, incentive, house, medical, arrea, welfare, retire, netamtgovt,
                    salary, mobilevata, travel, medical2, exam, festival, pf, net2,
                    modifieddate
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, ?, ?, 
                    NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssisssssssssssssssssssssssssssdddddddddddddddd",
            $sccode, $tid, $tname, $tnameb, $position, $slots,
            $jdate, $fjdate, $ranks, $subjects, $mobile,
            $emergency, $email, $nid, $mpoindex, $tin,
            $rfidtag, $dob, $gender, $religion, $bgroup,
            $curin, $curout, $status,
            $bankname, $branch, $accno, $routing,
            $bnamesch, $bbrsch, $accnosch, $routesch,
            $bnamepf, $bbrpf, $accnopf, $routepf,
            $paycode, $payscale, $basic, $incentive, $house, $medical, $arrea, $welfare, $retire, $netamtgovt,
            $salary, $mobilevata, $travel, $medical2, $exam, $festival, $pf, $net2
        );
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();

        api_response('success', 'Teacher registered successfully.', ['id' => $insertId, 'tid' => $tid], 201);
    }
}

// 4. Handle GET: Retrieve Teachers List or Single Teacher Profile
if ($method === 'GET') {
    $tid = trim($_GET['tid'] ?? '');
    $id = intval($_GET['id'] ?? 0);
    $search = trim($_GET['search'] ?? '');
    $position = trim($_GET['position'] ?? '');
    $slot = trim($_GET['slot'] ?? '');
    $status = trim($_GET['status'] ?? '');

    // Single Teacher Details
    if ($id > 0 || !empty($tid)) {
        if ($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM teacher WHERE id = ? AND sccode = ? LIMIT 1");
            $stmt->bind_param("ii", $id, $sccode);
        } else {
            $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ? LIMIT 1");
            $stmt->bind_param("si", $tid, $sccode);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $teacher = $res->fetch_assoc();
        $stmt->close();

        if ($teacher) {
            $teacher['photo_url'] = "https://eimbox.com/teachers/{$teacher['tid']}.jpg";
            $teacher['sign_url'] = "https://eimbox.com/sign/{$teacher['tid']}.png";
            api_response('success', 'Teacher profile retrieved.', $teacher);
        } else {
            api_response('error', 'Teacher not found.', null, 404);
        }
    }

    // Teachers Directory Query
    $sql = "SELECT id, sccode, tid, tname, tnameb, position, slots, jdate, fjdate, ranks, subjects,
                   mobile, emergency, email, nid, mpoindex, tin, rfidtag, dob, gender, religion, bgroup,
                   status, curin, curout,
                   bankname, branch, accno, routing, accnosch, bnamesch, bbrsch, routesch,
                   accnopf, bnamepf, bbrpf, routepf,
                   paycode, payscale, basic, incentive, house, medical, arrea, welfare, retire, netamtgovt,
                   salary, mobilevata, travel, medical2, exam, festival, pf, net2, modifieddate
            FROM teacher 
            WHERE sccode = ?";
    
    $params = [$sccode];
    $types = "i";

    if (!empty($position) && $position !== 'all') {
        $sql .= " AND position = ?";
        $params[] = $position;
        $types .= "s";
    }

    if (!empty($slot) && $slot !== 'all') {
        $sql .= " AND (slots = ? OR slots = '')";
        $params[] = $slot;
        $types .= "s";
    }

    if ($status !== '' && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (tname LIKE ? OR tnameb LIKE ? OR tid LIKE ? OR mobile LIKE ? OR mpoindex LIKE ? OR subjects LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
        $types .= "ssssss";
    }

    $sql .= " ORDER BY CAST(COALESCE(ranks, 999) AS UNSIGNED) ASC, id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $row['photo_url'] = "https://eimbox.com/teachers/{$row['tid']}.jpg";
        $row['sign_url'] = "https://eimbox.com/sign/{$row['tid']}.png";
        $teachers[] = $row;
    }
    $stmt->close();

    api_response('success', 'Teachers directory retrieved successfully.', [
        'sccode' => $sccode,
        'total' => count($teachers),
        'teachers' => $teachers
    ]);
}
