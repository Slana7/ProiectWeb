<header class="mobile-header">
    <div class="header-container">
        <div class="logo-mobile">
            <a href="<?= BASE_URL ?>index.php">REM</a>
        </div>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>