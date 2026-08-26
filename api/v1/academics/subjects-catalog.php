<?php
/**
 * EIMBox REST API — Master Subjects Catalog & Custom Subjects Studio
 * Endpoint: /api/v1/academics/subjects-catalog.php
 * Table: subjects (subjects.sql)
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

// 2. Handle DELETE: Remove Custom Subject (Only 401-800 range allowed)
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    $subcode = intval($_GET['subcode'] ?? $input['subcode'] ?? 0);

    if ($id <= 0 && $subcode <= 0) {
        api_response('error', 'Valid Subject ID or Code is required for deletion.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ? AND sccode = ? AND subcode BETWEEN 401 AND 800");
        $stmt->bind_param("ii", $id, $sccode);
    } else {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE subcode = ? AND sccode = ? AND subcode BETWEEN 401 AND 800");
        $stmt->bind_param("ii", $subcode, $sccode);
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Custom subject removed successfully.');
    } else {
        api_response('error', 'Subject not found or cannot delete NCTB standard subject.', null, 403);
    }
}

// 3. Handle POST: Create or Update Custom Subject (401 - 800)
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $subcode = intval($input['subcode'] ?? 0);
    $subject = trim($input['subject'] ?? $input['subject_eng'] ?? '');
    $subben = trim($input['subben'] ?? $input['subject_ben'] ?? '');
    $subshname = trim($input['subshname'] ?? $input['short_code'] ?? '');
    $sccategory = trim($input['sccategory'] ?? 'School');
    $fourth = intval($input['fourth'] ?? 0);

    if (empty($subject) || empty($subshname)) {
        api_response('error', 'Subject English name and Short code are required.', null, 422);
    }

    // Auto-assign next custom subject code if not provided
    if ($subcode <= 0) {
        $maxRes = $conn->query("SELECT MAX(subcode) AS max_code FROM subjects WHERE sccode = $sccode AND subcode BETWEEN 401 AND 800");
        $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
        $maxVal = intval($maxRow['max_code'] ?? 0);
        $subcode = ($maxVal >= 401) ? ($maxVal + 1) : 401;
    }

    // Validate Code Range (401 - 800)
    if ($subcode < 401 || $subcode > 800) {
        api_response('error', "Custom subject code must strictly be between 401 and 800. Provided code ($subcode) is outside allowed range.", null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET subcode = ?, subject = ?, subben = ?, subshname = ?, sccategory = ?, fourth = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $stmt->bind_param("issssiii", $subcode, $subject, $subben, $subshname, $sccategory, $fourth, $id, $sccode);
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Custom subject updated successfully.', ['id' => $id, 'subcode' => $subcode]);
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (sccode, sccategory, subcode, subject, subben, subshname, fourth, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isisssi", $sccode, $sccategory, $subcode, $subject, $subben, $subshname, $fourth);
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Custom subject added successfully to institutional catalog.', ['id' => $insertId, 'subcode' => $subcode], 201);
    }
}

// 4. Handle GET: Fetch Master Subjects (NCTB 101-399 + Custom 401-800)
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $scope = trim($_GET['scope'] ?? 'all'); // 'all', 'nctb', 'custom'

    // Fetch School Category from scinfo if category not specified or 'all'
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

    $targetCategory = (!empty($category) && $category !== 'all') ? $category : $sccategory;

    $sql = "SELECT id, sccode, sccategory, subcode, subject, subben, subshname, fourth, modifieddate,
                   CASE 
                     WHEN sccode = 0 THEN 'NCTB Standard'
                     ELSE 'Custom Institutional'
                   END AS subject_type
            FROM subjects 
            WHERE (sccode = 0 OR sccode = ?)
              AND (sccategory = ? OR sccategory = '' OR sccategory IS NULL)";
    
    $params = [$sccode, $targetCategory];
    $types = "is";

    if ($scope === 'nctb') {
        $sql .= " AND sccode = 0";
    } elseif ($scope === 'custom') {
        $sql .= " AND sccode = ?";
        $params[] = $sccode;
        $types .= "i";
    }

    if (!empty($search)) {
        $sql .= " AND (subject LIKE ? OR subben LIKE ? OR subshname LIKE ? OR CAST(subcode AS CHAR) LIKE ?)";
        $sTerm = "%$search%";
        $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm; $params[] = $sTerm;
        $types .= "ssss";
    }

    $sql .= " ORDER BY subcode ASC, (sccode = ?) DESC";
    $params[] = $sccode;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    $seenCodes = [];
    $nctbCount = 0;
    $customCount = 0;

    while ($row = $result->fetch_assoc()) {
        $code = intval($row['subcode']);
        if (isset($seenCodes[$code])) continue; // Each subject code strictly once
        $seenCodes[$code] = true;

        if ($row['sccode'] == 0) {
            $nctbCount++;
        } else {
            $customCount++;
        }
        $subjects[] = $row;
    }
    $stmt->close();

    api_response('success', 'Subjects catalog retrieved successfully.', [
        'sccode' => $sccode,
        'sccategory' => $targetCategory,
        'kpis' => [
            'total_subjects' => count($subjects),
            'nctb_standards' => $nctbCount,
            'custom_subjects' => $customCount
        ],
        'subjects' => $subjects
    ]);
}
