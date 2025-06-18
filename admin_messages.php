<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/utils/AdminUtils.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();

$conn = Database::connect();

$stmt = $conn->query("
    SELECT m.id, m.content, m.sent_at, m.is_read, m.is_flagged,
           sender.name as sender_name,
           receiver.name as receiver_name,
           p.title as property_title
    FROM messages m
    JOIN users sender ON m.sender_id = sender.id
    JOIN users receiver ON m.receiver_id = receiver.id
    JOIN properties p ON m.property_id = p.id
    ORDER BY m.sent_at DESC
    LIMIT 100
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Messages</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Property</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Sent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= $message['id'] ?></td>
                    <td><?= htmlspecialchars($message['sender_name']) ?></td>
                    <td><?= htmlspecialchars($message['receiver_name']) ?></td>
                    <td><?= htmlspecialchars(substr($message['property_title'], 0, 20)) ?><?= strlen($message['property_title']) > 20 ? '...' : '' ?></td>
                    <td><?= htmlspecialchars(substr($message['content'], 0, 50)) ?><?= strlen($message['content']) > 50 ? '...' : '' ?></td>
                    <td>
                        <?php if ($message['is_flagged']): ?>
                            <span class="status-badge status-active">⚠️ Flagged</span>
                        <?php endif; ?>
                        <?php if (!$message['is_read']): ?>
                            <span class="status-badge status-inactive">Unread</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M j, H:i', strtotime($message['sent_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
