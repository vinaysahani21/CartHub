<?php
include 'auth_check.php';
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? "customer@example.com";
$user_name = $_SESSION['name'];

// 1. Calculate Grand Total
$sql = "SELECT c.quantity, p.price, p.discount_price 
        FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id";
$res = $conn->query($sql);
$total_in_rupee = 0;

while($row = $res->fetch_assoc()) {
    $price = ($row['discount_price'] > 0) ? $row['discount_price'] : $row['price'];
    $total_in_rupee += ($price * $row['quantity']);
}

// Add 5% tax as per your cart.php logic
$total_with_tax = $total_in_rupee * 1.05;

if($total_with_tax <= 0) { header("Location: cart.php"); exit(); }

$amount_in_paisa = round($total_with_tax * 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout | CartHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; padding-top: 50px; }
        .checkout-card { border-radius: 24px; border: 1px solid #e2e8f0; background: white; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; background: #fdfdfd; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .price-summary { background: #f1f5f9; border-radius: 15px; padding: 20px; }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="card checkout-card shadow-sm p-4">
                        <h4 class="fw-800 mb-4"><i class="fas fa-truck me-2 text-primary"></i>Shipping Details</h4>
                        <form id="addressForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">FULL NAME</label>
                                    <input type="text" id="cust_name" class="form-control" value="<?php echo $user_name; ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">STREET ADDRESS / HOUSE NO.</label>
                                    <input type="text" id="address" class="form-control" placeholder="Building Name, Street, Area" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">CITY</label>
                                    <input type="text" id="city" class="form-control" placeholder="e.g. Surat" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">PINCODE</label>
                                    <input type="number" id="pincode" class="form-control" placeholder="6 Digits" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                                    <input type="tel" id="phone" class="form-control" placeholder="10-digit mobile number" required>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card checkout-card shadow-sm p-4 position-sticky" style="top: 20px;">
                        <h5 class="fw-800 mb-4">Order Summary</h5>
                        <div class="price-summary mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Items:</span>
                                <span class="fw-bold"><?php echo $res->num_rows; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Net Amount:</span>
                                <span class="fw-bold">₹<?php echo number_format($total_in_rupee, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">GST (5%):</span>
                                <span class="fw-bold">₹<?php echo number_format($total_in_rupee * 0.05, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-800">Final Payable:</span>
                                <span class="fw-800 fs-3 text-primary">₹<?php echo number_format($total_with_tax, 2); ?></span>
                            </div>
                        </div>

                        <button id="rzp-button1" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-lg">
                            <i class="fas fa-shield-check me-2"></i>Pay with Razorpay
                        </button>
                        
                        <div class="text-center mt-3">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" width="100" class="opacity-50">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var options = {
    "key": "Your_Razorpay_Key", // ENTER YOUR KEY ID HERE
    "amount": "<?php echo $amount_in_paisa; ?>", 
    "currency": "INR",
    "name": "CartHub Store",
    "description": "Secure Purchase",
    "image": "https://cdn-icons-png.flaticon.com/512/1162/1162499.png",
    "handler": function (response){
        // Success: Send Address + Payment ID to place_order.php
        var addr = document.getElementById('address').value + ", " + 
                   document.getElementById('city').value + " - " + 
                   document.getElementById('pincode').value;
        var phone = document.getElementById('phone').value;

        // Redirect with all info
        window.location.href = "place_order.php?payment_id=" + response.razorpay_payment_id + 
                               "&address=" + encodeURIComponent(addr) + 
                               "&phone=" + encodeURIComponent(phone);
    },
    "prefill": {
        "name": "<?php echo $user_name; ?>",
        "email": "<?php echo $user_email; ?>"
    },
    "theme": { "color": "#2563eb" }
};

var rzp1 = new Razorpay(options);

document.getElementById('rzp-button1').onclick = function(e){
    // Validation: check if form is filled
    var form = document.getElementById('addressForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    rzp1.open();
    e.preventDefault();
}
</script>
</body>
</html> 