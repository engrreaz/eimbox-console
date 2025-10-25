<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/functions.php';


var_dump($_SESSION);

if (!isset($_SESSION['partial_auth'])) {
    $redi = APP_PATH . 'login.php';
    header("Location: $redi");
    exit;
}

$userId = $_SESSION['partial_auth'];

// Fetch user info
$stmt = $conn->prepare("SELECT id, email, mobile FROM usersapp WHERE id=? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// 🔐 Generate a new 6-digit MFA code
$newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$hashedCode = password_hash($newCode, PASSWORD_DEFAULT);
$expires = date('Y-m-d H:i:s', time() + 300); // expires in 5 min

// Update user's MFA token
$stmt = $conn->prepare("UPDATE usersapp 
                        SET mfa_secret=?, mfa_temp_token=?, mfa_temp_expires=? 
                        WHERE id=?");
$stmt->bind_param('sssi', $newCode, $hashedCode, $expires, $userId);
$stmt->execute();
$stmt->close();

echo $user['email'] . '<br>' . $newCode;

send_mfa_token($user, $newCode, 'resend');

// যদি তুমি সত্যিকারের SMS / Email পাঠাতে চাও,
// তাহলে এখানে SMS API / Mailer function কল করতে পারো যেমনঃ
// send_sms($user['phone'], "Your verification code is: $newCode");
// অথবা send_mail($user['email'], "Your MFA code", "Code: $newCode");

$_SESSION['mfa_message'] = "A new verification code has been sent to your registered number.";

// Redirect back to MFA verify page
$redi = APP_PATH . 'mfa_verify.php';
header("Location: $redi");
exit;
?>