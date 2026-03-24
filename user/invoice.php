<?php
include 'auth_check.php';
include '../config/db.php';

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get Order Info
$order_res = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
$order = $order_res->fetch_assoc();
if (!$order) exit("Unauthorized access.");

// Get Items
$items_res = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice_#<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } }
        .invoice-box { background: #fff; padding: 40px; border: 1px solid #eee; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Print Invoice</button>
    </div>
    
    <div class="invoice-box shadow-sm">
        <div class="row mb-5">
            <div class="col-6">
                <h2 class="fw-bold text-primary">ShopEase</h2>
                <p class="text-muted">Surat, Gujarat, India<br>GSTIN: 24AAACS1234F1Z5</p>
            </div>
            <div class="col-6 text-end">
                <h4 class="text-uppercase text-muted">Tax Invoice</h4>
                <p class="mb-0">Order ID: #<?php echo $order_id; ?></p>
                <p>Date: <?php echo date("d/m/Y", strtotime($order['created_at'])); ?></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <p class="text-muted mb-1">Billed To:</p>
                <h6 class="fw-bold"><?php echo $_SESSION['name']; ?></h6>
                <p class="small text-muted"><?php echo $_SESSION['email'] ?? ''; ?></p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Item Description</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $items_res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td class="text-center">₹<?php echo number_format($row['price'], 2); ?></td>
                    <td class="text-center"><?php echo $row['quantity']; ?></td>
                    <td class="text-end">₹<?php echo number_format($row['price'] * $row['quantity'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Grand Total</th>
                    <th class="text-end text-primary">₹<?php echo number_format($order['total_amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-5 text-center text-muted small">
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</div>
</body>
</html>