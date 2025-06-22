<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/UIHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$flashMessage = null;
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .badge {
            display: inline-block;
            background-color: #eee;
            color: #333;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            margin: 3px 3px 0 0;
        }
    </style>
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Properties</h1>
    <div>
        <a href="add_property.php" class="btn-primary btn-add-property">Add New Property</a>
    </div>
</header>

<?php if ($flashMessage): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($flashMessage) ?>
    </div>
<?php endif; ?>

<section class="properties-list" id="propertiesList">
    <div class="loading">Loading properties...</div>
</section>

<?= UIHelper::generateFooter() ?>
<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
async function loadProperties() {
    const section = document.getElementById('propertiesList');
    try {
        const res = await fetch('../../src/api/properties.php?scope=user');
        const properties = await res.json();

        if (!Array.isArray(properties) || properties.length === 0) {
            section.innerHTML = `
                <div class="no-data">
                    <p>You don't have any properties yet.</p>
                    <a href="add_property.php" class="btn-primary">Add your first property</a>
                </div>
            `;
            return;
        }

        section.innerHTML = '<div class="property-grid"></div>';
        const grid = section.querySelector('.property-grid');

        properties.forEach(property => {
            const facilities = property.facilities ? property.facilities.split(',') : [];
            const facilityHTML = facilities.map(f => `<span class="badge">${f.trim()}</span>`).join('');

            const card = document.createElement('div');
            card.className = 'property-card';
            card.innerHTML = `
                <h3>${property.title}</h3>
                <p>${property.description ?? 'No description available'}</p>
                <p class="property-price">€${property.price}</p>
                <p class="property-area"><strong>Area:</strong> ${property.area} m²</p>
                ${facilityHTML ? `<div class="facilities">${facilityHTML}</div>` : ''}
                <p class="property-status"><strong>Status:</strong> ${property.status.replace('_', ' ')}</p>
                <div class="property-actions">
                    <a href="property_details.php?id=${property.id}" class="btn-link">View Details</a>
                    <a href="edit_property.php?id=${property.id}" class="btn-link">Edit</a>
                    <a href="remove_property.php?id=${property.id}" class="btn-danger">Remove</a>
                </div>
            `;
            grid.appendChild(card);
        });

    } catch (err) {
        console.error('Failed to load properties', err);
        section.innerHTML = '<p class="error">Failed to load properties.</p>';
    }
}

loadProperties();
</script>

</body>
</html>
