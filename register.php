<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - <?= APP_NAME ?></title>
     <link rel="stylesheet" href="/REM/public/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <h2>Create an Account</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error">Registration failed. Please try again.</p>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>src/controllers/AuthController.php?action=register" class="auth-form">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Register" class="btn-primary">
        </form>

        <p class="switch-link">Already have an account?
            <a href="<?= BASE_URL ?>login.php">Login here</a>
        </p>
    </div>
</div>
</body>
</html>
