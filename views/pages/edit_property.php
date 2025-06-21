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

$propertyId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$propertyId) {
    $_SESSION['flash_message'] = 'No property specified for editing';
    header('Location: my_properties.php');
    exit;
}

$property = PropertyController::getPropertyById($propertyId);

if (!$property) {
    $_SESSION['flash_message'] = 'Property not found';
    header('Location: my_properties.php');
    exit;
}

if ($property['user_id'] != $userId) {
    $_SESSION['flash_message'] = 'You do not have permission to edit this property';
    header('Location: my_properties.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyData = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? '',
        'area' => $_POST['area'] ?? '',
        'status' => $_POST['status'] ?? 'for_sale',
        'lat' => $_POST['lat'] ?? $property['lat'],
        'lng' => $_POST['lng'] ?? $property['lng'],
        'facilities' => $_POST['facilities'] ?? []
    ];

    $result = PropertyController::updateProperty($propertyId, $propertyData, $userId, false);

    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        header('Location: my_properties.php');
        exit;
    } else {
        $errors[] = $result['message'];
        $property = PropertyController::getPropertyById($propertyId);
    }
}

$facilities = PropertyController::getFacilities();
$propertyFacilities = $property['facilities'] ?? [];
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

    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $propertyId ?>" class="property-form">
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
        </div>        <div class="button-group">
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
