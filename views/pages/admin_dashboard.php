<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
require_once __DIR__ . '/../../src/controllers/AdminController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();

$totalUsers = AdminController::getTotalUsers();
$totalProperties = AdminController::getTotalProperties();

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
    <title>Admin Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Admin Dashboard</h1>
</header>

<?php if ($flashMessage): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($flashMessage) ?>
    </div>
<?php endif; ?>

<section class="cards">
    <div class="card">
        <h3>Total Users</h3>
        <p class="stat-number"><?= $totalUsers ?></p>
        <p>Registered clients</p>
    </div>

    <div class="card">
        <h3>Total Properties</h3>
        <p class="stat-number"><?= $totalProperties ?></p>
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
    

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



