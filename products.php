<?php
session_start();
include 'config/database.php';
$result = mysqli_query($conn, "SELECT * FROM products WHERE quantity > 0 ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - AgriTrade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <section class="products-preview" style="padding: 50px 0;">
            <div class="container">
                <h2>Products</h2>
                <div class="products-grid">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php $img = !empty($row['image_url']) ? $row['image_url'] : 'assets/images/product-placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p class="price">Rs.<?php echo htmlspecialchars($row['price']); ?></p>
                                <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'buyer'): ?>
                                    <a href="buyer/product-details.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn btn-primary">Login to Buy</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No products available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
