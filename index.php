<?php
include 'config/db.php'; // Ensure path is correct

// --- 1. FETCH DYNAMIC DATA ---
// Fetch Categories for the icons (simulated mapping)
$categories_res = $conn->query("SELECT * FROM categories LIMIT 4");

// Fetch 8 Featured Products from Database
$products_res = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEase | Modern E-Commerce</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="./assets/icon.jpeg" type="image/x-icon">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }

        /* Navbar Styling - Cleaned */
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--secondary-color) !important;
            font-size: 1.5rem;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
            padding: 100px 0;
        }
        .hero-title {
            font-weight: 800;
            color: var(--secondary-color);
            font-size: 3.5rem;
            letter-spacing: -1px;
        }

        /* Product Cards - Real Data Styling */
        .card-product {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: white;
            transition: 0.3s;
            overflow: hidden;
            height: 100%;
        }
        .card-product:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        .product-img-wrapper {
            height: 220px;
            background: #fff;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .price-tag {
            font-weight: 800;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: #94a3b8;
            padding: 80px 0 30px;
        }
    </style>
</head>
<body>

    <nav class="navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><span class="text-primary">Cart</span>Hub</a>
            
            <div class="d-flex align-items-center gap-3">
                <a href="auth/login.php" class="btn btn-link text-decoration-none text-dark fw-bold">Login</a>
                <a href="auth/register.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Get Started</a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-bold">India's Modern Marketplace</span>
                    <h1 class="hero-title mb-4">Quality Products, <br>Unbeatable Prices.</h1>
                    <p class="lead text-muted mb-5">Join thousands of shoppers discovering premium electronics, fashion, and lifestyle items every day.</p>
                    <div class="d-flex gap-3">
                        <a href="auth/register.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">Start Shopping</a>
                        <a href="seller/register.php" class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold">Sell Online</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center d-none d-lg-block">
                    <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=800&q=80" alt="Shopping" class="img-fluid rounded-5 shadow-2xl">
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="products">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-800 text-dark">Trending Now</h2>
                <p class="text-muted">Discover our most popular products recently added.</p>
            </div>

            <div class="row g-4">
                <?php if($products_res->num_rows > 0): ?>
                    <?php while($row = $products_res->fetch_assoc()): 
                        $img = !empty($row['image']) ? $row['image'] : "assets/placeholder.png";
                        $price = ($row['discount_price'] > 0) ? $row['discount_price'] : $row['price'];
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card card-product">
                            <div class="product-img-wrapper">
                                <img src="<?php echo $img; ?>" class="product-img" alt="<?php echo $row['name']; ?>">
                            </div>
                            <div class="card-body">
                                <h6 class="text-dark fw-bold text-truncate mb-2"><?php echo htmlspecialchars($row['name']); ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">₹<?php echo number_format($price, 0); ?></span>
                                    <a href="auth/login.php" class="btn btn-sm btn-light rounded-circle text-primary"><i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">No products available at the moment.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h5 class="text-white fw-bold mb-4">ShopEase</h5>
                    <p class="small">The ultimate e-commerce platform connecting buyers and sellers with seamless technology and secure payments.</p>
                    <div class="mt-4 d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <h6 class="text-white fw-bold mb-4">Company</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="auth/login.php" class="footer-link">Log In</a></li>
                        <li><a href="auth/register.php" class="footer-link">Sign Up</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white fw-bold mb-4">Trusted Worldwide</h6>
                    <p class="small">Subscribe for the latest updates on flash sales and new arrivals.</p>
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark border-0 text-white rounded-pill-start" placeholder="Email">
                        <button class="btn btn-primary rounded-pill-end">Join</button>
                    </div>
                </div>
            </div>
            <div class="text-center pt-5 mt-5 border-top border-secondary small">
                © 2026 ShopEase India. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>