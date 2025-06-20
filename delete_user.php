<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/utils/AdminUtils.php';
require_once __DIR__ . '/src/controllers/AdminController.php';

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
$result = AdminController::deleteUserCompletely($userId);

if ($result['success']) {
    header("Location: admin_users.php?success=user_deleted");
} else {
    $error = $result['error'] ?? 'delete_failed';
    header("Location: admin_users.php?error=$error");
}
exit;
