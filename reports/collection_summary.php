<h6 class="fw-bold mt-4">Collection Summary</h6>
<hr>

<?php

$t1total = 0;



$itemList = [];
$sql0x2 = "SELECT itemcode, max(particulareng), max(particularben), sum(pr1) as tk  from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by itemcode order by itemcode ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $itemList[] = $row0x2;
    }
}


$dataList = [];
$sql0x2 = "SELECT classname, sectionname, itemcode, max(particulareng), max(particularben), sum(pr1) as taka from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by classname, sectionname, itemcode ";

$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $dataList[] = $row0x2;
    }
}


// 🔹 main loop: class/section
foreach ($classList as $cls):

    $classname = $cls['classname'];
    $sectionname = $cls['sectionname'];

    $clsTotal = 0;

    // check any data exists
    $hasData = false;

    ?>

    <div class="mb-3">
        <b><?= htmlspecialchars($classname) ?> - <?= htmlspecialchars($sectionname) ?></b>

        <table class="table table-sm table-bordered mt-2">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($itemList as $item):

                    $itemcode = $item['itemcode'];

                    // sum per class+section+item
                    $amount = 0;

                    foreach ($dataList as $data) {
                        if (
                            strtolower($data['itemcode']) == strtolower($itemcode) &&
                            strtolower($data['classname']) == strtolower($classname) &&
                            strtolower($data['sectionname']) == strtolower($sectionname)
                        ) {
                            $amount = (float) $data['taka'];
                            break;
                        }
                    }

                    if ($amount > 0) {
                        $hasData = true;
                    }

                    $clsTotal += $amount;

                    ?>

                    <tr>
                        <td><?= htmlspecialchars($item['itemcode']) ?></td>
                        <td class="text-end"><?= number_format($amount, 2) ?></td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

            <tfoot>
                <tr>
                    <th class="text-end">Sub Total</th>
                    <th class="text-end"><?= number_format($clsTotal, 2) ?></th>
                </tr>
            </tfoot>

        </table>
    </div>

    <?php

    $t1total += $clsTotal;

endforeach;

?>

<!-- GRAND TOTAL -->
<div class="mt-4">
    <h5 class="text-end text-danger">
        Grand Total: <?= number_format($t1total, 2) ?>
    </h5>
</div>