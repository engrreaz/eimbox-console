<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

/* =======================
   INPUT
======================= */
$sy = $_POST['sy'] ?? '';
$type = $_POST['type'] ?? '';
$part = $_POST['part'] ?? '';
$icode = $_POST['icode'] ?? '';
$stid = $_POST['stid'] ?? '';
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';

$startTime = time();

$new = $noneed = $update = 0;

/* =======================
   GET ONE STUDENT (QUEUE)
======================= */
$where = "sccode='$sccode' AND sessionyear LIKE '%$sy%' AND validate=0";

if ($stid)
    $where .= " AND stid='$stid'";
elseif ($sec)
    $where .= " AND classname='$cls' AND sectionname='$sec'";
elseif ($cls)
    $where .= " AND classname='$cls'";

$sql = "SELECT id, stid, classname, sectionname, rollno, rate, sessionyear
        FROM sessioninfo
        WHERE $where
        ORDER BY id
        LIMIT 1";

$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo '<div id="totaltotal" hidden>0</div>';
    exit;
}

$st = $res->fetch_assoc();
$stid = $st['stid'];
$cls = strtolower($st['classname']);
$sec = strtolower($st['sectionname']);
$roll = $st['rollno'];
$rate = $st['rate'];
$syear = $st['sessionyear'];

/* =======================
   RESET STUDENT FINANCE
======================= */
if ($part === 'icode' && $icode) {
    $conn->query("UPDATE stfinance SET validate=0
                  WHERE stid='$stid' AND sccode='$sccode'
                  AND sessionyear LIKE '%$syear%' AND itemcode='$icode'");
} else {
    $conn->query("UPDATE stfinance SET validate=0
                  WHERE stid='$stid' AND sccode='$sccode'
                  AND sessionyear LIKE '%$syear%'");
}

/* =======================
   LOAD SETUP
======================= */

$finsetupind = array();
$sql0x = "SELECT * FROM financesetupind where sccode='$sccode' and sessionyear LIKE '%$y_v2%' and stid='$stid' order by slno;";
// echo $sql0x;
$result0xvalstt = $conn->query($sql0x);
if ($result0xvalstt->num_rows > 0) {
    while ($row0x = $result0xvalstt->fetch_assoc()) {
        $finsetupind[] = $row0x;
    }
}
// var_dump($finsetupind);


$finSetup = [];
$q = "SELECT id,itemcode,month,particulareng,particularben
      FROM financesetup
      WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
      ORDER BY id";
$r = $conn->query($q);
while ($row = $r->fetch_assoc())
    $finSetup[] = $row;

/* =======================
   LOAD VALUES
======================= */
$finValues = [];
$q = "SELECT itemcode,classname,sectionname,amount
      FROM financesetupvalue
      WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND amount >0
      ORDER BY itemcode,classname DESC,sectionname DESC";
$r = $conn->query($q);
while ($row = $r->fetch_assoc())
    $finValues[] = $row;

/* =======================
   EXISTING STUDENT FINANCE
======================= */
$stFinance = [];
$q = "SELECT * FROM stfinance
      WHERE stid='$stid' AND sccode='$sccode'
      AND sessionyear LIKE '%$sy%'";
$r = $conn->query($q);
while ($row = $r->fetch_assoc())
    $stFinance[] = $row;



function getMonthsFromRule(int $monthRule): array
{
    // প্রতি মাসে
    if ($monthRule === 0) {
        return range(1, 12);
    }

    // নির্দিষ্ট মাস
    if ($monthRule >= 1 && $monthRule <= 12) {
        return [$monthRule];
    }

    // interval logic (22,33,44,66...)
    if ($monthRule > 12) {
        $step = intval(substr((string) $monthRule, 0, 1));
        $months = [];
        for ($m = 1; $m <= 12; $m += $step) {
            $months[] = $m;
        }
        return $months;
    }

    return [];
}


/* =======================
   PROCESS FINANCE
======================= */
foreach ($finSetup as $fs) {

    $itemcode = $fs['itemcode'];
    $monthRule = (int) $fs['month'];
    $partid = $fs['id'];
    $partex = $fs['particulareng'];
    $partbx = $fs['particularben'];

    /* ======================
       AMOUNT PRIORITY
    ====================== */
    $amt = 0;
    foreach ($finValues as $fv) {
        if ($fv['itemcode'] != $itemcode)
            continue;

        if ($fv['classname'] == $cls && $fv['sectionname'] == $sec) {
            $amt = $fv['amount'];
            break;
        }
        if ($fv['classname'] == $cls && $fv['sectionname'] == '') {
            $amt = $fv['amount'];
        }
        if ($fv['classname'] == '' && $fv['sectionname'] == '') {
            $amt = $fv['amount'];
        }
    }

    if ($amt == 0)
        continue;

    // Tuition percentage
    if (stripos($partex, 'tution') !== false) {
        $amt = ($amt * $rate) / 100;
    }

    /* ======================
       MONTH RESOLUTION
    ====================== */
    $months = getMonthsFromRule($monthRule);

    foreach ($months as $z) {

        // existing finance detect
        $stfinid = 0;
        $paid = 0;

        foreach ($stFinance as $sf) {
            if ($sf['itemcode'] == $itemcode && (int) $sf['month'] === $z) {
                $stfinid = $sf['id'];
                $paid = $sf['paid'];
                break;
            }
        }

        include 'check-student-finance-update.php';
    }
}

echo ' -- ' . $new . ' | ' . $update . ' | ' . $noneed;

/* =======================
   FINALIZE STUDENT
======================= */

$conn->query("DELETE FROM stfinance
              WHERE stid='$stid' AND sccode='$sccode'
              AND sessionyear LIKE '%$sy%' AND validate=0");

$conn->query("UPDATE sessioninfo
              SET validate=1, validationtime='$cur'
              WHERE stid='$stid' AND sccode='$sccode'
              AND sessionyear LIKE '%$sy%'");

/* =======================
   REMAINING COUNT
======================= */
$q = $conn->query("SELECT COUNT(*) c FROM sessioninfo WHERE $where");
$left = $q->fetch_assoc()['c'];

/* =======================
   OUTPUT (AJAX SAFE)
======================= */
$time = time() - $startTime;

echo "<div>
        <span class='text-muted'>" . date('H:i:s') . "</span>
        → $cls ($sec) | Roll $roll | $stid
        <small class='text-info'> {$time}s</small>
      </div>";

echo "<div id='totaltotal' hidden>$left</div>";
