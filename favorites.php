<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$favorites = PropertyController::getFavoritesByUserId($userId);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Favorite Properties</h1>
</header>

<section class="property-list favorites-list">
    <?php if (empty($favorites)): ?>
        <p>You haven't saved any properties yet.</p>
    <?php else: ?>
        <div class="favorite-cards">
            <?php foreach ($favorites as $property): ?>
                <div class="favorite-card">
                    <h3><?= htmlspecialchars($property['title']) ?></h3>
                    <p class="price">€<?= number_format($property['price']) ?></p>
                    <a href="property_details.php?id=<?= $property['id'] ?>" class="btn-link">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
