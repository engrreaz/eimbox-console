<?php
session_start();
include('core/config.php');
include('core/db.php');
include('core/core-val.php');

// echo $_SESSION['otp'];

$sccode = $_COOKIE['sccode'];
if ($sccode == '') {
    header("Location: admission-login.php");
    exit;
}

if (isset($_SESSION['admission']) !== true && isset($_SESSION['step']) !== 'otp') {
    header("Location: admission-login.php");
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// session id as integer (if set)
$sess_id = isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0;

// validate: যদি সেশন id নির্ধারিত না থাকে বা GET id এর সঙ্গে মিল না করে
if ($sess_id === 0 || $id === 0 || $sess_id !== $id) {
    $alert = 'danger';
    $alert_text = 'Invalid ID';
} else {
    // valid: clear alerts (optional)
    $alert = '';
    $alert_text = '';
}


include_once('header-plain.php');
include_once('actions/get-sc-data.php');


?>

<div>
    <table class="mt-4 mb-6 " style="margin:auto;">
        <tr>
            <td><img src="<?php echo BASE_PATH . 'logo/' . $sccode . '.png'; ?> "
                    style="max-width:50px; max-height:50px;" />
            </td>
            <td style="width:15px; border-right:5px solid gray;"></td>
            <td style="width:15px;"></td>
            <td>
                <h3 class="m-0 p-0 fw-bold"><?= $scname; ?></h3>
                <h6 class="m-0 p-0"><?= $address; ?></h6>

            </td>
        </tr>
    </table>
</div>
<?php


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
        $alert_text = "'OTP expired. Please Register Again.";
        unset($_SESSION['otp']);
    } elseif ($entered_otp == $stored_otp) {
        mysqli_query($conn, "UPDATE registrations SET verified=1, verifytime=NOW() WHERE id='$id'");

        $message = $_SESSION['stname'] . ',\n Your Regd. No. is ' . $_SESSION['regid'] . ' and login PIN is ' . $_SESSION['pin'] . '\nURL is https://console.eimbox.com/admisssion.login.php';
        // sms_send($mobile, $message);

        echo "
            <script>
            alert('Verification successful!');
            if (confirm('Do you want to open Admit Card?')) {
                window.location.href = 'admit_card.php?id=$id';
            }
            </script>
            ";
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

        <div class="alert alert-info">
            OTP is <?= $_SESSION['otp'] ?? 0 ?>
        </div>



            <div class="card-header text-white text-center">
                <h5 class="text-info fw-bold">Mobile Number Verification<br>মোবাইল নাম্বার যাচাই</h5>
            </div>
            <?php if ($alert_text != '') {
                ?>
                <div class="alert alert-<?= $alert; ?>"><?= $alert_text; ?> </div>
                <?php
            }
            ?>
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

    <?php include('footer-plain.php'); ?>


    <?php if ($alert_text != ''): ?>
        <script>
            $(document).ready(function () {
                // alert_text আছে মানে invalid অবস্থা
                $('form').on('submit', function (e) {
                    e.preventDefault(); // ফরম সাবমিট বন্ধ
                    alert('⚠️ Invalid form. Please check and reload the page.');
                });

                // চাইলে বোতামও disable করতে পারো
                $('form button[type="submit"]').prop('disabled', true);
            });
        </script>
    <?php endif; ?>


</body>

</html>