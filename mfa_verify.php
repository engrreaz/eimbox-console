<?php
require_once 'core/init.php';
$conn = db_connect();
$errors = [];

// Ensure user is partially authenticated
if (!isset($_SESSION['partial_auth'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['partial_auth'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM usersapp WHERE id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    $errors[] = "User not found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $code = trim($_POST['mfa_code'] ?? '');

        if (!preg_match('/^\d{6}$/', $code)) {
            $errors[] = "Invalid code format.";
        } elseif (!isset($user['mfa_temp_token']) || !isset($user['mfa_temp_expires'])) {
            $errors[] = "No MFA token found. Please login again.";
        } elseif (strtotime($user['mfa_temp_expires']) < time()) {
            $errors[] = "MFA code expired. Please login again.";
        } elseif (!password_verify($code, $user['mfa_temp_token'])) {
            $errors[] = "Incorrect MFA code.";
        } else {
            // MFA successful → full login
            store_user_session($user);

            // Clear temporary MFA token
            $stmt = $conn->prepare("UPDATE usersapp SET mfa_temp_token=NULL, mfa_temp_expires=NULL WHERE id=?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();

            // Optional: Remember-me
            if (isset($_SESSION['remember_me_flag']) && $_SESSION['remember_me_flag']) {
                create_remember_token($conn, $userId);
            }

            // Remove partial auth
            unset($_SESSION['partial_auth']);
            unset($_SESSION['remember_me_flag']);

            // Redirect to dashboard
            header('Location: core/suspicious-activity.php');
            exit;
        }
    }
}

// CSRF token for form
$csrf = csrf_token();


include_once('header-plain.php');
?>

<div class="positive-relative">
    <div class="authentication-wrapper authentication-basic">
        <div class="authentication-inner py-6 mx-4">
            <!--  Two Steps Verification -->
            <div class="card p-sm-7 p-2">
                <!-- Logo -->
                <div class="app-brand justify-content-center mt-5">
                    <a href="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/index.html"
                        class="app-brand-link gap-3">
                        <span class="app-brand-logo demo">
                            <span class="text-primary">
                                LOgo
                            </span>
                        </span>
                        <span class="app-brand-text demo text-heading fw-semibold">Materio</span>
                    </a>
                </div>
                <!-- /Logo -->
                <div class="card-body mt-1">
                    <h4 class="mb-1">Two Step Verification 💬</h4>
                    <p class="text-start mb-5">
                        We sent a verification code to your mobile. Enter the code from the mobile in the field below.
                        <span class="d-block mt-1 h6">******1234</span>
                    </p>
                    <p class="mb-0">Type your 6 digit security code</p>
                    <form id="twoStepsForm" method="POST" action="mfa_verify.php">
                        <div class="mb-5 form-control-validation">


                            <?php if (!empty($errors)): ?>
                                <div class="error bg-danger text-white rounded p-3 mb-3">
                                    <?php foreach ($errors as $e) {
                                        echo h($e) . "<br>";
                                    } ?>
                                </div>
                            <?php endif; ?>

                            <div
                                class="auth-input-wrapper d-flex align-items-center justify-content-between numeral-mask-wrapper">
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" autofocus />
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" />
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" />
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" />
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" />
                                <input type="tel"
                                    class="form-control auth-input text-center numeral-mask h-px-50 mx-sm-1 my-2"
                                    maxlength="1" />
                            </div>
                            <!-- Create a hidden field which is combined by 3 fields above -->
                            <input type="hidden" name="otp" />

                            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="mfa_code" maxlength="6" pattern="\d{6}" placeholder="123456"
                                required>



                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-5">Verify my account</button>
                        <div class="text-center">
                            Didn't get the code?
                            <a href="javascript:void(0);"> Resend </a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- / Two Steps Verification -->
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree-3.png"
                alt="auth-tree" class="authentication-image-object-left d-none d-lg-block" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/auth-basic-mask-light.png"
                class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="172" alt="triangle-bg"
                data-app-light-img="illustrations/auth-basic-mask-light.png"
                data-app-dark-img="https://demos.themeselection.com/materio-bootstrap-html-admin-template/html/vertical-menu-template/illustrations/auth-basic-mask-dark.png" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree.png"
                alt="auth-tree" class="authentication-image-object-right d-none d-lg-block" />
        </div>
    </div>
</div>

<!-- / Content -->


<div class="mfa-container">
    <h2>MFA Verification</h2>


    <p>Enter the 6-digit code sent to your configured MFA method.</p>


</div>


<?php include_once('footer-plain.php'); ?>