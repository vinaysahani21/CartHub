<?php
include 'auth_check.php';
include '../config/db.php';

$message = "";

// --- 1. HANDLE ACTIONS ---
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id == $_SESSION['user_id']) {
        $message = '<div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-ban me-2"></i>Security Alert: You cannot delete your own session!</div>';
    } else {
        $conn->query("DELETE FROM users WHERE id = $delete_id");
        $message = '<div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>User account purged successfully.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    if ($user_id == $_SESSION['user_id']) {
        $message = '<div class="alert alert-warning border-0 shadow-sm">Self-demotion is restricted for security reasons.</div>';
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success border-0 shadow-sm"><i class="fas fa-shield-check me-2"></i>Permissions updated for User #'.$user_id.'</div>';
        }
    }
}

// --- 2. PAGINATION & FILTER LOGIC ---
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$where_clauses = ["1=1"];
$params = [];
$types = "";

$search = $_GET['search'] ?? "";
if (!empty($search)) {
    $where_clauses[] = "(name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param;
    $types .= "ss";
}

$role_filter = $_GET['role'] ?? "";
if (!empty($role_filter)) {
    $where_clauses[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);
$count_sql = "SELECT COUNT(*) as total FROM users WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) { $count_stmt->bind_param($types, ...$params); }
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM users WHERE $where_sql ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$params[] = $start; $params[] = $limit;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Directory | CartHub Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-blue: #2563eb; --soft-bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--soft-bg); color: #1e293b; }
        
        /* Filter Section */
        .glass-filter { background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 25px; margin-bottom: 30px; }
        
        /* User Table UI */
        .user-card { border: none; border-radius: 24px; background: white; border: 1px solid #e2e8f0; overflow: hidden; }
        .table thead th { background: #f9fafb; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 15px 20px; }
        .table td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        .avatar-circle {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1rem; color: white;
        }
        
        .role-badge { padding: 6px 12px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; }
        .badge-admin { background: #fee2e2; color: #b91c1c; }
        .badge-seller { background: #dcfce7; color: #15803d; }
        .badge-customer { background: #eff6ff; color: #1e40af; }

        .pagination .page-link { border: none; margin: 0 3px; border-radius: 8px !important; color: #64748b; font-weight: 600; }
        .pagination .page-item.active .page-link { background: var(--primary-blue); color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-800 text-dark mb-1">User Directory</h2>
                    <p class="text-muted small">Manage authentication levels and account standings.</p>
                </div>
                <div class="d-flex gap-2">
    <button class="btn btn-white border rounded-pill px-4 fw-bold shadow-sm" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Report
    </button>
    <a href="export_user    .php" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
        <i class="fas fa-file-export me-2"></i>Export CSV
    </a>
</div>
            </div>

            <?php echo $message; ?>

            <div class="glass-filter shadow-sm">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">SEARCH DATABASE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" 
                                   placeholder="Filter by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">ACCESS LEVEL</label>
                        <select name="role" class="form-select border-0 bg-light" onchange="this.form.submit()">
                            <option value="">All Account Types</option>
                            <option value="customer" <?php if($role_filter=='customer') echo 'selected'; ?>>Customers</option>
                            <option value="seller" <?php if($role_filter=='seller') echo 'selected'; ?>>Sellers</option>
                            <option value="admin" <?php if($role_filter=='admin') echo 'selected'; ?>>Administrators</option>
                        </select>
                    </div>

                    <div class="col-md-4 text-end">
                        <?php if($search || $role_filter): ?>
                            <a href="users.php" class="btn btn-link text-danger fw-bold text-decoration-none me-3"><i class="fas fa-undo me-1"></i> Reset</a>
                        <?php endif; ?>
                        <span class="text-muted small fw-bold">Records: <?php echo $total_records; ?></span>
                    </div>
                </form>
            </div>

            <div class="card user-card shadow-sm">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Profile Info</th>
                                <th>Access Role</th>
                                <th>Account Health</th>
                                <th>Registration</th>
                                <th class="text-end pe-4">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $initial = strtoupper(substr($row['name'], 0, 1));
                                    $role = $row['role'];
                                    $bg_color = match($role) {
                                        'admin' => '#ef4444',
                                        'seller' => '#10b981',
                                        default => '#3b82f6'
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3 shadow-sm" style="background-color: <?php echo $bg_color; ?>">
                                                <?php echo $initial; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                                <div class="text-muted small" style="font-size: 0.75rem;"><?php echo htmlspecialchars($row['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge badge-<?php echo $role; ?>">
                                            <i class="fas fa-circle me-1" style="font-size: 0.4rem;"></i>
                                            <?php echo strtoupper($role); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-success small fw-bold d-flex align-items-center">
                                            <span class="bg-success rounded-circle me-2" style="width:6px; height:6px;"></span> Active
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-3 shadow-sm border" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-h text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">
                                                <li>
                                                    <a class="dropdown-item py-2 rounded-3" href="#" onclick="openRoleModal(<?php echo $row['id']; ?>, '<?php echo $row['role']; ?>', '<?php echo htmlspecialchars($row['name']); ?>')">
                                                        <i class="fas fa-user-shield text-primary me-2"></i> Modify Permissions
                                                    </a>
                                                </li>
                                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item py-2 rounded-3 text-danger" href="users.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('WARNING: Purge this user data permanently?')">
                                                        <i class="fas fa-trash-alt me-2"></i> Terminate Account
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No database entries match your filter.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white py-4 border-top-0 d-flex justify-content-center">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&role=<?php echo $role_filter; ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link shadow-sm" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&role=<?php echo $role_filter; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&role=<?php echo $role_filter; ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h6 class="modal-title fw-bold">Update Access Level</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body px-4 pb-4 pt-3">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    <input type="hidden" name="update_role" value="1">
                    
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-primary text-white mx-auto mb-2" style="width:60px; height:60px; font-size: 1.5rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p class="fw-bold mb-0" id="modal_user_name">Username</p>
                        <small class="text-muted">Targeted adjustment</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">New Assignment</label>
                        <select name="role" id="modal_role" class="form-select border-0 bg-light rounded-3">
                            <option value="customer">Customer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openRoleModal(id, currentRole, name) {
        document.getElementById('modal_user_id').value = id;
        document.getElementById('modal_role').value = currentRole;
        document.getElementById('modal_user_name').innerText = name;
        new bootstrap.Modal(document.getElementById('roleModal')).show();
    }
</script>

</body>
</html>