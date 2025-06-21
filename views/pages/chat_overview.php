<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/controllers/MessageController.php';
require_once __DIR__ . '/../../src/models/Message.php';

// Backend logic using existing controllers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Use Message model for now since MessageController doesn't have this method yet
$conversations = Message::getConversationsWithLastMessage($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Conversations</h1>
</header>

<?php if (empty($conversations)): ?>
    <div class="chat-list">
        <p>You have no conversations yet. Start chatting by contacting property owners from the property details pages.</p>
    </div>
<?php else: ?>
    <div class="chat-list">
    <?php foreach ($conversations as $conv): ?>
        <div class="chat-card">
            <a href="chat.php?with=<?= $conv['other_user_id'] ?>&property=<?= $conv['property_id'] ?>">
                <h3>
                    <?= htmlspecialchars($conv['property_title']) ?>
                    <?php if (!empty($conv['has_unread'])): ?>
                        <span class="unread-badge">New</span>
                    <?php endif; ?>
                </h3>
                <p><strong>with <?= htmlspecialchars($conv['user_name']) ?></strong></p>
                <small>Last message: <?= date('Y-m-d H:i', strtotime($conv['last_message_time'])) ?></small>
            </a>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



