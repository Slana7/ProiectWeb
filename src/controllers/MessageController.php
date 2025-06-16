<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id'] ?? null;
$propertyId = $_POST['property_id'] ?? null;
$content = trim($_POST['content'] ?? '');
$attachment = null;

if (!$receiverId || !$propertyId || $content === '') {
    die("Missing required data.");
}

$conn = Database::connect();

if (!empty($_FILES['attachment']['name'])) {
    $uploadDir = __DIR__ . '/../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['attachment']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath)) {
        $attachment = $fileName;
    }
}

$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, property_id, content, attachment, is_read)
    VALUES (:sender, :receiver, :property, :content, :attachment, false)
");


$stmt->execute([
    'sender' => $userId,
    'receiver' => $receiverId,
    'property' => $propertyId,
    'content' => $content,
    'attachment' => $attachment
]);

header("Location: ../../chat.php?property=$propertyId&with=$receiverId");
exit;
