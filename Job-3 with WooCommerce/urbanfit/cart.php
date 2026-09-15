<?php
require_once __DIR__ . '/header.php';

// Handle removal
if (isset($_GET['remove'])) {
    removeFromCart($_GET['remove']);
    header("Location: cart.php");
    exit;
}

// Handle clear
if (isset($_GET['clear'])) {
    clearCart();
    header("Location: cart.php");
    exit;
}

$cart = getCart();
$totals = getCartTotals();
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem;">Shopping Cart</h1>
    <p style="color: var(--text-muted);">Review selected apparel items, size variants, and shipping calculations.</p>
</div>

<!-- SSL Security Padlock Notification -->
<div class="ssl-badge-box">
    <div style="font-size: 1.8rem;">🔒</div>
    <div>
        <strong style="font-size: 1rem; display: block; color: var(--text-main);">HTTPS / SSL Encrypted Session Active</strong>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Your transaction details are protected by 256-Bit SSL Transport Layer Security.</span>
    </div>
</div>

<?php if (empty($cart)): ?>
    <div style="text-align: center; padding: 4rem 2rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
        <h2 style="font-family: var(--font-heading); font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">Your Cart is Currently Empty</h2>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Choose from our collection of fashion apparel to add items to your cart.</p>
        <a href="shop.php" class="btn-primary">Browse Shop Collection →</a>
    </div>
<?php else: ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Cart Items Table -->
        <div>
            <table class="cart-table" style="background: var(--bg-card); border-radius: var(--radius-md); overflow: hidden;">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Selected Size</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $key => $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <strong style="color: var(--text-main); display: block; font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">SKU: <?php echo htmlspecialchars($item['sku']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="background: var(--accent-emerald-glow); border: 1px solid var(--accent-emerald); color: var(--accent-emerald); padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">
                                    Size: <?php echo htmlspecialchars($item['size']); ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;"><?php echo formatPrice($item['price']); ?></td>
                            <td style="font-weight: 700;"><?php echo $item['quantity']; ?></td>
                            <td style="font-weight: 700; color: var(--accent-emerald);"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo urlencode($key); ?>" style="color: var(--accent-rose); font-size: 0.85rem; font-weight: 600;">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="shop.php" class="btn-secondary">← Continue Shopping</a>
                <a href="cart.php?clear=1" style="color: var(--accent-rose); font-size: 0.9rem; font-weight: 600;">Clear All Cart Items</a>
            </div>
        </div>

        <!-- Order Summary Box -->
        <div>
            <div class="order-summary-box">
                <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; color: var(--text-main);">
                    Order Summary
                </h3>

                <div class="summary-line">
                    <span style="color: var(--text-muted);">Items Subtotal</span>
                    <strong style="color: var(--text-main);"><?php echo formatPrice($totals['subtotal']); ?></strong>
                </div>

                <div class="summary-line">
                    <div>
                        <span style="color: var(--text-muted); display: block;">EMS Shipping (Local)</span>
                        <small style="color: var(--accent-cyan); font-size: 0.75rem;">Courier flat rate</small>
                    </div>
                    <strong style="color: var(--text-main);"><?php echo formatPrice($totals['shipping']); ?></strong>
                </div>

                <div class="summary-line total">
                    <span>Grand Total</span>
                    <span><?php echo formatPrice($totals['total']); ?></span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <a href="checkout.php" class="btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.05rem;">
                        Proceed to Checkout 🔒
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
