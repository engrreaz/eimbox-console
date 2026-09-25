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

function clean_phone_val($phone) {
    if (!$phone) return '';
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) == 11 && str_starts_with($digits, '01')) {
        return $digits;
    } elseif (strlen($digits) == 13 && str_starts_with($digits, '8801')) {
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
    // Reset all voter_no to 0 / NULL in sessioninfo for this session & sccode
    $reset_sql = "UPDATE sessioninfo SET voter_no = 0 WHERE sccode = ? AND sessionyear = ?";
    $stmt = $conn->prepare($reset_sql);
    $stmt->bind_param("is", $sccode, $sessionyear);
    if ($stmt->execute()) {
        $stmt->close();
        
        // Also reset in students table if voter_no column exists
        @$conn->query("UPDATE students SET voter_no = 0 WHERE sccode = $sccode");

        echo json_encode([
            'status' => 'success',
            'message' => 'সকল ভোটার নম্বর সফলভাবে রিসেট করা হয়েছে।'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ভোটার নম্বর রিসেট করতে ব্যর্থ হয়েছে: ' . $stmt->error
        ]);
    }
    exit;
}

if ($action === 'generate') {
    // Fetch all active students sorted by standard class hierarchy:
    // Six -> Seven -> Eight -> Nine -> Ten (and others) -> Section -> Roll
    $sql = "
        SELECT 
            si.id as sessioninfo_id, si.stid, si.classname, si.sectionname, si.rollno, si.slot,
            s.fname, s.mname, s.guarname, s.fnid, s.mnid, s.fmobile, s.mmobile, s.guarmobile, s.previll
        FROM sessioninfo si
        JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? AND si.sessionyear = ? AND si.status = 1
        ORDER BY 
          CASE 
            WHEN LOWER(TRIM(si.classname)) = 'play' THEN 1
            WHEN LOWER(TRIM(si.classname)) = 'nursery' THEN 2
            WHEN LOWER(TRIM(si.classname)) = 'kg' THEN 3
            WHEN LOWER(TRIM(si.classname)) IN ('one', '1', 'class 1', 'class one') THEN 4
            WHEN LOWER(TRIM(si.classname)) IN ('two', '2', 'class 2', 'class two') THEN 5
            WHEN LOWER(TRIM(si.classname)) IN ('three', '3', 'class 3', 'class three') THEN 6
            WHEN LOWER(TRIM(si.classname)) IN ('four', '4', 'class 4', 'class four') THEN 7
            WHEN LOWER(TRIM(si.classname)) IN ('five', '5', 'class 5', 'class five') THEN 8
            WHEN LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six') THEN 9
            WHEN LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven') THEN 10
            WHEN LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight') THEN 11
            WHEN LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine') THEN 12
            WHEN LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten') THEN 13
            WHEN LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven') THEN 14
            WHEN LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve') THEN 15
            ELSE 99
          END ASC,
          si.classname ASC,
          si.sectionname ASC,
          si.rollno ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $sccode, $sessionyear);
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
            'message' => 'নির্বাচিত সেশনে কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি।'
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

    // Now iterate through the ordered students list (Class Six -> Seven -> Eight -> Nine -> Ten...)
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
            'message' => 'ভোটার নম্বর সফলভাবে জেনারেট ও সংরক্ষণ করা হয়েছে!',
            'data' => [
                'total_students' => $total_students,
                'total_unique_voters' => $total_unique_voters,
                'sibling_clusters_count' => $sibling_clusters_count,
                'sessionyear' => $sessionyear
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'ডাটাবেজে ভোটার নম্বর সংরক্ষণ করতে সমস্যা হয়েছে: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action request.']);
