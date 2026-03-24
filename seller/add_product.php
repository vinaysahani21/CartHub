<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $price = $_POST['price'];
    $category = $_POST['category_id'];
    $desc = $conn->real_escape_string($_POST['description']);
    $stock = $_POST['stock'];
    $tags = $conn->real_escape_string($_POST['tags']);
    
    $target_dir = "../assets/uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $filename = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $filename;
    $uploadOk = 1;
    
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check === false) {
        $message = '<div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>File is not an image.</div>';
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $db_image_path = "assets/uploads/" . $filename;
            $sql = "INSERT INTO products (seller_id, category_id, name, price, stock, image, description, tags) 
                    VALUES ('$seller_id', '$category', '$name', '$price', '$stock', '$db_image_path', '$desc', '$tags')";

            if ($conn->query($sql) === TRUE) {
                $message = '<div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>Product published successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>';
            } else {
                $message = '<div class="alert alert-danger">Database Error: ' . $conn->error . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add New Listing | ShopEase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .seller-nav { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; }
        .form-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background: #fdfdfd; transition: 0.3s; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .input-group-text { border-radius: 12px 0 0 12px; background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b; font-weight: 600; }
        .price-input { border-radius: 0 12px 12px 0 !important; }
        .preview-box { height: 300px; border: 2px dashed #e2e8f0; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8fafc; }
        .preview-box img { max-height: 100%; max-width: 100%; object-fit: contain; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg seller-nav sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fas fa-store me-2"></i>Seller Hub</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <p class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($seller_name); ?></p>
                <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.6rem;">ACTIVE SELLER</span>
            </div>
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                <?php echo strtoupper(substr($seller_name, 0, 1)); ?>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <div class="mb-4">
                <a href="index.php" class="text-decoration-none small fw-bold text-muted"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
                <h2 class="fw-bold text-dark mt-2">Create New Listing</h2>
                <p class="text-muted">Fill in the details below to add your product to the marketplace.</p>
            </div>

            <?php echo $message; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card form-card p-4 shadow-sm">
                            <div class="mb-4">
                                <label class="form-label">Product Title</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter descriptive name (e.g. Apple iPhone 15 Pro Max)" required>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Listing Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="price" class="form-control price-input" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Inventory Count</label>
                                    <input type="number" name="stock" class="form-control" placeholder="Available units" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" selected disabled>Select appropriate category</option>
                                    <?php
                                    $cat_sql = "SELECT * FROM categories ORDER BY name ASC";
                                    $cat_result = $conn->query($cat_sql);
                                    while($cat = $cat_result->fetch_assoc()) {
                                        echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Search Keywords (Tags)</label>
                                <input type="text" name="tags" class="form-control" placeholder="e.g. smartphone, mobile, apple, ios">
                                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Use commas to separate tags. Helps in appearing in search results.</div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Product Description</label>
                                <textarea name="description" class="form-control" rows="6" placeholder="Tell customers about features, warranty, and specs..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card form-card p-4 shadow-sm h-100">
                            <h6 class="fw-bold mb-3">Product Media</h6>
                            
                            <div class="mb-4">
                                <label class="form-label border rounded-3 p-3 w-100 text-center bg-light" style="cursor: pointer; border: 2px dashed #cbd5e1 !important;">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i><br>
                                    <span class="small fw-bold">Click to upload image</span>
                                    <input type="file" name="image" id="imgInput" class="d-none" accept="image/*" onchange="previewImage(this)" required>
                                </label>
                            </div>

                            <div class="preview-box">
                                <div id="placeholder-text" class="text-center p-4">
                                    <p class="text-muted small mb-0">Image preview will appear here</p>
                                </div>
                                <img id="imgPreview" src="" class="d-none">
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                                    <i class="fas fa-rocket me-2"></i>Publish Product
                                </button>
                                <p class="text-center text-muted small mt-3">Product will be live immediately after publishing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('imgPreview');
                const text = document.getElementById('placeholder-text');
                img.src = e.target.result;
                img.classList.remove('d-none');
                text.classList.add('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>