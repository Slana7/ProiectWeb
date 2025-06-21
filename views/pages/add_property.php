<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/controllers/PropertyController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$facilities = PropertyController::getFacilities();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        #map { height: 300px; width: 100%; margin-bottom: 20px; }
    </style>
</head>
<body>

<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Add New Property</h1>
</header>

<section class="form-section">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form method="post" action="../../src/controllers/PropertyController.php?action=add_property" class="property-form">
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

        <label>Location:</label>
        <p>Click on the map to set the property location</p>
        <div id="map"></div>

        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">

        <label>Facilities:</label>
        <div class="checkbox-group">
            <?php foreach ($facilities as $facility): ?>
                <label>
                    <input type="checkbox" name="facilities[]" value="<?= htmlspecialchars($facility['name']) ?>">
                    <?= htmlspecialchars($facility['name']) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <input type="submit" value="Add Property" class="btn-primary">
    </form>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
    const map = L.map('map').setView([47.1585, 27.6014], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }    });
</script>

</body>
</html>



