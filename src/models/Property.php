<?php
require_once __DIR__ . '/../db/Database.php';

class Property {
    public static function create($data) {
        $conn = Database::connect();

        try {
            $conn->beginTransaction();

            $pointSQL = "ST_GeogFromText('SRID=4326;POINT(" . $data['lng'] . " " . $data['lat'] . ")')";

            $sql = "INSERT INTO properties (user_id, title, description, price, area, location, status) 
                    VALUES (:user_id, :title, :description, :price, :area, $pointSQL, :status)
                    RETURNING id";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'] ?? 1,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':area' => $data['area'],
                ':status' => $data['status']
            ]);

            $propertyId = $stmt->fetchColumn();

            if (!empty($data['facilities']) && is_array($data['facilities'])) {
                foreach ($data['facilities'] as $facilityName) {
                    $facilityId = self::getFacilityIdByName($facilityName);

                    if ($facilityId) {
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

    public static function getAll() {
        $conn = Database::connect();

        try {
            $sql = "SELECT p.*, ST_X(location::geometry) as lng, ST_Y(location::geometry) as lat
                    FROM properties p ORDER BY id DESC";
            $stmt = $conn->query($sql);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as &$property) {
                $property['facilities'] = self::getFacilitiesFor($property['id']);
            }

            return $properties;
        } catch (PDOException $e) {
            error_log("Error getting properties: " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        $conn = Database::connect();

        try {
            $sql = "SELECT p.*, ST_X(location::geometry) as lng, ST_Y(location::geometry) as lat
                    FROM properties p WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($property) {
                $property['facilities'] = self::getFacilitiesFor($id);
            }

            return $property;
        } catch (PDOException $e) {
            error_log("Error getting property by id: " . $e->getMessage());
            return false;
        }
    }

    public static function getFacilitiesFor($propertyId) {
        $conn = Database::connect();

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

    public static function getFacilityIdByName($name) {
        $conn = Database::connect();

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

    public static function getAllFacilities() {
        $conn = Database::connect();

        try {
            $stmt = $conn->query("SELECT id, name FROM facilities ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all facilities: " . $e->getMessage());
            return [];
        }
    }
    public static function findByUserId($userId) {
    $conn = Database::connect();

    try {
        $sql = "SELECT id, title, price, area, status 
                FROM properties 
                WHERE user_id = :uid 
                ORDER BY posted_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in Property::findByUserId: " . $e->getMessage());
        return [];
    }
}

}
