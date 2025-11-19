<?php
session_start();
// include_once 'core/config.php';
// include_once 'core/db.php';
// include_once 'core/global_values.php';
include_once 'header.php';





$dtf = $_GET['dfrom'] ?? date('Y-m-d');
$dtt = $_GET['dto'] ?? date('Y-m-d');

// $dtf = "2025-11-01";
// $dtt = "2025-11-19";


$classList = [];
$sql0x2 = "SELECT areaname, subarea from areas where  sessionyear LIKE '%$y_v2%' and user='$rootuser' order by idno";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $classList[] = $row0x2;
    }
}


$codeList = [];
$sql0x2 = "SELECT itemcode, particulareng, particularben from financesetup where  sessionyear LIKE '%$y_v2%'  and sccode='$sccode' ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $codeList[] = $row0x2;
    }
}


$itemList = [];
$sql0x2 = "SELECT itemcode, particulareng, particularben, sum(pr1) as tk  from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by itemcode order by itemcode ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $itemList[] = $row0x2;
    }
}


$dataList = [];
$sql0x2 = "SELECT classname, sectionname, itemcode, particulareng, particularben, sum(pr1) as taka from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by classname, sectionname, itemcode ";

$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $dataList[] = $row0x2;
    }
}

// echo '<pre>';
// print_r($dataList);
// echo '</pre>';
// input



// classList তোমার দেওয়া অ্যারে (already loaded)

// DB connection
$conn = db_connect();


// ======================
// REPORT-1 DATA PROCESS
// ======================

$report1 = []; // [classname][section][itemcode] = total

foreach ($classList as $cls) {

    $classname = $conn->real_escape_string($cls['areaname']);
    $sectionname = $conn->real_escape_string($cls['subarea']);

    $sql1 = "
        SELECT itemcode, SUM(pr1) AS total
        FROM stfinance
        WHERE sessionyear LIKE '%$y_v2%'
          AND pr1date BETWEEN '$dtf' AND '$dtt'
          AND sccode = '$sccode'
          AND classname = '$classname'
          AND sectionname = '$sectionname'
        GROUP BY itemcode
        order by itemcode
    ";

    $q1 = $conn->query($sql1);

    while ($row = $q1->fetch_assoc()) {
        $item = $row['itemcode'];
        $report1[$classname][$sectionname][$item] = $row['total'];
    }
}



// ======================
// REPORT-2 DATA PROCESS
// ======================

$sql2 = "
    SELECT itemcode, 
           particulareng AS en, 
           particularben AS bn,
           SUM(pr1) AS total
    FROM stfinance
    WHERE sessionyear LIKE '%$y_v2%'
      AND pr1date BETWEEN '$dtf' AND '$dtt'
      AND sccode = '$sccode'
    GROUP BY itemcode
    ORDER BY itemcode
";

$q2 = $conn->query($sql2);

$report2 = [];
while ($row = $q2->fetch_assoc()) {
    $report2[] = $row;
}

?>

<!-- ===================== -->
<!--   REPORT 1 TABLE      -->
<!-- ===================== -->
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="dfrom" class="form-label">From Date</label>
                    <input type="date" id="dfrom" name="dfrom" class="form-control" value="<?= $dtf ?>">
                </div>

                <div class="col-md-3">
                    <label for="dto" class="form-label">To Date</label>
                    <input type="date" id="dto" name="dto" class="form-control" value="<?= $dtt ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary" onclick="loadDailyReport()">Load Report</button>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-dark me-2" onclick="printBlock()">Print</button>
                    <a href="daily-collection-summery-pdf.php?dfrom=<?= $dtf ?>&dto=<?= $dtt ?>"
                        class="btn btn-danger me-2" target="_blank">PDF</a>
                    <button type="button" class="btn btn-secondary" onclick="sendEmail()">Email</button>
                </div>
            </div>
        </div>
    </div>

    <div id="print-block">

        <h2>Class/Section Wise Item Collection</h2>

        <div class="table-responsive">
            <table class="table table-sm" cellspacing="0" cellpadding="5">
                <tr>
                    <th>Class</th>
                    <th>Section</th>
                    <?php
                    $cnt = count($itemList);
                    for ($i = 0; $i < $cnt; $i++) {
                        echo "<th>" . chr(65 + $i) . "</th>";
                        $var = 'item' . ($i + 1);
                        $$var = 0;
                    }
                    ?>

                    <th>Total Amount</th>
                </tr>

                <?php foreach ($classList as $cls): ?>
                    <?php
                    $classname = $cls['areaname'];
                    $sectionname = $cls['subarea'];
                    ?>
                    <tr>
                        <td><?= $classname ?></td>
                        <td><?= $sectionname ?></td>
                        <?php
                        $clsAmount = 0;
                        $x = 1;
                        foreach ($itemList as $item) {
                            $itemcode = $item['itemcode'];
                            $var = 'item' . ($x);


                            $amount = 0;
                            foreach ($dataList as $data) {
                                if (strtolower($data['itemcode']) == strtolower($itemcode) && strtolower($data['classname']) == strtolower($classname) && trim(strtolower($data['sectionname'])) == trim(strtolower($sectionname))) {
                                    $amount = $data['taka'];
                                    $$var += $amount;
                                    break;
                                }
                            }

                            $clsAmount += $amount;
                            echo "<td style='text-align:right;'>" . number_format($amount, 2) . "</td>";
                            $x++;
                        }
                        ?>
                        <td style="text-align:right;"><?= number_format($clsAmount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <th></th>
                    <td></td>
                    <?php
                    $gtotal = 0;
                    $cnt = count($itemList);
                    for ($i = 0; $i < $cnt; $i++) {
                        $var = 'item' . ($i + 1);
                        echo "<th style='text-align:right;'>" . number_format($$var, 2) . "</th>";
                        $gtotal += $$var;
                    }
                    ?>
                    <th style='text-align:right;'><?= number_format($gtotal, 2) ?></th>
                </tr>
            </table>

        </div>


        <h2>Item Wise Total Collection</h2>

        <table border="1" cellspacing="0" cellpadding="5">
            <tr>
                <th>Item Code</th>
                <th>Item Name (English)</th>
                <th>Item Name (Bangla)</th>
                <th>Total Amount</th>
            </tr>

            <?php $mot = 0;
            foreach ($report2 as $r):
                $mot += $r['total'];
                ?>
                <tr>
                    <td><?= $r['itemcode'] ?></td>
                    <td><?= $r['en'] ?></td>
                    <td><?= $r['bn'] ?></td>
                    <td style="text-align:right;"><?= number_format($r['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th>d</th>
                <th></th>
                <th></th>
                <th style='text-align:right;'> <?= number_format($mot, 2) ?></th>
            </tr>
        </table>
    </div>
</div>







<?php include_once 'footer.php'; ?>



<script>
    function loadDailyReport() {
        let dfrom = document.getElementById('dfrom').value;
        let dto = document.getElementById('dto').value;
        window.location.href = "daily-collection-summery.php?dfrom=" + dfrom + "&dto=" + dto;
    }
</script>

<script>
function printBlock() {
    var printContents = document.getElementById("print-block").innerHTML;
    var newWindow = window.open('', '', 'width=900,height=650');
    newWindow.document.write(`
        <html>
        <head>
            <title>Print</title>
            <style>
                body { font-family: SutonnyOMJ, Arial; }
                table { border-collapse: collapse; width: 100%; }
                table, th, td { border: 1px solid #333; }
                th, td { padding: 5px; }
            </style>
        </head>
        <body>${printContents}</body>
        </html>
    `);
    newWindow.document.close();
    newWindow.print();
}
</script>


</body>

</html>