<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';
class AdminController {
    public static function getTotalUsers() {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
        return $stmt->fetchColumn();
    }

    public static function getTotalProperties() {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT COUNT(*) FROM properties");
        return $stmt->fetchColumn();
    }

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
}
?>