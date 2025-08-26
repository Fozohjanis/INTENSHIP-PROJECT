<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

requireAdmin();

// Get stats for dashboard
$totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(price_paid) FROM orders")->fetchColumn();

// Get recent orders
$recentOrders = $pdo->query("SELECT o.*, b.title, b.image_path, u.username 
                             FROM orders o 
                             JOIN books b ON o.book_id = b.id 
                             JOIN users u ON o.user_id = u.id 
                             ORDER BY o.order_date DESC 
                             LIMIT 5")->fetchAll();

// Get recent books
$recentBooks = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 5")->fetchAll();

// Get recent users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard - BookStore</title>
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
                            <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="admin_books.php"><i class="fas fa-book"></i> Books</a></li>
                            <li><a href="admin_add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
                            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                            <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-header">
                        <h2>Admin Dashboard</h2>
                        <div class="current-date">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                    
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <i class="fas fa-book"></i>
                            <h3><?php echo $totalBooks; ?></h3>
                            <p>Total Books</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-shopping-cart"></i>
                            <h3><?php echo $totalOrders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-grid">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Recent Orders</h3>
                                <a href="admin_orders.php" class="btn btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentOrders) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Book</th>
                                            <th>User</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['title']); ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td>$<?php echo number_format($order['price_paid'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p>No recent orders found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Recent Books</h3>
                                <a href="admin_books.php" class="btn btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentBooks) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBooks as $book): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p>No books added yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Recent Users</h3>
                                <a href="admin_users.php" class="btn btn-sm">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentUsers) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p>No users registered yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>