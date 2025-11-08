<?php
session_start();

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);
$appKey = $array['app_key'];

if (isset($_GET['paymentID'])) {
    $paymentID = $_GET['paymentID'];
}

$auth = $_SESSION['token'];

$post_token = array(
    'paymentID' => $paymentID
);
$url = curl_init('https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/execute');
$posttoken = json_encode($post_token);

$header = array(
    'Content-Type:application/json',
    'Authorization:' . $auth,
    'X-APP-Key:' . $appKey
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

?>