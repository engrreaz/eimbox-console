<?php
// include_once("header.php");
session_start();

// define('BASEURL', 'http://localhost/bkashpgw');

echo '<pre>' . var_dump($_SESSION) . '</pre>';

$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);


if (isset($_GET['paymentID']) && isset($_GET['status'])) {
   $paymentID = $_GET['paymentID'];
   $status = $_GET['status'];



   if ($status == 'success') {

       $clientToken = $_SESSION['token'];

       $post_token = [
           'paymentID' => $paymentID,
       ];
       $url = curl_init($array['executeURL']);
       $posttoken = json_encode($post_token);

       $header = [
           'Content-Type:application/json',
           'Authorization:' . $clientToken,
           'X-APP-Key:' . $array['app_key'],
       ];

       curl_setopt($url, CURLOPT_HTTPHEADER, $header);
       curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
       curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
       curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
       curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
       $resultdata = curl_exec($url);
       curl_close($url);

       if (!empty($resultdata)) {
           $obj = json_decode($resultdata);

           echo '<pre>';
           print_r($resultdata);
           echo '</pre>';

           $statusCode = $obj->statusCode;
           $statusMessage = $obj->statusMessage;

           // if ($statusCode == '0000') {

           //     // pgw return value
           //     $paymentID             = $obj->paymentID;
           //     $trxID                 = $obj->trxID;
           //     $transactionStatus     = $obj->transactionStatus;
           //     $amount                = round($obj->amount, 2);
           //     $currency              = $obj->currency;
           //     $intent                = $obj->intent;
           //     $paymentExecuteTime    = $obj->paymentExecuteTime;
           //     $merchantInvoiceNumber = $obj->merchantInvoiceNumber;
           //     $payerType             = $obj->payerType;
           //     $payerReference        = $obj->payerReference;
           //     $customerMsisdn        = $obj->customerMsisdn;
           //     $payerAccount          = $obj->payerAccount;
           //     $maxRefundableAmount   = $obj->maxRefundableAmount;
           //     $statusCode            = $obj->statusCode;
           //     $statusMessage         = $obj->statusMessage;
           // }
       }
   }


}

// include_once("footer.php");

// ?>

<!-- // </body> -->

<!-- // </html> -->