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

// Fetch conversations
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

// Fetch unread messages
$unreadStmt = $conn->prepare("
    SELECT property_id, sender_id, COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_id = :uid AND is_read = false
    GROUP BY property_id, sender_id
");
$unreadStmt->execute(['uid' => $userId]);
$unread = $unreadStmt->fetchAll(PDO::FETCH_ASSOC);

$unreadMap = [];
foreach ($unread as $row) {
    $key = $row['property_id'] . '-' . $row['sender_id'];
    $unreadMap[$key] = $row['unread_count'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        .unread-badge {
            background-color: crimson;
            color: white;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Conversations</h1>
</header>

<?php if (empty($conversations)): ?>
    <p>You have no conversations yet.</p>
<?php else: ?>
    <div class="chat-list">
    <?php foreach ($conversations as $conv): 
        $userStmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
        $userStmt->execute(['id' => $conv['other_user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        $propStmt = $conn->prepare("SELECT title FROM properties WHERE id = :id");
        $propStmt->execute(['id' => $conv['property_id']]);
        $property = $propStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$property) continue;

        $key = $conv['property_id'] . '-' . $conv['other_user_id'];
        $hasUnread = isset($unreadMap[$key]);
    ?>
        <div class="chat-card">
            <a href="chat.php?with=<?= $conv['other_user_id'] ?>&property=<?= $conv['property_id'] ?>">
                <h3>
                    <?= htmlspecialchars($property['title']) ?>
                    <?php if ($hasUnread): ?><span class="unread-badge">New</span><?php endif; ?>
                </h3>
                <p><strong>with <?= htmlspecialchars($user['name']) ?></strong></p>
                <small>Last message: <?= date('Y-m-d H:i', strtotime($conv['last_message_time'])) ?></small>
            </a>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
