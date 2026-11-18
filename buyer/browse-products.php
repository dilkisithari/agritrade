<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$sort = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'name_asc';

$query = "SELECT p.*, u.username FROM products p JOIN users u ON p.supplier_id = u.id WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if (!empty($category)) {
    $query .= " AND p.category = '$category'";
}

switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    default:
        $query .= " ORDER BY p.name ASC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Browse Products - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Modal Styles -->
    <style>
        #cartModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            color: #333;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 350px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.3s ease-in-out;
            font-family: 'Segoe UI', sans-serif;
        }

        #cartModal p {
            font-size: 16px;
            margin: 0 0 15px;
            font-weight: 500;
        }

        #cartModal button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        #cartModal button:hover {
            background-color: #45a049;
        }

        #modalBackdrop {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
    </style>
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
            <h2>Browse Products</h2>
        </div>

        <div class="filter-container" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: var(--shadow); margin-bottom: 20px;">
            <form action="browse-products.php" method="get" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Search products..." />
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="vegetable" <?php echo ($category == 'vegetable') ? 'selected' : ''; ?>>Vegetables</option>
                        <option value="fertilizer" <?php echo ($category == 'fertilizer') ? 'selected' : ''; ?>>Fertilizers</option>
                        <option value="seed" <?php echo ($category == 'seed') ? 'selected' : ''; ?>>Seeds</option>
                        <option value="insecticide" <?php echo ($category == 'insecticide') ? 'selected' : ''; ?>>Insecticides</option>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort">
                        <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="height: 40px;">Filter</button>
                <a href="browse-products.php" class="btn btn-secondary" style="height: 40px;">Reset</a>
            </form>
        </div>

        <div class="products-grid" style="margin-bottom: 30px;">
            <?php 
            if (mysqli_num_rows($result) > 0):
                while ($product = mysqli_fetch_assoc($result)): 
            ?>
                <div class="product-card">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" />
                    <?php else: ?>
                        <img src="../assets/images/product-placeholder.jpg" alt="Product placeholder" />
                    <?php endif; ?>
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price">Rs.<?php echo $product['price']; ?></p>
                    <p class="category"><?php echo ucfirst($product['category']); ?></p>
                    <p class="seller">Seller: <?php echo $product['username']; ?></p>
                    <div class="product-actions">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                        <button class="btn btn-secondary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px 0;">
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="cartModal">
    <p id="cartMessage">Product added to cart!</p>
    <button onclick="closeCartModal()">OK</button>
</div>

<div id="modalBackdrop"></div>

<script src="../assets/js/main.js"></script>
<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {
        const productId = this.getAttribute('data-id');
        fetch('add_to_cart.php?id=' + productId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cartMessage').textContent = data.message;
                    document.getElementById('cartModal').style.display = 'block';
                    document.getElementById('modalBackdrop').style.display = 'block';
                    setTimeout(() => {
                        closeCartModal();
                    }, 3000);
                } else {
                    alert(data.message || 'Failed to add product to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong!');
            });
    });
});

function closeCartModal() {
    document.getElementById('cartModal').style.display = 'none';
    document.getElementById('modalBackdrop').style.display = 'none';
}
</script>
</body>
</html>
