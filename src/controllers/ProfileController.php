<?php
require_once __DIR__ . '/../db/Database.php';

class ProfileController {
    public static function getUser($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }    public static function updateUser($userId, $name, $email, $newPassword = null) {
        $conn = Database::connect();
        
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->execute(['email' => $email, 'user_id' => $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already in use'];
        }
        
        try {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
                $result = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'id' => $userId
                ]);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
                $result = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'id' => $userId
                ]);
            }
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'No changes were made or user not found'];
            }
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

?>