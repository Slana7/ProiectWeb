<?php
require_once __DIR__ . '/../../src/config/config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
<?php include_once '../../public/includes/auth_header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Create an Account</h2>
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'empty'): ?>
                <p class="error">Please fill in all fields.</p>
            <?php elseif ($_GET['error'] === 'notfound'): ?>
                <p class="error">No account found with this email.</p>
            <?php elseif ($_GET['error'] === 'invalid'): ?>
                <p class="error">Incorrect password. Please try again.</p>
            <?php elseif ($_GET['error'] === 'email_taken'): ?>
                <p class="error">Email already in use. Please use a different one.</p>
            <?php elseif ($_GET['error'] === 'database'): ?>
                <p class="error">A database error occurred. Please try again later.</p>
            <?php else: ?>
                <p class="error">Login failed. Please try again.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
            <p class="success">Account created! You can now log in.</p>
        <?php endif; ?>

        <form method="post" action="../../src/controllers/AuthController.php?action=register" class="auth-form">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Register" class="btn-primary">
        </form>
        <p class="switch-link">Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<script src="../../public/assets/js/responsive.js"></script>
</body>
</html>



