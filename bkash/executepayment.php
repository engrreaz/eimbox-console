<?php

$strJsonFileContents = file_get_contents("bkash/config.json");
$array = json_decode($strJsonFileContents, true);
session_id($array['sid']);
session_start();


if (isset($_COOKIE[session_name()])) {
    $sid = $_COOKIE[session_name()]; // সাধারণত PHPSESSID
    echo "Session ID: " . $sid;
} else {
    echo "No session cookie found!";
}
$_SESSION['response_execute'] = 'start';
$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);
$appKey = $array['bkash_app_key'];

if (isset($_GET['paymentID'])) {
    $paymentID = $_GET['paymentID'];
}

$auth = $_SESSION['token'];

$post_token = array(
    'paymentID' => $paymentID
);
$url = curl_init('https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute');
$posttoken = json_encode($post_token);

$header = array(
    'Content-Type:application/json',
    'Authorization:' . $auth,
    'X-APP-Key:'.$appKey
);
curl_setopt($url, CURLOPT_HTTPHEADER, $header);
curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$resultdata = curl_exec($url);

curl_close($url);

$obj = json_decode($resultdata);

$_SESSION['response_execute'] = $obj;

?>
