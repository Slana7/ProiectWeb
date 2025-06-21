<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PropertyController.php';

class EditPropertyHandler
{
    private $errors = [];
    private $property = null;
    private $facilities = [];
    private $propertyId;
    private $userId;
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->checkAuthentication();
        $this->validatePropertyAccess();
        $this->loadFacilities();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }
    }
    
    private function checkAuthentication()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        $this->userId = $_SESSION['user_id'];
    }
    
    private function validatePropertyAccess()
    {
        $this->propertyId = $_GET['id'] ?? null;
        
        if (!$this->propertyId) {
            $_SESSION['flash_message'] = 'No property specified for editing';
            header('Location: my_properties.php');
            exit;
        }
        
        $this->property = PropertyController::getPropertyById($this->propertyId);
        
        if (!$this->property) {
            $_SESSION['flash_message'] = 'Property not found';
            header('Location: my_properties.php');
            exit;
        }
        
        if ($this->property['user_id'] != $this->userId) {
            $_SESSION['flash_message'] = 'You do not have permission to edit this property';
            header('Location: my_properties.php');
            exit;
        }
    }
    
    private function loadFacilities()
    {
        $this->facilities = PropertyController::getFacilities();
    }
    
    private function handleFormSubmission()
    {
        $propertyData = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? '',
            'area' => $_POST['area'] ?? '',
            'status' => $_POST['status'] ?? 'for_sale',
            'lat' => $_POST['lat'] ?? $this->property['lat'],
            'lng' => $_POST['lng'] ?? $this->property['lng'],
            'facilities' => $_POST['facilities'] ?? []
        ];

        $result = PropertyController::updateProperty($this->propertyId, $propertyData, $this->userId, false);

        if ($result['success']) {
            $_SESSION['flash_message'] = $result['message'];
            header('Location: my_properties.php');
            exit;
        } else {
            $this->errors[] = $result['message'];
            $this->property = PropertyController::getPropertyById($this->propertyId);
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getProperty()
    {
        return $this->property;
    }
    
    public function getFacilities()
    {
        return $this->facilities;
    }
}
