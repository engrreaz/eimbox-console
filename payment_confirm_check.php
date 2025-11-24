<?php
// ALTER TABLE `stpr` ADD `collection_media` VARCHAR(15) NOT NULL DEFAULT 'Cash' AFTER `cashbook`;

$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);
session_id($array['sid']);
session_start();
include_once 'core/config.php';
include_once 'core/db.php';
include_once 'core/global_values.php';
include_once 'core/functions.php';

if (isset($_COOKIE[session_name()])) {
    $sid = $_COOKIE[session_name()]; // সাধারণত PHPSESSID
    // echo "Session ID: " . $sid;
} else {
    // echo "No session cookie found!";
}
// jeql17a9hjnmkmjasmh78e7qkk -- 01770618575
define('BASEURL', 'http://localhost/eimbox-dashboard/eimbox-materio/');


$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);

?>
<style>
    .payment-wrapper {
        min-height: 80vh;
        /* পুরো স্ক্রিন */
        display: flex;
        align-items: center;
        /* উলম্বভাবে কেন্দ্র */
        justify-content: center;
        /* আড়াআড়িভাবে কেন্দ্র */
        padding: 20px;
    }

    .payment-card {
        width: 75%;
        /* বড় স্ক্রিনে 75% */
        max-width: 900px;
        /* সর্বোচ্চ আকার */
    }

    .status-img {
        width: 150px;
        height: 150px;
        border-radius: 8px;
        margin-top: 50px;
        z-index: 9999;
    }

    @media(max-width: 768px) {
        .payment-card {
            width: 100%;
            /* মোবাইলে ফুল-উইডথ */
        }
    }
</style>

<?php


