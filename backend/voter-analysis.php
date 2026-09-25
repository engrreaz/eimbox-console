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

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_summary';
$sessionyear = $_GET['sessionyear'] ?? $_POST['sessionyear'] ?? ($_COOKIE['chain-session'] ?? date('Y'));

// Extract 2 digit year pattern (e.g., '26' matches 2026, 2025-26, 2026-27)
$yr_digits = preg_replace('/\D/', '', $sessionyear);
$yr_last2 = substr($yr_digits, -2);
$session_pattern = !empty($yr_last2) ? ('%' . $yr_last2 . '%') : ('%' . date('y') . '%');

function clean_phone($phone) {
    if (!$phone) return '';
    $digits = preg_replace('/\D/', '', $phone);
    if (empty($digits)) return '';
    if (strlen($digits) == 13 && str_starts_with($digits, '8801')) {
        return substr($digits, 2);
    }
    return $digits;
}

function clean_nid($nid) {
    if (!$nid) return '';
    $digits = preg_replace('/\D/', '', $nid);
    if (strlen($digits) >= 10) {
        return $digits;
    }
    return '';
}

// Fetch active students strictly for Classes Six to Twelve with matching Session pattern
$query = "
    SELECT 
        si.id as sessioninfo_id, si.stid, si.sessionyear, si.classname, si.sectionname, si.rollno, si.voter_no, si.slot,
        s.stnameeng, s.stnameben, s.fname, s.fnameben, s.mname, s.mnameben,
        s.fnid, s.mnid, s.fmobile, s.mmobile, s.guarmobile, s.guarname,
        s.previll, s.prepo, s.preps, s.predist
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

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $sccode, $session_pattern);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

$total_students = count($students);

if ($action === 'get_summary') {
    $with_fnid = 0;
    $with_mnid = 0;
    $with_any_nid = 0;
    $with_fmobile = 0;
    $with_any_mobile = 0;
    $voter_assigned_count = 0;
    $unique_voters = [];

    // Grouping by clean keys to estimate siblings
    $nid_groups = [];
    $mobile_groups = [];

    foreach ($students as $st) {
        $fnid = clean_nid($st['fnid']);
        $mnid = clean_nid($st['mnid']);
        $fm = clean_phone($st['fmobile']);
        $mm = clean_phone($st['mmobile']);
        $gm = clean_phone($st['guarmobile']);

        if (!empty($fnid)) $with_fnid++;
        if (!empty($mnid)) $with_mnid++;
        if (!empty($fnid) || !empty($mnid)) $with_any_nid++;

        if (!empty($fm)) $with_fmobile++;
        if (!empty($fm) || !empty($mm) || !empty($gm)) $with_any_mobile++;

        if (!empty($st['voter_no']) && intval($st['voter_no']) > 0) {
            $voter_assigned_count++;
            $unique_voters[intval($st['voter_no'])] = true;
        }

        if (!empty($fnid)) {
            $nid_groups['F_' . $fnid][] = $st['stid'];
        }
        if (!empty($mnid)) {
            $nid_groups['M_' . $mnid][] = $st['stid'];
        }
        $primary_phone = !empty($fm) ? $fm : (!empty($mm) ? $mm : $gm);
        if (!empty($primary_phone)) {
            $mobile_groups[$primary_phone][] = $st['stid'];
        }
    }

    $sibling_clusters_by_nid = 0;
    foreach ($nid_groups as $k => $arr) {
        if (count($arr) > 1) $sibling_clusters_by_nid++;
    }

    $sibling_clusters_by_mobile = 0;
    foreach ($mobile_groups as $k => $arr) {
        if (count($arr) > 1) $sibling_clusters_by_mobile++;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_students' => $total_students,
            'with_fnid' => $with_fnid,
            'with_mnid' => $with_mnid,
            'with_any_nid' => $with_any_nid,
            'missing_nid' => $total_students - $with_any_nid,
            'with_fmobile' => $with_fmobile,
            'with_any_mobile' => $with_any_mobile,
            'missing_mobile' => $total_students - $with_any_mobile,
            'voter_assigned_students' => $voter_assigned_count,
            'total_unique_voters' => count($unique_voters),
            'sibling_clusters_by_nid' => $sibling_clusters_by_nid,
            'sibling_clusters_by_mobile' => $sibling_clusters_by_mobile,
            'sessionyear' => $sessionyear,
            'session_pattern' => $session_pattern
        ]
    ]);
    exit;
}

