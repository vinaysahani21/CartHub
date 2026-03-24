<?php
include '../config/db.php';
session_start();

// --- 1. AJAX HANDLER (For Live Suggestions) ---
if (isset($_GET['ajax']) && isset($_GET['q'])) {
    $search = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT id, name, image, price FROM products 
            WHERE (name LIKE '%$search%' OR tags LIKE '%$search%') 
            AND is_active = 1 LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<div class="list-group shadow-lg border-0">';
        while ($row = $result->fetch_assoc()) {
            $img = !empty($row['image']) ? "../" . $row['image'] : "https://via.placeholder.com/50";
            echo '
            <a href="search.php?q=' . urlencode($row['name']) . '" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 border-bottom">
                <img src="' . $img . '" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                <div>
                    <div class="fw-bold text-dark small">' . $row['name'] . '</div>
                    <div class="text-primary small fw-bold">₹' . number_format($row['price'], 0) . '</div>
                </div>
            </a>';
        }
        echo '</div>';
    } else {
        echo '<div class="list-group shadow-lg"><div class="list-group-item text-muted small p-3">No matches found</div></div>';
    }
    exit();
}

// --- 2. SEARCH & FILTER LOGIC ---
$search_query = $_GET['q'] ?? "";
$sort = $_GET['sort'] ?? "latest";
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 100000;

$order_by = match($sort) {
    'low' => 'price ASC',
    'high' => 'price DESC',
    'oldest' => 'id ASC',
    default => 'id DESC'
};

