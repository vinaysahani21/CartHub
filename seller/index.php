<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'];

// --- DATA FETCHING ---

// 1. Total Products Count
$prod_count_res = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id");
$prod_count = ($prod_count_res) ? $prod_count_res->fetch_assoc()['c'] : 0;

// 2. Total Revenue (Calculated from order_items belonging to this seller)
$revenue_query = $conn->query("SELECT SUM(price * quantity) as total FROM order_items WHERE seller_id = $seller_id AND status != 'cancelled'");
$total_revenue = ($revenue_query) ? ($revenue_query->fetch_assoc()['total'] ?? 0) : 0;

// 3. Pending Orders Count
$pending_res = $conn->query("SELECT COUNT(*) as c FROM order_items WHERE seller_id = $seller_id AND status = 'pending'");
$pending_orders = ($pending_res) ? $pending_res->fetch_assoc()['c'] : 0;

// 4. Fetch Top 3 Selling Products
$top_products = $conn->query("
    SELECT p.name, p.image, COUNT(oi.id) as sales_count, SUM(oi.price * oi.quantity) as total_earned
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.seller_id = $seller_id AND oi.status != 'cancelled'
    GROUP BY oi.product_id
    ORDER BY sales_count DESC LIMIT 3
");

// 5. Recent Activity (Last 5 order items)
$recent_activity = $conn->query("
    SELECT oi.*, p.name as p_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.seller_id = $seller_id 
    ORDER BY oi.id DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seller Dashboard | ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .seller-nav { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; }
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; background: #fff; }
        .icon-box { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .info-card { background: white; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; height: 100%; }
        .product-img-mini { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; }
        .activity-row { padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .activity-row:last-child { border-bottom: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg seller-nav sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-store-alt me-2"></i>Seller Hub
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($seller_name); ?></p>
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.6rem;">ACTIVE SELLER</span>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($seller_name, 0, 1)); ?>
                </div>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">Store Overview</h2>
                        <p class="text-muted mb-0">Managing your business at a glance.</p>
                    </div>
                    <a href="add_product.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="fas fa-plus me-2"></i>New Listing
                    </a>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card stat-card shadow-sm p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="fas fa-boxes"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold uppercase">Products</p>
                                    <h3 class="fw-bold mb-0"><?php echo $prod_count; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card shadow-sm p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-success bg-opacity-10 text-success"><i class="fas fa-indian-rupee-sign"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold uppercase">Revenue</p>
                                    <h3 class="fw-bold mb-0">₹<?php echo number_format($total_revenue, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card shadow-sm p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="fas fa-truck-loading"></i></div>
                                <div>
                                    <p class="text-muted mb-0 small fw-bold uppercase">New Orders</p>
                                    <h3 class="fw-bold mb-0"><?php echo $pending_orders; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="info-card shadow-sm">
                            <h5 class="fw-bold mb-4">Top Performing Products</h5>
                            <?php if($top_products && $top_products->num_rows > 0): ?>
                                <?php while($tp = $top_products->fetch_assoc()): ?>
                                    <div class="activity-row d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="../<?php echo $tp['image']; ?>" class="product-img-mini border">
                                            <div>
                                                <p class="mb-0 fw-bold small"><?php echo $tp['name']; ?></p>
                                                <p class="mb-0 text-muted small"><?php echo $tp['sales_count']; ?> units sold</p>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-0 fw-bold text-success">₹<?php echo number_format($tp['total_earned'], 0); ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted py-4 text-center">No sales recorded yet.</p>
                            <?php endif; ?>
                            <a href="products.php" class="btn btn-link btn-sm text-decoration-none mt-3 p-0">View all products →</a>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-card shadow-sm">
                            <h5 class="fw-bold mb-4">Recent Activity</h5>
                            <?php if($recent_activity && $recent_activity->num_rows > 0): ?>
                                <?php while($ra = $recent_activity->fetch_assoc()): 
                                    $status_badge = match($ra['status']) {
                                        'pending' => 'bg-warning',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-primary'
                                    };
                                ?>
                                    <div class="activity-row d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="mb-0 fw-bold small"><?php echo $ra['p_name']; ?></p>
                                            <p class="mb-0 text-muted small" style="font-size: 0.7rem;">Order #<?php echo $ra['order_id']; ?></p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge <?php echo $status_badge; ?> bg-opacity-10 text-<?php echo str_replace('bg-','',$status_badge); ?> small rounded-pill px-3">
                                                <?php echo strtoupper($ra['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted py-4 text-center">No recent activity.</p>
                            <?php endif; ?>
                            <a href="orders.php" class="btn btn-link btn-sm text-decoration-none mt-3 p-0">Manage all orders →</a>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="fw-bold text-primary mb-2">Boost Your Sales! 🚀</h5>
                            <p class="mb-0 text-dark opacity-75">Did you know? Products with more than 3 tags in their description appear 60% more often in customer searches. Update your product tags today!</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="products.php" class="btn btn-primary rounded-pill px-4 fw-bold">Update Tags</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>