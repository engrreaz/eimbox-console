<?php
session_start();
include_once 'core/config.php';
include_once 'core/db.php';
include_once 'core/global_values.php';


$classname = $_GET['cls'] ?? '';
$sectionname = $_GET['sec'] ?? '';
// $exam = $_GET['exam'];
// $sub = $_GET['sub'];
// $assess = $_GET['assess'];
// $tkn = $_GET['token'];

// $rs = $_GET['rollst'];
// $re = $_GET['rollto'];


// $prdate = $re = $_GET['dt'];
// ;
$dtf = $_GET['dfrom'] ?? date('Y-m-d');
$dtt = $_GET['dto'] ?? date('Y-m-d');
$sy = 2025;



$sql0x2 = "SELECT * from areas where areaname='$classname' and subarea = '$sectionname' and sessionyear = '$sy' and user='$rootuser'";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $ctid = $row0x2["classteacher"];
    }
}

$sql0x2v = "SELECT * from teacher where sccode='$sccode' and (position='Head Teacher' or position='Principal')";
$result0x2v = $conn->query($sql0x2v);
if ($result0x2v->num_rows > 0) {
    while ($row0x2v = $result0x2v->fetch_assoc()) {
        $hname = $row0x2v["tname"];
        $hpos = $row0x2v["position"];
        $htid = $row0x2v["tid"];
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="css.css?v=a">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />


    <script>
        function hideall(id) {
            alert(id);
            document.getElementById("toptable" + id).innerHTML = "1";
            document.getElementById("midtable" + id).innerHTML = "2";
            document.getElementById("bottomtable" + id).innerHTML = "3";
        }
    </script>


    <style>
        .pic {
            width: 45px;
            height: 45px;
            padding: 1px;
            border-radius: 50%;
            border: 1px solid var(--dark);
            margin: 5px;
        }

        marg {
            width: 12px;
        }

        @media print {
            .noprint {
                display: none !important;
            }

            body {
                width: 267mm !important;
                color: black !important;
                padding: 12mm !important;
            }


        }


        .a {
            font-size: 16px;
            font-weight: 700;
            font-style: normal;
            line-height: 18px;
        }

        .b {
            font-size: 14px;
            font-weight: 400;
            font-style: normal;
            line-height: 15px;
        }

        .c {
            font-size: 12px;
            font-weight: 400;
            font-style: italic;
            line-height: 16px;
        }

        .top {
            font-size: 16x;
            width: 70px;
            text-align: center;
            font-weight: 700;
        }

        .gen {
            font-size: 16px;
            text-align: center;
            font-weight: 400;
            padding: 5px 0;
        }

        .x {
            font-size: 12px;
            font-weight: 400;
            font-style: normal;
            line-height: 15px;
        }

        .y {
            font-size: 14px;
            font-weight: 600;
            font-style: normal;
            line-height: 15px;
        }

        #boxtbl tr,
        #boxtbl td {
            border: 1px solid gray;
        }

        thead {
            display: table-header-group;
        }

        .gap {
            vertical-align: top;
            padding: 2px 5px 2px 2px;
        }

        .gap small {
            font-size: 10px;
        }

        .rndbox {
            border: 1px solid gray;
            border-radius: 4px;
            height: 62px;
            padding: 8px;
            margin: 0 5px;
        }

        .rndbox table {
            width: 100%;
            ;
        }

        .sh {
            height: 62px;
        }

        .sh2 {
            height: 40px;
            text-align: center;
        }

        .sh3 {
            height: 50px;
            text-align: center;
            line-height: 15px;
        }

        .itl {
            font-size: 10px;
            font-style: italic;
        }

        .topic tr,
        .topic td {
            border: 1px solid gray;
            text-align: justify;
            padding: 5px;
            vertical-align: top;
        }

        .ttl {
            font-size: 1.25rem;
            text-align: center;
            line-height: 1.5rem;
            width: 25%;
        }

        .ttlb {
            font-size: 1.15rem;
            text-align: center;
            line-height: 1.25rem;
        }

        .ttleng {
            font-size: 1.1rem;
            text-align: center;
            line-height: 1.25rem;
            width: 25%;
        }

        .sct {
            text-align: center;
        }

        .tsign {
            height: 15mm;
        }

        #item td,
        #item th {
            border: 1px solid black;
            padding: 3px 5px;
        }

        th {
            font-size: 13px;
            padding: 3px 5px;
        }

        .tsing:before {
            content: ' ';
            display: block;
            position: absolute;
            height: 15mm;
            background-image: url('https://eimbox.com/sign/105673.png');
        }

        .pop {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            font-style: italic;
            border: 0px solid red;
        }
    </style>
</head>

