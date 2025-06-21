<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/controllers/PropertyController.php';
require_once __DIR__ . '/../../src/utils/UIHelper.php';

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

$property = PropertyController::getPropertyById($propertyId);

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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Property - <?= APP_NAME ?></title>    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

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
    </div>    <form method="post" action="../../src/controllers/PropertyController.php?action=remove_property&id=<?= $propertyId ?>" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Remove Property" class="btn-danger">
            <a href="<?= $isAdmin ? 'admin_properties.php' : 'my_properties.php' ?>" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?= UIHelper::generateFooter() ?>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
<script src="../../public/assets/js/responsive.js"></script>
</body>
</html>


