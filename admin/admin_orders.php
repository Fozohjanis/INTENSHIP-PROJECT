<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Get total orders for pagination
$total = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pages = ceil($total / $perPage);

// Get orders with pagination
$orders = $pdo->prepare("SELECT o.*, b.title, b.image_path, u.username 
                         FROM orders o 
                         JOIN books b ON o.book_id = b.id 
                         JOIN users u ON o.user_id = u.id 
                         ORDER BY o.order_date DESC 
                         LIMIT {$start}, {$perPage}");
$orders->execute();

// Handle order deletion
if (isset($_GET['delete'])) {
    $orderId = $_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    
    $_SESSION['success'] = "Order deleted successfully!";
    header('Location: admin_orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="admin.css">
    <title>Manage Orders - BookStore Admin</title>
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
                            <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-header">
                        <h2>Manage Orders</h2>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>All Orders</h3>
                            <form method="GET" class="search-form">
                                <input type="text" name="search" placeholder="Search orders..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <div class="card-body">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Book</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>
                                            <div style="display:flex;align-items:center;">
                                                <img src="../<?php echo htmlspecialchars($order['image_path']); ?>" alt="<?php echo htmlspecialchars($order['title']); ?>" class="book-cover">
                                                <?php echo htmlspecialchars($order['title']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>$<?php echo number_format($order['price_paid'], 2); ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="admin_orders.php?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?');">
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