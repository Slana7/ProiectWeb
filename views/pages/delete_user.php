<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Delete User</h1>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div id="api-message"></div>
    <div class="alert alert-warning" id="userInfo">
        <h3>⚠️ Delete User Confirmation</h3>
        <p>Loading user info...</p>
    </div>
    <form id="deleteUserForm" class="property-form">
        <input type="hidden" name="confirm" value="yes">
        <div class="button-group">
            <input type="submit" value="Yes, Delete User" class="btn-danger">
            <a href="admin_users.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
const userId = <?= (int)$userId ?>;
const userInfoDiv = document.getElementById('userInfo');
const msgDiv = document.getElementById('api-message');

async function loadUserInfo() {
    const res = await fetch(`../../src/api/admin.php?action=user&id=${userId}`);
    const user = await res.json();
    if (!user || user.error) {
        userInfoDiv.innerHTML = '<p class="error">User not found or cannot be deleted.</p>';
        document.getElementById('deleteUserForm').style.display = 'none';
        return;
    }
    if (user.role === 'admin') {
        userInfoDiv.innerHTML = '<p class="error">You cannot delete another admin.</p>';
        document.getElementById('deleteUserForm').style.display = 'none';
        return;
    }
    userInfoDiv.innerHTML = `
        <h3>⚠️ Delete User Confirmation</h3>
        <p>Are you sure you want to delete the user: <strong>${escapeHtml(user.name)}</strong> (${escapeHtml(user.email)})?</p>
        <p><strong>This action will:</strong></p>
        <ul>
            <li>Permanently delete the user account</li>
            <li>Remove all their properties</li>
            <li>Delete all their messages and conversations</li>
            <li>Remove them from all favorites</li>
        </ul>
        <p><strong>This action cannot be undone!</strong></p>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('deleteUserForm').onsubmit = async function(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to permanently delete this user?')) return;

    const res = await fetch(`../../src/api/admin.php`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'delete_user',
            id: userId
        })
    });

    const result = await res.json();
    if (result.success) {
        msgDiv.innerHTML = '<div class="alert success">User deleted successfully.</div>';
        setTimeout(() => window.location.href = 'admin_users.php?success=user_deleted', 1200);
    } else {
        msgDiv.innerHTML = '<div class="alert error">' + (result.error || 'Failed to delete user') + '</div>';
    }
};

loadUserInfo();
</script>
</body>
</html>
