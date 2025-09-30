<?php
require_once 'core/init.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    // Try remember-me
    $conn = db_connect();
    if (!verify_remember_token($conn)) {
        header('Location: login.php');
        exit;
    }
}

$user_name = $_SESSION['user_name'] ?? 'User';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            padding: 20px;
        }

        .dashboard {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            margin: auto;
        }

        a.logout {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <h2>Welcome, <?php echo h($user_name); ?>!</h2>
        <p>This is a secure dashboard page.</p>

        <button id="btn_1">One</button>
        <button id="btn_2">Two</button>
        <button id="btn_3">Three</button>
        <button id="btn_4">Four</button>
        <button id="btn_5">Five</button>


        <?php
        echo $_SESSION['user_id'] . '/ ' .
            $_SESSION['user_email'] . '/ ' .
            $_SESSION['user_name'] . '/ ' .
            $_SESSION['first_name'] . '/ ' .
            $_SESSION['last_name'] . '/ ' .
            $_SESSION['phone'] . '/ ' .
            $_SESSION['address'] . '/ ' .
            $_SESSION['dob'];
        ?>


        <p><a class="logout" href="logout.php">Logout</a></p>
    </div>
</body>

</html>