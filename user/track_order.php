<?php
include 'auth_check.php';
include '../config/db.php';

if (!isset($_GET['id'])) { header("Location: my_orders.php"); exit(); }

$item_id = intval($_GET['id']);
$sql = "SELECT oi.*, p.name as p_name, p.image as p_img 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.id = $item_id AND oi.order_id IN (SELECT id FROM orders WHERE user_id = {$_SESSION['user_id']})";
$res = $conn->query($sql);
$item = $res->fetch_assoc();

if (!$item) { echo "Order item not found."; exit(); }

$status = $item['status'];

// Map status to progress percentage
$status_map = [
    'pending' => 10, 
    'processing' => 40, 
    'shipped' => 70, 
    'delivered' => 100,
    'cancelled' => 0
];
$current_progress = $status_map[$status] ?? 10;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Track Order - #<?php echo $item_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .track-line { height: 4px; background: #e0e0e0; position: relative; top: 20px; z-index: 1; }
        .track-line .progress-fill { height: 100%; background: #6366f1; transition: width 1s; }
        .dot { width: 40px; height: 40px; background: #fff; border: 4px solid #e0e0e0; border-radius: 50%; z-index: 2; position: relative; display: flex; align-items: center; justify-content: center; }
        .dot.active { border-color: #6366f1; color: #6366f1; }
        .dot.finished { background: #6366f1; border-color: #6366f1; color: #fff; }
        .btn-cancel-link { color: #dc3545; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .btn-cancel-link:hover { color: #a71d2a; text-decoration: underline; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card border-0 shadow-sm p-4 rounded-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <img src="../<?php echo $item['p_img']; ?>" width="70" class="rounded-3 border me-3">
                <div>
                    <h5 class="fw-bold mb-0"><?php echo $item['p_name']; ?></h5>
                    <p class="text-muted small mb-0">Tracking ID: TRK<?php echo time() . $item['id']; ?></p>
                </div>
            </div>
            
            <?php if ($status !== 'shipped' && $status!=='delivered' && $status !== 'cancelled'): ?>
                <a href="cancel_order.php?item_id=<?php echo $item['id']; ?>" 
                   class="btn btn-outline-danger rounded-pill px-4 btn-sm fw-bold"
                   onclick="return confirm('Are you sure you want to cancel this order?')">
                   Cancel Order
                </a>
            <?php endif; ?>
        </div>

        <?php if ($status === 'cancelled'): ?>
            <div class="text-center py-4">
                <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
                <h4 class="fw-bold text-danger">Order Cancelled</h4>
                <p class="text-muted">This order was cancelled and a refund (if applicable) has been initiated.</p>
            </div>
        <?php else: ?>
            <div class="row text-center mb-5 mt-4 position-relative">
                <div class="track-line mx-auto" style="width: 80%;">
                    <div class="progress-fill" style="width: <?php echo $current_progress; ?>%;"></div>
                </div>
                <div class="col-3">
                    <div class="dot mx-auto <?php echo $current_progress >= 10 ? 'finished' : ''; ?>"><i class="fas fa-receipt"></i></div>
                    <div class="mt-2 small fw-bold">Ordered</div>
                </div>
                <div class="col-3">
                    <div class="dot mx-auto <?php echo $current_progress >= 40 ? ($current_progress > 40 ? 'finished' : 'active') : ''; ?>"><i class="fas fa-box"></i></div>
                    <div class="mt-2 small fw-bold">Packed</div>
                </div>
                <div class="col-3">
                    <div class="dot mx-auto <?php echo $current_progress >= 70 ? ($current_progress > 70 ? 'finished' : 'active') : ''; ?>"><i class="fas fa-truck"></i></div>
                    <div class="mt-2 small fw-bold">Shipped</div>
                </div>
                <div class="col-3">
                    <div class="dot mx-auto <?php echo $current_progress == 100 ? 'finished' : ''; ?>"><i class="fas fa-check"></i></div>
                    <div class="mt-2 small fw-bold">Delivered</div>
                </div>
            </div>
        <?php endif; ?>

        <div class="alert <?php echo ($status === 'cancelled') ? 'alert-danger' : 'alert-info'; ?> border-0 rounded-3 d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-info-circle me-2"></i> Current Status: <strong><?php echo strtoupper($status); ?></strong>
            </div>
            <a href="my_orders.php" class="btn btn-dark btn-sm rounded-pill px-3">View All Orders</a>
        </div>
    </div>
</div>
</body>
</html>