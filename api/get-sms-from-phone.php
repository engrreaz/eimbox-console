// save-token.php
<?php
require_once('../core/config.php');
require_once('../core/db.php');


$txt = $msg = $_POST['key'];
$time = $_POST['time'];

$data = [];

// Amount
preg_match('/received Tk ([0-9,\.]+)/i', $msg, $m);
$data['amount'] = $m[1] ?? '';

// Mobile (11 digit)
preg_match('/from (\d{11})/', $msg, $m);
$data['mobile'] = $m[1] ?? '';

// Reference
preg_match('/Ref ([^\.]+)/i', $msg, $m);
$data['ref'] = $m[1] ?? '';

// TrxID (10 char)
preg_match('/TrxID ([A-Z0-9]{10})/i', $msg, $m);
$data['trxid'] = $m[1] ?? '';

// Date + Time
preg_match('/at (\d{2}\/\d{2}\/\d{4}) (\d{2}:\d{2})/', $msg, $m);
$data['date'] = $m[1] ?? '';
$data['time'] = $m[2] ?? '';


$amount = $data['amount'];
$mobile = $data['mobile'];
$ref = $data['ref'];
$trxid = $data['trxid'];
$trx_date = $data['date'];
$trx_time = $data['time'];

    

$sql = "INSERT INTO forward_sms(txt, time, amount, mobile, ref, trxid, trx_date, trx_time) VALUES ('$txt', '$time', '$amount', '$mobile', '$ref', '$trxid', '$trx_date', '$trx_time');";
$conn->query($sql);
?>
