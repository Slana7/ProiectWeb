<?php
require_once __DIR__ . '/../db/Database.php';
session_start();

$conn = Database::connect();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'register') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        header("Location: ../../register.php?error=1");
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        header("Location: ../../register.php?error=1");
        exit;
    }

    // Insert new user
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password, name) VALUES (:email, :password, :name)");
    $stmt->execute([
        'email' => $email,
        'password' => $hashed,
        'name' => $name
    ]);

    header("Location: ../../login.php");
    exit;
}

if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: /REM/dashboard.php");
        exit;
    } else {
        header("Location: ../../login.php?error=1");
        exit;
    }
}