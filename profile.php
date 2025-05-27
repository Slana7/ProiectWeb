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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo">
            <h2>Real Estate Management Application</h2>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="chat_overview.php">My Chats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1>Your Profile</h1>
        </header>

        <section class="form-section">

            <?php if (isset($_GET['error']) && $_GET['error'] === 'email_taken'): ?>
                <p class="error">Email already in use. Please choose another.</p>
            <?php endif; ?>

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
        foreach ($favorites as $fav):
        ?>
            <li>
                <a href="property_details.php?id=<?= $fav['id'] ?>">
                    <?= htmlspecialchars($fav['title']) ?> (€<?= number_format($fav['price']) ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="my-listings">
    <h2>My Listings</h2>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT id, title, price FROM properties WHERE user_id = :uid ORDER BY posted_at DESC");
        $stmt->execute(['uid' => $userId]);
        $myProperties = $stmt->fetchAll();

        if (empty($myProperties)) {
            echo "<p>You haven't added any properties yet.</p>";
        } else {
            foreach ($myProperties as $prop): ?>
                <li>
                    <a href="property_details.php?id=<?= $prop['id'] ?>">
                        <?= htmlspecialchars($prop['title']) ?> (€<?= number_format($prop['price']) ?>)
                    </a>
                </li>
            <?php endforeach;
        }
        ?>
    </ul>
</section>


        <footer class="dashboard-footer">
            &copy; <?= date('Y') ?> REM Project. All rights reserved.
        </footer>
    </main>
</div>
</body>
</html>
