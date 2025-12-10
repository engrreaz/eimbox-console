<?php
session_start();
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

header('Content-Type: application/json'); // JSON must be declared BEFORE any output

$sccode = $_POST['sccode'] ?? '';
$session = $_POST['session'] ?? '';
$unit = $_POST['unit'] ?? '';
$class = $_POST['class'] ?? '';
$section = $_POST['section'] ?? '';
$roll = $_POST['roll'] ?? '';
$teacher = $_POST['teacher'] ?? '';

// Validation Query
$sql = "SELECT * FROM sessioninfo
        WHERE sccode='$sccode'
        AND sessionyear='$session'
        AND slot='$unit'
        AND classname='$class'
        AND sectionname='$section'
        AND rollno='$roll'
        LIMIT 1";

$q = mysqli_query($conn, $sql);

if (!$q) {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    exit;
}

if (mysqli_num_rows($q) == 1) {

    $std = mysqli_fetch_assoc($q);

    $_SESSION['guest_login'] = true;
    $_SESSION['stid'] = $std['stid'];


    $_SESSION['user_id'] = $std['stid'];
    $_SESSION['user_email'] = $std['stid'];
    $_SESSION['user_name'] = $std['stid'];
    $_SESSION['userid'] = '';
    $_SESSION['first_name'] = '';
    $_SESSION['last_name'] = '';
    $_SESSION['phone'] = '';
    $_SESSION['address'] = '';
    $_SESSION['dob'] = '';
    $_SESSION['user_role'] = 'guest';

    $_SESSION['userlevel'] = 'guest';

    $_SESSION['sccode'] = $sccode;
    $_SESSION['photourl'] = '';
    $_SESSION['isadmin'] = 0;
    $_SESSION['page_status_grant'] = $user['page_status_grant'] ?? 6;
    $_SESSION['fullname'] = '';

    $_SESSION['locktime'] = 10000; //$user['admin'] ?? 0;

    $sqlg = "SELECT * FROM scinfo
        WHERE sccode='$sccode'
        LIMIT 1";

    $qg = mysqli_query($conn, $sqlg);

    if (mysqli_num_rows($qg) == 1) {

        $school = mysqli_fetch_assoc($qg);
        // স্কুল ইনফো
        $_SESSION['scname'] = $school['scname'] ?? '';
        $_SESSION['sccategory'] = $school['sccategory'] ?? '';
        $_SESSION['admin_data'] = $school['admin_data'] ?? '';
        $_SESSION['package_id'] = $school['package_id'] ?? 2;
        $_SESSION['scaddress_top'] = $school['ps'] . ', ' . $school['dist'];
        $_SESSION['scaddress_top_full'] = str_replace(', ,', ', ', $school['scadd1'] . ', ' . $school['scadd2'] . ', ' . $school['ps'] . ', ' . $school['dist']);

        $_SESSION['rootuser'] = $school['rootuser'];
        $_SESSION['scmobile'] = $school['mobile'];
        $_SESSION['sms_gateway'] = $school['sms_gateway'] ?? '';

        $_SESSION['valid_module'] = $school['valid_module'] ?? '';
        $_SESSION['active_module'] = $school['active_module'] ?? '';
    }

    setcookie("gl_sccode", $sccode, time() + 86400, "/");
    setcookie("gl_session", $session, time() + 86400, "/");
    setcookie("gl_class", $class, time() + 86400, "/");

    echo json_encode(["status" => "ok"]);
    exit;

} else {
    echo json_encode(["status" => "error", "message" => "Invalid login information"]);
    exit;
}
