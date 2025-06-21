<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
require_once __DIR__ . '/../../src/controllers/PropertyController.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();

$properties = PropertyController::getAllWithOwners();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Properties</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto; width: 100%;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Price</th>
                    <th>Area</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                <tr>
                    <td><?= htmlspecialchars(substr($property['title'], 0, 30)) ?><?= strlen($property['title']) > 30 ? '...' : '' ?></td>
                    <td><?= htmlspecialchars($property['owner_name']) ?></td>
                    <td>€<?= number_format($property['price']) ?></td>
                    <td><?= $property['area'] ?> m²</td>
                    <td>
                        <span class="status-badge <?= $property['status'] === 'for_sale' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst(str_replace('_', ' ', $property['status'])) ?>
                        </span>
                    </td>                    <td>
                        <a href="remove_property.php?id=<?= $property['id'] ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Are you sure you want to delete this property?');">
                            Delete
                        </a>
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



