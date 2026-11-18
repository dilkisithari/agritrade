<?php
session_start();
include 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriTrade - Agricultural E-commerce Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class="hero" style="background: url(assets/images/farm3.jpg); background-size: cover; height: 600px;">
            <div class="container">
<div class="hero-content">
    <style>
        .hero-content h1 {
            color: white;
            border: 2px solid green;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 8px;
        }

        .hero-content p {
            color: white;
            margin-top: 10px;
        }

        /* Optional: Background to improve contrast */
        .hero-content {
           
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
        }

        .hero-buttons a {
            margin-right: 10px;
        }
    </style>

    <h1>Welcome to AgriTrade</h1>
    <p>Your one-stop platform for agricultural products, fertilizers, seeds, and insecticides</p>
    <div class="hero-buttons">
        <a href="auth/register.php" class="btn btn-primary">Register Now</a>
        <a href="auth/login.php" class="btn btn-secondary">Login</a>
    </div>
</div>

                <!-- <div class="hero-image">
                    <img src="assets/images/home.jpg" alt="Agricultural products">
                </div> -->
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Our Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-seedling"></i>
                        <h3>Quality Products</h3>
                        <p>Access high-quality agricultural products directly from verified suppliers</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-truck"></i>
                        <h3>Reliable Delivery</h3>
                        <p>Track your orders in real-time with our efficient logistics system</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-hand-holding-usd"></i>
                        <h3>Competitive Pricing</h3>
                        <p>Get the best market prices for all agricultural products</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Secure Transactions</h3>
                        <p>Safe and secure payment processing for all transactions</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="user-types">
            <div class="container">
                <h2>Who Can Use AgriTrade?</h2>
                <div class="user-types-grid">
                    <div class="user-type-card">
                        <img src="assets/images/supplier.jpg" alt="Supplier">
                        <h3>Suppliers</h3>
                        <p>List and sell your agricultural products, manage orders, and track sales</p>
                    </div>
                    <div class="user-type-card">
                        <img src="assets/images/buyer.jpg" alt="Buyer">
                        <h3>Buyers</h3>
                        <p>Browse and purchase quality agricultural products at competitive prices</p>
                    </div>
                    <div class="user-type-card">
                        <img src="assets/images/logistics.jpg" alt="Logistics">
                        <h3>Logistics</h3>
                        <p>Manage deliveries and ensure products reach buyers efficiently</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="products-preview">
            <div class="container">
                <h2>Featured Products</h2>
                <div class="products-grid">
                    <?php
                    // Fetch featured products
                    $query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
                    $result = mysqli_query($conn, $query);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="product-card">';
                            echo '<img src="' . $row['image_url'] . '" alt="' . $row['name'] . '">';
                            echo '<h3>' . $row['name'] . '</h3>';
                            echo '<p class="price">Rs.' . $row['price'] . '</p>';
                            echo '<p>' . substr($row['description'], 0, 100) . '...</p>';
                            echo '<a href="buyer/product-details.php?id=' . $row['id'] . '" class="btn btn-primary">View Details</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No featured products available.</p>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
