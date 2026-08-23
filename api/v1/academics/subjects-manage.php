<?php
/**
 * EIMBox REST API - Subject Master & Class-wise Setup Management
 * Endpoint: /api/v1/academics/subjects-manage.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request
$user = api_authenticate_request();
$sccode = (int)($user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_send_response(400, false, "Invalid school institution code.");
}

$conn = api_get_db_connection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. GET: Fetch Subjects Master & Class Setup
if ($method === 'GET') {
    $sessionyear = trim($_GET['sessionyear'] ?? date('Y'));
    $classname = trim($_GET['classname'] ?? '');
    $sectionname = trim($_GET['sectionname'] ?? '');

    // Fetch Global and School-specific Master Subjects
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
                'id' => (int)$row['id'],
                'subcode' => (int)$row['subcode'],
                'subject' => $row['subject'],
                'subben' => $row['subben'],
                'subshname' => $row['subshname'],
                'fourth' => (int)$row['fourth'],
                'sup_class' => $row['sup_class']
            ];
        }
        $mStmt->close();
    }

    // Fetch Class-specific subsetup records if classname provided
    $setupList = [];
    if (!empty($classname)) {
        $sql = "SELECT ss.id, ss.slno, ss.sccode, ss.sessionyear, ss.slot, ss.classname, ss.sectionname,
                       ss.subject as subcode, ss.fullmarks, ss.ctest, ss.mtest, ss.subj, ss.obj, ss.pra, ss.ca,
                       ss.subj_pass, ss.obj_pass, ss.pra_pass, ss.comb_subcode,
                       COALESCE(s.subject, CONCAT('Subject #', ss.subject)) as subject_name,
                       COALESCE(s.subben, '') as subject_ben
                FROM subsetup ss
                LEFT JOIN subjects s ON (s.subcode = ss.subject AND (s.sccode = 0 OR s.sccode = ss.sccode))
                WHERE ss.sccode = ? AND ss.sessionyear = ? AND ss.classname = ?";
        
        $params = [$sccode, $sessionyear, $classname];
        $types = "iss";

        if (!empty($sectionname) && $sectionname !== 'All') {
            $sql .= " AND ss.sectionname = ?";
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
                    'id' => (int)$sRow['id'],
                    'slno' => (int)$sRow['slno'],
                    'subcode' => (int)$sRow['subcode'],
                    'subject_name' => $sRow['subject_name'],
                    'subject_ben' => $sRow['subject_ben'],
                    'classname' => $sRow['classname'],
                    'sectionname' => $sRow['sectionname'],
                    'slot' => $sRow['slot'],
                    'fullmarks' => (int)$sRow['fullmarks'],
                    'subj' => (float)$sRow['subj'],
                    'obj' => (float)$sRow['obj'],
                    'pra' => (float)$sRow['pra'],
                    'ca' => (float)$sRow['ca'],
                    'ctest' => (float)$sRow['ctest'],
                    'subj_pass' => (float)$sRow['subj_pass'],
                    'obj_pass' => (float)$sRow['obj_pass'],
                    'pra_pass' => (float)$sRow['pra_pass'],
                    'comb_subcode' => (int)$sRow['comb_subcode']
                ];
            }
            $sStmt->close();
        }
    }

    api_send_response(200, true, "Subjects data fetched successfully.", [
        'sessionyear' => $sessionyear,
        'classname' => $classname,
        'sectionname' => $sectionname,
        'master_subjects' => $masterSubjects,
        'setup_list' => $setupList
    ]);
}

// 2. POST: Upsert Subject Setup Record
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $sessionyear = trim($input['sessionyear'] ?? date('Y'));
    $classname = trim($input['classname'] ?? '');
    $sectionname = trim($input['sectionname'] ?? 'All');
    $subcode = (int)($input['subcode'] ?? $input['subject'] ?? 0);
    $fullmarks = (int)($input['fullmarks'] ?? 100);
    $subj = (float)($input['subj'] ?? 0);
    $obj = (float)($input['obj'] ?? 0);
    $pra = (float)($input['pra'] ?? 0);
    $ca = (float)($input['ca'] ?? 0);
    $ctest = (float)($input['ctest'] ?? 0);
    $subj_pass = (float)($input['subj_pass'] ?? 0);
    $obj_pass = (float)($input['obj_pass'] ?? 0);
    $pra_pass = (float)($input['pra_pass'] ?? 0);
    $comb_subcode = (int)($input['comb_subcode'] ?? 0);
    $slno = (int)($input['slno'] ?? 1);
    $slot = trim($input['slot'] ?? 'School');

    if (empty($classname) || $subcode <= 0 || empty($sessionyear)) {
        api_send_response(422, false, "Class name, subject code, and session year are required.");
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE subsetup SET 
            fullmarks = ?, subj = ?, obj = ?, pra = ?, ca = ?, ctest = ?,
            subj_pass = ?, obj_pass = ?, pra_pass = ?, comb_subcode = ?, slno = ?, slot = ?, modifieddate = NOW()
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param("iddddddddiiiii", 
            $fullmarks, $subj, $obj, $pra, $ca, $ctest,
            $subj_pass, $obj_pass, $pra_pass, $comb_subcode, $slno, $slot,
            $id, $sccode
        );
        $stmt->execute();
        $stmt->close();

        api_send_response(200, true, "Subject setup updated successfully.");
    } else {
        $stmt = $conn->prepare("INSERT INTO subsetup (
            sccode, sessionyear, slot, classname, sectionname, subject, fullmarks,
            subj, obj, pra, ca, ctest, subj_pass, obj_pass, pra_pass, comb_subcode, slno, modifieddate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            fullmarks = VALUES(fullmarks), subj = VALUES(subj), obj = VALUES(obj), pra = VALUES(pra),
            ca = VALUES(ca), subj_pass = VALUES(subj_pass), obj_pass = VALUES(obj_pass), pra_pass = VALUES(pra_pass),
            comb_subcode = VALUES(comb_subcode), slno = VALUES(slno), modifieddate = NOW()");
        
        $stmt->bind_param("issssiiidddddddii",
            $sccode, $sessionyear, $slot, $classname, $sectionname, $subcode, $fullmarks,
            $subj, $obj, $pra, $ca, $ctest, $subj_pass, $obj_pass, $pra_pass, $comb_subcode, $slno
        );
        $stmt->execute();
        $insertId = $conn->insert_id ?: $id;
        $stmt->close();

        api_send_response(201, true, "Subject setup assigned successfully.", ['id' => $insertId]);
    }
}

// 3. DELETE: Remove Subject Setup Record
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
    }

    if ($id <= 0) {
        api_send_response(422, false, "Valid setup ID required.");
    }

    $stmt = $conn->prepare("DELETE FROM subsetup WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_send_response(200, true, "Subject setup removed successfully.");
    } else {
        api_send_response(404, false, "Record not found.");
    }
}

api_send_response(405, false, "Method not allowed.");
