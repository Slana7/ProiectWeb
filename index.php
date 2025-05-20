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
    <title>Welcome - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <h1>Welcome to <?= APP_NAME ?></h1>
        <p>Manage properties, explore listings, and more.</p>
        <div style="margin-top: 2rem;">
            <a href="<?= BASE_URL ?>login.php" class="btn-primary" style="margin-right: 1rem;">Login</a>
            <a href="<?= BASE_URL ?>register.php" class="btn-primary">Register</a>
        </div>
    </div>
</div>
</body>
</html>
