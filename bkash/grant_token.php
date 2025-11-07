<?php
require_once("../core/config.php");
require_once("../core/db.php");
require_once("../core/core-val.php");


$api = $bkash['base_url'] . '/token/grant';
$body = [
  'app_key' => $bkash['app_key'],
  'app_secret' => $bkash['app_secret'],
];

$headers = [
  "Content-Type:application/json",
  "username: {$bkash['username']}",
  "password: {$bkash['password']}",
];

$ch = curl_init($api);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
$response = curl_exec($ch);
curl_close($ch);

echo $response;