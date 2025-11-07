<?php
// actions/register_submit.php
// header.php already included in your app environment (do NOT re-include)
// ensure session started earlier if needed
// assume $conn is available (mysqli)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// sanitize inputs (basic)
$stnameeng = $_POST['stnameeng'] ?? '';
$stnameben = $_POST['stnameben'] ?? '';
$fname = $_POST['fname'] ?? '';
$mname = $_POST['mname'] ?? '';
$mnumber = $_POST['mnumber'] ?? '';
$dist = $_POST['dist'] ?? '';
$ps = $_POST['ps'] ?? '';
$po = $_POST['po'] ?? '';
$village = $_POST['village'] ?? '';
$testno = $_POST['testno'] ?? '';
$insdist = $_POST['insdist'] ?? '';
$insps = $_POST['insps'] ?? '';
$inspo = $_POST['inspo'] ?? '';
$insname = $_POST['insname'] ?? '';
$photo_data = $_POST['photo_data'] ?? null;

// simple validation
if (empty($stnameeng) || empty($mnumber)) {
    die('Name and mobile number required.');
}

// generate reg_id and pin
$year = date('Y');
$unique = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
$reg_id = $year . '-' . $unique;
$pin = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

// handle photo
$photo_path = null;
if ($photo_data) {
    // expected data url: data:image/jpeg;base64,...
    if (preg_match('/^data:image\/(\w+);base64,/', $photo_data, $type)) {
        $data = substr($photo_data, strpos($photo_data, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif
        $data = base64_decode($data);
        if ($data === false) {
            // invalid
        } else {
            $allowed = ['jpg','jpeg','png'];
            if ($type === 'jpeg') $type = 'jpg';
            if (!in_array($type, $allowed)) {
                // fallback: still save as jpg
                $type = 'jpg';
            }
            $uploads_dir = __DIR__ . '/../uploads/photos';
            if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
            $filename = $reg_id . '.' . $type;
            $filepath = $uploads_dir . '/' . $filename;
            file_put_contents($filepath, $data);
            // store relative path
            $photo_path = 'uploads/photos/' . $filename;
        }
    }
}

// generate OTP for mobile verification (6-digit)
$otp = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
$otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// insert into DB
$stmt = $conn->prepare("INSERT INTO registrations (reg_id, pin, stnameeng, stnameben, fname, mname, mnumber, dist, ps, po, village, testno, insdist, insps, inspo, insname, photo_path, otp, otp_expires, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssssssssssssssssss", $reg_id, $pin, $stnameeng, $stnameben, $fname, $mname, $mnumber, $dist, $ps, $po, $village, $testno, $insdist, $insps, $inspo, $insname, $photo_path, $otp, $otp_expires);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    die('Database error: ' . $conn->error);
}

// send OTP via SMS (placeholder — replace with gateway integration)
function send_sms($to, $message) {
    global $conn;
    // place SMS gateway integration here; for now, log to sms_logs
    $stmt = $conn->prepare("INSERT INTO sms_logs (`to_number`, `message`, `status`) VALUES (?, ?, ?)");
    $status = 'queued';
    $stmt->bind_param("sss", $to, $message, $status);
    $stmt->execute();
    $stmt->close();
    // return true for now
    return true;
}

$sms_text = "Your OTP for verification is: $otp. It expires in 10 minutes.";
send_sms($mnumber, $sms_text);

// store reg id in session to track
session_start();
$_SESSION['pending_reg_id'] = $reg_id;

// redirect to verification page
header("Location: ../verify.php");
exit;
