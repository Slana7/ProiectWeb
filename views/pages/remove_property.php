<?php
require_once __DIR__ . '/../../src/config/config.php';
session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$propertyId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

if (!$propertyId) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Remove Property</h1>
    <?php if ($isAdmin): ?>
        <div class="admin-badge">
            <span>🛡️ Administrator</span>
        </div>
    <?php endif; ?>
</header>

<section class="form-section">
    <div id="api-message"></div>
    <div class="alert alert-warning" id="propertyInfo">
        <p>Loading property info...</p>
    </div>
    <form id="removePropertyForm" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Remove Property" class="btn-danger">
            <a href="<?= $isAdmin ? 'admin_properties.php' : 'my_properties.php' ?>" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
<script src="../../public/assets/js/responsive.js"></script>
<script>
const propertyId = <?= json_encode($propertyId) ?>;
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
const userId = <?= (int)$userId ?>;
const propertyInfoDiv = document.getElementById('propertyInfo');
const msgDiv = document.getElementById('api-message');

async function loadPropertyInfo() {
    const res = await fetch(`../../src/api/property.php?id=${propertyId}`);
    const property = await res.json();
    if (!property || property.error) {
        propertyInfoDiv.innerHTML = '<p class="error">Property not found or you do not have permission to remove it.</p>';
        document.getElementById('removePropertyForm').style.display = 'none';
        return;
    }
    if (!isAdmin && property.user_id != userId) {
        propertyInfoDiv.innerHTML = '<p class="error">You do not have permission to remove this property.</p>';
        document.getElementById('removePropertyForm').style.display = 'none';
        return;
    }
    propertyInfoDiv.innerHTML = `
        <p>Are you sure you want to remove the property: <strong>${escapeHtml(property.title)}</strong>?</p>
        <p>This action cannot be undone.</p>
        ${isAdmin && property.user_id != userId ? '<p><em>Note: You are removing this property as an administrator.</em></p>' : ''}
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('removePropertyForm').onsubmit = async function(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to permanently remove this property?')) return;
    const res = await fetch(`../../src/api/property.php?id=${propertyId}`, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ is_admin: isAdmin })
    });
    const result = await res.json();
    if (result.success) {
        msgDiv.innerHTML = '<div class="alert success">Property removed successfully.</div>';
        setTimeout(() => window.location.href = isAdmin ? 'admin_properties.php' : 'my_properties.php', 1200);
    } else {
        msgDiv.innerHTML = '<div class="alert error">' + (result.error || 'Failed to remove property') + '</div>';
    }
};

loadPropertyInfo();
</script>
</body>
</html>


