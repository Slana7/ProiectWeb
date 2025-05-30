<?php
require_once __DIR__ . '/src/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .main-content {
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
        }

        #map {
            flex: 1;
            height: 100vh;
            width: 100%;
            transition: flex 0.3s ease;
        }

        #property-sidebar {
            width: 300px;
            flex: 0 0 300px;
            padding: 1.5rem;
            background-color: white;
            border-left: 1px solid #ddd;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        #property-sidebar.hidden {
            display: none;
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
            width: 100%;
            overflow: hidden;
        }

        .toggle-property-list {
            display: block;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #42a5f5;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        #filter-button {
            position: fixed;
            bottom: 90px;
            right: 20px;
            z-index: 1000;
            background-color: #fdd835;
            color: black;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        #filter-panel {
            position: absolute;
            right: 0;
            top: 0;
            height: 100vh;
            width: 300px;
            background: white;
            border-left: 1px solid #ccc;
            padding: 1.5rem;
            z-index: 1000;
            overflow-y: auto;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        #filter-panel.open {
            display: block;
            opacity: 1;
            transform: translateX(0);
        }

        #filter-panel h3 {
            margin-top: 1rem;
            font-size: 16px;
            color: #333;
        }

        #filter-panel label {
            display: block;
            margin-bottom: 0.5rem;
        }

        #close-filter {
            position: absolute;
            top: 15px;
            right: 20px;
            background: transparent;
            font-size: 24px;
            border: none;
            cursor: pointer;
            color: #333;
        }

        @media (max-width: 768px) {
            #property-sidebar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<div class="map-layout">
    <div id="map"></div>

    <div id="property-sidebar">
        <h2>Available Properties</h2>
        <ul id="property-list"></ul>
    </div>

    <div id="filter-panel">
        <button id="close-filter" title="Close filters">✖</button>

        <h3>Map Layers</h3>
        <label><input type="checkbox" id="layer-pollution"> Pollution</label><br>
        <label><input type="checkbox" id="layer-traffic"> Traffic</label><br>
        <label><input type="checkbox" id="layer-shops"> Shops</label><br>
        <label><input type="checkbox" id="layer-parking"> Parking Lots</label><br>

        <hr>
        <h3>Filter by Facilities</h3>
        <label><input type="checkbox" class="facility-filter" value="air conditioning"> Air Conditioning</label><br>
        <label><input type="checkbox" class="facility-filter" value="balcony"> Balcony</label><br>
        <label><input type="checkbox" class="facility-filter" value="central heating"> Central Heating</label><br>
        <label><input type="checkbox" class="facility-filter" value="elevator"> Elevator</label><br>
        <label><input type="checkbox" class="facility-filter" value="parking"> Parking</label><br>
        <label><input type="checkbox" class="facility-filter" value="wifi"> Wifi</label><br>

        <hr>
        <h3>Filter by Price</h3>
        <label>Min: <input type="number" id="min-price" style="width: 100%;"></label><br>
        <label>Max: <input type="number" id="max-price" style="width: 100%;"></label>

        <hr>
        <h3>Other Filters</h3>
        <label><input type="checkbox" id="filter-near-me"> Show properties near me</label><br>
    </div>
</div>

<button class="toggle-property-list" id="toggle-list">&#9776;</button>

<button id="filter-button" title="Open filters">🔍</button>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/map.js"></script>
<script>
    document.getElementById("filter-button")?.addEventListener("click", () => {
        document.getElementById("property-sidebar").classList.add("hidden");
        document.getElementById("filter-panel").classList.add("open");
    });

    document.getElementById("close-filter")?.addEventListener("click", () => {
        document.getElementById("filter-panel").classList.remove("open");
        document.getElementById("filter-panel").scrollTop = 0;
        document.getElementById("property-sidebar").classList.remove("hidden");
    });
</script>
</body>
</html>
