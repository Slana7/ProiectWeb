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

// Verifica existenta ID-ului proprietatii
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = 'No property specified for removal';
    header('Location: dashboard.php');
    exit;
}

// Extrage ID-ul proprietatii si al utilizatorului
$propertyId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Proceseaza confirmarea stergerii
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica confirmarea utilizatorului
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Sterge proprietatea
        $result = removeProperty($propertyId, $userId);
        
        // Pregateste mesajul de feedback
        if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
        } else {
            $_SESSION['flash_message'] = 'Error: ' . implode(', ', $result['errors']);
        }
    } else {
        $_SESSION['flash_message'] = 'Property removal was cancelled';
    }
    
    // Redirectioneaza la dashboard
    header('Location: dashboard.php');
    exit;
}

// Obtine detaliile proprietatii
$property = getPropertyById($propertyId);

// Verifica drepturile de acces
if (!$property || $property['user_id'] != $userId) {
    $_SESSION['flash_message'] = 'You do not have permission to remove this property';
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Property - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Remove Property</h1>
</header>

<section class="form-section">
    <!-- Afiseaza alerta de confirmare -->
    <div class="alert alert-warning">
        <p>Are you sure you want to remove the property: <strong><?= htmlspecialchars($property['title']) ?></strong>?</p>
        <p>This action cannot be undone.</p>
    </div>

    <!-- Formular de confirmare stergere -->
    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $propertyId ?>" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Remove Property" class="btn-danger">
            <a href="dashboard.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html> 