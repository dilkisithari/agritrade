<?php
session_start();
include 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - AgriTrade</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <section style="padding: 50px 0;">
            <div class="container">
                <h2>Contact Us</h2>
                <p style="margin-top:15px;"><i class="fas fa-envelope"></i> info@agritrade.com</p>
                <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                <p style="margin-top:15px;">For account or order help, please log in and contact the platform administrator.</p>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
