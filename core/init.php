<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'functions-achievements.php';
require_once __DIR__ . '/../achievements_engine.php';
require_once 'core-val.php';

?>

<script>
    (function () {
        let theme = localStorage.getItem("templateCustomizer-vertical-menu-template--Theme");
        if (theme && document.cookie.indexOf("site_theme=" + theme) === -1) {
            document.cookie = "site_theme=" + theme + "; path=/";
            // location.reload(); // একবার reload হবে, তারপর PHP থিম পাবে
        }
    })();
</script>

<?php


$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Regenerate session occasionally
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Determine current file
$currentFile = basename($_SERVER['PHP_SELF']);
$_SESSION['current_page'] = $currentFile;

// Publicly accessible pages (no login required)
$publicPages = ['regd_new.php', 'login.php', 'logout.php', 'mfa_verify.php', 'forgot_password.php', 'forgot_password_process', 'reset_password.php'];



// Fingerprint
// if (!isset($_SESSION['fingerprint'])) {
//     $_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . client_ip());
// } elseif ($_SESSION['fingerprint'] !== hash('sha256', $_SERVER['HTTP_USER_AGENT'] . client_ip())) {
//     session_destroy();
//     header('Location: login.php');
//     exit;
// }

// Only check session for protected pages
if (!in_array($currentFile, $publicPages)) {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    } else {
        $user_id = $_SESSION['user_id'] ?? '';

        require_once 'global_values.php';
        // require_once 'billing_checker.php';
        require_once 'permissions.php';
        require_once 'package_checker.php';

    }
}
if($_SESSION['checked_suspicious'] == false){
require_once('suspicious-activity.php');
}

// createNotification($user_id, "Contrats!", 'You have won the achievements', 'my-achievements.php', 'achievement');