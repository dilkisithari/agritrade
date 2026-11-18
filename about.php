<?php
session_start();
include 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AgriTrade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <section style="padding: 50px 0;">
            <div class="container">
                <h2>About AgriTrade</h2>
                <p style="margin-top:15px;">AgriTrade is an online marketplace that connects farmers and suppliers of vegetables, fertilizers, seeds and insecticides with buyers, and arranges delivery through registered logistics partners.</p>
                <div class="features-grid" style="margin-top:30px;">
                    <div class="feature-card"><i class="fas fa-store"></i><h3>Suppliers</h3><p>List products, approve orders and track payments.</p></div>
                    <div class="feature-card"><i class="fas fa-shopping-basket"></i><h3>Buyers</h3><p>Browse products, order and upload payment slips.</p></div>
                    <div class="feature-card"><i class="fas fa-truck"></i><h3>Logistics</h3><p>Accept delivery jobs and update delivery status.</p></div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
