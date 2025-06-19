<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';
require_once __DIR__ . '/src/utils/AdminUtils.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$propertyId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

if (!$propertyId) {
    $_SESSION['flash_message'] = 'No property specified for removal';
    header('Location: dashboard.php');
    exit;
}

$property = getPropertyById($propertyId);

if (!$property) {
    $_SESSION['flash_message'] = 'Property not found';
    header('Location: dashboard.php');
    exit;
}

if (!$isAdmin && $property['user_id'] != $userId) {
    $_SESSION['flash_message'] = 'You do not have permission to remove this property';
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $result = removeProperty($propertyId, $userId, $isAdmin);
        
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
    <?php if ($isAdmin): ?>
        <div class="admin-badge">
            <span>🛡️ Administrator</span>
        </div>
    <?php endif; ?>
</header>

<section class="form-section">
    <div class="alert alert-warning">
        <p>Are you sure you want to remove the property: <strong><?= htmlspecialchars($property['title']) ?></strong>?</p>
        <p>This action cannot be undone.</p>
        <?php if ($isAdmin && $property['user_id'] != $userId): ?>
            <p><em>Note: You are removing this property as an administrator.</em></p>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $propertyId ?>" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Remove Property" class="btn-danger">
            <a href="<?= $isAdmin ? 'admin_properties.php' : 'dashboard.php' ?>" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>