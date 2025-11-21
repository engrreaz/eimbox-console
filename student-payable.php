<?php require_once 'header.php'; ?>


<?php
$sql = "SELECT * FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$res = $conn->query($sql);
$scinfo = $res->fetch_assoc();


$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);

$gatewaylist = [];
$gwList = ['bkash', 'nagad', 'rocket', 'bank'];

foreach ($gwList as $gw) {

    $act_pg = $gw;
    $gw_data = trim($scinfo[$gw] ?? "");
    $p = explode(" | ", $gw_data);
    // var_dump($p);

    $act_act = $p[1] ?? 0;
    if ($act_act == 1) {
        $gatewaylist[] = $act_pg;

        $array[$act_pg . '_app_key'] = $p[3];
        $array[$act_pg . '_app_secret'] = $p[4];
        $array[$act_pg . '_username'] = $p[5];
        $array[$act_pg . '_password'] = $p[6];

    }
}


// echo '<hr><b>GET DATA</b><hr>';
// echo '<pre>';
// print_r($array);
// echo '</pre>';
// echo '<hr><br>';

$newJsonString = json_encode($array);
file_put_contents('bkash/config.json', $newJsonString);
?>
<!-- <script id="myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script> -->
<script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

<div class="container-xxl flex-grow-1 container-p-y">
    <?php



    $strJsonFileContents = file_get_contents("bkash/config.json");
    $array = json_decode($strJsonFileContents, true);

    // echo '******** ' . $array['bkash_app_key'] . '/' . $array['bkash_app_secret'] . '/' . $array['bkash_username'] . '/' . $array['bkash_password'] . ' ***************';
    

    // echo "<br><br>";
    // echo '<pre>
    // "bkash_app_key": "0vWQuCRGiUX7EPVjQDr0EUAYtc",
    // "bkash_app_secret": "jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx",
    // "bkash_username": "01770618567",
    // "bkash_password": "D7DaC<*E*eG",</pre>';
    // ---------------------
// Student ID নির্ধারণ
// ---------------------
    // echo '<hr>' . $_SESSION['token'] . '<hr>' . $_SESSION['refresh_token'] . '<hr>';
    
    // echo strlen($_SESSION['token']) . '/' . strlen($_SESSION['refresh_token']);
    
    if (isset($_SESSION['current_student_id']) && !empty($_SESSION['current_student_id'])) {
        $stid = $_SESSION['current_student_id'];
    } else {

        $sql = mysqli_query($conn, "SELECT stid FROM sessioninfo WHERE sccode = '$sccode' and sessionyear LIKE '%$y_v2%' ORDER BY RAND() LIMIT 1");
        $std = mysqli_fetch_assoc($sql);
        $stid = $std['stid'];
        $_SESSION['current_student_id'] = $stid;
    }

    // ---------------------
