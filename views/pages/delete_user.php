<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
require_once __DIR__ . '/../../src/controllers/AdminController.php';

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_users.php?error=invalid_id");
    exit;
}

$userId = (int) $_GET['id'];
$user = AdminController::getUserById($userId);

if (!$user) {
    header("Location: admin_users.php?error=user_not_found");
    exit;
}

if ($user['role'] === 'admin') {
    header("Location: admin_users.php?error=forbidden");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Delete User</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div class="alert alert-warning">
        <h3>⚠️ Delete User Confirmation</h3>
        <p>Are you sure you want to delete the user: <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)?</p>
        <p><strong>This action will:</strong></p>
        <ul>
            <li>Permanently delete the user account</li>
            <li>Remove all their properties</li>
            <li>Delete all their messages and conversations</li>
            <li>Remove them from all favorites</li>
        </ul>
        <p><strong>This action cannot be undone!</strong></p>
    </div>    <form method="post" action="../../src/controllers/AdminController.php?action=delete_user&id=<?= $userId ?>" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Delete User" class="btn-danger">
            <a href="admin_users.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>
