<?php
require_once __DIR__ . '/header.php';

$id = $_GET['id'] ?? 1;
$product = getProductById($id);

if (!$product) {
    echo "<div class='alert alert-danger'>Product not found! <a href='shop.php'>Return to shop</a></div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$errorMessage = '';
$successMessage = '';

// Handle Add to Cart submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $selectedSize = trim($_POST['selected_size'] ?? '');
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    
    $res = addToCart($product['id'], $selectedSize, $qty);
    if ($res['success']) {
        $successMessage = $res['message'];
        header("Location: cart.php?added=1");
        exit;
    } else {
        $errorMessage = $res['message'];
    }
}

$isOutOfStock = (int)$product['stock'] <= 0;
$sizes = is_array($product['sizes']) ? $product['sizes'] : explode(',', $product['sizes']);
?>

<div style="margin-bottom: 1.5rem;">
    <a href="shop.php" style="color: var(--text-muted); font-size: 0.9rem;">← Back to Shop Products</a>
</div>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="alert alert-success">✓ <?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<div class="detail-layout">
    <!-- Gallery -->
    <div class="detail-gallery">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>

    <!-- Product Info Form -->
    <div class="detail-info">
        <span style="color: var(--accent-cyan); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; margin-bottom: 0.5rem; display: block;">
            <?php echo htmlspecialchars($product['category'] ?? $product['category_name'] ?? ''); ?>
        </span>

        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem; line-height: 1.2;">
            <?php echo htmlspecialchars($product['name']); ?>
        </h1>

        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
            SKU Identifier: <strong style="color: var(--text-main);"><?php echo htmlspecialchars($product['sku']); ?></strong>
        </div>

        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-emerald); margin-bottom: 1.5rem;">
            <?php echo formatPrice($product['price']); ?>
        </div>

        <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6;">
            <?php echo htmlspecialchars($product['description']); ?>
        </p>

        <!-- Stock Status Badge -->
        <div style="margin-bottom: 1.5rem;">
            <?php if (!$isOutOfStock): ?>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: var(--accent-emerald); padding: 0.4rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.9rem;">
                    <span>● In Stock</span>
                    <span style="opacity: 0.7;">(<?php echo $product['stock']; ?> items remaining)</span>
                </div>
            <?php else: ?>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(244, 63, 94, 0.2); border: 1px solid rgba(244, 63, 94, 0.5); color: var(--accent-rose); padding: 0.4rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.9rem;">
                    <span>🚫 Stock = 0 (OUT OF STOCK)</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add to Cart Form -->
        <form method="POST" action="product.php?id=<?php echo $product['id']; ?>" id="add-to-cart-form">
            <input type="hidden" name="action" value="add_to_cart">

            <!-- Size Selector (MANDATORY REQUIREMENT) -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.5rem; color: var(--text-main);">
                    Select Size Variant <span style="color: var(--accent-rose);">* (Required)</span>
                </label>
                <div class="size-selector">
                    <?php foreach ($sizes as $idx => $sz): 
                        $szTrim = trim($sz);
                    ?>
                        <div class="size-opt">
                            <input type="radio" name="selected_size" id="size_<?php echo $idx; ?>" value="<?php echo htmlspecialchars($szTrim); ?>" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                            <label for="size_<?php echo $idx; ?>" class="size-btn-label"><?php echo htmlspecialchars($szTrim); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted);">* You must select a size option before adding to your shopping cart.</p>
            </div>

            <!-- Quantity Selector -->
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 700; font-family: var(--font-heading); margin-bottom: 0.5rem; color: var(--text-main);">Quantity</label>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo max(1, (int)$product['stock']); ?>" class="form-control" style="width: 120px;" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
            </div>

            <!-- Action Button -->
            <?php if (!$isOutOfStock): ?>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 1.1rem; font-size: 1.1rem;">
                    🛒 Add to Cart Now
                </button>
            <?php else: ?>
                <button type="button" disabled class="btn-primary btn-disabled" style="width: 100%; justify-content: center; padding: 1.1rem; font-size: 1.1rem;">
                    🚫 Out of Stock - Purchase Blocked
                </button>
                <p style="color: var(--accent-rose); font-size: 0.85rem; text-align: center; margin-top: 0.75rem;">
                    System Protection: Items with stock = 0 cannot be added to cart.
                </p>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
