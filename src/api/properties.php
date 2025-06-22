<?php
session_start();
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/PropertyService.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if (isset($_GET['scope']) && $_GET['scope'] === 'user') {
            $userId = $_SESSION['user_id'];
            $properties = PropertyService::getUserProperties($userId);
        } else {
            $properties = PropertyService::getAllPropertiesForMap();
        }

        echo json_encode($properties);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = PropertyService::createProperty($input);
        if (!empty($result['success'])) {
            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $result['id'] ?? null]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Failed to create property']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
