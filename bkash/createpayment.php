<?php
session_start();

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);

$token = $_SESSION['token'];

$mode = $_GET['mode'];
$payerReference = $_GET['payerReference'];
$callbackURL = $_GET['callbackURL'];
$amount = $_GET['amount'];
$currency = $_GET['currency'];
$intent = $_GET['intent'];
$invoice = $_SESSION['invoice'] ; // time(); // must be unique


if ($amount > 0 && $mode=='0011') {

$proxy = $array["proxy"];
    $createpaybody=array('mode'=>$mode, 'payerReference'=>$payerReference, 'callbackURL'=>$callbackURL, 'amount'=>$amount, 'currency'=>$currency, 'intent'=>$intent, 'merchantInvoiceNumber'=>$invoice);
    $url = curl_init($array["createURL"]);

    $createpaybodyx = json_encode($createpaybody);

    $header=array(
        'Content-Type:application/json',
        'authorization:'.$token,
        'x-app-key:'.$array["bkash_app_key"]
    );

    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url,CURLOPT_POSTFIELDS, $createpaybodyx);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    // curl_setopt($url, CURLOPT_TIMEOUT,30);
    //curl_setopt($url, CURLOPT_PROXY, $proxy);
    
    $resultdata = curl_exec($url);
    curl_close($url);
    
    echo $resultdata;
} else {
    return false;
}    

$_SESSION['response_create'] = json_decode($resultdata, true);

?>