if (isset($_GET['paymentID']) && isset($_GET['status'])) {
    $paymentID = $_GET['paymentID'];
    $status = $_GET['status'];

    $clientToken = $_SESSION['token'];

    $post_token = [
        'paymentID' => $paymentID,
    ];
    $url = curl_init($array['executeURL']);
    $posttoken = json_encode($post_token);

    $header = [
        'Content-Type:application/json',
        'Authorization:' . $clientToken,
        'X-APP-Key:' . $array['bkash_app_key'],
    ];

    curl_setopt($url, CURLOPT_HTTPHEADER, $header);
    curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
    curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $resultdata = curl_exec($url);
    // curl_close($url);
    $url = null;
    $obj = json_decode($resultdata, true);



    $_SESSION['response_confirm'] = $obj;

    if (!empty($resultdata)) {
        $statusCode = $obj['statusCode'];
        $statusMessage = $obj['statusMessage'];
    } else {
        $statusCode = 0;
        $statusMessage = 'Undefined Error';
    }

    // echo $statusCode . '/' . $statusMessage;

    if ($status == 'success' && $statusCode == '0000') {



        // echo '-----------------------------------------------------------------------<pre>';
        // var_dump($resultdata);
        // echo '</pre><hr><hr>';

        // echo '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<pre>';
        // var_dump($obj);
        // echo '</pre><hr><hr>';





        // echo $statusCode . ' | ' . $statusMessage;



        //     // pgw return value
        $paymentID = $obj['paymentID'];
        $trxID = $obj['trxID'];
        $transactionStatus = $obj['transactionStatus'];
        $amount = round($obj['amount'], 2);
        $currency = $obj['currency'];
        $intent = $obj['intent'];
        $paymentExecuteTime = $obj['paymentExecuteTime'];
        $merchantInvoiceNumber = $obj['merchantInvoiceNumber'];
        $payerType = $obj['payerType'];
        $payerReference = $obj['payerReference'];
        $customerMsisdn = $obj['customerMsisdn'];
        $payerAccount = $obj['payerAccount'];
        $maxRefundableAmount = $obj['maxRefundableAmount'];
        $statusCode = $obj['statusCode'];
        $statusMessage = $obj['statusMessage'];


        $stpr = substr($merchantInvoiceNumber, -8);

        $stid = $_SESSION['current_student_id'];
        $sql = "SELECT sessionyear, classname, sectionname, rollno FROM sessioninfo WHERE stid = '$stid' AND sessionyear LIKE '%$y_v2%'  order by id DESC LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $sessionyear = $row['sessionyear'];
            $classname = $row['classname'];
            $sectionname = $row['sectionname'];
            $rollno = $row['rollno'];

        } else {
            $sessionyear = date('Y');
            $classname = '';
            $sectionname = '';
            $rollno = 0;

        }


        $tkn = $_SESSION['token'];
        $stmt = $conn->prepare("SELECT id FROM bkash_token_list WHERE token=? AND sccode=? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ss", $tkn, $sccode);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $store_token_id = $row['id'];


        $trans = "INSERT INTO `payment_pgw` 
                    (sccode, sessionyear, stid, paydate, partial, paymentID, trxID, transactionStatus, amount, currency, intent, paymentExecuteTime, merchantInvoiceNumber, payerType, payerReference, customerMsisdn, payerAccount, maxRefundableAmount, statusCode, statusMessage, gateway, token_id, entrytime) 
                    VALUES
                    ('$sccode', '$sessionyear', '$stid', '$td', 'Full', '$paymentID', '$trxID', '$transactionStatus', '$amount', '$currency', '$intent', '$paymentExecuteTime', '$merchantInvoiceNumber', '$payerType', '$payerReference', '$customerMsisdn', '$payerAccount', '$maxRefundableAmount', '$statusCode', '$statusMessage', 'bkash', '$store_token_id', NOW()); ";
        // echo $trans;
        $conn->query($trans);


        $items = isset($_COOKIE['selected_items']) ? $_COOKIE['selected_items'] : 'Full';
        if ($items != 'Full') {
            $ids = explode('|', $items);
        } else {
            $ids = [];
            $cm = date('m');
            if ($cm >= 10) {
                $cm = 12;
            }
            $query = "
                    SELECT id 
                    FROM stfinance 
                    WHERE sccode='$sccode'
                        AND stid='$stid'
                        AND sessionyear='$sessionyear'
                        AND month <= '$cm'
                        AND dues > 0
                    ORDER BY month ASC
                ";

            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ids[] = $row['id'];
                }
            }

        }

        foreach ($ids as $finance_id) {
            // update stfinance
            $update_sql = "
                    UPDATE stfinance 
                    SET 
                       pr1 = dues,
                        paid = dues,
                        dues = 0,
   
                        pr1no = $stpr, pr1date = '$td'

                    WHERE id = '$finance_id' AND stid = '$stid' AND sccode='$sccode'";
            // echo $update_sql;
            $conn->query($update_sql);

        }




        $stprx = "INSERT INTO stpr (sccode, stid, sessionyear, classname, sectionname, rollno, prdate, prno, amount, entryby, entrytime, smstxt, smscnt, mobileno, smsstatus, statusvalue, collection_media) 
                            VALUES
                            ('$sccode', '$stid', '$sessionyear', '$classname', '$sectionname', '$rollno', '$td', '$stpr', '$amount',  'SELF', NOW(), '', 0, '', '', '0', 'bKash');";
        // echo $stprx;
        $conn->query($stprx);

        // INSERT Payment gatewary
        // insert stpr
        // update stfinance

        $sqlb = "SELECT stnameeng, guarmobile FROM students WHERE stid = '$stid' AND sccode = '$sccode'  order by id DESC LIMIT 1";
        // echo $sqlb;
        $result = mysqli_query($conn, $sqlb);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $guarmobile = $row['guarmobile'];
            $stnameeng = $row['stnameeng'];
        } else {
            $guarmobile = $stnameeng = '';
        }




        $msg = 'Payment of Taka ' . $amount . ' has been received from ' . $stnameeng . ' at ' . $cur;
        global_send_sms($guarmobile, $msg, 'bKash Payment', 'Payment', $stid);



        ?>

        <div class="payment-wrapper">
            <div class="card payment-card p-3">

                <div class="row">
                    <div class="col-md-3 d-flex h-100 justify-content-center align-items-center">
                        <?php

                        $image = 'success.png';
                        echo '<img class="status-img img-fluid" src="assets/images/pgw/' . $image . '" onclick="showhide();"/>';
                        ?>
                    </div>

                    <div class="col-md-9 h-100">

                        <div class="card-header pb-0 text-center text-dark fw-bold"> Transaction Details </div>
                        <hr class="pb-0 mb-0">
                        <div class="card-body">
                            <div class="alert alert-success">BDT <?= number_format($amount, 2, '.', ',') ?> has been received
                                from
                                <?= htmlspecialchars($stnameeng) ?> successfully.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col">Student's ID</div>
                                        <div class="col text-end fw-bold"><?= htmlspecialchars($stid) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col">Student's Name</div>
                                        <div class="col text-end fw-bold"><?= htmlspecialchars($stnameeng) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col">Class</div>
                                        <div class="col text-end fw-bold"><?= htmlspecialchars($classname) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col">Section</div>
                                        <div class="col text-end fw-bold"><?= htmlspecialchars($sectionname) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col">Rollno</div>
                                        <div class="col text-end fw-bold"><?= htmlspecialchars($rollno) ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col">Paid Amount</div>
                                        <div class="col text-end text-success fs-4 fw-bold">
                                            <?= number_format($amount, 2, '.', ',') ?>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                        <div class="col-12 d-flex">
                                            <button class="btn btn-sm btn-primary me-3" id="printReceiptBtn">Print
                                                Receipt</button>

                                            <!-- Download form posts to PDF generator and opens in new tab -->
                                            <form id="pdfForm" method="post" action="pdf/generate_receipt_pdf.php"
                                                target="_blank" style="display:inline;">
                                                <input type="hidden" name="stid" value="<?= htmlspecialchars($stid) ?>">
                                                <input type="hidden" name="syear" value="<?= htmlspecialchars($sessionyear) ?>">
                                                <input type="hidden" name="trx" value="<?= htmlspecialchars($trxID) ?>">
                                                <input type="hidden" name="stnameeng"
                                                    value="<?= htmlspecialchars($stnameeng) ?>">
                                                <input type="hidden" name="classname"
                                                    value="<?= htmlspecialchars($classname) ?>">
                                                <input type="hidden" name="sectionname"
                                                    value="<?= htmlspecialchars($sectionname) ?>">
                                                <input type="hidden" name="rollno" value="<?= htmlspecialchars($rollno) ?>">
                                                <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
                                                <input type="hidden" name="td"
                                                    value="<?= htmlspecialchars($td ?? date('Y-m-d')) ?>">
                                                <input type="hidden" name="prno"
                                                    value="<?= htmlspecialchars($merchantInvoiceNumber ?? '') ?>">
                                                <input type="hidden" name="verify_url"
                                                    value="<?= htmlspecialchars($verifyUrl) ?>">
                                                <button class="btn btn-sm btn-info me-3" id="downloadReceiptBtn"
                                                    type="submit">Download
                                                    Receipt</button>
                                            </form>

                                            <button class="btn btn-sm btn-dark" id="gohomeBtn">Back To Home</button>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Hidden printable receipt HTML (used for print & pdf) -->
                            <div id="receiptContent" style="display:none;">
                                <div style="width:800px; padding:24px; font-family: Arial, Helvetica, sans-serif; color:#222;">
                                    <h2 style="text-align:center; margin-bottom:8px;">Payment Receipt</h2>
                                    <p style="text-align:center; margin-top:0; margin-bottom:16px;">Receipt generated on
                                        <?= date('Y-m-d H:i:s') ?>
                                    </p>

                                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                                        <tr>
                                            <td style="padding:6px; width:50%;"><strong>Student ID:</strong>
                                                <?= htmlspecialchars($stid) ?></td>
                                            <td style="padding:6px;"><strong>Paid:</strong> BDT
                                                <?= number_format($amount, 2, '.', ',') ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px;"><strong>Name:</strong> <?= htmlspecialchars($stnameeng) ?>
                                            </td>
                                            <td style="padding:6px;"><strong>Date:</strong>
                                                <?= htmlspecialchars($td ?? date('Y-m-d')) ?></td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px;"><strong>Class/Section:</strong>
                                                <?= htmlspecialchars($classname) ?>
                                                / <?= htmlspecialchars($sectionname) ?></td>
                                            <td style="padding:6px;"><strong>Roll:</strong> <?= htmlspecialchars($rollno) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px;"><strong>PR No:</strong>
                                                <?= htmlspecialchars($merchantInvoiceNumber ?? '') ?></td>
                                            <td style="padding:6px;"><strong>Amount:</strong> BDT
                                                <?= number_format($amount, 2, '.', ',') ?>
                                            </td>
                                        </tr>
                                    </table>

                                    <hr>

                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div style="max-width:60%;">
                                            <p style="font-size:12px; margin:0;">This is a computer generated receipt. Visit the
                                                verification link or scan the QR code to verify.</p>
                                            <p style="font-size:12px; margin:2px 0 0 0; word-break:break-all;"><a
                                                    href="<?= htmlspecialchars($verifyUrl) ?>"><?= htmlspecialchars($verifyUrl) ?></a>
                                            </p>
                                        </div>

                                        <div>
                                            <!-- QR code will be embedded as base64 in the PDF page -->
                                            <img id="receiptQR" src="<?= $qrApiUrl ?>" alt="QR"
                                                style="width:140px; height:140px; border:1px solid #ddd; padding:6px; background:#fff;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /receiptContent -->

                        </div>


                    </div>
                </div>

            </div>
        </div>





        <?php





    } else {

        // ********************************************************************



        $errorMessages = [
            2001 => "Invalid App Key",
            2002 => "Invalid Payment ID",
            2003 => "Process failed",
            2004 => "Invalid firstPaymentDate",
            2005 => "Invalid frequency",
            2006 => "Invalid amount",
            2007 => "Invalid currency",
            2008 => "Invalid intent",
            2009 => "Invalid Wallet",
            2010 => "Invalid OTP",
            2011 => "Invalid PIN",
            2012 => "Invalid Receiver MSISDN",
            2013 => "Resend Limit Exceeded",
            2014 => "Wrong PIN",
            2015 => "Wrong PIN count exceeded",
            2016 => "Wrong verification code",
            2017 => "Wrong verification limit exceeded",
            2018 => "OTP verification time expired",
            2019 => "PIN verification time expired",
            2020 => "Exception Occurred",
            2021 => "Invalid Mandate ID",
            2022 => "The mandate does not exist",
            2023 => "Insufficient Balance",
            2024 => "Exception occurred",
            2025 => "Invalid request body",
            2026 => "The reversal amount cannot be greater than the original transaction amount",
            2027 => "Mandate already exists for this payer reference number",
            2028 => "Reverse failed because the transaction serial number does not exist",
            2029 => "Duplicate for all transactions",
            2030 => "Invalid mandate request type",
            2031 => "Invalid merchant invoice number",
            2032 => "Invalid transfer type",
            2033 => "Transaction not found",
            2034 => "Original transaction already reversed",
            2035 => "Initiator has no permission to reverse this transaction",
            2036 => "Mandate not in Active state",
            2037 => "Debit party account prohibits execution",
            2038 => "Debit party identity prohibits execution",
            2039 => "Credit party account prohibits execution",
            2040 => "Credit party identity prohibits execution",
            2041 => "Credit party identity does not support current service",
            2042 => "Initiator has no permission to reverse this transaction",
            2043 => "Incorrect security credential",
            2044 => "Identity not subscribed or inactive",
            2045 => "Customer MSISDN does not exist",
            2046 => "Identity not subscribed to requested service",
            2047 => "TLV Data Format Error",
            2048 => "Invalid Payer Reference",
            2049 => "Invalid Merchant Callback URL",
            2050 => "Agreement already exists",
            2051 => "Invalid Agreement ID",
            2052 => "Agreement is incomplete",
            2053 => "Agreement already cancelled",
            2054 => "Prerequisite not met for agreement execution",
            2055 => "Invalid Agreement State",
            2056 => "Invalid Payment State",
            2057 => "Not a bKash Account",
            2058 => "Not a Customer Wallet",
            2059 => "Multiple OTP requests denied",
            2060 => "Prerequisite not met for payment execution",
            2061 => "Only initiator can perform this action",
            2062 => "Payment already completed",
            2063 => "Invalid mode",
            2064 => "Product mode unavailable",
            2065 => "Mandatory field missing",
            2066 => "Agreement not shared with merchant",
            2067 => "Invalid permission",
            2068 => "Transaction already completed",
            2069 => "Transaction already cancelled",
            2116 => "Agreement execution already completed",
            2117 => "Payment execution already completed",
            2118 => "Invalid Platform value",
            2119 => "Authorized payment already processed",
            503 => "System is undergoing maintenance. Try again later"
        ];

        $code = $statusCode ?? 0; // API response status code

        if (array_key_exists($code, $errorMessages)) {
            $error = $errorMessages[$code];
        } else {
            $error = "Unknown Error (Code: $code)";
        }

        ?>


        <div class="payment-wrapper">
            <div class="card payment-card p-3">

                <div class="row">
                    <div class="col-md-4 d-flex h-100 justify-content-center align-items-center">
                        <?php
                        echo '<div id="showhide" style="display:none;">';
                        echo '<pre>';
                        print_r($obj);
                        echo '</pre>' . '</div>';

                        $image = 'error.png';
                        if ($statusCode == 2056) {
                            $image = 'cancel.png';
                        } else if ($statusCode == 2029) {
                            $image = 'duplicate.png';
                        } else if ($statusCode == 2062) {
                            $image = 'cancel.png';
                        }

                        echo '<img class="status-img img-fluid" src="assets/images/pgw/' . $image . '" onclick="showhide();"/>';
                        ?>
                    </div>

                    <div class="col-md-8 h-100">
                        <div class="card-header pb-0 text-center text-dark fw-bold">
                            Transaction Info
                        </div>
                        <hr class="pb-0 mb-0">

                        <div class="card-body">
                            <div class="alert alert-danger text-center fs-5 fw-bold"><?= $error ?></div>

                            <p class="text-center text-dark"><?= $statusMessage ?></p>

                            <div class="text-center">
                                <button class="btn btn-sm btn-dark mt-3 px-5"
                                    onclick="window.location.href='student-payable.php';">
                                    Go Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php


    }






} else {



    ?>
<div class="payment-wrapper">
            <div class="card payment-card p-3">

                <div class="row">
                    <div class="col-md-4 d-flex h-100 justify-content-center align-items-center">
                        <?php
                       

                        $image = 'error.png';
                     

                        echo '<img class="status-img img-fluid" src="assets/images/pgw/' . $image . '" onclick="showhide();"/>';
                        ?>
                    </div>

                    <div class="col-md-8 h-100">
                        <div class="card-header pb-0 text-center text-dark fw-bold">
                            Transaction Info
                        </div>
                        <hr class="pb-0 mb-0">

                        <div class="card-body">
                            <div class="alert alert-danger text-center fs-5 fw-bold">Failure Transaction</div>

                            <p class="text-center text-dark">Something went wrong. Try again.</p>

                            <div class="text-center">
                                <button class="btn btn-sm btn-dark mt-3 px-5"
                                    onclick="window.location.href='student-payable.php';">
                                    Go Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php 
}


