<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/UserService.php';

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
        return UserService::getAllUsersWithPropertyCount();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../views/pages/login.php");
        exit;
    }

    $action = $_GET['action'] ?? '';
    
    if ($action === 'update_profile') {
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $newPassword = $_POST['new_password'] ?? null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Invalid email format.';
            header("Location: ../../views/pages/profile.php");
            exit;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->execute(['email' => $email, 'user_id' => $userId]);
        if ($stmt->fetch()) {
            $_SESSION['flash_message'] = 'Email already in use. Please choose another.';
            header("Location: ../../views/pages/profile.php?error=email_taken");
            exit;
        }

        try {
            if ($newPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
                $success = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'id' => $userId
                ]);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
                $success = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'id' => $userId
                ]);
            }

            if ($success) {
                $_SESSION['flash_message'] = 'Profile updated successfully!';
                header("Location: ../../views/pages/profile.php");
            } else {
                $_SESSION['flash_message'] = 'Failed to update profile.';
                header("Location: ../../views/pages/profile.php");
            }
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['flash_message'] = 'Database error occurred.';
            header("Location: ../../views/pages/profile.php");
        }
        exit;
    }
}
?>