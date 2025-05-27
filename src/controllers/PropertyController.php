<?php
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../db/Database.php';

$conn = Database::connect();

function addProperty($data) {
    // Validate input
    $errors = validatePropertyData($data);
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }
    
    // Process facilities
    if (isset($data['facilities']) && is_array($data['facilities'])) {
        // Already an array, good to go
    } else {
        $data['facilities'] = [];
    }
    
    // Save to database
    $propertyId = createProperty($data);
    
    if ($propertyId) {
        return [
            'success' => true,
            'message' => 'Property added successfully',
            'property_id' => $propertyId
        ];
    } else {
        return [
            'success' => false,
            'errors' => ['Failed to add property to database']
        ];
    }
}

function validatePropertyData($data) {
    $errors = [];
    
    $requiredFields = ['title', 'description', 'price', 'area', 'status', 'lat', 'lng'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Numeric fields
    if (!empty($data['price']) && !is_numeric($data['price'])) {
        $errors[] = 'Price must be a number';
    }
    
    if (!empty($data['area']) && !is_numeric($data['area'])) {
        $errors[] = 'Area must be a number';
    }
    
    // Validate coordinates
    if (!empty($data['lat']) && (!is_numeric($data['lat']) || $data['lat'] < -90 || $data['lat'] > 90)) {
        $errors[] = 'Latitude must be a valid number between -90 and 90';
    }
    
    if (!empty($data['lng']) && (!is_numeric($data['lng']) || $data['lng'] < -180 || $data['lng'] > 180)) {
        $errors[] = 'Longitude must be a valid number between -180 and 180';
    }
    
    return $errors;
}

function removeProperty($propertyId, $userId) {
    global $conn;
    
    try {
        // Verifica existenta proprietatii
        $sql = "SELECT user_id FROM properties WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $propertyId]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$property) {
            return [
                'success' => false,
                'errors' => ['Property not found']
            ];
        }
        
        // Verifica daca utilizatorul este proprietarul
        if ($property['user_id'] != $userId) {
            return [
                'success' => false,
                'errors' => ['You do not have permission to remove this property']
            ];
        }
        
        // Porneste tranzactia
        $conn->beginTransaction();
        
        // Sterge relatiile cu facilitatile
        $sql = "DELETE FROM property_facility WHERE property_id = :property_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':property_id' => $propertyId]);
        
        // Sterge proprietatea
        $sql = "DELETE FROM properties WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $propertyId]);
        
        // Finalizeaza tranzactia
        $conn->commit();
        
        // Returneaza succes
        return [
            'success' => true,
            'message' => 'Property removed successfully'
        ];
        
    } catch (PDOException $e) {
        // Anuleaza tranzactia in caz de eroare
        $conn->rollBack();
        error_log("Error removing property: " . $e->getMessage());
        return [
            'success' => false,
            'errors' => ['Database error occurred while removing the property']
        ];
    }
}

function getUserProperties($userId) {
    global $conn;
    
    try {
        // Obtine proprietatile utilizatorului
        $sql = "SELECT p.*, 
                ST_X(location::geometry) as lng, 
                ST_Y(location::geometry) as lat
                FROM properties p 
                WHERE p.user_id = :user_id
                ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Adauga facilitatile pentru fiecare proprietate
        foreach ($properties as &$property) {
            $property['facilities'] = getPropertyFacilities($property['id']);
        }
        
        // Returneaza lista de proprietati
        return $properties;
    } catch (PDOException $e) {
        // Logeaza eroarea
        error_log("Error getting user properties: " . $e->getMessage());
        return [];
    }
}