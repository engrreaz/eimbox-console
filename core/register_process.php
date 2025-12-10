<?php
session_start();
include('config.php');
include('db.php');


// ফর্ম ডেটা
$stnameeng = $_POST['stnameeng'] ?? '';
$stnameben = $_POST['stnameben'] ?? '';
$fname = $_POST['fname'] ?? '';
$mname = $_POST['mname'] ?? '';
$mnumber = $_POST['mnumber'] ?? '';
$dist = htmlspecialchars($_POST['dist'] ?? '', ENT_QUOTES, 'UTF-8');
$ps = htmlspecialchars($_POST['ps'] ?? '', ENT_QUOTES, 'UTF-8');
$po = $_POST['po'] ?? '';
$village = $_POST['village'] ?? '';
$testno = $_POST['testno'] ?? '';
$insdist = htmlspecialchars($_POST['insdist'] ?? '', ENT_QUOTES, 'UTF-8');
$insps = htmlspecialchars($_POST['insps'] ?? '', ENT_QUOTES, 'UTF-8');
$inspo = $_POST['inspo'] ?? '';
$insname = $_POST['insname'] ?? '';
$dob = $_POST['dob'] ?? '';
$brnno = $_POST['brnno'] ?? '';
// $cls = $_POST['admit_class'] ?? 'Six';
$cls = $_POST['admclass'] ?? 'Six';
// $photo_data = $_POST['photo_data'] ?? '';

// Session Year ও SCCODE
$sessionyear = $_POST['sessionyear'] ?? '2026';
$sccode = $_POST['sccode'] ?? NULL;

$religion = $_POST['religion'] ?? NULL;
$gender = $_POST['gender'] ?? NULL;
$bgroup = htmlspecialchars($_POST['bgroup'] ?? NULL, ENT_QUOTES, 'UTF-8');
$falive = $_POST['falive'] ?? NULL;
$fmobile = $_POST['fmobile'] ?? NULL;
$malive = $_POST['malive'] ?? NULL;
$mmobile = $_POST['mmobile'] ?? NULL;
$guar = $_POST['guar'] ?? NULL;
$guarname = $_POST['guarname'] ?? NULL;


$query = "SELECT roll_no 
          FROM registrations 
          WHERE sccode=? AND sessionyear=? 
          ORDER BY roll_no DESC 
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $sccode, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$last_roll = 0;
if ($row = $result->fetch_assoc()) {
    $last_roll = (int) $row['roll_no']; // শেষ রোল নম্বর
}
$stmt->close();
$next_roll = $last_roll + 1; // নতুন রোল


$photoPath = '';

if (!empty($_FILES['photo']['tmp_name'])) {
    $uploadDir = '../uploads/photos/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0777, true);

    $filename = 'photo_' . $sccode . '_' . time() . '.jpg';
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
        $photoPath = $filename;
    }
}

// ডাটাবেজ ইনসার্ট
// Unique Registration ID এবং PIN জেনারেট
$regid = 'REG' . date('y') . strtoupper(substr(md5(uniqid()), 0, 5));
$pin = rand(100000, 999999);

// ডাটাবেজ ইনসার্ট
$stmt = $conn->prepare("INSERT INTO registrations 
(sessionyear, sccode, stnameeng, stnameben, fname, mname, mnumber, dist, ps, po, village, 
    testno, insdist, insps, inspo, insname, photo, reg_id, pin, roll_no, admit_class, dob, brnno,
    religion, gender, bgroup, falive, fmobile, malive, mmobile, guar, guarname)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sisssssssssssssssssissssssssssss",
    $sessionyear,
    $sccode,
    $stnameeng,
    $stnameben,
    $fname,
    $mname,
    $mnumber,
    $dist,
    $ps,
    $po,
    $village,
    $testno,
    $insdist,
    $insps,
    $inspo,
    $insname,
    $photoPath,
    $regid,
    $pin,
    $next_roll,
    $cls,
    $dob,
    $brnno,
    $religion,
    $gender,
    $bgroup,
    $falive,
    $fmobile,
    $malive,
    $mmobile,
    $guar,
    $guarmobile
);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();

    // মোবাইল ভেরিফিকেশন পেজে পাঠানো
    echo json_encode([
        'status' => 'success',
        'redirect' => 'mobile_verify.php?id=' . $insert_id
    ]);
    $_SESSION['admission'] = true;
    $_SESSION['stname'] = $stnameeng;
    $_SESSION['regid'] = $regid;
    $_SESSION['pin'] = $pin;
    $_SESSION['step'] = 'otp';
    $_SESSION['id'] = $insert_id;

    exit;
    // header("Location: ../mobile_verify.php?id=$insert_id");
    // exit;
} else {
    echo "<div class='alert alert-danger'>Registration failed: " . $stmt->error . "</div>";
    $stmt->close();
}