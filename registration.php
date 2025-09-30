<?php
require_once 'core/init.php';
$conn = db_connect();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $mfa_enabled = isset($_POST['mfa_enabled']) ? 1 : 0;

        // Validate password
        if (!validate_password($password)) {
            $errors[] = 'Password must be 8+ chars with uppercase, lowercase, number, special char';
        }

        // Check if email exists
        if (find_user_by_email($conn, $email)) {
            $errors[] = 'Email already registered';
        }

        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_ARGON2ID);

            $stmt = $conn->prepare("INSERT INTO usersapp (email, username, password_salt, password_hash, mfa_enabled) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssi', $email, $username, $password, $password_hash, $mfa_enabled);
            $stmt->execute();
            $stmt->close();

            $success = true;
        }
    }
}

$csrf = csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; }
        .container { width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        input { width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:4px; }
        button { width:100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        button:hover { background:#218838; }
        .error { color:red; }
        .success { color:green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <?php
    if (!empty($errors)) {
        echo '<div class="error">'.implode('<br>', array_map('h', $errors)).'</div>';
    }
    if ($success) echo '<div class="success">Registration successful! <a href="login.php">Login here</a></div>';
    ?>

    <form method="POST" action="registration.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label><input type="checkbox" name="mfa_enabled"> Enable MFA</label>
        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
