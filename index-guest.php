<?php
include_once('index-guest-student-info.php');

$guest = $settings['Panel Settings']['Guest Student'];

// basic
$panel_active = $guest['panel_active'] ?? 'no';
$access_times = (int) ($guest['access_times'] ?? 0);
$max_stay_time = (int) ($guest['max_stay_time'] ?? 0);

// login security (array)
$login_security = $guest['login_security'] ?? [];

// permissions / features
$result = (int) ($guest['result'] ?? 0);
$result_details = $guest['result_details'] ?? '';
$result_pdf = $guest['result_pdf'] ?? '';
$result_archive = $guest['result_archive'] ?? '';

$attendance = (int) ($guest['attendance'] ?? 0);
$attendance_details = $guest['attendance_details'] ?? '';

$payment = (int) ($guest['payment'] ?? 0);
$payment_details = $guest['payment_details'] ?? '';
$payment_history = $guest['payment_history'] ?? '';
$online_payment = (int) ($guest['online_payment'] ?? 0);

$download_profile = (int) ($guest['download_profile'] ?? 0);
$notice = $guest['notice'] ?? '';
$notification = (int) ($guest['notification'] ?? 0);




$marksArr = [32, 39, 49, 59, 69, 79, 100];
$colorArr = ['red', 'orange', 'darkorange', 'steelblue', 'darkcyan', 'teal', 'seagreen'];
$barColor = $colorArr[0]; // default
$attndColor = 'seagreen';
$duesColor = 'crimson';

$sql = "SELECT * from examlist where sccode='$sccode' and datestart <= '$td' order by datestart desc limit 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $lastExamName = $row['examtitle'] ?? '';
    $dateStart = $row['datestart'] ?? '';
    $datePublish = $row['result_publish'] ?? '';
    $sessionyear = $row['sessionyear'] ?? '';
}

$sql = "SELECT * from tabulatingsheet where sccode='$sccode' and exam='$lastExamName' and sessionyear='$sessionyear' and stid='$stid' order by id DESC limit 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $gpa = $row['gpa'];
    $grade = $row['gla'];
    $totalMarks = $row['totalmarks'];
    $avgRate = $row['avgrate'];
}


foreach ($marksArr as $i => $max) {
    if ($avgRate <= $max) {
        $barColor = $colorArr[$i];
        break;
    }
}



// ------------------------র Attendance Calculation ------------------------
$from = $SY . '-01-01';
$to = date('Y-m-d');

$sql = "
SELECT COUNT(*) AS working_days
FROM (
    SELECT adate
    FROM stattnd
    WHERE sccode='$sccode'
      AND sessionyear='$SY'
      AND adate BETWEEN '$from' AND '$to'
    GROUP BY adate
    HAVING SUM(yn) > 0
) x
";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

$workingDays = (int) $row['working_days'];

$sql = "
SELECT
    SUM(yn=1)   AS present_days,
    SUM(bunk=1) AS bunk_days
FROM stattnd
WHERE sccode='$sccode'
  AND sessionyear='$SY'
  AND stid='$stid'
  AND adate BETWEEN '$from' AND '$to'
";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

$present0 = (int) $row['present_days'];
$bunk0 = (int) $row['bunk_days'];
$absent0 = max(0, $workingDays - ($present0 + $bunk0));







$present0 = 0;
$bunk0 = 0;
$absent0 = $workingDays - $present0 - $bunk0;

if ($present0 + $bunk0 > 100) {
    $present0 = 100;
    $bunk0 = 0;
}
$absent = 100 - $present0 - $bunk0;

$present = ($present0 > 0 && $workingDays > 0)
    ? ($present0 / $workingDays) * 100
    : 0;

$bunk = ($bunk0 > 0 && $workingDays > 0)
    ? ($bunk0 / $workingDays) * 100
    : 0;

$absent = ($absent0 > 0 && $workingDays > 0)
    ? ($absent0 / $workingDays) * 100
    : 0;




