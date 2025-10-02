<?php
require_once 'db.php';

// ========================
// XSS Safe Output
// ========================
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ========================
// CSRF Token
// ========================
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========================
// Client IP
// ========================
function client_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ========================
// Audit Log
// ========================
function auth_log($conn, $action, $userId = null, $emailAttempt = null)
{
    $ip = client_ip();
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $stmt = $conn->prepare("INSERT INTO auth_logs(user_id, email_attempted, ip, user_agent, action) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $userId, $emailAttempt, $ip, $ua, $action);
    $stmt->execute();
    $stmt->close();
}

// ========================
// Password Validation
// ========================
function validate_password($pwd)
{
    if (strlen($pwd) < 8)
        return false;
    if (!preg_match('/[a-z]/', $pwd))
        return false;
    if (!preg_match('/[A-Z]/', $pwd))
        return false;
    if (!preg_match('/[0-9]/', $pwd))
        return false;
    if (!preg_match('/[\W]/', $pwd))
        return false;
    return true;
}

// ========================
// Find User by Email
// ========================
function find_user_by_email($conn, $email)
{
    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null; // ইউজার পাওয়া যায়নি
    }

    // ২) স্কুল ডেটা ফেচ করা (sccode দিয়ে)
    $stmt2 = $conn->prepare("SELECT * FROM scinfo WHERE sccode=? LIMIT 1");
    $stmt2->bind_param('i', $user['sccode']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $school = $res2->fetch_assoc();
    $stmt2->close();

    // ৩) সেশনে রাখা
    // $_SESSION['user'] = $user;
    // $_SESSION['school'] = $school ?: [];

    return [
        "user" => $user,
        "school" => $school
    ];
}

// ========================
// Store User Session
// ========================
function store_user_session($user, $school = [])
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['first_name'] = $user['first_name'] ?? '';
    $_SESSION['last_name'] = $user['last_name'] ?? '';
    $_SESSION['phone'] = $user['phone'] ?? '';
    $_SESSION['address'] = $user['address'] ?? '';
    $_SESSION['dob'] = $user['dob'] ?? '';
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['userlevel'] = $user['user_level'] ?? '';
    $_SESSION['sccode'] = $user['sccode'] ?? '';
    $_SESSION['photourl'] = $user['photourl'] ?? '';

    // স্কুল ইনফো
    $_SESSION['scname'] = $school['scname'] ?? '';
    $_SESSION['sccategory'] = $school['sccategory'] ?? '';
}


// ========================
// Remember-Me Token
// ========================
function create_remember_token($conn, $userId)
{
    $token = bin2hex(random_bytes(32));
    $hash = password_hash($token, PASSWORD_BCRYPT);
    $expires = date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_EXPIRE_DAYS . ' days'));

    $stmt = $conn->prepare("UPDATE usersapp SET remember_token_hash=?, remember_token_expires=? WHERE id=?");
    $stmt->bind_param('ssi', $hash, $expires, $userId);
    $stmt->execute();
    $stmt->close();

    setcookie('remember_me', $userId . ':' . $token, time() + 60 * 60 * 24 * REMEMBER_ME_EXPIRE_DAYS, '/', '', true, true);
}

// ========================
// Verify Remember-Me Token
// ========================

function verify_remember_token($conn)
{
    if (empty($_COOKIE['remember_me'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        return false;
    }

    [$uid, $token] = $parts;

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user)
        return false;

    // Expire চেক
    if (empty($user['remember_token_expires']) || strtotime($user['remember_token_expires']) < time()) {
        return false;
    }

    // Token verify
    if (!password_verify($token, $user['remember_token_hash'])) {
        return false;
    }

    // স্কুল ডেটা ফেচ
    $stmt2 = $conn->prepare("SELECT * FROM scinfo WHERE sccode=? LIMIT 1");
    $stmt2->bind_param('i', $user['sccode']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $school = $res2->fetch_assoc();
    $stmt2->close();

    // সেশনে স্টোর করা (এখন দুইটা আর্গুমেন্ট পাঠাই)
    store_user_session($user, $school);

    // চাইলে এখানে নতুন টোকেন issue করা যেতে পারে (replay attack রোধ করতে)
    // create_remember_token($conn, $user['id']);

    return true;
}



// ========================
// Send MFA Token (Stub)
// ========================
function send_mfa_token($user, $token)
{
    $to = $user['email'];
    $subject = "Your MFA code";
    $message = "Your one-time code is: $token\nIt expires in 5 minutes.";
    $headers = "From: noreply@example.com\r\n";

    $mail_type = 'otp';
    $mail_to = $to;
    $mail_name = 'EIMBox User';
    $mail_subject = 'EIMBox OTP for MFA';
    $mail_attach = '';
    $msg_success = "Your OTP has been send to your mail $mail_to";
    include('mailer/send-mail.php');



    // Uncomment to send email if mail server configured
    // mail($to, $subject, $message, $headers);

    // SMS / Push integration এখানে করতে পারেন
}



// Example: admin user detection (adjust your logic)
function is_admin_user()
{
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') || (isset($_SESSION['user_name']) && $_SESSION['user_name'] == 'engrreaz@gmail.com');
}

// Error logging location
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log'); // adjust path
ini_set('display_errors', 0); // never show errors to normal users

// Custom error handler
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');

function custom_error_handler($errno, $errstr, $errfile, $errline)
{
    $errorMessage = "PHP ERROR [$errno]: $errstr in $errfile on line $errline";
    error_log($errorMessage); // always log

    if (is_admin_user()) {
        // Admin sees detailed error
        echo "<pre>$errorMessage</pre>";
    } else {
        // Normal users → redirect or show generic page
        echo "<pre>$errorMessage</pre>";
        // if(!headers_sent()){
        //     header("Location: error_page.php");
        //     exit;
        // } else {

        echo "<h2>Something went wrong. Please try again later.</h2>";
        //     exit;
        // }
    }
}

// Custom exception handler
function custom_exception_handler($exception)
{
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() .
        " in " . $exception->getFile() .
        " on line " . $exception->getLine();
    error_log($errorMessage);

    if (is_admin_user()) {
        echo "<pre>$errorMessage</pre>";
    } else {
        echo "<pre>$errorMessage</pre>";
        // if(!headers_sent()){
        //     header("Location: error_page.php");
        //     exit;
        // } else {
        //     echo "<h2>Something went wrong. Please try again later.</h2>";
        //     exit;
        // }
    }
}

// Optional: shutdown function to catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE))) {
        $msg = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log($msg);

        if (is_admin_user()) {
            echo "<pre>$msg</pre>";
        } else {
            if (!headers_sent()) {
                header("Location: pages/error_page.php");
                exit;
            } else {
                echo "<h2>Something went wrong. Please try again later.</h2>";
                exit;
            }
        }
    }
});

