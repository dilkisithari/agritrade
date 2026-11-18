<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Query to get all products without supplier join
$query = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Products - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-content {
            padding: 40px 20px;
            width: 100%;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        .product-table th, .product-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .product-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .table-container {
            margin-top: 30px;
        }
        img.product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <div class="sidebar">
        <h3>Admin Dashboard</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_product.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="market-prices.php"><i class="fas fa-chart-line"></i> Market Prices</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2><i class="fas fa-box"></i> All Products</h2>

        <div class="table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" class="product-img">
                                <?php else: ?>
                                    <img src="../assets/images/product-placeholder.jpg" alt="No Image" class="product-img">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                            <td>Rs.<?php echo htmlspecialchars($product['price']); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
