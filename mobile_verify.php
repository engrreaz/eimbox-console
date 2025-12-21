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



// ডাটাবেজ থেকে রেজিস্ট্রেশন তথ্য রিট্রিভ

$query = mysqli_query($conn, "SELECT * FROM registrations WHERE id='$id' LIMIT 1");
$reg = mysqli_fetch_assoc($query);

if (!$reg) {
    die("<div style='color:red;text-align:center;margin-top:50px;'>Invalid registration ID.</div>");
}

$mobile = $reg['mnumber'];
$regid = $reg['reg_id'];
$pin = $reg['pin'];
echo $regid . $_POST['send_otp'];

// OTP তৈরি
if (isset($_POST['send_otp']) && empty($_SESSION['regid'])) {

    $otp = random_int(100000, 999999); // more secure
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();
    if ($sms_active == 0) {
        $alert = 'info';
        $alert_text = 'OTP is ' . $otp . ' ';
    } else {
        $message = "Your verification code for admission is: $otp";
        global_send_sms($mobile, $message, 'Admission', 'OTP');

    }
} else {
    echo 'Error';
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
        global_send_sms($mobile, $message, 'Admission', 'Form Submit');

        $_SESSION['student_reg'] = $_SESSION['regid'];

        echo "
            <script>
            alert('Verification successful! A SMS with related information has been sent.');
            if (confirm('Do you want to open Admit Card?')) {

                // Admit card new tab
                window.open('admit_card.php?id=$id', '_blank');

                
                // Dashboard same tab
                window.location.href = 'admission-dashboard.php';
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