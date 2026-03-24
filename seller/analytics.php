<?php
include 'auth_check.php';
include '../config/db.php';

$seller_id = $_SESSION['user_id'];

// 1. Fetch Monthly Sales Data (Safely)
$months = [];
$sales_values = [];

$monthly_query = "
    SELECT DATE_FORMAT(created_at, '%b') as month, SUM(price * quantity) as total 
    FROM order_items 
    WHERE seller_id = $seller_id AND status = 'delivered'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), month
    ORDER BY created_at ASC LIMIT 6
";

$monthly_sales = $conn->query($monthly_query);

if ($monthly_sales && $monthly_sales->num_rows > 0) {
    while ($row = $monthly_sales->fetch_assoc()) {
        $months[] = $row['month'];
        $sales_values[] = (float)$row['total'];
    }
} else {
    // Fallback data so the chart doesn't break
    $months = [date('M')];
    $sales_values = [0];
}

// 2. Fetch Category Distribution (Safely)
$cat_names = [];
$cat_counts = [];

$cat_query = "
    SELECT c.name, SUM(oi.quantity) as count 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE oi.seller_id = $seller_id
    GROUP BY c.name
";

$cat_dist = $conn->query($cat_query);

if ($cat_dist && $cat_dist->num_rows > 0) {
    while ($row = $cat_dist->fetch_assoc()) {
        $cat_names[] = $row['name'];
        $cat_counts[] = (int)$row['count'];
    }
} else {
    $cat_names = ['No Sales'];
    $cat_counts = [0];
}

// 3. Top 5 Products
$top_products_query = "
    SELECT p.name, SUM(oi.quantity) as sold, SUM(oi.price * oi.quantity) as earned
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.seller_id = $seller_id AND oi.status = 'delivered'
    GROUP BY p.id, p.name 
    ORDER BY sold DESC LIMIT 5
";
$top_products = $conn->query($top_products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Analytics | ShopEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .chart-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 25px; min-height: 400px; transition: 0.3s; }
        .stat-text { font-size: 0.85rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <div class="mb-5">
                <h2 class="fw-bold text-dark">Business Insights</h2>
                <p class="text-muted">Data-driven overview of your store's health.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="chart-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="stat-text text-uppercase">Revenue Trend (₹)</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">Delivered Orders Only</span>
                        </div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card shadow-sm">
                        <span class="stat-text text-uppercase d-block mb-4">Sales by Category</span>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <h5 class="fw-bold mb-4">Best Selling Products</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th class="text-center">Units Sold</th>
                                        <th class="text-end">Total Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($top_products && $top_products->num_rows > 0): ?>
                                        <?php while($p = $top_products->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo $p['name']; ?></td>
                                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $p['sold']; ?></span></td>
                                            <td class="text-end fw-bold text-primary">₹<?php echo number_format($p['earned'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No delivered sales data available yet.</td></tr>
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



<script>
    // 1. Revenue Line Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue',
                data: <?php echo json_encode($sales_values); ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // 2. Category Pie Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($cat_names); ?>,
            datasets: [{
                data: <?php echo json_encode($cat_counts); ?>,
                backgroundColor: ['#2563eb', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>