<?php



$txt = '';
if ($secname == '') {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sessionyear = '$sy' ";
} else {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' ";
}

$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $stsd = $row000111["sts"];
    }
}

if ($secname == '') {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn'  and sessionyear = '$sy' and totalmarks>0 ";
} else {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' and totalmarks>0 ";
}

$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $stappear = $row000111["sts"];
    }
}

$abs = $stsd - $stappear;

if ($secname == '') {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn'  and sessionyear = '$sy'  and gpa>0";
} else {
    $sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy'  and gpa>0";
}

$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $passst = $row000111["sts"];
    }
}

if ($secname == '') {
    $sql000111 = "SELECT gla, count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn'  and sessionyear = '$sy' group by gla order by gla ";
} else {
    $sql000111 = "SELECT gla, count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' group by gla order by gla ";
}

$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $gla = $row000111["gla"];
        $sts = $row000111["sts"];
        $txt = $txt . "['" . $gla . "', " . $sts . '],';
    }
}



// if (($cn == 'Six') || ($cn == 'Seven')) {
//     $tsub = 8;
// } else if (($cn == 'Eight')) {
//     $tsub = 7;
// } else {
//     $tsub = 9;
// }

$tsub = 10;

$stprofile = array();
$sql00 = "SELECT stid, stnameeng, stnameben, previll FROM students where  sccode='$sccode' ";
$result00 = $conn->query($sql00);
if ($result00->num_rows > 0) {
    while ($row00 = $result00->fetch_assoc()) {
        $stprofile[] = $row00;
    }
}


?>




<div class="full-box-2">

    <table width="100%" style="margin:5mm 15mm; border:1;">
        <tr>
            <td style="padding:2px;">
                <table width="100%" id="topdata">
                    <tr>
                        <td>
                            Class : <b>
                                <?php echo $cn; ?>
                            </b>
                        </td>
                        <td>
                            Section/Group : <b>
                                <?php echo $secname; ?>
                            </b>
                        </td>
                        <td>
                            Total Student : <b>
                                <?php echo $stsd; ?>
                            </b>
                        </td>
                        <td>
                            Appear in Exam : <b>
                                <?php echo $stappear; ?>
                            </b>
                        </td>
                        <td>
                            Pass : <b>
                                <?php echo $passst; ?>
                            </b>
                        </td>
                        <td>
                            Passing Rate : <b>
                                <?php
                                if ($stappear > 0) {
                                    $passrate = $passst * 100 / $stappear;
                                } else {
                                    $passrate = 0;
                                }
                                echo sprintf('%0.2f', $passrate) . '%';
                                ?>
                            </b>
                        </td>
                    </tr>
                </table>


            </td>


        </tr>
    </table>

<style>
    th {
        line-height:12px;
    }
</style>
    <table style="margin:5mm 15mm; border:1px solid black; width:100%;">
        <thead>
            <th style="padding:5px; text-align:center;">Merit <br><small>(Section)</small> </th>
            <th style="font-size:11px; text-align:center;">Name of Students</th>
            <th style="font-size:11px; text-align:center;">Class Roll</th>
            <th style="font-size:11px; text-align:center;">Section</th>

            <th style="padding:5px; text-align:center;">Marks Obtained</th>
            <th style="padding:5px; text-align:center;">GPA</th>
            <th style="padding:5px; text-align:center;">GLA</th>
            <th style="padding:5px; text-align:center;">Merit <br><small>(Class)</small> </th>
            <th style="padding:5px; text-align:center;">Merit <br><small>(Gender)</small> </th>
            <th style="padding:5px; text-align:center;">Pass/Fail</th>

            </tr>
        </thead>
        <tbody>
            <?php
            if ($secname == '') {
                $sql000111 = "SELECT * from tabulatingsheet where  sccode='$sccode'  and classname='$cn'  and exam='$exam' and sessionyear = '$sy' and totalmarks>0  order by cast(meritplace as int)";
            } else {
                $sql000111 = "SELECT * from tabulatingsheet where  sccode='$sccode'  and classname='$cn' and sectionname='$secname' and exam='$exam' and sessionyear = '$sy' and totalmarks>0  order by (meritplace * 1)";
            }

            //  echo $sql000111;
            
            // 
            $result000111 = $conn->query($sql000111);
            if ($result000111->num_rows > 0) {
                while ($row000111 = $result000111->fetch_assoc()) {
                    $stid = $row000111["stid"];
                    $meritplace = $row000111["meritplace"];
                    $meritplacecomb = $row000111["meritplacecomb"];
                    $meritplacegen = $row000111["meritplacegender"];
                    $totalmarks = $row000111["totalmarks"];
                    $rollno = $row000111["rollno"];
                    $gpa = $row000111["gpa"];
                    $gla = $row000111["gla"];
                    $tf = $row000111["totalfail"];
                    $snm = $row000111["sectionname"];
                    $thisex = $row000111["thisexam"];

                    if ($tf == 0) {
                        $clc = "fg-green";
                        $txt = "Passed";
                    } else {
                        $clc = "fg-red";
                        $txt = $tf . " Sub Failed";
                    }


                    // $sql000111r = "SELECT * from students where  stid='$stid' ";
                    // $result000111r = $conn->query($sql000111r);
                    // if ($result000111r->num_rows > 0) {
                    //     while ($row000111r = $result000111r->fetch_assoc()) {
                    //         $stnameeng = $row000111r["stnameeng"];
                    //     }
                    // }
            
                    $ind = array_search($stid, array_column($stprofile, 'stid'));
                    $neng = $nben = $vill = '';
                    if ($ind != NULL || $ind != '') {
                        $stnameeng = $stprofile[$ind]["stnameeng"];
                    }

                    ?>
                    <tr>
                        <td class="cent">
                            <?php echo $meritplace; ?>
                        </td>

                        <td class="cent" style="text-align:left;">
                            <?php echo $stnameeng; ?>
                        </td>
                        <td class="cent">
                            <?php echo $rollno; ?>
                        </td>
                        <td class="cent">
                            <?php echo $snm; ?>
                        </td>
                        <td class="cent">
                            <?php echo $totalmarks;

                            ?>
                        </td>

                        <td class="cent">
                            <?php echo $gpa; ?>
                        </td>

                        <td class="cent">
                            <?php echo $gla; ?>
                        </td>
                        <td class="cent">
                            <?php echo $meritplacecomb; ?>
                        </td>
                        <td class="cent">
                            <?php $meritplacegen = ''; echo $meritplacegen; ?>
                        </td>
                        <td class="cent <?php echo $clc; ?>">
                            <?php echo $txt; ?>
                        </td>

                    </tr>
                <?php }
            } ?>
        </tbody>
    </table>
</div>