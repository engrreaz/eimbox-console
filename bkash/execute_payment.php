<?php
require_once("../header.php");

$token = $_POST['token'];
$paymentID = $_POST['paymentID'];

$url = $bkash['base_url'] . '/execute';
$headers = [
  "Content-Type:application/json",
  "authorization: $token",
  "x-app-key: {$bkash['app_key']}"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['paymentID' => $paymentID]));
$response = curl_exec($ch);
curl_close($ch);

echo $response;
