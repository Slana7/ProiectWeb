<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = 'No property specified for removal';
    header('Location: dashboard.php');
    exit;
}

$propertyId = $_GET['id'];
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $result = removeProperty($propertyId, $userId);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
        } else {
            $_SESSION['flash_message'] = 'Error: ' . implode(', ', $result['errors']);
        }
    } else {
        $_SESSION['flash_message'] = 'Property removal was cancelled';
    }
    
    header('Location: dashboard.php');
    exit;
}

$property = getPropertyById($propertyId);

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