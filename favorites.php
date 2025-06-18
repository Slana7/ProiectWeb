<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT p.id, p.title, p.price
    FROM saved_properties sp
    JOIN properties p ON sp.property_id = p.id
    WHERE sp.user_id = :uid
");
$stmt->execute(['uid' => $userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
