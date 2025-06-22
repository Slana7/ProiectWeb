<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/utils/AdminUtils.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

requireAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Manage Users</h1>
    <div id="api-message"></div>
    <div class="admin-badge">
        <span>🛡️ Administrator</span>
    </div>
</header>

<section class="form-section">
    <div style="overflow-x: auto; width: 100%;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Properties</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTbody">
            </tbody>
        </table>
    </div>
</section>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>

<script>
async function loadUsers() {
    const res = await fetch('../../src/api/admin.php?action=all_users');
    const users = await res.json();
    const tbody = document.getElementById('usersTbody');
    tbody.innerHTML = '';
    if (Array.isArray(users)) {
        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>
                    <span class="status-badge ${user.role === 'admin' ? 'status-active' : 'status-inactive'}">
                        ${capitalize(user.role)}
                    </span>
                </td>
                <td>${user.property_count}</td>                <td>
                    ${user.role !== 'admin' ? `<a href="delete_user.php?id=${user.id}" class="btn btn-small btn-danger">Delete</a>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="5">No users found or error loading data.</td></tr>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

loadUsers();
</script>
</body>
</html>



