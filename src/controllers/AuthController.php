<?php
require_once __DIR__ . '/../db/Database.php';

class AuthController {
    public static function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'empty'];
        }
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['success' => false, 'error' => 'notfound'];
        }
        if (!password_verify($password, $row['password'])) {
            return ['success' => false, 'error' => 'invalid'];
        }
        return [
            'success' => true,
            'user_id' => $row['id'],
            'role' => $row['role'],
            'name' => $row['name']
        ];
    }

    public static function register($name, $email, $password) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'email_taken'];
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $success = $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        if ($success) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'database'];
        }
    }
}
