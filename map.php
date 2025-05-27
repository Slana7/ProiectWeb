<?php
require_once __DIR__ . '/src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Map View - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        #map {
            flex-grow: 1;
            height: 100vh;
        }

        #property-sidebar {
            width: 300px;
            padding: 1.5rem;
            background-color: white;
            border-left: 1px solid #ddd;
            overflow-y: auto;
        }

        #property-sidebar h2 {
            margin-top: 0;
        }

        #property-list {
            list-style: none;
            padding-left: 0;
        }

        #property-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .map-layout {
            display: flex;
            height: 100vh;
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo">
            <h2>Real Estate Management Application</h2>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php" class="active">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="chat_overview.php">My Chats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content" style="padding: 0;">
        <div class="map-layout">
            <div id="map"></div>
            <div id="property-sidebar">
                <h2>Available Properties</h2>
                <ul id="property-list"></ul>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/map.js"></script>
</body>
</html>
