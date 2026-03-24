<style>
    .sidebar {
        min-height: 100vh;
        background: #1e293b; /* Dark Slate Blue */
        color: white;
    }
    .nav-link {
        color: #94a3b8;
        padding: 15px 20px;
        margin-bottom: 5px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .nav-link:hover, .nav-link.active {
        background: #2563eb; /* Primary Blue */
        color: white;
        transform: translateX(5px);
    }
    .nav-link i { width: 25px; }
    .logo-area {
        padding: 30px 20px;
        border-bottom: 1px solid #334155;
        margin-bottom: 20px;
    }
</style>

<div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
    <div class="logo-area">
        <h4 class="fw-bold"><i class="fas fa-store me-2"></i>SellerZone</h4>
        <small class="text-muted">Welcome, <?php echo $_SESSION['name']; ?></small>
    </div>
    
    <nav class="nav flex-column px-2">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>" href="add_product.php">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>" href="inventory.php">
            <i class="fas fa-box-open"></i> My Inventory
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <!-- <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
            <i class="fas fa-box-open"></i> Analytics
        </a> -->
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
            <i class="fas fa-box-open"></i> Profile
        </a>
        <div class="mt-5">
            <a class="nav-link text-danger" href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</div>