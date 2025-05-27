<?php
require_once __DIR__ . '/src/config/config.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/REM/public/assets/css/style.css">

</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo">
            <h2>Real Estate Management Application</h2>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="chat_overview.php">My Chats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1>Welcome to your Dashboard</h1>
            <p>Here you can explore properties, add new listings or manage your profile.</p>
        </header>

        <section class="cards">
            <div class="card">
                <h3>View Property Map</h3>
                <a href="map.php" class="btn-link">Open Map</a>
            </div>
            <div class="card">
                <h3>Add New Property</h3>
                <a href="add_property.php" class="btn-link">Add Property</a>
            </div>
            <div class="card">
                <h3>Edit Profile</h3>
                <a href="profile.php" class="btn-link">Update Info</a>
            </div>
        </section>

        <footer class="dashboard-footer">
            &copy; <?= date('Y') ?> REM Project. All rights reserved.
        </footer>
    </main>
</div>
</body>
</html>
