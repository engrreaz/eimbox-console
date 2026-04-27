<?php 
$t1total = 0;
$classList = [];
$sql0x2 = "SELECT areaname, subarea from areas where  sessionyear LIKE '%$sessionyear%' and (user='$rootuser' or sccode='$sccode') order by idno";
// echo $sql0x2 ;
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $classList[] = $row0x2;
    }
}


$codeList = [];
$sql0x2 = "SELECT itemcode, particulareng, particularben from financesetup where  sessionyear LIKE '%$sessionyear%'  and sccode='$sccode' ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $codeList[] = $row0x2;
    }
}


$itemList = [];
$sql0x2 = "SELECT itemcode, max(particulareng), max(particularben), sum(pr1) as tk  from stfinance where  sessionyear LIKE '%$sessionyear%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by itemcode order by itemcode ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $itemList[] = $row0x2;
    }
}


$dataList = [];
$sql0x2 = "SELECT classname, sectionname, itemcode, max(particulareng), max(particularben), sum(pr1) as taka from stfinance where  sessionyear LIKE '%$sessionyear%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by classname, sectionname, itemcode ";

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
        WHERE sessionyear LIKE '%$sessionyear%'
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
           max(particulareng) AS en, 
           max(particularben) AS bn,
           SUM(pr1) AS total
    FROM stfinance
    WHERE sessionyear LIKE '%$sessionyear%'
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

<style>
    table {
        width:100% !important;
        font-size:13px;
    }
</style>
<table class="table-bordered table-striped table-sm" border="1"  cellspacing="0" cellpadding="5">
    <tr>
        <td>Class</td>
        <td>Section</td>

        <?php
        $cnt = count($itemList);
        for ($i = 0; $i < $cnt; $i++) {
            echo "<td style='text-align:right;'>" . chr(65 + $i) . "</td>";
            $var = 'gitem' . ($i + 1);
            $$var = 0;
        }
        ?>
        <td style="text-align:right;">Total</td>
    </tr>

    <?php foreach ($classList as $cls): ?>
        <?php
        $classname = $cls['areaname'];
        $sectionname = $cls['subarea'];

        $clsAmount = 0;

        for ($i = 0; $i < $cnt; $i++) {
            $var = 'ritem' . ($i + 1);
            $$var = 0;
        }

        $x = 1;

        foreach ($itemList as $item) {
            $itemcode = $item['itemcode'];
            $var = 'ritem' . ($x);
            $amount = 0;

            foreach ($dataList as $data) {
                if (
                    strtolower($data['itemcode']) == strtolower($itemcode) &&
                    strtolower($data['classname']) == strtolower($classname) &&
                    trim(strtolower($data['sectionname'])) == trim(strtolower($sectionname))
                ) {
                    $amount = (float) $data['taka'];
                    break;
                }
            }

            $$var += $amount;
            $clsAmount += $amount;

            $gvar = 'gitem' . ($x);
            $$gvar += $amount;

            $x++;
        }

        if ($clsAmount == 0) continue;
        ?>

        <tr>
            <td><?= $classname ?></td>
            <td><?= $sectionname ?></td>

            <?php
            for ($i = 0; $i < $cnt; $i++) {
                $var = 'ritem' . ($i + 1);
                echo "<td align='right'>" . number_format($$var, 0) . "</td>";
            }
            ?>

            <td align="right"><?= number_format($clsAmount, 2) ?></td>
        </tr>

    <?php 
    $t1total += $clsAmount;
    endforeach; ?>

<tr>
    <td colspan="<?= $cnt+2;?>">Total : </td>
                  <td align="right"><?= number_format($t1total, 2) ?></td>
      
</tr>
</table>






<h6 class="fw-bold mt-4">Collection Summary</h6>
<hr>
<div>Total collection short report...</div>