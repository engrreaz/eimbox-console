<?php
// require_once 'core/init.php';
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/functions.php';


$email = trim($_POST['email']);
echo $email;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: forgot_password.php");
        exit;
    }

    $email = trim($_POST['email']);
    echo $email;
    $user = find_user_by_email($conn, $email);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $hash = password_hash($token, PASSWORD_DEFAULT);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE usersapp SET reset_token_hash=?, reset_token_expires=? WHERE email=?");
        $stmt->bind_param('sss', $hash, $expires, $email);
        $stmt->execute();
        $stmt->close();

        $resetLink = "https://yourdomain.com/reset_password.php?email=" . urlencode($email) . "&token=$token";

        // Send email (pseudo-code, replace with PHPMailer or SMTP)
        $subject = "Password Reset Request";
        $message = "Click the link to reset your password: $resetLink\nThis link will expire in 1 hour.";
        // mail($email, $subject, $message, "From: noreply@yourdomain.com");

        $_SESSION['msg'] = "Password reset link sent to your email.";
    } else {
        $_SESSION['error'] = "If this email exists, a reset link will be sent.";
    }
    header("Location: forgot_password.php");
    exit;
}
