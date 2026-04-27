<h6 class="fw-bold mt-4">Collection Summary</h6>
<hr>

<?php

$codeList = [];
$sql0x2 = "SELECT itemcode, particulareng from financesetup where  sessionyear LIKE '%$sessionyear%'  and sccode='$sccode' ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $codeList[$row0x2['itemcode']] = $row0x2['particulareng'];
    }
}

$t1total = 0;

/*
🔹 ITEM LIST (columns)
*/
$itemList = [];
$sql0x2 = "
    SELECT itemcode
    FROM stfinance
    WHERE sessionyear LIKE '%$sessionyear%'
      AND pr1date = '$date'
      AND sccode='$sccode'
    GROUP BY itemcode
    ORDER BY itemcode
";

$result0x2 = $conn->query($sql0x2);

while ($row = $result0x2->fetch_assoc()) {
    $itemList[] = $row['itemcode'];
}

/*
🔹 DATA LIST (class-section-item wise)
*/
$dataList = [];
$sql0x2 = "
    SELECT classname, sectionname, itemcode, SUM(pr1) AS taka
    FROM stfinance
    WHERE sessionyear LIKE '%$sessionyear%'
      AND pr1date = '$date'
      AND sccode='$sccode'
    GROUP BY classname, sectionname, itemcode
";

$result0x2 = $conn->query($sql0x2);

while ($row = $result0x2->fetch_assoc()) {
    $dataList[] = $row;
}

// helper function
function getAmount($dataList, $cls, $sec, $item)
{
    foreach ($dataList as $d) {
        if (
            strtolower($d['classname']) == strtolower($cls) &&
            strtolower($d['sectionname']) == strtolower($sec) &&
            strtolower($d['itemcode']) == strtolower($item)
        ) {
            return (float) $d['taka'];
        }
    }
    return 0;
}

?>

<div >
    <?php 
    $char = 65;
foreach ($itemList as $item) { 
    $eng = $codeList[$item] ?? $item;
    
    echo "<span class='badge bg-secondary me-2 mb-2'> " . chr($char) . ". $eng</span>";
    $char++;
}

?>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm">

        <!-- HEADER -->
        <thead class="table-light">
            <tr>
                <th>Class</th>
                <th>Section</th>

                <?php $char = 65; foreach ($itemList as $item): ?>
                    <th class="text-end"><?= chr($char) ?></th>
                    <?php $char++; ?>
                <?php endforeach; ?>

                <th class="text-end text-primary">Total</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($classList as $cls):

                $classname = $cls['classname'];
                $sectionname = $cls['sectionname'];

                $rowTotal = 0;

                ?>

                <tr>
                    <td><?= htmlspecialchars($classname) ?></td>
                    <td><?= htmlspecialchars($sectionname) ?></td>

                    <?php foreach ($itemList as $item):

                        $amount = getAmount($dataList, $classname, $sectionname, $item);

                        $rowTotal += $amount;

                        ?>

                        <td class="text-end">
                            <?= $amount > 0 ? number_format($amount, 2) : '' ?>
                        </td>

                    <?php endforeach; ?>

                    <td class="text-end fw-bold">
                        <?= number_format($rowTotal, 2) ?>
                    </td>
                </tr>

                <?php
                $t1total += $rowTotal;
            endforeach;
            ?>

        </tbody>

        <!-- GRAND TOTAL -->
        <tfoot class="table-dark">
            <tr>
                <th colspan="2" class="text-end">Grand Total</th>

                <?php foreach ($itemList as $item): ?>
                    <th></th>
                <?php endforeach; ?>

                <th class="text-end"><?= number_format($t1total, 2) ?></th>
            </tr>
        </tfoot>

    </table>
</div>