<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<div class="main-content">
    <header class="top-bar">
        <h1>Your Profile</h1>
    </header>

    <!-- Afiseaza mesajul flash daca exista -->
    <?php if ($flashMessage): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($flashMessage) ?>
        </div>
    <?php endif; ?>

    <section class="form-section">
        <!-- Afiseaza erori daca exista -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'email_taken'): ?>
            <p class="error">Email already in use. Please choose another.</p>
        <?php endif; ?>

        <!-- Formular actualizare profil -->
        <form method="post" action="<?= BASE_URL ?>src/controllers/ProfileController.php" class="property-form">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>New Password:</label>
            <input type="password" name="new_password" placeholder="Leave blank to keep current password">

            <input type="submit" value="Update Profile" class="btn-primary">
        </form>
    </section>

    <!-- Sectiune proprietati favorite -->
    <section class="favorites-section">
        <h2>My Favorite Properties</h2>
        <ul>
            <?php
            $favorites = $conn->prepare("
                SELECT p.id, p.title, p.price
                FROM saved_properties sp
                JOIN properties p ON sp.property_id = p.id
                WHERE sp.user_id = :uid
            ");
            $favorites->execute(['uid' => $userId]);
            $favs = $favorites->fetchAll();
            
            if (empty($favs)) {
                echo "<p>You haven't saved any properties yet.</p>";
            } else {
                foreach ($favs as $fav):
                ?>
                    <li>
                        <a href="property_details.php?id=<?= $fav['id'] ?>">
                            <?= htmlspecialchars($fav['title']) ?> (€<?= number_format($fav['price']) ?>)
                        </a>
                    </li>
                <?php endforeach;
            }
            ?>
        </ul>
    </section>
</div>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
