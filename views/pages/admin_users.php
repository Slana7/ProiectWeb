<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
require_once __DIR__ . '/../../src/controllers/ProfileController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();

$users = ProfileController::getAllUsersWithPropertyCount();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Users</h1>
            <?php if (isset($_GET['success']) && $_GET['success'] === 'user_deleted'): ?>
            <div class="alert success">User deleted successfully.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
            <div class="alert error">An error occurred while deleting the user.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_id'): ?>
            <div class="alert error">Invalid user ID.</div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
            <div class="alert error">You cannot delete an admin account.</div>
        <?php endif; ?>

    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto; width: 100%;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Properties</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="status-badge <?= $user['role'] === 'admin' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td><?= $user['property_count'] ?></td>                    <td>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <a href="delete_user.php?id=<?= $user['id'] ?>"
                               class="btn btn-small btn-danger">
                                Delete
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



