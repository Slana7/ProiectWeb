<?php
require_once __DIR__ . "/../../src/config/config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header("Location: my_properties.php");
    exit;
}
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
    <div id="api-message"></div>
    <form id="editPropertyForm" class="property-form" autocomplete="off" style="display:none;">
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
        <p>Click on the map to update the property location</p>
        <div id="map"></div>
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">

        <label>Facilities:</label>
        <div class="checkbox-group" id="facilitiesGroup"></div>

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
const propertyId = <?= (int)$id ?>;
let marker = null;
let map = null;

async function loadPropertyAndFacilities() {
    const [propertyRes, facilitiesRes] = await Promise.all([
        fetch(`../../src/api/property.php?id=${propertyId}`),
        fetch('../../src/api/facilities.php')
    ]);
    const property = await propertyRes.json();
    const facilities = await facilitiesRes.json();

    if (!property || property.error) {
        document.getElementById('api-message').innerHTML = '<div class="alert alert-error">Property not found.</div>';
        return;
    }

    const form = document.getElementById('editPropertyForm');
    form.title.value = property.title;
    form.description.value = property.description;
    form.price.value = property.price;
    form.area.value = property.area;
    form.status.value = property.status;
    form.lat.value = property.lat;
    form.lng.value = property.lng;

    const facilitiesGroup = document.getElementById('facilitiesGroup');
    facilitiesGroup.innerHTML = '';
    facilities.forEach(facility => {
        const checked = property.facilities && property.facilities.includes(facility.name) ? 'checked' : '';
        facilitiesGroup.innerHTML += `
            <label>
                <input type="checkbox" name="facilities[]" value="${escapeHtml(facility.name)}" ${checked}>
                ${escapeHtml(facility.name)}
            </label>
        `;
    });

    map = L.map('map').setView([property.lat, property.lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([property.lat, property.lng]).addTo(map);

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        form.lat.value = lat;
        form.lng.value = lng;
        marker.setLatLng(e.latlng);
    });

    form.style.display = '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('editPropertyForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const facilities = Array.from(form.querySelectorAll('input[name="facilities[]"]:checked')).map(cb => cb.value);

    const data = {
        title: form.title.value,
        description: form.description.value,
        price: form.price.value,
        area: form.area.value,
        status: form.status.value,
        lat: form.lat.value,
        lng: form.lng.value,
        facilities: facilities
    };

    const res = await fetch(`../../src/api/property.php?id=${propertyId}`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    const result = await res.json();
    const msgDiv = document.getElementById('api-message');
    if (result.success) {
        msgDiv.innerHTML = '<div class="alert alert-success">Property updated successfully!</div>';
        setTimeout(() => window.location.href = 'my_properties.php', 1200);
    } else {
        msgDiv.innerHTML = '<div class="alert alert-danger">' + (result.error || 'Failed to update property') + '</div>';
    }
};

loadPropertyAndFacilities();
</script>
</body>
</html>