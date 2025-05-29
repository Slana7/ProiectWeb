const map = L.map('map').setView([47.1585, 27.6014], 12); // Centrat pe Iași

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// ====================== //
// Straturi suplimentare //
// ====================== //

const pollutionLayer = L.layerGroup([
    L.circle([47.164, 27.59], { radius: 300, color: 'red' }).bindPopup("High Pollution Area"),
    L.circle([47.15, 27.61], { radius: 200, color: 'red' }).bindPopup("Polluted Zone")
]);

const trafficLayer = L.layerGroup([
    L.polyline([
        [47.162, 27.58],
        [47.160, 27.61]
    ], { color: 'orange', weight: 5 }).bindPopup("Traffic Congestion"),
    L.polyline([
        [47.155, 27.62],
        [47.153, 27.6]
    ], { color: 'orange', weight: 5 }).bindPopup("Heavy Traffic Area")
]);

const shopsLayer = L.layerGroup([
    L.marker([47.16, 27.61]).bindPopup("Mega Image"),
    L.marker([47.157, 27.595]).bindPopup("Lidl"),
    L.marker([47.159, 27.607]).bindPopup("Carrefour Express")
]);

document.getElementById("layer-pollution")?.addEventListener("change", (e) => {
    e.target.checked ? pollutionLayer.addTo(map) : map.removeLayer(pollutionLayer);
});

document.getElementById("layer-traffic")?.addEventListener("change", (e) => {
    e.target.checked ? trafficLayer.addTo(map) : map.removeLayer(trafficLayer);
});

document.getElementById("layer-shops")?.addEventListener("change", (e) => {
    e.target.checked ? shopsLayer.addTo(map) : map.removeLayer(shopsLayer);
});

// ============================ //
// Marker + listă proprietăți //
// ============================ //

let allProperties = [];
let markersGroup = L.layerGroup().addTo(map);

function loadAndDisplayProperties() {
    fetch('public/api/get_property.php')
        .then(res => res.json())
        .then(data => {
            allProperties = data;
            updateDisplayedProperties();
        })
        .catch(error => {
            console.error('Error loading properties:', error);
        });
}

function updateDisplayedProperties() {
    const list = document.getElementById('property-list');
    if (list) list.innerHTML = '';

    const selectedFacilities = Array.from(document.querySelectorAll('.facility-filter:checked'))
        .map(cb => cb.value);

    const filterNearMe = document.getElementById('filter-near-me')?.checked;

    const minPrice = parseInt(document.getElementById("min-price")?.value) || 0;
    const maxPrice = parseInt(document.getElementById("max-price")?.value) || Number.MAX_VALUE;

    markersGroup.clearLayers();

    const filterAndDisplay = (properties) => {
        properties.forEach(property => {
            if (!shouldDisplayProperty(property, selectedFacilities, minPrice, maxPrice)) return;
            displayMarker(property);
        });
    };

    if (filterNearMe && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;

            const nearbyProperties = allProperties.filter(property => {
                const distance = getDistanceFromLatLonInKm(userLat, userLng, property.lat, property.lng);
                return distance <= 3;
            });

            filterAndDisplay(nearbyProperties);
        });
    } else {
        filterAndDisplay(allProperties);
    }
}

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

function shouldDisplayProperty(property, selectedFacilities, minPrice, maxPrice) {
    if (property.price < minPrice || property.price > maxPrice) return false;

    if (selectedFacilities.length > 0) {
        if (!property.facilities) return false;
        const propertyFacilities = property.facilities.split(',').map(f => f.trim());
        return selectedFacilities.every(f => propertyFacilities.includes(f));
    }
    return true;
}

function displayMarker(property) {
    const marker = L.marker([property.lat, property.lng]);
    marker.bindPopup(`
        <b>${property.title}</b><br>
        €${property.price}<br>
        <a href="property_details.php?id=${property.id}">View Details</a>
    `);
    marker.addTo(markersGroup);

    const list = document.getElementById('property-list');
    if (list) {
        const li = document.createElement('li');
        const link = document.createElement('a');
        link.href = `property_details.php?id=${property.id}`;
        link.textContent = `${property.title} (€${property.price})`;
        li.appendChild(link);
        list.appendChild(li);
    }
}

document.querySelectorAll('.facility-filter').forEach(cb => {
    cb.addEventListener('change', updateDisplayedProperties);
});

document.getElementById('filter-near-me')?.addEventListener('change', updateDisplayedProperties);
document.getElementById('min-price')?.addEventListener('input', updateDisplayedProperties);
document.getElementById('max-price')?.addEventListener('input', updateDisplayedProperties);

document.getElementById("filter-button")?.addEventListener("click", () => {
    const panel = document.getElementById("filter-panel");
    panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "block" : "none";
});

loadAndDisplayProperties();
