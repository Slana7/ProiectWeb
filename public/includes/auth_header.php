<header class="mobile-header">
    <div class="header-container">
        <div class="logo-mobile">
            <a href="<?= BASE_URL ?>">REM</a>
        </div>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>views/pages/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>src/controllers/AuthController.php?action=logout">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>