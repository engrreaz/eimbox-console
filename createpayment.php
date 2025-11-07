<?php
require_once(dirname(__DIR__)."/header.php");

header('Content-Type: application/json');

$token = $_POST['token'] ?? '';
$amount = $_POST['amount'] ?? '';

if(!$token || !$amount){
    echo json_encode(['error'=>'Missing token or amount']);
    exit;
}

$invoice = "INV".time();

$data = [
    'mode' => '0011',
    'payerReference' => 'EIMBOX_USER',
    'callbackURL' => 'callback.php',
    'amount' => $amount,
    'currency' => 'BDT',
    'intent' => 'sale',
    'merchantInvoiceNumber' => $invoice
];

$config = json_decode(file_get_contents("config.json"), true);

$ch = curl_init($config['base_url']);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type:application/json",
    "authorization: $token",
    "x-app-key: ".$config['app_key']
]);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
