<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/db/Database.php';

header('Content-Type: application/json');

try {
    $conn = Database::connect();

    $sql = "SELECT id, title, price, ST_Y(location::geometry) AS lat, ST_X(location::geometry) AS lng
            FROM properties
            WHERE location IS NOT NULL";

    $stmt = $conn->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
