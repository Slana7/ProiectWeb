<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'], $_GET['with'], $_GET['property'])) {
    header("Location: chat_overview.php");
    exit;
}

$userId = $_SESSION['user_id'];
$senderId = (int) $_GET['with'];
$propertyId = (int) $_GET['property'];

$conn = Database::connect();

$stmt = $conn->prepare("
    UPDATE messages
    SET is_read = TRUE
    WHERE receiver_id = :uid AND sender_id = :sender AND property_id = :prop
");
$stmt->execute([
    'uid' => $userId,
    'sender' => $senderId,
    'prop' => $propertyId
]);

header("Location: chat_overview.php");
exit;
