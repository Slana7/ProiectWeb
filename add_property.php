<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
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
    
    // Add property
    $result = addProperty($propertyData);
    
    if ($result['success']) {
        $success = true;
        $_SESSION['flash_message'] = $result['message'];
        
        // Redirect to dashboard or property details page
        header('Location: dashboard.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}

// Get all available facilities for the form
$facilitiesStmt = $conn->query("SELECT id, name FROM facilities ORDER BY name");
$facilities = $facilitiesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <!-- Include a map library like Leaflet for location selection -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        #map { height: 300px; width: 100%; margin-bottom: 20px; }
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
            <a href="map.php">Map</a>
            <a href="add_property.php" class="active">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="chat_overview.php">My Chats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
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
                
                <!-- Hidden fields for coordinates -->
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
    </main>
</div>

<script>
    // Initialize the map centered on Iasi, Romania
    const map = L.map('map').setView([47.1585, 27.6014], 13);
    
    // Add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add a marker variable (initially null)
    let marker = null;
    
    // Add a click event to the map
    map.on('click', function(e) {
        // Get the coordinates
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Update the hidden form fields
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        
        // Update or add a marker
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
</script>
</body>
</html>
