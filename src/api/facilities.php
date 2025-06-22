<?php
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

try {
    $conn = Database::connect();
    $stmt = $conn->query("SELECT id, name FROM facilities ORDER BY id");
    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($facilities);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