// sessioninfo টেবিল
// ---------------------
    $sessionyear = date('y');
    $q1 = mysqli_query($conn, "SELECT sessionyear, classname, sectionname, groupname, rollno 
                           FROM sessioninfo 
                           WHERE sccode='$sccode' 
                             AND sessionyear LIKE '%$sessionyear%' 
                             AND stid='$stid'");

    $session = mysqli_fetch_assoc($q1);
    $sessionyear = $session['sessionyear'];
    // ---------------------
// student টেবিল
// ---------------------
    $q2 = mysqli_query($conn, "SELECT stnameeng, stnameben, previll, prepo, preps, predist, guarmobile, guaremail 
                           FROM students
                           WHERE sccode='$sccode' AND stid='$stid'");

    $student = mysqli_fetch_assoc($q2);

    // ---------------------
// stpr টেবিল
// ---------------------
    $q3 = mysqli_query($conn, "SELECT prno, amount, prdate 
                           FROM stpr 
                           WHERE sccode='$sccode' 
                             AND stid='$stid' 
                             AND sessionyear='$sessionyear' 
                           ORDER BY prno DESC LIMIT 1");

    $stpr = mysqli_fetch_assoc($q3);

    // ---------------------
// stfinance টেবিল
// ---------------------++
$syear = htmlspecialchars($session['sessionyear'] ?? $sessionyear);

    $currentMonth = date('n');
    $q4 = mysqli_query($conn, "SELECT SUM(dues) AS totaldues 
                           FROM stfinance 
                           WHERE sccode='$sccode' 
                             AND stid='$stid' 
                             AND sessionyear='$syear' 
                             AND month <= $currentMonth");

    $finance = mysqli_fetch_assoc($q4);
    ?>

    <style>
        .info-table td {
            padding: 6px;
        }

        .modal-body {
    max-height: 65vh;
    overflow-y: auto;
    padding-right: 10px;
}

        .bkash-btn {
            background: linear-gradient(135deg, #E3106E, #FF4EA0);
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 400;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(227, 16, 110, 0.4);
        }

        .bkash-btn img {
            height: 28px;
            transition: 0.25s;
        }

        .bkash-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(227, 16, 110, 0.6);
        }

        .bkash-btn:hover img {
            transform: scale(1.15) rotate(-3deg);
        }

        .bkash-btn:active {
            transform: scale(0.96);
        }

        /* Shared Button Style (Base) */
        .pay-btn {
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 400;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.35s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.20);
        }

        .pay-btn img {
            height: 26px;
            transition: 0.25s;
        }

        .pay-btn:hover {
            transform: translateY(-3px);
        }

        .pay-btn:hover img {
            transform: scale(1.15) rotate(-4deg);
        }

        .pay-btn:active {
            transform: scale(0.96);
        }

        /* bKash Style */
        .btn-bkash {
            background: linear-gradient(135deg, #E3106E, #FF4EA0);
            box-shadow: 0 4px 10px rgba(227, 16, 110, 0.4);
        }

        /* Nagad Style */
        .btn-nagad {
            background: linear-gradient(135deg, #FF8C00, #FF4500);
            box-shadow: 0 4px 10px rgba(255, 69, 0, 0.45);
        }

        /* Rocket Style */
        .btn-rocket {
            background: linear-gradient(135deg, #7A1FA2, #BA68C8);
            box-shadow: 0 4px 10px rgba(122, 31, 162, 0.45);
        }

        /* Bank Style */
        .btn-bank {
            background: linear-gradient(135deg, #0052D4, #4364F7, #6FB1FC);
            box-shadow: 0 4px 10px rgba(0, 82, 212, 0.40);
        }
    </style>

    <!-- ================== VIEW ================== -->
    <div class="card mb-4">
        <div class="card-header bg-gray text-primary fw-bold">
            <i class="bx bx-credit-card"></i> Payment Information
        </div>
        <div class="card-body pt-4">
            <?php
            if ($student): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm fs-6 info-table">

                            <tr>
                                <td rowspan="2">Name :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($student['stnameeng']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold"><?= htmlspecialchars($student['stnameben']) ?></td>
                            </tr>
                            <tr>
                                <td>Mobile :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($student['guarmobile']) ?></td>
                            </tr>
                            <tr>
                                <td>E-mail :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($student['guaremail']) ?></td>
                            </tr>
                            <tr>
                                <td>Address :</td>
                                <td class="text-end fw-bold">
                                    <?= htmlspecialchars($student['previll']) ?>,
                                    <?= htmlspecialchars($student['prepo']) ?>,
                                    <?= htmlspecialchars($student['preps']) ?>, <?= htmlspecialchars($student['predist']) ?>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="col-md-4 table-responsive ">
                        <table class="table table-sm fs-6 info-table">

                            <tr>
                                <td>Session :</td>
                                <td class="text-end fw-bold">
                                    <?= htmlspecialchars($session['sessionyear'] ?? $sessionyear) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>ID :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($stid ?? '') ?></td>
                            </tr>
                            <tr>
                                <td>Class :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($session['classname'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td>Section :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($session['sectionname'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td>Roll No :</td>
                                <td class="text-end fw-bold"><?= htmlspecialchars($session['rollno'] ?? '') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-2 text-center h-100 ">
                        <img src="<?= BASE_PATH . 'students/' . $stid . '.jpg'; ?>" alt="Student Avatar"
                            style="height:120px; border-radius:5px; object-fit:cover;">
                    </div>
                </div>


                <div class="modal fade" id="financeModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">Pending Dues</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <?php
                                // current month logic
                                $cm = date('n');
                                if ($cm >= 10) {
                                    $cm = 12;
                                }

                                $query = "
                    SELECT particulareng, dues 
                    FROM stfinance 
                    WHERE sccode='$sccode'
                        AND stid='$stid'
                        AND sessionyear='$syear'
                        AND month <= '$cm'
                        AND dues > 0
                    ORDER BY month ASC
                ";

                                $result = $conn->query($query);
                                ?>

                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width:70%">Particular</th>
                                            <th style="width:30%; text-align:right;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalDues = 0;

                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {

                                                $totalDues += $row['dues'];
                                                ?>
                                                <tr>
                                                    <td><?= $row['particulareng'] ?></td>
                                                    <td style="text-align:right;"><?= number_format($row['dues'], 2) ?></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='2' class='text-center text-danger'>No dues found</td></tr>";
                                        }
                                        ?>
                                    </tbody>

                                    <?php if ($totalDues > 0): ?>
                                        <tfoot>
                                            <tr>
                                                <th>Total</th>
                                                <th style="text-align:right;"><?= number_format($totalDues, 2) ?></th>
                                            </tr>
                                        </tfoot>
                                    <?php endif; ?>
                                </table>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>
                </div>




                <div class=" pt-3">
                    <div class="row">

                        <div class="col-md-6 table-responsive h-100">
                            <table class="table table-sm info-table">


                                <tr>
                                    <td class="fs-6">Total Dues :</td>
                                    <td class="fs-4 text-danger fw-bold">
                                        <?php $paya_2 = $total ?? 15;
                                        $payable = number_format($paya_2, 2);
                                        echo $payable; ?> ৳
                                    </td>
                                    <td>
                                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#financeModal">
                                            View Dues
                                        </button>
                                    </td>

                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6 table-responsive">
                            <table class="table table-sm info-table">

                                <tr>
                                    <td class="fs-4"><?= htmlspecialchars($stpr['prno'] ?? 'N/A') ?></td>
                                    <td class="fs-4"><?= htmlspecialchars($stpr['prdate'] ?? 'N/A') ?></td>
                                    <td class="fs-4"><?= htmlspecialchars(number_format($stpr['amount'] ?? 0, 2)) ?> ৳</td>
                                </tr>
                                <tr>
                                    <td class="fs-6">Last Payment No</td>
                                    <td class="fs-6">Date</td>
                                    <td class="fs-6">Amount</td>
                                </tr>
                            </table>
                        </div>

                    </div>


                    <?php

                    if (htmlspecialchars($stpr['prno'])) {
                        $found_last_pr = htmlspecialchars($stpr['prno']);
                    } else {
                        $found_last_pr = $y_v2 . sprintf("%04d", $stid % 10000);
                        $found_last_pr *= 100;
                    }
                    $new_prno = $found_last_pr + 1;
                    $invoice = $sccode . '-' . $new_prno;
                    $_SESSION['invoice'] = $invoice;


                    ?>
                    <div hidden>
                        <input type="text" id="payamount" value="<?php echo $payable; ?>" />
                        <input type="text" id="reference" value="<?php echo 'PAYMENT-' . $stid; ?>" />
                        <input type="text" id="prno" value="<?php echo $new_prno; ?>" />
                        <input type="text" id="invoice" value="<?php echo $invoice; ?>" disabled />

                    </div>
                </div>

                <div class="mt-4">
                    <div class="row d-flex justify-content-start align-items-center">

                        <div class="col-3" hidden>
                            <button class="btn btn-bkash d-flex align-items-center px-4 py-2" id="bKash_button2">
                                <img src="assets/images/bkash_payment_logo.png" alt="bKash"
                                    style="height:24px; margin-right:10px;">
                                Pay with bKash
                            </button>
                        </div>
                        <?php foreach ($gatewaylist as $gl) {
                            echo '<div class="col">';
                            if ($gl == 'bkash') {
                                ?>
                                <button class="bkash-btn" id="bKash_button">
                                    <img src="assets/images/bkash_payment_logo.png" alt="bKash">
                                    <span>Pay with bKash</span>
                                </button>
                                <?php
                            } else if ($gl == 'nagad') {
                                ?>
                                    <button class="pay-btn btn-nagad" id="nagad_button">
                                        <img src="assets/images/nagad_payment_logo.png" alt="Nagad">
                                        <span>নগদ পেমেন্ট</span>
                                    </button>
                                <?php
                            } else if ($gl == 'rocket') {
                                ?>
                                        <button class="pay-btn btn-rocket" id="rocket_button">
                                            <img src="assets/images/rocket_payment_logo.png" alt="Rocket">
                                            <span>Pay with Rocket</span>
                                        </button>
                                <?php
                            } else if ($gl == 'bank') {
                                ?>
                                            <button class="pay-btn btn-bank" id="bank_button">
                                                <img src="assets/images/bank_payment_logo.png" alt="Bank">
                                                <span>Pay through Bank</span>
                                            </button>
                                <?php
                            }
                            ?>


                            <?php echo '</div>';
                        } ?>
                    </div>
                </div>

                <style>
                    .btn-bkash {
                        /* bKash রেড কালার */
                        color: deeppink;
                        font-weight: 600;
                        border-radius: 8px;
                        border: none;
                        box-shadow: 0 4px 12px rgba(212, 53, 93, 0.2);
                        transition: all 0.2s ease-in-out;
                    }

                    .btn-bkash:hover {
                        transform: scale(1.05);
                        box-shadow: 0 4px 12px rgba(199, 60, 141, 0.2);
                    }
                </style>


            <?php else: ?>
                <div class="alert alert-warning">Student data not found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script type="text/javascript">

    var accessToken = '';
    $(document).ready(function () {
        $.ajax({
            url: "bkash/token.php",
            type: 'POST',
            contentType: 'application/json',
            success: function (data) {

                console.log(data);
                accessToken = JSON.stringify(data);
                console.log(accessToken);

            },
            error: function () {
                console.log('error');

            }
        });

        var paymentConfig = {
            createCheckoutURL: "bkash/createpayment.php",
        };

        var amount = $('#payamount').val();
        var payerReference = $('#reference').val();



        if (!amount || !payerReference) {
            alert('Amount বা Reference খালি!');
            return;
        }

        // alert(payerReference);

        var paymentRequest;
        var cBURL = '<?= APP_PATH; ?>payment_confirm_check.php';
        // var cBURL = 'http://localhost/eimbox-dashboard/eimbox-materio/payment_confirm_check.php';
        paymentRequest = { mode: '0011', payerReference: payerReference, callbackURL: cBURL, amount: amount, currency: 'BDT', intent: 'sale' };

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: paymentRequest,
            createRequest: function (request) {

                $.ajax({
                    url: paymentConfig.createCheckoutURL + "?amount=" + paymentRequest.amount + "&currency=" + paymentRequest.currency + "&intent=" + paymentRequest.intent + "&mode=" + paymentRequest.mode + "&payerReference=" + paymentRequest.payerReference + "&callbackURL=" + paymentRequest.callbackURL,
                    type: 'GET',
                    contentType: 'application/json',
                    success: function (data) {

                        var obj = JSON.parse(data);

                        console.log('cox' + obj);

                        if (data && obj.paymentID != null) {
                            paymentID = obj.paymentID;
                            bkashURL = obj.bkashURL;
                            window.location.href = bkashURL;

                        }
                        else {
                            console.log('error');
                            bKash.create().onError();
                        }
                    },
                    error: function () {
                        console.log('error');
                        bKash.create().onError();
                    }
                });
            }

        });
    });

    function callReconfigure(val) {
        bKash.reconfigure(val);
    }

    function clickPayButton() {
        $("#bKash_button").trigger('click');
    }

</script>

<!-- ----------------------------------- -->
</body>

</html>