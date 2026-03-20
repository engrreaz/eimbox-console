<?php



$txt = '';
$sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' ";
$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $stsd = $row000111["sts"];
    }
}

$sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' and totalmarks>0 ";
$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $stappear = $row000111["sts"];
    }
}

$abs = $stsd - $stappear;

$sql000111 = "SELECT count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' and slot='$slot'  and gpa>0";
// echo $sql000111;
$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $passst = $row000111["sts"];
    }
}

$sql000111 = "SELECT gla, count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' group by gla order by gla ";
$result000111 = $conn->query($sql000111);
if ($result000111->num_rows > 0) {
    while ($row000111 = $result000111->fetch_assoc()) {
        $gla = $row000111["gla"];
        $sts = $row000111["sts"];
        $txt = $txt . "['" . $gla . "', " . $sts . '],';
    }
}

$tsub = 0;

$subnamelist = array();
$sql000111r = "SELECT * from subjects where sccategory = '$sctype' ";
$result000111r = $conn->query($sql000111r);
if ($result000111r->num_rows > 0) {
    while ($row000111r = $result000111r->fetch_assoc()) {
        $subnamelist[] = $row000111r;
    }
}

$glcnt = array();
$sql000111xt = "SELECT subject, gl, count(gl) as gon from stmark where  sccode='$sccode'  and classname='$cn' and sectionname='$secname' and exam='$exam' group by subject, gl";
// echo $sql000111xt;
$result000111xt = $conn->query($sql000111xt);
if ($result000111xt->num_rows > 0) {
    while ($row000111xt = $result000111xt->fetch_assoc()) {
        $glcnt[] = $row000111xt;
    }
}
$num = count($glcnt);
// var_dump($glcnt);
?>

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        #stat-table td {
            border: 0;
            padding: 1px;
        }

        .page-a4{
            height:296mm;
            page-break-after: always;
            padding:5mm 10mm 5mm 20mm;
        }
    </style>
    
</head>

<body>

    <!-----------------------------------------------------------------------------------

 <td>
                    <div id="piechart" style="width:450px; height: 250px;"></div>
                </td>
                ----------------------------------->

<div class="page-a4 full-box">
    This is no  nsisdifjs lsjs ld fslkfslfls kjfs flsjflsj fs
