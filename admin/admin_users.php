<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Get total users for pagination
$total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pages = ceil($total / $perPage);

// Get users with pagination
$users = $pdo->prepare("SELECT * FROM users ORDER BY id DESC LIMIT {$start}, {$perPage}");
$users->execute();

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    
    // Check if user has orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        $_SESSION['error'] = "Cannot delete user with existing orders!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['success'] = "User deleted successfully!";
    }
    
    header('Location: admin_users.php');
    exit();
}

// Handle make admin
if (isset($_GET['make_admin'])) {
    $userId = $_GET['make_admin'];
    
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->execute([$userId]);
    
    $_SESSION['success'] = "User promoted to admin successfully!";
    header('Location: admin_users.php');
    exit();
}

// Handle remove admin
if (isset($_GET['remove_admin'])) {
    $userId = $_GET['remove_admin'];
    
    // Don't allow removing the last admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_admin = 1");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount <= 1) {
        $_SESSION['error'] = "Cannot remove the last admin!";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['success'] = "Admin privileges removed successfully!";
    }
    
    header('Location: admin_users.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="admin.css">
    <title>Manage Users - BookStore Admin</title>
</head>
<body class="logged-in">
    <?php include '../includes/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <div class="admin-profile">
                        <img src="../assets/images/admin-avatar.png" alt="Admin Avatar">
                        <h4>Administrator</h4>
                        <p>Super Admin</p>
                    </div>
                    <nav class="dashboard-menu">
                        <ul>
                            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="admin_books.php"><i class="fas fa-book"></i> Books</a></li>
                            <li><a href="admin_add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
                            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                            <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                            <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-header">
                        <h2>Manage Users</h2>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>All Users</h3>
                            <form method="GET" class="search-form">
                                <input type="text" name="search" placeholder="Search users..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <div class="card-body">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                            <span class="badge badge-primary">Admin</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <?php if ($user['is_admin']): ?>
                                                <a href="admin_users.php?remove_admin=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to remove admin privileges?');">
                                                    <i class="fas fa-user-minus"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="admin_users.php?make_admin=<?php echo $user['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to make this user an admin?');">
                                                    <i class="fas fa-user-shield"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="admin_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <?php if ($pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="page-link">&laquo; Prev</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>