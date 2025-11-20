<?php
session_start();
// include_once 'core/config.php';
// include_once 'core/db.php';
// include_once 'core/global_values.php';
include_once 'header.php';

ob_start();
include "templete/letter-head-01.php";
$letterHead = ob_get_clean();

include "templete/letter-tail-01.php";
$letterTail = ob_get_clean();




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
                    <input type="date" id="dfrom" name="dfrom" class="form-control  form-control-sm" value="<?= $dtf ?>">
                </div>

                <div class="col-md-3">
                    <label for="dto" class="form-label">To Date</label>
                    <input type="date" id="dto" name="dto" class="form-control form-control-sm" value="<?= $dtt ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary p-2 w-100" onclick="loadDailyReport()">Load
                        Report</button>
                </div>

                <div class="col-md-3 d-flex align-items-end">

                    <div class="btn-group w-100 " role="group">

                        <button type="button" class="btn btn-dark btn-sm p-2" onclick="printBlock()">
                            <i class="bi bi-printer-fill fs-large "></i>
                        </button>

                        <a href="pdf/daily-collection-summery-pdf.php?dfrom=<?= $dtf ?>&dto=<?= $dtt ?>"
                            class="btn btn-danger btn-sm" target="_blank" disabled>
                            <i class="bi bi-file-earmark-pdf-fill  fs-large"> </i>
                        </a>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="sendEmail()" disabled>
                            <i class="bi bi-envelope-fill fs-large"> </i>
                        </button>

                    </div>

                </div>



            </div>
        </div>
    </div>

    <div class="card mt-3" id="print-block">

        <div class="text-center fs-xlarge  fw-bold mt-4">Class/Section Wise Item Collection</d>

            <div class="table-responsive">
                <table class="table table-sm" cellspacing="0" cellpadding="5">
                    <tr class="fw-bold  bg-gray">
                        <td >Class</td>
                        <td>Section</td>
                        <?php
                        $cnt = count($itemList);
                        for ($i = 0; $i < $cnt; $i++) {
                            echo "<td>" . chr(65 + $i) . "</td>";
                            $var = 'item' . ($i + 1);
                            $$var = 0;
                        }
                        ?>

                        <td class="text-end">Total Amount</td>
                    </tr>

                    <?php foreach ($classList as $cls): ?>
                        <?php
                        $classname = $cls['areaname'];
                        $sectionname = $cls['subarea'];

                        $clsAmount = 0;
                        $x = 1;

                        // প্রথমে amount হিসাব
                        foreach ($itemList as $item) {
                            $itemcode = $item['itemcode'];
                            $var = 'item' . ($x);
                            $amount = 0;

                            foreach ($dataList as $data) {
                                if (
                                    strtolower($data['itemcode']) == strtolower($itemcode) &&
                                    strtolower($data['classname']) == strtolower($classname) &&
                                    trim(strtolower($data['sectionname'])) == trim(strtolower($sectionname))
                                ) {
                                    $amount = $data['taka'];
                                    $$var += $amount;
                                    break;
                                }
                            }

                            $clsAmount += $amount;
                            $x++;
                        }

                        // যদি কোনো collection না থাকে, তাহলে skip
                        if ($clsAmount == 0) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td><?= $classname ?></td>
                            <td><?= $sectionname ?></td>
                            <?php
                            $x = 1;
                            foreach ($itemList as $item) {
                                $var = 'item' . ($x);
                                echo "<td style='text-align:right;'>" . number_format($$var, 0) . "</td>";
                                $x++;
                            }
                            ?>
                            <td style="text-align:right;"><?= number_format($clsAmount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>


                    <tr>
                        <td colspan="2" class="fw-bold text-primary">Total</td>

                        <?php
                        $gtotal = 0;
                        $cnt = count($itemList);
                        for ($i = 0; $i < $cnt; $i++) {
                            $var = 'item' . ($i + 1);
                            echo "<td class='text-primary' style='text-align:right; font-weight: bold;;'>" . number_format($$var, 0) . "</td>";
                            $gtotal += $$var;
                        }
                        ?>
                        <td class='text-primary' style='text-align:right; font-weight: bold;; '>
                            <?= number_format($gtotal, 2) ?>
                        </td>
                    </tr>
                </table>

            </div>


            <div  class="text-center fs-4  fw-bold mt-4">Item Wise Total Collection</div>

            <div class="table-responsive">
                <table class="table table-sm" cellspacing="0" cellpadding="5">
                    <tr class="fw-bold bg-gray text-white">
                        <td>Item Code</td>
                        <td>Item Name (English)</td>
                        <td>Item Name (Bangla)</td>
                        <td class="text-end">Total Amount</td>
                    </tr>

                    <?php $mot = 0;
                    foreach ($report2 as $r):
                        $mot += $r['total'];
                        $itemcode = chr(65 + array_search($r, $report2));
                        ?>
                        <tr>
                            <td><?= $itemcode; ?></td>
                            <td><?= $r['en'] ?></td>
                            <td><?= $r['bn'] ?></td>
                            <td style="text-align:right;"><?= number_format($r['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold text-primary">
                        <td colspan="3">Total</td>
                        <td style='text-align:right;'> <?= number_format($mot, 2) ?></td>
                    </tr>
                </table>
            </div>

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

            var letterHead = <?= json_encode($letterHead); ?>;
            var letterTail = <?= json_encode($letterTail); ?>;

            var printTopData = `
            <div class="text-center fs-xlarge  fw-bold mt-4">Daily Collection Report</d>
            <div>Date from ${document.getElementById('dfrom').value} to ${document.getElementById('dto').value}</div>
            `;
            var printContents = document.getElementById("print-block").innerHTML;

            var newWindow = window.open('', '', 'width=900,height=650');

            newWindow.document.write(`
        <html>
        <head>
            <title>Print</title>

            <style>
                body { 
                    font-family: SutonnyOMJ, Arial; 
                    -webkit-print-color-adjust: exact;
                    padding: 30px;
                }

                table { border-collapse: collapse; width: 100%; }
                table, th, td { border: 1px solid #333; }
                th, td { padding: 5px; }

                
                @page {
                    size: A4;
                    margin: 20mm;
                }

                /* ========= ONLY FIRST PAGE HEADER ========= */
                @media print {
                    #first-page-header {
                        display: block;
                        margin-bottom: 20px;
                    }
                }
            </style>
        </head>

        <body>

            <!-- FIRST PAGE HEADER -->
            <div id="first-page-header">
                ${letterHead}
            </div>

            <!-- REPORT TOP DATA -->
            ${printTopData}
            <!-- REPORT CONTENT -->
            ${printContents}

            <div id="first-page-footer">
                ${letterTail}
            </div>

        </body>
        </html>
    `);

            newWindow.document.close();
            newWindow.focus();
            newWindow.print();

            // Auto close even if user presses Cancel
            newWindow.onafterprint = function () {
                newWindow.close();
            };
        }
    </script>




    </body>

    </html>