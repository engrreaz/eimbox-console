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
$mode = $_POST['mode'] ?? 'preview';



// Custom Settings Value --------------------------
$admit_background = $_COOKIE['admit-background'] ?? 'sample_02.png';
$admit_title = $_COOKIE['admit-title'] ?? 'title_01.png';

$color2 = $_COOKIE['admit-color2'] ?? '#000000';
$color3 = $_COOKIE['admit-color3'] ?? '#852357';

$gridSize = $_COOKIE['seat-grid'] ?? '2x5';
$orientation = $_COOKIE['seat-orientation'] ?? 'P';
list($cols, $rows) = explode('x', $gridSize);

if ($orientation == 'P') {
    $x = 210;
    $y = 297;
} else {
    $x = 297;
    $y = 210;
}
$width = $x / $cols;
$height = $y / $rows;

$scNameSize = $_COOKIE['inst-name-size'] ?? 14;
$color1 = $_COOKIE['admit-color1'] ?? '#263547';
$scAddress = $_COOKIE['inst-address'] ?? 'No';



// echo $admit_background . '/' . $admit_title . '/' . $color1.'/'. $color2 . '/' . $color3;
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


if ($mode == 'preview') {
    $sqlStudent = "SELECT 
                    si.rollno,
                    si.stid,
                    st.stnameeng,
                    st.stnameben
               FROM sessioninfo si
               INNER JOIN students st 
                    ON si.stid = st.stid
               WHERE si.classname = '$clsname'
               AND si.sectionname = '$secname'
               AND si.sessionyear = '$sessionyear'
               AND si.sccode = '$sccode'
               AND si.slot = '$slot'
               LIMIT 1";
} else {
    $sqlStudent = "SELECT 
                    si.rollno,
                    si.stid,
                    st.stnameeng,
                    st.stnameben
               FROM sessioninfo si
               INNER JOIN students st 
                    ON si.stid = st.stid
               WHERE si.classname = '$clsname'
               AND si.sectionname = '$secname'
               AND si.sessionyear = '$sessionyear'
               AND si.sccode = '$sccode'
               AND si.slot = '$slot' LIMIT 12";
}

$resStudent = mysqli_query($conn, $sqlStudent);

$students = [];

while ($row = mysqli_fetch_assoc($resStudent)) {
    $students[] = $row;
}





?>




<div class="card-header d-flex justify-content-between">
    <div>
        <h4>Seat Card</h4>
        <div id="routine-info" class="d-flex mt-2">
            <div>
                <div class="small">Slot</div>
                <div class="fs-6 fw-bold text-primary"><?= $slot ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Session</div>
                <div class="fs-6 fw-bold text-primary"><?= $sessionyear ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Examination</div>
                <div class="fs-6 fw-bold text-primary"><?= $examname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Class</div>
                <div class="fs-6 fw-bold text-primary"><?= $clsname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Section</div>
                <div class="fs-6 fw-bold text-primary"><?= $secname ?? '' ?></div>
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
    <div style="top:-1px; display:grid; grid-template-columns: repeat(<?= $cols ?>, 1fr);">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                /* display: grid;
            grid-template-columns: repeat(<?= $cols ?>, 1fr); */
            }

            .admit-card {
                border: none;
            }

            table {
                border: 0;
            }

            .inst-name {
                color:
                    <?= $color1 ?>
                ;
            }

            .inst-address {
                color:
                    <?= $color2 ?>
                ;
            }

            .exam-name {
                color:
                    <?= $color3 ?>
                ;
            }
        </style>
        <?php
        foreach ($students as $st) {
            ?>
            <div class="admit-card mb-4 p-3 border"
                style="margin:auto; width:<?= $width ?>mm; height:<?= $height ?>mm;  overflow: hidden; background:url('assets/admit/<?= $admit_background ?>'); background-size:cover;  ">

                <!-- HEADER -->

                <table style="margin: 10px auto 0;" class="admit-header">
                    <tr>

                        <td style="text-align:center">
                            <div class="inst-name"
                                style="font-size:<?= $scNameSize ?>px; font-weight:bold;  margin:0; margin-bottom:1px;">
                                <?php echo $scname; ?>
                            </div>
                            <? if ($scAddress == 'Yes') { ?>
                                <small class="inst-address"><?php echo $scaddress; ?></small><br>
                                <small class="inst-address">Contact: <?php echo $scmobile; ?></small>
                            <? } ?>
                        </td>
                    </tr>
                </table>


                <!-- TITLE -->
                <div class="exam-name"
                    style="text-align:center;  font-weight:bold; font-size:16px; margin:0; padding:0;">
                    <?php echo $examname . ' Examination - ' . $sessionyear; ?>
                </div>

                <!-- STUDENT INFO -->
                <table style="width:92%; margin : 0 auto;">
                    <tr>
                        <td style="text-align: center;">
                            <b><?php echo $st['stnameeng']; ?></b><br>
                            <b><?php echo $st['stnameben']; ?></b><br>
                            <span style="font-size:9px;">ID: <?php echo $st['stid']; ?></span>

                            <table style="width:100%; margin-bottom:7px;">
                                <tr>
                                    <td>Class: <b><?php echo $clsname; ?></b><br>
                                        Section: <b><?php echo $secname; ?></b><br>
                                        Roll: <b><?php echo $st['rollno']; ?></b></td>
                                </tr>
                            </table>




                        </td>

                        <td style="width:80px; text-align:right; padding-left:5mm;">
                            <img src="https://www.eimbox.com/students/<?= $st['stid'] ?>.jpg"
                                style="border:1px solid #000; height:70px; width:70px; border-radius:50%;">

                        </td>
                    </tr>
                </table>





                <div style="height:17px;"></div>
                <!-- ROUTINE TABLE -->


            </div>
        <?php } ?>
    </div>
</div>