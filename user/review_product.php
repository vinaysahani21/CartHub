<?php
include 'auth_check.php';
include '../config/db.php';
// Basic structure for the review page
$p_id = intval($_GET['id']);
$product = $conn->query("SELECT name FROM products WHERE id = $p_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Review Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card border-0 shadow p-4 mx-auto" style="max-width: 500px; border-radius: 20px;">
            <h4 class="fw-bold">Rate Product</h4>
            <p class="text-muted"><?php echo $product['name']; ?></p>
            <form action="save_review.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $p_id; ?>">
                <div class="mb-3">
                    <label class="form-label">Stars (1-5)</label>
                    <select name="rating" class="form-select">
                        <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                        <option value="4">⭐⭐⭐⭐ (Good)</option>
                        <option value="3">⭐⭐⭐ (Average)</option>
                        <option value="2">⭐⭐ (Poor)</option>
                        <option value="1">⭐ (Terrible)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-control" rows="3" required placeholder="Tell others about your experience..."></textarea>
                </div>
                <button type="submit" class="btn btn-dark w-100 py-2 rounded-pill">Submit Review</button>
            </form>
        </div>
    </div>
</body>
</html>