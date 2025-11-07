<?php
// header.php থেকে ইতিমধ্যে config.php এবং db.php অন্তর্ভুক্ত থাকে,
// তাই এখানে নতুন করে include দরকার নেই।

require_once '../core/config.php';
require_once '../core/db.php';

// ইনপুট গ্রহণ
$sccode = trim($_POST['sccode'] ?? '');
$scname = trim($_POST['scname'] ?? '');
$sccategory = trim($_POST['sccategory'] ?? '');
$scadd1 = trim($_POST['scadd1'] ?? '');
$scadd2 = trim($_POST['scadd2'] ?? '');
$ps = trim($_POST['ps'] ?? '');
$dist = trim($_POST['dist'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$scmail = trim($_POST['scmail'] ?? '');
$headname = trim($_POST['headname'] ?? '');
$headtitle = trim($_POST['headtitle'] ?? '');
$rootuser = trim($_POST['rootuser'] ?? '');
$entryby = $_SESSION['user'] ?? 'system';

// Validation
if (!$sccode || !$scname) {
    exit('Error: sccode and scname are required.');
}

// আগে থেকে একই sccode আছে কি না
$check = $conn->prepare("SELECT sccode FROM scinfo WHERE sccode = ?");
$check->bind_param("s", $sccode);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    exit('Error: Institution code already exists.');
}

$check->close();

// ইনসার্ট
$sql = "INSERT INTO scinfo 
(sccode, scname, sccategory, scadd1, scadd2, ps, dist, mobile, scmail, headname, headtitle, rootuser)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$ok = $stmt->execute([
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
    $rootuser
]);

if ($ok) {
    echo "Register New Institute Successfully.";
} else {
    echo "Register Failure";
}
