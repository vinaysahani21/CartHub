<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
// Assuming email is in session, otherwise provide a fallback for Razorpay
$user_email = $_SESSION['email'] ?? "customer@example.com"; 

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$p_id = intval($_GET['id']);

// 1. Fetch Cart Count for Nav
$cart_count = $conn->query("SELECT SUM(quantity) as q FROM cart WHERE user_id = $user_id")->fetch_assoc()['q'] ?? 0;

// 2. Fetch Product Details
$sql = "SELECT p.*, c.name as cat_name FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id = $p_id AND p.is_active = 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='container mt-5 alert alert-danger'>Product not found. <a href='index.php'>Go Back</a></div>";
    exit();
}

$product = $result->fetch_assoc();
$cat_id = $product['category_id'];

// Determine current price for Razorpay
$current_price = ($product['discount_price'] > 0) ? $product['discount_price'] : $product['price'];

// 3. Fetch Related Products
$related_sql = "SELECT * FROM products WHERE category_id = $cat_id 
                AND id != $p_id AND is_active = 1 LIMIT 4";
$related_result = $conn->query($related_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $product['name']; ?> - ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        :root { --primary: #2563eb; --dark: #0f172a; }
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; padding-top: 80px; }
        .navbar-main { background: white; box-shadow: 0 1px 10px rgba(0,0,0,0.05); padding: 0.8rem 0; height: 70px; }
        .search-container { position: relative; width: 100%; max-width: 600px; }
        .search-input { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 50px; padding: 10px 20px 10px 45px; width: 100%; cursor: text; }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .product-img-main { width: 100%; max-height: 500px; object-fit: contain; background: white; border-radius: 20px; }
        .related-card { transition: transform 0.2s; border: none; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .related-card img { height: 150px; object-fit: contain; }
        .breadcrumb-item a { text-decoration: none; color: #64748b; font-weight: 500; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-main fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-dark me-5" href="index.php">
                CartHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <form class="d-flex mx-auto search-container my-3 my-lg-0" action="search.php">
                    <i class="fas fa-search search-icon"></i>
                    <input class="search-input" type="text" placeholder="Search for products..." readonly onclick="window.location.href='search.php'"> 
                </form>
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link position-relative text-dark">
                            <i class="fas fa-shopping-cart fs-5"></i>
                            <?php if($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-dark" href="#" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                <?php echo substr($user_name, 0, 1); ?>
                            </div>
                            <span class="fw-bold small d-none d-md-block"><?php echo explode(' ', $user_name)[0]; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 rounded-3">
                            <li><a class="dropdown-item rounded" href="my_orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item rounded text-danger" href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="#"><?php echo $product['cat_name']; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-md-6">
                <div class="p-4 bg-white rounded-4 shadow-sm border">
                    <img src="../<?php echo $product['image']; ?>" class="product-img-main" alt="Product">
                </div>
            </div>

            <div class="col-md-6">
                <h1 class="fw-bold mb-2"><?php echo $product['name']; ?></h1>
                <div class="mb-3 text-warning small">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    <span class="text-muted ms-2">(4.5 Rating)</span>
                </div>

                <div class="mb-4">
                    <?php if ($product['discount_price'] > 0): ?>
                        <span class="fs-2 fw-bold text-primary">₹<?php echo number_format($product['discount_price'], 2); ?></span>
                        <span class="text-muted text-decoration-line-through ms-3 fs-5">₹<?php echo number_format($product['price'], 2); ?></span>
                    <?php else: ?>
                        <span class="fs-2 fw-bold text-primary">₹<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>

                <p class="text-secondary mb-4" style="line-height: 1.7;"><?php echo $product['description']; ?></p>

                <hr class="my-4">

                <div class="d-flex gap-3">
                    <div style="width: 80px;">
                        <input type="number" id="buy_qty" name="quantity" class="form-control py-2 text-center" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                    
                    <form action="../cart_action.php" method="POST" class="flex-grow-1">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-outline-dark w-100 py-2 fw-bold">
                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                        </button>
                    </form>

                    <button type="button" onclick="payNow()" class="btn btn-primary flex-grow-1 py-2 fw-bold">
                        Buy Now
                    </button>
                </div>

                <div class="mt-4 p-3 bg-light rounded-3 small text-muted d-flex align-items-center">
                    <i class="fas fa-shield-check text-success fs-4 me-3"></i>
                    <div>
                        <strong>100% Quality Assurance</strong><br>
                        Secure payments and easy returns.
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Similar Items You May Like</h4>
                <a href="search.php?q=<?php echo urlencode($product['cat_name']); ?>" class="btn btn-sm btn-link text-decoration-none fw-bold">View More</a>
            </div>
            
            <div class="row g-4">
                <?php while($rel = $related_result->fetch_assoc()): 
                    $rel_img = !empty($rel['image']) ? "../" . $rel['image'] : "https://via.placeholder.com/150";
                ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 related-card shadow-sm p-3">
                        <a href="product_details.php?id=<?php echo $rel['id']; ?>">
                            <img src="<?php echo $rel_img; ?>" class="card-img-top p-2" alt="Related">
                        </a>
                        <div class="card-body px-0 pb-0">
                            <h6 class="fw-bold text-truncate mb-1"><?php echo $rel['name']; ?></h6>
                            <div class="text-primary fw-bold">₹<?php echo number_format($rel['price'], 2); ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
    function payNow() {
        const qty = document.getElementById('buy_qty').value;
        const basePrice = <?php echo $current_price; ?>;
        const amount = (basePrice * qty) * 100; // In Paisa

        var options = {
            "key": "rzp_test_SPRbM48uGd6FEp", // REPLACE WITH YOUR KEY
            "amount": amount,
            "currency": "INR",
            "name": "ShopEase Store",
            "description": "Buying <?php echo $product['name']; ?>",
            "handler": function (response){
                // On success, redirect to place_order with product details
                window.location.href = "place_order.php?payment_id=" + response.razorpay_payment_id + 
                                       "&direct_p_id=<?php echo $product['id']; ?>&qty=" + qty;
            },
            "prefill": {
                "name": "<?php echo $user_name; ?>",
                "email": "<?php echo $user_email; ?>"
            },
            "theme": { "color": "#2563eb" }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
    }
    </script>

    <footer class="bg-dark text-white-50 py-5 mt-5">
        <div class="container text-center">
            <p class="mb-0 small">&copy; 2026 ShopEase Inc. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>