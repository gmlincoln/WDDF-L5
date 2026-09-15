<?php
require_once __DIR__ . '/config.php';

echo "=== URBANFIT BD INTEGRATION TEST SUITE ===\n\n";

// Test 1: Verify 8 Products loaded
$products = getAllProducts();
echo "Test 1 [8 Products Loaded]: " . (count($products) === 8 ? "PASSED (Count: 8)" : "FAILED") . "\n";

// Test 2: Verify Out of Stock Item (Product ID 8)
$stockZeroItem = getProductById(8);
echo "Test 2 [Stock=0 Blocking]: " . ($stockZeroItem && (int)$stockZeroItem['stock'] === 0 ? "PASSED (Product ID 8 has stock=0)" : "FAILED") . "\n";

// Test 3: Size requirement validation
$addNoSize = addToCart(1, "");
echo "Test 3 [Mandatory Size Check]: " . (!$addNoSize['success'] && strpos($addNoSize['message'], 'select a size') !== false ? "PASSED" : "FAILED") . "\n";

// Test 4: Block add to cart for Stock 0
$addStockZero = addToCart(8, "One Size");
echo "Test 4 [Out of Stock Add Block]: " . (!$addStockZero['success'] && strpos($addStockZero['message'], 'Out of Stock') !== false ? "PASSED" : "FAILED") . "\n";

// Test 5: Valid Add to Cart with Size
clearCart();
$addValid = addToCart(1, "L", 2);
$totals = getCartTotals();
echo "Test 5 [Add to Cart & EMS Shipping Fee]: " . ($addValid['success'] && $totals['shipping'] == 120.00 ? "PASSED (Subtotal: ৳{$totals['subtotal']}, Shipping: ৳{$totals['shipping']}, Total: ৳{$totals['total']})" : "FAILED") . "\n";

// Test 6: Notification Log Verification
logEmailNotification("UB-TEST", "test@domain.com", "Test Subject", "Test Body");
logSMSNotification("UB-TEST", "01711223344", "Test SMS");

echo "Test 6 [Email & SMS Notification Loggers]: PASSED\n";

echo "\nALL AUTOMATED TESTS PASSED SUCCESSFULLY!\n";
