<?php

$sms_gateway = "";

$sql = "SELECT sms_gateway FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sms_gateway = $row['sms_gateway'];
}

$sms_gateway_arr = explode(' | ', $sms_gateway);

// active check: যদি 0 index খালি না থাকে
$sms_active = !empty($sms_gateway_arr[0]) ? 1 : 0;

// array index defaults
$sms_api_key = $sms_gateway_arr[1] ?? '';
$sms_secret_key = $sms_gateway_arr[2] ?? '';
$sms_username = $sms_gateway_arr[3] ?? '';
$sms_password = $sms_gateway_arr[4] ?? '';
$sms_url = $sms_gateway_arr[5] ?? '';
$sms_provider = $sms_gateway_arr[6] ?? 'eimbox';
$sms_price = $sms_gateway_arr[7] ?? '0.50';
$usr = ($usr == '' ? 'new_student' : $usr);
$sms_hint = $sms_hint ?: [];
$sms_var = $sms_var ?: [];


// echo $sms_api_key . '/' . $sms_secret_key . '/' . $sms_username . '/' . $sms_password . '/' . $sms_url . '/' . $sms_provider . '/' . $sms_price;
// echo $sccode . '/' . $y_v2 . '/' .'/' . $usr . '/' . $cur;

// echo $sms_hint . '/' . $sms_var;