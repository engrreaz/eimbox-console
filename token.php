<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

function bkash_Get_Token() {
    $config = json_decode(file_get_contents("config.json"), true);

    $post_token = [
        'app_key' => $config["app_key"],
        'app_secret' => $config["app_secret"]
    ];

    $ch = curl_init($config["tokenURL"]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type:application/json",
        "username:".$config["username"],
        "password:".$config["password"]
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_token));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true) ?? ['error'=>'invalid_json','raw'=>$res];
}

$token_data = bkash_Get_Token();
$id_token = $token_data['id_token'] ?? '';

if(!$id_token){
    echo json_encode(['error'=>'Failed to get token','response'=>$token_data]);
    exit;
}

$_SESSION['token'] = $id_token;

// Optionally save to config.json
$config = json_decode(file_get_contents("config.json"), true);
$config['token'] = $id_token;
file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo json_encode(['token'=>$id_token]);
