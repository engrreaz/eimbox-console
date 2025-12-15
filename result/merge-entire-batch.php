<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

set_time_limit(0);

$slot    = $_POST['slot'];
$session = $_POST['session'];
$batch   = intval($_POST['batchSize'] ?? 1);
$cls     = $_POST['classname'] ?? '';
$sec     = $_POST['sectionname'] ?? '';
$subcode = $_POST['subcode'] ?? '';

$data = '';

// exams + rate
$selectedExams = [];
$examRates = [];

if (!empty($_COOKIE['examitems'])) {
    $selectedExams = explode(",", $_COOKIE['examitems']);
    foreach ($selectedExams as $ex) {
        $key = "rate_" . str_replace(" ", "_", $ex);
        $examRates[$ex] = isset($_COOKIE[$key]) ? floatval($_COOKIE[$key]) / 100 : 1;
    }
}

$where = "sessionyear='$session'
          AND slot='$slot'
          AND sccode='$sccode'
          AND grand_merged=0";

if ($cls !== '') $where .= " AND classname='$cls'";
if ($sec !== '') $where .= " AND sectionname='$sec'";

/* total students */
$qTotal = "SELECT COUNT(*) cnt FROM sessioninfo WHERE $where";
$total  = mysqli_fetch_assoc(mysqli_query($conn, $qTotal))['cnt'];

/* fetch NEXT batch (no OFFSET) */
$q = "
SELECT stid, classname, sectionname
FROM sessioninfo
WHERE $where
LIMIT $batch
";
$res = mysqli_query($conn, $q);

$processed = 0;

while ($stu = mysqli_fetch_assoc($res)) {

    mergeStudent(
        $stu['stid'],
        $stu['classname'],
        $stu['sectionname'],
        $slot,
        $session,
        $sccode,
        $usr,
        $conn,
        $selectedExams,
        $examRates,
        $subcode,
        $data
    );

    $processed++;
}

/* how many merged so far */
$qMerged = "
SELECT COUNT(*) cnt
FROM sessioninfo
WHERE sessionyear='$session'
AND slot='$slot'
AND sccode='$sccode'
AND grand_merged=1
";
$merged = mysqli_fetch_assoc(mysqli_query($conn, $qMerged))['cnt'];

echo json_encode([
    'done'   => true,
    'merged'=> $merged,
    'total' => $total,
    'hasMore' => ($merged < $total),
    'data'  => $data
]);
