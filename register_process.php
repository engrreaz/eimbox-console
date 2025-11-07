<?php
include('core/config.php');
include('core/db.php');

// ফর্ম ডেটা
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
$photo_data = $_POST['photo_data'] ?? ''; // ✅ ঠিক করা হলো

// Session Year ও SCCODE
$sessionyear = $_POST['sessionyear'] ?? '2026';
$sccode = $_POST['sccode'] ?? NULL;


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
if($row = $result->fetch_assoc()){
    $last_roll = (int)$row['roll_no']; // শেষ রোল নম্বর
}
$stmt->close();
$next_roll = $last_roll + 1; // নতুন রোল



// ফটো প্রক্রিয়া
$photo_path = '';
if (!empty($photo_data)) {
    $folder = 'uploads/photos/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    // ফাইলনেম বানাও
    $filename = 'photo_' . time() . '_' . rand(1000, 9999) . '.jpg';
    $photo_path = $folder . $filename;

    // Base64 ডেটা থেকে ইমেজে রূপান্তর
    $photo_data = str_replace('data:image/jpeg;base64,', '', $photo_data);
    $photo_data = str_replace(' ', '+', $photo_data);
    file_put_contents($photo_path, base64_decode($photo_data));
}

// Unique Registration ID এবং PIN জেনারেট
$regid = 'REG' . date('y') . strtoupper(substr(md5(uniqid()), 0, 5));
$pin = rand(100000, 999999);

// ডাটাবেজ ইনসার্ট
$stmt = $conn->prepare("INSERT INTO registrations 
(sessionyear, sccode, stnameeng, stnameben, fname, mname, mnumber, dist, ps, po, village, testno, insdist, insps, inspo, insname, photo, reg_id, pin, roll_no)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// ✅ মোট 19টা ভ্যালু => টাইপ স্ট্রিং 19 অক্ষরের
$stmt->bind_param(
    "sisssssssssssssssssi",
    $sessionyear, $sccode, $stnameeng, $stnameben, $fname, $mname, $mnumber,
    $dist, $ps, $po, $village, $testno, $insdist, $insps, $inspo, $insname,
    $photo_path, $regid, $pin, $next_roll
);

if ($stmt->execute()) {
    $insert_id = $stmt->insert_id;
    $stmt->close();

    // মোবাইল ভেরিফিকেশন পেজে পাঠানো
    header("Location: mobile_verify.php?id=$insert_id");
    exit;
} else {
    echo "<div class='alert alert-danger'>Registration failed: " . $stmt->error . "</div>";
    $stmt->close();
}
?>
