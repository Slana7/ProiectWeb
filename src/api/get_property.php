<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/PropertyService.php';

header('Content-Type: application/json');

try {
    $properties = PropertyService::getAllPropertiesForMap();
    echo json_encode($properties);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
