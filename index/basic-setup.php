<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/global_values.php';

$icon = '';


// ===================== TRACK CONFIG =====================
$track_value = [
    "Logo" => 10,
    "Weekends" => 3,
    "Version" => 1,
    "Medium" => 1,
    "Session" => 5,
    "Address" => 5,
    "Mobile" => 3,
    "Website" => 2,
    "Head Name" => 2,
    "Head Position" => 2,
    "Administrator" => 1,
    "Chief" => 5,
    "Geo-Fence" => 5,
    "Thershold" => 4,
    "Slot" => 3,
    "Teacher" => 5,
    "Class" => 5,
    "Student" => 5,
    "Subject" => 10
];


// ===================== SCINFO =====================
$stmt = $conn->prepare("SELECT profile_track FROM scinfo WHERE sccode = ?");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$res = $stmt->get_result();
$scinfo = $res->fetch_assoc();

$profile_track = $scinfo['profile_track'] ?? 0;


// ===================== SETTINGS =====================
$stmt2 = $conn->prepare("SELECT setting_title, settings_value FROM settings WHERE sccode = ?");
$stmt2->bind_param("i", $sccode);
$stmt2->execute();

$stmt2->bind_result($title, $value);

$settings = [];

while ($stmt2->fetch()) {
    $settings[$title] = $value;
}

$weekend = $settings['Weekends'] ?? '';
$version = $settings['Version'] ?? '';
$medium  = $settings['Medium'] ?? '';


// ===================== SESSION YEAR =====================
$YY = '%' . date('y') . '%';
$stmt3 = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode = ? AND active = 1 AND syear LIKE ? LIMIT 1");
$stmt3->bind_param("ss", $sccode, $YY);
$stmt3->execute();
$res3 = $stmt3->get_result();
$session = $res3->fetch_assoc();

$current_session_year = $session['syear'] ?? '';


// ===================== LOGO =====================
$logo_path = 'logo/default.png';

$file = BASE_ROOT . 'logo/' . $sccode . '.png';
if (file_exists($file)) {
    $logo_path = 'logo/' . $sccode . '.png';
}


// ===================== USER FETCHING =====================
$stmt4 = $conn->prepare("SELECT userlevel, is_chief FROM usersapp WHERE sccode = ?");
$stmt4->bind_param("s", $sccode);
$stmt4->execute();

$res4 = $stmt4->get_result();

$ulf = $icf = 0;

while ($row = $res4->fetch_assoc()) {
    $ul = $row['userlevel'];
    $ic = $row['is_chief'];
    if($ul == 'Administrator') $ulf = 1;
    if($ic == 1) $icf = 1;
    
}


