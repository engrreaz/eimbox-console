<?php
// require_once 'core/init.php';
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/functions.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();


if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!csrf_validate($_POST['csrf_token'])){
        $_SESSION['error']="Invalid CSRF token.";
        header("Location: reset_password.php");
        exit;
    }

    $email = $_POST['email'];
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if($password !== $password2){
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset_password.php?email=".urlencode($email)."&token=".urlencode($token));
        exit;
    }

    if(!validate_password($password)){
        $_SESSION['error'] = "Password must be at least 8 chars, with upper, lower, number, and special character.";
        header("Location: reset_password.php?email=".urlencode($email)."&token=".urlencode($token));
        exit;
    }

    $stmt = $conn->prepare("SELECT reset_token_hash, reset_token_expires FROM usersapp WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if(!$user || strtotime($user['reset_token_expires']) < time() || !password_verify($token, $user['reset_token_hash'])){
        $_SESSION['error'] = "Invalid or expired reset link.";
        header("Location: forgot_password.php");
        exit;
    }

    // Update password
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    $stmt = $conn->prepare("UPDATE usersapp SET password_salt=?, password_hash=?, reset_token_hash=NULL, reset_token_expires=NULL WHERE email=?");
    $stmt->bind_param('sss', $password, $hash, $email);
    $stmt->execute();
    $stmt->close();

    $_SESSION['msg'] = "Password successfully reset. You can now login.";
    header("Location: login.php");
    exit;
}
