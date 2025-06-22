<?php
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../services/PropertyService.php';

class PropertyController
{
    public static function addProperty($data)
    {
        if (!isset($data['user_id'], $data['title'], $data['description'], $data['price'], $data['status'])) {
            return ['success' => false, 'message' => 'Missing fields'];
        }
        $propertyId = Property::create($data);
        if ($propertyId) {
            return ['success' => true, 'message' => 'Property added successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to add property'];
        }
    }

    public static function updateProperty($propertyId, $data, $userId, $isAdmin = false)
    {
        $property = Property::findById($propertyId);
        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }
        if (!$isAdmin && $property['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        return PropertyService::updateProperty($propertyId, $data, $userId, $isAdmin);
    }

    public static function removeProperty($propertyId, $userId, $isAdmin = false)
    {
        return PropertyService::removeProperty($propertyId, $userId, $isAdmin);
    }

    public static function getPropertyById($propertyId)
    {
        return Property::findById($propertyId);
    }

    public static function getUserProperties($userId)
    {
        return Property::findByUserId($userId);
    }

    public static function saveProperty($propertyId, $userId)
    {
        $property = Property::findById($propertyId);
        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }
        $success = PropertyService::savePropertyForUser($propertyId, $userId);
        return $success
            ? ['success' => true, 'message' => 'Property saved']
            : ['success' => false, 'message' => 'Failed to save property'];
    }

    public static function unsaveProperty($propertyId, $userId)
    {
        $success = PropertyService::removeSavedProperty($propertyId, $userId);
        return $success
            ? ['success' => true, 'message' => 'Property removed from favorites']
            : ['success' => false, 'message' => 'Failed to remove from favorites'];
    }

    public static function getSavedProperties($userId)
    {
        return PropertyService::getSavedPropertiesByUser($userId);
    }

    public static function getFacilities()
    {
        return Property::getAllFacilities();
    }

    public static function getAllWithOwners()
    {
        return PropertyService::getAllPropertiesWithOwners();
    }

    public static function getFavoritesByUserId($userId)
    {
        return PropertyService::getFavoriteProperties($userId);
    }

    public static function isPropertySavedByUser($propertyId, $userId)
    {
        return PropertyService::isPropertySavedByUser($propertyId, $userId);
    }

    public static function getPropertyWithStats($id)
    {
        return PropertyService::getPropertyWithStats($id);
    }

    public static function getFacilitiesByPropertyId($propertyId)
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT f.name 
            FROM facilities f
            INNER JOIN property_facility pf ON f.id = pf.facility_id
            WHERE pf.property_id = ?
        ");
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}