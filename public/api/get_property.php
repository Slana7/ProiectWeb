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
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $facilityStmt = $conn->query("
        SELECT pf.property_id, f.name
        FROM property_facility pf
        JOIN facilities f ON pf.facility_id = f.id
    ");
    $facilityData = $facilityStmt->fetchAll(PDO::FETCH_ASSOC);

    $facilityMap = [];
    foreach ($facilityData as $row) {
        $pid = $row['property_id'];
        if (!isset($facilityMap[$pid])) {
            $facilityMap[$pid] = [];
        }
        $facilityMap[$pid][] = $row['name'];
    }

    foreach ($properties as &$property) {
        $pid = $property['id'];
        $facilities = $facilityMap[$pid] ?? [];
        $property['facilities'] = implode(',', $facilities);
    }

    echo json_encode($properties);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
