<?php
require_once __DIR__ . '/../../src/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
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
    <form id="addPropertyForm" class="property-form">
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
        <div class="checkbox-group" id="facilitiesGroup"></div>

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

let marker = null;
map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;

    if (marker) {
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }
});

async function loadFacilities() {
    const res = await fetch('../../src/api/facilities.php');
    const facilities = await res.json();
    const group = document.getElementById('facilitiesGroup');
    group.innerHTML = '';
    facilities.forEach(facility => {
        group.innerHTML += `
            <label>
                <input type="checkbox" name="facilities[]" value="${escapeHtml(facility.name)}">
                ${escapeHtml(facility.name)}
            </label>
        `;
    });
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
loadFacilities();

document.getElementById('addPropertyForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        title: form.title.value,
        description: form.description.value,
        price: form.price.value,
        area: form.area.value,
        status: form.status.value,
        lat: form.lat.value,
        lng: form.lng.value,
        facilities: Array.from(form.querySelectorAll('input[name="facilities[]"]:checked')).map(cb => cb.value)
    };
    const res = await fetch('../../src/api/properties.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
        alert('Property added!');
        window.location.href = 'my_properties.php';
    } else {
        alert(result.error || 'Failed to add property');
    }
};
</script>

</body>
</html>



