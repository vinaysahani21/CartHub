<?php
include 'auth_check.php';
include '../config/db.php';

// 1. User Growth Data
$user_growth = $conn->query("
    SELECT DATE_FORMAT(created_at, '%b') as month, 
           SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers,
           SUM(CASE WHEN role = 'seller' THEN 1 ELSE 0 END) as sellers
    FROM users 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), month
    ORDER BY MIN(created_at) ASC LIMIT 6
");

$months = []; $customer_data = []; $seller_data = [];
if ($user_growth) {
    while ($row = $user_growth->fetch_assoc()) {
        $months[] = $row['month'];
        $customer_data[] = (int)$row['customers'];
        $seller_data[] = (int)$row['sellers'];
    }
}

// 2. Revenue by Seller
$seller_perf = $conn->query("
    SELECT u.name, SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN users u ON oi.seller_id = u.id
    WHERE oi.status = 'delivered'
    GROUP BY u.id ORDER BY revenue DESC LIMIT 5
");

// 3. Totals
$totals = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM products) as total_prods,
        (SELECT COUNT(*) FROM orders WHERE payment_status='completed') as total_orders,
        (SELECT SUM(total_amount) FROM orders WHERE payment_status='completed') as total_rev
    FROM dual
")->fetch_assoc();

// 4. Audit List
$audit_list = $conn->query("
    SELECT name, email, role, created_at,
    (CASE 
        WHEN role = 'seller' THEN (SELECT SUM(price * quantity) FROM order_items WHERE seller_id = users.id AND status='delivered')
        WHEN role = 'customer' THEN (SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND payment_status='completed')
        ELSE 0 
     END) as total_volume
    FROM users 
    WHERE role != 'admin'
    ORDER BY created_at DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Analytics | CartHub Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; margin: 0; padding: 0; overflow-x: hidden; }
        
        .sidebar-container { min-height: 100vh; background: #111827; position: sticky; top: 0; }
        .content-container { padding: 30px; }

        .admin-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 20px; transition: 0.3s; margin-bottom: 20px; }
        .stat-label { font-size: 0.65rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase; }
        
        /* Fixed smaller height for chart */
        .chart-wrapper { height: 220px; position: relative; }

        .table thead th { background: #f9fafb; font-size: 0.7rem; text-transform: uppercase; color: #64748b; border: none; }
        .badge-role { padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 700; }
        .role-seller { background: #dcfce7; color: #15803d; }
        .role-customer { background: #eff6ff; color: #1e40af; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0"> 
        <div class="col-md-2 sidebar-container">
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="col-md-10 content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">Platform Intelligence</h3>
                <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="window.print()">Export PDF</button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="admin-card shadow-sm border-start border-primary border-4 py-3">
                        <span class="stat-label">Total Revenue</span>
                        <h4 class="fw-bold mb-0">₹<?php echo number_format($totals['total_rev'], 2); ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-card shadow-sm border-start border-success border-4 py-3">
                        <span class="stat-label">Active Listings</span>
                        <h4 class="fw-bold mb-0"><?php echo $totals['total_prods']; ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-card shadow-sm border-start border-warning border-4 py-3">
                        <span class="stat-label">Sales Count</span>
                        <h4 class="fw-bold mb-0"><?php echo $totals['total_orders']; ?></h4>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="admin-card shadow-sm">
                        <h6 class="fw-bold mb-3 small">Acquisition Growth</h6>
                        <div class="chart-wrapper">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="admin-card shadow-sm">
                        <h6 class="fw-bold mb-3 small">Top Merchants</h6>
                        <?php while($s = $seller_perf->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small fw-bold text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($s['name']); ?></span>
                            <span class="small fw-bold text-primary">₹<?php echo number_format($s['revenue'], 0); ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="admin-card shadow-sm p-0 overflow-hidden mt-3">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-0 small">User Volume Audit</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">User</th>
                                <th>Role</th>
                                <th class="text-end pe-3">Total Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $audit_list->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold" style="font-size: 0.8rem;"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="text-muted" style="font-size: 0.65rem;"><?php echo $user['email']; ?></div>
                                </td>
                                <td><span class="badge-role role-<?php echo $user['role']; ?>"><?php echo strtoupper($user['role']); ?></span></td>
                                <td class="text-end pe-3 fw-bold text-dark" style="font-size: 0.8rem;">₹<?php echo number_format($user['total_volume'], 0); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('growthChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [
                { label: 'Customers', data: <?php echo json_encode($customer_data); ?>, backgroundColor: '#2563eb', borderRadius: 4 },
                { label: 'Sellers', data: <?php echo json_encode($seller_data); ?>, backgroundColor: '#10b981', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: { 
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }, 
                y: { beginAtZero: true, ticks: { font: { size: 10 } } } 
            }
        }
    });
</script>
</body>
</html>