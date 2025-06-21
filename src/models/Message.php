<?php
require_once __DIR__ . '/../db/Database.php';

class Message {
    public static function sendMessage($senderId, $receiverId, $propertyId, $content, $attachment = null) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, property_id, content, attachment, sent_at, is_read, is_flagged)
            VALUES (:sender, :receiver, :property, :content, :attachment, NOW(), FALSE, FALSE)
        ");
        return $stmt->execute([
            'sender' => $senderId,
            'receiver' => $receiverId,
            'property' => $propertyId,
            'content' => $content,
            'attachment' => $attachment
        ]);
    }

    public static function getConversation($userId1, $userId2, $propertyId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT * FROM messages
            WHERE property_id = :property
              AND ((sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u2 AND receiver_id = :u1))
            ORDER BY sent_at ASC
        ");
        $stmt->execute([
            'property' => $propertyId,
            'u1' => $userId1,
            'u2' => $userId2
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markMessagesAsRead($fromUser, $toUser, $propertyId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            UPDATE messages
            SET is_read = TRUE
            WHERE sender_id = :from AND receiver_id = :to AND property_id = :prop
        ");
        $stmt->execute([
            'from' => $fromUser,
            'to' => $toUser,
            'prop' => $propertyId
        ]);
    }

    public static function getConversationWithUsernames($userId, $receiverId, $propertyId) {
    $conn = Database::connect();

    $stmt = $conn->prepare("
        SELECT m.*, 
               us.name AS sender_name
        FROM messages m
        JOIN users us ON us.id = m.sender_id
        WHERE m.property_id = :pid
          AND ((m.sender_id = :uid AND m.receiver_id = :rid) 
            OR (m.sender_id = :rid AND m.receiver_id = :uid))
        ORDER BY m.sent_at ASC
    ");
    $stmt->execute([
        'pid' => $propertyId,
        'uid' => $userId,
        'rid' => $receiverId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public static function getConversationsWithLastMessage($userId) {
    $conn = Database::connect();

    $stmt = $conn->prepare("
        SELECT 
            m.property_id,
            p.title AS property_title,
            CASE
                WHEN m.sender_id = :uid THEN m.receiver_id
                ELSE m.sender_id
            END AS other_user_id,
            u.name AS user_name,
            MAX(m.sent_at) AS last_message_time,
            SUM(CASE 
                WHEN m.receiver_id = :uid AND m.is_read = FALSE THEN 1 
                ELSE 0 
            END) > 0 AS has_unread
        FROM messages m
        JOIN properties p ON m.property_id = p.id
        JOIN users u ON u.id = CASE 
            WHEN m.sender_id = :uid THEN m.receiver_id
            ELSE m.sender_id
        END
        WHERE m.sender_id = :uid OR m.receiver_id = :uid
        GROUP BY m.property_id, p.title, other_user_id, u.name
        ORDER BY last_message_time DESC
    ");

    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public static function hasUnreadMessages($userId) {
        $conn = Database::connect();
        
        try {
            $stmt = $conn->prepare("SELECT 1 FROM messages WHERE receiver_id = :uid AND is_read = FALSE LIMIT 1");
            $stmt->execute(['uid' => $userId]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Error checking unread messages: " . $e->getMessage());
            return false;
        }
    }

}
