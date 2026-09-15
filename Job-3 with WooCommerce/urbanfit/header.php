<?php
require_once __DIR__ . '/config.php';
$cartTotals = getCartTotals();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo STORE_NAME; ?> - Premium Fashion & Apparel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- SSL Security Header Bar -->
    <div class="ssl-header-bar">
        <span>🔒 256-Bit SSL Secured Checkout & Encrypted Connection Active</span>
        <span style="opacity: 0.6;">|</span>
        <span>🚚 EMS Express Local Delivery (Flat Rate ৳120)</span>
    </div>

    <!-- Header Navigation -->
    <header class="site-header">
        <div class="header-container">
            <a href="index.php" class="logo-brand">
                <div class="logo-icon">U</div>
                <span>UrbanFit <span style="color: var(--accent-emerald);">BD</span></span>
            </a>

            <ul class="nav-links">
                <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="shop.php" class="<?php echo $currentPage === 'shop.php' ? 'active' : ''; ?>">Shop Store</a></li>
                <li><a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a></li>
                <li><a href="admin.php" class="<?php echo $currentPage === 'admin.php' ? 'active' : ''; ?>">Admin Panel</a></li>
            </ul>

            <a href="cart.php" class="cart-badge-btn">
                <span>🛒 Cart</span>
                <span class="cart-count-bubble"><?php echo $cartTotals['count']; ?></span>
            </a>
        </div>
    </header>

    <main class="main-wrapper">
