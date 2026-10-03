<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) && !isset($sccode)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$slot = mysqli_real_escape_string($conn, trim($_POST['slot'] ?? ''));
$session = mysqli_real_escape_string($conn, trim($_POST['session'] ?? ''));
$exam = mysqli_real_escape_string($conn, trim($_POST['exam'] ?? ''));
$class = mysqli_real_escape_string($conn, trim($_POST['class'] ?? ''));
$section = mysqli_real_escape_string($conn, trim($_POST['section'] ?? ''));
$subject = mysqli_real_escape_string($conn, trim($_POST['subject'] ?? ''));

$offset = intval($_POST['offset'] ?? 0);
$batchSize = intval($_POST['batchSize'] ?? 25);
if ($batchSize <= 0) $batchSize = 25;

if (empty($slot) || empty($session) || empty($exam)) {
    echo json_encode(['success' => false, 'message' => 'Slot, Session and Exam are required']);
    exit;
}

// 1. Fetch Decimal configuration from slots
$sqly = "SELECT decimal_mark FROM slots WHERE sccode='$sccode' AND slotname = '$slot' LIMIT 1";
$resy = mysqli_query($conn, $sqly);
$rowy = mysqli_fetch_assoc($resy);
$decimal = intval($rowy['decimal_mark'] ?? 0);

// 2. Fetch min pass threshold from gpa (default 33)
$sql_gpa = "SELECT maxvalues FROM gpa 
            WHERE (sccode='$sccode' OR sccode = '0') 
            AND (slot IS NULL OR slot = '' OR slot = '$slot')
            AND gp=0
            ORDER BY sccode DESC, slot DESC LIMIT 1";
$res_gpa = mysqli_query($conn, $sql_gpa);
$row_gpa = mysqli_fetch_assoc($res_gpa);
$min = isset($row_gpa['maxvalues']) ? (floor($row_gpa['maxvalues']) + 1) : 33;

// 3. Build WHERE condition for stmark
$where = "sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND exam='$exam'";
if ($class !== '') $where .= " AND classname='$class'";
if ($section !== '') $where .= " AND sectionname='$section'";
if ($subject !== '') $where .= " AND subject='$subject'";

// 4. Total count calculation on first batch
$totalCount = intval($_POST['totalCount'] ?? -1);
if ($totalCount < 0) {
    $q_cnt = "SELECT COUNT(*) AS total FROM stmark WHERE $where";
    $res_cnt = mysqli_query($conn, $q_cnt);
    $row_cnt = mysqli_fetch_assoc($res_cnt);
    $totalCount = intval($row_cnt['total'] ?? 0);
}

if ($totalCount == 0) {
    echo json_encode([
        'success' => true,
        'done' => true,
        'total' => 0,
        'processed' => 0,
        'passed' => 0,
        'failed' => 0,
        'nextOffset' => null,
        'items' => [],
        'message' => 'No mark records found for the selected criteria.'
    ]);
    exit;
}

// 5. Pre-cache subsetup for quick lookup: key => class_sec_subj
$subsetups = [];
$q_sub = "SELECT * FROM subsetup WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot'";
if ($class !== '') $q_sub .= " AND classname='$class'";
if ($section !== '') $q_sub .= " AND sectionname='$section'";
if ($subject !== '') $q_sub .= " AND subject='$subject'";

$res_sub = mysqli_query($conn, $q_sub);
while ($srow = mysqli_fetch_assoc($res_sub)) {
    $k = $srow['classname'] . '|' . $srow['sectionname'] . '|' . $srow['subject'];
    $subsetups[$k] = $srow;
}

// 6. Fetch batch of stmark records
$q_marks = "SELECT id, stid, classname, sectionname, subject, ctest, mtest, subj, obj, pra, ca, markobt, gp, gl, on100 
            FROM stmark 
            WHERE $where 
            ORDER BY classname ASC, sectionname ASC, subject ASC, stid ASC 
            LIMIT $offset, $batchSize";
$res_marks = mysqli_query($conn, $q_marks);

$items = [];
$batchPassed = 0;
$batchFailed = 0;

