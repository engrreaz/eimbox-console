<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$stmt = $conn->prepare("SELECT bkash_token, bkash_refresh_token, bkash_token_expire FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
	return ["error" => "School not found"];
}

$row = $res->fetch_assoc();
$dbToken = $row['bkash_token'];
$dbRefresh = $row['bkash_refresh_token'];
$dbExpire = $row['bkash_token_expire'];

$currentTime = time(); // current timestamp


// যদি expire টাইম ফাঁকা বা পার হয়ে গেছে
if (empty($dbExpire) || $currentTime >= strtotime($dbExpire)) {
	// নতুন টোকেন জেনারেট
	$request_token = bkash_Get_Token();
	$idtoken = $request_token['id_token'];
	$refreshToken = $request_token['refresh_token'];
	if (strlen($idtoken) == 0 || strlen($refreshToken) == 0) {
		$expireTime = date("Y-m-d H:i:s", $currentTime - 5); // ধরুন ১ ঘন্টা এক্সপায়ার
	} else {
		$expireTime = date("Y-m-d H:i:s", $currentTime + 3600); // ধরুন ১ ঘন্টা এক্সপায়ার
	}



	// ডেটাবেজে আপডেট
	$update_stmt = $conn->prepare("UPDATE scinfo SET bkash_token=?, bkash_refresh_token=?, bkash_token_expire=? WHERE sccode=?");
	$update_stmt->bind_param("ssss", $idtoken, $refreshToken, $expireTime, $sccode);
	$update_stmt->execute();
	$update_stmt->close();

	$store_token = "INSERT INTO bkash_token_list(sccode, date, token, refresh_token, generate_time, expire_time) VALUES ('$sccode', '$td', '$idtoken', '$refreshToken', '$cur', '$expireTime' );";
	$conn->query($store_token);

	return [
		"id_token" => $idtoken,
		"refresh_token" => $refreshToken,
		"expire" => $expireTime
	];
} else {
	$idtoken = $dbToken;
	$refreshToken = $dbRefresh;
}


$sid = $_COOKIE[session_name()];
$_SESSION['token'] = $idtoken;
$_SESSION['refresh_token'] = $refreshToken;

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
	// curl_close($url);
	$url = null;
	$_SESSION['response_token'] = json_decode($resultdata, true);

	return json_decode($resultdata, true);
}



?>