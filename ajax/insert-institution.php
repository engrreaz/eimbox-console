<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../core/config.php';
require_once '../core/db.php';

// ================== INPUT ==================
$sccode     = trim($_POST['sccode'] ?? '');
$scname     = trim($_POST['scname'] ?? '');
$sccategory = trim($_POST['sccategory'] ?? '');
$scadd1     = trim($_POST['scadd1'] ?? '');
$scadd2     = trim($_POST['scadd2'] ?? '');
$ps         = trim($_POST['ps'] ?? '');
$dist       = trim($_POST['dist_name'] ?? '');
$mobile     = trim($_POST['mobile'] ?? '');
$scmail     = trim($_POST['scmail'] ?? '');
$headname   = trim($_POST['headname'] ?? '');
$headtitle  = trim($_POST['headtitle'] ?? '');
$rootuser   = trim($_POST['rootuser'] ?? '');
$entryby    = $_SESSION['user'] ?? 'system';

// $dist_id = $_POST['dist'] ?? '';
// $dist_name = $_POST['dist_name'] ?? '';
// ================== STATIC ==================
$package_id    = '2';
$package_name  = 'Trial';
$tier          = 'A';
$billing_data  = '';
$valid_module  = $active_module = 'Student | Examination | Result | Payment | Attendance | Gradebook | Finance';
$valid_panel   = $active_panel  = 'Administrator | Chief | Teacher';

// ================== VALIDATION ==================
if (!$sccode || !$scname) {
    exit('Error: sccode and scname are required.');
}

// ================== DUPLICATE CHECK ==================
$check = $conn->prepare("SELECT sccode FROM scinfo WHERE sccode = ?");
$check->bind_param("s", $sccode);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    exit('Error: Institution code already exists.');
}
$check->close();

// ================== TRANSACTION START ==================
$conn->begin_transaction();

try {

    // ================== INSERT INTO scinfo ==================
    $sql = "INSERT INTO scinfo 
    (sccode, scname, sccategory, scadd1, scadd2, ps, dist, mobile, scmail, headname, headtitle, rootuser, tier, billing_data, valid_module, active_module, valid_panel, active_panel, package_id, package_name)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt1 = $conn->prepare($sql);

    $stmt1->bind_param(
        "ssssssssssssssssssss",
        $sccode,
        $scname,
        $sccategory,
        $scadd1,
        $scadd2,
        $ps,
        $dist,
        $mobile,
        $scmail,
        $headname,
        $headtitle,
        $rootuser,
        $tier,
        $billing_data,
        $valid_module,
        $active_module,
        $valid_panel,
        $active_panel,
        $package_id,
        $package_name
    );

    if (!$stmt1->execute()) {
        throw new Exception("scinfo insert failed: " . $stmt1->error);
    }
    $stmt1->close();

    // ================== USER CREATE ==================
    $pin = rand(100000, 999999);
    $password = $pin;

    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

    $ulv = 'Administrator';
    $uid = $sccode . '9999';
    $pid = '';
    $actv = 1;
    $chief = 1;

    $token = uniqid();
    $rt = $token;

    $now = date('Y-m-d H:i:s');

    $stmt2 = $conn->prepare("INSERT INTO usersapp 
    (sccode, email, password_hash, created_at, userlevel, userid, photourl, status, fixedpin, reset_token_hash, reset_token_expires, token, password_salt, is_chief)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt2->bind_param(
        "sssssssisssssi",
        $sccode,
        $scmail,
        $hashedPassword,
        $now,
        $ulv,
        $uid,
        $pid,
        $actv,
        $pin,
        $rt,
        $now,
        $token,
        $password, $chief
    );

    if (!$stmt2->execute()) {
        throw new Exception("usersapp insert failed: " . $stmt2->error);
    }
    $stmt2->close();

    // ================== COMMIT ==================
    $conn->commit();

    echo "SUCCESS|Password: " . $password;

} catch (Exception $e) {

    $conn->rollback();
    echo "ERROR: " . $e->getMessage();
}