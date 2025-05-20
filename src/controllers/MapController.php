<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

$conn = Database::connect();
$stmt = $conn->query("SELECT id, name, latitude, longitude, address FROM properties");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($properties);