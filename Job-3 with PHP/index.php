<?php
require_once __DIR__ . '/header.php';
$products = getAllProducts();
?>

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="hero-content">
        <span style="background: rgba(16, 185, 129, 0.2); color: var(--accent-emerald); font-weight: 700; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; border: 1px solid rgba(16, 185, 129, 0.4);">
            New Season Arrivals 2026
        </span>
        <h1 class="hero-title" style="margin-top: 1rem;">Elevate Your Style with UrbanFit BD</h1>
        <p class="hero-sub">Discover curated men's fashion, elegant women's wear, and trendy accessories with instant EMS local delivery across Bangladesh.</p>
        <div style="display: flex; gap: 1rem;">
            <a href="shop.php" class="btn-primary">Explore Full Shop →</a>
            <a href="contact.php" class="btn-secondary">Store Information</a>
        </div>
    </div>
</section>

<!-- Store Features Highlight -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🚚</div>
        <h4 style="font-family: var(--font-heading); font-weight: 700;">EMS Express Delivery</h4>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Fast local shipping rate calculated flat at ৳120</p>
    </div>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💳</div>
        <h4 style="font-family: var(--font-heading); font-weight: 700;">Mobile Banking & COD</h4>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Instant bKash, Nagad, Rocket or Cash on Delivery</p>
    </div>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔒</div>
        <h4 style="font-family: var(--font-heading); font-weight: 700;">HTTPS & SSL Secured</h4>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">256-bit encrypted checkout and order verification</p>
    </div>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📱</div>
        <h4 style="font-family: var(--font-heading); font-weight: 700;">Instant SMS & Email Alerts</h4>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Real-time automated notification logged on order</p>
    </div>
</div>

<!-- Featured Collection -->
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800;">Featured Collection</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Handpicked 8 premium products from our catalog</p>
    </div>
    <a href="shop.php" style="color: var(--accent-emerald); font-weight: 700; font-size: 0.95rem;">View All (8 Products) →</a>
</div>

<!-- Product Cards Grid -->
<div class="product-grid">
    <?php foreach ($products as $p): ?>
        <div class="product-card">
            <div class="card-img-wrap">
                <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                <span class="badge-category"><?php echo htmlspecialchars($p['category'] ?? $p['category_name'] ?? ''); ?></span>
                <?php if ((int)$p['stock'] > 0): ?>
                    <span class="badge-stock in-stock">In Stock (<?php echo $p['stock']; ?>)</span>
                <?php else: ?>
                    <span class="badge-stock out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="product-sku">SKU: <?php echo htmlspecialchars($p['sku']); ?></div>
                <h3 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h3>
                <div class="product-price"><?php echo formatPrice($p['price']); ?></div>
                
                <div class="sizes-preview">
                    <span style="font-size: 0.75rem; color: var(--text-muted); width: 100%; margin-bottom: 0.2rem;">Sizes Available:</span>
                    <?php 
                    $sizesList = is_array($p['sizes']) ? $p['sizes'] : explode(',', $p['sizes']);
                    foreach ($sizesList as $sz):
                    ?>
                        <span class="size-pill"><?php echo trim(htmlspecialchars($sz)); ?></span>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: auto;">
                    <?php if ((int)$p['stock'] > 0): ?>
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="btn-primary" style="width: 100%; justify-content: center;">
                            Select Size & Buy
                        </a>
                    <?php else: ?>
                        <button disabled class="btn-primary btn-disabled" style="width: 100%; justify-content: center;">
                            🚫 Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
