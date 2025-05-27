<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['property_id'])) {
    header("Location: ../../login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];
$propertyId = (int) $_POST['property_id'];
$action = $_POST['action'];

if ($action === 'Save') {
    $stmt = $conn->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (:uid, :pid) ON CONFLICT DO NOTHING");
    $stmt->execute(['uid' => $userId, 'pid' => $propertyId]);
} elseif ($action === 'Unsave') {
    $stmt = $conn->prepare("DELETE FROM saved_properties WHERE user_id = :uid AND property_id = :pid");
    $stmt->execute(['uid' => $userId, 'pid' => $propertyId]);
}

header("Location: ../../property_details.php?id=$propertyId");
exit;
