<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$action = $_POST['action'] ?? '';

// 1. Update Routine Field
if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $field = trim($_POST['field'] ?? '');
    $value = trim($_POST['value'] ?? '');

    $allowed = ['date', 'time', 'subcode', 'subj'];
    if (!in_array($field, $allowed) || $id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid update parameters']);
        exit;
    }

    // If updating subcode, resolve subject title as well
    if ($field === 'subcode') {
        $subInt = intval($value);
        $subTitle = '';
        $stQ = $conn->prepare("SELECT subject FROM subjects WHERE subcode = ? AND (sccategory = ? OR sccategory = '' OR sccategory IS NULL) AND (sccode = ? OR sccode = 0 OR sccode IS NULL) ORDER BY (sccode = ?) DESC LIMIT 1");
        if ($stQ) {
            $stQ->bind_param("isis", $subInt, $sctype, $sccode, $sccode);
            $stQ->execute();
            $stRes = $stQ->get_result();
            if ($stRow = $stRes->fetch_assoc()) {
                $subTitle = $stRow['subject'];
            }
            $stQ->close();
        }

        $stmt = $conn->prepare("UPDATE examroutine SET subcode = ?, subj = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("isii", $subInt, $subTitle, $id, $sccode);
        $success = $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE examroutine SET $field = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("sii", $value, $id, $sccode);
        $success = $stmt->execute();
        $stmt->close();
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Routine updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update: ' . $conn->error]);
    }
    exit;
}

// 2. Delete Routine Row
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM examroutine WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Subject removed from routine']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete row']);
    }
    $stmt->close();
    exit;
}

// 3. Insert Single Subject to Routine
if ($action === 'insert') {
    $sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
    $examname = trim($_POST['examname'] ?? '');
    $clsname = trim($_POST['clsname'] ?? '');
    $secname = trim($_POST['secname'] ?? '');
    $date = !empty($_POST['date']) ? trim($_POST['date']) : date('Y-m-d');
    $time = !empty($_POST['time']) ? trim($_POST['time']) : '10:00:00';
    $subcode = intval($_POST['subcode'] ?? 0);

    if (empty($examname) || empty($clsname) || $subcode <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Examination, Class, and Subject are required']);
        exit;
    }

    // Resolve subject title
    $subTitle = '';
    $stQ = $conn->prepare("SELECT subject FROM subjects WHERE subcode = ? AND (sccategory = ? OR sccategory = '' OR sccategory IS NULL) AND (sccode = ? OR sccode = 0 OR sccode IS NULL) ORDER BY (sccode = ?) DESC LIMIT 1");
    if ($stQ) {
        $stQ->bind_param("isis", $subcode, $sctype, $sccode, $sccode);
        $stQ->execute();
        $stRes = $stQ->get_result();
        if ($stRow = $stRes->fetch_assoc()) {
            $subTitle = $stRow['subject'];
        }
        $stQ->close();
    }

    $stmt = $conn->prepare("INSERT INTO examroutine 
        (sccode, sessionyear, examname, clsname, secname, date, time, subcode, subj, modifieddate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssssis", $sccode, $sessionyear, $examname, $clsname, $secname, $date, $time, $subcode, $subTitle);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Subject added to routine', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add subject: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// 4. Preview Clone / Import Routine
if ($action === 'preview_clone') {
    $sessionyear = trim($_POST['sessionyear'] ?? '');
    $examname = trim($_POST['examname'] ?? '');
    $clsname = trim($_POST['clsname'] ?? '');
    $secname = trim($_POST['secname'] ?? '');

    if (empty($sessionyear) || empty($examname) || empty($clsname)) {
        echo json_encode(['status' => 'error', 'message' => 'Please select Session, Exam, and Class to preview', 'data' => []]);
        exit;
    }

    $sql = "
        SELECT 
            r.id, 
            r.date, 
            r.time, 
            r.subcode, 
            r.subj, 
            r.clsname, 
            r.secname,
            COALESCE(NULLIF(s.subject, ''), r.subj, CONCAT('Subject ', r.subcode)) AS subject,
            s.subben
        FROM examroutine r
        LEFT JOIN subjects s ON r.subcode = s.subcode 
            AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL)
            AND (s.sccode = ? OR s.sccode = 0 OR s.sccode IS NULL)
        WHERE r.sccode = ? 
          AND r.sessionyear = ? 
          AND r.examname = ? 
          AND r.clsname = ?
    ";

    $params = [$sctype, $sccode, $sccode, $sessionyear, $examname, $clsname];
    $types = "siisss";

    if (!empty($secname) && $secname !== 'Select Section' && $secname !== 'All' && $secname !== 'All Sections') {
        $sql .= " AND (TRIM(r.secname) = TRIM(?) OR r.secname = ?)";
        $params[] = $secname;
        $params[] = $secname;
        $types .= "ss";
    }

    $sql .= " ORDER BY r.date ASC, r.time ASC, r.id ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error, 'data' => []]);
        exit;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = [
            'id' => (int)$row['id'],
            'date' => $row['date'],
            'time' => $row['time'],
            'subcode' => (int)$row['subcode'],
            'subject' => $row['subject'],
            'subben' => $row['subben'] ?? '',
            'clsname' => $row['clsname'],
            'secname' => $row['secname']
        ];
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'count' => count($data)
    ]);
    exit;
}

