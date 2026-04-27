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

while($cls = $classQ->fetch_assoc()):

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
                    <th>Roll</th>
                    <th>Student ID</th>
                    <th>Receipt No</th>
                    <th>Collected By</th>
                    <th>Particular</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>

<?php
    // 2️⃣ প্রতিটি class/section অনুযায়ী stpr থেকে data
    $sss = "
        SELECT rollno, stid, prno, entryby 
        FROM stpr
        WHERE sccode='$sccode' 
        AND prdate='$date'
        AND classname='$classname'
        AND sectionname='$sectionname'
        ORDER BY rollno ASC
    ";
    // echo $sss;
    $stQ = $conn->query($sss);

    while($st = $stQ->fetch_assoc()):

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

        $body = ''; $tak = 0;
        while($fin = $finQ->fetch_assoc()):

            $particular = $fin['particulareng'];
            $amount = $fin['pr1'];

            $sub_total += $amount;
            $grand_total += $amount;
            $tak += $amount;


            $body .= $particular . ': ' . number_format($amount) . ", ";
            
?>
            

<?php endwhile;?>

<tr>
                    <td><?= $roll ?></td>
                    <td><?= $stid ?></td>
                    <td><?= $prno ?></td>
                    <td><?= $entryby ?></td>
                    <td class="small"><?= $body ?></td>
                    <td class="text-end"><?= number_format($tak,2) ?></td>
                </tr>

<?php  endwhile; ?>

            </tbody>

            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Sub Total</th>
                    <th class="text-end text-success">
                        <?= number_format($sub_total,2) ?>
                    </th>
                </tr>
            </tfoot>

        </table>
    </div>
</div>

<?php endwhile; ?>

<!-- GRAND TOTAL -->
<div class="mt-4">
    <h5 class="text-end">
        Grand Total: 
        <span class="text-danger"><?= number_format($grand_total,2) ?></span>
    </h5>
</div>