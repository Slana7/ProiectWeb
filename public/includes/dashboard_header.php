<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/db/Database.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
// session_start();
$conn = Database::connect();

$hasUnread = false;

if (isset($_SESSION['user_id'])) {
    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT 1 FROM messages WHERE receiver_id = :uid AND is_read = FALSE LIMIT 1");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $hasUnread = $stmt->fetchColumn() !== false;
}
?>

<div class="dashboard-layout">
    <header class="mobile-header">
        <div class="header-container">
            <div class="logo-mobile">
                <a href="dashboard.php">REM</a>
            </div>            <div class="burger-menu">
                <div class="burger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <nav class="nav-menu">
                <?php if (isAdmin()): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                    <a href="admin_users.php">Users</a>
                    <a href="admin_properties.php">Properties</a>
                    <a href="admin_messages.php">Messages</a>
                <?php else: ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="map.php">Map</a>
                    <a href="favorites.php">Favorites</a>
                    <a href="my_properties.php">My Properties</a>
                    <a href="add_property.php">Add Property</a>
                    <a href="profile.php">Profile</a>
                    <a href="chat_overview.php" class="nav-link"> My Chats
                        <?php if (!empty($hasUnread)): ?>
                        <span class="unread-dot"></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <aside class="sidebar">
        <div class="logo">
            <h2>Real Estate Management</h2>
        </div>        <nav class="nav-links">
            <?php if (isAdmin()): ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
                <a href="admin_users.php">Users</a>
                <a href="admin_properties.php">Properties</a>
                <a href="admin_messages.php">Messages</a>
            <?php else: ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="map.php">Map</a>
                <a href="favorites.php">Favorites</a>
                <a href="my_properties.php">My Properties</a>
                <a href="add_property.php">Add Property</a>
                <a href="profile.php">Profile</a>
                <a href="chat_overview.php" class="nav-link"> My Chats
                    <?php if (!empty($hasUnread)): ?>
                    <span class="unread-dot"></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content"> 