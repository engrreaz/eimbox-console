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


$admit_background = $_COOKIE['admit-background'] ?? 'sample_02.png';
$admit_title = $_COOKIE['admit-title'] ?? 'title_01.png';
$color1 = $_COOKIE['admit-color1'] ?? '#263547';
$color2 = $_COOKIE['admit-color2'] ?? '#000000';
$color3 = $_COOKIE['admit-color3'] ?? '#852357';


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
               AND si.slot = '$slot' LIMIT 4";
}

$resStudent = mysqli_query($conn, $sqlStudent);

$students = [];

while ($row = mysqli_fetch_assoc($resStudent)) {
    $students[] = $row;
}





?>




<div class="card-header d-flex justify-content-between">
    <div>
        <h4>Admit Card</h4>
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

<div id="routineTable" class="table table-responsive" style="top:-1px;">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admit-card {
            border: none;
        }
        table{
            border:0;
        }

        .inst-name{
            color: <?= $color1 ?>;
        }
        .inst-address{
            color: <?= $color2 ?>;
        }
        .exam-name{
            color: <?= $color3 ?>;
        }
    </style>
    <?php
    foreach ($students as $st) {
        ?>
        <div class="admit-card mb-4 p-3 border"
            style="margin:auto; width:210mm;  overflow-y: hidden; background:url('assets/admit/<?= $admit_background ?>'); background-size:cover;  ">

            <!-- HEADER -->

            <table style="margin: 18px auto 0;"  class="admit-header">
                <tr>
                    <td>
                        <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" height="60">
                    </td>
                    <td style="padding-left:10px;">
                        <div  class="inst-name" style="font-size:20px; font-weight:bold; margin:0; margin-bottom:1px;"><?php echo $scname; ?></div>
                        <small class="inst-address"><?php echo $scaddress; ?></small><br>
                        <small class="inst-address">Contact: <?php echo $scmobile; ?></small><br>
                        <small class="inst-address0">Email : <?= $scmail ?>, Web : <?= $scweb ?></small>
                    </td>
                </tr>
            </table>


            <!-- TITLE -->
            <div style="text-align:center; margin-top:5px;">
                <img  class="admit-title-img"  src="assets/admit/<?= $admit_title ?>" style="height:30px; margin:0; padding:0;"><br>
                <span class="exam-name" style="color:<?= $color3 ?>; font-weight:bold; font-size:20px;">
                    <?php echo $examname . ' Examination - ' . $sessionyear; ?>
                </span>
            </div>

            <!-- STUDENT INFO -->
            <table style="width:92%; margin : 0 auto;">
                <tr>
                    <td>
                        <b><?php echo $st['stnameeng']; ?></b><br>
                        <b><?php echo $st['stnameben']; ?></b><br>
                        ID: <?php echo $st['stid']; ?>

                        <table style="width:100%; margin-bottom:7px;">
                            <tr>
                                <td>Class: <b><?php echo $clsname; ?></b></td>
                                <td>Section: <b><?php echo $secname; ?></b></td>
                                <td>Roll: <b><?php echo $st['rollno']; ?></b></td>
                            </tr>
                        </table>




                    </td>

                    <td style="width:80px; text-align:right; padding-left:5mm;">
                        <img src="https://www.eimbox.com/students/<?= $st['stid'] ?>.jpg" height="80" style="border:1px solid #000;">

                    </td>
                </tr>
            </table>



            <table style="width:92%; margin:auto;">
                <tr>
                    <td style="width:45%; vertical-align: top;;">
                        <table style="width:100%; font-size:10px; border-collapse: collapse; ">
                            <tr>
                                <th style="border:1px solid gray; padding:2px; text-align:center; border-collapse: collapse;">Date</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center; border-collapse: collapse;">Day</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center; border-collapse: collapse;">Time</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center; border-collapse: collapse;">Subject</th>
                            </tr>

                            <?php foreach ($data as $r) { ?>
                                <tr>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;  border-collapse: collapse;">
                                        <?= date('d/m/Y', strtotime($r['date'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;  border-collapse: collapse;">
                                        <?= date('l', strtotime($r['date'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;  border-collapse: collapse;">
                                        <?= date('h:i A', strtotime($r['time'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:left;  border-collapse: collapse;"><?= $r['subject'] ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>


                    <td style="width:55%;">
                        <table style="width:100%; text-align:center;">
                            <tr>
                                <td colspan="2" style="text-align:left; font-size:10px; padding-left:7px;">
                                <div style="font-size:12px; font-weight:bold; color:red; margin-bottom:5px; text-align: center;">Read this instruction :</div>    
                                <ul style="margin:0;">
                                        <li>Don’t be late. Report to the hall min 15 min. before the exam
                                            starts.</li>
                                        <li>Carry your admit card and occupy the seat where your roll is marked.
                                        </li>
                                        <li>Carry your own stationary with calculator. Programmable Calculator
                                            and any electronic gadgets are not allowed.</li>
                                        <li>Don’t exchange stationary or calculator with others without
                                            invigilator permission.</li>
                                        <li>Don’t tear/damage your seat card on desk.</li>
                                        <li>Don’t any misbehave/argue with invigilator and others.</li>
                                        <li>Submit all of invalid equipment/docs to the invigilator before start
                                            exam and collect them before exiting hall.</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12px;  text-align:center;">

                                   <img src="<?php echo 'https://eimbox.com/sign/' . $cteacherid; ?>.png"
                                                height="32"
                                                onerror="this.style.display='none'; this.insertAdjacentHTML('afterend','<span style=\'font-family:Kunstler Script; \'><?= $cteachername ?></span>');">
                                    <br>
                                    <b>(<?php echo $cteachername; ?>)</b> <br> Class Teacher
                                </td style="font-size:12px;">
                                <td style="font-size:12px; text-align:center;">
                                    <img src="<?php echo 'https://eimbox.com/sign/' . $sccode; ?>.png" height="32px" /><br>
                                    <?php echo '<b>' . $headname . '</b><br>' . $headtitle; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size:8px;padding-bottom:5px;">
                                    <?php echo $scname; ?><br>
                                    <?php echo $scaddress; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="height:17px;"></div>
            <!-- ROUTINE TABLE -->


        </div>
    <?php } ?>
</div>