<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/controllers/PropertyController.php';
require_once __DIR__ . '/../../src/utils/UIHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    die("Missing property ID.");
}

$id = (int) $_GET['id'];
$data = PropertyController::getPropertyWithStats($id);

if (!$data || !isset($data['details'])) {
    die("Property not found.");
}

$details = $data['details'];
$stats = $data['stats'];
$lat = $details['lat'];
$lng = $details['lng'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($details['title']) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1><?= htmlspecialchars($details['title']) ?></h1>
</header>

<section class="property-details">    <p><strong>Price:</strong> <?= UIHelper::formatPrice($details['price']) ?><?= $details['status'] === 'for_rent' ? ' / month' : '' ?></p>
    <p><strong>Area:</strong> <?= $details['area'] ?> m²</p>
    <p><strong>Status:</strong> <?= UIHelper::formatPropertyStatus($details['status']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($details['description'])) ?></p>
    <p><strong>Posted at:</strong> <?= UIHelper::formatDate($details['posted_at'], 'd-m-Y H:i') ?></p>    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $isSaved = PropertyController::isPropertySavedByUser($id, $_SESSION['user_id']);
        ?>
        <button id="favoriteBtn" class="favorite-heart <?= $isSaved ? 'saved' : '' ?>" aria-label="Toggle favorite">
            ♥
        </button>
        <span id="favoriteMsg"></span>

        <?php if ($_SESSION['user_id'] !== $details['user_id']): ?>
            <a href="chat.php?property=<?= $details['id'] ?>&with=<?= $details['user_id'] ?>" class="btn-link">Contact landlord</a>
        <?php endif; ?>
    <?php endif; ?>

    <canvas id="priceChart" width="400" height="200" class="<?= $stats['total_properties'] > 0 ? '' : 'blurred' ?>"></canvas>

    <?php if ($stats['total_properties'] > 0): ?>
        <script>
            const ctx = document.getElementById('priceChart').getContext('2d');
            const labelUnit = "<?= $details['status'] === 'for_rent' ? '€/month' : '€' ?>";

            const priceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Your Listing', 'Avg Nearby', 'Min Nearby', 'Max Nearby'],
                    datasets: [{
                        label: 'Price Comparison',
                        data: [
                            <?= $details['price'] ?>,
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
        $price = $details['price'];
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
    <?php else: ?>
        <p style="margin-top:1rem; font-style:italic;">📌 There are no nearby properties available for chart comparison.</p>    <?php endif; ?>
</section>

<section id="propertyDetails"></section>
<script>
async function loadProperty() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const res = await fetch('../../src/api/property.php?id=' + id);
    const property = await res.json();
}
loadProperty();
</script>
<script>
const favoriteBtn = document.getElementById('favoriteBtn');
const favoriteMsg = document.getElementById('favoriteMsg');
let isSaved = <?= $isSaved ? 'true' : 'false' ?>;
const propertyId = <?= (int)$id ?>;

favoriteBtn.onclick = async function(e) {
    e.preventDefault();
    const action = isSaved ? 'remove' : 'add';
    const res = await fetch('../../src/api/favorites.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ property_id: propertyId, action })
    });
    const result = await res.json();
    if (result.success) {
        isSaved = !isSaved;
        favoriteBtn.classList.toggle('saved', isSaved);
        favoriteMsg.textContent = isSaved ? 'Added to favorites!' : 'Removed from favorites!';
        setTimeout(() => favoriteMsg.textContent = '', 1500);
    } else {
        favoriteMsg.textContent = result.error || 'Error!';
    }
};
</script>

<?= UIHelper::generateFooter() ?>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



