<?php
session_start();
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PropertyController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing property id']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $property = PropertyController::getPropertyById($id);
            if ($property) {
                $property['facilities'] = PropertyController::getFacilitiesByPropertyId($id);
                echo json_encode($property);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Property not found']);
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input']);
                exit;
            }
            $userId = $input['user_id'] ?? 1;
            $isAdmin = $input['is_admin'] ?? false;
            $result = PropertyController::updateProperty($id, $input, $userId, $isAdmin);
            if (!empty($result['success'])) {
                echo json_encode(['message' => 'Property updated']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => $result['message'] ?? 'Failed to update property']);
            }
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $userId = $input['user_id'] ?? 1;
            $isAdmin = $input['is_admin'] ?? false;
            $result = PropertyController::removeProperty($id, $userId, $isAdmin);
           if (!empty($result['success'])) {
                echo json_encode(['success' => true, 'message' => 'Property deleted']);
            }
            else {
                http_response_code(400);
                echo json_encode(['error' => $result['message'] ?? 'Failed to delete property']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>