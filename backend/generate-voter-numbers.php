<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit;
}

$sccode = $_SESSION['sccode'] ?? null;
if (!$sccode) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid institution code.']);
    exit;
}

$action = $_POST['action'] ?? 'generate';
$sessionyear = $_POST['sessionyear'] ?? ($_COOKIE['chain-session'] ?? date('Y'));
$slot = $_POST['slot'] ?? ($_COOKIE['chain-slot'] ?? '');

// Extract 2 digit year pattern (e.g., '26' matches 2026, 2025-26, 2026-27)
$yr_digits = preg_replace('/\D/', '', $sessionyear);
$yr_last2 = substr($yr_digits, -2);
$session_pattern = !empty($yr_last2) ? ('%' . $yr_last2 . '%') : ('%' . date('y') . '%');

function clean_phone_val($phone) {
    if (!$phone) return '';
    $digits = preg_replace('/\D/', '', $phone);
    if (empty($digits)) return '';
    if (strlen($digits) == 13 && str_starts_with($digits, '8801')) {
        return substr($digits, 2);
    }
    return $digits;
}

function clean_nid_val($nid) {
    if (!$nid) return '';
    $digits = preg_replace('/\D/', '', $nid);
    if (strlen($digits) >= 10) {
        return $digits;
    }
    return '';
}

if ($action === 'reset') {
    // Reset all voter_no to 0 in sessioninfo for this session pattern & sccode
    $reset_sql = "UPDATE sessioninfo SET voter_no = 0 WHERE sccode = ? AND sessionyear LIKE ?";
    $stmt = $conn->prepare($reset_sql);
    $stmt->bind_param("is", $sccode, $session_pattern);
    if ($stmt->execute()) {
        $stmt->close();
        
        // Also reset in students table if voter_no column exists
        @$conn->query("UPDATE students SET voter_no = 0 WHERE sccode = $sccode");

        echo json_encode([
            'status' => 'success',
            'message' => 'All voter numbers have been successfully reset.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to reset voter numbers: ' . $stmt->error
        ]);
    }
    exit;
}

