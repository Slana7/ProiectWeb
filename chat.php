<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/models/Message.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$propertyId = $_GET['property'] ?? null;
$receiverId = $_GET['with'] ?? null;

if (!$propertyId || !$receiverId) {
    header("Location: chat_overview.php");
    exit;
}
Message::markMessagesAsRead($receiverId, $userId, $propertyId);
$messages = Message::getConversationWithUsernames($userId, $receiverId, $propertyId);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chat - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Chat</h1>
    <div class="chat-header-actions">
        <a href="chat_overview.php" class="btn-secondary">
            ← Back to Conversations
        </a>
    </div>
</header>

<section class="chat-window">
    <div class="messages">
        <?php foreach ($messages as $msg): ?>
            <?php
                $isMine = $msg['sender_id'] == $userId;
                $formattedTime = date('H:i', strtotime($msg['sent_at']));
            ?>
            <div class="message-wrapper <?= $isMine ? 'mine' : 'theirs' ?>">
                <div class="message-card <?= $msg['is_flagged'] ? 'flagged' : '' ?>">
                    <?php if ($msg['is_flagged']): ?>
                        <div class="flag-label">⚠️ Important</div>
                    <?php endif; ?>
                    <div class="sender-name"><?= htmlspecialchars($msg['sender_name']) ?></div>
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

    <form class="chat-form" action="src/controllers/MessageController.php?action=send" method="post" enctype="multipart/form-data">
        <input type="hidden" name="property_id" value="<?= $propertyId ?>">
        <input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
        <textarea name="content" placeholder="Write a message..." required></textarea>
        <input type="file" name="attachment">
        <button type="submit" class="btn-primary">Send</button>
    </form>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const messagesContainer = document.querySelector(".messages");
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
</script>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
