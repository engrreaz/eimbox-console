<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

header("Content-Type: application/json");

// ===============================
// Incoming parameters
// ===============================
$classname = $_POST['class'] ?? '';
$sectionname = $_POST['section'] ?? '';
$rollno = $_POST['rollno'] ?? '';
$sessionyear = $_POST['session'] ?? '';
$slot = $_POST['slot'] ?? '';
$medium = $_POST['medium'] ?? '';
$version = $_POST['version'] ?? '';

if (!$classname || !$sectionname || !$rollno || !$sessionyear) {
    echo json_encode(["status" => "error", "msg" => "Missing parameters"]);
    exit;
}

// ===============================
// 1. FIRST TRY: SESSIONINFO lookup
// ===============================
$sqlSession = "
    SELECT * FROM sessioninfo 
    WHERE sccode='$sccode'
      AND classname='$classname'
      AND sectionname='$sectionname'
      AND rollno='$rollno'
      AND sessionyear='$sessionyear'
      AND slot='$slot'
    LIMIT 1;
";

$resSession = $conn->query($sqlSession);

// ========== CASE A: FOUND ============
if ($resSession->num_rows > 0) {

    $s = $resSession->fetch_assoc();
    $stid = $s['stid'];

    // Basic session values
    $sessionData = [
        "classname" => $s["classname"],
        "section" => $s["sectionname"],
        "rollno" => $s["rollno"],
        "sessionyear" => $s["sessionyear"],
        "slot" => $s["slot"],
        "medium" => $medium,
        "version" => $version,
        "waiver" => 100 - $s["rate"],
        "quota" => $s["sector"],
        "stid" => $stid,
        "rfid" => $s["rfidtag"],
    ];

    $new = 0;
}

// ========== CASE B: NOT FOUND → NEW STUDENT ============
else {

    // ---- Generate New STID ----
    $qLast = "SELECT stid FROM students WHERE sccode='$sccode' ORDER BY stid DESC LIMIT 1";
    $rs = $conn->query($qLast);

    if ($rs->num_rows > 0) {
        $stid = $rs->fetch_assoc()["stid"] + 1;
    } else {
        $stid = $sccode * 10000 + 1;
    }

    // session default
    $sessionData = [
        "classname" => $classname,
        "section" => $sectionname,
        "rollno" => $rollno,
        "sessionyear" => $sessionyear,
        "slot" => $slot,
        "medium" => $medium,
        "version" => $version,
        "waiver" => 0,
        "quota" => "",
        "stid" => $stid, 
        "rfid" => ""
    ];

    $new = 1;
}

// ===============================
// 2. Fetch Student Info (if exists)
// ===============================
$sqlStd = "SELECT * FROM students WHERE stid='$stid' AND sccode='$sccode' LIMIT 1";
$rStd = $conn->query($sqlStd);

$studentData = [];

if ($rStd->num_rows > 0) {
    $studentData = $rStd->fetch_assoc();
} else {
    // EMPTY STRUCTURE FOR NEW STUDENT
    $studentData = [
        "stnameeng" => "",
        "stnameben" => "",
        "fname" => "",
        "fnameben" => "",
        "fprof" => "",
        "fmobile" => "",
        "fnid" => "",
        "falive" => 0,
        "mname" => "",
        "mnameben" => "",
        "mprof" => "",
        "mmobile" => "",
        "mnid" => "",
        "malive" => 0,
        "previll" => "",
        "prepo" => "",
        "preps" => "",
        "predist" => "",
        "pervill" => "",
        "perpo" => "",
        "perps" => "",
        "perdist" => "",
        "dob" => "",
        "religion" => "",
        "brn" => "",
        "gender" => "",
        "guarname" => "",
        "guarnameben" => "",
        "guarrelation" => "",
        "guaradd" => "",
        "guarmobile" => "",
        "guarmobile2" => "",
        "guaremail" => "",
        "guaremail2" => "",
        "guarnid" => "",
        "tcno" => "",
        "preins" => "",
        "preinsadd" => "",
        "doa" => "",
        "photo_id" => "",
        "photo_pick_date" => "",
        "sscpassyear" => "",
        "regdno" => "",
        "rollno" => "",
        "gpa" => "",
        "bgroup" => "",
        "height" => "",
        "weight" => "",
        "disables" => "",
        "icardno" => "",
        "mobileself" => "",
        "uniqueid" => ""
    ];
}

$studentData['rollno'] = $rollno;
$studentData['waiver'] = $sessionData['waiver'];
$studentData['quota'] = $sessionData['quota'];
$studentData['rfid'] = $sessionData['rfid'];
// Final Output
echo json_encode([
    "status" => "found",
    "new" => $new,
    "session" => $sessionData,
    "data" => $studentData
]);
exit;
