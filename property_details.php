<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';

session_start();


if (!isset($_GET['id'])) {
    die("Missing property ID.");
}

$id = (int) $_GET['id'];
$conn = Database::connect();

$stmt = $conn->prepare("SELECT * FROM properties WHERE id = :id");
$stmt->execute(['id' => $id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Property not found.");
}

// Extragem coordonatele proprietatii
$lat = $conn->query("SELECT ST_Y(location::geometry) AS lat FROM properties WHERE id = $id")->fetchColumn();
$lng = $conn->query("SELECT ST_X(location::geometry) AS lng FROM properties WHERE id = $id")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1><?= htmlspecialchars($property['title']) ?></h1>
</header>

<section class="property-details">
    <p><strong>Price:</strong> €<?= number_format($property['price']) ?></p>
    <p><strong>Area:</strong> <?= $property['area'] ?> m²</p>
    <p><strong>Status:</strong> <?= htmlspecialchars($property['status']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($property['description'])) ?></p>
    <p><strong>Posted at:</strong> <?= $property['posted_at'] ?></p>
    <p><strong>Location:</strong> <?= $lat ?>, <?= $lng ?></p>

    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
    $check = $conn->prepare("SELECT 1 FROM saved_properties WHERE user_id = :uid AND property_id = :pid");
    $check->execute(['uid' => $_SESSION['user_id'], 'pid' => $id]);
    $isSaved = $check->fetch();
    ?>

    <form method="post" action="src/controllers/FavoriteController.php">
    <input type="hidden" name="property_id" value="<?= $id ?>">
    <button type="submit" name="action" value="<?= $isSaved ? 'Unsave' : 'Save' ?>"
            class="favorite-heart <?= $isSaved ? 'saved' : '' ?>">
        ♥
    </button>
</form>

<?php if ($_SESSION['user_id'] !== $property['user_id']): ?>
    <a href="chat.php?property=<?= $property['id'] ?>&with=<?= $property['user_id'] ?>" class="btn-link">Contact landlord</a>
<?php endif; ?>

<?php endif; ?>

</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
