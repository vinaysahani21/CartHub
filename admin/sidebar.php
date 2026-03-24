<style>
    .admin-sidebar { min-height: 100vh; background: #111827; color: #9ca3af; }
    .admin-nav-link {
        color: #9ca3af; padding: 12px 20px; display: block;
        text-decoration: none; border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .admin-nav-link:hover, .admin-nav-link.active {
        background: #1f2937; color: white; border-left-color: #3b82f6;
    }
    .admin-nav-link i { width: 25px; }
</style>

<div class="col-md-2 admin-sidebar d-none d-md-block p-0">
    <div class="p-4 border-bottom border-secondary">
        <h5 class="text-white fw-bold"><i class="fas fa-user-shield me-2"></i>AdminPanel</h5>
    </div>
    <nav class="mt-3">
        <a href="index.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'active':''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF'])=='users.php'?'active':''; ?>">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <a href="products.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF'])=='products.php'?'active':''; ?>">
            <i class="fas fa-box"></i> Manage Products
        </a>
        <a href="slider.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF'])=='slider.php'?'active':''; ?>">
            <i class="fas fa-images"></i> Hero Slider
        </a>
        <a href="analytics.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF'])=='analytics.php'?'active':''; ?>">
            <i class="fas fa-images"></i> Analytics
        </a>
        <a href="../auth/logout.php" class="admin-nav-link text-danger mt-5">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>