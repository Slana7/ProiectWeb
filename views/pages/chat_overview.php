<?php
require_once __DIR__ . '/../../src/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Chats - <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .chat-card {
            background: #fff;
            padding: 15px 20px;
            margin: 15px auto;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        .chat-card a {
            color: inherit;
            text-decoration: none;
            display: block;
        }
        .unread-badge {
            background-color: crimson;
            color: white;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 10px;
            font-weight: bold;
        }
        .empty-state, .error-box {
            background: #ffe5e5;
            border: 1px solid #ffaaaa;
            padding: 30px;
            border-radius: 10px;
            margin: 40px auto;
            text-align: center;
            max-width: 600px;
        }
    </style>
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>My Conversations</h1>
</header>

<div class="chat-list" id="chatList">
    <p>Loading conversations...</p>
</div>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
async function loadConversations() {
    const chatList = document.getElementById('chatList');
    chatList.innerHTML = '<p>Loading conversations...</p>';

    try {
        const res = await fetch('../../src/api/messages.php?action=overview');
        const text = await res.text();
        console.log("API Response:", text);
        const conversations = JSON.parse(text);

        if (!Array.isArray(conversations) || conversations.length === 0) {
            chatList.innerHTML = `
                <div class="empty-state">
                    <strong>You have no conversations yet.</strong><br>
                    Start chatting by contacting a landlord from a property page.
                </div>`;
            return;
        }

        chatList.innerHTML = '';
        conversations.forEach(conv => {
            const card = document.createElement('div');
            card.className = 'chat-card';
            card.innerHTML = `
                <a href="chat.php?with=${conv.other_user_id}&property=${conv.property_id}">
                    <h3>
                        ${escapeHtml(conv.property_title)}
                        ${conv.has_unread ? '<span class="unread-badge">New</span>' : ''}
                    </h3>
                    <p><strong>with ${escapeHtml(conv.user_name)}</strong></p>
                    <small>Last message: ${conv.last_message_time ? escapeHtml(conv.last_message_time.replace('T', ' ').substring(0, 16)) : '-'}</small>
                </a>`;
            chatList.appendChild(card);
        });

    } catch (err) {
        console.error("Fetch error:", err);
        chatList.innerHTML = `
            <div class="error-box">
                <strong>❌ Failed to load conversations.</strong><br>
                Please try again later.
            </div>`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadConversations();
</script>

</body>
</html>
