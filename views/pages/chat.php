<?php
require_once __DIR__ . '/../../src/config/config.php';
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Chat - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ef5350;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Chat</h1>
    <div class="chat-header-actions">
        <a href="chat_overview.php" class="btn-secondary">
            ← Back to Conversations
        </a>
    </div>
</header>

<section class="chat-window">
    <div class="messages" id="messages"></div>

    <form class="chat-form" id="chatForm" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="property_id" value="<?= htmlspecialchars($propertyId) ?>">
        <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiverId) ?>">
        <textarea name="content" placeholder="Write a message..." required></textarea>
        <input type="file" name="attachment">
        <button type="submit" class="btn-primary">Send</button>
    </form>
</section>

<script>
const userId = <?= (int)$userId ?>;
const propertyId = <?= (int)$propertyId ?>;
const receiverId = <?= (int)$receiverId ?>;
const messagesDiv = document.getElementById('messages');

async function loadMessages(scrollToBottom = true) {
    try {
        const res = await fetch(`../../src/api/messages.php?property=${propertyId}&with=${receiverId}`);
        const text = await res.text();
        console.log("Raw API response:", text);
        
        let messages;
        try {
            messages = JSON.parse(text);
        } catch (parseError) {
            console.error("JSON parse error:", parseError);
            console.error("Response text:", text.substring(0, 500));
            throw new Error("Invalid JSON response from server");
        }
        
        messagesDiv.innerHTML = '';
        messages.forEach(msg => {
            const isMine = msg.sender_id == userId;
            const formattedTime = msg.sent_at ? msg.sent_at.substring(11, 16) : '';
            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper ' + (isMine ? 'mine' : 'theirs');
            wrapper.innerHTML = `
                <div class="message-card ${msg.is_flagged ? 'flagged' : ''}">
                    ${msg.is_flagged ? '<div class="flag-label">⚠️ Important</div>' : ''}
                    <div class="sender-name">${escapeHtml(msg.sender_name)}</div>
                    <div class="message-content">
                        <p>${escapeHtml(msg.content).replace(/\n/g, '<br>')}</p>
                        ${msg.attachment ? renderAttachment(msg.attachment) : ''}
                    </div>
                    <div class="timestamp">${formattedTime}</div>
                </div>
            `;
            messagesDiv.appendChild(wrapper);
        });
        if (scrollToBottom) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    } catch (error) {
        console.error("Error loading messages:", error);
        messagesDiv.innerHTML = '<div class="error-message">Failed to load messages. Check console for details.</div>';
    }
}

async function markAsRead() {
    await fetch('../../src/api/messages.php?action=mark_read', {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ with: receiverId, property: propertyId })
    });
}

document.getElementById('chatForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const res = await fetch('../../src/api/messages.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await res.text();
        console.log("Send message response:", text);
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error("JSON parse error on send:", parseError);
            console.error("Response text:", text.substring(0, 500));
            alert('Server error. Check console for details.');
            return;
        }
        
        if (result.success) {
            form.content.value = '';
            form.attachment.value = '';
            await loadMessages();
        } else {
            alert(result.error || 'Failed to send message');
        }
    } catch (error) {
        console.error("Error sending message:", error);
        alert('Network error. Please try again.');
    }
};

function renderAttachment(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
    if (isImage) {
        return `<img src="../../uploads/${encodeURIComponent(filename)}" alt="Attachment" class="chat-image">`;
    } else {
        return `<a class="attachment-link" href="../../uploads/${encodeURIComponent(filename)}" target="_blank">📎 Attachment</a>`;
    }
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

(async function() {
    await loadMessages();
    await markAsRead();
    setInterval(loadMessages, 5000);
})();
</script>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



