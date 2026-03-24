<?php
include 'auth_check.php';
include '../config/db.php';

// --- DATA FETCHING ---
// 1. Core Stats
$users = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$sellers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='seller'")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT SUM(total_amount) as t FROM orders WHERE payment_status='completed'")->fetch_assoc()['t'] ?? 0;
$pending = $conn->query("SELECT COUNT(*) as c FROM orders WHERE payment_status='pending'")->fetch_assoc()['c'];

// 2. Platform Commission (Sum of all platform_fees from payouts table)
$commission_query = $conn->query("SELECT SUM(platform_fee) as total_comm FROM payouts WHERE status='processed'");
$total_commission = $commission_query->fetch_assoc()['total_comm'] ?? 0;

// 3. Inventory Distribution
$cat_share = $conn->query("SELECT c.name, COUNT(p.id) as p_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id LIMIT 4");

// 4. System Alerts
$out_of_stock = $conn->query("SELECT COUNT(*) as c FROM products WHERE stock <= 0")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Master Admin | CartHub Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-blue: #2563eb; --soft-bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--soft-bg); color: #1e293b; }
        .stat-card { border: none; border-radius: 20px; transition: 0.3s ease; background: white; }
        .icon-shape { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .admin-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; }
        .badge-commission { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 800; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <div class="col-md-10 p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-800 mb-1 text-dark">Administrative Overview</h2>
                    <p class="text-muted mb-0">Platform health and market statistics.</p>
                </div>
                <div class="text-end">
                    <div class="badge badge-commission p-3 rounded-4 shadow-sm">
                        <small class="d-block text-uppercase mb-1" style="font-size: 0.6rem;">Net Platform Commission</small>
                        <span class="fs-5">₹<?php echo number_format($total_commission, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="fas fa-indian-rupee-sign"></i></div>
                            <div>
                                <small class="text-muted fw-bold d-block">TOTAL SALES</small>
                                <h4 class="fw-bold mb-0">₹<?php echo number_format($revenue, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
                            <div>
                                <small class="text-muted fw-bold d-block">CUSTOMERS</small>
                                <h4 class="fw-bold mb-0"><?php echo $users; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-info bg-opacity-10 text-info"><i class="fas fa-store"></i></div>
                            <div>
                                <small class="text-muted fw-bold d-block">SELLERS</small>
                                <h4 class="fw-bold mb-0"><?php echo $sellers; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                            <div>
                                <small class="text-muted fw-bold d-block">PENDING ORDERS</small>
                                <h4 class="fw-bold mb-0"><?php echo $pending; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <div class="card admin-card p-4 h-100 shadow-sm border-top border-warning border-4">
                        <h6 class="fw-bold mb-4 text-uppercase ls-1">Revenue Stream</h6>
                        <div class="mb-4">
                            <label class="text-muted small d-block mb-1">Gross Merchandise Value (GMV)</label>
                            <h4 class="fw-bold text-dark">₹<?php echo number_format($revenue, 2); ?></h4>
                        </div>
                        <div class="p-3 rounded-4 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="text-muted small d-block">Platform Fee (10%)</label>
                                    <span class="fw-bold text-primary">₹<?php echo number_format($total_commission, 2); ?></span>
                                </div>
                                <i class="fas fa-chart-pie text-primary opacity-25 fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card admin-card p-4 h-100 shadow-sm">
                        <h6 class="fw-bold mb-4 text-uppercase ls-1">Inventory Distribution</h6>
                        <div class="row">
                            <?php if($cat_share): while($cat = $cat_share->fetch_assoc()): ?>
                                <div class="col-md-3 text-center border-end last-border-none">
                                    <p class="text-muted small mb-1 fw-bold"><?php echo strtoupper($cat['name']); ?></p>
                                    <h4 class="fw-bold"><?php echo $cat['p_count']; ?></h4>
                                    <small class="text-primary fw-bold">Items</small>
                                </div>
                            <?php endwhile; endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card admin-card shadow-sm overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Users</h5>
                    <a href="users.php" class="btn btn-sm btn-link text-decoration-none fw-bold">Manage All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Profile</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-end pe-4">Registration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
                            while($row = $res->fetch_assoc()):
                                $role = $row['role'];
                                $initial = strtoupper(substr($row['name'], 0, 1));
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small shadow-sm" style="width: 35px; height: 35px;"><?php echo $initial; ?></div>
                                        <span class="fw-bold small"><?php echo htmlspecialchars($row['name']); ?></span>
                                    </div>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge bg-light text-dark border rounded-pill px-3 py-1 small"><?php echo strtoupper($role); ?></span></td>
                                <td class="text-end pe-4 text-muted small"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>