<?php
session_start();

require_once dirname(dirname(dirname(__FILE__))) . '/core/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/db.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/global_values.php';

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';
$stid = isset($_GET['stid']) ? $conn->real_escape_string($_GET['stid']) : '';

$sql = "SELECT * FROM tabulatingsheet 
        WHERE stid='$stid' AND id='$id' 
        ORDER BY id DESC LIMIT 1";
$row = $conn->query($sql)->fetch_assoc();

/* subject list */
$subjectList = array_filter(explode('.', $row['allsubject']));

$sqlSub = "SELECT subcode, subject 
           FROM subjects 
           WHERE sccategory='$sctype'
             AND (sccode=0 OR sccode='$sccode') ORDER BY subcode, sccode DESC";

             echo $sqlSub;
$resSub = $conn->query($sqlSub);

$subjectLists = [];
while ($r = $resSub->fetch_assoc()) {
    $subjectLists[$r['subcode']] = $r['subject'];
}

/* session + profile */
$sessionInfo = $conn->query(
    "SELECT * FROM sessioninfo 
     WHERE stid='$stid' AND sccode='$sccode' 
     ORDER BY id DESC LIMIT 1"
)->fetch_assoc();

$profileInfo = $conn->query(
    "SELECT * FROM students 
     WHERE stid='$stid' AND sccode='$sccode' 
     ORDER BY id DESC LIMIT 1"
)->fetch_assoc();

/* build subject array */
$subjectIndexMap = [];
for ($i = 1; $i <= 15; $i++) {
    if (!empty($row["sub_$i"])) {
        $subjectIndexMap[$row["sub_$i"]] = $i;
    }
}

$subjects = [];
foreach ($subjectList as $code) {
    if (!isset($subjectIndexMap[$code]))
        continue;
    $i = $subjectIndexMap[$code];

    $subjects[] = [
        'code' => $code,
        'subj' => $row["sub_{$i}_sub"],
        'obj' => $row["sub_{$i}_obj"],
        'pra' => $row["sub_{$i}_pra"],
        'ca' => $row["sub_{$i}_ca"],
        'ct' => $row["sub_{$i}_ct"],
        'mt' => $row["sub_{$i}_mt"],
        'total' => $row["sub_{$i}_total"],
        'gp' => $row["sub_{$i}_gp"],
        'gl' => $row["sub_{$i}_gl"],
    ];
}

$gpa = $row['gpa'];
$gla = $row['gla'];
$totalmarks = $row['totalmarks'];

var_dump($subjectLists);