<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$propertyId = $_GET['property'] ?? null;
$receiverId = $_GET['with'] ?? null;

if (!$propertyId || !$receiverId) {
    die("Missing property or user.");
}

$conn = Database::connect();

// Obținem numele utilizatorilor
function getUsername($conn, $id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchColumn();
}

$myName = getUsername($conn, $userId);
$otherName = getUsername($conn, $receiverId);

// Fetch messages
$stmt = $conn->prepare("
    SELECT * FROM messages
    WHERE property_id = :property_id
      AND ((sender_id = :me AND receiver_id = :them) OR (sender_id = :them AND receiver_id = :me))
    ORDER BY sent_at ASC
");
$stmt->execute([
    'property_id' => $propertyId,
    'me' => $userId,
    'them' => $receiverId
]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat - <?= APP_NAME ?></title>
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
        <header class="top-bar"><h1>Chat</h1></header>

        <section class="chat-window">
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                    <?php
                        $isMine = $msg['sender_id'] == $userId;
                        $senderName = $isMine ? $myName : $otherName;
                        $formattedTime = date('H:i', strtotime($msg['sent_at']));
                    ?>
                    <div class="message-wrapper <?= $isMine ? 'mine' : 'theirs' ?>">
    <div class="message-card">
        <div class="sender-name"><?= htmlspecialchars($senderName) ?></div>
        <div class="message-content">
            <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
            <?php if ($msg['attachment']): ?>
                <a class="attachment-link" href="uploads/<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">📎 Attachment</a>
            <?php endif; ?>
        </div>
        <div class="timestamp"><?= $formattedTime ?></div>
    </div>
</div>

                <?php endforeach; ?>
            </div>

            <form class="chat-form" action="src/controllers/MessageController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="property_id" value="<?= $propertyId ?>">
                <input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
                <textarea name="content" placeholder="Write a message..." required></textarea>
                <input type="file" name="attachment">
                <button type="submit" class="btn-primary">Send</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