// --------------------- Dues Calculation ------------------------
$currentMonth = (int) date('n'); // 1–12
$sql = "
SELECT SUM(dues) AS total_dues
FROM stfinance
WHERE stid='$stid'
  AND sccode='$sccode'
  AND sessionyear='$SY'
  AND month <= '$currentMonth'
";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

// $totalDues = (float)($row['total_dues'] ?? 0);
$totalDues = $row['total_dues'] !== null ? (float) $row['total_dues'] : 0;
?>


<style>
    .progress-present {
        background: seagreen;
    }

    .progress-bunk {
        background: orange;
    }

    .progress-absent {
        background: crimson;
    }
</style>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        font-size: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .25s ease;
    }

    .avatar-circle:hover {
        transform: scale(1.12);
        box-shadow: 0 0 0 4px rgba(0, 0, 0, .08);
    }

    .avatar-circle:active {
        transform: scale(0.95);
    }
</style>


<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Hour chart  -->
    <div class="card bg-transparent shadow-none border-0 mb-6">
        <div class="card-body row g-6 p-0 pb-4">
            <div class="col-12 col-md-8 card-separator">
                <h5 class="mb-0">Welcome back,<span class="h4 fw-semibold"> <?= $stnameeng ?> 👋🏻</span></h5>


                <div class="col-12 mb-2">
                    ID # <b><?= $stid ?></b>
                </div>


                <div class="d-flex justify-content-between flex-wrap gap-4 me-12 mt-4">
                    <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-label-primary rounded">
                                <div class="text-primary">
                                    <i class="bi bi-laptop-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="content-right">
                            <p class="mb-1 fw-medium">Class</p>
                            <span class="text-primary mb-0 h5"><?= $classname ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-label-info rounded">
                                <div class="text-info">
                                    <i class="bi bi-laptop-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="content-right">
                            <p class="mb-1 fw-medium">Section</p>
                            <span class="text-info mb-0 h5"><?= $sectionname ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-label-warning rounded">
                                <div class="text-warning">
                                    <i class="bi bi-laptop-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="content-right">
                            <p class="mb-1 fw-medium">Roll No</p>
                            <span class="text-warning mb-0 h5"><?= $rollno ?></span>
                        </div>
                    </div>


                </div>
            </div>
            <div class="col-12 col-md-4 ps-md-4 ps-lg-6">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div>
                            <h5 class="mb-1">Time Spendings</h5>
                            <p class="mb-9">Weekly report</p>
                        </div>
                        <div class="time-spending-chart">
                            <h5 class="mb-2">231<span class="text-body">h</span> 14<span class="text-body">m</span></h5>
                            <span class="badge bg-label-success rounded-pill">+18.4%</span>
                        </div>
                    </div>
                    <div id="leadsReportChart"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hour chart End  -->

    <div class="row mb-5" <?php if ($result == 0)
        echo 'style="display:none;"'; ?>>
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary card-title m-0 me-2">Grade Book</h5>
                        <div class="fs-tiny text-secondary">Last exam status are given below</div>
                    </div>



                    <div class="avatar-circle" style="background:<?= $barColor ?>;" onclick="alert('Clicked!');">
                        <i class="bi bi-file-earmark-break text-white"></i>
                    </div>





                </div>
                <div class="card-body">
                    <div class="row align-items-center g-4">
                        <div class="col-md-2">
                            <p class="mb-1">Session</p>
                            <h5><?= $sessionyear ?></h5>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1">Examination Title</p>
                            <h5><?= $lastExamName ?></h5>
                        </div>

                        <div class="col-md-2">

                        </div>

                        <div class="col-md-4">
                            <p class="mb-1 text-dark">Result Summary</p>

                            <div class="d-flex align-items-center mb-3">
                                <div class="progress w-75 bg-label-primary" style="height:12px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width:<?= $avgRate ?>%; background:<?= $barColor ?>;"
                                        aria-valuenow="<?= $avgRate ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <p class="ms-2 mb-0" style="color:<?= $barColor ?>;"><?= $avgRate ?>%</p>
                            </div>

                            <div class="row">
                                <div class="col-12" style="color:<?= $barColor ?>;">
                                    <?php if (strtotime($datePublish) > strtotime($cur)) {
                                        echo 'Published Date: ' . date('d M, Y', strtotime($datePublish));
                                    } else {
                                        echo 'GPA' . $gpa . ' | ' . $grade;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




        </div>

    </div>

    <div class="row mb-5">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="text-primary card-title m-0 me-2">Attendance</h5>
                        <div class="fs-tiny text-secondary">Attendance summery for current session</div>
                    </div>
                    <div class="avatar-circle" style="background:<?= $attndColor ?>;" onclick="alert('Clicked!');">
                        <i class="bi bi-fingerprint text-white"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-4">
                        <div class="col-md-2">
                            <p class="mb-1">Working Days</p>
                            <h5><?= $workingDays ?></h5>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1">Present</p>
                            <h5><?= $present0 ?></h5>
                        </div>

                        <div class="col-md-2">
                            <p class="mb-1">Bunk</p>
                            <h5><?= $bunk0 ?></h5>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1">Absent</p>
                            <h5><?= $absent0 ?></h5>
                        </div>

                        <div class="col-md-4">
                            <p class="mb-1 text-dark">Attendaance Summary</p>

                            <div class="d-flex align-items-center mb-3">
                                <div class="progress w-75 bg-label-primary" style="height:12px;">
                                    <div class="progress w-100" style="height:12px;">
                                        <div class="progress-bar progress-present" style="width:<?= $present ?>%"
                                            title="Present <?= round($present) ?>%">
                                        </div>

                                        <div class="progress-bar progress-bunk" style="width:<?= $bunk ?>%"
                                            title="Bunk <?= round($bunk) ?>%">
                                        </div>

                                        <div class="progress-bar progress-absent" style="width:<?= $absent ?>%"
                                            title="Absent <?= round($absent) ?>%">
                                        </div>
                                    </div>

                                </div>
                                <p class="ms-2 mb-0" style="color:<?= $barColor ?>;"><?= $avgRate ?>%</p>
                            </div>


                            <div class="row w-75 fs-small">
                                <div class="col-4" style="color:seagreen;">Present</div>
                                <div class="col-4" style="color:orange;">Bunk</div>
                                <div class="col-4" style="color:crimson;">Absent</div>
                            </div>
                            <div class="row w-75 fs-5 fw-bold">
                                <div class="col-4" style="color:seagreen;"><?= round($present) ?>%</div>
                                <div class="col-4" style="color:orange;"><?= round($bunk) ?>%</div>
                                <div class="col-4" style="color:crimson;"><?= round($absent) ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-5">
        <div class="col-md-12">

            <div class="card">
                <div class=" card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title m-0 me-2 text-primary">Dues</h5>
                        <div class="fs-tiny text-secondary">Dues till today</div>
                    </div>
                    <div class="avatar-circle" style="background:<?= $duesColor ?>;" onclick="alert('Clicked!');">
                        <i class="bi bi-coin text-white"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-4">
                        <div class="col-md-3">
                            <p class="mb-1">......</p>
                            <h5><?= $sessionyear ?></h5>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1">Amount Dues</p>
                            <h5 class="fw-bold text-danger"><?= number_format($totalDues, 2); ?></h5>
                        </div>

                        <div class="col-md-5">
                            <p class="mb-1 text-dark">Attendaance Summary</p>

                            <div class="d-flex align-items-center mb-3">
                                <button class="btn btn-danger">Pay with bkash</button>
                            </div>


                        </div>




                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Topic and Instructors
    <div class="row mb-6 g-6">
        <div class="col-12 col-xxl-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Topic you are interested in</h5>
                    <div class="dropdown">
                        <button class="btn text-body-secondary p-0" type="button" id="topic" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base ri ri-more-2-line icon-24px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="topic">
                            <a class="dropdown-item" href="javascript:void(0);">Highest Views</a>
                            <a class="dropdown-item" href="javascript:void(0);">See All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-2 row g-3">
                    <div class="col-md-6">
                        <div id="horizontalBarChart"></div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-around align-items-center">
                        <div>
                            <div class="d-flex mb-10 align-items-baseline">
                                <span class="text-primary me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">UI Design</p>
                                    <h5 class="mb-0">35%</h5>
                                </div>
                            </div>
                            <div class="d-flex mb-10 align-items-baseline">
                                <span class="text-success me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">Music</p>
                                    <h5 class="mb-0">14%</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <span class="text-danger me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">React</p>
                                    <h5 class="mb-0">10%</h5>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex mb-10 align-items-baseline">
                                <span class="text-info me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">UX Design</p>
                                    <h5 class="mb-0">20%</h5>
                                </div>
                            </div>
                            <div class="d-flex mb-10 align-items-baseline">
                                <span class="text-secondary me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">Animation</p>
                                    <h5 class="mb-0">12%</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <span class="text-warning me-2"><i
                                        class="icon-base ri ri-circle-fill icon-12px"></i></span>
                                <div>
                                    <p class="mb-0">SEO</p>
                                    <h5 class="mb-0">9%</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Popular Instructors</h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn text-body-secondary p-0" type="button" id="popularInstructors"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base ri ri-more-2-line icon-24px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="popularInstructors">
                            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Share</a>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4 border border-start-0 border-end-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-heading text-uppercase">Instructors</small>
                        <small class="text-heading text-uppercase">courses</small>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar me-4">
                                <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/1.png"
                                    alt="Avatar" class="rounded-circle" />
                            </div>
                            <div>
                                <div>
                                    <h6 class="mb-1 text-truncate">Maven Analytics</h6>
                                    <p class="mb-0 text-truncate">Business Intelligence</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">33</h6>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar me-4">
                                <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/2.png"
                                    alt="Avatar" class="rounded-circle" />
                            </div>
                            <div>
                                <div>
                                    <h6 class="mb-1 text-truncate">Bentlee Emblin</h6>
                                    <p class="mb-0 text-truncate">Digital Marketing</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">52</h6>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar me-4">
                                <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/3.png"
                                    alt="Avatar" class="rounded-circle" />
                            </div>
                            <div>
                                <div>
                                    <h6 class="mb-1 text-truncate">Benedetto Rossiter</h6>
                                    <p class="mb-0 text-truncate">UI/UX Design</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">12</h6>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar me-4">
                                <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/avatars/4.png"
                                    alt="Avatar" class="rounded-circle" />
                            </div>
                            <div>
                                <div>
                                    <h6 class="mb-1 text-truncate">Beverlie Krabbe</h6>
                                    <p class="mb-0 text-truncate">React Native</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0">8</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top Courses</h5>
                    <div class="dropdown">
                        <button class="btn text-body-secondary p-0" type="button" id="topCourses"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base ri ri-more-2-line icon-24px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="topCourses">
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Download</a>
                            <a class="dropdown-item" href="javascript:void(0);">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex mb-6">
                            <div class="avatar flex-shrink-0 me-4">
                                <span class="avatar-initial rounded bg-label-primary"><i
                                        class="icon-base ri ri-vidicon-line icon-24px"></i></span>
                            </div>
                            <div class="d-sm-flex w-100 align-items-center">
                                <div class="w-100 mb-2 mb-sm-0 me-sm-4">
                                    <h6 class="mb-0">Videography Basic Design Course</h6>
                                </div>
                                <div class="badge bg-label-secondary rounded-pill text-heading">1.2k Views</div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="avatar flex-shrink-0 me-4">
                                <span class="avatar-initial rounded bg-label-info"><i
                                        class="icon-base ri ri-code-fill icon-24px"></i></span>
                            </div>
                            <div class="d-sm-flex w-100 align-items-center">
                                <div class="w-100 mb-2 mb-sm-0 me-sm-4">
                                    <h6 class="mb-0">Basic Front-end Development Course</h6>
                                </div>
                                <div class="badge bg-label-secondary rounded-pill text-heading">834 Views</div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="avatar flex-shrink-0 me-4">
                                <span class="avatar-initial rounded bg-label-success"><i
                                        class="icon-base ri ri-camera-fill icon-24px"></i></span>
                            </div>
                            <div class="d-sm-flex w-100 align-items-center">
                                <div class="w-100 mb-2 mb-sm-0 me-sm-4">
                                    <h6 class="mb-0">Basic Fundamentals of Photography</h6>
                                </div>
                                <div class="badge bg-label-secondary rounded-pill text-heading">3.7k Views</div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="avatar flex-shrink-0 me-4">
                                <span class="avatar-initial rounded bg-label-warning"><i
                                        class="icon-base ri ri-palette-line icon-24px"></i></span>
                            </div>
                            <div class="d-sm-flex w-100 align-items-center">
                                <div class="w-100 mb-2 mb-sm-0 me-sm-4">
                                    <h6 class="mb-0">Advance Dribble Base Visual Design</h6>
                                </div>
                                <div class="badge bg-label-secondary rounded-pill text-heading">2.5k Views</div>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="avatar flex-shrink-0 me-4">
                                <span class="avatar-initial rounded bg-label-danger"><i
                                        class="icon-base ri ri-mic-fill icon-24px"></i></span>
                            </div>
                            <div class="d-sm-flex w-100 align-items-center">
                                <div class="w-100 mb-2 mb-sm-0 me-sm-4">
                                    <h6 class="mb-0">Your First Singing Lesson</h6>
                                </div>
                                <div class="badge bg-label-secondary rounded-pill text-heading">948 Views</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="bg-label-primary text-center mb-6 pt-2 rounded-3">
                        <img class="img-fluid w-px-150"
                            src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/sitting-girl-with-laptop.png"
                            alt="Card girl image" />
                    </div>
                    <h5 class="mb-1">Upcoming Webinar</h5>
                    <p class="small mb-6">Next Generation Frontend Architecture Using Layout Engine And React Native
                        Web.</p>
                    <div class="row mb-6 g-4">
                        <div class="col-6">
                            <div class="d-flex">
                                <div class="avatar flex-shrink-0 me-4">
                                    <span class="avatar-initial rounded bg-label-primary"><i
                                            class="icon-base ri ri-calendar-line icon-24px"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-nowrap fw-normal">17 Nov 23</h6>
                                    <small>Date</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex">
                                <div class="avatar flex-shrink-0 me-4">
                                    <span class="avatar-initial rounded bg-label-primary"><i
                                            class="icon-base ri ri-time-line icon-24px"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-nowrap fw-normal">32 minutes</h6>
                                    <small>Duration</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="javascript:void(0);" class="btn btn-primary w-100">Join the event</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Assignment Progress</h5>
                    <div class="dropdown">
                        <button class="btn text-body-secondary p-0" type="button" id="assignProgress"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-base ri ri-more-2-line icon-24px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="assignProgress">
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Download</a>
                            <a class="dropdown-item" href="javascript:void(0);">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="primary" data-series="72"
                                data-progress_variant="true"></div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">User experience Design</h6>
                                        <p class="mb-0">120 Tasks</p>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="icon-base ri ri-arrow-right-s-line icon-20px scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="success" data-series="48"
                                data-progress_variant="true"></div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">Basic fundamentals</h6>
                                        <p class="mb-0">32 Tasks</p>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="icon-base ri ri-arrow-right-s-line icon-20px scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="danger" data-series="15"
                                data-progress_variant="true"></div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">React native components</h6>
                                        <p class="mb-0">182 Tasks</p>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="icon-base ri ri-arrow-right-s-line icon-20px scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="chart-progress me-4" data-color="info" data-series="24"
                                data-progress_variant="true"></div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">Basic of music theory</h6>
                                        <p class="mb-0">56 Tasks</p>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="icon-base ri ri-arrow-right-s-line icon-20px scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
  End-->

    <!-- Course datatable 
    <div class="card mb-4">
        <div class="table-responsive mb-3">
            <table class="table datatables-academy-course">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Course Name</th>
                        <th>Time</th>
                        <th class="w-25">Progress</th>
                        <th class="w-25">Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

     -->

</div>
<!-- / Content -->



<script>

</script>