<?php
session_start();
include '../config/db.php';

$error = "";

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string(trim($_POST['username']));
    $password = trim($_POST['password']);

    // 1. Prepare Statement (More secure than standard query)
    $stmt = $conn->prepare("SELECT id, username,password from admin where username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

                // 3. Security: Regenerate Session ID to prevent fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['role'] = 'admin';

                header("Location: ./index.php");
                exit();
    
        
    } else {
        $error = "Invalid email or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ShopEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #111827; /* Dark background */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            color: #f3f4f6;
        }
        .form-control {
            background: #374151;
            border: 1px solid #4b5563;
            color: #fff;
        }
        .form-control:focus {
            background: #374151;
            border-color: #3b82f6;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        .btn-primary {
            background: #3b82f6;
            border: none;
            font-weight: 600;
        }
        .btn-primary:hover { background: #2563eb; }
        .brand-icon {
            font-size: 3rem;
            color: #3b82f6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="brand-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <h3 class="fw-bold mb-1">Admin Panel</h3>
        <p class="text-muted mb-4 small">Secure Access Only</p>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small border-0 bg-danger bg-opacity-25 text-danger">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">User Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-envelope"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin" required>
                </div>
            </div>
            
            <div class="mb-4 text-start">
                <label class="form-label small text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                Login to Dashboard <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
        
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted small hover-white">
                <i class="fas fa-arrow-left me-1"></i> Back to Site
            </a>
        </div>
    </div>

</body>
</html>