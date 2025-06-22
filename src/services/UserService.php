<?php
require_once __DIR__ . '/../db/Database.php';

class UserService {
    
    public static function deleteUserCompletely($userId) {
        $conn = Database::connect();

        $stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] === 'admin') {
            return ['success' => false, 'error' => 'forbidden'];
        }

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("DELETE FROM saved_properties WHERE property_id IN (SELECT id FROM properties WHERE user_id = :id)");
            $stmt->execute([':id' => $userId]);

            $stmt = $conn->prepare("DELETE FROM property_facility WHERE property_id IN (SELECT id FROM properties WHERE user_id = :id)");
            $stmt->execute([':id' => $userId]);

            $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = :id OR receiver_id = :id");
            $stmt->execute([':id' => $userId]);

            $stmt = $conn->prepare("DELETE FROM properties WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);

            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);

            $conn->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Delete user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'delete_failed'];
        }
    }

    public static function getAllUsersWithPropertyCount(): array {
        $conn = Database::connect();

        $stmt = $conn->query("
            SELECT u.id, u.name, u.email, u.role,
                   COUNT(p.id) as property_count
            FROM users u
            LEFT JOIN properties p ON u.id = p.user_id
            GROUP BY u.id, u.name, u.email, u.role
            ORDER BY u.id DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getUserById(int $id): ?array {
    $conn = Database::connect();

    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

}
