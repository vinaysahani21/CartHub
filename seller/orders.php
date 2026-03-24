<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'];
$message = "";

// --- HANDLE STATUS UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $item_id = intval($_POST['item_id']);
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE order_items SET status = ? WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("sii", $new_status, $item_id, $seller_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>Status synced to <b>'.strtoupper($new_status).'</b>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// --- FETCH ORDERS FUNCTION ---
function getOrders($conn, $seller_id, $is_completed) {
    $status_condition = $is_completed ? "IN ('delivered', 'cancelled')" : "NOT IN ('delivered', 'cancelled')";
    
    $sql = "SELECT oi.id as item_id, oi.status as item_status, oi.quantity, oi.price,
                   p.name as product_name, p.image, 
                   o.id as order_id, o.created_at,
                   u.name as customer_name, u.email as customer_email
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            JOIN users u ON o.user_id = u.id
            WHERE oi.seller_id = $seller_id AND oi.status $status_condition
            ORDER BY o.created_at DESC";
            
    return $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Management | ShopEase Seller</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .seller-nav { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; }
        
        /* Tab Styling */
        .nav-tabs { border-bottom: 2px solid #e2e8f0; gap: 20px; }
        .nav-tabs .nav-link { border: none; color: #64748b; font-weight: 600; padding: 12px 20px; border-radius: 0; position: relative; }
        .nav-tabs .nav-link.active { color: #2563eb; background: none; }
        .nav-tabs .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: #2563eb; }

        /* Table UI */
        .order-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
        .table thead th { background: #f9fafb; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 15px 20px; }
        .table td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .order-img { width: 50px; height: 50px; object-fit: cover; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }

        /* Status Badges */
        .st-badge { padding: 6px 14px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .st-pending { background: #fff7ed; color: #c2410c; }
        .st-shipped { background: #eff6ff; color: #1e40af; }
        .st-delivered { background: #dcfce7; color: #15803d; }
        .st-cancelled { background: #fee2e2; color: #b91c1c; }
        
        .customer-info small { color: #94a3b8; font-size: 0.75rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg seller-nav sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fas fa-box-check me-2"></i>Seller Hub</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 small fw-bold"><?php echo htmlspecialchars($seller_name); ?></p>
                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.6rem;">FULFILLMENT CENTER</span>
            </div>
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                <?php echo strtoupper(substr($seller_name, 0, 1)); ?>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1">Fulfillment Management</h2>
                <p class="text-muted">Process your incoming orders and track shipment history.</p>
            </div>

            <?php echo $message; ?>

            <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#active" type="button">Active Requests</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">Past Orders</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="active">
                    <div class="card order-card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Item & Order Details</th>
                                        <th>Recipient</th>
                                        <th>Earnings</th>
                                        <th>Current Status</th>
                                        <th class="text-end pe-4">Fulfillment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $result = getOrders($conn, $seller_id, false); 
                                    if ($result->num_rows > 0):
                                        while($row = $result->fetch_assoc()):
                                            $total = $row['price'] * $row['quantity'];
                                            $status = $row['item_status'];
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="../<?php echo $row['image']; ?>" class="order-img me-3">
                                                <div>
                                                    <div class="fw-bold text-dark mb-0"><?php echo $row['product_name']; ?></div>
                                                    <small class="text-muted">Order ID: #<?php echo 5000 + $row['order_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="customer-info">
                                            <div class="fw-bold text-dark"><?php echo $row['customer_name']; ?></div>
                                            <small><i class="far fa-envelope me-1"></i><?php echo $row['customer_email']; ?></small>
                                        </td>
                                        <td><div class="fw-bold text-dark">₹<?php echo number_format($total, 2); ?></div><small class="text-muted">Qty: <?php echo $row['quantity']; ?></small></td>
                                        <td><span class="st-badge st-<?php echo $status; ?>"><?php echo $status; ?></span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm" onclick="openStatusModal(<?php echo $row['item_id']; ?>, '<?php echo $status; ?>')">
                                                <i class="fas fa-sync-alt me-1"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="text-center py-5 text-muted">All orders have been dispatched!</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="history">
                    <div class="card order-card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Product Details</th>
                                        <th>Completion Date</th>
                                        <th>Revenue</th>
                                        <th class="text-end pe-4">Final Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $result = getOrders($conn, $seller_id, true); 
                                    if ($result->num_rows > 0):
                                        while($row = $result->fetch_assoc()):
                                            $total = $row['price'] * $row['quantity'];
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="../<?php echo $row['image']; ?>" class="order-img me-3" style="filter: grayscale(1);">
                                                <div>
                                                    <div class="fw-bold text-muted mb-0"><?php echo $row['product_name']; ?></div>
                                                    <small class="text-muted small">Order #<?php echo 5000 + $row['order_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small"><?php echo date("d M, Y | h:i A", strtotime($row['created_at'])); ?></td>
                                        <td class="fw-bold">₹<?php echo number_format($total, 2); ?></td>
                                        <td class="text-end pe-4">
                                            <span class="st-badge st-<?php echo $row['item_status']; ?>"><?php echo $row['item_status']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">No completed orders found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h6 class="modal-title fw-bold">Shipment Progress</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body px-4 pb-4">
                    <input type="hidden" name="item_id" id="modal_item_id">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">SELECT SHIPMENT STAGE</label>
                        <select name="status" id="modal_status" class="form-select border-0 bg-light rounded-3 py-2">
                            <option value="pending">Pending Acceptance</option>
                            <option value="processing">In Processing/Packing</option>
                            <option value="shipped">Dispatched (In Transit)</option>
                            <option value="delivered">Delivered to Customer</option>
                            <option value="cancelled">Cancel Order</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Sync Status</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    function openStatusModal(id, currentStatus) {
        document.getElementById('modal_item_id').value = id;
        document.getElementById('modal_status').value = currentStatus;
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>