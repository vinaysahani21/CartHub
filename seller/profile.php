<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$message = "";

// 1. Fetch Seller Personal Info
$seller_query = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$seller_query->bind_param("i", $seller_id);
$seller_query->execute();
$seller = $seller_query->get_result()->fetch_assoc();

// 2. Fetch Financial Data (Earnings - Withdrawals)
$earnings_query = $conn->query("SELECT SUM(price * quantity) as total FROM order_items WHERE seller_id = $seller_id AND status = 'delivered'");
$lifetime_earnings = $earnings_query->fetch_assoc()['total'] ?? 0;

$payouts_query = $conn->query("SELECT SUM(amount) as paid FROM payouts WHERE seller_id = $seller_id AND status = 'processed'");
$total_paid = $payouts_query->fetch_assoc()['paid'] ?? 0;

$withdrawable_balance = $lifetime_earnings - $total_paid;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Seller Profile | CartHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .profile-card { background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; }
        .avatar-lg { width: 100px; height: 100px; border-radius: 30px; background: #2563eb; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; }
        .wallet-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 20px; padding: 30px; position: relative; overflow: hidden; }
        .wallet-card::after { content: "\f555"; font-family: "Font Awesome 6 Free"; position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.1; font-weight: 900; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4 p-lg-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="profile-card shadow-sm p-4 text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <?php echo strtoupper(substr($seller['name'], 0, 1)); ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($seller['name']); ?></h4>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($seller['email']); ?></p>
                        <hr>
                        <div class="text-start">
                            <label class="small fw-bold text-muted text-uppercase d-block mb-1">Member Since</label>
                            <p class="fw-semibold"><?php echo date('F d, Y', strtotime($seller['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="wallet-card shadow-lg mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <span class="small opacity-75 text-uppercase fw-bold">Withdrawable Balance</span>
                                <h1 class="display-4 fw-800 my-2">₹<?php echo number_format($withdrawable_balance, 2); ?></h1>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <button class="btn btn-light btn-lg rounded-pill px-4 fw-bold" 
                                        data-bs-toggle="modal" data-bs-target="#withdrawModal"
                                        <?php echo ($withdrawable_balance < 500) ? 'disabled' : ''; ?>>
                                    Withdraw Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="profile-card shadow-sm p-4">
                        <h5 class="fw-bold mb-4">Recent Payout History</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>PAYOUT ID</th>
                                        <th>DATE</th>
                                        <th>REQUESTED</th>
                                        <th>FINAL RECEIVED</th>
                                        <th class="text-end">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $history = $conn->query("SELECT * FROM payouts WHERE seller_id = $seller_id ORDER BY id DESC LIMIT 5");
                                    if($history && $history->num_rows > 0):
                                        while($p = $history->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="fw-bold small">#PAY-<?php echo $p['id']; ?></td>
                                        <td class="text-muted small"><?php echo date('d M, Y', strtotime($p['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold">₹<?php echo number_format($p['amount'], 2); ?></div>
                                            <small class="text-danger">Fee (10%): -₹<?php echo number_format($p['platform_fee'], 2); ?></small>
                                        </td>
                                        <td class="text-success fw-bold">₹<?php echo number_format($p['final_payout'], 2); ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><?php echo strtoupper($p['status']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No payout history found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Withdraw Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process_payout.php" method="POST">
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">AMOUNT TO WITHDRAW (₹)</label>
                        <input type="number" name="amount" id="payout_amount" class="form-control form-control-lg rounded-3" 
                               min="500" max="<?php echo $withdrawable_balance; ?>" 
                               placeholder="Min ₹500" required>
                    </div>

                    <div class="p-3 rounded-4 bg-light mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Withdrawal Amount:</span>
                            <span class="fw-bold" id="display_req">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Platform Fee (10%):</span>
                            <span class="text-danger fw-bold" id="display_fee">-₹0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-dark fw-bold">You will receive:</span>
                            <span class="text-primary fw-bold" id="display_final">₹0.00</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">UPI ID / ACCOUNT DETAILS</label>
                        <input type="text" name="account_details" class="form-control rounded-3" placeholder="username@upi" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                        Confirm Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Logic to update numbers in the modal in real-time
document.getElementById('payout_amount').addEventListener('input', function() {
    let amt = parseFloat(this.value) || 0;
    let fee = amt * 0.10;
    let final = amt - fee;
    
    document.getElementById('display_req').innerText = '₹' + amt.toFixed(2);
    document.getElementById('display_fee').innerText = '-₹' + fee.toFixed(2);
    document.getElementById('display_final').innerText = '₹' + final.toFixed(2);
});
</script>

</body>
</html>