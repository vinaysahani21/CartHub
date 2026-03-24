<?php
include '../config/db.php';

$mobile=$_GET['mobile'];

$message="";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$otp=$_POST['otp'];

$sql="SELECT * FROM users WHERE phone='$mobile' AND otp='$otp' AND otp_expiry>NOW()";

$result=$conn->query($sql);

if($result->num_rows>0){

header("Location: reset_password.php?mobile=$mobile");

}else{

$message="<div class='alert alert-danger'>Invalid OTP</div>";

}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center" style="height:100vh;background:#f5f5f5;">

<div class="card p-4 mx-auto" style="width:400px">

<h4 class="text-center mb-3">Verify OTP</h4>

<?php echo $message; ?>

<form method="POST">

<input type="text" name="otp" class="form-control mb-3" placeholder="Enter OTP">

<button class="btn btn-success w-100">Verify OTP</button>

</form>

</div>

</body>
</html>