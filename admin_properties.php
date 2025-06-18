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
    SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at,
           u.name as owner_name, u.email as owner_email
    FROM properties p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.posted_at DESC
");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Properties</h1>
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
                    <th>Title</th>
                    <th>Owner</th>
                    <th>Price</th>
                    <th>Area</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($properties as $property): ?>
                <tr>
                    <td><?= $property['id'] ?></td>
                    <td><?= htmlspecialchars(substr($property['title'], 0, 30)) ?><?= strlen($property['title']) > 30 ? '...' : '' ?></td>
                    <td><?= htmlspecialchars($property['owner_name']) ?></td>
                    <td>€<?= number_format($property['price']) ?></td>
                    <td><?= $property['area'] ?> m²</td>
                    <td>
                        <span class="status-badge <?= $property['status'] === 'for_sale' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst(str_replace('_', ' ', $property['status'])) ?>
                        </span>
                    </td>
                    <td><?= date('M j, Y', strtotime($property['posted_at'])) ?></td>
                    <td>
                        <a href="property_details.php?id=<?= $property['id'] ?>" class="btn btn-small btn-primary">View</a>
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
