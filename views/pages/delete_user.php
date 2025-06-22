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
    <title>Delete User - <?= APP_NAME ?></title>    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
    try {
        const res = await fetch(`../../src/api/admin.php?action=user&id=${userId}`);
        const user = await res.json();
        console.log(user);
        
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
            <p><strong>This action will permanently remove:</strong></p>
            <ul>
                <li>The user account</li>
                <li>All their properties</li>
                <li>All their messages and conversations</li>
                <li>All properties they saved as favorites</li>
                <li>All their property interests</li>
                <li>All related data and associations</li>
            </ul>
            <p class="error"><strong>⚠️ This action cannot be undone!</strong></p>
        `;
    } catch (error) {
        console.error('Error loading user info:', error);
        userInfoDiv.innerHTML = '<p class="error">Error loading user information. Please try again.</p>';
        document.getElementById('deleteUserForm').style.display = 'none';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('deleteUserForm').onsubmit = async function(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to permanently delete this user and all their data?')) return;

    // Disable the form to prevent double submission
    const submitBtn = this.querySelector('input[type="submit"]');
    const originalText = submitBtn.value;
    submitBtn.disabled = true;
    submitBtn.value = 'Deleting...';

    try {
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
            msgDiv.innerHTML = '<div class="alert success">User deleted successfully. Redirecting...</div>';
            setTimeout(() => window.location.href = 'admin_users.php?success=user_deleted', 1500);
        } else {
            msgDiv.innerHTML = '<div class="alert error">Error: ' + (result.error || 'Failed to delete user') + '</div>';
            // Re-enable the form
            submitBtn.disabled = false;
            submitBtn.value = originalText;
        }
    } catch (error) {
        console.error('Delete user error:', error);
        msgDiv.innerHTML = '<div class="alert error">Network error. Please try again.</div>';
        // Re-enable the form
        submitBtn.disabled = false;
        submitBtn.value = originalText;
    }
};

loadUserInfo();
</script>
</body>
</html>