$scweb =  '';
$stmt = $conn->prepare("SELECT scweb, geolat, geolon, dista_differ, time_differ FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("i", $sccode);
$stmt->execute();

$stmt->store_result();
$stmt->bind_result($scweb, $geolat, $geolon,  $dista_differ, $time_differ);

$stmt->fetch();
$stmt->close();




$year2 = date('y');

// ================= TEACHER COUNT =================
$stmt = $conn->prepare("SELECT COUNT(*) FROM teacher WHERE sccode = ?");
$stmt->bind_param("i", $sccode);
$stmt->execute();
$stmt->bind_result($teacher_count);
$stmt->fetch();
$stmt->close();


// ================= CLASS COUNT =================
$likeYear = "%$year2%";

$stmt = $conn->prepare("SELECT COUNT(*) FROM areas WHERE sccode = ? AND sessionyear LIKE ?");
$stmt->bind_param("is", $sccode, $likeYear);
$stmt->execute();
$stmt->bind_result($class_count);
$stmt->fetch();
$stmt->close();


// ================= STUDENT COUNT =================
$stmt = $conn->prepare("SELECT COUNT(*) FROM sessioninfo WHERE sccode = ? AND sessionyear LIKE ?");
$stmt->bind_param("is", $sccode, $likeYear);
$stmt->execute();
$stmt->bind_result($student_count);
$stmt->fetch();
$stmt->close();


// ================= SUBJECT COUNT =================
$stmt = $conn->prepare("SELECT COUNT(*) FROM subsetup WHERE sccode = ? AND sessionyear LIKE ?");
$stmt->bind_param("is", $sccode, $likeYear);
$stmt->execute();
$stmt->bind_result($subject_count);
$stmt->fetch();
$stmt->close();

// ===================== TRACK CALCULATION =====================
$total_points = 0;
$earned_points = 0;
$track_status = [];

foreach ($track_value as $key => $value) {

    $status = 'gray';

    switch ($key) {

        case "Logo":
            if (file_exists(BASE_ROOT . 'logo/' . $sccode . '.png')) {
                $status = 'green';
                $earned_points += $value;
                $icon = 'card-image';
            }
            break;

        case "Weekends":
            if (!empty($weekend)) {
                $status = 'green';
                $earned_points += $value;
                $icon = 'calendar-day';
            }
            break;

        case "Version":
            if (!empty($version)) {
                $status = 'green';
                $earned_points += $value;
                $icon = 'translate';
            }
            break;

        case "Medium":
            if (!empty($medium)) {
                $status = 'green';
                $earned_points += $value;
                $icon = 'cast';
            }
            break;

        case "Session":
            if ($current_session_year != '') {
                $status = 'green';
                $earned_points += $value;
                $icon = 'calendar-fill';
            }
            break;
        
        case "Address":
            if($scaddress !== ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'geo-alt-fill';
            }
            break;
        
        case "Mobile":
            if($scmobile !== ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'phone';
            }
            break;
        
        case "Website":
            if($scweb !== ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'globe2';
            }
            break;
        case "Head Name":
            if($headname !== ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'person-square';
            }
            break;
            
        case "Head Position":
            if($headtitle !== ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'person-video';
            }
            break;
            
   
            
        case "Administrator":
            if($ulf == 1){
                $status = 'green';
                $earned_points += $value;
                $icon = 'person-video';
            }
            break;
            
        case "Chief":
            if($icf == 1){
                $status = 'green';
                $earned_points += $value;
                $icon = 'person-video';
            }
            break;
       
        case "Geo-Fence":
            if($geolat != '' && $geolon != ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'geo-fill';
            }
            break;
        
        case "Thershold":
            if($dista_differ != '' && $time_differ != ''){
                $status = 'green';
                $earned_points += $value;
                $icon = 'clock-fill';
            }
            break;
       
        case "Slot":
            if($dista_differ==100){
                $status = 'green';
                $earned_points += $value;
                
            }$icon = 'layout-split';
            break;
        
        case "Teacher":
            if($teacher_count > 0){
                $status = 'green';
                $earned_points += $value;
                $icon = 'person-circle';
            }
            break;
        
        case "Class":
           if($class_count > 0){
                $status = 'green';
                $earned_points += $value;
                $icon = 'grid-1x2';
            }
            break;
        
        case "Student":
           if($student_count > 0){
                $status = 'green';
                $earned_points += $value;
                $icon = 'people-fill';
            }
            break;
        
        case "Subject":
           if($subject_count > 0){
                $status = 'green';
                $earned_points += $value;
                $icon = 'book-fill';
            }
            break;





        default:
            $status = 'gray';
            $icon = 'circle';
    }

    $total_points += $value;

    $track_status[] = [
        'name' => $key,
        'points' => $value,
        'status' => $status,
        'icon' => $icon
    ];
}


// ===================== PERCENTAGE =====================
$percent = ($total_points > 0)
    ? round(($earned_points / $total_points) * 100)
    : 0;
?>

<!-- ===================== UI ===================== -->

<div class="card shadow-sm">

    <div class="card-header  d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Institution Setup Progress</h5>
        <span class="badge bg-light text-dark"><?= $percent ?>%</span>
    </div>

    <div class="card-body">

        <!-- Progress Bar -->
        <div class="progress mt-2 mb-4" style="height: 16px; border-radius:10px;">
            <div class="progress-bar bg-primary" style="width: <?= $percent ?>%">
                <?= $percent ?>%
            </div>
        </div>

        <!-- Timeline -->
        <div class="d-flex align-items-center flex-wrap gap-2">

            <!-- HOME -->
            <div class="text-primary fs-5">
                <i class="bi bi-house-door-fill"></i>
            </div>

            <div class="border-bottom flex-grow-1"></div>

            <?php foreach ($track_status as $item): ?>

                <?php
                if ($item['status'] == 'green') {
                    $icon = "bi-" . $item['icon'] . " text-primary";
                } elseif ($item['status'] == 'red') {
                    $icon = "bi-" . $item['icon'] . " text-danger";
                } else {
                    $icon = "bi-" . $item['icon'] . " text-secondary";
                }
                ?>

                <div class="text-center">
                    <i class="bi <?= $icon ?> fs-5"></i>
                    <div style="font-size:11px" hidden><?= $item['name'] ?></div>
                </div>

                <div class="border-bottom flex-grow-1"></div>

            <?php endforeach; ?>

            <!-- FLAG -->
            <div class="text-primary fs-5">
                <i class="bi bi-flag-fill"></i>
            </div>

        </div>

        <!-- SCORE -->
        <hr>
        <div>
            <strong>Score:</strong> <?= $earned_points ?> / <?= $total_points ?>
        </div>

    </div>
</div>