<?php require_once 'header.php'; ?>


<?php


$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);

$gatewaylist = [];
foreach ($admin_data['settings']['payment_gateway'] as $pg) {
    $act_pg = $pg['gateway'];
    $act_act = $pg['active'] ?? 0;
    if ($act_act == 1) {
        $gatewaylist[] = $act_pg;

        $array[$act_pg . '_app_key'] = $pg['app_key'];
        $array[$act_pg . '_app_secret'] = $pg['app_secret'];
        $array[$act_pg . '_username'] = $pg['username'];
        $array[$act_pg . '_password'] = $pg['password'];

    }
}



$newJsonString = json_encode($array);
file_put_contents('bkash/config.json', $newJsonString);
?>
<!-- <script id="myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script> -->
<script src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

<div class="container-xxl flex-grow-1 container-p-y">
    <?php



    $strJsonFileContents = file_get_contents("bkash/config.json");
    $array = json_decode($strJsonFileContents, true);

    echo '******** ' . $array['bkash_app_key'] . '/' . $array['bkash_app_secret'] . '/' . $array['bkash_username'] . '/' . $array['bkash_password'] . ' ***************';


    echo "<br><br>";
    echo '<pre>
    "bkash_app_key": "0vWQuCRGiUX7EPVjQDr0EUAYtc",
    "bkash_app_secret": "jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx",
    "bkash_username": "01770618567",
    "bkash_password": "D7DaC<*E*eG",</pre>';
    // ---------------------
// Student ID নির্ধারণ
// ---------------------
    echo '<hr>' . $_SESSION['token'] . '<hr>' . $_SESSION['refresh_token'] . '<hr>';

    echo strlen($_SESSION['token']) . '/' . strlen($_SESSION['refresh_token']);


    $sql = mysqli_query($conn, "SELECT stid FROM sessioninfo WHERE sccode = '$sccode' and sessionyear LIKE '%$y_v2%' ORDER BY RAND() LIMIT 1");
    $std = mysqli_fetch_assoc($sql);
    $stid = $std['stid'];



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
    $q3 = mysqli_query($conn, "SELECT prno 
                           FROM stpr 
                           WHERE sccode='$sccode' 
                             AND stid='$stid' 
                             AND sessionyear='$sessionyear' 
                           ORDER BY prno DESC LIMIT 1");

    $stpr = mysqli_fetch_assoc($q3);

    // ---------------------
// stfinance টেবিল
// ---------------------
    $currentMonth = date('n');
    $q4 = mysqli_query($conn, "SELECT SUM(dues) AS totaldues 
                           FROM stfinance 
                           WHERE sccode='$sccode' 
                             AND stid='$stid' 
                             AND sessionyear='$sessionyear' 
                             AND month <= $currentMonth");

    $finance = mysqli_fetch_assoc($q4);
    ?>

    <!-- ================== VIEW ================== -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bx bx-credit-card"></i> BKASH Payment Information
        </div>
        <div class="card-body">
            <?php echo $stid;
            if ($student): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Student Name (Eng):</strong> <?= htmlspecialchars($student['stnameeng']) ?><br>
                        <strong>Student Name (Ben):</strong> <?= htmlspecialchars($student['stnameben']) ?><br>
                        <strong>Roll No:</strong> <?= htmlspecialchars($session['rollno'] ?? '') ?><br>
                        <strong>Class:</strong> <?= htmlspecialchars($session['classname'] ?? '') ?><br>
                        <strong>Section:</strong> <?= htmlspecialchars($session['sectionname'] ?? '') ?><br>
                        <strong>Group:</strong> <?= htmlspecialchars($session['groupname'] ?? '') ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Guardian Mobile:</strong> <?= htmlspecialchars($student['guarmobile']) ?><br>
                        <strong>Guardian Email:</strong> <?= htmlspecialchars($student['guaremail']) ?><br>
                        <strong>Present Address:</strong><br>
                        <?= htmlspecialchars($student['previll']) ?>, <?= htmlspecialchars($student['prepo']) ?>,
                        <?= htmlspecialchars($student['preps']) ?>, <?= htmlspecialchars($student['predist']) ?>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <p><strong>Session Year:</strong> <?= htmlspecialchars($session['sessionyear'] ?? $sessionyear) ?></p>
                    <p><strong>Last Payment Reference No:</strong> <?= htmlspecialchars($stpr['prno'] ?? 'N/A') ?></p>
                    <p><strong>Total Dues (till <?= date('F') ?>):</strong>
                        <span class="text-danger fw-bold">
                            <?php $paya_2 = $finance['totaldues'] ?? 15;  $payable = number_format($paya_2, 2);
                            echo $payable; ?> ৳
                        </span>
                    </p>

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

                    <input type="text" id="payamount" value="<?php echo $payable; ?>" />
                    <input type="text" id="reference" value="<?php echo 'PAYMENT-' . $stid; ?>" />
                    <input type="text" id="prno" value="<?php echo $new_prno; ?>" />
                    <input type="text" id="invoice" value="<?php echo $invoice; ?>" disabled />


                </div>

                <div class="mt-4">
                    <div class="row">

                        <div class="col-3">
                            <button class="btn btn-bkash d-flex align-items-center px-4 py-2" id="bKash_button">
                                <img src="assets/images/bkash_payment_logo.png" alt="bKash"
                                    style="height:24px; margin-right:10px;">
                                Pay with bKash
                            </button>
                        </div>
                        <?php foreach ($gatewaylist as $gl) {
                            echo '<div class="col-3">';
                            if ($gl == 'bkash') {
                                ?>
                                <button class="btn btn-bkash d-flex align-items-center px-4 py-2" id="bKash_button2">
                                    <img src="assets/images/bkash_payment_logo.png" alt="bKash"
                                        style="height:24px; margin-right:10px;">
                                    Pay with bKash
                                </button>
                                <?php
                            } else if ($gl == 'nagad') {
                                ?>
                                    <button class="btn btn-bkash d-flex align-items-center px-4 py-2 " style="color:orangered"
                                        id="nagad_button">
                                        <img src="assets/images/nagad_payment_logo.png" alt="bKash"
                                            style="height:24px; margin-right:10px;">
                                        নগদ পেমেন্ট
                                    </button>
                                <?php
                            } else if ($gl == 'rocket') {
                                ?>
                                        <button class="btn btn-bkash d-flex align-items-center px-4 py-2" id="rocket_button">
                                            <img src="assets/images/bkash_payment_logo.png" alt="bKash"
                                                style="height:24px; margin-right:10px;">
                                            Pay with Rocket
                                        </button>
                                <?php
                            } else if ($gl == 'bank') {
                                ?>
                                            <button class="btn btn-bkash d-flex align-items-center px-4 py-2" id="bank_button">
                                                <img src="assets/images/bkash_payment_logo.png" alt="bKash"
                                                    style="height:24px; margin-right:10px;">
                                                Pay through Bank
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