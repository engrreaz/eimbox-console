<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean inputs
    $stid = isset($_POST['stid']) ? $_POST['stid'] : '';
    $prno = isset($_POST['prno']) ? $_POST['prno'] : '';
    $prdate = isset($_POST['prdate']) ? $_POST['prdate'] : '';
    $session = isset($_POST['session']) ? $_POST['session'] : '';

    if (!$stid || !$prno || !$prdate) {
        echo "<div class='text-danger text-center'>Invalid request!</div>";
        exit;
    }

    // Fetch receipt data using prepared statement
    $sql = "SELECT r.*, s.stnameeng, s.stnameben
            FROM stpr r
            LEFT JOIN students s ON s.stid = r.stid AND s.sccode = r.sccode
            LEFT JOIN sessioninfo si ON si.stid = r.stid AND si.sccode = r.sccode AND si.sessionyear LIKE '%$session%'
            WHERE r.stid='$stid' AND r.prno='$prno' AND r.prdate='$prdate' AND r.sessionyear LIKE '%$session%'
            order by r.id DESC LIMIT 1";
    echo $sql;
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        ?>
        <div class="p-3">
            <h5 class="text-center mb-3">Payment Receipt</h5>

            <div class="row">
                <div class="col-md-8">
                    <div class="row fs-small">Student's Info</div>
                    <div class="row fw-bold text-info"><?= htmlspecialchars($row['stnameeng']) ?></div>
                    <div class="row fw-bold text-dark"><?= htmlspecialchars($row['stnameben']) ?></div>
                    <div class="row">Class : <?= htmlspecialchars($row['classname'] . '  (' . $row['sectionname']) . ')' ?> <i
                            class="bi bi-arrow-right"></i> <?= $row['rollno'] ?></div>
                </div>

                <div class="col-md-4">
                    <div class="row fs-small">Receipt No #</div>
                    <div class="row fw-bold text-info"><?= htmlspecialchars($row['prno']) ?></div>

                    <div class="row fs-small">Receipt <uib-datepicker ng-model="myDate"
                            datepicker-options="datepickerOptions"></uib-datepicker></div>
                    <div class="row fw-bold text-info"><?= htmlspecialchars($row['prdate']) ?></div>

                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="row fs-small">Collected By</div>
                    <div class="row fw-bold text-info"><?= htmlspecialchars($row['entryby']) ?></div>
                </div>

                <div class="col-md-4">
                    <div class="row fs-small">Amount</div>
                    <div class="row fw-bold text-info"><?= htmlspecialchars($row['amount']) ?></div>
                </div>
            </div>




            <div>
                <span>Session:<br><?= htmlspecialchars($row['sessionyear']) ?></span>
            </div>




        </div>
        <?php


        $finance_sql = "SELECT particulareng, particularben, pr1
        FROM stfinance
        WHERE sessionyear LIKE '%$session%' 
        AND sccode = '$sccode' 
        AND stid = '$stid' 
        AND pr1no = '$prno' 
        AND pr1date = '$prdate'";
        // echo $finance_sql;

        $finance_res = mysqli_query($conn, $finance_sql);

        if ($finance_res && mysqli_num_rows($finance_res) > 0) {
            ?>
            <div class="p-3">
                <h6 class="mb-2">Receipt Details:</h6>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Particular</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($finance_row = mysqli_fetch_assoc($finance_res)) { ?>
                            <tr>
                                <td><?= htmlspecialchars($finance_row['particulareng']) ?></td>
                                <td><?= number_format($finance_row['pr1'], 2) ?> ৳</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
        }


    } else {
        echo "<div class='text-center text-warning p-3'>No receipt found!</div>";
    }
} else {
    echo "<div class='text-danger text-center'>Invalid request method!</div>";
}