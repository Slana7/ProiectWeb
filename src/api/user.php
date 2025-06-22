<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/UserController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    if ($method === 'GET' && $id) {
        $user = UserController::getById($id);
        echo json_encode($user ?: ['error' => 'Not found']);

    } elseif ($method === 'DELETE' && $id) {
        // Poți verifica dacă utilizatorul e admin sau e propriul cont
        $currentId = $_SESSION['user_id'] ?? 0;
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if ($isAdmin || $currentId === $id) {
            $result = UserController::deleteById($id);
            echo json_encode(['success' => $result]);
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
        }

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