$search_results = null;
if ($search_query !== "") {
    $q = $conn->real_escape_string($search_query);
    $sql_full = "SELECT * FROM products 
                 WHERE (name LIKE '%$q%' OR description LIKE '%$q%' OR tags LIKE '%$q%')
                 AND (price BETWEEN $min_price AND $max_price)
                 AND is_active = 1 
                 ORDER BY $order_by";
    $search_results = $conn->query($sql_full);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Results - ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Modern Search Nav */
        .navbar-search { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .search-wrapper { position: relative; width: 100%; max-width: 600px; }
        .search-input {
            border-radius: 12px;
            padding: 12px 20px 12px 50px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.3s;
        }
        .search-input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        
        /* Suggestions */
        #suggestion-box { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; margin-top: 8px; border-radius: 12px; overflow: hidden; }

        /* Filter Sidebar */
        .filter-card { border: none; border-radius: 20px; background: white; position: sticky; top: 100px; }

        /* Product Cards */
        .product-card { border: none; border-radius: 18px; transition: 0.3s; overflow: hidden; background: white; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(0,0,0,0.08); }
        
        /* Out of Stock Logic */
        .out-of-stock { position: relative; }
        .out-of-stock .p-img-wrap { filter: blur(3px) grayscale(1); opacity: 0.6; }
        .out-of-stock .card-body { opacity: 0.7; }
        .oos-badge {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8); color: white; padding: 8px 15px;
            border-radius: 8px; font-weight: 700; font-size: 0.8rem; z-index: 10;
            letter-spacing: 1px; white-space: nowrap;
        }

        .p-img-wrap { height: 200px; padding: 20px; background: #fff; display: flex; align-items: center; justify-content: center; }
        .p-img-wrap img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .price-text { font-weight: 800; color: #0f172a; font-size: 1.2rem; }
    </style>
</head>
<body>

    <div class="navbar-search sticky-top mb-4">
        <div class="container d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-light rounded-circle shadow-sm"><i class="fas fa-chevron-left"></i></a>
            
            <div class="flex-grow-1 search-wrapper">
                <form action="search.php" method="GET" autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" id="search-input" class="form-control search-input" 
                           placeholder="Search brands or products..." value="<?php echo htmlspecialchars($search_query); ?>"
                           onkeyup="fetchSuggestions(this.value)">
                </form>
                <div id="suggestion-box"></div>
            </div>

            <a href="cart.php" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm d-none d-md-block">
                <i class="fas fa-shopping-cart me-2"></i>Cart
            </a>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="card filter-card p-4 shadow-sm">
                    <h6 class="fw-bold mb-4"><i class="fas fa-sliders-h me-2"></i>Filters</h6>
                    <form action="search.php" method="GET">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">SORT BY</label>
                            <select name="sort" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                <option value="latest" <?php if($sort=='latest') echo 'selected';?>>Newest Arrivals</option>
                                <option value="low" <?php if($sort=='low') echo 'selected';?>>Price: Low to High</option>
                                <option value="high" <?php if($sort=='high') echo 'selected';?>>Price: High to Low</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">PRICE RANGE (₹)</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?php echo $min_price; ?>">
                                <span class="text-muted">-</span>
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?php echo $max_price; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Apply Filters</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-9">
                <?php if ($search_query): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Results for "<span class="text-primary"><?php echo htmlspecialchars($search_query); ?></span>"</h5>
                        <p class="text-muted mb-0 small"><?php echo $search_results ? $search_results->num_rows : 0; ?> items found</p>
                    </div>

                    <div class="row g-4">
                        <?php 
                        if ($search_results && $search_results->num_rows > 0): 
                            while ($row = $search_results->fetch_assoc()):
                                $img = !empty($row['image']) ? "../" . $row['image'] : "https://via.placeholder.com/300";
                                $price = $row['discount_price'] > 0 ? $row['discount_price'] : $row['price'];
                                $is_out_of_stock = ($row['stock'] <= 0);
                        ?>
                        <div class="col-6 col-md-4">
                            <div class="card product-card h-100 shadow-sm <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>">
                                <?php if($is_out_of_stock): ?>
                                    <div class="oos-badge">OUT OF STOCK</div>
                                <?php endif; ?>

                                <a href="product_details.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                                    <div class="p-img-wrap">
                                        <img src="<?php echo $img; ?>" alt="Product">
                                    </div>
                                </a>

                                <div class="card-body d-flex flex-column pt-0">
                                    <p class="text-muted small mb-1 text-uppercase ls-1" style="font-size: 0.65rem;">Verified Seller</p>
                                    <h6 class="fw-bold text-dark text-truncate mb-2"><?php echo $row['name']; ?></h6>
                                    
                                    <div class="mb-3">
                                        <span class="price-text">₹<?php echo number_format($price, 0); ?></span>
                                        <?php if($row['discount_price'] > 0): ?>
                                            <span class="text-muted text-decoration-line-through small ms-2">₹<?php echo number_format($row['price'], 0); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if(!$is_out_of_stock): ?>
                                        <form action="../cart_action.php" method="POST" class="mt-auto">
                                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                            <button class="btn btn-outline-dark w-100 btn-sm py-2 rounded-pill fw-bold">
                                                <i class="fas fa-plus me-1"></i> Add to Cart
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-light w-100 btn-sm py-2 rounded-pill fw-bold disabled mt-auto">Unavailable</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="100" class="mb-4 opacity-25">
                                <h4 class="fw-bold">No products found</h4>
                                <p class="text-muted">Adjust your filters or try a different keyword.</p>
                                <a href="search.php?q=a" class="btn btn-primary rounded-pill px-4 mt-2">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center mt-5 pt-5">
                        <div class="bg-white d-inline-flex p-4 rounded-circle shadow-sm mb-4">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h4 class="fw-bold">What are you looking for?</h4>
                        <p class="text-muted">Type above to discover amazing deals across our store.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function fetchSuggestions(query) {
            const box = document.getElementById('suggestion-box');
            if (query.length < 2) { box.innerHTML = ''; return; }

            fetch(`search.php?ajax=1&q=${query}`)
                .then(res => res.text())
                .then(data => box.innerHTML = data)
                .catch(err => console.error(err));
        }

        document.addEventListener('click', function(e) {
            if (!document.querySelector('.search-wrapper').contains(e.target)) {
                document.getElementById('suggestion-box').innerHTML = '';
            }
        });
    </script>

</body>
</html>