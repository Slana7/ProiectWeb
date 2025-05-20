<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome Back</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error">Login failed. Please try again.</p>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>src/controllers/AuthController.php?action=login" class="auth-form">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login" class="btn-primary">
        </form>

        <p class="switch-link">Don't have an account?
            <a href="<?= BASE_URL ?>register.php">Register here</a>
        </p>
    </div>
</div>
</body>
</html>
