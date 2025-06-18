<?php
require_once __DIR__ . '/src/config/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/auth_header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <h1>Welcome to <?= APP_NAME ?></h1>
        <p>Manage properties, explore listings, and more.</p>
        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem; align-items: center;">
            <a href="<?= BASE_URL ?>login.php" class="btn-primary">Login</a>
            <a href="<?= BASE_URL ?>register.php" class="btn-primary">Register</a>
        </div>    </div>
</div>

<script src="<?= BASE_URL ?>public/assets/js/responsive.js"></script>
</body>
</html>
