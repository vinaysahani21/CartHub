<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch Cart Items with a secure prepared statement
$sql = "SELECT c.id as cart_id, c.quantity, p.name, p.price, p.discount_price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Cart | CartHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --light-bg: #f8fafc;
        }
        
        body { background-color: var(--light-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
        
        .cart-card { background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; }
        .cart-img { width: 90px; height: 90px; object-fit: contain; border-radius: 15px; background: #fff; border: 1px solid #f1f5f9; padding: 5px; }
        
        /* Quantity Controls */
        .qty-wrapper { background: #f1f5f9; border-radius: 12px; padding: 5px; display: inline-flex; align-items: center; }
        .qty-btn { width: 32px; height: 32px; border-radius: 10px; border: none; background: white; color: var(--dark); display: flex; align-items: center; justify-content: center; transition: 0.2s; text-decoration: none; }
        .qty-btn:hover { background: var(--primary); color: white; }
        
        .summary-card { background: white; border-radius: 24px; border: 1px solid #e2e8f0; position: sticky; top: 100px; }
        .table thead th { background: #f9fafb; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 15px 20px; }
        
        .remove-link { color: #94a3b8; transition: 0.3s; }
        .remove-link:hover { color: #ef4444; }
        
        .price-text { font-weight: 800; color: var(--dark); }
        .discount-tag { color: #94a3b8; text-decoration: line-through; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-white border shadow-sm rounded-circle me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left text-dark"></i>
            </a>
            <div>
                <h2 class="fw-800 mb-0">Your Bag</h2>
                <p class="text-muted small mb-0"><?php echo $result->num_rows; ?> items ready for checkout</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="cart-card shadow-sm">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product Details</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th class="pe-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $price = ($row['discount_price'] > 0) ? $row['discount_price'] : $row['price'];
                                    $subtotal = $price * $row['quantity'];
                                    $grand_total += $subtotal;
                                    $img = !empty($row['image']) ? "../" . $row['image'] : "https://via.placeholder.com/100";
                                ?>
                                <tr>
                                    <td class="ps-4 py-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo $img; ?>" class="cart-img" alt="Product">
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($row['name']); ?></h6>
                                                <span class="badge bg-light text-muted fw-normal rounded-pill px-2">SKU-<?php echo 1000 + $row['cart_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-text">₹<?php echo number_format($price, 2); ?></div>
                                        <?php if($row['discount_price'] > 0): ?>
                                            <div class="discount-tag">₹<?php echo number_format($row['price'], 2); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="qty-wrapper shadow-sm">
                                            <a href="../cart_action.php?action=dec&id=<?php echo $row['cart_id']; ?>" class="qty-btn"><i class="fas fa-minus small"></i></a>
                                            <span class="px-3 fw-800 small"><?php echo $row['quantity']; ?></span>
                                            <a href="../cart_action.php?action=inc&id=<?php echo $row['cart_id']; ?>" class="qty-btn"><i class="fas fa-plus small"></i></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-text text-primary">₹<?php echo number_format($subtotal, 2); ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="../cart_action.php?remove=<?php echo $row['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove this product?')">
                                            <i class="fas fa-times-circle fs-5"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="120" class="opacity-25 mb-4">
                        <h4 class="fw-bold">Your bag is empty</h4>
                        <p class="text-muted mb-4">Seems like you haven't added anything yet.</p>
                        <a href="index.php" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card shadow-sm p-4">
                <h5 class="fw-800 mb-4">Summary</h5>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted fw-bold small uppercase">Price Details</span>
                    <span class="text-muted small"><?php echo $result->num_rows; ?> Items</span>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Bag Total</span>
                    <span class="fw-bold">₹<?php echo number_format($grand_total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Shipping Fee</span>
                    <span class="text-success fw-bold">FREE</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary small">Estimated Tax (5%)</span>
                    <span class="fw-bold text-dark">₹<?php echo number_format($grand_total * 0.05, 2); ?></span>
                </div>
                
                <hr class="my-4 border-dashed">
                
                <div class="d-flex justify-content-between mb-4 align-items-end">
                    <span class="fw-800 fs-5">Total Amount</span>
                    <span class="fw-800 fs-3 text-primary">₹<?php echo number_format($grand_total * 1.05, 0); ?></span>
                </div>

                <a href="checkout.php" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-lg <?php echo ($grand_total == 0) ? 'disabled' : ''; ?>">
                    Checkout Now <i class="fas fa-arrow-right ms-2"></i>
                </a>
                
                <div class="mt-4 p-3 bg-light rounded-4 d-flex align-items-center gap-3">
                    <div class="bg-white p-2 rounded-3 shadow-sm">
                        <i class="fas fa-shield-halved text-success"></i>
                    </div>
                    <small class="text-muted fw-bold" style="font-size: 0.7rem;">100% Secure Payments with Razorpay Encryption</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>