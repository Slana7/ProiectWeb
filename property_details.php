<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';

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

// extragem lat/lng dacă vrem să le afișăm
$lat = $conn->query("SELECT ST_Y(location::geometry) AS lat FROM properties WHERE id = $id")->fetchColumn();
$lng = $conn->query("SELECT ST_X(location::geometry) AS lng FROM properties WHERE id = $id")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($property['title']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo"><h2>REM</h2></div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
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
        </section>

        <footer class="dashboard-footer">
            &copy; <?= date('Y') ?> REM Project. All rights reserved.
        </footer>
    </main>
</div>
</body>
</html>
