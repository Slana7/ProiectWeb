<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
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
