<?php
include 'auth_check.php';
include '../config/db.php';

if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);
    $user_id = $_SESSION['user_id'];

    // Ensure the item belongs to the user and is still 'pending'
    $check = $conn->query("SELECT oi.product_id, oi.quantity FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.id 
                           WHERE oi.id = $item_id AND o.user_id = $user_id AND oi.status = 'pending'");

    if ($check->num_rows > 0) {
        $data = $check->fetch_assoc();
        $p_id = $data['product_id'];
        $qty = $data['quantity'];

        $conn->begin_transaction();
        try {
            // 1. Update status
            $conn->query("UPDATE order_items SET status = 'cancelled' WHERE id = $item_id");
            // 2. Restore Stock
            $conn->query("UPDATE products SET stock = stock + $qty WHERE id = $p_id");
            
            $conn->commit();
            header("Location: my_orders.php?msg=cancelled");
        } catch (Exception $e) {
            $conn->rollback();
            die("Error processing cancellation.");
        }
    } else {
        die("Item cannot be cancelled at this stage.");
    }
}
?>