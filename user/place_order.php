<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$payment_id = $_GET['payment_id'] ?? 'DUMMY_'.time();

// 1. Calculate Grand Total again for security
$res = $conn->query("SELECT c.product_id, c.quantity, p.price, p.discount_price, p.seller_id 
                    FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");

$total = 0;
$items = [];
while($row = $res->fetch_assoc()) {
    $price = ($row['discount_price'] > 0) ? $row['discount_price'] : $row['price'];
    $total += ($price * $row['quantity']);
    $items[] = $row;
}

if(empty($items)) { header("Location: index.php"); exit(); }

// 2. Start Transaction
$conn->begin_transaction();

try {
    // A. Insert into Orders Table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status) VALUES (?, ?, 'completed')");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // B. Insert into Order_Items Table
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    
    foreach($items as $item) {
        $final_p = ($item['discount_price'] > 0) ? $item['discount_price'] : $item['price'];
        $item_stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $final_p);
        $item_stmt->execute();

        // C. Reduce Stock
        $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
    }

    // D. Clear Cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    $conn->commit();
    $success = true;
} catch (Exception $e) {
    $conn->rollback();
    die("Error placing order: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5 text-center">
        <div class="card border-0 shadow-lg p-5 mx-auto" style="max-width: 500px; border-radius: 20px;">
            <img src="https://cdn-icons-png.flaticon.com/512/148/148767.png" width="80" class="mx-auto mb-4">
            <h2 class="fw-bold text-success">Order Placed!</h2>
            <p class="text-muted">Payment ID: <?php echo $payment_id; ?></p>
            <p>Your order #<?php echo $order_id; ?> has been successfully placed. Sellers have been notified.</p>
            <a href="index.php" class="btn btn-dark rounded-pill px-5">Continue Shopping</a>
        </div>
    </div>
</body>
</html>