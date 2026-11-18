<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is supplier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

$supplier_id = $_SESSION['user_id'];
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validate input
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($price)) {
        $errors[] = "Price is required";
    } elseif (!is_numeric($price)) {
        $errors[] = "Price must be a number";
    }
    
    if (empty($quantity)) {
        $errors[] = "Quantity is required";
    } elseif (!is_numeric($quantity)) {
        $errors[] = "Quantity must be a number";
    }
    
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    
    // Handle image upload
    $image_url = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size should be less than 2MB";
        } else {
            $upload_dir = "../uploads/products/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . $_FILES['image']['name'];
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = "uploads/products/" . $file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // If no errors, proceed with adding product
    if (empty($errors)) {
        $query = "INSERT INTO products (name, description, price, category, quantity, image_url, supplier_id, featured) 
                  VALUES ('$name', '$description', '$price', '$category', '$quantity', '$image_url', '$supplier_id', '$featured')";
        
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
    <title>Add Product - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="sidebar">
            <h3>Supplier Dashboard</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> My Products</a></li>
                <li><a href="add-product.php" class="active"><i class="fas fa-plus"></i> Add Product</a></li>
                <li><a href="supplier-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="supplier-payment.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="supplier_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Add New Product</h2>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Product added successfully! <a href="products.php">View all products</a>
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
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="add-product.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
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
                    
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="price">Price (Rs.)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="1" value="<?php echo isset($_POST['quantity']) ? $_POST['quantity'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="image-preview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: center;">
                        <input type="checkbox" id="featured" name="featured" style="width: auto; margin-right: 10px;" <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?>>
                        <label for="featured" style="margin-bottom: 0;">Feature this product</label>
                    </div>
                    
                    <div class="form-buttons" style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">Add Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html>
