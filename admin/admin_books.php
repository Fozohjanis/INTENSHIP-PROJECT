<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Get total books for pagination
$total = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$pages = ceil($total / $perPage);

// Get books with pagination
$books = $pdo->prepare("SELECT * FROM books ORDER BY id DESC LIMIT {$start}, {$perPage}");
$books->execute();

// Handle book deletion
if (isset($_GET['delete'])) {
    $bookId = $_GET['delete'];
    
    // Get book image path first
    $stmt = $pdo->prepare("SELECT image_path FROM books WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    
    if ($book) {
        // Delete the book image file
        if (file_exists('../' . $book['image_path'])) {
            unlink('../' . $book['image_path']);
        }
        
        // Delete the book record
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        
        $_SESSION['success'] = "Book deleted successfully!";
        header('Location: admin_books.php');
        exit();
    }
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['book_ids'])) {
    $action = $_POST['bulk_action'];
    $bookIds = $_POST['book_ids'];
    
    if ($action === 'delete') {
        // Get image paths first
        $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
        $stmt = $pdo->prepare("SELECT image_path FROM books WHERE id IN ($placeholders)");
        $stmt->execute($bookIds);
        $booksToDelete = $stmt->fetchAll();
        
        // Delete the image files
        foreach ($booksToDelete as $book) {
            if (file_exists('../' . $book['image_path'])) {
                unlink('../' . $book['image_path']);
            }
        }
        
        // Delete the book records
        $stmt = $pdo->prepare("DELETE FROM books WHERE id IN ($placeholders)");
        $stmt->execute($bookIds);
        
        $_SESSION['success'] = count($bookIds) . " books deleted successfully!";
        header('Location: admin_books.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="admin.css">
    <title>Manage Books - BookStore Admin</title>
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
                            <li><a href="admin_books.php" class="active"><i class="fas fa-book"></i> Books</a></li>
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
                        <h2>Manage Books</h2>
                        <a href="admin_add_book.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Book
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>All Books</h3>
                            <form method="GET" class="search-form">
                                <input type="text" name="search" placeholder="Search books..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="bulk-action-form">
                                <div class="bulk-actions">
                                    <select name="bulk_action" class="form-control">
                                        <option value="">Bulk Actions</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm">Apply</button>
                                </div>
                                
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th width="30"><input type="checkbox" id="select-all"></th>
                                            <th>Cover</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Rating</th>
                                            <th>Downloads</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><input type="checkbox" name="book_ids[]" value="<?php echo $book['id']; ?>"></td>
                                            <td>
                                                <img src="../<?php echo htmlspecialchars($book['image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                                            </td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                            <td>
                                                <div class="stars">
                                                    <?php
                                                    $fullStars = floor($book['rating']);
                                                    $halfStar = $book['rating'] - $fullStars >= 0.5;
                                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                                    
                                                    for ($i = 0; $i < $fullStars; $i++) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    }
                                                    if ($halfStar) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    }
                                                    for ($i = 0; $i < $emptyStars; $i++) {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <td><?php echo $book['downloads']; ?></td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="admin_edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="admin_books.php?delete=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this book?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </form>
                            
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
    
    <script>
    // Select all checkbox functionality
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="book_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    </script>
</body>
</html>