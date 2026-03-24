<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'];
$message = "";

// --- HANDLE ACTIONS ---

// 1. Toggle Visibility
if (isset($_GET['toggle_id'])) {
    $p_id = intval($_GET['toggle_id']);
    $current_status = intval($_GET['status']); 
    $new_status = ($current_status == 1) ? 0 : 1;
    
    $conn->query("UPDATE products SET is_active = $new_status WHERE id = $p_id AND seller_id = $seller_id");
    header("Location: inventory.php");
    exit();
}

// 2. Update Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $p_id = intval($_POST['product_id']);
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount_price']);

    if ($discount > 0 && $discount >= $price) {
        $message = '<div class="alert alert-warning border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>Offer price must be less than actual price!</div>';
    } else {
        $stmt = $conn->prepare("UPDATE products SET stock=?, price=?, discount_price=? WHERE id=? AND seller_id=?");
        $stmt->bind_param("iddii", $stock, $price, $discount, $p_id, $seller_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>Product data synced successfully!</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory | CartHub Seller</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .seller-nav { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; }
        .inventory-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
        
        .table thead th { 
            background: #f9fafb; font-size: 0.75rem; text-transform: uppercase; 
            letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 15px 20px;
        }
        .table td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        .table-img { width: 50px; height: 50px; object-fit: contain; border-radius: 12px; background: #fff; border: 1px solid #e2e8f0; }
        .price-offer { color: #0f172a; font-weight: 800; font-size: 1rem; }
        .price-original { color: #94a3b8; text-decoration: line-through; font-size: 0.8rem; }
        
        .stock-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; }
        .stock-in { background: #dcfce7; color: #15803d; }
        .stock-low { background: #ffedd5; color: #9a3412; }
        .stock-out { background: #fee2e2; color: #b91c1c; }

        .btn-action { width: 35px; height: 35px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg seller-nav sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fas fa-layer-group me-2"></i>CartHub Seller</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 small fw-bold"><?php echo htmlspecialchars($seller_name); ?></p>
                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.6rem;">INVENTORY MANAGER</span>
            </div>
            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                <?php echo strtoupper(substr($seller_name, 0, 1)); ?>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Inventory Management</h2>
                    <p class="text-muted mb-0">Control your stock levels, pricing, and visibility.</p>
                </div>
                <a href="add_product.php" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i>Add New Item
                </a>
            </div>

            <?php echo $message; ?>

            <div class="card inventory-card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>Listing Price</th>
                                <th>Inventory</th>
                                <th>Store Status</th>
                                <th>Offers</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM products WHERE seller_id = $seller_id ORDER BY id DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                                    $has_offer = ($row['discount_price'] > 0);
                                    
                                    // Stock UI logic
                                    if($row['stock'] <= 0) { $s_class = "stock-out"; $s_text = "Out of Stock"; }
                                    elseif($row['stock'] < 10) { $s_class = "stock-low"; $s_text = "Low: ".$row['stock']; }
                                    else { $s_class = "stock-in"; $s_text = "In Stock: ".$row['stock']; }
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../<?php echo $row['image']; ?>" class="table-img me-3">
                                        <div>
                                            <div class="fw-bold text-dark mb-0"><?php echo $row['name']; ?></div>
                                            <small class="text-muted">SKU-<?php echo 1000 + $row['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($has_offer): ?>
                                        <div class="price-offer">₹<?php echo number_format($row['discount_price'], 2); ?></div>
                                        <div class="price-original">₹<?php echo number_format($row['price'], 2); ?></div>
                                    <?php else: ?>
                                        <div class="price-offer">₹<?php echo number_format($row['price'], 2); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="stock-badge <?php echo $s_class; ?>"><?php echo $s_text; ?></span></td>
                                <td>
                                    <?php if($row['is_active']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Visible</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if($has_offer) {
                                        $p = round((($row['price'] - $row['discount_price']) / $row['price']) * 100);
                                        echo "<span class='text-danger fw-bold small'><i class='fas fa-arrow-down me-1'></i>$p%</span>";
                                    } else {
                                        echo "<span class='text-muted small'>None</span>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn-action border-0 bg-primary bg-opacity-10 text-primary me-2" 
                                            onclick='openEditModal(<?php echo json_encode($row); ?>)' title="Quick Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    
                                    <a href="inventory.php?toggle_id=<?php echo $row['id']; ?>&status=<?php echo $row['is_active']; ?>" 
                                       class="btn-action bg-light text-dark text-decoration-none border"
                                       title="<?php echo $row['is_active'] ? 'Hide from Store' : 'Make Live'; ?>">
                                        <i class="fas <?php echo $row['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">You haven't listed any products yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Update Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body px-4 pb-4">
                    <input type="hidden" name="product_id" id="edit_id">
                    <input type="hidden" name="update_product" value="1">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">PRODUCT NAME</label>
                        <input type="text" class="form-control border-0 bg-light fw-bold" id="edit_name" readonly>
                    </div>

                    <div class="row g-3">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">STOCK QUANTITY</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">REGULAR PRICE (₹)</label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control rounded-3" required>
                        </div>
                    </div>

                    <div class="p-3 rounded-4" style="background: #fffbeb; border: 1px solid #fde68a;">
                        <label class="form-label small fw-bold text-warning mb-2"><i class="fas fa-bolt me-1"></i> SPECIAL OFFER PRICE (₹)</label>
                        <input type="number" step="0.01" name="discount_price" id="edit_discount" class="form-control border-warning-subtle" placeholder="Set 0 to remove offer">
                        <div class="form-text text-warning-emphasis mt-2 small">Setting this lower than regular price adds a "Sale" badge automatically.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    function openEditModal(product) {
        document.getElementById('edit_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_stock').value = product.stock;
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_discount').value = product.discount_price;

        var myModal = new bootstrap.Modal(document.getElementById('editModal'));
        myModal.show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>