<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_initial = strtoupper(substr($user_name, 0, 1));

// 1. Fetch Cart Count for Nav
$cart_count = $conn->query("SELECT SUM(quantity) as q FROM cart WHERE user_id = $user_id")->fetch_assoc()['q'] ?? 0;

// 2. Fetch Orders with Items Summary
$sql = "SELECT o.* FROM orders o 
        WHERE o.user_id = $user_id 
        ORDER BY o.created_at DESC";
$orders_res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Order History - ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --border-color: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            padding-top: 90px;
            color: #1e293b;
        }

        /* --- NAV PRESERVATION --- */
        .glass-nav {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            position: fixed; top: 0; width: 100%; z-index: 1000;
        }
        .user-avatar {
            width: 35px; height: 35px; background: var(--primary-gradient);
            color: white; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;
        }

        /* --- NEW DESIGN ELEMENTS --- */
        .page-title { font-weight: 800; letter-spacing: -0.02em; }
        
        .order-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .order-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            border-color: #e2e8f0;
        }

        .order-header {
            background: #fafafa;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
        }

        .status-pill {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fff7ed; color: #c2410c; }

        .item-row {
            padding: 20px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .item-row:last-child { border-bottom: none; }
        
        .item-img {
            width: 70px; height: 70px;
            object-fit: cover;
            border-radius: 14px;
            background: #f1f5f9;
        }

        .btn-action {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .btn-track { background: #0f172a; color: white; border: none; }
        .btn-track:hover { background: #1e293b; color: white; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3); }
        
        .empty-state { padding: 100px 0; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg glass-nav py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="index.php">
                <i class="fas fa-layer-group text-primary me-2"></i>CartHub
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="cart.php" class="text-dark position-relative me-2">
                    <i class="fas fa-shopping-cart fs-5"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size: 0.6rem;"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="user-avatar"><?php echo $user_initial; ?></div>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row mb-5 align-items-center">
            <div class="col-md-6">
                <h2 class="page-title mb-1">My Orders</h2>
                <p class="text-muted mb-0">Manage your recent purchases and tracking details.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="index.php" class="btn btn-white border bg-white rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-store me-2"></i>Continue Shopping
                </a>
            </div>
        </div>

        <?php if ($orders_res->num_rows > 0): ?>
            <?php while ($order = $orders_res->fetch_assoc()):
                $order_id = $order['id'];
                $status_class = ($order['payment_status'] == 'completed') ? 'status-completed' : 'status-pending';
                ?>
                <div class="card order-card">
                    <div class="order-header">
                        <div class="row g-3 align-items-center">
                            <div class="col-6 col-md-3">
                                <p class="text-muted small fw-bold mb-1">ORDER NUMBER</p>
                                <h6 class="fw-bold mb-0">#SE-<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h6>
                            </div>
                            <div class="col-6 col-md-3">
                                <p class="text-muted small fw-bold mb-1">DATE PLACED</p>
                                <h6 class="fw-bold mb-0"><?php echo date("d M, Y", strtotime($order['created_at'])); ?></h6>
                            </div>
                            <div class="col-6 col-md-3">
                                <p class="text-muted small fw-bold mb-1">TOTAL AMOUNT</p>
                                <h6 class="fw-bold text-primary mb-0">₹<?php echo number_format($order['total_amount'], 2); ?></h6>
                            </div>
                            <div class="col-6 col-md-3 text-md-end">
                                <span class="status-pill <?php echo $status_class; ?>">
                                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                                    <?php echo $order['payment_status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 py-2">
                        <?php
                        // Fetch items and capture the first item ID for tracking
                        $item_sql = "SELECT oi.*, p.name as p_name, p.image as p_img 
                                     FROM order_items oi 
                                     JOIN products p ON oi.product_id = p.id 
                                     WHERE oi.order_id = $order_id";
                        $items_res = $conn->query($item_sql);
                        
                        $is_first = true;
                        $track_id = 0;

                        while ($item = $items_res->fetch_assoc()):
                            if($is_first) { $track_id = $item['id']; $is_first = false; }
                            ?>
                            <div class="item-row">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img src="../<?php echo $item['p_img']; ?>" class="item-img" alt="Product">
                                    </div>
                                    <div class="col">
                                        <h6 class="fw-bold mb-1 text-dark"><?php echo $item['p_name']; ?></h6>
                                        <p class="text-muted small mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="col-auto text-end">
                                        <p class="fw-bold mb-1">₹<?php echo number_format($item['price'], 2); ?></p>
                                        <span class="text-muted" style="font-size: 0.7rem;">Status: <b><?php echo strtoupper($item['status']); ?></b></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="card-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="fas fa-shield-alt me-1"></i> Order Protected</span>
                        <div>
                            <a href="invoice.php?id=<?php echo $order_id; ?>" target="_blank"
                                class="btn btn-action btn-light border text-dark me-2">
                                <i class="fas fa-file-invoice me-2"></i>Invoice
                            </a>

                            <a href="track_order.php?id=<?php echo $track_id; ?>"
                                class="btn btn-action btn-track">
                                <i class="fas fa-truck-fast me-2"></i>Track Order
                            </a>
                            
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center empty-state">
                <div class="bg-white d-inline-flex p-4 rounded-circle shadow-sm mb-4">
                    <i class="fas fa-shopping-bag fa-3x text-light" style="color: #cbd5e1 !important;"></i>
                </div>
                <h4 class="fw-bold">No orders found</h4>
                <p class="text-muted">It seems you haven't placed any orders yet.</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-5 py-2 fw-bold mt-3 shadow-lg">
                    Discover Products
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>