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