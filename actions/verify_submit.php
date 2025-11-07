<?php
// actions/verify_submit.php
// header.php already included
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$reg_id = $_POST['reg_id'] ?? '';
$otp = $_POST['otp'] ?? '';

if (!$reg_id || !$otp) die('Missing data');

$stmt = $conn->prepare("SELECT id, otp, otp_expires, mnumber FROM registrations WHERE reg_id = ? LIMIT 1");
$stmt->bind_param("s", $reg_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) die('Registration not found.');

if ($row['otp'] !== $otp) {
    die('Invalid OTP.');
}

if (new DateTime() > new DateTime($row['otp_expires'])) {
    die('OTP expired. Please request a new OTP.');
}

// mark verified
$stmt = $conn->prepare("UPDATE registrations SET verified = 1, otp = NULL, otp_expires = NULL WHERE reg_id = ?");
$stmt->bind_param("s", $reg_id);
$stmt->execute();
$stmt->close();

// send ID+PIN via SMS (placeholder)
$stmt = $conn->prepare("SELECT pin FROM registrations WHERE reg_id = ?");
$stmt->bind_param("s", $reg_id);
$stmt->execute();
$res = $stmt->get_result();
$rec = $res->fetch_assoc();
$stmt->close();
$pin = $rec['pin'];

$sms_text = "Registration complete. Your ID: $reg_id, PIN: $pin. Keep it safe.";
function send_sms($to, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO sms_logs (`to_number`, `message`, `status`) VALUES (?, ?, ?)");
    $status = 'queued';
    $stmt->bind_param("sss", $to, $message, $status);
    $stmt->execute();
    $stmt->close();
    return true;
}
send_sms($row['mnumber'], $sms_text);

// redirect to a page offering PDF download and login
$_SESSION['verified_reg_id'] = $reg_id;
header("Location: ../welcome.php");
exit;
