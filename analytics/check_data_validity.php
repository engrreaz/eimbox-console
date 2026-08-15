<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_SESSION['sccode'] ?? null;
$slot = $_POST['slot'] ?? null;
$sessionyear = $_POST['sessionyear'] ?? null;
$examids_raw = $_POST['examid'] ?? null;

if (!$sccode || !$slot || !$sessionyear || !$examids_raw) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$examids = is_array($examids_raw) ? $examids_raw : [$examids_raw];
$examid_list_str = implode(',', array_map('intval', $examids));

$results = [];
$total_issues = 0;

// 1. Check for missing gender in students who appeared in the exam
$sql_gender = "
    SELECT COUNT(DISTINCT s.stid) AS count
    FROM stmark sm
    JOIN students s ON sm.stid = s.stid AND sm.sccode = s.sccode
    WHERE sm.sccode = ? AND sm.sessionyear = ? AND sm.slot = ? AND sm.examid IN ($examid_list_str)
    AND (s.gender IS NULL OR s.gender NOT IN ('Male', 'Boy', 'Female', 'Girl'));
";
$stmt = $conn->prepare($sql_gender);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'warning', 
        'message' => "Found <strong>$count</strong> students with unassigned gender who appeared in the exam.",
        'url' => "student-manager.php?sessionyear=$sessionyear&slot=$slot&filter=missing_gender", // Example URL
        'url_text' => 'Fix Now'
    ];
    $total_issues += $count;
}
$stmt->close();

// 2. Check for inconsistent presence in stmark
$sql_presence = "
    SELECT COUNT(*) AS count FROM stmark
    WHERE sccode = ? AND sessionyear = ? AND slot = ? AND examid IN ($examid_list_str)
    AND (presence = 0 OR presence IS NULL) AND markobt > 0;
";
$stmt = $conn->prepare($sql_presence);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'error', 
        'message' => "Found <strong>$count</strong> mark entries where student is marked absent but has marks.",
        'url' => "data-fixer.php?issue=inconsistent_presence&sessionyear=$sessionyear&slot=$slot&examids=$examid_list_str", // Example URL
        'url_text' => 'Review Marks'
    ];
    $total_issues += $count;
}
$stmt->close();

// 3. Check for marks greater than full marks
$sql_marks = "
    SELECT COUNT(*) AS count FROM stmark
    WHERE sccode = ? AND sessionyear = ? AND slot = ? AND examid IN ($examid_list_str)
    AND markobt > fullmark;
";
$stmt = $conn->prepare($sql_marks);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'error', 
        'message' => "Found <strong>$count</strong> mark entries where obtained marks are greater than full marks.",
        'url' => "data-fixer.php?issue=invalid_marks&sessionyear=$sessionyear&slot=$slot&examids=$examid_list_str", // Example URL
        'url_text' => 'Review Marks'
    ];
    $total_issues += $count;
}
$stmt->close();

// 4. Check for missing teacher ID in subsetup for relevant classes
$sql_tid = "
    SELECT COUNT(DISTINCT ss.subject) AS count
    FROM subsetup ss
    WHERE ss.sccode = ? AND ss.sessionyear = ? AND ss.slot = ?
    AND (ss.tid IS NULL OR ss.tid = '')
    AND EXISTS (
        SELECT 1 FROM stmark sm
        WHERE sm.sccode = ss.sccode AND sm.sessionyear = ss.sessionyear AND sm.slot = ss.slot
        AND sm.classname = ss.classname AND sm.sectionname = ss.sectionname AND sm.subject = ss.subject
        AND sm.examid IN ($examid_list_str)
    );
";
$stmt = $conn->prepare($sql_tid);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'warning', 
        'message' => "Found <strong>$count</strong> subjects in `subsetup` with no assigned teacher, which will affect teacher performance analysis.",
        'url' => "subsetup.php?sessionyear=$sessionyear&slot=$slot&filter=missing_tid", // Example URL
        'url_text' => 'Assign Teachers'
    ];
    $total_issues += $count;
}
$stmt->close();

// 5. Check for duplicate mark entries for the same student, subject, and exam
$sql_duplicates = "
    SELECT COUNT(*) AS count
    FROM (
        SELECT COUNT(id)
        FROM stmark
        WHERE sccode = ? AND sessionyear = ? AND slot = ? AND examid IN ($examid_list_str)
        GROUP BY stid, subject, examid
        HAVING COUNT(id) > 1
    ) AS duplicates;
";
$stmt = $conn->prepare($sql_duplicates);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'error', 
        'message' => "Found <strong>$count</strong> instances of duplicate mark entries (same student, subject, exam). This can cause incorrect calculations.",
        'url' => "data-fixer.php?issue=duplicate_marks&sessionyear=$sessionyear&slot=$slot&examids=$examid_list_str", // Example URL
        'url_text' => 'Find Duplicates'
    ];
    $total_issues += $count;
}
$stmt->close();

// 6. Check for students in stmark who are not in sessioninfo
$sql_orphan_marks = "
    SELECT COUNT(DISTINCT sm.stid) AS count
    FROM stmark sm
    LEFT JOIN sessioninfo si ON sm.stid = si.stid AND sm.sccode = si.sccode AND sm.sessionyear = si.sessionyear AND sm.slot = si.slot
    WHERE sm.sccode = ? AND sm.sessionyear = ? AND sm.slot = ? AND sm.examid IN ($examid_list_str)
    AND si.stid IS NULL;
";
$stmt = $conn->prepare($sql_orphan_marks);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'error', 
        'message' => "Found <strong>$count</strong> students with marks who are not assigned to any class/section in `sessioninfo`.",
        'url' => "session-manager.php?sessionyear=$sessionyear&slot=$slot&filter=orphan_students", // Example URL
        'url_text' => 'Manage Sessions'
    ];
    $total_issues += $count;
}
$stmt->close();

// 7. Check for zero or null full marks
$sql_zero_fullmark = "
    SELECT COUNT(*) AS count
    FROM stmark
    WHERE sccode = ? AND sessionyear = ? AND slot = ? AND examid IN ($examid_list_str)
    AND (fullmark IS NULL OR fullmark <= 0);
";
$stmt = $conn->prepare($sql_zero_fullmark);
$stmt->bind_param("iss", $sccode, $sessionyear, $slot);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
if ($count > 0) {
    $results[] = [
        'type' => 'error', 
        'message' => "Found <strong>$count</strong> mark entries with invalid (zero or null) full marks, which will cause calculation errors.",
        'url' => "data-fixer.php?issue=invalid_full_marks&sessionyear=$sessionyear&slot=$slot&examids=$examid_list_str", // Example URL
        'url_text' => 'Review Marks'
    ];
    $total_issues += $count;
}
$stmt->close();


if ($total_issues == 0) {
    $results[] = ['type' => 'success', 'message' => 'No data integrity issues found. Ready for analysis.'];
}

echo json_encode(['status' => 'success', 'issues' => $results, 'total_issues' => $total_issues]);
?>