<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/UserService.php';
class AdminController {
    public static function getTotalUsers() {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
        return $stmt->fetchColumn();
    }    public static function getTotalProperties() {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT COUNT(*) FROM properties");
        return $stmt->fetchColumn();
    }

    public static function getUserById($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }    public static function deleteUserCompletely($userId) {
        return UserService::deleteUserCompletely($userId);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        header("Location: ../../views/pages/login.php");
        exit;
    }
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'delete_user') {
        $userId = (int) ($_GET['id'] ?? 0);
        
        if (!$userId) {
            header("Location: ../../views/pages/admin_users.php?error=invalid_id");
            exit;
        }
          if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $result = AdminController::deleteUserCompletely($userId);
            
            if ($result['success']) {
                header("Location: ../../views/pages/admin_users.php?success=user_deleted");
            } else {
                $error = $result['error'] ?? 'delete_failed';
                header("Location: ../../views/pages/admin_users.php?error=$error");
            }
        } else {
            header("Location: ../../views/pages/admin_users.php?message=deletion_cancelled");
        }
        exit;
    }
}
?>