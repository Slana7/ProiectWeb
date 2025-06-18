<?php
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    }
}

function isClient() {
    return !isset($_SESSION['user_role']) || $_SESSION['user_role'] === 'client';
}
?>
