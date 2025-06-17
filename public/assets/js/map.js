const map = L.map('map').setView([47.1585, 27.6014], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const pollutionLayer = L.layerGroup();
const trafficLayer = L.layerGroup();
const shopsLayer = L.layerGroup();
const parkingLayer = L.layerGroup();

function fetchShops() {
    const overpassUrl = "https://overpass-api.de/api/interpreter";
    const bbox = "47.10,27.50,47.20,27.70";
    
    const query = `
    [out:json];
    (
      node["shop"="supermarket"]["name"~"Lidl|Kaufland|Mega Image|Carrefour|Profi|Penny",i](${bbox});
      way["shop"="supermarket"]["name"~"Lidl|Kaufland|Mega Image|Carrefour|Profi|Penny",i](${bbox});
      relation["shop"="supermarket"]["name"~"Lidl|Kaufland|Mega Image|Carrefour|Profi|Penny",i](${bbox});
    );
    out center;
    `;
    
    fetch(overpassUrl, {
        method: "POST",
        body: `data=${encodeURIComponent(query)}`
    })
    .then(response => response.json())
    .then(data => {
        shopsLayer.clearLayers();
        
        data.elements.forEach(element => {
            let lat, lng, name;
            
            if (element.type === "node") {
                lat = element.lat;
                lng = element.lon;
                name = element.tags.name;
            } else {
                lat = element.center.lat;
                lng = element.center.lon;
                name = element.tags.name;
            }
            
            const marketIcon = L.divIcon({
                className: 'custom-market-icon',
                html: `
                    <div style="
                        background-color: #3498db; 
                        color: white;
                        width: 25px; 
                        height: 25px; 
                        border-radius: 50%; 
                        border: 2px solid white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 16px;
                        box-shadow: 0 1px 5px rgba(0,0,0,0.4);
                    ">
                        🛒
                    </div>
                `,
                iconSize: [25, 25],
                iconAnchor: [12, 12],
                popupAnchor: [0, -10]
            });
            
            const marker = L.marker([lat, lng], { icon: marketIcon }).bindPopup(name);
            shopsLayer.addLayer(marker);
        });
    })
    .catch(error => {
        console.error("Error fetching shops data:", error);
        const fallbackShops = [
            { lat: 47.16, lng: 27.61, name: "Mega Image" },
            { lat: 47.157, lng: 27.595, name: "Lidl" },
            { lat: 47.159, lng: 27.607, name: "Carrefour Express" }
        ];
        
        const marketIcon = L.divIcon({
            className: 'custom-market-icon',
            html: `
                <div style="
                    background-color: #3498db; 
                    color: white;
                    width: 25px; 
                    height: 25px; 
                    border-radius: 50%; 
                    border: 2px solid white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 16px;
                    box-shadow: 0 1px 5px rgba(0,0,0,0.4);
                ">
                    🛒
                </div>
            `,
            iconSize: [25, 25],
            iconAnchor: [12, 12],
            popupAnchor: [0, -10]
        });
        
        fallbackShops.forEach(shop => {
            L.marker([shop.lat, shop.lng], { icon: marketIcon })
                .bindPopup(shop.name)
                .addTo(shopsLayer);
        });
    });
}

function fetchTrafficData() {
    trafficLayer.clearLayers();
    
    const overpassUrl = "https://overpass-api.de/api/interpreter";
    const bbox = "47.10,27.50,47.20,27.70";
    
    const query = `
    [out:json];
    (
      way["highway"="primary"](${bbox});
      way["highway"="secondary"](${bbox});
      way["highway"="trunk"](${bbox});
      way["highway"="primary_link"](${bbox});
      way["highway"="secondary_link"](${bbox});
      way["highway"="trunk_link"](${bbox});
    );
    out body geom;
    `;
    
    console.log('Fetching major road data from Overpass API');
    
    const loadingControl = L.control({position: 'bottomleft'});
    loadingControl.onAdd = function() {
        const div = L.DomUtil.create('div', 'info loading');
        div.innerHTML = '<strong>Loading traffic data...</strong>';
        div.style.padding = '6px 8px';
        div.style.background = 'white';
        div.style.borderRadius = '4px';
        return div;
    };
    loadingControl.addTo(map);
    
    fetch(overpassUrl, {
        method: "POST",
        body: `data=${encodeURIComponent(query)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Overpass API response was not ok');
        }
        return response.json();
    })
    .then(data => {
        map.removeControl(loadingControl);
        
        if (!data.elements || data.elements.length === 0) {
            throw new Error('No traffic data available');
        }
        
        const namedRoads = data.elements.filter(element => element.tags);
        
        namedRoads.forEach(element => {
            if (element.geometry && element.geometry.length > 1) {
                const points = element.geometry.map(point => [point.lat, point.lon]);
                const roadType = element.tags?.highway || 'road';
                const roadName = element.tags?.name || roadType;
                
                let trafficLevel, color;
                
                const congestedStreets = [
                    "Șoseaua Păcurari", "Tudor Vladimirescu", "Bulevardul Independenței",
                    "Bulevardul Carol I", "Strada Palat", "Bulevardul Ștefan cel Mare",
                    "Șoseaua Nicolina", "Bulevardul Copou", "Șoseaua Bucium", 
                    "Bulevardul Chimiei", "Calea Galata", "Șoseaua Arcu",
                    "Strada Cuza Vodă", "Strada Sărărie", "Aleea Grigore Ghica Vodă"
                ];
                
                if (congestedStreets.some(street => roadName && roadName.includes(street)) || 
                    roadType === 'primary' || roadType === 'trunk') {
                    trafficLevel = "Heavy Traffic";
                    color = 'red';
                } else {
                    trafficLevel = "Moderate Traffic";
                    color = 'orange';
                }
                
                const polyline = L.polyline(points, { 
                    color: color, 
                    weight: 5,
                    opacity: 0.7
                }).bindPopup(`
                    <strong>${roadName || 'Main Road'}</strong><br>
                    ${trafficLevel}<br>
                    Updated: ${new Date().toLocaleTimeString()}
                `);
                
                trafficLayer.addLayer(polyline);
            }
        });
    })
    .catch(error => {
        console.error("Error fetching traffic data:", error);
        map.removeControl(loadingControl);
        useFallbackTrafficData();
    });
}

function useFallbackTrafficData() {
    console.warn("Using fallback traffic data");
    
    const fallbackTrafficAreas = [
        {
            points: [[47.162, 27.58], [47.160, 27.61]],
            name: "Șoseaua Păcurari",
            trafficLevel: "Heavy Traffic"
        },
        {
            points: [[47.155, 27.62], [47.153, 27.6]],
            name: "Tudor Vladimirescu",
            trafficLevel: "Heavy Traffic"
        },
        {
            points: [[47.167, 27.59], [47.164, 27.61]],
            name: "Bulevardul Independenței",
            trafficLevel: "Heavy Traffic"
        },
        {
            points: [[47.165, 27.57], [47.158, 27.56]],
            name: "Calea Chișinăului",
            trafficLevel: "Moderate Traffic"
        },
        {
            points: [[47.152, 27.58], [47.150, 27.61]],
            name: "Bulevardul Socola",
            trafficLevel: "Moderate Traffic"
        }
    ];
    
    fallbackTrafficAreas.forEach(area => {
        let color = area.trafficLevel === "Heavy Traffic" ? "red" : "orange";
        
        L.polyline(area.points, { 
            color: color, 
            weight: 5,
            opacity: 0.7
        }).bindPopup(`
            <strong>${area.name}</strong><br>
            ${area.trafficLevel}<br>
            Updated: ${new Date().toLocaleTimeString()} (static data)
        `).addTo(trafficLayer);
    });
}

function fetchPollutionData() {
    pollutionLayer.clearLayers();
    
    console.log('Loading pollution data');
    
    const industrialSources = [
        { lat: 47.164, lng: 27.59, radius: 300, name: "CUG Industrial Zone", type: "Industrial Pollution", level: "High" },
        { lat: 47.1695, lng: 27.5698, radius: 350, name: "Țuțora Industrial Zone", type: "Industrial Pollution", level: "High" },
        { lat: 47.1580, lng: 27.6390, radius: 280, name: "Metallurgical Plant", type: "Industrial Pollution", level: "Medium" },
        { lat: 47.1790, lng: 27.5590, radius: 250, name: "Furniture Factory", type: "Industrial Pollution", level: "Medium" }
    ];
    
    const trafficSources = [
        { lat: 47.1655, lng: 27.5900, radius: 200, name: "Independence Intersection", type: "Traffic Pollution", level: "High" },
        { lat: 47.1565, lng: 27.5880, radius: 180, name: "Union Square", type: "Traffic Pollution", level: "High" },
        { lat: 47.1730, lng: 27.5720, radius: 150, name: "Copou Intersection", type: "Traffic Pollution", level: "Medium" },
        { lat: 47.1520, lng: 27.6020, radius: 170, name: "Tudor Vladimirescu Intersection", type: "Traffic Pollution", level: "High" },
        { lat: 47.1620, lng: 27.5610, radius: 160, name: "Dacia Intersection", type: "Traffic Pollution", level: "Medium" }
    ];
    
    const constructionSources = [
        { lat: 47.1610, lng: 27.5780, radius: 120, name: "Palas Construction Site", type: "Construction Pollution", level: "Temporary" },
        { lat: 47.1540, lng: 27.5940, radius: 100, name: "Residential Construction Site", type: "Construction Pollution", level: "Temporary" }
    ];
    
    industrialSources.forEach(source => {
        L.circle([source.lat, source.lng], { 
            radius: source.radius, 
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.3,
            weight: 2
        }).bindPopup(`
            <strong>${source.name}</strong><br>
            Type: ${source.type}<br>
            Level: ${source.level}<br>
            <small>Updated: ${new Date().toLocaleTimeString()}</small>
        `).addTo(pollutionLayer);
    });
    
    trafficSources.forEach(source => {
        L.circle([source.lat, source.lng], { 
            radius: source.radius, 
            color: 'orange',
            fillColor: '#ff8c00',
            fillOpacity: 0.3,
            weight: 2
        }).bindPopup(`
            <strong>${source.name}</strong><br>
            Type: ${source.type}<br>
            Level: ${source.level}<br>
            <small>Updated: ${new Date().toLocaleTimeString()}</small>
        `).addTo(pollutionLayer);
    });
    
    constructionSources.forEach(source => {
        L.circle([source.lat, source.lng], { 
            radius: source.radius, 
            color: 'purple',
            fillColor: '#9932cc',
            fillOpacity: 0.3,
            weight: 2
        }).bindPopup(`
            <strong>${source.name}</strong><br>
            Type: ${source.type}<br>
            Level: ${source.level}<br>
            <small>Updated: ${new Date().toLocaleTimeString()}</small>
        `).addTo(pollutionLayer);
    });
}

function fetchParkingSpots() {
    const overpassUrl = "https://overpass-api.de/api/interpreter";
    const bbox = "47.10,27.50,47.20,27.70";
    
    const query = `
    [out:json];
    (
      node["amenity"="parking"](${bbox});
      way["amenity"="parking"](${bbox});
      relation["amenity"="parking"](${bbox});
    );
    out center;
    `;
    
    fetch(overpassUrl, {
        method: "POST",
        body: `data=${encodeURIComponent(query)}`
    })
    .then(response => response.json())
    .then(data => {
        parkingLayer.clearLayers();
        
        data.elements.forEach(element => {
            let lat, lng, name, capacity;
            
            if (element.type === "node") {
                lat = element.lat;
                lng = element.lon;
                name = element.tags.name || "Parking";
                capacity = element.tags.capacity || "Unknown";
            } else {
                lat = element.center.lat;
                lng = element.center.lon;
                name = element.tags.name || "Parking";
                capacity = element.tags.capacity || "Unknown";
            }
            
            const parkingIcon = L.divIcon({
                className: 'custom-parking-icon',
                html: `
                    <div style="
                        background-color: #4CAF50; 
                        color: white;
                        width: 25px; 
                        height: 25px; 
                        border-radius: 50%; 
                        border: 2px solid white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 16px;
                        box-shadow: 0 1px 5px rgba(0,0,0,0.4);
                    ">
                        P
                    </div>
                `,
                iconSize: [25, 25],
                iconAnchor: [12, 12],
                popupAnchor: [0, -10]
            });
            
            const marker = L.marker([lat, lng], { icon: parkingIcon }).bindPopup(`
                <strong>${name}</strong><br>
                Capacity: ${capacity}<br>
                ${element.tags.fee === "yes" ? "Paid" : "Free"}
            `);
            parkingLayer.addLayer(marker);
        });
    })
    .catch(error => {
        console.error("Error fetching parking data:", error);
        const fallbackParkings = [
            { lat: 47.161, lng: 27.587, name: "Palas Mall Parking", capacity: "1000", fee: "yes" },
            { lat: 47.174, lng: 27.571, name: "Copou Parking", capacity: "50", fee: "yes" },
            { lat: 47.158, lng: 27.604, name: "Iulius Mall Parking", capacity: "800", fee: "yes" },
            { lat: 47.153, lng: 27.595, name: "Palace Street Parking", capacity: "30", fee: "yes" },
            { lat: 47.166, lng: 27.584, name: "Metropolitan Parking", capacity: "40", fee: "yes" }
        ];
        
        const parkingIcon = L.divIcon({
            className: 'custom-parking-icon',
            html: `
                <div style="
                    background-color: #4CAF50; 
                    color: white;
                    width: 25px; 
                    height: 25px; 
                    border-radius: 50%; 
                    border: 2px solid white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 16px;
                    box-shadow: 0 1px 5px rgba(0,0,0,0.4);
                ">
                    P
                </div>
            `,
            iconSize: [25, 25],
            iconAnchor: [12, 12],
            popupAnchor: [0, -10]
        });
        
        fallbackParkings.forEach(parking => {
            L.marker([parking.lat, parking.lng], { icon: parkingIcon })
                .bindPopup(`
                    <strong>${parking.name}</strong><br>
                    Capacity: ${parking.capacity}<br>
                    ${parking.fee === "yes" ? "Paid" : "Free"}
                `)
                .addTo(parkingLayer);
        });
    });
}

function loadAllMapData() {
    fetchShops();
    fetchTrafficData();
    fetchPollutionData();
    fetchParkingSpots();
    addMapLegends();
}

function addMapLegends() {
    const trafficLegend = L.control({position: 'bottomleft'});
    trafficLegend.onAdd = function() {
        const div = L.DomUtil.create('div', 'info legend');
        div.style.backgroundColor = 'white';
        div.style.padding = '10px';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';
        div.style.marginBottom = '10px';
        
        div.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 5px;">Traffic Legend</div>
            <div style="display: flex; align-items: center; margin-bottom: 3px;">
                <div style="background-color: red; width: 20px; height: 4px; margin-right: 5px;"></div>
                Heavy Traffic
            </div>
            <div style="display: flex; align-items: center;">
                <div style="background-color: orange; width: 20px; height: 4px; margin-right: 5px;"></div>
                Moderate Traffic
            </div>
        `;
        return div;
    };
    
    const pollutionLegend = L.control({position: 'bottomleft'});
    pollutionLegend.onAdd = function() {
        const div = L.DomUtil.create('div', 'info legend');
        div.style.backgroundColor = 'white';
        div.style.padding = '10px';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';
        div.style.marginBottom = '10px';
        div.style.marginLeft = '10px';
        
        div.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 5px;">Pollution Legend</div>
            <div style="display: flex; align-items: center; margin-bottom: 3px;">
                <div style="background-color: red; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px;"></div>
                Industrial pollution
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 3px;">
                <div style="background-color: orange; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px;"></div>
                Traffic pollution
            </div>
            <div style="display: flex; align-items: center;">
                <div style="background-color: purple; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px;"></div>
                Construction pollution
            </div>
        `;
        return div;
    };
    
    document.getElementById("layer-traffic")?.addEventListener("change", (e) => {
        if (e.target.checked) {
            trafficLayer.addTo(map);
            trafficLegend.addTo(map);
        } else {
            map.removeLayer(trafficLayer);
            map.removeControl(trafficLegend);
        }
    });

    document.getElementById("layer-pollution")?.addEventListener("change", (e) => {
        if (e.target.checked) {
            pollutionLayer.addTo(map);
            pollutionLegend.addTo(map);
        } else {
            map.removeLayer(pollutionLayer);
            map.removeControl(pollutionLegend);
        }
    });
    
    document.getElementById("layer-shops")?.addEventListener("change", (e) => {
        e.target.checked ? shopsLayer.addTo(map) : map.removeLayer(shopsLayer);
    });

    document.getElementById("layer-parking")?.addEventListener("change", (e) => {
        e.target.checked ? parkingLayer.addTo(map) : map.removeLayer(parkingLayer);
    });
}

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

loadAllMapData();
loadAndDisplayProperties();
