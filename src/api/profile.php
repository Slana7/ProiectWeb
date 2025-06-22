<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ProfileController.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = ProfileController::getUser($userId);
    if ($user) {
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
    exit;
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $newPassword = $input['new_password'] ?? null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
    $stmt->execute(['email' => $email, 'user_id' => $userId]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already in use']);
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
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);