if ($action === 'generate') {
    // Fetch all active students strictly from Class Six to Twelve sorted hierarchically:
    // Six -> Seven -> Eight -> Nine -> Ten -> Eleven -> Twelve -> Section -> Roll
    $sql = "
        SELECT 
            si.id as sessioninfo_id, si.stid, si.sessionyear, si.classname, si.sectionname, si.rollno, si.slot,
            s.fname, s.mname, s.guarname, s.fnid, s.mnid, s.fmobile, s.mmobile, s.guarmobile, s.previll
        FROM sessioninfo si
        JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? AND si.sessionyear LIKE ? AND si.status = 1
        AND (
            LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six')
            OR LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven')
            OR LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight')
            OR LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine')
            OR LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten')
            OR LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven', 'xi')
            OR LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve', 'xii')
        )
        ORDER BY 
          CASE 
            WHEN LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six') THEN 1
            WHEN LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven') THEN 2
            WHEN LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight') THEN 3
            WHEN LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine') THEN 4
            WHEN LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten') THEN 5
            WHEN LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven', 'xi') THEN 6
            WHEN LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve', 'xii') THEN 7
            ELSE 99
          END ASC,
          si.classname ASC,
          si.sectionname ASC,
          si.rollno ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $sccode, $session_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();

    $total_students = count($students);
    if ($total_students === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No active student records found matching the session pattern (' . $session_pattern . ').'
        ]);
        exit;
    }

    // Disjoint Set (Union-Find) to form sibling clusters
    $parent = [];
    foreach ($students as $idx => $st) {
        $parent[$idx] = $idx;
    }

    function find_root_gen(&$parent, $i) {
        if ($parent[$i] == $i) return $i;
        $parent[$i] = find_root_gen($parent, $parent[$i]);
        return $parent[$i];
    }

    function union_sets_gen(&$parent, $i, $j) {
        $root_i = find_root_gen($parent, $i);
        $root_j = find_root_gen($parent, $j);
        if ($root_i != $root_j) {
            $parent[$root_i] = $root_j;
        }
    }

    $nid_map = [];
    $mobile_map = [];

    foreach ($students as $idx => $st) {
        $fnid = clean_nid_val($st['fnid']);
        $mnid = clean_nid_val($st['mnid']);
        $fm = clean_phone_val($st['fmobile']);
        $mm = clean_phone_val($st['mmobile']);
        $gm = clean_phone_val($st['guarmobile']);

        if (!empty($fnid)) $nid_map[$fnid][] = $idx;
        if (!empty($mnid)) $nid_map[$mnid][] = $idx;

        if (!empty($fm)) $mobile_map[$fm][] = $idx;
        if (!empty($mm)) $mobile_map[$mm][] = $idx;
        if (!empty($gm)) $mobile_map[$gm][] = $idx;
    }

    // Priority 1: Match by NID
    foreach ($nid_map as $nid => $indices) {
        $first = $indices[0];
        for ($k = 1; $k < count($indices); $k++) {
            union_sets_gen($parent, $first, $indices[$k]);
        }
    }

    // Priority 2: Match by Mobile (with cross-match name/village)
    foreach ($mobile_map as $phone => $indices) {
        $first = $indices[0];
        for ($k = 1; $k < count($indices); $k++) {
            $curr = $indices[$k];
            $f1 = strtolower(trim($students[$first]['fname'] ?? ''));
            $f2 = strtolower(trim($students[$curr]['fname'] ?? ''));
            $v1 = strtolower(trim($students[$first]['previll'] ?? ''));
            $v2 = strtolower(trim($students[$curr]['previll'] ?? ''));

            $name_match = (empty($f1) || empty($f2) || soundex($f1) == soundex($f2) || levenshtein($f1, $f2) <= 4 || str_contains($f1, $f2) || str_contains($f2, $f1));
            $village_match = (empty($v1) || empty($v2) || $v1 === $v2);

            if ($name_match || $village_match) {
                union_sets_gen($parent, $first, $curr);
            }
        }
    }

    // Now iterate through the ordered students list (Class Six -> Seven -> Eight -> Nine -> Ten -> Eleven -> Twelve...)
    // Assign sequential voter numbers 1, 2, 3... to clusters as they appear
    $cluster_voter_map = [];
    $voter_counter = 1;
    $student_voter_assignments = [];
    $sibling_clusters_count = 0;

    foreach ($students as $idx => $st) {
        $root = find_root_gen($parent, $idx);
        if (!isset($cluster_voter_map[$root])) {
            $cluster_voter_map[$root] = $voter_counter++;
        }
        $assigned_voter_no = $cluster_voter_map[$root];
        $student_voter_assignments[] = [
            'sessioninfo_id' => $st['sessioninfo_id'],
            'stid' => $st['stid'],
            'voter_no' => $assigned_voter_no
        ];
    }

    // Count sibling clusters having > 1 members
    $cluster_counts = [];
    foreach ($students as $idx => $st) {
        $root = find_root_gen($parent, $idx);
        $cluster_counts[$root] = ($cluster_counts[$root] ?? 0) + 1;
    }
    foreach ($cluster_counts as $root => $cnt) {
        if ($cnt > 1) {
            $sibling_clusters_count++;
        }
    }

    $total_unique_voters = $voter_counter - 1;

    // Batch update sessioninfo table using prepared statement
    $conn->begin_transaction();
    try {
        $update_stmt = $conn->prepare("UPDATE sessioninfo SET voter_no = ? WHERE id = ? AND sccode = ?");
        $update_st_stmt = $conn->prepare("UPDATE students SET voter_no = ? WHERE stid = ? AND sccode = ?");

        foreach ($student_voter_assignments as $item) {
            $vno = $item['voter_no'];
            $si_id = $item['sessioninfo_id'];
            $stid = $item['stid'];

            $update_stmt->bind_param("iii", $vno, $si_id, $sccode);
            $update_stmt->execute();

            if ($update_st_stmt) {
                $update_st_stmt->bind_param("isi", $vno, $stid, $sccode);
                $update_st_stmt->execute();
            }
        }

        $update_stmt->close();
        if ($update_st_stmt) $update_st_stmt->close();

        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Voter numbers generated and assigned successfully!',
            'data' => [
                'total_students' => $total_students,
                'total_unique_voters' => $total_unique_voters,
                'sibling_clusters_count' => $sibling_clusters_count,
                'sessionyear' => $sessionyear,
                'session_pattern' => $session_pattern
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save voter numbers to database: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action request.']);
