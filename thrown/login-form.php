<?php
require_once 'core/init.php';
$csrf = csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body{font-family: Arial; background:#f0f2f5;}
        .login-container{width:350px; margin:100px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        input[type=email], input[type=password]{width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:4px;}
        input[type=checkbox]{margin-right:5px;}
        button{width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer;}
        button:hover{background:#0056b3;}
        .error{color:red;}
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $e){ echo h($e)."<br>"; } ?>
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
</body>
</html>
