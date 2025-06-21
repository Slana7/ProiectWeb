<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PropertyController.php';

class AddPropertyHandler
{
    private $errors = [];
    private $facilities = [];
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->checkAuthentication();
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
    }
    
    private function loadFacilities()
    {
        $this->facilities = PropertyController::getFacilities();
    }
    
    private function handleFormSubmission()
    {
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
            header('Location: dashboard.php');
            exit;
        } else {
            $this->errors[] = $result['message'];
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getFacilities()
    {
        return $this->facilities;
    }
}
