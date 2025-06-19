<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        .auth-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .auth-form input {
            max-width: 100%;
        }
        .switch-link {
            text-align: center;
        }
    </style>
</head>
<body>
<?php include_once 'public/includes/auth_header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome Back</h2>

        <?php if (isset($_GET['updated'])): ?>
        <p class="success">Your profile was updated. Please log in again.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'database'): ?>
                <p class="error">A database error occurred. Please try again later.</p>
            <?php elseif ($_GET['error'] === 'email_taken'): ?>
                <p class="error">Email already in use. Please use a different one.</p>
            <?php else: ?>
                <p class="error">Login failed. Please try again.</p>
            <?php endif; ?>
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

<script src="<?= BASE_URL ?>public/assets/js/responsive.js"></script>
</body>
</html>
