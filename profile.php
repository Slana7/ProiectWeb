<?php
require_once __DIR__ . '/src/config/config.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo">
            <h2>REM</h2>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1>Your Profile</h1>
        </header>

        <section class="form-section">
            <form method="post" action="#" class="property-form">
                <label>Name:</label>
                <input type="text" name="name" value="User Example" required>

                <label>Email:</label>
                <input type="email" name="email" value="user@example.com" required>

                <label>New Password:</label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current password">

                <input type="submit" value="Update Profile" class="btn-primary">
            </form>
        </section>

        <footer class="dashboard-footer">
            &copy; <?= date('Y') ?> REM Project. All rights reserved.
        </footer>
    </main>
</div>
</body>
</html>
