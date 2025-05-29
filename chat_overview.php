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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Conversations</h1>
</header>

<?php if (empty($conversations)): ?>
    <p>You have no conversations yet.</p>
<?php else: ?>
    <ul>
        <?php foreach ($conversations as $conv):
            $userStmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
            $userStmt->execute(['id' => $conv['other_user_id']]);
            $user = $userStmt->fetch();

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

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
