<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'urbanfit_db');
define('STORE_NAME', 'UrbanFit BD');
define('EMS_SHIPPING_FEE', 120.00);

// SSLCommerz Free Sandbox API Credentials & Endpoints
define('SSLC_STORE_ID', 'testbox');
define('SSLC_STORE_PASSWORD', 'qwerty');
define('SSLC_IS_SANDBOX', true);
define('SSLC_SANDBOX_INIT_URL', 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php');
define('SSLC_SANDBOX_VALIDATION_URL', 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            // Return null if database connection fails
            return null;
        }
    }
    return $pdo;
}

// Fallback JSON reader if database is not seeded yet
function getProductsFromJson() {
    $jsonPath = __DIR__ . '/Sample Product Data.json';
    if (file_exists($jsonPath)) {
        $content = file_get_contents($jsonPath);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function getAllProducts() {
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->query("SELECT * FROM products ORDER BY id ASC");
            $products = $stmt->fetchAll();
            if (!empty($products)) {
                foreach ($products as &$p) {
                    if (!isset($p['category']) && isset($p['category_name'])) {
                        $p['category'] = $p['category_name'];
                    }
                    if (!isset($p['category_name']) && isset($p['category'])) {
                        $p['category_name'] = $p['category'];
                    }
                    if (is_string($p['sizes'])) {
                        $p['sizes'] = json_decode($p['sizes'], true) ?: explode(',', $p['sizes']);
                    }
                }
                return $products;
            }
        } catch (Exception $e) {
            // fallback
        }
    }
    return getProductsFromJson();
}

function getProductById($id) {
    $products = getAllProducts();
    foreach ($products as $p) {
        if ((int)$p['id'] === (int)$id) {
            return $p;
        }
    }
    return null;
}

function formatPrice($amount) {
    return '৳' . number_format($amount, 2);
}

// Cart Management
function getCart() {
    return $_SESSION['cart'] ?? [];
}

function addToCart($productId, $size, $quantity = 1) {
    $product = getProductById($productId);
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found!'];
    }
    
    if ((int)$product['stock'] <= 0) {
        return ['success' => false, 'message' => 'Product is Out of Stock and cannot be added to cart.'];
    }
    
    if (empty($size)) {
        return ['success' => false, 'message' => 'Please select a size before adding to cart!'];
    }
    
    $cartKey = $productId . '_' . $size;
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'cart_key' => $cartKey,
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'image' => $product['image'],
            'size' => $size,
            'sku' => $product['sku'],
            'quantity' => $quantity
        ];
    }
    
    return ['success' => true, 'message' => 'Product added to cart!'];
}

function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartTotals() {
    $cart = getCart();
    $subtotal = 0;
    $itemCount = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $itemCount += $item['quantity'];
    }
    $shipping = $itemCount > 0 ? EMS_SHIPPING_FEE : 0;
    $total = $subtotal + $shipping;
    
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total,
        'count' => $itemCount
    ];
}

// Log SMS Simulation
function logSMSNotification($orderNumber, $phone, $message) {
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO sms_logs (order_number, phone_number, message, status) VALUES (?, ?, ?, 'Sent')");
            $stmt->execute([$orderNumber, $phone, $message]);
        } catch (Exception $e) {}
    }
    if (!isset($_SESSION['sms_logs'])) $_SESSION['sms_logs'] = [];
    $_SESSION['sms_logs'][] = [
        'order_number' => $orderNumber,
        'phone_number' => $phone,
        'message' => $message,
        'sent_at' => date('Y-m-d H:i:s')
    ];
}

// Log Email Simulation
function logEmailNotification($orderNumber, $email, $subject, $body) {
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO email_logs (order_number, recipient_email, subject, body, status) VALUES (?, ?, ?, ?, 'Delivered')");
            $stmt->execute([$orderNumber, $email, $subject, $body]);
        } catch (Exception $e) {}
    }
    if (!isset($_SESSION['email_logs'])) $_SESSION['email_logs'] = [];
    $_SESSION['email_logs'][] = [
        'order_number' => $orderNumber,
        'recipient_email' => $email,
        'subject' => $subject,
        'body' => $body,
        'sent_at' => date('Y-m-d H:i:s')
    ];
}

// Reduce Product Stock upon order
function reduceStock($productId, $quantity) {
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
            $stmt->execute([$quantity, $productId]);
        } catch (Exception $e) {}
    }
}