<body style="background:white; margin:0 !important; padding:0 !important; font-family: Segoe UI, SutonnyOMJ ;">
    <header>
        <!-- place navbar here -->
    </header>
    <main>

        <div style="page-break-after:always; border:0px dashed gray; padding:15mm 15mm 15mm 25mm; ">
            <table style="margin:auto;">
                <tr>
                    <td style="width:70px;">
                        <img src="https://eimbox.com/logo/103187.png" width="60" />
                    </td>
                    <td>
                        <div class="a"><?php echo $scname; ?></div>
                        <div class="b"><?php echo $scaddress; ?></div>
                        <div class="b"><?php echo 'Mobile : ' . $scmobile; ?></div>
                    </td>
                </tr>

            </table>


            <div
                style="padding:5px; margin: 10px auto; border-bottom: 1px solid gray; width: 50%; text-align:center; font-size:20px; font-weight:700;">
                Daily Collection Summery
            </div>
            <div style="text-align:center; border-bottom:1px solid gray; width: 100%;  margin-bottom:10px;">
                <?php echo 'Report between : <b>' . date('l, d-m-Y', strtotime($dtf)) . '</b> and <b>' . date('l, d-m-Y', strtotime($dtt)) . '</b>'; ?>
            </div>
            <br>


            <table style="width:100%;" class="table table-striped">
                <?php



                $cnt = 0;
                $mot = 0;
                $mkoyta = 0;
                $nidx = '';
                $sql0 = "SELECT partid from cashbook where sessionyear='$sy' and  sccode = '$sccode' and type='Income' and date between '$dtf' and '$dtt' group by partid order by partid";
                //echo $sql0; 
                $result0qtf = $conn->query($sql0);
                if ($result0qtf->num_rows > 0) {
                    while ($row0 = $result0qtf->fetch_assoc()) {
                        $nid = $row0['partid'] + 1000;
                        $nidx = $nidx . $nid;
                        $cnt++;
                    }
                }
                //echo $nidx;
                
                echo '<thead style="border:1px solid gray;"><th>Class</th><th>Sec</th><th  style="text-align:center;">Count</th>';
                for ($i = 0; $i < $cnt; $i++) {
                    echo '<th style="text-align:right;">' . chr(65 + $i) . '&nbsp;&nbsp;</th>';
                }
                echo '<th style="text-align:right;">Total</th></thead>';

                $sql0 = "SELECT date, particulars from cashbook where sessionyear='$sy' and  sccode = '$sccode' and type='Income' and date between '$dtf' and '$dtt' group by date, particulars order by date, particulars ";  //ORDER BY , ID
                $result0qt = $conn->query($sql0);
                if ($result0qt->num_rows > 0) {
                    while ($row0 = $result0qt->fetch_assoc()) {
                        $date = $row0["date"];
                        $parti = $row0["particulars"];
                        ?>

                        <tr>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $parti; ?></td>
                            <td style="text-align:center;"><?php echo $pid; ?></td>
                            <?php
                            $linemot = 0;
                            for ($i = 0; $i < $cnt; $i++) {
                                $partid = substr($nidx, $i * 4, 4) % 1000;
                                $sql0 = "SELECT partid, sum(amount) as ttkk from cashbook where sessionyear='$sy' and  sccode = '$sccode' and type='Income' and date ='$date' and partid='$partid' and particulars='$parti' ";
                                $result0qtfx = $conn->query($sql0);
                                if ($result0qtfx->num_rows > 0) {
                                    while ($row0 = $result0qtfx->fetch_assoc()) {
                                        $partid = $row0["partid"];
                                        $ttkk = $row0["ttkk"];

                                        $sql0 = "SELECT sum(pr1) as pr1, sum(pr2) as pr2  from stfinance where sessionyear='$sy' and  sccode = '$sccode' and partid='$partid' and classname='$cn' and sectionname='$sec'  and ( pr1date='$prdate' or pr2date='$prdate' )  ";
                                        //    echo $sql0;
                                        $result0qtfxv = $conn->query($sql0);
                                        if ($result0qtfxv->num_rows > 0) {
                                            while ($row0 = $result0qtfxv->fetch_assoc()) {
                                                $csh = $row0["pr1"] + $row0["pr2"];
                                            }
                                        }


                                    }
                                }
                                echo '<td style="text-align:right;">' . $ttkk . '</td>';
                                $linemot += $ttkk;

                            }



                            ?>


                            <td style="text-align:right;"><?php echo $linemot; ?></td>
                        </tr>


                        <?php
                        $mot += $linemot;
                    }
                }


                ?>
                <tfooter>
                    <th colspan="2" style="text-align: right; ">Total Receipt : </th>
                    <th style="text-align: center; "><?php echo $mkoyta; ?></th>
                    <th colspan="<?php echo $cnt; ?>" style="text-align: right; ">Total Amount : </th>
                    <th style="text-align: right; "><?php echo $mot; ?></th>
                </tfooter>

            </table>

            <br>

            <b>Particular / Category Wize Collection Report</b>
            <hr style="width:35%;">
            <table class="table table-striped">
                <?php




                $linemotx = 0;
                for ($i = 0; $i < $cnt; $i++) {
                    $str = 65 + $i;
                    $partid = substr($nidx, $i * 4, 4) % 1000;
                    $sql0 = "SELECT partid, sum(amount) as ttkks from cashbook where sessionyear='$sy' and  sccode = '$sccode' and type='Income' and date between '$dtf' and '$dtt' and partid='$partid'  ";
                    //echo $sql0; 
                    $result0qtfxx = $conn->query($sql0);
                    if ($result0qtfxx->num_rows > 0) {
                        while ($row0 = $result0qtfxx->fetch_assoc()) {
                            $partid = $row0["partid"];
                            $ttkks = $row0["ttkks"];

                            $sql0 = "SELECT id, particulareng, particularben from financesetup where sessionyear='$sy' and  sccode = '$sccode' and id = '$partid'";
                            // $sql0 = "SELECT id, particulareng, particularben from financesetup where id = '$partid'";
                            //echo $sql0; 
                            $result0qtf5 = $conn->query($sql0);
                            if ($result0qtf5->num_rows > 0) {
                                while ($row0 = $result0qtf5->fetch_assoc()) {
                                    $deseng = $row0["particulareng"];
                                    $desben = $row0["particularben"];
                                }
                            }


                        }
                    }
                    ?>
                    <tr>
                        <td style=""><?php echo chr($str); ?> </td>
                        <td style=""><?php echo $deseng; ?> </td>
                        <td style=""><?php echo ' <span style="font-size:18px;">(' . $desben . ')</span>'; ?> </td>
                        <td style="text-align: right; "><?php echo $ttkks; ?> </td>
                    </tr>
                    <?php
                    $linemotx += $ttkks;

                }








                ?>

                <tfooter>
                    <th colspan="3" style="text-align: right; ">Total : </th>
                    <th style="text-align: right; "><?php echo $linemotx; ?></th>
                </tfooter>

            </table>




            <table style="width:100%; margin-top:50px;">

                <tr>
                    <td class="pop"></td>
                    <td class="pop">Principal</td>
                    <td class="pop">Accountant</td>
                </tr>
            </table>




        </div>











    </main>
    <div style="height:52px;"></div>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
        </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
        </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <script>
        document.getElementById("cnt").innerHTML = "<?php echo $cnt; ?>";

        function go() {
            var cls = document.getElementById("classname").value;
            var sec = document.getElementById("sectionname").value;
            var sub = document.getElementById("subject").value;
            var assess = document.getElementById("assessment").value;
            var exam = document.getElementById("exam").value;
            let tail = '?exam=' + exam + '&cls=' + cls + '&sec=' + sec + '&sub=' + sub + '&assess=' + assess;
            if (cls == 'Six' || cls == 'Seven') {
                window.location.href = "pibiprint.php" + tail;
            } else {
                alert("Select Class Six/Seven Only");
            }
        }  
    </script>

    <script>
        function prnt(id) {
            if (id == 0) {
                $('.level').hide();
                $('.topic').hide();
            } else if (id == 1) {
                $('.level').hide();
                $('.topic').show();
            } else if (id == 2) {
                $('.level').show();
                $('.topic').show();
            }
        }
    </script>

    <script>
        function fetchsection() {
            var cls = document.getElementById("classname").value;

            var infor = "user=<?php echo $rootuser; ?>&cls=" + cls;
            $("#sectionblock").html("");

            $.ajax({
                type: "POST",
                url: "fetchsection.php",
                data: infor,
                cache: false,
                beforeSend: function () {
                    $('#sectionblock').html('<span class=""><center>Fetching Section Name....</center></span>');
                },
                success: function (html) {
                    $("#sectionblock").html(html);
                }
            });
        }
    </script>

    <script>
        function fetchsubject() {
            var cls = document.getElementById("classname").value;
            var sec = document.getElementById("sectionname").value;

            var infor = "sccode=<?php echo $sccode; ?>&cls=" + cls + "&sec=" + sec;
            $("#subblock").html("");

            $.ajax({
                type: "POST",
                url: "fetchsubject.php",
                data: infor,
                cache: false,
                beforeSend: function () {
                    $('#subblock').html('<span class="">Retriving Subjects...</span>');
                },
                success: function (html) {
                    $("#subblock").html(html);
                }
            });
        }

        function print() {
            window.print();
        }
    </script>



</body>

</html>