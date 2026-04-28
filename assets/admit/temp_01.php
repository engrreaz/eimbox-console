<style>
    #rout td {
        border: 1px solid black;
    }
</style>

<tr>
    <td valign="top" class="backpic" background="assets/admit/sample_02.png"
        style="width:210mm;   padding:<?php   echo $paad; ?>mm;   font-family:'Segoe UI'; border:0px solid red; border-collapse:collapse;">

        <table style="font-size:10px; margin:auto; border:0;" class="hideshow">
     
            <tr>
                <td  style="padding-right:0px; text-align:right;" vlign="top">

                    <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" height="60px" />
                </td>
                <td style="color:black; padding-left:20px;  font-family:'Segoe UI'; font-size:20px;" vlign="top">
                    <b><?php echo $scname; ?></b>



                    <div style="font-size:12px;">
                        <?php echo $scadd1 . ', ' . $ps . ', ' . $dist; ?>
                    </div>
                    <div style="font-size:12px;">
                        <?php echo 'Contact : ' . $mobile; ?>
                    </div>
                    
                </td>
            </tr>


            <tr>
                <td colspan="2">
                    <div style="text-align:center;">
                        <img src="assets/admit/admit.png" height="35px" />
                    </div>


                    <div  style=" padding:0; text-align:center; margin-top:0px; font-family:Segoe UI; font-size:20px; font-weight: bold; ">
                        <?php echo $exam2 . ' Examination - ' . $sy; ?>
                    </div>

                </td>
            </tr>

        </table>

        <table style="font-size:12px; width:100%; border:0;">
            <tr>


                <td valign="top" style="padding: 5px 0 5px 50px;">
                    Name of Student<br><span style="font-size:18px;  font-weight:bold;"><?php echo $stnameeng; ?></span>
                    <br>
                    Class : <b><?php echo $classname; ?></b>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php echo $secgr; ?> : <b><?php echo $sectionname; ?></b>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Roll : <b><?php echo $rollno; ?></b>
                    <br>
                    Student's ID : <?php echo $stid; ?>
                </td>

                <td style="padding-right:1px;" width="105px">
                    <?php
                    $file_pointer = "../students/" . $stid . ".jpg";
                    if (file_exists($file_pointer) === TRUE) {

                        $sobi = $BASE__PATH . "/students" . "/" . $stid . ".jpg";
                        ?>
                        <img src="<?php echo $sobi; ?>" alt="" height="85px"
                            style="border-radius:0%; border : 1px solid black; padding:3px; " />
                    <?php } else { ?>
                        <img src="http://www.eimbox.com/admit/noimg.jpg" alt="" height="90px"
                            style="border-radius:0%; border : 1px solid black; padding:3px; right:10px;" />

                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-left:30px;">
                    <table width="100%">
                        <tr>
                            <td style="padding-right:10px; width:55%; vertical-align: top;;">
                            <div class="text-center" style="font-weight:bold">Exam Schedule</div>    
                            <table id="rout"
                                    style="width:100%; border:1px solid gray; border-collapse:collapse; font-size:10px;">
                                    <tr>
                                        <td style="text-align:center" colspan="2"><b>Date</b></td>
                                        <td style="text-align:center"><b>Time</b></td>
                                        <td style="text-align:center"><b>Subject</b></td>
                                    </tr>

                                    <?php
                                    foreach ($routine as $xx) {
                                            $edate = $xx["date"];
                                            $etime = $xx["time"];
                                            $subj = $xx["subj"];
                                            ?>
                                            <tr>
                                                <td style="text-align:center"><?php echo date('d/m/Y', strtotime($edate)); ?>
                                                </td>
                                                <td style="text-align:center"><?php echo date('l', strtotime($edate)); ?>
                                                </td>
                                                <td style="text-align:center"><?php echo date('h:i:s A', strtotime($etime)); ?>
                                                </td>
                                                <td style="text-align:left; padding-left:8px;"><?php echo $subj; ?></td>
                                            </tr>
                                            <?php
                                        }
                                    
                                    ?>
                                </table>
                            </td>


                            <td style="font-size:11px; padding-bottom:10px; text-align:center;" valign="bottom">
                                <table style="width:100%; text-align:center;">
                                    <tr>
                                        <td colspan="2" style="text-align:left; font-size:10px;">
                                            <ul>
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

                                            <!-- <img src="<?php echo 'https://eimbox.com/sign/' . $ctea; ?>.png"
                                                height="40px" /> -->
                                                <br>
                                            <b>(<?php echo $cteaname; ?>)</b> <br> Class Teacher
                                        </td style="font-size:12px;">
                                        <td style="font-size:12px; text-align:center;">
                                            <img src="<?php echo 'https://eimbox.com/sign/' . $sccode; ?>.png"
                                                height="35px" /><br>
                                            <?php echo '<b>' . $headname . '</b><br>' . $headtitle; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="font-size:8px;">
                                            <?php echo $scname; ?><br>
                                            <?php echo $scadd1 . ', ' . $ps . ', ' . $dist; ?>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
            <tr>
                <td valign="top">



                </td>
            </tr>
        </table>

    </td>
</tr>

<?php
// if ($ssl >= 3 && $ssl % 2 == 1) {
if ($ssl >= 3 && $ssl % 2 == 0) {
    echo '<tr><td style="page-break-after:always;"></td></tr>';
}

if ($ssl >= 3 && $ssl % 2 == 1) {
    echo '<tr><td style="height:2mm;"></td></tr>';
}
?>