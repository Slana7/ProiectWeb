<?php
require_once __DIR__ . '/../models/Property.php';

class PropertyController {
    public static function addProperty($data) {
        if (!isset($data['user_id'], $data['title'], $data['description'], $data['price'], $data['location'], $data['type'])) {
            return ['success' => false, 'message' => 'Missing fields'];
        }

        $property = new Property();
        $property->setTitle($data['title']);
        $property->setDescription($data['description']);
        $property->setPrice($data['price']);
        $property->setLocation($data['location']);
        $property->setType($data['type']);
        $property->setUserId($data['user_id']);
        $property->save();

        return ['success' => true, 'message' => 'Property added successfully'];
    }

    public static function updateProperty($propertyId, $data, $userId, $isAdmin = false) {
        $property = Property::findById($propertyId);

        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        if (!$isAdmin && $property->getUserId() !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $property->setTitle($data['title']);
        $property->setDescription($data['description']);
        $property->setPrice($data['price']);
        $property->setLocation($data['location']);
        $property->setType($data['type']);
        $property->save();

        return ['success' => true, 'message' => 'Property updated successfully'];
    }

    public static function removeProperty($propertyId, $userId, $isAdmin = false) {
        $property = Property::findById($propertyId);

        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        if (!$isAdmin && $property->getUserId() !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $property->delete();

        return ['success' => true, 'message' => 'Property removed successfully'];
    }

    public static function getPropertyById($propertyId) {
        return Property::findById($propertyId);
    }

    public static function getUserProperties($userId) {
        return Property::findByUserId($userId);
    }

    public static function saveProperty($propertyId, $userId) {
        $property = Property::findById($propertyId);
        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        Property::saveForUser($propertyId, $userId);
        return ['success' => true, 'message' => 'Property saved'];
    }

    public static function unsaveProperty($propertyId, $userId) {
        Property::removeSaved($propertyId, $userId);
        return ['success' => true, 'message' => 'Property removed from favorites'];
    }

    public static function getSavedProperties($userId) {
        return Property::findSavedByUser($userId);
    }

    public static function getFacilities() {
    return Property::getAllFacilities();
    }

    public static function getAllWithOwners() {
    $conn = Database::connect();

    try {
        $sql = "SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at,
                       u.name as owner_name, u.email as owner_email
                FROM properties p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.posted_at DESC";

        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
        error_log("Error fetching properties with owners: " . $e->getMessage());
        return [];
        }
    }

    public static function getFavoritesByUserId($userId) {
    $conn = Database::connect();

    try {
        $sql = "SELECT p.id, p.title, p.price
                FROM saved_properties sp
                JOIN properties p ON sp.property_id = p.id
                WHERE sp.user_id = :uid";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching favorites: " . $e->getMessage());
        return [];
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


}
