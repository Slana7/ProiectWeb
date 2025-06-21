<?php
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/UserService.php';

class AuthController {
    public static function login($email, $password) {
        if (empty($email) || empty($password)) {
            header('Location: ../../views/pages/login.php?error=empty');
            exit;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            header('Location: ../../views/pages/login.php?error=notfound');
            exit;
        }

        if (!password_verify($password, $row['password'])) {
            header('Location: ../../views/pages/login.php?error=invalid');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_role'] = $row['role'];
        $_SESSION['user_name'] = $row['name'];

        if ($row['role'] === 'admin') {
            header('Location: ../../views/pages/admin_dashboard.php');
        } else {
            header('Location: ../../views/pages/dashboard.php');
        }
        exit;
    }

    public static function register($name, $email, $password) {
        if (empty($name) || empty($email) || empty($password)) {
            header('Location: ../../views/pages/register.php?error=empty');
            exit;
        }

        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            header('Location: ../../views/pages/register.php?error=email_taken');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password) 
            VALUES (:name, :email, :password)
        ");

        $success = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        if ($success) {
            header('Location: ../../views/pages/login.php?success=registered');
        } else {
            header('Location: ../../views/pages/register.php?error=database');
        }
        exit;
    }

    public static function findByEmail($email) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row['id'], $row['name'], $row['email'], $row['role']);
        }

        return null;
    }

    public static function findById($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row['id'], $row['name'], $row['email'], $row['role']);
        }

        return null;
    }

    public static function updateUser($id, $name, $password = null) {
        $conn = Database::connect();

        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = :name, password = :password WHERE id = :id");
            return $stmt->execute([
                'name' => $name,
                'password' => $hashed,
                'id' => $id
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = :name WHERE id = :id");
            return $stmt->execute([
                'name' => $name,
                'id' => $id
            ]);
        }
    }

    public static function deleteUser($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getAllUsers() {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
        $result = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new User($row['id'], $row['name'], $row['email'], $row['role']);
        }

        return $result;
    }    public static function deleteUserWithData($id) {
        return UserService::deleteUserCompletely($id);
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: ../../views/pages/login.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        AuthController::login($email, $password);
    }

    if ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        AuthController::register($name, $email, $password);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'logout') {
        AuthController::logout();
    }
}
