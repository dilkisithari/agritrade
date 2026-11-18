<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: browse-products.php");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get product details
$query = "SELECT p.*, u.username, u.full_name FROM products p 
          JOIN users u ON p.supplier_id = u.id 
          WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: browse-products.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle add to cart
$message = '';
if (isset($_POST['add_to_cart'])) {
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    
    if ($quantity <= 0) {
        $message = '<div class="alert alert-danger">Please enter a valid quantity.</div>';
    } elseif ($quantity > $product['quantity']) {
        $message = '<div class="alert alert-danger">Sorry, only ' . $product['quantity'] . ' items available in stock.</div>';
    } else {
        // Save into the same database cart that shopping_cart.php reads
        $buyer_id = (int)$_SESSION['user_id'];
        $pid = (int)$product_id;
        $qty = (int)$quantity;
        $existing = mysqli_query($conn, "SELECT id FROM cart WHERE buyer_id = $buyer_id AND product_id = $pid LIMIT 1");
        if ($existing && mysqli_num_rows($existing) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE buyer_id = $buyer_id AND product_id = $pid");
        } else {
            mysqli_query($conn, "INSERT INTO cart (buyer_id, user_id, product_id, quantity) VALUES ($buyer_id, $buyer_id, $pid, $qty)");
        }

        $message = '<div class="alert alert-success">Product added to cart successfully! <a href="shopping_cart.php">View Cart</a></div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Buyer Dashboard</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="browse-products.php" class="active"><i class="fas fa-search"></i> Browse Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li><a href="shopping_cart.php"><i class="fas fa-shopping-basket"></i> Shopping Cart</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="buyer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Product Details</h2>
            </div>
            
            <?php echo $message; ?>
            
            <div class="product-details" style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 30px;">
                <div class="product-image" style="flex: 1; min-width: 300px;">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100%; border-radius: 8px; box-shadow: var(--shadow);">
                    <?php else: ?>
                        <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder" style="width: 100%; border-radius: 8px; box-shadow: var(--shadow);">
                    <?php endif; ?>
                </div>
                
                <div class="product-info" style="flex: 2; min-width: 300px;">
                    <h1 style="margin-bottom: 10px;"><?php echo $product['name']; ?></h1>
                    <p class="price" style="font-size: 24px; color: var(--primary-color); margin-bottom: 15px;">$<?php echo $product['price']; ?></p>
                    
                    <div class="product-meta" style="margin-bottom: 20px;">
                        <p><strong>Category:</strong> <?php echo ucfirst($product['category']); ?></p>
                        <p><strong>Seller:</strong> <?php echo $product['full_name']; ?> (<?php echo $product['username']; ?>)</p>
                        <p><strong>Available Stock:</strong> <?php echo $product['quantity']; ?></p>
                    </div>
                    
                    <div class="product-description" style="margin-bottom: 30px;">
                        <h3>Description</h3>
                        <p><?php echo nl2br($product['description']); ?></p>
                    </div>
                    
                    <form action="product-details.php?id=<?php echo $product_id; ?>" method="post" style="margin-bottom: 20px;">
                        <div class="form-group" style="max-width: 200px;">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" value="1" required>
                        </div>
                        
                        <div class="form-buttons" style="display: flex; gap: 10px;">
                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                            <a href="browse-products.php" class="btn btn-secondary">Back to Products</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="related-products">
                <h3>Related Products</h3>
                <div class="products-grid">
                    <?php
                    $related_query = "SELECT p.*, u.username FROM products p 
                                     JOIN users u ON p.supplier_id = u.id 
                                     WHERE p.category = '{$product['category']}' AND p.id != $product_id 
                                     ORDER BY RAND() LIMIT 4";
                    $related_result = mysqli_query($conn, $related_query);
                    
                    while ($related = mysqli_fetch_assoc($related_result)):
                    ?>
                        <div class="product-card">
                            <?php if (!empty($related['image_url'])): ?>
                                <img src="../<?php echo $related['image_url']; ?>" alt="<?php echo $related['name']; ?>">
                            <?php else: ?>
                                <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder">
                            <?php endif; ?>
                            <h3><?php echo $related['name']; ?></h3>
                            <p class="price">$<?php echo $related['price']; ?></p>
                            <p class="seller">Seller: <?php echo $related['username']; ?></p>
                            <div class="product-actions">
                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">View Details</a>
                                <a href="add-to-cart.php?id=<?php echo $related['id']; ?>" class="btn btn-secondary">Add to Cart</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
