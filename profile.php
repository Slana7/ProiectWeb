<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$flashMessage = null;
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
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<div class="main-content">
    <header class="top-bar">
        <h1>Your Profile</h1>
    </header>    <?php if ($flashMessage): ?>
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
                <?php endif; ?>                <form method="post" action="<?= BASE_URL ?>src/controllers/ProfileController.php" class="property-form">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>

                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                    <label>New Password:</label>
                    <input type="password" name="new_password" placeholder="Leave blank to keep current password">
                    
                    <div class="form-submit-section">
                        <input type="submit" value="Update Profile" class="btn-primary">
                    </div>
                </form>
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
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=my_properties&format=csv" class="btn-export btn-csv">CSV</a>
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=my_properties&format=json" class="btn-export btn-json">JSON</a>
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=my_properties&format=pdf" class="btn-export btn-pdf">PDF</a>
                        </div>
                    </div>
                    
                    <div class="export-card">
                        <h3>Export Favorite Properties</h3>
                        <p>Download your saved favorite properties including owner contact information.</p>
                        <div class="export-buttons">
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=favorites&format=csv" class="btn-export btn-csv">CSV</a>
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=favorites&format=json" class="btn-export btn-json">JSON</a>
                            <a href="<?= BASE_URL ?>src/controllers/ExportController.php?type=favorites&format=pdf" class="btn-export btn-pdf">PDF</a>
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
</div>

<footer class="dashboard-footer">
    &copy; <?= date('Y') ?> REM Project. All rights reserved.
</footer>

<?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
