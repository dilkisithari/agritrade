<?php include_once __DIR__ . '/base.php';
$__dash = '/index.php';
if (isset($_SESSION['user_id'])) {
    $__r = $_SESSION['role'] ?? '';
    if (in_array($__r, ['admin','supplier','buyer','logistics'])) $__dash = '/' . $__r . '/dashboard.php';
}
?>
<header>
    <div class="container">
        <div class="logo">
            <a href="<?php echo $BASE_URL . $__dash; ?>">
                <h1><i class="fas fa-leaf"></i> AgriTrade</h1>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="<?php echo $BASE_URL . $__dash; ?>">Home</a></li>
                <li><a href="<?php echo $BASE_URL; ?>/products.php">Products</a></li>
                <li><a href="<?php echo $BASE_URL; ?>/about.php">About Us</a></li>
                <li><a href="<?php echo $BASE_URL; ?>/contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="<?php echo $BASE_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] == 'supplier'): ?>
                        <li><a href="<?php echo $BASE_URL; ?>/supplier/dashboard.php">Supplier Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] == 'buyer'): ?>
                        <li><a href="<?php echo $BASE_URL; ?>/buyer/dashboard.php">Buyer Dashboard</a></li>
                    <?php elseif ($_SESSION['role'] == 'logistics'): ?>
                        <li><a href="<?php echo $BASE_URL; ?>/logistics/dashboard.php">Logistics Dashboard</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="<?php echo $BASE_URL; ?>/auth/login.php">Login</a></li>
                    <li><a href="<?php echo $BASE_URL; ?>/auth/register.php" class="btn btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</header>
