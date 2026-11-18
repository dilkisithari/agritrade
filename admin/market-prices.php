<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM market_prices WHERE id = $id";

    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_message'] = "Market price deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting market price: " . mysqli_error($conn);
    }

    header("Location: market-prices.php");
    exit();
}

// Handle update from edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $updated_by = $_SESSION['user_id'];
    $updated_at = date('Y-m-d H:i:s');

    $update_query = "UPDATE market_prices SET product_name='$product_name', category='$category', price=$price, updated_by=$updated_by, updated_at='$updated_at' WHERE id=$id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Market price updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating market price: " . mysqli_error($conn);
    }

    header("Location: market-prices.php");
    exit();
}

// Get market prices
$query = "SELECT mp.*, u.username FROM market_prices mp 
         JOIN users u ON mp.updated_by = u.id 
         ORDER BY mp.updated_at DESC";
$result = mysqli_query($conn, $query);

// For the popup edit form: get data if edit_id is set in GET
$edit_price = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM market_prices WHERE id = $edit_id LIMIT 1");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_price = mysqli_fetch_assoc($edit_result);
    } else {
        $_SESSION['error_message'] = "Market price not found for editing.";
        header("Location: market-prices.php");
        exit();
    }
}

// Categories for dropdown
$categories = ['vegetables', 'fruits', 'grains', 'dairy', 'others'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Market Prices - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* Popup modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: white;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .modal h3 {
            margin-top: 0;
        }
        .modal .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 22px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .modal .close-btn:hover {
            color: #000;
        }
        .modal form label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
        }
        .modal form input[type="text"],
        .modal form select,
        .modal form input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        .modal form button {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal form button:hover {
            background-color: #0056b3;
        }

        /* Table price cell style with Rs */
        td.price-cell {
            white-space: nowrap;
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
                <h2>Market Prices</h2>
                <a href="add-market-price.php" class="btn btn-primary">Add Market Price</a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Updated By</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($row['category'])); ?></td>
                                <td class="price-cell">Rs <?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('M d, Y, g:i a', strtotime($row['updated_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="market-prices.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                                    <a href="market-prices.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this market price?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No market prices found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal Popup -->
    <div class="modal-overlay <?php echo $edit_price ? 'active' : ''; ?>" id="editModal">
        <div class="modal">
            <span class="close-btn" id="closeModal">&times;</span>
            <h3>Edit Market Price</h3>
            <?php if ($edit_price): ?>
            <form method="POST" action="market-prices.php">
                <input type="hidden" name="edit_id" value="<?php echo $edit_price['id']; ?>" />
                
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" required value="<?php echo htmlspecialchars($edit_price['product_name']); ?>" />
                
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php if ($cat == $edit_price['category']) echo 'selected'; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="price">Price (Rs)</label>
                <input type="number" step="0.01" min="0" id="price" name="price" required value="<?php echo number_format($edit_price['price'], 2, '.', ''); ?>" />
                
                <button type="submit">Update</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Close modal when clicking close button or outside modal
        const modal = document.getElementById('editModal');
        const closeBtn = document.getElementById('closeModal');

        closeBtn.addEventListener('click', () => {
            window.location.href = "market-prices.php"; // Reload without ?edit= param to close popup
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                window.location.href = "market-prices.php";
            }
        });
    </script>
</body>
</html>
