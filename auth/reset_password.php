<?php
include '../config/db.php';

$message = "";
$token = $_GET['token'] ?? "";

if (empty($token)) {
    die("Invalid Access.");
}

/* =========================
   1. Check Token Validity
========================= */

$stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid token.");
}

$user = $result->fetch_assoc();

/* =========================
   2. Check Expiry
========================= */

if ($user['reset_expires'] <= date("Y-m-d H:i:s")) {
    die("Token expired.");
}

/* =========================
   3. Handle Password Update
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (strlen($_POST['password']) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
    } else {

        $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users 
                                  SET password = ?, 
                                      reset_token = NULL, 
                                      reset_expires = NULL 
                                  WHERE id = ?");
        $update->bind_param("si", $new_pass, $user['id']);
        $update->execute();

        $message = '<div class="alert alert-success">
                        Password reset successful! 
                        <a href="login.php">Login here</a>
                    </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password | CartHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8fafc; 
            display: flex; 
            align-items: center; 
            height: 100vh; 
        }
        .auth-card { 
            max-width: 400px; 
            width: 100%; 
            margin: auto; 
            padding: 40px; 
            border-radius: 24px; 
            background: white; 
            border: 1px solid #e2e8f0; 
        }
    </style>
</head>
<body>

<div class="auth-card shadow-lg">
    <h4 class="fw-bold mb-4">Set New Password</h4>
    <?php echo $message; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">New Password</label>
            <input type="password" name="password" 
                   class="form-control rounded-3" 
                   minlength="6" required>
        </div>
        <button type="submit" 
                class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
            Update Password
        </button>
    </form>
</div>

</body>
</html>