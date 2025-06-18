<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/utils/AdminUtils.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();

$conn = Database::connect();

$stmt = $conn->query("
    SELECT u.id, u.name, u.email, u.role,
           COUNT(p.id) as property_count
    FROM users u
    LEFT JOIN properties p ON u.id = p.user_id
    GROUP BY u.id, u.name, u.email, u.role
    ORDER BY u.id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Users</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="status-badge <?= $user['role'] === 'admin' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td><?= $user['property_count'] ?></td>
                    <td>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <a href="#" class="btn btn-small btn-warning">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