// 5. Execute Clone / Import Routine
if ($action === 'clone') {
    $from_session = trim($_POST['from_session'] ?? '');
    $from_exam = trim($_POST['from_exam'] ?? '');
    $from_class = trim($_POST['from_class'] ?? '');
    $from_section = trim($_POST['from_section'] ?? '');

    $target_session = trim($_POST['sessionyear'] ?? '');
    $target_exam = trim($_POST['examname'] ?? '');
    $target_class = trim($_POST['clsname'] ?? '');
    $target_section = trim($_POST['secname'] ?? '');
    $overwrite = intval($_POST['overwrite'] ?? 0);

    if (empty($from_session) || empty($from_exam) || empty($from_class)) {
        echo json_encode(['status' => 'error', 'message' => 'Source Session, Exam, and Class are required']);
        exit;
    }
    if (empty($target_session) || empty($target_exam) || empty($target_class)) {
        echo json_encode(['status' => 'error', 'message' => 'Target Session, Exam, and Class are required']);
        exit;
    }

    // Step 1: Fetch source routine items
    $srcSql = "SELECT date, time, subcode, subj FROM examroutine WHERE sccode = ? AND sessionyear = ? AND examname = ? AND clsname = ?";
    $srcParams = [$sccode, $from_session, $from_exam, $from_class];
    $srcTypes = "isss";

    if (!empty($from_section) && $from_section !== 'Select Section' && $from_section !== 'All') {
        $srcSql .= " AND (TRIM(secname) = TRIM(?) OR secname = ?)";
        $srcParams[] = $from_section;
        $srcParams[] = $from_section;
        $srcTypes .= "ss";
    }
    $srcSql .= " ORDER BY date ASC, time ASC, id ASC";

    $srcStmt = $conn->prepare($srcSql);
    $srcStmt->bind_param($srcTypes, ...$srcParams);
    $srcStmt->execute();
    $srcRes = $srcStmt->get_result();

    $itemsToClone = [];
    while ($r = $srcRes->fetch_assoc()) {
        $itemsToClone[] = $r;
    }
    $srcStmt->close();

    if (empty($itemsToClone)) {
        echo json_encode(['status' => 'error', 'message' => 'No routine found in the selected source parameters']);
        exit;
    }

    // Step 2: If overwrite requested or clear existing
    if ($overwrite === 1) {
        $delStmt = $conn->prepare("DELETE FROM examroutine WHERE sccode = ? AND sessionyear = ? AND examname = ? AND clsname = ? AND (secname = ? OR secname = '' OR ? = '')");
        $delStmt->bind_param("isssss", $sccode, $target_session, $target_exam, $target_class, $target_section, $target_section);
        $delStmt->execute();
        $delStmt->close();
    }

    // Step 3: Insert items into target
    $insStmt = $conn->prepare("INSERT INTO examroutine 
        (sccode, sessionyear, examname, clsname, secname, date, time, subcode, subj, modifieddate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $clonedCount = 0;
    foreach ($itemsToClone as $item) {
        // Avoid duplicate subcode in same target routine if not overwriting
        if ($overwrite !== 1 && !empty($item['subcode'])) {
            $chk = $conn->prepare("SELECT id FROM examroutine WHERE sccode = ? AND sessionyear = ? AND examname = ? AND clsname = ? AND (secname = ? OR secname = '' OR ? = '') AND subcode = ? LIMIT 1");
            $chk->bind_param("isssssi", $sccode, $target_session, $target_exam, $target_class, $target_section, $target_section, $item['subcode']);
            $chk->execute();
            $chkRes = $chk->get_result();
            $exists = $chkRes->num_rows > 0;
            $chk->close();
            if ($exists) continue; // Skip existing
        }

        $insStmt->bind_param("issssssis", 
            $sccode, 
            $target_session, 
            $target_exam, 
            $target_class, 
            $target_section, 
            $item['date'], 
            $item['time'], 
            $item['subcode'], 
            $item['subj']
        );
        if ($insStmt->execute()) {
            $clonedCount++;
        }
    }
    $insStmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => "Successfully imported $clonedCount subject(s) into current routine",
        'cloned_count' => $clonedCount
    ]);
    exit;
}

