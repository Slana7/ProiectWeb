<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Message.php';

header('Content-Type: application/json');
//session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $propertyId = $_GET['property'] ?? null;
    $receiverId = $_GET['with'] ?? null;
    $action = $_GET['action'] ?? null;
    if ($propertyId && $receiverId) {
        $messages = Message::getConversationWithUsernames($userId, $receiverId, $propertyId);
        echo json_encode($messages);
    } elseif ($action === 'overview') {
        $conversations = Message::getConversationsWithLastMessage($userId);
        echo json_encode($conversations);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
    }
    exit;
}

if ($method === 'POST') {
    $input = $_POST;
    $receiverId = $input['receiver_id'] ?? null;
    $propertyId = $input['property_id'] ?? null;
    $content = trim($input['content'] ?? '');
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
        $ok = Message::sendMessage($userId, $receiverId, $propertyId, $content, $attachment);
        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
    }
    exit;
}

if ($method === 'PATCH' && isset($_GET['action']) && $_GET['action'] === 'mark_read') {
    $input = json_decode(file_get_contents('php://input'), true);
    $otherUserId = $input['with'] ?? null;
    $propertyId = $input['property'] ?? null;
    if ($otherUserId && $propertyId) {
        Message::markMessagesAsRead($otherUserId, $userId, $propertyId);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);