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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../views/pages/login.php');
        exit;
    }    $action = $_GET['action'] ?? '';

    if ($action === 'add_property') {
        $propertyData = [
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? '',
            'area' => $_POST['area'] ?? '',
            'status' => $_POST['status'] ?? 'for_sale',
            'lat' => $_POST['lat'] ?? '',
            'lng' => $_POST['lng'] ?? '',
            'facilities' => $_POST['facilities'] ?? []
        ];

        $result = PropertyController::addProperty($propertyData);

        if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
            header('Location: ../../views/pages/dashboard.php');
        } else {
            $_SESSION['flash_message'] = 'Error: ' . $result['message'];
            header('Location: ../../views/pages/add_property.php');
        }
        exit;
    }

    if ($action === 'toggle_favorite') {
        if (!isset($_POST['property_id'])) {
            $_SESSION['flash_message'] = 'No property specified';
            header('Location: ../../views/pages/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $propertyId = (int) $_POST['property_id'];
        $toggleAction = $_POST['action'];

        if ($toggleAction === 'Save') {
            $result = PropertyController::saveProperty($propertyId, $userId);
        } elseif ($toggleAction === 'Unsave') {
            $result = PropertyController::unsaveProperty($propertyId, $userId);
        } else {
            $result = ['success' => false, 'message' => 'Invalid action'];
        }

        if (!$result['success']) {
            $_SESSION['flash_message'] = $result['message'];
        }

        header("Location: ../../views/pages/property_details.php?id=$propertyId");
        exit;
    }

    if ($action === 'remove_property') {
        $propertyId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if (!$propertyId) {
            $_SESSION['flash_message'] = 'No property specified for removal';
            $redirectTo = $isAdmin ? '../../views/pages/admin_properties.php' : '../../views/pages/my_properties.php';
            header("Location: $redirectTo");
            exit;
        }
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $result = PropertyController::removeProperty($propertyId, $userId, $isAdmin);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
            } else {
                $_SESSION['flash_message'] = 'Error: ' . $result['message'];
            }
        } else {
            $_SESSION['flash_message'] = 'Property removal was cancelled';
        }

        $redirectTo = $isAdmin ? '../../views/pages/admin_properties.php' : '../../views/pages/my_properties.php';
        header("Location: $redirectTo");
        exit;
    }

    if ($action === 'edit_property') {
        $propertyId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if (!$propertyId) {
            $_SESSION['flash_message'] = 'No property specified for editing';
            $redirectTo = $isAdmin ? '../../views/pages/admin_properties.php' : '../../views/pages/my_properties.php';
            header("Location: $redirectTo");
            exit;
        }

        $propertyData = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? '',
            'area' => $_POST['area'] ?? '',
            'status' => $_POST['status'] ?? 'for_sale',
            'lat' => $_POST['lat'] ?? '',
            'lng' => $_POST['lng'] ?? '',
            'facilities' => $_POST['facilities'] ?? []
        ];

        $result = PropertyController::updateProperty($propertyId, $propertyData, $userId, $isAdmin);

        if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
            $redirectTo = $isAdmin ? '../../views/pages/admin_properties.php' : '../../views/pages/my_properties.php';
            header("Location: $redirectTo");
        } else {
            $_SESSION['flash_message'] = 'Error: ' . $result['message'];
            header("Location: ../../views/pages/edit_property.php?id=$propertyId");
        }
        exit;
    }
}
