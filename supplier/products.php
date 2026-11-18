<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supplier') {
    header("Location: ../auth/login.php");
    exit();
}

$supplier_id = $_SESSION['user_id'];

// Handle product update (from modal form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_product'])) {
        // Sanitize and validate inputs
        $product_id = intval($_POST['product_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);

        // Update query
        $sql_update = "UPDATE products SET 
            name = '$name',
            category = '$category',
            price = $price,
            quantity = $quantity,
            image_url = '$image_url'
            WHERE id = $product_id AND supplier_id = $supplier_id";

        mysqli_query($conn, $sql_update);
        // You may add error checking here
    }

    // Handle delete request
    if (isset($_POST['delete_product_id'])) {
        $delete_id = intval($_POST['delete_product_id']);
        $sql_delete = "DELETE FROM products WHERE id = $delete_id AND supplier_id = $supplier_id";
        mysqli_query($conn, $sql_delete);
    }

    // After POST, redirect to avoid form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch products after any changes
$query = "SELECT * FROM products WHERE supplier_id = $supplier_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Your existing styles here */
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
        .action-buttons a, .action-buttons button {
            margin-right: 8px;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .btn-edit {
            background-color: #007bff;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-add-product {
            background-color: #28a745;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .btn-add-product i {
            margin-right: 5px;
        }
        .table-container {
            margin-top: 30px;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; top: 0; 
            width: 100%; height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; 
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,.3);
        }
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
        }
        .modal form input[type="text"],
        .modal form input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            margin: 8px 0 16px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal form label {
            font-weight: 600;
        }
        .modal form button {
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="dashboard">
    <div class="sidebar">
        <h3>Supplier Dashboard</h3>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> My Products</a></li>
            <li><a href="add-product.php"><i class="fas fa-plus"></i> Add Product</a></li>
            <li><a href="supplier-orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="supplier-payment.php"><i class="fas fa-money-bill"></i> Payments</a></li>
            <li><a href="supplier_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-content">
        <h2><i class="fas fa-box"></i> My Products</h2>

        <a href="add-product.php" class="btn-add-product"><i class="fas fa-plus"></i> Add New Product</a>

        <div class="table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <tr data-product='<?php echo json_encode($product); ?>'>
                            <td>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                <?php else: ?>
                                    <img src="../assets/images/product-placeholder.jpg" alt="No Image" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($product['price'],2)); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td class="action-buttons">
                                <button class="btn-edit edit-btn" type="button"><i class="fas fa-edit"></i> Edit</button>

                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="modalCloseBtn">&times;</span>
        <h3>Edit Product</h3>
        <form method="post" id="editProductForm">
            <input type="hidden" name="product_id" id="editProductId">

            <label for="editName">Name</label>
            <input type="text" name="name" id="editName" required>

            <label for="editCategory">Category</label>
            <input type="text" name="category" id="editCategory" required>

            <label for="editPrice">Price</label>
            <input type="number" name="price" id="editPrice" step="0.01" min="0" required>

            <label for="editQuantity">Quantity</label>
            <input type="number" name="quantity" id="editQuantity" min="0" required>

            <label for="editImageUrl">Image URL</label>
            <input type="text" name="image_url" id="editImageUrl">

            <button type="submit" name="update_product">Save Changes</button>
        </form>
    </div>
</div>

<script>
    // Modal handling
    const modal = document.getElementById('editModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const editForm = document.getElementById('editProductForm');

    // Close modal when X is clicked
    modalCloseBtn.onclick = () => {
        modal.style.display = 'none';
    };

    // Close modal when clicking outside the modal content
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Fill and open modal on Edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const tr = button.closest('tr');
            const productData = JSON.parse(tr.getAttribute('data-product'));

            // Fill the form fields
            document.getElementById('editProductId').value = productData.id;
            document.getElementById('editName').value = productData.name;
            document.getElementById('editCategory').value = productData.category;
            document.getElementById('editPrice').value = productData.price;
            document.getElementById('editQuantity').value = productData.quantity;
            document.getElementById('editImageUrl').value = productData.image_url || '';

            // Show the modal
            modal.style.display = 'block';
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
