<?php
session_start();
include('core/config.php');
include('core/db.php');
include('core/core-val.php');
include('core/functions.php');

// echo $_SESSION['otp'];

$sccode = $_COOKIE['sccode'];
include_once('core/sms-gateway-info.php');

// echo $sms_username . '/' . $sms_api_key;
if ($sccode == '') {
    header("Location: admission-login.php");
    exit;
}

if (
    !isset($_SESSION['admission']) ||
    !isset($_SESSION['step']) ||
    $_SESSION['step'] !== 'otp'
) {
    header("Location: admission-login.php");
    exit;
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
if (isset($_POST['send_otp']) && !isset($_SESSION['otp'])) {

    $otp = random_int(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();

    // প্রয়োজনীয় session
    $_SESSION['regid'] = $reg['reg_id'];
    $_SESSION['pin'] = $reg['pin'];
    $_SESSION['stname'] = $reg['name'];

    if ($sms_active == 1) {

        // SMS পাঠানো
        $message = "Your verification code for admission is: $otp";
        global_send_sms($mobile, $message, 'Admission', 'OTP');

        // SMS + alert দুইটাই
        // $alert = 'info';
        // $alert_text = "OTP has been sent to your mobile.<br><b>OTP:</b> $otp";

    } else {

        // SMS বন্ধ থাকলে শুধু alert
        $alert = 'warning';
        $alert_text = "SMS service is disabled.<br><b>OTP:</b> $otp";
    }
}


// OTP যাচাই

if (isset($_POST['verify_otp'])) {

    $entered_otp = trim($_POST['otp']);
    $stored_otp = $_SESSION['otp'] ?? '';
    $otp_time = $_SESSION['otp_time'] ?? 0;

    if (!$stored_otp) {
        $alert = 'danger';
        $alert_text = 'OTP not found. Please request again.';
    } elseif ((time() - $otp_time) > 300) {
        $alert = 'danger';
        $alert_text = 'OTP expired. Please request a new OTP.';
        unset($_SESSION['otp'], $_SESSION['otp_time']);
    } elseif ($entered_otp === (string) $stored_otp) {

        $id = (int) $id;
        mysqli_query($conn, "
            UPDATE registrations 
            SET verified=1
            WHERE id=$id
        ");

        unset($_SESSION['otp'], $_SESSION['otp_time']);

        $_SESSION['student_reg'] = $_SESSION['regid'];

        $message =
            $_SESSION['stname'] .
            " Your Regd. No: " . $_SESSION['regid'] .
            " PIN: " . $_SESSION['pin'] .
            " URL: https://console.eimbox.com/admission-login.php";

        global_send_sms($mobile, $message, 'Admission', 'Verified');

        echo "<script>
            alert('Verification successful!');
            window.location.href='admission-dashboard.php';
        </script>";
        exit;

    } else {
        $alert = 'danger';
        $alert_text = 'Incorrect OTP.';
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

                <?php
                $jt = $_SESSION['otp'] ?? 0;
                if ($jt == 0) {
                    echo '';
                } else {
                    echo 'An OTP has been send to your mobile.';
                }
                //
                ?>
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
                            <input type="number" name="otp" class="form-control text-center" maxlength="6"
                                placeholder="৬-সংখ্যার OTP লিখুন" required>
                        </div>
                        <button type="submit" name="verify_otp" class="btn btn-primary"> Verify Now </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <?php include('footer-plain.php'); ?>


 

</body>

</html>