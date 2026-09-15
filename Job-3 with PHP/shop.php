<?php
require_once __DIR__ . '/header.php';
$products = getAllProducts();

$selectedCategory = $_GET['cat'] ?? 'All';

if ($selectedCategory !== 'All') {
    $products = array_filter($products, function($p) use ($selectedCategory) {
        $cat = $p['category'] ?? $p['category_name'] ?? '';
        return strcasecmp($cat, $selectedCategory) === 0;
    });
}
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">UrbanFit BD Catalog</h1>
    <p style="color: var(--text-muted);">Browse all 8 products across Men's Fashion, Women's Fashion, and Accessories.</p>
</div>

<!-- Category Filter Navigation -->
<div class="filter-bar">
    <a href="shop.php?cat=All" class="filter-btn <?php echo $selectedCategory === 'All' ? 'active' : ''; ?>">All Products (8)</a>
    <a href="shop.php?cat=Men's Fashion" class="filter-btn <?php echo strpos($selectedCategory, "Men") !== false ? 'active' : ''; ?>">Men's Fashion</a>
    <a href="shop.php?cat=Women's Fashion" class="filter-btn <?php echo strpos($selectedCategory, "Women") !== false ? 'active' : ''; ?>">Women's Fashion</a>
    <a href="shop.php?cat=Accessories" class="filter-btn <?php echo $selectedCategory === 'Accessories' ? 'active' : ''; ?>">Accessories</a>
</div>

<!-- Products Grid -->
<div class="product-grid">
    <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--bg-card); border-radius: var(--radius-md);">
            <h3>No products found in this category.</h3>
            <p><a href="shop.php" style="color: var(--accent-emerald);">Show all products</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <div class="card-img-wrap">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    <span class="badge-category"><?php echo htmlspecialchars($p['category']); ?></span>
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
                        <span style="font-size: 0.75rem; color: var(--text-muted); width: 100%; margin-bottom: 0.2rem;">Available Sizes:</span>
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
                                Select Size & Add to Cart
                            </a>
                        <?php else: ?>
                            <button disabled class="btn-primary btn-disabled" style="width: 100%; justify-content: center;">
                                🚫 Out of Stock (Block Add-to-Cart)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