</div>






    <div
        style="padding:0; margin:auto; width:<?php echo $paper; ?>; margin-left:0mm; page-break-after:always; font-family:'Segoe UI'; text-align:center; ; ">
        <?php include 'assets/pad/pad.php'; ?>

        <div hidden>
            <br>
            <span style="font-size:16px; line-height:18px;; color:#1b5cc4; font-weight:bold; " onclick="sh();">
                <?php echo $einame; ?>
            </span><br>
            <span style="font-size:11px; color:#a35c35; line-height:12px;"><strong>
                    <?php echo $eiadd . '<br>' . $eicontact; ?>
                </strong></span><br><br>

        </div>



        <span Style="font-size:20px; color:#005678; font-weight:bold">Result : At&#9866;A&#9866;Glance</span>
        <br>
        <span Style="font-size:18px; color:#008765; font-weight:bold">
            <?php echo $exam . ' Examination - ' . $sy; ?>
        </span>

        <table style="margin:0 15mm; width:100%;border:0;">
            <tr style="height:20px; font-weight:bold;">
                <td style="padding:5px; text-align:center;"><span style="color:#e02a67">Class Info</span></td>
                <td style="padding:5px; text-align:center;"><span style="color:#e02a67">Grading Statistics</span></td>
                <td style="padding:5px; text-align:center;"><span style="color:#e02a67">Fail Statistics</span></td>
                <td style="padding:5px; text-align:center;"><span style="color:#e02a67">Grading System</span></td>
            </tr>
            <tr>
                <td style="padding:5px;">
                    <table width="100%" style="border:0;">
                        <tr>
                            <td style="text-align:right; border:0; line-height:20px;">
                                Class :<br>
                                Section/Group :<br>
                                Total Student :<br>
                                Appear in Exam : <br>
                                Pass : <br>
                                Passing Rate :
                            </td>
                            <td style="text-align:left; border:0; line-height:20px;">
                                <b>
                                    <?php echo $cn; ?>
                                </b><br>
                                <b>
                                    <?php echo $secname; ?>
                                </b><br>
                                <b>
                                    <?php echo $stsd; ?>
                                </b><br>
                                <b>
                                    <?php echo $stappear; ?>
                                </b><br>
                                <b>
                                    <?php echo $passst; ?>
                                </b><br>
                                <b>
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



                <td style="padding:5px;">
                    <table border="0" width="100%">
                        <?php
                        $sql000111 = "SELECT gla, count(gla) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and sessionyear = '$sy' and totalmarks > 0  group by gla order by gla ";
                        $result000111 = $conn->query($sql000111);
                        if ($result000111->num_rows > 0) {
                            while ($row000111 = $result000111->fetch_assoc()) {
                                $gla = $row000111["gla"];
                                $sts = $row000111["sts"];
                                $rate = $sts * 100 / $stappear;
                                ?>
                        <tr>
                            <td style="width:30%; text-align:right;">
                                <?php echo $gla . '&nbsp;&nbsp;:&nbsp;&nbsp;'; ?>
                            </td>
                            <td>
                                <?php echo '<b>' . $sts . '</b> ( ' . sprintf('%0.2f', $rate) . '% )'; ?>
                            </td>
                        </tr>
                        <?php
                        if ($gla == 'F') {
                            $failst = $sts;
                        }
                            }
                        }
                        ?>
                    </table>

                </td>
                <td style="padding:5px;">
                    <table border="0" width="100%">
                        <?php
                        $sql000111 = "SELECT totalfail, count(totalfail) as sts FROM tabulatingsheet where  sccode='$sccode' and exam='$exam' and classname='$cn' and sectionname='$secname' and totalmarks > 0 and sessionyear = '$sy' and totalfail between 1 and '$tsub'-0 group by totalfail order by totalfail ";
                        $result000111 = $conn->query($sql000111);
                        if ($result000111->num_rows > 0) {
                            while ($row000111 = $result000111->fetch_assoc()) {
                                $gla = $row000111["totalfail"];
                                $sts = $row000111["sts"];
                                $rates = $sts * 100 / $failst;
                                ?>
                        <tr>
                            <td style="width:50%; text-align:right;">
                                <?php echo '<b>' . $gla . '</b> Subject(s)&nbsp;&nbsp;:&nbsp;&nbsp;'; ?>
                            </td>
                            <td>
                                <?php echo '<b>' . $sts . '</b> ( ' . sprintf('%0.2f', $rates) . '% )'; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        ?>
                    </table>

                </td>

                <td style="padding:0px;">
                    <table border="1" width="100%" style="color:#1c702c">
                        <tr>
                            <td colspan="2" align="center" style="font-size:9px; font-weight:bold;">Grade</td>
                            <td align="center" style="font-size:9px; font-weight:bold;">Marks Range</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">A+</td>
                            <td align="center" style="font-size:9px;">5.00</td>
                            <td align="center" style="font-size:9px;">80+</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">A</td>
                            <td align="center" style="font-size:9px;">4.00</td>
                            <td align="center" style="font-size:9px;">70 - 79</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">A-</td>
                            <td align="center" style="font-size:9px;">3.50</td>
                            <td align="center" style="font-size:9px;">60 - 69</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">B</td>
                            <td align="center" style="font-size:9px;">3.00</td>
                            <td align="center" style="font-size:9px;">50 - 59</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">C</td>
                            <td align="center" style="font-size:9px;">2.00</td>
                            <td align="center" style="font-size:9px;">40 - 49</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">D</td>
                            <td align="center" style="font-size:9px;">1.00</td>
                            <td align="center" style="font-size:9px;">33 - 39</td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size:9px;">F</td>
                            <td align="center" style="font-size:9px;">0.00</td>
                            <td align="center" style="font-size:9px;">0 - 32</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table style="margin:1mm 15mm; width:100%;border:0;">
            <tr style="border:0;">
                <td style="background:white; color: black; text-align:center; border:0;">
                    <b>Subject Wise Grading Analysis</b>
                </td>
            </tr>
        </table>

        <table id="stat-table" width="100%" style="margin:2mm 15mm; border:1px solid black;">
            <tr style="font-weight:bold; color:green; border:1px solid black;">
                <td colspan="2" style="padding:5px;" text-align="center">Subject</td>
                <td text-align="center" style="font-size:11px;">Full Marks</td>
                <td text-align="center" style="font-size:11px;">Highest Marks</td>


                <td text-align="center" style="padding:5px;">A+</td>
                <td text-align="center" style="padding:5px;">A</td>
                <td text-align="center" style="padding:5px;">A-</td>
                <td text-align="center" style="padding:5px;">B</td>
                <td text-align="center" style="padding:5px;">C</td>
                <td text-align="center" style="padding:5px;">D</td>
                <td text-align="center" style="padding:5px; color:red;">F</td>
            </tr>
            <?php
            $hit = 0;
            $sql000111 = "SELECT * from subsetup where  sccode='$sccode'  and classname='$cn' and sectionname='$secname' and sessionyear='$sy' order by subject ";
            $result000111 = $conn->query($sql000111);
            if ($result000111->num_rows > 0) {
                while ($row000111 = $result000111->fetch_assoc()) {
                    $subcode = $row000111["subject"];
                    $fms = $row000111["fullmarks"];

                    $ind = array_search($subcode, array_column($subnamelist, 'subcode'));
                    $subname = $subnamelist[$ind]["subject"];
                    $subshname = $subnamelist[$ind]["subshname"];
                    $ap = $a = $am = $b = $c = $d = $f = 0;
                    for ($n = 0; $n < $num; $n++) {
                        if ($glcnt[$n]['subject'] == $subcode) {
                            if ($glcnt[$n]['gl'] == 'A+') {
                                $ap += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'A') {
                                $a += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'A-') {
                                $am += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'B') {
                                $b += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'C') {
                                $c += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'D') {
                                $d += $glcnt[$n]['gon'];
                            } else if ($glcnt[$n]['gl'] == 'F') {
                                $f += $glcnt[$n]['gon'];
                            }
                        }
                    }

                    ?>
            <tr>
                <td align="center">
                    <?php echo $subcode; ?>
                </td>
                <td style="padding:3px; text-align:left;">
                    <?php echo $subname . ' <b>[' . $subshname . ']</b>'; ?>
                </td>
                <td align="center" style="padding:3px;">
                    <?php echo $fms; ?>
                </td>
                <td>
                    <?php
                    $sql000111xt = "SELECT * from stmark where  sccode='$sccode'  and classname='$cn' and sectionname='$secname' and exam='$exam' and subject='$subcode'  and sessionyear = '$sy'  order by markobt desc limit 1";
                    $result000111xt = $conn->query($sql000111xt);
                    if ($result000111xt->num_rows > 0) {
                        while ($row000111xt = $result000111xt->fetch_assoc()) {
                            $gonona = $row000111xt["markobt"];
                        }
                    }
                    // $gonona = 77;
                    echo '<center>' . $gonona . '</center>';
                    ?>
                </td>

                <td align="center" width="10mm">
                    <?php
                    $subarr = array();
                    echo '<center>' . $ap . '</center>';
                    ?>
                </td>
                <td>
                    <?php echo '<center>' . $a . '</center>'; ?>
                </td>
                <td>
                    <?php echo '<center>' . $am . '</center>'; ?>
                </td>
                <td>
                    <?php echo '<center>' . $b . '</center>'; ?>
                </td>
                <td>
                    <?php echo '<center>' . $c . '</center>'; ?>
                </td>
                <td>
                    <?php echo '<center>' . $d . '</center>'; ?>
                </td>
                <td>
                    <?php
                    $fsub = $f - $abs;
                    echo '<center><span style="color:red;">' . $fsub . '</span></center>';
                    ?>
                </td>
            </tr>
            <?php
                }
            } ?>

        </table>

    </div>

    <!---------------------------------------------------------------------------------------------------------------------->


</body>

</html>