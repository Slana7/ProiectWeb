<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/UserService.php';

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

    public static function getUserById($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function deleteUserCompletely($userId) {
        return UserService::deleteUserCompletely($userId); 
    }

    public static function getAllPropertiesWithOwners() {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT p.*, u.name AS owner_name
            FROM properties p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllUsersWithPropertyCount() {
    $conn = Database::connect();
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.role,
               COUNT(p.id) AS property_count
        FROM users u
        LEFT JOIN properties p ON u.id = p.user_id
        GROUP BY u.id, u.name, u.email, u.role
        ORDER BY u.name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
