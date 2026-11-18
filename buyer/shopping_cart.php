<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../auth/login.php");
    exit();
}

$buyer_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

$cart_query = "SELECT c.*, p.name, p.price, p.image_url FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.buyer_id = $buyer_id";
$cart_result = mysqli_query($conn, $cart_query);

if (!$cart_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Shopping Cart - AgriTrade</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* Scoped styling only for shopping cart table area */
        .table-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.07);
            max-width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            font-size: 15px;
        }

        thead tr {
            background-color: #2c3e50; /* keep your color scheme or adjust if needed */
            color: #fff;
            font-weight: 600;
            text-align: left;
            border-radius: 8px;
        }

        thead th {
            padding: 12px 15px;
        }

        tbody tr {
            background-color: #fefefe;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            border-radius: 8px;
        }

        tbody tr:hover {
            background-color: #f0f8ff;
        }

        tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: none;
        }

        tbody td img {
            border-radius: 8px;
            max-width: 60px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Quantity input and update button */
        form {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        input[type="number"] {
            width: 60px;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .btn {
            padding: 7px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color:  #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Grand total row styling */
        tbody tr.grand-total td {
            font-weight: 700;
            font-size: 16px;
            color: #333;
            text-align: right;
            padding-right: 25px;
            border-top: 2px solid #ddd;
        }

        tbody tr.grand-total td:nth-child(5),
        tbody tr.grand-total td:nth-child(6) {
            text-align: center;
            padding-left: 10px;
        }

        /* Place Order button container aligned right */
        .place-order-container {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
            padding: 10px 22px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background-color 0.25s ease;
            text-decoration: none;
        }

        .btn-success:hover {
            background-color: #1e8449;
        }

        /* Responsive tweaks */
        @media (max-width: 600px) {
            .table-container {
                padding: 15px 10px;
            }
            thead tr {
                font-size: 13px;
            }
            tbody tr {
                font-size: 14px;
            }
            input[type="number"] {
                width: 50px;
            }
            .btn, .btn-success {
                font-size: 13px;
                padding: 6px 12px;
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
                <li><a href="browse-products.php"><i class="fas fa-search"></i> Browse Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li><a href="shopping_cart.php" class="active"><i class="fas fa-shopping-basket"></i> Shopping Cart</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="buyer_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Shopping Cart</h2>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $has_items = false;
                        $grand_total = 0;
                        while ($item = mysqli_fetch_assoc($cart_result)):
                            $has_items = true;
                            $total = $item['price'] * $item['quantity'];
                            $grand_total += $total;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <img src="../<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                                </td>
                                <td>Rs.<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <form action="update-cart.php" method="post">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required />
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </form>
                                </td>
                                <td>Rs.<?php echo number_format($total, 2); ?></td>
                                <td>
                                    <a href="remove-from-cart.php?id=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$has_items): ?>
                            <tr><td colspan="6" style="text-align: center;">Your cart is empty.</td></tr>
                        <?php else: ?>
                            <tr class="grand-total">
                                <td colspan="4">Grand Total:</td>
                                <td colspan="2">Rs.<?php echo number_format($grand_total, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($has_items): ?>
                    <div class="place-order-container">
                        <a href="placeorder.php" class="btn btn-success">Place Order</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