// 6. Cascading Helper Endpoints for Clone Modal
if ($action === 'get_clone_sessions') {
    $sessions = [];
    $stmt = $conn->prepare("SELECT DISTINCT syear FROM sessionyear WHERE (sccode = ? OR sccode = 0) ORDER BY syear DESC");
    if ($stmt) {
        $stmt->bind_param("i", $sccode);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['syear']) && !in_array($row['syear'], $sessions)) {
                $sessions[] = (string)$row['syear'];
            }
        }
        $stmt->close();
    }
    // Fallback: examlist
    if (empty($sessions)) {
        $stmt2 = $conn->prepare("SELECT DISTINCT sessionyear FROM examlist WHERE sccode = ? AND sessionyear != '' ORDER BY sessionyear DESC");
        if ($stmt2) {
            $stmt2->bind_param("i", $sccode);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($row2 = $res2->fetch_assoc()) {
                if (!empty($row2['sessionyear']) && !in_array($row2['sessionyear'], $sessions)) {
                    $sessions[] = (string)$row2['sessionyear'];
                }
            }
            $stmt2->close();
        }
    }
    // Fallback: areas
    if (empty($sessions)) {
        $stmt3 = $conn->prepare("SELECT DISTINCT sessionyear FROM areas WHERE sccode = ? AND sessionyear != '' ORDER BY sessionyear DESC");
        if ($stmt3) {
            $stmt3->bind_param("i", $sccode);
            $stmt3->execute();
            $res3 = $stmt3->get_result();
            while ($row3 = $res3->fetch_assoc()) {
                if (!empty($row3['sessionyear']) && !in_array($row3['sessionyear'], $sessions)) {
                    $sessions[] = (string)$row3['sessionyear'];
                }
            }
            $stmt3->close();
        }
    }
    if (empty($sessions)) {
        $currY = date('Y');
        $sessions[] = (string)$currY;
        $sessions[] = (string)($currY - 1);
    }
    echo json_encode(['status' => 'success', 'sessions' => array_values(array_unique($sessions))]);
    exit;
}

if ($action === 'get_clone_exams') {
    $sy = trim($_POST['session'] ?? '');
    $exams = [];
    
    // 1. Query examlist
    $sql = "SELECT DISTINCT examtitle FROM examlist WHERE (sccode = ? OR sccode = 0)";
    if (!empty($sy)) {
        $sql .= " AND (sessionyear = ? OR sessionyear LIKE ? OR sessionyear = '' OR sessionyear IS NULL)";
    }
    $sql .= " ORDER BY examtitle ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($sy)) {
            $syLike = "%$sy%";
            $stmt->bind_param("iss", $sccode, $sy, $syLike);
        } else {
            $stmt->bind_param("i", $sccode);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['examtitle']) && !in_array($row['examtitle'], $exams)) {
                $exams[] = $row['examtitle'];
            }
        }
        $stmt->close();
    }
    
    // 2. Fallback: examroutine
    if (empty($exams)) {
        $stmt2 = $conn->prepare("SELECT DISTINCT examname AS examtitle FROM examroutine WHERE sccode = ? AND (sessionyear = ? OR sessionyear = '' OR sessionyear IS NULL OR ? = '') ORDER BY examname ASC");
        if ($stmt2) {
            $stmt2->bind_param("iss", $sccode, $sy, $sy);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($row2 = $res2->fetch_assoc()) {
                if (!empty($row2['examtitle']) && !in_array($row2['examtitle'], $exams)) {
                    $exams[] = $row2['examtitle'];
                }
            }
            $stmt2->close();
        }
    }
    
    echo json_encode(['status' => 'success', 'exams' => array_values(array_unique($exams))]);
    exit;
}

