<?php
session_start();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'] ?? null;
    $propertyId = $input['property_id'] ?? null;
    $action = $input['action'] ?? '';
    if (!$userId || !$propertyId) {
        echo json_encode(['success' => false, 'error' => 'Missing data']);
        exit;
    }
    require_once __DIR__ . '/../db/Database.php';
    $conn = Database::connect();
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (:user, :property) ON CONFLICT DO NOTHING");
        $stmt->execute(['user' => $userId, 'property' => $propertyId]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM saved_properties WHERE user_id = :user AND property_id = :property");
        $stmt->execute(['user' => $userId, 'property' => $propertyId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']);