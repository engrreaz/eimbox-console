<?php
require_once 'core/init.php';

$errors = [];

// Ensure user is partially authenticated
if (!isset($_SESSION['partial_auth'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['partial_auth'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM usersapp WHERE id=? LIMIT 1");
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
        } elseif (empty($user['mfa_temp_token']) || empty($user['mfa_temp_expires'])) {
            $errors[] = "No MFA token found. Please login again.";
        } elseif (strtotime($user['mfa_temp_expires']) < time()) {
            $errors[] = "MFA code expired. Please login again.";
        } elseif (!password_verify($code, $user['mfa_temp_token'])) {
            $errors[] = "Incorrect MFA code.";
        } else {
            // ✅ MFA successful → full login
            store_user_session($user, $school);

            // Clear temporary MFA token
            $stmt = $conn->prepare("UPDATE usersapp SET mfa_temp_token=NULL, mfa_temp_expires=NULL WHERE id=?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();

            // Optional: Remember-me
            if (!empty($_SESSION['remember_me_flag'])) {
                create_remember_token($conn, $userId);
            }

            unset($_SESSION['partial_auth'], $_SESSION['remember_me_flag']);
            $_SESSION['checked_suspicious'] = false;
            header('Location: core/suspicious-activity.php');
            exit;
        }
    }
}

$csrf = csrf_token();

include_once('header-plain.php');
?>

<div class="positive-relative">
    <div class="authentication-wrapper authentication-basic">
        <div class="authentication-inner py-6 mx-4">
            <div class="card p-sm-7 p-3">
                <!-- Logo -->
                <div class="app-brand justify-content-center mt-5">

                    <?php include 'core/top-part.php'; ?>
                </div>

                <div class="card-body mt-1">
                    <h4 class="mb-1">Two Step Verification 💬</h4>
                    <p class="text-start mb-4">
                        We sent a 6-digit verification code to your mobile.<br>
                        <span class="d-block mt-1 h6">******1234</span>
                    </p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger p-2">
                            <?php foreach ($errors as $e)
                                echo h($e) . "<br>"; ?>
                        </div>
                    <?php endif; ?>

                    <form id="mfaForm" method="POST" action="">
                        <div class="d-flex justify-content-between gap-2 mb-3">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <input type="tel" class="form-control text-center otp-input" maxlength="1"
                                    inputmode="numeric" pattern="\d*" required />
                            <?php endfor; ?>
                        </div>

                        <input type="hidden" name="mfa_code" id="mfa_code">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                        <button type="submit" class="btn btn-primary d-grid w-100 mb-4">Verify my account</button>

                        <div class="text-center">
                            Didn’t get the code?
                            <a href="mailer/resend-mfa.php">Resend</a>
                        </div>
                        <div class="text-center">
                            Sign in with a different account?
                            <a href="logout.php">Click Here</a>
                        </div>
                    </form>
                </div>
            </div>



            <?php if (!empty($_SESSION['mfa_message'])): ?>
                <div class="alert alert-success p-2">
                    <?= h($_SESSION['mfa_message']); ?>
                </div>
                <?php unset($_SESSION['mfa_message']); ?>
            <?php endif; ?>


            <img src="https://demos.themeselection.com/materio-bootstrap-html-admin-template/assets/img/illustrations/tree.png"
                alt="auth-tree" class="authentication-image-object-right d-none d-lg-block" />
        </div>
    </div>
</div>

<script>
    // 🔢 Join all OTP inputs into one hidden input
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('.otp-input');
        const mfaInput = document.getElementById('mfa_code');

        inputs.forEach((input, i) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && i < inputs.length - 1) {
                    inputs[i + 1].focus();
                }
                mfaInput.value = Array.from(inputs).map(el => el.value).join('');
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && i > 0) {
                    inputs[i - 1].focus();
                }
            });
        });
    });
</script>

<?php include_once('footer-plain.php'); ?>