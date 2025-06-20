<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';

class ProfileController
{
    public static function getUser($userId)
    {
        $conn = Database::connect();
        return User::findById($conn, $userId);
    }

    public static function updateProfile($userId, $name, $password = null)
    {
        $conn = Database::connect();
        return User::update($conn, $userId, $name, $password);
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

}
