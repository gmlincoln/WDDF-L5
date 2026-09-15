<?php
require_once __DIR__ . '/config.php';

echo "<h2>UrbanFit BD Database Seeder</h2>";

try {
    $rawPdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $rawPdo->exec($sql);
        echo "<p style='color:green;'>✓ Database `urbanfit_db` schema created successfully!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Database Schema Initialization Notice: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$db = getDB();
if (!$db) {
    echo "<p style='color:orange;'>Running in memory JSON mode (MySQL PDO connection unavailable). Application will serve directly from Sample Product Data.json.</p>";
    exit;
}

$jsonData = getProductsFromJson();
if (empty($jsonData)) {
    echo "<p style='color:red;'>Error: Sample Product Data.json not found or invalid.</p>";
    exit;
}

// Seed Categories
$categories = array_unique(array_column($jsonData, 'category'));
$catMap = [];
foreach ($categories as $catName) {
    $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $stmt->execute([$catName]);
    
    $fetchStmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $fetchStmt->execute([$catName]);
    $catMap[$catName] = $fetchStmt->fetchColumn();
}

// Clear and Seed Products
$db->exec("DELETE FROM products");
$insertStmt = $db->prepare("INSERT INTO products (id, name, category_id, category_name, price, sku, stock, sizes, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$count = 0;
foreach ($jsonData as $p) {
    $catId = $catMap[$p['category']] ?? 1;
    $sizesJson = json_encode($p['sizes']);
    $insertStmt->execute([
        $p['id'],
        $p['name'],
        $catId,
        $p['category'],
        $p['price'],
        $p['sku'],
        $p['stock'],
        $sizesJson,
        $p['description'],
        $p['image']
    ]);
    $count++;
}

echo "<p style='color:green;'>✓ Successfully seeded {$count} products from Sample Product Data.json into MySQL database!</p>";
echo "<p><a href='index.php'>Go to UrbanFit BD Storefront →</a></p>";
