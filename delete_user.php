<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/utils/AdminUtils.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_users.php?error=invalid_id");
    exit;
}

$userId = (int) $_GET['id'];

$conn = Database::connect();

$stmt = $conn->prepare("DELETE FROM saved_properties WHERE property_id IN (SELECT id FROM properties WHERE user_id = :id)");
$stmt->execute([':id' => $userId]);


$stmt = $conn->prepare("DELETE FROM property_facility WHERE property_id IN (SELECT id FROM properties WHERE user_id = :id)");
$stmt->execute([':id' => $userId]);

$stmt = $conn->prepare("DELETE FROM properties WHERE user_id = :id");
$stmt->execute([':id' => $userId]);


$stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] === 'admin') {
    header("Location: admin_users.php?error=forbidden");
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("DELETE FROM property_facility WHERE property_id IN (SELECT id FROM properties WHERE user_id = :id)");
    $stmt->execute([':id' => $userId]);

    $stmt = $conn->prepare("DELETE FROM properties WHERE user_id = :id");
    $stmt->execute([':id' => $userId]);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);

    $conn->commit();
    header("Location: admin_users.php?success=user_deleted");
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Delete user error: " . $e->getMessage());
    header("Location: admin_users.php?error=delete_failed");
}
exit;
