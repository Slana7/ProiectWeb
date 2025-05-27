<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';

// Porneste sesiunea daca nu e deja pornita
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica daca utilizatorul e logat
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initializeaza variabile
$errors = [];
$success = false;

// Proceseaza datele trimise
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtine datele din formular
    $propertyData = [
        'user_id' => $_SESSION['user_id'],
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? '',
        'area' => $_POST['area'] ?? '',
        'status' => $_POST['status'] ?? 'for_sale',
        'lat' => $_POST['lat'] ?? '',
        'lng' => $_POST['lng'] ?? '',
        'facilities' => $_POST['facilities'] ?? []
    ];
    
    // Adauga proprietatea
    $result = addProperty($propertyData);
    
    if ($result['success']) {
        $success = true;
        $_SESSION['flash_message'] = $result['message'];
        
        // Redirectioneaza catre dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}

// Obtine toate facilitatile disponibile
$facilitiesStmt = $conn->query("SELECT id, name FROM facilities ORDER BY name");
$facilities = $facilitiesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <!-- Include biblioteca leaflet pentru selectarea locatiei -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        #map { height: 300px; width: 100%; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Add New Property</h1>
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

    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data" class="property-form">
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
        
        <!-- Campuri ascunse pentru coordonate -->
        <input type="hidden" name="lat" id="lat" required>
        <input type="hidden" name="lng" id="lng" required>

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

<?php include_once 'public/includes/dashboard_footer.php'; ?>

<script>
    // Initializeaza harta centrata pe Iasi, Romania
    const map = L.map('map').setView([47.1585, 27.6014], 13);
    
    // Adauga tiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Variabila pentru marker (initial null)
    let marker = null;
    
    // Adauga eveniment de click pe harta
    map.on('click', function(e) {
        // Obtine coordonatele
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Actualizeaza campurile ascunse
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        
        // Actualizeaza sau adauga un marker
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
</script>
</body>
</html>
