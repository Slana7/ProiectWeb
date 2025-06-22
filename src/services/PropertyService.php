<?php
require_once __DIR__ . '/../db/Database.php';

class PropertyService {
    
    public static function getFacilitiesForProperty($propertyId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT f.name 
            FROM property_facility pf 
            JOIN facilities f ON pf.facility_id = f.id 
            WHERE pf.property_id = :pid
        ");
        $stmt->execute(['pid' => $propertyId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function addFacilitiesToProperties(&$properties) {
        foreach ($properties as &$property) {
            $facilities = self::getFacilitiesForProperty($property['id']);
            $property['facilities'] = implode(', ', $facilities);
        }
    }

    
    public static function getFavoriteProperties($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at as created_at,
                   ST_Y(p.location::geometry) as lat, ST_X(p.location::geometry) as lng,
                   u.name as owner_name, u.email as owner_email
            FROM saved_properties sp
            JOIN properties p ON sp.property_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE sp.user_id = :uid
            ORDER BY p.id DESC
        ");
        $stmt->execute(['uid' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        self::addFacilitiesToProperties($data);
        return $data;
    }

    public static function getUserProperties($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at as created_at,
                   ST_Y(p.location::geometry) as lat, ST_X(p.location::geometry) as lng
            FROM properties p
            WHERE p.user_id = :uid
            ORDER BY p.posted_at DESC
        ");
        $stmt->execute(['uid' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        self::addFacilitiesToProperties($data);
        return $data;
    }

    public static function savePropertyForUser($propertyId, $userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            INSERT INTO saved_properties (user_id, property_id) 
            VALUES (:uid, :pid) 
            ON CONFLICT DO NOTHING
        ");
        return $stmt->execute(['uid' => $userId, 'pid' => $propertyId]);
    }

    public static function removeSavedProperty($propertyId, $userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            DELETE FROM saved_properties 
            WHERE user_id = :uid AND property_id = :pid
        ");
        return $stmt->execute(['uid' => $userId, 'pid' => $propertyId]);
    }

    public static function getSavedPropertiesByUser($userId) {
        $conn = Database::connect();
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.price
            FROM saved_properties sp
            JOIN properties p ON sp.property_id = p.id
            WHERE sp.user_id = :uid
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function removeProperty($propertyId, $userId, $isAdmin = false) {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT user_id FROM properties WHERE id = :id");
        $stmt->execute(['id' => $propertyId]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        if (!$isAdmin && $property['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("DELETE FROM saved_properties WHERE property_id = :id");
            $stmt->execute(['id' => $propertyId]);
            
            $stmt = $conn->prepare("DELETE FROM property_facility WHERE property_id = :id");
            $stmt->execute(['id' => $propertyId]);
            
            $stmt = $conn->prepare("DELETE FROM messages WHERE property_id = :id");
            $stmt->execute(['id' => $propertyId]);
            
            $stmt = $conn->prepare("DELETE FROM properties WHERE id = :id");
            $success = $stmt->execute(['id' => $propertyId]);
            
            $conn->commit();
            
            return $success 
                ? ['success' => true, 'message' => 'Property removed successfully']
                : ['success' => false, 'message' => 'Failed to remove property'];
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error removing property: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public static function updateProperty($propertyId, $data, $userId, $isAdmin = false) {
        $conn = Database::connect();
        try {
            $stmt = $conn->prepare("
                UPDATE properties 
                SET title = :title, description = :description, price = :price, 
                    area = :area, status = :status 
                WHERE id = :id
            ");
            $success = $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'area' => $data['area'],
                'status' => $data['status'],
                'id' => $propertyId
            ]);
            
            return $success 
                ? ['success' => true, 'message' => 'Property updated successfully']
                : ['success' => false, 'message' => 'Failed to update property'];
        } catch (PDOException $e) {
            error_log("Error updating property: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    public static function isPropertySavedByUser($propertyId, $userId) {
        $conn = Database::connect();

        try {
            $stmt = $conn->prepare("SELECT 1 FROM saved_properties WHERE user_id = :uid AND property_id = :pid");
            $stmt->execute(['uid' => $userId, 'pid' => $propertyId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error checking if property is saved: " . $e->getMessage());
            return false;
        }
    }

    public static function getPropertyWithStats($id) {
        $conn = Database::connect();

        $stmt = $conn->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            return null;
        }

        $lat = $conn->query("SELECT ST_Y(location::geometry) FROM properties WHERE id = $id")->fetchColumn();
        $lng = $conn->query("SELECT ST_X(location::geometry) FROM properties WHERE id = $id")->fetchColumn();

        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) AS total_properties,
                ROUND(AVG(price), 2) AS avg_price,
                MIN(price) AS min_price,
                MAX(price) AS max_price
            FROM properties
            WHERE id != :id
              AND status = :status
              AND ST_DWithin(location, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)::geography, 2000)
        ");
        $statsStmt->execute([
            'id' => $property['id'],
            'status' => $property['status'],
            'lat' => $lat,
            'lng' => $lng
        ]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        $property['lat'] = $lat;
        $property['lng'] = $lng;

        return [
            'details' => $property,
            'stats' => $stats
        ];
    }

    public static function getAllPropertiesForMap() {
        $conn = Database::connect();
        
        try {
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

            return $properties;
        } catch (PDOException $e) {
            error_log("Error getting properties for map: " . $e->getMessage());
            return [];
        }
    }

    public static function getAllPropertiesWithOwners() {
        $conn = Database::connect();

        try {
            $sql = "SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at,
                           u.name as owner_name, u.email as owner_email
                    FROM properties p
                    JOIN users u ON p.user_id = u.id
                    ORDER BY p.posted_at DESC";

            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching properties with owners: " . $e->getMessage());
            return [];
        }
    }
    public static function createProperty($data) {
    $conn = Database::connect();

    try {
        $stmt = $conn->prepare("
            INSERT INTO properties (user_id, title, description, price, area, status, location)
            VALUES (:uid, :title, :description, :price, :area, :status, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326))
        ");
        $stmt->execute([
            'uid' => $_SESSION['user_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'area' => $data['area'],
            'status' => $data['status'],
            'lat' => $data['lat'],
            'lng' => $data['lng']
        ]);
        $propertyId = $conn->lastInsertId();

        if (!empty($data['facilities'])) {
            $facilityStmt = $conn->prepare("
                INSERT INTO property_facility (property_id, facility_id)
                SELECT :pid, id FROM facilities WHERE name = :fname
            ");
            foreach ($data['facilities'] as $facilityName) {
                $facilityStmt->execute([
                    'pid' => $propertyId,
                    'fname' => $facilityName
                ]);
            }
        }

        return ['success' => true, 'id' => $propertyId];

    } catch (PDOException $e) {
        error_log('DB error in createProperty: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

}
