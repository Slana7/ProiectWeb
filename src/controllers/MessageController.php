<?php
require_once __DIR__ . '/../models/Message.php';

class MessageController {
    public static function hasUnreadMessages($userId) {
        return Message::hasUnreadMessages($userId);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../views/pages/login.php');
        exit;
    }

    $senderId = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    if ($action === 'send') {
        $receiverId = $_POST['receiver_id'] ?? null;
        $propertyId = $_POST['property_id'] ?? null;
        $content = trim($_POST['content'] ?? '');
        $attachment = null;

        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $filename = time() . '_' . basename($_FILES['attachment']['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachment = $filename;
            }
        }

        if ($receiverId && $propertyId && $content !== '') {
            Message::sendMessage($senderId, $receiverId, $propertyId, $content, $attachment);
        }

        header("Location: ../../views/pages/chat.php?with=$receiverId&property=$propertyId");
        exit;
    }

    if ($action === 'mark_read') {
        $otherUserId = $_POST['with'] ?? null;
        $propertyId = $_POST['property'] ?? null;

        if ($otherUserId && $propertyId) {
            Message::markMessagesAsRead($otherUserId, $senderId, $propertyId);
        }

        header("Location: ../../views/pages/chat.php?with=$otherUserId&property=$propertyId");
        exit;
    }
}
