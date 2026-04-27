<?php
// IMPORTANT: এখানে DB query already main file থেকে include হবে

$t1total = 0;
?>

<div class="text-center fs-xlarge fw-bold mt-4 mb-3">
    Class/Section Wise Item Collection
</div>
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


<br><br>

<div class="text-center fw-bold">Item Wise Total Collection</div>

<table  class="table-bordered table-striped table-sm"  border="1" width="100%" cellpadding="5">
    <tr>
        <td style="text-align:center;">Item</td>
        <td>Particulart (English)</td>
        <td>Particulart (Bangla)</td>
        <td style="text-align:right;">Total</td>
    </tr>

    <?php $mot = 0; $ch = 65;
    foreach ($report2 as $r):
        $mot += $r['total'];
        $a = chr($ch);
        $ch++;
    ?>
        <tr>
            <td style="text-align:center;"><?= $a ?></td>
            <td><?= $r['en'] ?></td>
            <td><?= $r['bn'] ?></td>
            <td align="right"><?= number_format($r['total'], 2) ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="3">Total</td>
        <td align="right"><?= number_format($mot, 2) ?></td>
    </tr>

</table>