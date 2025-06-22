<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AdminController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = file_get_contents('php://input');
$inputData = json_decode($input, true) ?? $_POST;
$overriddenMethod = strtoupper($inputData['_method'] ?? '');

if ($method === 'POST' && $overriddenMethod === 'DELETE') {
    $method = 'DELETE';
}

$action = $_GET['action'] ?? $inputData['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($inputData['id'] ?? 0);

try {
    if ($method === 'GET') {
        switch ($action) {
            case 'stats':
                echo json_encode([
                    'total_users' => AdminController::getTotalUsers(),
                    'total_properties' => AdminController::getTotalProperties()
                ]);
                break;

            case 'user':
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing user ID']);
                    break;
                }
                $user = AdminController::getUserById($id);
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                }
                break;

            case 'all_users':
                echo json_encode(AdminController::getAllUsersWithPropertyCount());
                break;

            case 'all_properties':
                echo json_encode(AdminController::getAllPropertiesWithOwners());
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }

    } elseif ($method === 'DELETE') {
        switch ($action) {
            case 'delete_user':
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid user ID']);
                    break;
                }

                $result = AdminController::deleteUserCompletely($id);
                if (!empty($result['success'])) {
                    echo json_encode(['success' => true, 'message' => 'User deleted']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => $result['error'] ?? 'Failed to delete user']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
