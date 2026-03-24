<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// --- HANDLE PROFILE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_name = $conn->real_escape_string($_POST['name']);
    $new_email = $conn->real_escape_string($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_name, $new_email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['name'] = $new_name; // Update session data
        $message = '<div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } else {
        $message = '<div class="alert alert-danger border-0 shadow-sm">Error updating profile.</div>';
    }
}

// Re-fetch fresh data after potential update
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'] ?? "User Email Not Set";
$user_initial = strtoupper(substr($user_name, 0, 1));

// --- FETCH USER STATS ---
$order_count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id = $user_id")->fetch_assoc()['c'];
$cart_count = $conn->query("SELECT SUM(quantity) as q FROM cart WHERE user_id = $user_id")->fetch_assoc()['q'] ?? 0;
$user_data = $conn->query("SELECT created_at, email FROM users WHERE id = $user_id")->fetch_assoc();
$joined_date = date("M Y", strtotime($user_data['created_at']));
$fresh_email = $user_data['email']; // Get most recent email from DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile | CartHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-blue: #2563eb; --glass: rgba(255, 255, 255, 0.9); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; min-height: 100vh; padding-top: 80px; }
        .profile-card { background: var(--glass); border-radius: 24px; border: 1px solid white; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .avatar-large { width: 100px; height: 100px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; border-radius: 30px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }
        .option-tile { background: white; border-radius: 18px; padding: 20px; text-decoration: none; color: #1f2937; display: flex; align-items: center; transition: 0.2s; border: 1px solid #f1f5f9; margin-bottom: 15px; }
        .option-tile:hover { transform: scale(1.02); background: #f8fafc; color: var(--primary-blue); }
        .option-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 15px; }
        .stat-badge { background: #eff6ff; color: #1e40af; padding: 5px 12px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; }
        .modal-content { border-radius: 24px; border: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="index.php">CartHub</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php echo $message; ?>

                <div class="profile-card p-4 p-md-5 mb-5">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-large"><?php echo $user_initial; ?></div>
                        </div>
                        <div class="col mt-3 mt-md-0">
                            <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user_name); ?></h2>
                            <p class="text-muted mb-3"><i class="far fa-envelope me-2"></i><?php echo htmlspecialchars($fresh_email); ?></p>
                            <div class="d-flex gap-2">
                                <span class="stat-badge">Member since <?php echo $joined_date; ?></span>
                                <span class="stat-badge bg-success bg-opacity-10 text-success"><?php echo $order_count; ?> Orders Placed</span>
                            </div>
                        </div>
                        <div class="col-md-auto mt-4 mt-md-0">
                            <button class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-4 px-2">Shopping Activity</h5>
                        <a href="my_orders.php" class="option-tile">
                            <div class="option-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-box"></i></div>
                            <div class="flex-grow-1"><div class="fw-bold">My Orders</div><small class="text-muted">Track or buy things again</small></div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                        <a href="cart.php" class="option-tile">
                            <div class="option-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-shopping-cart"></i></div>
                            <div class="flex-grow-1"><div class="fw-bold">Shopping Cart</div><small class="text-muted">View added items</small></div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-4 px-2">Account Settings</h5>
                        <a href="#" class="option-tile">
                            <div class="option-icon bg-info bg-opacity-10 text-info"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="flex-grow-1"><div class="fw-bold">Saved Addresses</div><small class="text-muted">Manage shipping locations</small></div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                        <a href="#" class="option-tile">
                            <div class="option-icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-lock"></i></div>
                            <div class="flex-grow-1"><div class="fw-bold">Security</div><small class="text-muted">Update password</small></div>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body px-4 pb-4">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">FULL NAME</label>
                            <input type="text" name="name" class="form-control rounded-3" value="<?php echo htmlspecialchars($user_name); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?php echo htmlspecialchars($fresh_email); ?>" required>
                            <div class="form-text small">This email is used for order notifications.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>