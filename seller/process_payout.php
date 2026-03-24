<?php
include 'auth_check.php';
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id = $_SESSION['user_id'];
    $requested_amount = floatval($_POST['amount']);
    $details = $conn->real_escape_string($_POST['account_details']);

    // 1. Calculate actual withdrawable balance
    $bal_query = $conn->query("
        SELECT (COALESCE(SUM(price * quantity), 0) - 
               (SELECT COALESCE(SUM(amount), 0) FROM payouts WHERE seller_id = $seller_id AND status='processed')) as bal 
        FROM order_items WHERE seller_id = $seller_id AND status = 'delivered'
    ");
    $actual_bal = $bal_query->fetch_assoc()['bal'] ?? 0;

    // 2. Validation
    if ($requested_amount >= 500 && $requested_amount <= $actual_bal) {
        
        // --- COMMISSION CALCULATIONS ---
        $commission_rate = 0.10; // 10%
        $platform_fee = $requested_amount * $commission_rate;
        $final_payout = $requested_amount - $platform_fee;

        $conn->begin_transaction();
        try {
            // We store the full requested amount to balance the earnings, 
            // but we add columns to track the fee and final payout.
            $sql = "INSERT INTO payouts (seller_id, amount, platform_fee, final_payout, status, account_details) 
                    VALUES ($seller_id, $requested_amount, $platform_fee, $final_payout, 'processed', '$details')";
            
            $conn->query($sql);
            $conn->commit();
            header("Location: profile.php?msg=payout_success");
        } catch (Exception $e) {
            $conn->rollback();
            die("Transaction failed: " . $e->getMessage());
        }
    } else {
        header("Location: profile.php?msg=insufficient_balance");
    }
}
?>