if ($action === 'get_clone_classes') {
    $sy = trim($_POST['session'] ?? '');
    $classes = [];
    
    // 1. Query areas table for this institution and session
    $sql = "SELECT areaname AS classname FROM areas WHERE sccode = ? AND areaname IS NOT NULL AND areaname != ''";
    if (!empty($sy)) {
        $sql .= " AND (sessionyear = ? OR sessionyear LIKE ? OR sessionyear = '' OR sessionyear IS NULL)";
    }
    $sql .= " GROUP BY areaname ORDER BY MIN(idno) ASC, areaname ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($sy)) {
            $syLike = "%$sy%";
            $stmt->bind_param("iss", $sccode, $sy, $syLike);
        } else {
            $stmt->bind_param("i", $sccode);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['classname']) && !in_array($row['classname'], $classes)) {
                $classes[] = $row['classname'];
            }
        }
        $stmt->close();
    }
    
    // 2. Fallback: areas for this school regardless of sessionyear
    if (empty($classes)) {
        $stmt2 = $conn->prepare("SELECT areaname AS classname FROM areas WHERE sccode = ? AND areaname IS NOT NULL AND areaname != '' GROUP BY areaname ORDER BY MIN(idno) ASC, areaname ASC");
        if ($stmt2) {
            $stmt2->bind_param("i", $sccode);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($row2 = $res2->fetch_assoc()) {
                if (!empty($row2['classname']) && !in_array($row2['classname'], $classes)) {
                    $classes[] = $row2['classname'];
                }
            }
            $stmt2->close();
        }
    }
    
    // 3. Fallback: sessioninfo
    if (empty($classes)) {
        $stmt3 = $conn->prepare("SELECT DISTINCT classname FROM sessioninfo WHERE sccode = ? AND (sessionyear = ? OR ? = '') AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
        if ($stmt3) {
            $stmt3->bind_param("iss", $sccode, $sy, $sy);
            $stmt3->execute();
            $res3 = $stmt3->get_result();
            while ($row3 = $res3->fetch_assoc()) {
                if (!empty($row3['classname']) && !in_array($row3['classname'], $classes)) {
                    $classes[] = $row3['classname'];
                }
            }
            $stmt3->close();
        }
    }
    
    // 4. Fallback: subsetup
    if (empty($classes)) {
        $stmt4 = $conn->prepare("SELECT DISTINCT classname FROM subsetup WHERE sccode = ? AND (sessionyear = ? OR ? = '') AND classname IS NOT NULL AND classname != '' ORDER BY classname ASC");
        if ($stmt4) {
            $stmt4->bind_param("iss", $sccode, $sy, $sy);
            $stmt4->execute();
            $res4 = $stmt4->get_result();
            while ($row4 = $res4->fetch_assoc()) {
                if (!empty($row4['classname']) && !in_array($row4['classname'], $classes)) {
                    $classes[] = $row4['classname'];
                }
            }
            $stmt4->close();
        }
    }

    // 5. Fallback: examroutine
    if (empty($classes)) {
        $stmt5 = $conn->prepare("SELECT DISTINCT clsname AS classname FROM examroutine WHERE sccode = ? AND (sessionyear = ? OR ? = '') AND clsname IS NOT NULL AND clsname != '' ORDER BY clsname ASC");
        if ($stmt5) {
            $stmt5->bind_param("iss", $sccode, $sy, $sy);
            $stmt5->execute();
            $res5 = $stmt5->get_result();
            while ($row5 = $res5->fetch_assoc()) {
                if (!empty($row5['classname']) && !in_array($row5['classname'], $classes)) {
                    $classes[] = $row5['classname'];
                }
            }
            $stmt5->close();
        }
    }

    echo json_encode(['status' => 'success', 'classes' => array_values($classes)]);
    exit;
}

