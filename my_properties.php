<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';

// Porneste sesiunea
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica autentificarea
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Extrage ID-ul utilizatorului
$userId = $_SESSION['user_id'];
// Obtine lista proprietatilor utilizatorului
$properties = getUserProperties($userId);

// Pregateste mesajul flash
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
    <title>My Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<!-- Plus button for small screens -->
<a href="add_property.php" class="btn-primary btn-add-property btn-plus">+</a>

<div class="main-content">
    <header class="top-bar">
        <h1>My Properties</h1>
        <div>
            <a href="add_property.php" class="btn-primary btn-add-property">Add New Property</a>
        </div>
    </header>

    <!-- Afiseaza mesajul flash daca exista -->
    <?php if ($flashMessage): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($flashMessage) ?>
        </div>
    <?php endif; ?>

    <section class="properties-list">
        <?php if (empty($properties)): ?>
            <!-- Afiseaza mesaj cand nu exista proprietati -->
            <div class="no-data">
                <p>You don't have any properties yet.</p>
                <a href="add_property.php" class="btn-primary" style="width: auto; display: inline-block; max-width: none;">Add your first property</a>
            </div>
        <?php else: ?>
            <!-- Afiseaza lista de proprietati -->
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <!-- Card pentru fiecare proprietate -->
                    <div class="property-card">
                        <h3><?= htmlspecialchars($property['title']) ?></h3>
                        <p class="property-price">€<?= number_format($property['price']) ?></p>
                        <p class="property-area"><strong>Area:</strong> <?= $property['area'] ?> m²</p>
                        <p class="property-status"><?= $property['status'] == 'for_sale' ? 'For Sale' : 'For Rent' ?></p>
                        
                        <!-- Butoane de actiune -->
                        <div class="property-actions">
                            <a href="property_details.php?id=<?= $property['id'] ?>" class="btn-link">View Details</a>
                            <a href="remove_property.php?id=<?= $property['id'] ?>" class="btn-danger">Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html> 