<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$sessionyear = $_POST['sessionyear'] ?? '';
$examname = $_POST['exam'] ?? '';
$clsname = $_POST['classname'] ?? '';
$secname = $_POST['sectionname'] ?? '';
$action = $_POST['action'] ?? '';


$subjectList = [];

$sqlSub = "SELECT subcode, subject 
           FROM subjects
           WHERE sccategory='$sctype'
           AND (sccode='0' OR sccode='$sccode')
           ORDER BY subcode";

$resSub = mysqli_query($conn, $sqlSub);

while ($rowSub = mysqli_fetch_assoc($resSub)) {
    $subjectList[] = $rowSub;
}


$sql = "SELECT r.id, r.date, r.time, r.subcode, s.subject
            FROM examroutine r
            LEFT JOIN subjects s 
                ON r.subcode = s.subcode 
            WHERE s.sccategory = '$sctype'
            AND r.sccode='$sccode'
            AND r.sessionyear='$sessionyear'
            AND r.examname='$examname'
            AND r.clsname='$clsname'
            AND r.secname='$secname'
            ORDER BY r.date, r.time";

$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}



$students = [
    [
        'stnameeng' => 'Rahim Uddin',
        'classname' => $clsname,
        'sectionname' => $secname,
        'rollno' => '01',
        'stid' => '1001'
    ],
    [
        'stnameeng' => 'Rahim Uddin',
        'classname' => $clsname,
        'sectionname' => $secname,
        'rollno' => '01',
        'stid' => '1001'
    ]
];



?>

<div class="card-header d-flex justify-content-between">
    <div>
        <b>Exam Routine</b>
        <div id="routine-info" class="d-flex mt-2">
            <div>
                <div class="small">Slot</div>
                <div class="fs-6 fw-bold"><?= $slot ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Session</div>
                <div class="fs-6 fw-bold"><?= $sessionyear ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Examination</div>
                <div class="fs-6 fw-bold"><?= $examname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Class</div>
                <div class="fs-6 fw-bold"><?= $clsname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Section</div>
                <div class="fs-6 fw-bold"><?= $secname ?? '' ?></div>
            </div>

        </div>
    </div>

    <div class="d-flex gap-3 fs-4">

        <!-- PRINT -->
        <i class="bi bi-printer text-primary" style="cursor:pointer;" onclick="printAdmit()"></i>

        <!-- PDF -->
        <i class="bi bi-file-earmark-pdf text-danger" style="cursor:pointer;" onclick="downloadPDF()"></i>

        <!-- SETTINGS -->
        <i class="bi bi-gear text-info" style="cursor:pointer;" onclick="openSettings()"></i>

    </div>
</div>

<div class="card-body">

    <!-- Empty State -->
    <div id="emptyState" style="display:none; text-align:center;">
        <h4>No Routine Found</h4>
        <button id="btnStart" class="btn btn-success">
            Let's Get Started
        </button>
    </div>

    <!-- Table -->


</div>

<div id="routineTable" class="table table-responsive">
    <?php
    foreach ($students as $st) {
        ?>
        <div class="admit-card mb-4 p-3 border"
            style="background:url('assets/admit/sample_02.png'); background-size:cover;">

            <!-- HEADER -->
            <div class="d-flex align-items-center mb-2">
                <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" height="60">
                <div class="ms-3">
                    <h5 class="mb-0"><?php echo $scname; ?></h5>
                    <small><?php echo $scaddress; ?></small><br>
                    <small>Contact: <?php echo $scmobile; ?></small><br>
                    <small>Email : <?= $scmail ?>, Web : <?= $scweb ?></small>
                </div>
            </div>

            <!-- TITLE -->
            <div class="text-center mb-2">
                <img src="assets/admit/admit.png" height="35px"><br>
                <b><?php echo $examname . ' Examination - ' . $sessionyear; ?></b>
            </div>

            <!-- STUDENT INFO -->
            <div class="d-flex justify-content-between">
                <div>
                    <b><?php echo $st['stnameeng']; ?></b><br>
                    Class: <b><?php echo $st['classname']; ?></b>
                    Section: <b><?php echo $st['sectionname']; ?></b>
                    Roll: <b><?php echo $st['rollno']; ?></b><br>
                    ID: <?php echo $st['stid']; ?>
                </div>

                <div>
                    <img src="http://www.eimbox.com/admit/noimg.jpg" height="90" style="border:1px solid #000;">
                </div>
            </div>

            <!-- ROUTINE TABLE -->
            <table class="table table-bordered mt-2" style="font-size:12px;">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Subject</th>
                </tr>

                <?php foreach ($data as $r) { ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['date'])) ?></td>
                        <td><?= date('l', strtotime($r['date'])) ?></td>
                        <td><?= date('h:i A', strtotime($r['time'])) ?></td>
                        <td><?= $r['subject'] ?></td>
                    </tr>
                <?php } ?>
            </table>

        </div>
    <?php } ?>
</div>