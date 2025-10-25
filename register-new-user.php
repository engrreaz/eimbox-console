<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/core-val.php';
require_once 'core/global_values.php';
require_once 'core/functions.php';

$csrf = csrf_token();
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

include_once('header-plain.php');

// --------------------------------------
// TOKEN VALIDATION SECTION
// --------------------------------------
$valid = false;
$message = '';
$fullname = '';

if (!empty($email) && !empty($token)) {
    $stmt = $conn->prepare("
        SELECT email, reset_token_hash, reset_token_expires, status 
        FROM usersapp 
        WHERE email = ? LIMIT 1
    ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        $hash = $user['reset_token_hash'] ?? '';
        $expires_raw = $user['reset_token_expires'] ?? '';
        $expires = !empty($expires_raw) ? strtotime($expires_raw) : 0;
        $fullname = $user['email'] ?? '';
        $status = (int)($user['status'] ?? 0);

        // ১️⃣ টোকেন মিল আছে কি না যাচাই
        if (empty($hash) || !password_verify($token, $hash)) {
            $message = "Invalid or tampered activation link!";
        }
        // ২️⃣ সময় এক্সপায়ার হয়েছে কি না যাচাই
        elseif (time() > $expires) {
            $message = "Activation link has expired. Please request a new one.";
        }
        // ৩️⃣ ইউজার আগেই অ্যাক্টিভ কিনা চেক
        elseif ($status === 1) {
            $message = "Your account is already active. You can login now.";
        } else {
            // ✅ সব কিছু ঠিক আছে → এক্টিভ করা হবে
            $conn->query("
                UPDATE usersapp 
                SET reset_token_hash = NULL, reset_token_expires = NULL, status = 1 
                WHERE email = '" . $conn->real_escape_string($email) . "'
            ");
            $valid = true;
        }
    } else {
        $message = "Invalid activation link!";
    }
    $stmt->close();
} else {
    $message = "Activation link is incomplete!";
}
?>

<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <div class="card p-sm-7 p-2">

                <div class="app-brand justify-content-center mt-5">
                    <?php include 'core/top-part.php'; ?>
                </div>

                <div class="card-body mt-1">

                    <?php if (!$valid): ?>
                        <!-- ❌ INVALID TOKEN / EXPIRED -->
                        <h4 class="mb-3 text-danger">Activation Failed</h4>
                        <p class="mb-5"><?php echo h($message); ?></p>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                Back to Login
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- ✅ SUCCESSFUL ACTIVATION -->
                        <div class="text-center my-5">
                            <h4 class="text-success mb-3">🎉 Account Activated Successfully!</h4>
                            <p class="fs-5 mb-5">
                                Welcome <b><?php echo h($fullname); ?></b>!  
                                Your account has been successfully activated.  
                                You can now log in and start using <b>EIMBOX</b>.
                            </p>
                            <a href="login.php" class="btn btn-success btn-lg">
                                Proceed to Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Decorative Images -->
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree-3.png"
                alt="auth-tree" class="authentication-image-object-left d-none d-lg-block" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/auth-basic-mask-light.png"
                class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="172" alt="triangle-bg" />
            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree.png"
                alt="auth-tree" class="authentication-image-object-right d-none d-lg-block" />
        </div>
    </div>
</div>

<?php include_once('footer-plain.php'); ?>
