<?php
require_once 'core/init.php';


if (isset($_SESSION['user_id'])) {
    // Already logged in → redirect to dashboard
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['partial_auth'])) {
    // Pending MFA → redirect MFA page
    header('Location: mfa_verify.php');
    exit;
}

$conn = db_connect();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $data = find_user_by_email($conn, $email);
        $user = $data['user'];
        $school = $data['school'];


        if (!$user) {
            // User not found
            $errors[] = 'Invalid email or password';
            auth_log($conn, 'login_failed', null, $email);
        } else {
            // Safe access with defaults
            $lockUntil = $user['lock_until'] ?? null;
            $failedAttempts = intval($user['failed_attempts'] ?? 0);
            $passwordHash = $user['password_hash'] ?? null;

            // Check account lock
            if ($lockUntil && strtotime($lockUntil) > time()) {
                $errors[] = 'Account locked. Try later.';
            }
            // Check password
            elseif ($passwordHash && password_verify($password, $passwordHash)) {
                // Reset failed attempts
                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=0, lock_until=NULL WHERE id=?");
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $stmt->close();

                auth_log($conn, 'login_success', $user['id']);

                // -------------------
                // MFA check
                // -------------------
                if (!empty($user['mfa_enabled'])) {
                    $token = sprintf("%06d", random_int(0, 999999));
                    $hash = password_hash($token, PASSWORD_DEFAULT);
                    $expires = date('Y-m-d H:i:s', time() + 300);

                    $stmt = $conn->prepare("UPDATE usersapp 
                    SET mfa_secret=?, mfa_temp_token=?, mfa_temp_expires=? 
                    WHERE id=?");
                    $stmt->bind_param('sssi', $token, $hash, $expires, $user['id']);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['partial_auth'] = $user['id'];

                    // Redirect first, send mail after flush
                    // ob_end_clean();
                    header("Location: mfa_verify.php");
                    // flush();
                    send_mfa_token($user, $token); // email OTP
                    exit;
                }

                // -------------------
                // Full login
                // -------------------
                store_user_session($user, $school);

                if ($remember) {
                    create_remember_token($conn, $user['id']);
                }

                // Suspicious activity flag
                $_SESSION['checked_suspicious'] = false;

                header('Location: core/suspicious-activity.php');
                exit;

            } else {
                // -------------------
                // Failed login attempt
                // -------------------
                $failedAttempts++;
                $lockUntil = null;
                if ($failedAttempts >= MAX_FAILED_ATTEMPTS) {
                    $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME_SECONDS);
                }

                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=?, lock_until=? WHERE id=?");
                $stmt->bind_param('isi', $failedAttempts, $lockUntil, $user['id']);
                $stmt->execute();
                $stmt->close();

                $errors[] = 'Invalid email or password';
                auth_log($conn, 'login_failed', $user['id'], $email);
            }
        }

    }
}

// CSRF token for form
$csrf = csrf_token();

include_once('header-plain.php');
?>
<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-2 mx-4">
            <!-- Login -->
            <div class="card p-sm-7 p-2">
                <!-- Logo -->
                <div class="app-brand justify-content-center mt-5">

                    <?php include 'core/top-part.php'; ?>
                </div>
                <!-- /Logo -->

                <div class="card-body mt-1">
                    <p class="mb-5">Please sign-in to your account</p>

                    <?php if (!empty($errors)): ?>
                        <div class="error bg-danger text-white rounded p-3 mb-3">
                            <?php foreach ($errors as $e)
                                echo h($e) . "<br>"; ?>
                        </div>
                    <?php endif; ?>


                    <form id="formAuthentication" class="mb-5" method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">

                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="Enter your email or username" autofocus required />
                            <label for="email">Email or Username</label>
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle form-control-validation">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password" required />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                            <a href="forgot_password.php" class="float-end mb-1">
                                <span>Forgot Password?</span>
                            </a>
                        </div>
                        <div class="mb-5">
                            <button class="btn btn-primary d-grid w-100" type="submit">login</button>
                        </div>
                    </form>

                    <p class="text-center mb-5">
                        <span>New on our platform?</span>
                        <a href="regd_new.php">
                            <span>Create an account</span>
                        </a>
                    </p>

                    <div class="divider my-5">
                        <div class="divider-text">or</div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
                            <i class="icon-base ri ri-facebook-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
                            <i class="icon-base ri ri-twitter-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
                            <i class="icon-base ri ri-github-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
                            <i class="icon-base ri ri-google-fill icon-24px"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-linkedin">
                            <i class="icon-base bi bi-qr-code icon-20px"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Login -->
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


<?php include_once('footer-plain.php'); ?>