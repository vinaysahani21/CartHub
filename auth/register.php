<?php
session_start();
include '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'customer' or 'seller'

    // 1. Check if email exists
    $checkEmail = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        $message = '<div class="alert alert-danger">Email already registered!</div>';
    } else {
        // 2. Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert User
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";

        if ($conn->query($sql) === TRUE) {
            // $message = '<div class="alert alert-success">Registration successful! <a href="login.php">Login here</a></div>';
            header("location:login.php");
        } else {
            $message = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - ShopEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; height: 100vh; }
        .auth-card { max-width: 450px; width: 100%; margin: auto; padding: 40px; border-radius: 15px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="auth-card">
    <h3 class="text-center fw-bold mb-4">Create Account</h3>
    <?php echo $message; ?>
    
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">I want to be a:</label>
            <select name="role" class="form-select">
                <option value="customer">Customer (I want to buy)</option>
                <option value="seller">Seller (I want to sell)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Sign Up</button>
    </form>
    <div class="text-center mt-3">
        <small>Already have an account? <a href="login.php">Login</a></small>
    </div>
</div>

</body>
</html>