<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo">
            <h2>Real Estate Management Application</h2>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php" class="active">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1>Add New Property</h1>
        </header>

        <section class="form-section">
            <form method="post" action="#" enctype="multipart/form-data" class="property-form">
                <label>Title:</label>
                <input type="text" name="title" required>

                <label>Description:</label>
                <textarea name="description" required></textarea>

                <label>Price (€):</label>
                <input type="number" name="price" required>

                <label>Area (m²):</label>
                <input type="number" name="area" required>

                <label>Status:</label>
                <select name="status">
                    <option value="for_sale">For Sale</option>
                    <option value="for_rent">For Rent</option>
                </select>

                <label>Latitude:</label>
                <input type="text" name="lat" required>

                <label>Longitude:</label>
                <input type="text" name="lng" required>

                <label>Facilities:</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="facilities[]" value="parking"> Parking</label>
                    <label><input type="checkbox" name="facilities[]" value="wifi"> WiFi</label>
                    <label><input type="checkbox" name="facilities[]" value="air conditioning"> AC</label>
                </div>

                <input type="submit" value="Add Property" class="btn-primary">
            </form>
        </section>

        <footer class="dashboard-footer">
            &copy; <?= date('Y') ?> REM Project. All rights reserved.
        </footer>
    </main>
</div>
</body>
</html>
