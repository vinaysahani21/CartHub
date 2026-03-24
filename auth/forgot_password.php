<?php
// 1. Load Database
include '../config/db.php';

// 2. Load PHPMailer via Composer Autoload
// This assumes your vendor folder is at D:\xampp\htdocs\ecomm\vendor
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    // Debugging aid: if this triggers, your vendor folder isn't where we expect
    die("Error: 'vendor/autoload.php' not found. Please run 'composer require phpmailer/phpmailer' in your 'ecomm' folder.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "SELECT id, name FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Store Token in DB
        $conn->query("UPDATE users SET reset_token = '$token', reset_expires = '$expiry' WHERE email = '$email'");

        // Prepare Email
        $reset_link = "http://localhost/carthub-main/auth/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);
        try {
            // SMTP Config
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yadavgaurav0104@gmail.com'; // REPLACE THIS
            $mail->Password   = 'aothzswkvlvobtrj';    // REPLACE THIS (16 chars)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender & Recipient
            $mail->setFrom('no-reply@carthub.com', 'CartHub Security');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #2563eb;'>Password Reset Request</h2>
                    <p>Hi <strong>{$user['name']}</strong>,</p>
                    <p>We received a request to reset your password. Click the button below to proceed:</p>
                    <p style='text-align: center;'>
                        <a href='$reset_link' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Reset Password</a>
                    </p>
                    <p style='color: #666; font-size: 12px;'>If you didn't request this, you can safely ignore this email. Link expires in 1 hour.</p>
                </div>
            ";
$mail->SMTPDebug = 2; // 0 = off, 1 = client messages, 2 = client and server messages
            $mail->send();
            $message = '<div class="alert alert-success">Check your inbox! We have sent a reset link to <b>'.$email.'</b></div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Mailer Error: ' . $mail->ErrorInfo . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">We could not find an account with that email.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password | CartHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; display: flex; align-items: center; height: 100vh; font-family: sans-serif; }
        .auth-card { max-width: 400px; width: 100%; margin: auto; padding: 40px; border-radius: 16px; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="auth-card">
    <h4 class="fw-bold mb-3 text-center">Forgot Password?</h4>
    <p class="text-muted small mb-4 text-center">No worries! Enter your email and we'll send you reset instructions.</p>
    
    <?php echo $message; ?>
    
    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Registered Email</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Send Reset Link</button>
    </form>
    
    <div class="text-center mt-4">
        <a href="login.php" class="text-decoration-none small text-muted"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
</div>
</body>
</html>