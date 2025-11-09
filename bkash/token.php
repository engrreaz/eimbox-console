<?php

session_start();


$request_token = bkash_Get_Token();
$idtoken = $request_token['id_token'];
$refreshToken = $request_token['refresh_token'];

$sid = $_COOKIE[session_name()];
$_SESSION['token'] = $idtoken;

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);

$array['token'] = $idtoken;
$array['sid'] = $sid;

$newJsonString = json_encode($array);
file_put_contents('config.json', $newJsonString);


function bkash_Get_Token()
{

	$strJsonFileContents = file_get_contents("config.json");
	$array = json_decode($strJsonFileContents, true);

	$post_token = array(
		'app_key' => $array["bkash_app_key"],
		'app_secret' => $array["bkash_app_secret"]
	);

	$url = curl_init($array["tokenURL"]);
	$proxy = $array["proxy"];
	$posttoken = json_encode($post_token);
	$header = array(
		'Content-Type:application/json',
		'password:' . $array["bkash_password"],
		'username:' . $array["bkash_username"]
	);

	curl_setopt($url, CURLOPT_HTTPHEADER, $header);
	curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
	curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	//curl_setopt($url, CURLOPT_PROXY, $proxy);
	curl_setopt($url, CURLOPT_TIMEOUT, 30);
	$resultdata = curl_exec($url);
	curl_close($url);
	$_SESSION['response_token'] = json_decode($resultdata, true);

	return json_decode($resultdata, true);

}



?>