include_once('header-plain.php');

$verifyUrl = "https://verify.eimbox.com/stpr.php?data=" . urlencode($stpr) . "&tail=" . urlencode("sfslkfslsflsflesporrfdssd");
$qrApiUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($verifyUrl);

?>


<div class="container-xxl flex-grow-1 container-p-y px-12 py-6" hidden>
    <div class="card mt-4">
        <div class="card-header pb-0 text-center text-dark fw-bold"> Payment Details </div>
        <hr class="pb-0 mb-0">
        <div class="card-body">
            <div class="alert alert-success">BDT <?= number_format($amount, 2, '.', ',') ?> has been received from
                <?= htmlspecialchars($stnameeng) ?> successfully.
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col">Student's ID</div>
                        <div class="col text-end fw-bold"><?= htmlspecialchars($stid) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Student's Name</div>
                        <div class="col text-end fw-bold"><?= htmlspecialchars($stnameeng) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Class</div>
                        <div class="col text-end fw-bold"><?= htmlspecialchars($classname) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Section</div>
                        <div class="col text-end fw-bold"><?= htmlspecialchars($sectionname) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Rollno</div>
                        <div class="col text-end fw-bold"><?= htmlspecialchars($rollno) ?></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <div class="col">Paid Amount</div>
                        <div class="col text-end text-success fs-4 fw-bold"><?= number_format($amount, 2, '.', ',') ?>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-12 d-flex">
                            <button class="btn btn-sm btn-primary me-3" id="printReceiptBtn">Print Receipt</button>

                            <!-- Download form posts to PDF generator and opens in new tab -->
                            <form id="pdfForm" method="post" action="pdf/generate_receipt_pdf.php" target="_blank"
                                style="display:inline;">
                                <input type="hidden" name="stid" value="<?= htmlspecialchars($stid) ?>">
                                <input type="hidden" name="stnameeng" value="<?= htmlspecialchars($stnameeng) ?>">
                                <input type="hidden" name="classname" value="<?= htmlspecialchars($classname) ?>">
                                <input type="hidden" name="sectionname" value="<?= htmlspecialchars($sectionname) ?>">
                                <input type="hidden" name="rollno" value="<?= htmlspecialchars($rollno) ?>">
                                <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
                                <input type="hidden" name="td" value="<?= htmlspecialchars($td ?? date('Y-m-d')) ?>">
                                <input type="hidden" name="prno"
                                    value="<?= htmlspecialchars($merchantInvoiceNumber ?? '') ?>">
                                <input type="hidden" name="verify_url" value="<?= htmlspecialchars($verifyUrl) ?>">
                                <button class="btn btn-sm btn-info me-3" id="downloadReceiptBtn" type="submit">Download
                                    Receipt</button>
                            </form>

                            <button class="btn btn-sm btn-dark" id="gohomeBtn">Back To Home</button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Hidden printable receipt HTML (used for print & pdf) -->
            <div id="receiptContent" style="display:none;">
                <div style="width:800px; padding:24px; font-family: Arial, Helvetica, sans-serif; color:#222;">
                    <h2 style="text-align:center; margin-bottom:8px;">Payment Receipt</h2>
                    <p style="text-align:center; margin-top:0; margin-bottom:16px;">Receipt generated on
                        <?= date('Y-m-d H:i:s') ?>
                    </p>

                    <table style="width:100%; border-collapse:collapse; font-size:14px;">
                        <tr>
                            <td style="padding:6px; width:50%;"><strong>Student ID:</strong>
                                <?= htmlspecialchars($stid) ?></td>
                            <td style="padding:6px;"><strong>Paid:</strong> BDT
                                <?= number_format($amount, 2, '.', ',') ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:6px;"><strong>Name:</strong> <?= htmlspecialchars($stnameeng) ?></td>
                            <td style="padding:6px;"><strong>Date:</strong>
                                <?= htmlspecialchars($td ?? date('Y-m-d')) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:6px;"><strong>Class/Section:</strong> <?= htmlspecialchars($classname) ?>
                                / <?= htmlspecialchars($sectionname) ?></td>
                            <td style="padding:6px;"><strong>Roll:</strong> <?= htmlspecialchars($rollno) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:6px;"><strong>PR No:</strong>
                                <?= htmlspecialchars($merchantInvoiceNumber ?? '') ?></td>
                            <td style="padding:6px;"><strong>Amount:</strong> BDT
                                <?= number_format($amount, 2, '.', ',') ?>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="max-width:60%;">
                            <p style="font-size:12px; margin:0;">This is a computer generated receipt. Visit the
                                verification link or scan the QR code to verify.</p>
                            <p style="font-size:12px; margin:2px 0 0 0; word-break:break-all;"><a
                                    href="<?= htmlspecialchars($verifyUrl) ?>"><?= htmlspecialchars($verifyUrl) ?></a>
                            </p>
                        </div>

                        <div>
                            <!-- QR code will be embedded as base64 in the PDF page -->
                            <img id="receiptQR" src="<?= $qrApiUrl ?>" alt="QR"
                                style="width:140px; height:140px; border:1px solid #ddd; padding:6px; background:#fff;">
                        </div>
                    </div>
                </div>
            </div>
            <!-- /receiptContent -->

        </div>
    </div>
