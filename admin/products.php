<?php
include 'auth_check.php';
include '../config/db.php';

$message = "";

// --- 1. HANDLE ACTIONS ---
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $current_status = intval($_GET['status']);
    $new_status = ($current_status == 1) ? 0 : 1;
    $conn->query("UPDATE products SET is_active = $new_status WHERE id = $id");
    header("Location: products.php"); 
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    $message = '<div class="alert alert-success border-0 shadow-sm rounded-4"><i class="fas fa-check-circle me-2"></i>Product removed from global directory.</div>';
}

// --- 2. FETCH DATA ---
$sql = "SELECT p.*, u.name as seller_name, c.name as category_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.id 
        JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Global Inventory | CartHub Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-blue: #2563eb; --soft-bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--soft-bg); color: #1e293b; }
        
        /* Modern UI Elements */
        .glass-search { background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 15px; margin-bottom: 30px; }
        .product-card-wrap { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
        
        /* Table Styling */
        .table thead th { background: #f9fafb; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 15px 20px; }
        .table td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        .prod-img-box { width: 50px; height: 50px; border-radius: 12px; object-fit: contain; background: white; border: 1px solid #e2e8f0; padding: 5px; }
        
        /* Stock Logic */
        .stock-indicator { font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 8px; }
        .in-stock { background: #dcfce7; color: #15803d; }
        .low-stock { background: #fff7ed; color: #c2410c; }
        .out-stock { background: #fee2e2; color: #b91c1c; }

        /* Status Toggle */
        .form-switch .form-check-input { width: 2.8em; height: 1.4em; cursor: pointer; }
        .form-check-input:checked { background-color: var(--primary-blue); border-color: var(--primary-blue); }

        .btn-action-round { width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; border: 1px solid #e2e8f0; background: white; color: #64748b; }
        .btn-action-round:hover { background: #fee2e2; color: #ef4444; border-color: #fee2e2; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-800 text-dark mb-1">Product Directory</h2>
                    <p class="text-muted small mb-0">Global management of all seller listings and stock status.</p>
                </div>
                <div class="text-end">
                    <div class="bg-white border rounded-pill px-4 py-2 shadow-sm fw-bold">
                        <i class="fas fa-cubes text-primary me-2"></i><?php echo $result->num_rows; ?> Total Items
                    </div>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="glass-search shadow-sm">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0 bg-transparent" 
                           placeholder="Search by product name, seller, or category ID..." 
                           onkeyup="filterTable()">
                </div>
            </div>

            <div class="card product-card-wrap shadow-sm">
                <div class="table-responsive">
                    <table class="table mb-0" id="productTable">
                        <thead>
                            <tr>
                                <th class="ps-4">Item Identity</th>
                                <th>Category</th>
                                <th>Listing Price</th>
                                <th>Stock Health</th>
                                <th>Visibility</th>
                                <th class="text-end pe-4">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $img = !empty($row['image']) ? "../".$row['image'] : "https://via.placeholder.com/100";
                                    
                                    // Stock Logic
                                    $stock = $row['stock'];
                                    if($stock <= 0) { $stock_class = "out-stock"; $stock_text = "Out of Stock"; }
                                    elseif($stock < 10) { $stock_class = "low-stock"; $stock_text = "Low: $stock Left"; }
                                    else { $stock_class = "in-stock"; $stock_text = "$stock in Stock"; }
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo $img; ?>" class="prod-img-box shadow-sm">
                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($row['name']); ?></div>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Seller: <span class="fw-bold"><?php echo htmlspecialchars($row['seller_name']); ?></span></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold border rounded-pill px-3"><?php echo strtoupper($row['category_name']); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-800 text-dark">₹<?php echo number_format($row['price'], 2); ?></div>
                                        <?php if($row['discount_price'] > 0): ?>
                                            <small class="text-danger fw-bold">₹<?php echo number_format($row['discount_price'], 2); ?> Offer</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="stock-indicator <?php echo $stock_class; ?>">
                                            <i class="fas fa-circle me-1" style="font-size: 0.4rem;"></i> <?php echo $stock_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input" type="checkbox" 
                                                   onclick="window.location.href='products.php?toggle=<?php echo $row['id']; ?>&status=<?php echo $row['is_active']; ?>'"
                                                   <?php echo $row['is_active'] ? 'checked' : ''; ?>>
                                            <span class="ms-2 small fw-bold <?php echo $row['is_active'] ? 'text-primary' : 'text-muted'; ?>">
                                                <?php echo $row['is_active'] ? 'LIVE' : 'HIDDEN'; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="../user/product_details.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-action-round" title="View in Store">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <a href="products.php?delete=<?php echo $row['id']; ?>" 
                                               class="btn-action-round" 
                                               onclick="return confirm('DANGER: Delete this product permanently from the database?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No products have been listed on CartHub yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("productTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            const rowText = tr[i].textContent || tr[i].innerText;
            if (rowText.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>