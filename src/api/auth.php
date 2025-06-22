<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'empty']);
        exit;
    }

    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'notfound']);
        exit;
    }

    if (!password_verify($password, $row['password'])) {
        echo json_encode(['success' => false, 'error' => 'invalid']);
        exit;
    }

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_role'] = $row['role'];
    $_SESSION['user_name'] = $row['name'];

    echo json_encode(['success' => true, 'role' => $row['role'], 'user_id' => $row['id']]);
    exit;
}

if ($method === 'POST' && $action === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'POST' && $action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'empty']);
        exit;
    }

    $conn = Database::connect();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'email_taken']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'user')");
    try {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hash
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'database']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