if ($action === 'get_clone_sections') {
    $sy = trim($_POST['session'] ?? '');
    $cls = trim($_POST['class'] ?? '');
    $sections = [];

    if (!empty($cls)) {
        // 1. Query areas
        $sql = "SELECT subarea AS sectionname FROM areas WHERE sccode = ? AND areaname = ? AND subarea IS NOT NULL AND subarea != ''";
        if (!empty($sy)) {
            $sql .= " AND (sessionyear = ? OR sessionyear LIKE ? OR sessionyear = '' OR sessionyear IS NULL)";
        }
        $sql .= " GROUP BY subarea ORDER BY MIN(idno) ASC, subarea ASC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($sy)) {
                $syLike = "%$sy%";
                $stmt->bind_param("isss", $sccode, $cls, $sy, $syLike);
            } else {
                $stmt->bind_param("is", $sccode, $cls);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['sectionname']) && !in_array($row['sectionname'], $sections)) {
                    $sections[] = $row['sectionname'];
                }
            }
            $stmt->close();
        }

        // 2. Fallback: areas for this school & class regardless of sessionyear
        if (empty($sections)) {
            $stmt2 = $conn->prepare("SELECT subarea AS sectionname FROM areas WHERE sccode = ? AND areaname = ? AND subarea IS NOT NULL AND subarea != '' GROUP BY subarea ORDER BY MIN(idno) ASC, subarea ASC");
            if ($stmt2) {
                $stmt2->bind_param("is", $sccode, $cls);
                $stmt2->execute();
                $res2 = $stmt2->get_result();
                while ($row2 = $res2->fetch_assoc()) {
                    if (!empty($row2['sectionname']) && !in_array($row2['sectionname'], $sections)) {
                        $sections[] = $row2['sectionname'];
                    }
                }
                $stmt2->close();
            }
        }

        // 3. Fallback: sessioninfo
        if (empty($sections)) {
            $stmt3 = $conn->prepare("SELECT DISTINCT sectionname FROM sessioninfo WHERE sccode = ? AND classname = ? AND (sessionyear = ? OR ? = '') AND sectionname IS NOT NULL AND sectionname != '' ORDER BY sectionname ASC");
            if ($stmt3) {
                $stmt3->bind_param("isss", $sccode, $cls, $sy, $sy);
                $stmt3->execute();
                $res3 = $stmt3->get_result();
                while ($row3 = $res3->fetch_assoc()) {
                    if (!empty($row3['sectionname']) && !in_array($row3['sectionname'], $sections)) {
                        $sections[] = $row3['sectionname'];
                    }
                }
                $stmt3->close();
            }
        }

        // 4. Fallback: subsetup
        if (empty($sections)) {
            $stmt4 = $conn->prepare("SELECT DISTINCT sectionname FROM subsetup WHERE sccode = ? AND classname = ? AND (sessionyear = ? OR ? = '') AND sectionname IS NOT NULL AND sectionname != '' ORDER BY sectionname ASC");
            if ($stmt4) {
                $stmt4->bind_param("isss", $sccode, $cls, $sy, $sy);
                $stmt4->execute();
                $res4 = $stmt4->get_result();
                while ($row4 = $res4->fetch_assoc()) {
                    if (!empty($row4['sectionname']) && !in_array($row4['sectionname'], $sections)) {
                        $sections[] = $row4['sectionname'];
                    }
                }
                $stmt4->close();
            }
        }

        // 5. Fallback: examroutine
        if (empty($sections)) {
            $stmt5 = $conn->prepare("SELECT DISTINCT secname AS sectionname FROM examroutine WHERE sccode = ? AND clsname = ? AND (sessionyear = ? OR ? = '') AND secname IS NOT NULL AND secname != '' ORDER BY secname ASC");
            if ($stmt5) {
                $stmt5->bind_param("isss", $sccode, $cls, $sy, $sy);
                $stmt5->execute();
                $res5 = $stmt5->get_result();
                while ($row5 = $res5->fetch_assoc()) {
                    if (!empty($row5['sectionname']) && !in_array($row5['sectionname'], $sections)) {
                        $sections[] = $row5['sectionname'];
                    }
                }
                $stmt5->close();
            }
        }
    }

    echo json_encode(['status' => 'success', 'sections' => array_values($sections)]);
    exit;
}


echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

