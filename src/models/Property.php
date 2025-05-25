<?php
require_once __DIR__ . '/../db/Database.php';

$conn = Database::connect();

function createProperty($data) {
    global $conn;
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Create point geometry from lat/lng
        $pointSQL = "ST_GeogFromText('SRID=4326;POINT(" . $data['lng'] . " " . $data['lat'] . ")')";
        
        // Insert property
        $sql = "INSERT INTO properties (user_id, title, description, price, area, location, status) 
                VALUES (:user_id, :title, :description, :price, :area, " . $pointSQL . ", :status)
                RETURNING id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'] ?? 1, // Default to user 1 if not specified
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':area' => $data['area'],
            ':status' => $data['status']
        ]);
        
        $propertyId = $stmt->fetchColumn();
        
        // Add facilities if provided
        if (!empty($data['facilities']) && is_array($data['facilities'])) {
            foreach ($data['facilities'] as $facilityName) {
                // First get the facility ID
                $facilityId = getFacilityIdByName($facilityName);
                
                if ($facilityId) {
                    // Insert into property_facility table
                    $sql = "INSERT INTO property_facility (property_id, facility_id) VALUES (:property_id, :facility_id)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':property_id' => $propertyId,
                        ':facility_id' => $facilityId
                    ]);
                }
            }
        }
        
        $conn->commit();
        return $propertyId;
        
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error creating property: " . $e->getMessage());
        return false;
    }
}

function getFacilityIdByName($name) {
    global $conn;
    
    try {
        $sql = "SELECT id FROM facilities WHERE name = :name";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':name' => $name]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting facility ID: " . $e->getMessage());
        return false;
    }
}

function getAllProperties() {
    global $conn;
    
    try {
        $sql = "SELECT p.*, 
                ST_X(location::geometry) as lng, 
                ST_Y(location::geometry) as lat
                FROM properties p ORDER BY id DESC";
        $stmt = $conn->query($sql);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get facilities for each property
        foreach ($properties as &$property) {
            $property['facilities'] = getPropertyFacilities($property['id']);
        }
        
        return $properties;
    } catch (PDOException $e) {
        error_log("Error getting properties: " . $e->getMessage());
        return [];
    }
}

function getPropertyById($id) {
    global $conn;
    
    try {
        $sql = "SELECT p.*, 
                ST_X(location::geometry) as lng, 
                ST_Y(location::geometry) as lat
                FROM properties p WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($property) {
            $property['facilities'] = getPropertyFacilities($id);
        }
        
        return $property;
    } catch (PDOException $e) {
        error_log("Error getting property by id: " . $e->getMessage());
        return false;
    }
}

function getPropertyFacilities($propertyId) {
    global $conn;
    
    try {
        $sql = "SELECT f.name FROM facilities f
                JOIN property_facility pf ON f.id = pf.facility_id
                WHERE pf.property_id = :property_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':property_id' => $propertyId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error getting property facilities: " . $e->getMessage());
        return [];
    }
}