if ($action === 'check_nid') {
    $nid_clusters = [];
    $missing_nid_list = [];
    $invalid_nid_list = [];

    foreach ($students as $st) {
        $fnid_raw = trim($st['fnid'] ?? '');
        $mnid_raw = trim($st['mnid'] ?? '');
        $fnid = clean_nid($fnid_raw);
        $mnid = clean_nid($mnid_raw);

        if (empty($fnid_raw) && empty($mnid_raw)) {
            $missing_nid_list[] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear'],
                'father' => $st['fname'],
                'mother' => $st['mname'],
                'mobile' => $st['fmobile'] ?: $st['mmobile']
            ];
        }

        if (!empty($fnid_raw) && strlen(preg_replace('/\D/', '', $fnid_raw)) < 10) {
            $invalid_nid_list[] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'type' => 'Father NID',
                'raw_nid' => $fnid_raw,
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear']
            ];
        }

        if (!empty($mnid_raw) && strlen(preg_replace('/\D/', '', $mnid_raw)) < 10) {
            $invalid_nid_list[] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'type' => 'Mother NID',
                'raw_nid' => $mnid_raw,
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear']
            ];
        }

        if (!empty($fnid)) {
            $nid_clusters[$fnid]['type'] = "Father NID ($fnid)";
            $nid_clusters[$fnid]['guardian_name'] = $st['fname'] ?: $st['fnameben'];
            $nid_clusters[$fnid]['students'][] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear']
            ];
        }
    }

    // Filter clusters having > 1 students (true sibling groups)
    $multiple_nid_groups = [];
    foreach ($nid_clusters as $nid => $info) {
        if (count($info['students']) > 1) {
            $multiple_nid_groups[] = [
                'nid' => $nid,
                'type' => $info['type'],
                'guardian_name' => $info['guardian_name'],
                'count' => count($info['students']),
                'students' => $info['students']
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'multiple_nid_groups' => $multiple_nid_groups,
            'invalid_nid_list' => $invalid_nid_list,
            'missing_nid_list' => $missing_nid_list
        ]
    ]);
    exit;
}

if ($action === 'check_mobile') {
    $mobile_clusters = [];
    $missing_mobile_list = [];
    $invalid_mobile_list = [];

    foreach ($students as $st) {
        $fm_raw = trim($st['fmobile'] ?? '');
        $mm_raw = trim($st['mmobile'] ?? '');
        $gm_raw = trim($st['guarmobile'] ?? '');

        $fm = clean_phone($fm_raw);
        $mm = clean_phone($mm_raw);
        $gm = clean_phone($gm_raw);

        $primary_phone = $fm ?: ($mm ?: $gm);

        if (empty($fm_raw) && empty($mm_raw) && empty($gm_raw)) {
            $missing_mobile_list[] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear'],
                'father' => $st['fname']
            ];
        }

        if (!empty($fm_raw) && (strlen(preg_replace('/\D/', '', $fm_raw)) < 11 || strlen(preg_replace('/\D/', '', $fm_raw)) > 13)) {
            $invalid_mobile_list[] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'type' => 'Father Mobile',
                'raw_mobile' => $fm_raw,
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear']
            ];
        }

        if (!empty($primary_phone)) {
            $mobile_clusters[$primary_phone]['phone'] = $primary_phone;
            $mobile_clusters[$primary_phone]['father'] = $st['fname'] ?: $st['fnameben'];
            $mobile_clusters[$primary_phone]['village'] = $st['previll'];
            $mobile_clusters[$primary_phone]['students'][] = [
                'stid' => $st['stid'],
                'name' => $st['stnameeng'] ?: $st['stnameben'],
                'class' => $st['classname'],
                'section' => $st['sectionname'],
                'roll' => $st['rollno'],
                'sessionyear' => $st['sessionyear'],
                'father' => $st['fname']
            ];
        }
    }

    $multiple_mobile_groups = [];
    foreach ($mobile_clusters as $phone => $info) {
        if (count($info['students']) > 1) {
            $multiple_mobile_groups[] = [
                'phone' => $phone,
                'father' => $info['father'],
                'village' => $info['village'],
                'count' => count($info['students']),
                'students' => $info['students']
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'multiple_mobile_groups' => $multiple_mobile_groups,
            'invalid_mobile_list' => $invalid_mobile_list,
            'missing_mobile_list' => $missing_mobile_list
        ]
    ]);
    exit;
}

