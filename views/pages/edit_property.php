<?php

require_once __DIR__ . "/../../src/config/config.php";
require_once __DIR__ . '/../../src/controllers/PropertyController.php';


$property = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $controller = new PropertyController();
    $property = $controller->getPropertyById($id);
}

$facilities = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $controller = new PropertyController();
    $property = $controller->getPropertyById($id);
    $facilities = $controller->getFacilitiesByPropertyId($id);
}

$propertyFacilities = array_map(fn($f) => $f['name'], $facilities);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - <?= APP_NAME ?></title>
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
    <h1>Edit Property</h1>
</header>

<section class="form-section">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $id ?>" class="property-form">
        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" required>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($property['description']) ?></textarea>

        <label>Price (€):</label>
        <input type="number" name="price" value="<?= $property['price'] ?>" required>

        <label>Area (m²):</label>
        <input type="number" name="area" value="<?= $property['area'] ?>" required>

        <label>Status:</label>
        <select name="status">
            <option value="for_sale" <?= $property['status'] === 'for_sale' ? 'selected' : '' ?>>For Sale</option>
            <option value="for_rent" <?= $property['status'] === 'for_rent' ? 'selected' : '' ?>>For Rent</option>
        </select>

        <label>Location:</label>
        <p>Click on the map to update the property location</p>
        <div id="map"></div>

        <input type="hidden" name="lat" id="lat" value="<?= $property['lat'] ?>">
        <input type="hidden" name="lng" id="lng" value="<?= $property['lng'] ?>">

        <label>Facilities:</label>
        <div class="checkbox-group">
            <?php foreach ($facilities as $facility): ?>
                <label>
                    <input type="checkbox" name="facilities[]" value="<?= htmlspecialchars($facility['name']) ?>"
                           <?= in_array($facility['name'], $propertyFacilities) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($facility['name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="button-group">
            <input type="submit" value="Update Property" class="btn-primary">
            <a href="my_properties.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
    const initialLat = <?= $property['lat'] ?>;
    const initialLng = <?= $property['lng'] ?>;
    
    const map = L.map('map').setView([initialLat, initialLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([initialLat, initialLng]).addTo(map);

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;

        marker.setLatLng(e.latlng);
    });
</script>

</body>
</html>