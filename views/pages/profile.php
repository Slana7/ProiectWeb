<?php
require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/controllers/ProfileController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = ProfileController::getUser($userId);

$flashMessage = '';
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
<?php include_once '../../public/includes/dashboard_header.php'; ?>

<header class="top-bar">
    <h1>Your Profile</h1>
</header>

<?php if ($flashMessage): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($flashMessage) ?>
    </div>
<?php endif; ?>

<div class="profile-container">
    <div class="profile-left">
        <section class="form-section">
            <h2>Profile Information</h2>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'email_taken'): ?>
                <p class="error">Email already in use. Please choose another.</p>
            <?php endif; ?>
              <form id="profileForm" class="property-form">
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                <label>New Password:</label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current password">
                
                <div class="form-submit-section">
                    <input type="submit" value="Update Profile" class="btn-primary">
                </div>
            </form>            <script>
document.getElementById('profileForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        name: form.name.value.trim(),
        email: form.email.value.trim(),
        new_password: form.new_password.value.trim() || null
    };
    
    if (!data.name) {
        alert('Name is required');
        return;
    }
    if (!data.email) {
        alert('Email is required');
        return;
    }
    
    try {
        const res = await fetch('../../src/api/profile.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.error || 'Network error');
        }
        
        const result = await res.json();
        if (result.success) {
            alert('Profile updated successfully!');
            window.location.reload();
        } else {
            alert(result.error || 'Failed to update profile');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        alert('Error: ' + error.message);
    }
};
</script>
        </section>
    </div>

    <div class="profile-right">
        <section class="export-section">
            <h2>Export Your Data</h2>
            <p>Download your property data in various formats for backup or analysis.</p>
            
            <div class="export-cards">
                <div class="export-card">
                    <h3>Export My Properties</h3>
                    <p>Download all your listed properties with complete details.</p>
                    <div class="export-buttons">
                        <a href="<?= BASE_URL ?>src/api/export.php?type=my_properties&format=csv" class="btn-export btn-csv">CSV</a>
                        <a href="<?= BASE_URL ?>src/api/export.php?type=my_properties&format=json" class="btn-export btn-json">JSON</a>
                        <a href="<?= BASE_URL ?>views/pages/export_pdf_preview.php?type=my_properties" class="btn-export btn-pdf">PDF</a>

                    </div>
                </div>
                
                <div class="export-card">
                    <h3>Export Favorite Properties</h3>
                    <p>Download your saved favorite properties including owner contact information.</p>
                    <div class="export-buttons">
                       <a href="<?= BASE_URL ?>src/api/export.php?type=favorites&format=csv" class="btn-export btn-csv">CSV</a>
                        <a href="<?= BASE_URL ?>src/api/export.php?type=favorites&format=json" class="btn-export btn-json">JSON</a>
                        <a href="<?= BASE_URL ?>views/pages/export_pdf_preview.php?type=favorites" class="btn-export btn-pdf">PDF</a>

                    </div>
                </div>
            </div>
            
            <div class="export-info">
                <h4>Export Formats:</h4>
                <ul>
                    <li><strong>CSV:</strong> Best for spreadsheet applications like Excel or Google Sheets</li>
                    <li><strong>JSON:</strong> Ideal for developers and data analysis tools</li>
                    <li><strong>PDF:</strong> Perfect for printing and sharing formatted reports</li>
                </ul>
            </div>
        </section>
    </div>
</div>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once '../../public/includes/dashboard_footer.php'; ?>
</body>
</html>



