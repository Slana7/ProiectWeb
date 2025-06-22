<?php
require_once __DIR__ . '/../models/Message.php';

class MessageController {
    public static function hasUnreadMessages($userId) {
        return Message::hasUnreadMessages($userId);
    }

    public static function sendMessage($senderId, $receiverId, $propertyId, $content, $attachment = null) {
        return Message::sendMessage($senderId, $receiverId, $propertyId, $content, $attachment);
    }

    public static function markMessagesAsRead($otherUserId, $userId, $propertyId) {
        return Message::markMessagesAsRead($otherUserId, $userId, $propertyId);
    }

    public static function getConversationWithUsernames($userId, $receiverId, $propertyId) {
        return Message::getConversationWithUsernames($userId, $receiverId, $propertyId);
    }
}
