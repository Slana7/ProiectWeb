<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Properties</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto; width: 100%;">
        <table class="admin-table" id="propertiesTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Price</th>
                    <th>Area</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="propertiesTbody">
                <!-- Proprietățile vor fi încărcate dinamic -->
            </tbody>
        </table>
    </div>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
async function loadProperties() {
    const res = await fetch('../../src/api/admin.php?action=all_properties');
    const properties = await res.json();
    const tbody = document.getElementById('propertiesTbody');
    tbody.innerHTML = '';
    if (Array.isArray(properties)) {
        properties.forEach(property => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(property.title.length > 30 ? property.title.substring(0, 30) + '...' : property.title)}</td>
                <td>${escapeHtml(property.owner_name)}</td>
                <td>€${Number(property.price).toLocaleString()}</td>
                <td>${property.area} m²</td>
                <td>
                    <span class="status-badge ${property.status === 'for_sale' ? 'status-active' : 'status-inactive'}">
                        ${capitalize(property.status.replace('_', ' '))}
                    </span>
                </td>
                <td>
                    <button class="btn btn-small btn-danger" onclick="deleteProperty(${property.id})">
                        Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="6">No properties found or error loading data.</td></tr>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

async function deleteProperty(id) {
    if (!confirm('Are you sure you want to delete this property?')) return;
    const res = await fetch(`../../src/api/property.php?id=${id}`, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ is_admin: true })
    });
    const result = await res.json();
    if (result.success) {
        alert('Property deleted!');
        loadProperties();
    } else {
        alert(result.error || 'Failed to delete property');
    }
}

loadProperties();
</script>
</body>
</html>



