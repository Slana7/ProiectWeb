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

function getUsername($conn, $id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchColumn();
}

$myName = getUsername($conn, $userId);
$otherName = getUsername($conn, $receiverId);

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar"><h1>Chat</h1> <div style="margin: 1rem 0;">
    <a href="mark_read_and_redirect.php?with=<?= $receiverId ?>&property=<?= $propertyId ?>" class="btn-secondary">
        ← Back to Conversations
    </a>
</div>
</header>

<section class="chat-window">
    <div class="messages">
        <?php foreach ($messages as $msg): ?>
            <?php
                $isMine = $msg['sender_id'] == $userId;
                $senderName = $isMine ? $myName : $otherName;
                $formattedTime = date('H:i', strtotime($msg['sent_at']));
            ?>
            <div class="message-wrapper <?= $isMine ? 'mine' : 'theirs' ?>">
                <div class="message-card <?= $msg['is_flagged'] ? 'flagged' : '' ?>">
                    <?php if ($msg['is_flagged']): ?>
                        <div class="flag-label">⚠️ Important</div>
                    <?php endif; ?>
                    <div class="sender-name"><?= htmlspecialchars($senderName) ?></div>
                    <div class="message-content">
                        <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                        <?php if ($msg['attachment']): ?>
                            <?php
                                $ext = strtolower(pathinfo($msg['attachment'], PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            ?>
                            <?php if ($isImage): ?>
                                <img src="uploads/<?= htmlspecialchars($msg['attachment']) ?>" alt="Attachment" class="chat-image">
                            <?php else: ?>
                                <a class="attachment-link" href="uploads/<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">📎 Attachment</a>
                            <?php endif; ?>
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

<?php include_once 'public/includes/dashboard_footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const messagesContainer = document.querySelector(".messages");
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
</script>

</body>
</html>
