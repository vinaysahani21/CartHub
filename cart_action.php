<?php
session_start();
include 'config/db.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- ACTION: ADD TO CART ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $p_id = intval($_POST['product_id']);
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Check if product is already in cart
    $check_sql = "SELECT id FROM cart WHERE user_id = $user_id AND product_id = $p_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Product exists -> Update Quantity
        $sql = "UPDATE cart SET quantity = quantity + $qty WHERE user_id = $user_id AND product_id = $p_id";
    } else {
        // New Product -> Insert Row
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $p_id, $qty)";
    }

    if ($conn->query($sql)) {
        // Redirect back to where they came from (Store or Search page)
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}

// --- ACTION: UPDATE QUANTITY (Inc/Dec) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $cart_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'inc') {
        $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE id = $cart_id AND user_id = $user_id");
    } elseif ($action == 'dec') {
        // Check current qty first so we don't go below 1
        $curr = $conn->query("SELECT quantity FROM cart WHERE id = $cart_id")->fetch_assoc()['quantity'];
        if ($curr > 1) {
            $conn->query("UPDATE cart SET quantity = quantity - 1 WHERE id = $cart_id AND user_id = $user_id");
        }
    }
    
    header("Location: user/cart.php");
    exit();
}

// --- ACTION: REMOVE ITEM ---
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    
    header("Location: user/cart.php");
    exit();
}
?>