if ($action === 'preview_siblings') {
    // Disjoint Set (Union-Find) for clustering
    $parent = [];
    foreach ($students as $idx => $st) {
        $parent[$idx] = $idx;
    }

    function find_root(&$parent, $i) {
        if ($parent[$i] == $i) return $i;
        $parent[$i] = find_root($parent, $parent[$i]);
        return $parent[$i];
    }

    function union_sets(&$parent, $i, $j) {
        $root_i = find_root($parent, $i);
        $root_j = find_root($parent, $j);
        if ($root_i != $root_j) {
            $parent[$root_i] = $root_j;
        }
    }

    // Index by NID and Mobile
    $nid_to_indices = [];
    $mobile_to_indices = [];

    foreach ($students as $idx => $st) {
        $fnid = clean_nid($st['fnid']);
        $mnid = clean_nid($st['mnid']);
        $fm = clean_phone($st['fmobile']);
        $mm = clean_phone($st['mmobile']);
        $gm = clean_phone($st['guarmobile']);

        if (!empty($fnid)) $nid_to_indices[$fnid][] = $idx;
        if (!empty($mnid)) $nid_to_indices[$mnid][] = $idx;

        if (!empty($fm)) $mobile_to_indices[$fm][] = $idx;
        if (!empty($mm)) $mobile_to_indices[$mm][] = $idx;
        if (!empty($gm)) $mobile_to_indices[$gm][] = $idx;
    }

    // Connect NID matches (Priority 1)
    foreach ($nid_to_indices as $nid => $indices) {
        $first = $indices[0];
        for ($k = 1; $k < count($indices); $k++) {
            union_sets($parent, $first, $indices[$k]);
        }
    }

    // Connect Mobile matches (Priority 2 - with basic sanity)
    foreach ($mobile_to_indices as $phone => $indices) {
        $first = $indices[0];
        for ($k = 1; $k < count($indices); $k++) {
            $curr = $indices[$k];
            // Cross check: father name similarity or village similarity or empty check
            $f1 = strtolower(trim($students[$first]['fname'] ?? ''));
            $f2 = strtolower(trim($students[$curr]['fname'] ?? ''));
            $v1 = strtolower(trim($students[$first]['previll'] ?? ''));
            $v2 = strtolower(trim($students[$curr]['previll'] ?? ''));

            $name_match = (empty($f1) || empty($f2) || soundex($f1) == soundex($f2) || levenshtein($f1, $f2) <= 4 || str_contains($f1, $f2) || str_contains($f2, $f1));
            $village_match = (empty($v1) || empty($v2) || $v1 === $v2);

            if ($name_match || $village_match) {
                union_sets($parent, $first, $curr);
            }
        }
    }

    // Group into clusters
    $clusters = [];
    foreach ($students as $idx => $st) {
        $root = find_root($parent, $idx);
        $clusters[$root][] = $st;
    }

    // Filter only sibling clusters (count > 1)
    $sibling_groups = [];
    foreach ($clusters as $root => $members) {
        if (count($members) > 1) {
            $primary = $members[0];
            $sibling_groups[] = [
                'guardian_name' => $primary['fname'] ?: ($primary['mname'] ?: $primary['guarname']),
                'nid' => $primary['fnid'] ?: $primary['mnid'],
                'mobile' => $primary['fmobile'] ?: ($primary['mmobile'] ?: $primary['guarmobile']),
                'village' => $primary['previll'],
                'count' => count($members),
                'children' => array_map(function($m) {
                    return [
                        'stid' => $m['stid'],
                        'name' => $m['stnameeng'] ?: $m['stnameben'],
                        'class' => $m['classname'],
                        'section' => $m['sectionname'],
                        'roll' => $m['rollno'],
                        'sessionyear' => $m['sessionyear'],
                        'current_voter_no' => $m['voter_no']
                    ];
                }, $members)
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_sibling_groups' => count($sibling_groups),
            'sibling_groups' => $sibling_groups
        ]
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
exit;
