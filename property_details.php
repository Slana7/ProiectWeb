<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_GET['id'])) {
    die("Missing property ID.");
}

$id = (int) $_GET['id'];
$conn = Database::connect();

// Fetch property
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = :id");
$stmt->execute(['id' => $id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Property not found.");
}

// Get coordinates from PostGIS geometry
$lat = $conn->query("SELECT ST_Y(location::geometry) FROM properties WHERE id = $id")->fetchColumn();
$lng = $conn->query("SELECT ST_X(location::geometry) FROM properties WHERE id = $id")->fetchColumn();

// Nearby stats with same status
$statsStmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_properties,
        ROUND(AVG(price), 2) AS avg_price,
        MIN(price) AS min_price,
        MAX(price) AS max_price
    FROM properties
    WHERE id != :id
      AND status = :status
      AND ST_DWithin(location, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)::geography, 2000)
");
$statsStmt->execute([
    'id' => $property['id'],
    'status' => $property['status'],
    'lat' => $lat,
    'lng' => $lng
]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($property['title']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1><?= htmlspecialchars($property['title']) ?></h1>
</header>

<section class="property-details">
    <p><strong>Price:</strong> €<?= number_format($property['price']) ?><?= $property['status'] === 'for_rent' ? ' / month' : '' ?></p>
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
            <button type="submit" name="action" value="<?= $isSaved ? 'Unsave' : 'Save' ?>" class="favorite-heart <?= $isSaved ? 'saved' : '' ?>">
                ♥
            </button>
        </form>

        <?php if ($_SESSION['user_id'] !== $property['user_id']): ?>
            <a href="chat.php?property=<?= $property['id'] ?>&with=<?= $property['user_id'] ?>" class="btn-link">Contact landlord</a>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($stats && $stats['total_properties'] > 0): ?>
        <div class="property-stats">
            <h3>📊 Market Stats in This Area</h3>
            <ul>
                <li>Other listings nearby: <?= $stats['total_properties'] ?></li>
                <li>Average price: €<?= $stats['avg_price'] ?><?= $property['status'] === 'for_rent' ? ' / month' : '' ?></li>
                <li>Minimum price: €<?= $stats['min_price'] ?><?= $property['status'] === 'for_rent' ? ' / month' : '' ?></li>
                <li>Maximum price: €<?= $stats['max_price'] ?><?= $property['status'] === 'for_rent' ? ' / month' : '' ?></li>
            </ul>
        </div>

        <canvas id="priceChart" width="400" height="200"></canvas>
        <script>
            const ctx = document.getElementById('priceChart').getContext('2d');
            const labelUnit = "<?= $property['status'] === 'for_rent' ? '€/month' : '€' ?>";

            const priceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Your Listing', 'Avg Nearby', 'Min Nearby', 'Max Nearby'],
                    datasets: [{
                        label: 'Price Comparison',
                        data: [
                            <?= $property['price'] ?>,
                            <?= $stats['avg_price'] ?? 0 ?>,
                            <?= $stats['min_price'] ?? 0 ?>,
                            <?= $stats['max_price'] ?? 0 ?>
                        ],
                        backgroundColor: ['#42a5f5', '#66bb6a', '#ffa726', '#ef5350'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${labelUnit} ${ctx.raw}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => `${labelUnit} ${value}`
                            }
                        }
                    }
                }
            });
        </script>

        <?php
        $price = $property['price'];
        $avg = $stats['avg_price'];
        if ($avg > 0) {
            $diff = round(($price - $avg) / $avg * 100);
            if ($diff > 0) {
                $class = "color-red";
                $message = "This property's price is <strong>$diff%</strong> above the area average.";
            } elseif ($diff < 0) {
                $class = "color-green";
                $message = "This property's price is <strong>" . abs($diff) . "%</strong> below the area average.";
            } else {
                $class = "";
                $message = "This property's price matches the area average.";
            }
            echo "<p class='$class' style='margin-top:1rem; font-style:italic;'>📌 $message</p>";
        }
        ?>
    <?php endif; ?>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
