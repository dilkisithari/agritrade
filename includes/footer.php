<?php include_once __DIR__ . '/base.php'; ?>
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section about">
                <h3><i class="fas fa-leaf"></i> AgriTrade</h3>
                <p>Your one-stop platform for agricultural products, fertilizers, seeds, and insecticides.</p>
                <div class="contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@agritrade.com</p>
                </div>
                <div class="socials">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-section links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo $BASE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/products.php">Products</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/about.php">About Us</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/contact.php">Contact</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/auth/register.php">Register</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/auth/login.php">Login</a></li>
                </ul>
            </div>
            <div class="footer-section newsletter">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for updates on new products and offers.</p>
                <form action="<?php echo $BASE_URL; ?>/newsletter.php" method="post">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> AgriTrade. All rights reserved.</p>
        </div>
    </div>
</footer>
