<h6 class="fw-bold mt-4">Student Collection</h6>
<hr>

<?php

// নিরাপদ ইনপুট
$date = mysqli_real_escape_string($conn, $date);

// 1️⃣ প্রথমে class + section তালিকা
$sql = "
    SELECT DISTINCT classname, sectionname 
    FROM stpr 
    WHERE sccode='$sccode' AND prdate='$date' 
    ORDER BY classname, sectionname
";
// echo $sql;
$classQ = $conn->query($sql);

$grand_total = 0;

foreach ($classList as $cls) {

    $classname = $cls['classname'];
    $sectionname = $cls['sectionname'];

    $sub_total = 0;
    ?>

    <div class="mt-3">
        <h6 class="text-primary">
            Class: <?= $classname ?> | Section: <?= $sectionname ?>
        </h6>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th class="small py-1">Roll</th>
                        <th class="small py-1">ID</th>
                        <th class="small py-1">PR No</th>
                        <th class="small py-1">Particular</th>
                        <th class="small py-1">Amount</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    // 2️⃣ প্রতিটি class/section অনুযায়ী stpr থেকে data
                    $entrybyPre = '';
                    $sss = "
        SELECT rollno, stid, prno, entryby 
        FROM stpr
        WHERE sccode='$sccode' 
        AND prdate='$date'
        AND classname='$classname'
        AND sectionname='$sectionname'
        ORDER BY entryby, rollno ASC
    ";
                    // echo $sss;
                    $stQ = $conn->query($sss);

                    while ($st = $stQ->fetch_assoc()):

                        $roll = $st['rollno'];
                        $stid = $st['stid'];
                        $prno = $st['prno'];
                        $entryby = $st['entryby'];

                        // 3️⃣ stfinance থেকে details
                        $qqq = "
            SELECT particulareng, pr1 
            FROM stfinance
            WHERE stid='$stid'
            AND pr1no='$prno'
            AND sccode='$sccode'
            AND pr1date='$date'
        ";
                        // echo $qqq;
                        $finQ = $conn->query($qqq);

                        $body = '';
                        $tak = 0;
                        while ($fin = $finQ->fetch_assoc()):

                            $particular = $fin['particulareng'];
                            $amount = $fin['pr1'];

                            $sub_total += $amount;
                            $grand_total += $amount;
                            $tak += $amount;


                            $body .= $particular . ': ' . number_format($amount) . ", ";

                            ?>


                        <?php endwhile; ?>
                        <?php
                        if ($entrybyPre != $entryby) {
                            $entrybyPre = $entryby;
                            $collector = "Collected by: " . $entryby;

                            ?>
                            <tr>
                                <td colspan="5"><?= $collector ?></td>
                            </tr>
                            <?php
                        }

                        ?>

                        <tr>
                            <td class="small py-1"><?= $roll ?></td>
                            <td class="small py-1"><?= $stid ?></td>
                            <td class="small py-1"><?= $prno ?></td>
                            <td class="small py-0" class="small"><?= $body ?></td>
                            <td class="text-end small py-1"><?= number_format($tak, 2) ?></td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Sub Total</th>
                        <th class="text-end text-success">
                            <?= number_format($sub_total, 2) ?>
                        </th>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>

<?php } ?>

<!-- GRAND TOTAL -->
<div class="mt-4">
    <h5 class="text-end">
        Grand Total:
        <span class="text-danger"><?= number_format($grand_total, 2) ?></span>
    </h5>
</div>