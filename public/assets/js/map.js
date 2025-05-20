const map = L.map('map').setView([47.1585, 27.6014], 12); // Centrat pe Iași

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Încarcă proprietățile din API
fetch('public/api/get_property.php')
  .then(res => res.json())
  .then(data => {
      const list = document.getElementById('property-list');
      list.innerHTML = '';

      data.forEach(property => {
          // Adaugă marker pe hartă
          const marker = L.marker([property.lat, property.lng]).addTo(map);
          marker.bindPopup(`
              <b>${property.title}</b><br>
              €${property.price}<br>
              <a href="property_details.php?id=${property.id}">View Details</a>
          `);

          // Adaugă element în listă cu link
          const li = document.createElement('li');
          const link = document.createElement('a');
          link.href = `property_details.php?id=${property.id}`;
          link.textContent = `${property.title} (€${property.price})`;
          li.appendChild(link);
          list.appendChild(li);
      });
  })
  .catch(error => {
      console.error('Error loading properties:', error);
  });
