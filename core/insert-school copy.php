<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'core-val.php';
require_once 'global_values.php';

header('Content-Type: application/json');

$eiin = trim($_POST['eiin'] ?? '');
$sctype = trim($_POST['sctype'] ?? '');
$url = trim($_POST['url'] ?? '');
$scname = trim($_POST['area'] ?? '');
$address1 = trim($_POST['address1'] ?? '');
$address2 = trim($_POST['address2'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$scmobile = trim($_POST['mobile'] ?? '');
$scmail = trim($_POST['pincode'] ?? '');
$rootuser = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

$token = bin2hex(random_bytes(32));

$hash = password_hash($token, PASSWORD_DEFAULT);
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ✅ EIIN আগে থেকেই আছে কিনা চেক
$stmt = $conn->prepare("SELECT id FROM scinfo WHERE sccode = ?");
$stmt->bind_param("s", $eiin);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'EIIN already registered!']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

if ($rootuser == '') {
    $words = preg_split('/\s+/', $scname); // স্পেস দিয়ে ভাগ
    $acronym = '';
    foreach ($words as $w) {
        if ($w !== '' && $w !== '&') {
            $acronym .= strtoupper($w[0]);
        }
    }
    $rootuser = strtolower($acronym) . $eiin;
}

// ✅ EIIN আগে থেকেই আছে কিনা চেক
$stmt = $conn->prepare("SELECT rootuser FROM scinfo WHERE rootuser = ?");
$stmt->bind_param("s", $rootuser);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $words = preg_split('/\s+/', $scname); // স্পেস দিয়ে ভাগ
    $acronym = '';
    foreach ($words as $w) {
        if ($w !== '' && $w !== '&') {
            $acronym .= strtoupper($w[0]);
        }
    }
    $rootuser = strtolower($acronym) . $eiin;
}
$stmt->close();

// ✅ ইনপুট যাচাই
if ($eiin == '' || $sctype == '' || $rootuser == '') {
    echo json_encode(['success' => false, 'message' => 'Some information may be missing']);
    exit;
}

// ✅ প্রথম টেবিল: scinfo
$naw = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO scinfo 
    (sccode, sccategory, scname, scadd1, scadd2, ps, dist, 
    mobile, scmail, rootuser, modifieddate, reg_hash, hash_expire)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssssssss",
    $eiin,
    $sctype,
    $scname,
    $address1,
    $address2,
    $city,
    $state,
    $scmobile,
    $scmail,
    $rootuser,
    $naw,
    $hash,
    $expires
);

// if (!$stmt->execute()) {
//     echo json_encode(['success' => false, 'message' => 'Institution registration process has been failed.']);
//     $stmt->close();
//     $conn->close();
//     exit;
// }
$stmt->close();






// ✅ দ্বিতীয় টেবিল: usersapp
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
$ulv = 'Administrator';
$uid = $eiin . '9999';
$pid = ''; // null হলে খালি string
$pin = rand(100000, 999999);
$actv = 1;

$stmt = $conn->prepare("INSERT INTO usersapp 
    (sccode, email, password_hash, created_at, 
    userlevel, userid, photourl, status, fixedpin)
    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssssssis",
    $eiin,
    $email,
    $hashedPassword,
    $ulv,
    $uid,
    $pid,
    $actv,
    $pin
);


// if ($stmt->execute()) {
//     echo json_encode(['success' => true, 'message' => '✅ Institution has been registered successfully.']);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Administrator registration failed.']);
// }

$stmt->close();
$conn->close();


// ---------------------------------------------------------------
// ---------------------------------------------------------------
$resetLink = APP_PATH . "register-new-ins.php?eiin=" . urlencode($eiin) . "&token=$token";

// echo json_encode(['success' => false, 'message' => $resetLink]);


$subject = "Password Reset Request";
$message = "Click the link to reset your password: $resetLink\nThis link will expire in 1 hour.";
$mail_type = 'new-ins';
$mail_to = $email;
$mail_name = $scname;
$mail_subject = 'Register New Institute';
$mail_attach = '';
$msg_success = "Account activation mail has been send to your mail $mail_to";

// $resetLink = dirname(__DIR__) . '/mailer/send-mail.php';
include( dirname(__DIR__) . '/mailer/send-mail.php');

echo json_encode(['success' => true, 'message' => $msg_success]);
$_SESSION['msg'] = $msg_success;

// ---------------------------------------------------------------
// ---------------------------------------------------------------