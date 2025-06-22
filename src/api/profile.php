<?php
session_start();
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ProfileController.php';

header('Content-Type: application/json');

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
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }
    
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $newPassword = !empty($input['new_password']) ? $input['new_password'] : null;

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required']);
        exit;
    }    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }
    
    // Use the ProfileController to update the user
    $result = ProfileController::updateUser($userId, $name, $email, $newPassword);
    
    if ($result['success']) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $result['error']]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);