<?php
require_once 'core/init.php';

if (isset($_SESSION['user_id'])) {
    // Already logged in → redirect to dashboard
    header('Location: dashboard.php');
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

        $user = find_user_by_email($conn, $email);
        if (!$user) {
            $errors[] = 'Invalid email or password';
            auth_log($conn, 'login_failed', null, $email);
        } else {
            // lockout check
            if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
                $errors[] = 'Account locked. Try later.';
            } elseif (password_verify($password, $user['password_hash'])) {
                // reset failed attempts
                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=0, lock_until=NULL WHERE id=?");
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $stmt->close();

                auth_log($conn, 'login_success', $user['id']);

                // Check MFA
                if ($user['mfa_enabled']) {
                    $token = random_int(100000, 999999);
                    echo $token;
                    $hash = password_hash($token, PASSWORD_DEFAULT);
                    $expires = date('Y-m-d H:i:s', time() + 300); // 5 min
                    $stmt = $conn->prepare("UPDATE usersapp SET mfa_secret=?, mfa_temp_token=?, mfa_temp_expires=? WHERE id=?");
                    $stmt->bind_param('sssi', $token, $hash, $expires, $user['id']);
                    $stmt->execute();
                    $stmt->close();

                    // send token via user's MFA method
                    send_mfa_token($user, $token);

                    $_SESSION['partial_auth'] = $user['id'];
                    header('Location: mfa_verify.php');
                    exit;
                } else {
                    // Full login
                    store_user_session($user);
                    if ($remember)
                        create_remember_token($conn, $user['id']);
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                // failed attempt
                $fa = $user['failed_attempts'] + 1;
                $lock = null;
                if ($fa >= MAX_FAILED_ATTEMPTS)
                    $lock = date('Y-m-d H:i:s', time() + LOCKOUT_TIME_SECONDS);
                $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts=?, lock_until=? WHERE id=?");
                $stmt->bind_param('isi', $fa, $lock, $user['id']);
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
<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e)
                echo h($e) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label><input type="checkbox" name="remember"> Remember me</label>
        <button type="submit">Login</button>
    </form>
</div>


<?php include_once('footer.php'); ?>