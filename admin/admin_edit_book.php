<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: admin_books.php');
    exit();
}

$bookId = $_GET['id'];

// Get book data
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: admin_books.php');
    exit();
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $rating = $_POST['rating'];
    $isPromo = isset($_POST['is_promo']) ? 1 : 0;
    $promoPrice = $isPromo ? $_POST['promo_price'] : null;
    
    // Handle file upload if new image is provided
    $imagePath = $book['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Delete old image first
        if (file_exists('../' . $book['image_path'])) {
            unlink('../' . $book['image_path']);
        }
        
        $uploadDir = '../uploads/book_covers/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/book_covers/' . $fileName;
        }
    }
    
    // Update book in database
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, description = ?, price = ?, 
                          category = ?, image_path = ?, rating = ?, is_promo = ?, promo_price = ? 
                          WHERE id = ?");
    $stmt->execute([$title, $author, $description, $price, $category, $imagePath, $rating, $isPromo, $promoPrice, $bookId]);
    
    $_SESSION['success'] = "Book updated successfully!";
    header('Location: admin_books.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="admin.css">
    <title>Edit Book - BookStore Admin</title>
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
                            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <div class="dashboard-header">
                        <h2>Edit Book</h2>
                        <a href="admin_books.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Books
                        </a>
                    </div>
                    
                    <div class="form-container">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Book Title *</label>
                                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="author">Author *</label>
                                    <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Price *</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $book['price']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <select id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo $category['name'] === $book['category'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="rating">Rating (0-5)</label>
                                    <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="<?php echo $book['rating']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="image">Book Cover</label>
                                    <input type="file" id="image" name="image" accept="image/*">
                                    <div class="current-image">
                                        <p>Current Image:</p>
                                        <img src="../<?php echo htmlspecialchars($book['image_path']); ?>" alt="Current Book Cover" class="book-cover">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" id="is_promo" name="is_promo" <?php echo $book['is_promo'] ? 'checked' : ''; ?>>
                                    <span class="checkmark"></span>
                                    This book is on promotion
                                </label>
                            </div>
                            
                            <div class="form-group promo-price-group" style="<?php echo $book['is_promo'] ? 'display:block;' : 'display:none;'; ?>">
                                <label for="promo_price">Promotional Price</label>
                                <input type="number" id="promo_price" name="promo_price" step="0.01" min="0" value="<?php echo $book['promo_price'] ?? ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Book</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Show/hide promo price field
    document.getElementById('is_promo').addEventListener('change', function() {
        const promoPriceGroup = document.querySelector('.promo-price-group');
        if (this.checked) {
            promoPriceGroup.style.display = 'block';
        } else {
            promoPriceGroup.style.display = 'none';
        }
    });
    </script>
</body>
</html>