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
    <title>Admin Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Admin Dashboard</h1>
</header>

<div id="admin-stats">
    <section class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p class="stat-number" id="totalUsers">...</p>
            <p>Registered clients</p>
        </div>
        <div class="card">
            <h3>Total Properties</h3>
            <p class="stat-number" id="totalProperties">...</p>
            <p>Properties listed</p>
        </div>
        <div class="card">
            <h3>Manage Users</h3>
            <p>View and manage user accounts.</p>
            <a href="admin_users.php" class="btn-link">Manage Users</a>
        </div>
        <div class="card">
            <h3>Manage Properties</h3>
            <p>View and manage all properties.</p>
            <a href="admin_properties.php" class="btn-link">Manage Properties</a>
        </div>
    </section>
</div>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
async function loadAdminStats() {
    try {
        const res = await fetch('../../src/api/admin.php?action=stats');
        const stats = await res.json();
        document.getElementById('totalUsers').textContent = stats.total_users ?? '0';
        document.getElementById('totalProperties').textContent = stats.total_properties ?? '0';
    } catch (err) {
        document.getElementById('totalUsers').textContent = '!';
        document.getElementById('totalProperties').textContent = '!';
    }
}
loadAdminStats();
</script>
</body>
</html>