</div>

<?php
include_once('footer-plain.php');

/*
echo '<br><br><br><b>Token : <hr></b><pre>';
print_r($_SESSION['response_token']);
echo '</pre>';
echo '<br></b><b>Create : <hr></b><pre>';
print_r($_SESSION['response_create']);
echo '</pre>';
// echo '<br><b>Execute : <hr></b><pre>';
// print_r($_SESSION['response_execute']);
// echo '</pre>';
echo '<br><b>confirm : <hr></b><pre>';
print_r($_SESSION['response_confirm']);
echo '</pre>';
// echo '<hr><b>Full : <hr></b><pre>';
// print_r($_SESSION);
// echo '</pre>';
*/

?>

<script>
    document.getElementById('gohomeBtn').addEventListener('click', function () {
        window.location.href = 'student-payable.php';
    });

    // Print functionality: open a new window with printable HTML
    document.getElementById('printReceiptBtn').addEventListener('click', function () {
        var content = document.getElementById('receiptContent').innerHTML;
        var win = window.open('', '_blank', 'width=900,height=700');
        win.document.open();
        win.document.write('<!doctype html><html><head><title>Print Receipt</title>');
        win.document.write('<meta charset="utf-8"><style>body{font-family: Arial,Helvetica,sans-serif; padding:20px;}</style>');
        win.document.write('</head><body>');
        win.document.write(content);
        win.document.write('</body></html>');
        win.document.close();
        win.focus();
        setTimeout(function () { win.print(); }, 500);
    });

    // Download button uses the form submit to generate_receipt_pdf.php (target _blank)
</script>


<script>
    function showhide() {
        var blk = document.getElementById('showhide');

        // বর্তমান display ভ্যালু নাও
        var current = window.getComputedStyle(blk).display;

        if (current === "none") {
            blk.style.display = "flex";   // তুমি flex চাও তাই flex রাখা হলো
        } else {
            blk.style.display = "none";
        }
    }

</script>



</body>

</html>