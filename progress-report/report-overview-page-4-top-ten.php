<?php

$all_student_profile_data = fetchAll($conn, "SELECT * FROM students");

// echo $secname;
if ($secname != '' && $cn != '') {
    $sql000111 = "SELECT * FROM tabulatingsheet where exam='$exam'  and sessionyear = '$sessionyear' and sccode='$sccode' and totalfail>=0 and classname='$cn' and sectionname= '$secname' order by totalmarks DESC LIMIT 10";
    $ttl = $cn . ' (' . $secname . ') ';
}
if ($cn != '' && $secname == '') {
    $sql000111 = "SELECT * FROM tabulatingsheet where exam='$exam'  and sessionyear = '$sessionyear' and sccode='$sccode' and totalfail>=0 and classname='$cn' order by totalmarks DESC LIMIT 10";
    $ttl = $cn;
} else {
    $sql000111 = "SELECT * FROM tabulatingsheet where exam='$exam'  and sessionyear = '$sessionyear' and sccode='$sccode' and totalfail>=0 order by totalmarks DESC LIMIT 10";
    $ttl = ' entire school';
}
$top10 = array();
// echo $sql000111;
$result000111top = $conn->query($sql000111);
if ($result000111top->num_rows > 0) {
    while ($row000111 = $result000111top->fetch_assoc()) {
        $top10[] = $row000111;
    }
}
// var_dump($top10);
?>
<div class="page-a4">
    <?php include 'assets/pad/letter-head.php'; ?>

    <style>
        #top10-table,
        #top10-table tr,
        #top10-table td,
        #top10-2,
        #top10-2 tr,
        #top10-2 td {
            border: 0;
        }

        .img {
            border-radius: 50%;
            border: 1px solid lightgray;
            margin: 0;
        }

        .name {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin: 0;
        }

        .cls {
            font-style: italic;
            font-size: 12px;
            text-align: center;
            margin: 0 0 10px 0;
            line-height: 12px;
        }

        .mpla {
            text-align: center;
            font-size: 16px;
        }

        .leader {
            background: white;
            width: 50px;
            border: 3px solid black;
            padding: 5px;
            margin: auto;
            margin-top: -25px;
            z-index: 999;
            font-size: 24px;
            border-radius: 6px;
            font-weight: 700;
        }
    </style>
    <div style="text-align:center;;">

        <h3>TOP 10 Performers</h3>
        <h5>Top 10 students of scoring highest marks in <?php echo $ttl; ?> in
            <?php echo $exam . ' Examination - ' . $sy; ?>
        </h5>


        <table style="margin:auto; " id="top10-table">
            <tr>
                <td style="text-align:center;vertical-align:bottom;">
                    <?php
                    $sl = 1;
                    $stid = $top10[$sl]['stid'];
                    $ind = array_search($stid, array_column($all_student_profile_data, 'stid'));
                    $stname = $all_student_profile_data[$ind]['stnameeng'];
                    $cls = $top10[$sl]['classname'];
                    $sec = $top10[$sl]['sectionname'];
                    $rollno = $top10[$sl]['rollno'];
                    $mpla = $top10[$sl]['totalmarks'];
                    $fname = $all_student_profile_data[$ind]['fname'];
                    $vill = $all_student_profile_data[$ind]['previll'];


                    ?>
                    <div style="position:relative; text-align:center;">
                        <img class="img " style="width:100px; height:100px; margin:8px;"
                            src="https://eimbox.com/students/<?php echo $stid; ?>.jpg" />
                        <div class="leader">2</div>
                    </div>

                    <div class="name"><?php echo $stname; ?></div>
                    <div class="cls" style="">
                        <?php echo $cls . ' (' . $sec . ') # ' . $rollno . '<span style="font-style:normal; font-weight:700; font-size:14px; padding-left:5px;">' . $mpla . '</span>'; ?>
                    </div>
                    <div class="cls" style="color:teal">
                        <?php echo $fname . '<br><span style="color:deeppink;">' . $vill . '</span>'; ?>
                    </div>
                </td>
                <td style="text-align:center; vertical-align:bottom;">
                    <?php
                    $sl = 0;
                    $stid = $top10[$sl]['stid'];
                    $ind = array_search($stid, array_column($all_student_profile_data, 'stid'));
                    $stname = $all_student_profile_data[$ind]['stnameeng'];
                    $cls = $top10[$sl]['classname'];
                    $sec = $top10[$sl]['sectionname'];
                    $rollno = $top10[$sl]['rollno'];
                    $mpla = $top10[$sl]['totalmarks'];
                    $fname = $all_student_profile_data[$ind]['fname'];
                    $vill = $all_student_profile_data[$ind]['previll'];

                    ?>
                    <div style="position:relative; text-align:center;vertical-align:bottom;">
                        <img class="img " style="width:150px; height:150px; margin:8px;"
                            src="https://eimbox.com/students/<?php echo $stid; ?>.jpg" />
                        <div class="leader">1</div>
                    </div>

                    <div class="name"><?php echo $stname; ?></div>
                    <div class="cls" style="">
                        <?php echo $cls . ' (' . $sec . ') # ' . $rollno . '<span style="font-style:normal; font-weight:700; font-size:14px; padding-left:5px;">' . $mpla . '</span>'; ?>
                    </div>
                    <div class="cls" style="color:teal">
                        <?php echo $fname . '<br><span style="color:deeppink;">' . $vill . '</span>'; ?>
                    </div>
                </td>
                <td style="text-align:center;">
                    <?php
                    $sl = 2;
                    $stid = $top10[$sl]['stid'];
                    $ind = array_search($stid, array_column($all_student_profile_data, 'stid'));
                    $stname = $all_student_profile_data[$ind]['stnameeng'];
                    $cls = $top10[$sl]['classname'];
                    $sec = $top10[$sl]['sectionname'];
                    $rollno = $top10[$sl]['rollno'];
                    $mpla = $top10[$sl]['totalmarks'];
                    $fname = $all_student_profile_data[$ind]['fname'];
                    $vill = $all_student_profile_data[$ind]['previll'];

                    ?>
                    <div style="position:relative; text-align:center;">
                        <img class="img " style="width:100px; height:100px; margin:8px;"
                            src="https://eimbox.com/students/<?php echo $stid; ?>.jpg" />
                        <div class="leader">3</div>
                    </div>

                    <div class="name"><?php echo $stname; ?></div>
                    <div class="cls" style="">
                        <?php echo $cls . ' (' . $sec . ') # ' . $rollno . '<span style="font-style:normal; font-weight:700; font-size:14px; padding-left:5px;">' . $mpla . '</span>'; ?>
                    </div>
                    <div class="cls" style="color:teal">
                        <?php echo $fname . '<br><span style="color:deeppink;">' . $vill . '</span>'; ?>
                    </div>
                </td>
            </tr>
        </table>
        <table style="margin:auto;" id="top10-2">
            <tr>
                <?php
                for ($a = 0; $a < 7; $a++) {


                    ?>
                    <td style="text-align:center;">
                        <?php
                        $sl = 3 + $a;
                        $stid = $top10[$sl]['stid'];
                        $ind = array_search($stid, array_column($all_student_profile_data, 'stid'));
                        $stname = $all_student_profile_data[$ind]['stnameeng'];
                        $cls = $top10[$sl]['classname'];
                        $sec = $top10[$sl]['sectionname'];
                        $rollno = $top10[$sl]['rollno'];
                        $mpla = $top10[$sl]['totalmarks'];
                        $fname = $all_student_profile_data[$ind]['fname'];
                        $vill = $all_student_profile_data[$ind]['previll'];

                        ?>
                        <div style="position:relative; text-align:center;">
                            <img class="img " style="width:80px; height:80px; margin:8px;"
                                src="https://eimbox.com/students/<?php echo $stid; ?>.jpg" />
                            <div class="leader"><?php echo $sl + 1; ?></div>
                        </div>

                        <div class="name"><?php echo $stname; ?></div>
                        <div class="cls" style="">
                            <?php echo $cls . ' (' . $sec . ') # ' . $rollno . '<span style="font-style:normal; font-weight:700; font-size:14px; padding-left:5px;">' . $mpla . '</span>'; ?>
                        </div>
                        <div class="cls" style="color:teal">
                            <?php echo $fname . '<br><span style="color:deeppink;">' . $vill . '</span>'; ?>
                        </div>
                    </td>
                    <?php
                }
                ?>


            </tr>
        </table>


    </div>
    <div style="text-align:right; padding-top:20px; line-height:15px;">
        <div style="font-size:11px; text-align:center; line-height:12px;">
            <?php echo '<b>( ' . $headname . ' )</b>'; ?><br>
            <small><?php echo $headtitle; ?></small><br>
            <?php echo $scname; ?><br>
            <?php echo $eiaddress; ?>
        </div>
    </div>

</div>
<!---------------------------------------------------------------------------------------------------------------------->