while ($mrk = mysqli_fetch_assoc($res_marks)) {
    $id = $mrk['id'];
    $stid = $mrk['stid'];
    $c = $mrk['classname'];
    $s = $mrk['sectionname'];
    $sub = $mrk['subject'];
    
    $k = $c . '|' . $s . '|' . $sub;
    $setup = $subsetups[$k] ?? null;

    if (!$setup) {
        $q_single = "SELECT * FROM subsetup WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND classname='$c' AND sectionname='$s' AND subject='$sub' LIMIT 1";
        $res_single = mysqli_query($conn, $q_single);
        if ($res_single && mysqli_num_rows($res_single) > 0) {
            $setup = mysqli_fetch_assoc($res_single);
            $subsetups[$k] = $setup;
        }
    }

    $sub_full = (float)($setup['subj'] ?? 0);
    $obj_full = (float)($setup['obj'] ?? 0);
    $pra_full = (float)($setup['pra'] ?? 0);
    $full_full = (float)($setup['fullmarks'] ?? 0);
    $alg = (int)($setup['pass_algorithm'] ?? 0);
    $add_ctest = (int)($setup['add_ctest'] ?? 0);
    $add_mtest = (int)($setup['add_mtest'] ?? 0);

    $ct = (float)($mrk['ctest'] ?? 0);
    $mt = (float)($mrk['mtest'] ?? 0);
    $subj_obt = (float)($mrk['subj'] ?? 0);
    $obj_obt = (float)($mrk['obj'] ?? 0);
    $pra_obt = (float)($mrk['pra'] ?? 0);
    $ca_obt = (float)($mrk['ca'] ?? 0);

    // Pass validation
    $p = pass_validation($ct, $mt, $subj_obt, $obj_obt, $pra_obt, $ca_obt, $sub_full, $obj_full, $pra_full, $full_full, $alg, $min, $decimal, $add_ctest, $add_mtest);

    // Calculate total for grade
    $ct_calc = ($add_ctest == 1) ? $ct : 0;
    $mt_calc = ($add_mtest == 1) ? $mt : 0;
    $total_for_grade = $subj_obt + $obj_obt + $pra_obt + $ca_obt + $ct_calc + $mt_calc;

    $on100 = 0;
    if ($full_full > 0 && $total_for_grade > 0) {
        $on100 = $total_for_grade * 100 / $full_full;
    }

    if ($p === false || $p == 0) {
        $new_gp = 0.00;
        $new_gl = 'F';
        $batchFailed++;
    } else {
        $gpgl = get_GP_GL($total_for_grade, $full_full, $slot, $decimal);
        $new_gp = (float)($gpgl['gp'] ?? 0);
        $new_gl = $gpgl['gl'] ?? 'F';
        $batchPassed++;
    }

    $old_gp = (float)$mrk['gp'];
    $old_gl = $mrk['gl'];

    // Update stmark record
    $upd = "UPDATE stmark SET 
            fullmark = '$full_full',
            markobt = '$total_for_grade',
            on100 = '$on100',
            gp = '$new_gp',
            gl = '$new_gl',
            modifieddate = NOW()
            WHERE id = '$id'";
    mysqli_query($conn, $upd);

    $items[] = [
        'id' => $id,
        'stid' => $stid,
        'class' => $c,
        'section' => $s,
        'subject' => $sub,
        'total' => $total_for_grade,
        'fullmarks' => $full_full,
        'old_gp' => $old_gp,
        'old_gl' => $old_gl,
        'new_gp' => $new_gp,
        'new_gl' => $new_gl,
        'status' => ($p === false || $p == 0) ? 'Fail' : 'Pass'
    ];
}

$processedSoFar = $offset + count($items);
$hasMore = ($processedSoFar < $totalCount);
$nextOffset = $hasMore ? $processedSoFar : null;

echo json_encode([
    'success' => true,
    'done' => !$hasMore,
    'total' => $totalCount,
    'offset' => $offset,
    'batchCount' => count($items),
    'processed' => $processedSoFar,
    'passed' => $batchPassed,
    'failed' => $batchFailed,
    'nextOffset' => $nextOffset,
    'items' => $items
]);
