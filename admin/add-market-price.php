<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    
    // Validate input
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    
    if (empty($price)) {
        $errors[] = "Price is required";
    } elseif (!is_numeric($price)) {
        $errors[] = "Price must be a number";
    }
    
    // If no errors, proceed with adding market price
    if (empty($errors)) {
        $query = "INSERT INTO market_prices (product_name, category, price, updated_by) 
                  VALUES ('$product_name', '$category', '$price', '$admin_id')";
        
        if (mysqli_query($conn, $query)) {
            $success = true;
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Market Price - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Admin Dashboard</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_product.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="market-prices.php" class="active"><i class="fas fa-chart-line"></i> Market Prices</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Add Market Price</h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Market price added successfully! <a href="market-prices.php">View all market prices</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 600px; margin: 0;">
                <form action="add-market-price.php" method="post">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo isset($_POST['product_name']) ? $_POST['product_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select category</option>
                            <option value="vegetable" <?php echo (isset($_POST['category']) && $_POST['category'] == 'vegetable') ? 'selected' : ''; ?>>Vegetable</option>
                            <option value="fertilizer" <?php echo (isset($_POST['category']) && $_POST['category'] == 'fertilizer') ? 'selected' : ''; ?>>Fertilizer</option>
                            <option value="seed" <?php echo (isset($_POST['category']) && $_POST['category'] == 'seed') ? 'selected' : ''; ?>>Seed</option>
                            <option value="insecticide" <?php echo (isset($_POST['category']) && $_POST['category'] == 'insecticide') ? 'selected' : ''; ?>>Insecticide</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-buttons" style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">Add Market Price</button>
                        <a href="market-prices.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
