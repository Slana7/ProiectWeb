<?php
require_once __DIR__ . '/src/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Dashboard</h1>
    <p>Welcome to your real estate management dashboard.</p>
</header>

<?php if ($flashMessage): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($flashMessage) ?>
    </div>
<?php endif; ?>

<section class="cards">
    <div class="card">
        <h3>Browse Properties</h3>
        <p>Explore available properties for sale or rent.</p>
        <a href="map.php" class="btn-link">View Map</a>
    </div>
    
    <div class="card">
        <h3>My Properties</h3>
        <p>Manage your listed properties.</p>
        <a href="my_properties.php" class="btn-link">View Properties</a>
    </div>
    
    <div class="card">
        <h3>Add Property</h3>
        <p>List your own property for sale or rent.</p>
        <a href="add_property.php" class="btn-link">Add New</a>
    </div>
    
    <div class="card">
        <h3>Your Profile</h3>
        <p>View and edit your profile details.</p>
        <a href="profile.php" class="btn-link">View Profile</a>
    </div>
    
    <div class="card">
        <h3>Messages</h3>
        <p>Check your messages with property owners or buyers.</p>
        <a href="chat_overview.php" class="btn-link">View Messages</a>
    </div>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
