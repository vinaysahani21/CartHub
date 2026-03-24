<?php
include 'auth_check.php';
include '../config/db.php';

$message = "";

// 1. Handle File Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slide_img'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $subtitle = $conn->real_escape_string($_POST['subtitle']);
    
    // Check if PHP caught an error during the initial upload to the temp folder
    if ($_FILES['slide_img']['error'] !== UPLOAD_ERR_OK) {
        $err_code = $_FILES['slide_img']['error'];
        $err_msg = "Unknown Error";
        if ($err_code == 1) $err_msg = "File exceeds upload_max_filesize in php.ini.";
        if ($err_code == 3) $err_msg = "File was only partially uploaded.";
        if ($err_code == 4) $err_msg = "No file was uploaded.";
        
        $message = '<div class="alert alert-danger border-0 shadow-sm rounded-4"><i class="fas fa-exclamation-circle me-2"></i>Upload Error: ' . $err_msg . ' (Code: '.$err_code.')</div>';
    } else {
        $target_dir = "../assets/uploads/";
        
        // Try to create directory, and catch if it fails
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                die('<div class="alert alert-danger">Fatal Error: PHP cannot create the directory. Check folder permissions.</div>');
            }
        }
        
        $filename = time() . "_" . basename($_FILES["slide_img"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["slide_img"]["tmp_name"], $target_file)) {
            $db_path = "assets/uploads/" . $filename;
            $conn->query("INSERT INTO hero_slides (image_path, title, subtitle) VALUES ('$db_path', '$title', '$subtitle')");
            $message = '<div class="alert alert-success border-0 shadow-sm rounded-4"><i class="fas fa-check-circle me-2"></i>New promotion slide active!</div>';
        } else {
            $message = '<div class="alert alert-danger border-0 shadow-sm rounded-4"><i class="fas fa-exclamation-circle me-2"></i>move_uploaded_file() failed. The server cannot write to ' . $target_dir . '</div>';
        }
    }
}

// 2. Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Optional: Add logic to unlink (delete) the physical file here
    $conn->query("DELETE FROM hero_slides WHERE id = $id");
    header("Location: slider.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hero Content | CartHub Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-blue: #2563eb; --soft-bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--soft-bg); color: #1e293b; }
        
        .admin-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; }
        .form-label { font-weight: 700; font-size: 0.85rem; color: #64748b; margin-bottom: 8px; }
        .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background: #fdfdfd; }
        .form-control:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }

        /* Slide Preview Cards */
        .slide-card { transition: 0.3s; overflow: hidden; height: 100%; border: 1px solid #f1f5f9; }
        .slide-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .slide-img-wrap { position: relative; height: 180px; overflow: hidden; }
        .slide-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .slide-overlay { position: absolute; top: 10px; right: 10px; }

        .btn-upload { background: var(--primary-blue); color: white; padding: 12px 25px; border-radius: 12px; font-weight: 700; }
        .btn-upload:hover { background: #1d4ed8; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <div class="col-md-10 p-4 p-lg-5">
            <div class="mb-5">
                <h2 class="fw-800 text-dark mb-1">Homepage Visuals</h2>
                <p class="text-muted">Manage the large promotional banners shown on the customer dashboard.</p>
            </div>

            <?php echo $message; ?>

            <div class="card admin-card shadow-sm mb-5">
                <div class="card-body p-4 p-lg-5">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-plus-circle me-2"></i>Create New Banner</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">BANNER IMAGE</label>
                                <input type="file" name="slide_img" class="form-control" required accept="image/*">
                                <div class="form-text mt-2 small">Recommended: 1200x400px (Wide)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">HEADLINE TITLE</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Winter Collection 2026" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SUBTITLE / OFFER</label>
                                <input type="text" name="subtitle" class="form-control" placeholder="e.g. Flat 50% Off on Electronics" required>
                            </div>
                        </div>
                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-upload shadow-sm">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Push to Production
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Live Banner Rotation</h5>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">Displaying Newest First</span>
            </div>

            <div class="row g-4">
                <?php
                $slides = $conn->query("SELECT * FROM hero_slides ORDER BY id DESC");
                if ($slides->num_rows > 0):
                    while($row = $slides->fetch_assoc()):
                        $img = strpos($row['image_path'], 'http') === 0 ? $row['image_path'] : "../" . $row['image_path'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card admin-card slide-card shadow-sm">
                        <div class="slide-img-wrap">
                            <img src="<?php echo $img; ?>" alt="Promotion">
                            <div class="slide-overlay">
                                <a href="slider.php?delete=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm rounded-circle shadow" 
                                   onclick="return confirm('Archive this banner permanently?')"
                                   title="Remove Slide">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary small rounded-pill px-2 mb-2">Active Slide</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['title']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($row['subtitle']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-images fa-4x text-muted opacity-25 mb-3"></i>
                        <h6 class="text-muted">No banners found. Add your first promotion above!</h6>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    