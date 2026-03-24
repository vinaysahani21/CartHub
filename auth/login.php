<?php
session_start();
include '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify Password
        if (password_verify($password, $user['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // REDIRECT BASED ON ROLE
            if ($user['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] == 'seller') {
                header("Location: ../seller/index.php");
            } else {
                header("Location: ../user/index.php"); // Customer
            }
            exit();
        } else {
            $message = '<div class="alert alert-danger">Invalid Password!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Email not found!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ShopEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; height: 100vh; }
        .auth-card { max-width: 400px; width: 100%; margin: auto; padding: 40px; border-radius: 15px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="auth-card">
    <h3 class="text-center fw-bold mb-4">Welcome Back</h3>
    <?php echo $message; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
    </form>
    <a href="forgot_password.php" class="small text-decoration-none">Forgot Password?</a>
    <div class="text-center mt-3">
    
        <small>Don't have an account? <a href="register.php">Sign Up</a></small>
    </div>
</div>

</body>
</html>