<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/core-val.php';
require_once 'core/global_values.php';

$success = false;
$message = '';

try {
    $eiin = trim($_GET['eiin'] ?? '');
    $token = trim($_GET['token'] ?? '');

    if ($eiin === '' || $token === '') {
        throw new Exception('Invalid activation link.');
    }

    // Fetch registration hash and expiration
    $stmt = $conn->prepare("SELECT reg_hash, hash_expire, status, scname FROM scinfo WHERE sccode = ?");
    $stmt->bind_param("s", $eiin);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($reg_hash, $hash_expire, $status, $scname);
    if ($stmt->num_rows === 0) {
        throw new Exception('Institution not found.');
    }
    $stmt->fetch();
    $stmt->close();

    if ($status == 1) {
        throw new Exception('Account already activated.');
    }

if ($token !== $reg_hash) {
        throw new Exception('Invalid activation token.');
    }

    if (strtotime($hash_expire) < time()) {
        throw new Exception('Activation link has expired.');
    }

    // Activate account
    $stmt = $conn->prepare("UPDATE scinfo SET status = 1, reg_hash = NULL, hash_expire = NULL WHERE sccode = ?");
    $stmt->bind_param("s", $eiin);
    if (!$stmt->execute()) {
        throw new Exception('Failed to activate account.');
    }
    $stmt->close();
    $conn->close();

    $success = true;
    $message = "✅ Your account for <b>$scname</b> has been successfully activated. You can now login.";

} catch (Exception $e) {
    $message = "❌ " . $e->getMessage();
}
?>

<?php include 'header-plain.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-4">
                    <h3 class="mb-3"><?= $success ? 'Account Activated!' : 'Activation Failed' ?></h3>
                    <p><?= $message ?></p>
                    <?php if ($success): ?>
                        <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer-plain.php'; ?>
