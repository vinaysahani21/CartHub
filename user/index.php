<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// --- DATA FETCHING ---
// 1. Cart Count
$cart_count = $conn->query("SELECT SUM(quantity) as q FROM cart WHERE user_id = $user_id")->fetch_assoc()['q'] ?? 0;

// 2. Categories
$categories_res = $conn->query("SELECT * FROM categories LIMIT 6");
$categories = [];
while($c = $categories_res->fetch_assoc()) { $categories[] = $c; }

// 3. Latest Products
$latest_products = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 8");

// 4. Often Bought
$often_bought = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY RAND() LIMIT 4");

// 5. Hero Slides
$slides_result = $conn->query("SELECT * FROM hero_slides ORDER BY id DESC");
$slides = [];
while($row = $slides_result->fetch_assoc()) { $slides[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>CartHub | Premium E-Commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; --light: #f8fafc; --accent: #f59e0b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; padding-top: 75px; overflow-x: hidden; }
        
        /* --- RESPONSIVE NAVBAR --- */
        .navbar-main { background: white; border-bottom: 1px solid #e2e8f0; transition: all 0.3s; z-index: 1050; }
        .search-container { position: relative; width: 100%; max-width: 500px; margin: 10px 0; }
        .search-input { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 50px; padding: 10px 20px 10px 45px; width: 100%; cursor: pointer; }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        
        /* Logo fitting */
        .navbar-brand img { max-height: 50px; width: auto; object-fit: contain; }

        /* --- HERO RESPONSIVENESS --- */
        .hero-slider .carousel-item { height: 500px; background: #000; }
        @media (max-width: 768px) { 
            .hero-slider .carousel-item { height: 400px; }
            .hero-caption { padding: 25px !important; border-radius: 20px !important; margin: 0 15px; }
            .hero-caption h1 { font-size: 1.8rem !important; }
        }
        .hero-slider img { object-fit: cover; opacity: 0.6; height: 100%; width: 100%; }
        .hero-caption { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 40px; border-radius: 24px; max-width: 450px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }

        /* --- CATEGORIES & CARDS --- */
        .cat-icon { width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; box-shadow: 0 8px 15px rgba(0,0,0,0.05); font-size: 1.5rem; color: var(--primary); transition: 0.3s; }
        @media (max-width: 576px) { .cat-icon { width: 60px; height: 60px; font-size: 1.2rem; } }

        .product-card { background: white; border-radius: 20px; overflow: hidden; transition: 0.3s; height: 100%; border: 1px solid #e2e8f0; }
        .p-img-container { height: 180px; padding: 15px; display: flex; align-items: center; justify-content: center; }
        .p-img-container img { max-height: 100%; max-width: 100%; object-fit: contain; }

        /* --- AD BANNER --- */
        .ad-banner { background: linear-gradient(45deg, #0f172a, #2563eb); border-radius: 24px; padding: 60px 20px; color: white; margin: 40px 0; }
        @media (max-width: 768px) { .ad-banner h2 { font-size: 2rem !important; } }

        footer { background: var(--dark); color: #94a3b8; padding: 60px 0 30px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-main fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-800 fs-3 text-dark" href="index.php">
                <span class="text-primary">Cart</span>Hub
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-3">
                <a href="cart.php" class="nav-link position-relative text-dark d-lg-none me-2">
                    <i class="fas fa-shopping-basket fs-4"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                    <i class="fas fa-bars-staggered fs-3"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navContent">
                <form class="d-flex mx-auto search-container mt-3 mt-lg-0" action="search.php">
                    <i class="fas fa-search search-icon"></i>
                    <input class="search-input" type="text" placeholder="Search brands, products..." readonly onclick="window.location.href='search.php'"> 
                </form>

                <ul class="navbar-nav ms-auto align-items-center gap-3 mt-3 mt-lg-0">
                    <li class="nav-item d-none d-lg-block">
                        <a href="cart.php" class="nav-link position-relative text-dark">
                            <i class="fas fa-shopping-basket fs-4"></i>
                            <?php if($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown w-100 text-center text-lg-start">
                        <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-center gap-2 text-dark fw-bold" href="#" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <span>Hello, <?php echo explode(' ', $user_name)[0]; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 rounded-4 mt-2">
                            <li><a class="dropdown-item py-2" href="my_orders.php"><i class="fas fa-box me-2 text-muted"></i>My Orders</a></li>
                            <li><a class="dropdown-item py-2" href="profile.php"><i class="fas fa-user me-2 text-muted"></i>Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="../auth/logout.php"><i class="fas fa-power-off me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="heroCarousel" class="carousel slide hero-slider mb-5" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($slides as $index => $slide): 
                $img = strpos($slide['image_path'], 'http') === 0 ? $slide['image_path'] : "../" . $slide['image_path'];
            ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo $img; ?>" alt="Promo Banner">
                <div class="container h-100">
                    <div class="d-flex align-items-center justify-content-center justify-content-lg-start h-100">
                        <div class="hero-caption text-center text-lg-start" data-aos="fade-right">
                            <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Limited Offer</span>
                            <h1 class="display-5 fw-800 mb-3"><?php echo htmlspecialchars($slide['title']); ?></h1>
                            <p class="text-secondary mb-4"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                            <a href="search.php" class="btn btn-dark px-5 py-3 rounded-pill fw-bold">Shop Collection</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container">
        <div class="mb-5 py-2" data-aos="fade-up">
            <h4 class="fw-800 mb-4 text-center text-md-start">Top Categories</h4>
            <div class="row g-3 g-md-4 text-center justify-content-center">
                <?php 
                $icons = ['laptop', 'tshirt', 'mobile-alt', 'headphones', 'camera', 'gamepad'];
                foreach($categories as $key => $cat): 
                ?>
                <div class="col-4 col-md-2">
                    <a href="search.php?q=<?php echo urlencode($cat['name']); ?>" class="cat-item text-decoration-none">
                        <div class="cat-icon"><i class="fas fa-<?php echo $icons[$key % count($icons)]; ?>"></i></div>
                        <p class="fw-bold small mb-0"><?php echo $cat['name']; ?></p>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <section class="mb-5" data-aos="fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h3 class="fw-800 mb-0">Best Sellers ✨</h3>
                <a href="search.php" class="text-primary fw-bold text-decoration-none small">Explore All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="row g-3 g-md-4">
                <?php while($row = $often_bought->fetch_assoc()): 
                    $price = $row['discount_price'] > 0 ? $row['discount_price'] : $row['price'];
                    $img = !empty($row['image']) ? "../".$row['image'] : "https://via.placeholder.com/300";
                ?>
                <div class="col-6 col-md-3">
                    <div class="product-card shadow-sm" onclick="window.location.href='product_details.php?id=<?php echo $row['id']; ?>'" style="cursor: pointer;">
                        <div class="p-img-container">
                            <img src="<?php echo $img; ?>" alt="Product">
                        </div>
                        <div class="p-3">
                            <h6 class="fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($row['name']); ?></h6>
                            <p class="text-primary fw-800 mb-0 small">₹<?php echo number_format($price, 0); ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <div class="ad-banner text-center shadow-lg" data-aos="zoom-in">
            <h5 class="text-warning fw-bold text-uppercase ls-2 mb-2" style="font-size: 0.8rem;">Exclusive Launch</h5>
            <h2 class="display-4 fw-800 mb-3 text-white">Next-Gen Gadgets</h2>
            <p class="mb-4 opacity-75 d-none d-md-block">Experience technology like never before with our premium electronics range.</p>
            <a href="search.php?q=Electronics" class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold text-primary border-0">Discover Now</a>
        </div>
    </div>

    <footer>
        <div class="container text-center text-md-start">
            <div class="row g-4">
                <div class="col-md-6">
                    <h4 class="text-white fw-800 mb-3">CartHub</h4>
                    <p class="small opacity-75">Your trusted partner for premium shopping in India. Curated products, secure checkout, and lightning fast delivery.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-white fw-bold mb-3">Newsletter</h6>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control bg-dark border-0 text-white rounded-pill-start" placeholder="Email address">
                        <button class="btn btn-primary px-4 rounded-pill-end">Join</button>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <p class="small mb-0 text-center opacity-50">&copy; 2026 CartHub Inc. Developed with passion in Surat, India.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        
        // Dynamic Navbar Shrink
        window.onscroll = function() {
            var nav = document.querySelector('.navbar-main');
            if (window.pageYOffset > 50) {
                nav.style.padding = "5px 0";
            } else {
                nav.style.padding = "10px 0";
            }
        };
    </script>
</body>
</html>