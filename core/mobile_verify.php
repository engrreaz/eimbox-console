<?php
session_start();
include('config.php');
include('db.php');
include('header-plain.php');

$alert = '';
$alert_text = '';
// SMS Gateway Configuration (example)
$sms_api_url = "http://bulksmsbd.net/api/smsapi?api_key=tNrdSSziORSgTc85sDxJ&type=text&number=Receiver&senderid=8809617618425&message=TestSMS";
$sms_api_key = "tNrdSSziORSgTc85sDxJ"; // <-- এখানে তোমার gateway key দাও



function sms_send($number, $message)
{
    $url = "http://bulksmsbd.net/api/smsapi";
    $api_key = "tNrdSSziORSgTc85sDxJ";
    $senderid = "8809617618425";


    $data = [
        "api_key" => $api_key,
        "senderid" => $senderid,
        "number" => $number,
        "message" => $message
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
// ডাটাবেজ থেকে রেজিস্ট্রেশন তথ্য রিট্রিভ
$id = $_GET['id'] ?? 0;
$query = mysqli_query($conn, "SELECT * FROM registrations WHERE id='$id' LIMIT 1");
$reg = mysqli_fetch_assoc($query);

if (!$reg) {
    die("<div style='color:red;text-align:center;margin-top:50px;'>Invalid registration ID.</div>");
}

$mobile = $reg['mnumber'];
$regid = $reg['reg_id'];
$pin = $reg['pin'];

// OTP তৈরি
if (isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();

    $message = "Your verification code is: $otp";


    // sms_send($mobile, $message);



    echo "<script>alert('OTP sent to $mobile / $otp / ');</script>";
}

// OTP যাচাই
if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    $stored_otp = $_SESSION['otp'] ?? 0;
    $otp_time = $_SESSION['otp_time'] ?? 0;

    if ((time() - $otp_time) > 300) { // ৫ মিনিট পর মেয়াদ শেষ
        $alert = 'danger';
        $alert_text = "'OTP expired. Please request a new one.";
        unset($_SESSION['otp']);
    } elseif ($entered_otp == $stored_otp) {
        mysqli_query($conn, "UPDATE registrations SET verified=1, verifytime=NOW() WHERE id='$id'");

        echo "<script>
            alert('Verification successful!');
            window.location='admit_card.php?id=$id';
        </script>";
        exit;
    } else {
        echo "<script>alert('Incorrect OTP');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <title>Mobile Verification</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container" style="max-width:500px;margin-top:70px;">
        <div class="card shadow">
            <?php if ($alert_text != '') {
                ?>
                <div class="alert alert-<?= $alert; ?>"><?= $alert_text; ?> </div>
                <?php
            }
            ?>


            <div class="card-header text-white text-center">
                <h5 class="text-info fw-bold">Mobile Number Verification<br>মোবাইল নাম্বার যাচাই</h5>
            </div>
            <div class="card-body text-center">
                <p>Mobile Number / মোবাইল নাম্বার : <strong><?php echo htmlspecialchars($mobile); ?></strong></p>
                <form method="post">
                    <?php if (!isset($_SESSION['otp'])): ?>
                        <button type="submit" name="send_otp" class="btn btn-success">Send OTP</button>
                    <?php else: ?>
                        <div class="mb-3">
                            <input type="text" name="otp" class="form-control text-center" maxlength="6"
                                placeholder="৬-সংখ্যার OTP লিখুন" required>
                        </div>
                        <button type="submit" name="verify_otp" class="btn btn-primary"> Verify Now </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>