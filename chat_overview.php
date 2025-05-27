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

// Afișăm toate conversațiile în care utilizatorul a fost implicat
$query = "
    SELECT 
        CASE 
            WHEN sender_id = :uid THEN receiver_id
            ELSE sender_id
        END AS other_user_id,
        property_id,
        MAX(sent_at) AS last_message_time
    FROM messages
    WHERE sender_id = :uid OR receiver_id = :uid
    GROUP BY other_user_id, property_id
    ORDER BY last_message_time DESC
";

$stmt = $conn->prepare($query);
$stmt->execute(['uid' => $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Chats - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="logo"><h2>REM</h2></div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="map.php">Map</a>
            <a href="add_property.php">Add Property</a>
            <a href="profile.php">Profile</a>
            <a href="chat_overview.php" class="active">My Chats</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h1>My Conversations</h1>
        </header>

        <?php if (empty($conversations)): ?>
            <p>You have no conversations yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($conversations as $conv):
                    // Obține numele celuilalt utilizator
                    $userStmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
                    $userStmt->execute(['id' => $conv['other_user_id']]);
                    $user = $userStmt->fetch();

                    // Obține titlul proprietății
                    $propStmt = $conn->prepare("SELECT title FROM properties WHERE id = :id");
                    $propStmt->execute(['id' => $conv['property_id']]);
                    $property = $propStmt->fetch();
                ?>
                    <li>
                        <a href="chat.php?with=<?= $conv['other_user_id'] ?>&property=<?= $conv['property_id'] ?>">
                            <?= htmlspecialchars($property['title']) ?> - with <?= htmlspecialchars($user['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
