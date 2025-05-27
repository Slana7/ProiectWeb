<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $newPassword = $_POST['new_password'];

    if (!$email) {
        die("Email is required.");
    }

    $stmtName = $conn->prepare("SELECT name FROM users WHERE id = :id");
    $stmtName->execute(['id' => $userId]);
    $name = $stmtName->fetchColumn();

    $check = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $check->execute(['email' => $email, 'id' => $userId]);
    if ($check->fetch()) {
        header('Location: ../../profile.php?error=email_taken');
        exit;
    }

    if (!empty($newPassword)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET email = :email, password = :password WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'password' => $hashed,
            'id' => $userId
        ]);
    } else {
        $sql = "UPDATE users SET email = :email WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'id' => $userId
        ]);
    }

    session_destroy();
    header('Location: ../../login.php?updated=1');
    exit;
}
