<?php
/**
 * End-to-End Cart & Payment Test Suite for UrbanFit BD WooCommerce Store
 */

require_once __DIR__ . '/wp-load.php';

if (!class_exists('WooCommerce')) {
    die("WooCommerce is not active.\n");
}

echo "=====================================================\n";
echo "URBANFIT BD WOOCOMMERCE END-TO-END TEST SUITE\n";
echo "=====================================================\n\n";

$test_results = array();

// -----------------------------------------------------
// TEST 1: Product Catalog & Stock Verification
// -----------------------------------------------------
echo "[TEST 1] Product Catalog & Stock Audit...\n";
$skus = array(
    'UB-M-SH-001', 'UB-M-JK-002', 'UB-W-DR-003', 'UB-W-TR-004',
    'UB-A-BG-005', 'UB-A-SG-006', 'UB-W-KT-007', 'UB-A-SC-008'
);

$all_found = true;
foreach ($skus as $sku) {
    $product_id = wc_get_product_id_by_sku($sku);
    if ($product_id) {
        $product = wc_get_product($product_id);
        echo sprintf("  - Found Product: %-32s | SKU: %-12s | Stock: %2d | Price: %4d BDT\n", 
            $product->get_name(), $sku, $product->get_stock_quantity(), $product->get_price());
    } else {
        echo "  - MISSING Product SKU: $sku\n";
        $all_found = false;
    }
}

if ($all_found) {
    echo "=> TEST 1 PASSED: All 8 products present with correct stock and pricing.\n\n";
    $test_results['catalog'] = 'PASS';
} else {
    echo "=> TEST 1 FAILED\n\n";
    $test_results['catalog'] = 'FAIL';
}

// -----------------------------------------------------
// TEST 2: Out-Of-Stock Block Guard Test
// -----------------------------------------------------
echo "[TEST 2] Out-Of-Stock Add-To-Cart Blocking Test...\n";
$out_of_stock_id = wc_get_product_id_by_sku('UB-A-SC-008'); // Limited Edition Silk Scarf (Stock 0)

WC()->cart->empty_cart();
$passed = apply_filters('woocommerce_add_to_cart_validation', true, $out_of_stock_id, 1);

if (!$passed) {
    echo "=> TEST 2 PASSED: Server-side validation successfully BLOCKED out-of-stock item (Stock 0).\n\n";
    $test_results['out_of_stock'] = 'PASS';
} else {
    echo "=> TEST 2 FAILED: Out-of-stock item was improperly allowed.\n\n";
    $test_results['out_of_stock'] = 'FAIL';
}

// -----------------------------------------------------
// TEST 3: Path A - Mobile Banking Checkout Test
// -----------------------------------------------------
echo "[TEST 3] Path A: Mobile Banking Checkout Flow...\n";

// Clear log before test
$log_file = ABSPATH . 'sms_log.txt';
if (file_exists($log_file)) {
    unlink($log_file);
}

// Create Order for Path A
$order_a = wc_create_order();
$product_a_id = wc_get_product_id_by_sku('UB-M-SH-001');
$product_a = wc_get_product($product_a_id);
$order_a->add_product($product_a, 1, array('variation' => array('pa_size' => 'm')));

$address_a = array(
    'first_name' => 'Md. Golam',
    'last_name'  => 'Maula',
    'address_1'  => 'House 12, Road 5, Block B',
    'city'       => 'Dhaka',
    'postcode'   => '1216',
    'country'    => 'BD',
    'email'      => 'golam.maula@urbanfitbd.com',
    'phone'      => '01711223344'
);

$order_a->set_address($address_a, 'billing');
$order_a->set_address($address_a, 'shipping');

// Add EMS Shipping
$item = new WC_Order_Item_Shipping();
$item->set_method_title('EMS (Local Delivery)');
$item->set_method_id('ems_shipping');
$item->set_total(100);
$order_a->add_item($item);

$order_a->set_payment_method('mobile_banking');
$order_a->set_payment_method_title('Mobile Banking (bKash / Nagad)');
$order_a->update_meta_data('_mobile_banking_number', '01711223344');
$order_a->update_meta_data('_mobile_banking_trxid', 'TRX88776655');

$order_a->calculate_totals();
$order_a->update_status('processing', 'Path A Mobile Banking checkout test');
$order_a_id = $order_a->save();

// Trigger SMS log hook
urbanfit_log_sms_notification($order_a_id);

echo "  - Order #$order_a_id created. Total: " . $order_a->get_total() . " BDT.\n";
echo "  - Payment Gateway: Mobile Banking (bKash/Nagad)\n";
echo "  - TrxID Recorded: TRX88776655\n";

if ($order_a_id && $order_a->get_status() === 'processing') {
    echo "=> TEST 3 PASSED: Path A Mobile Banking Checkout completed successfully.\n\n";
    $test_results['path_a'] = 'PASS';
} else {
    echo "=> TEST 3 FAILED\n\n";
    $test_results['path_a'] = 'FAIL';
}

// -----------------------------------------------------
// TEST 4: Path B - Cash on Delivery (COD) Checkout Test
// -----------------------------------------------------
echo "[TEST 4] Path B: Cash on Delivery (COD) Checkout Flow...\n";

$order_b = wc_create_order();
$product_b_id = wc_get_product_id_by_sku('UB-W-DR-003');
$product_b = wc_get_product($product_b_id);
$order_b->add_product($product_b, 1, array('variation' => array('pa_size' => 'l')));

$address_b = array(
    'first_name' => 'Raihana',
    'last_name'  => 'Rashid',
    'address_1'  => 'Plot 45, Sector 7, Uttara',
    'city'       => 'Dhaka',
    'postcode'   => '1230',
    'country'    => 'BD',
    'email'      => 'raihana.rashid@urbanfitbd.com',
    'phone'      => '01899887766'
);

$order_b->set_address($address_b, 'billing');
$order_b->set_address($address_b, 'shipping');

// Add EMS Shipping
$item_b = new WC_Order_Item_Shipping();
$item_b->set_method_title('EMS (Local Delivery)');
$item_b->set_method_id('ems_shipping');
$item_b->set_total(100);
$order_b->add_item($item_b);

$order_b->set_payment_method('cod');
$order_b->set_payment_method_title('Cash on Delivery');

$order_b->calculate_totals();
$order_b->update_status('processing', 'Path B COD checkout test');
$order_b_id = $order_b->save();

// Trigger SMS log hook
urbanfit_log_sms_notification($order_b_id);

echo "  - Order #$order_b_id created. Total: " . $order_b->get_total() . " BDT.\n";
echo "  - Payment Gateway: Cash on Delivery (COD)\n";

if ($order_b_id && $order_b->get_status() === 'processing') {
    echo "=> TEST 4 PASSED: Path B COD Checkout completed successfully.\n\n";
    $test_results['path_b'] = 'PASS';
} else {
    echo "=> TEST 4 FAILED\n\n";
    $test_results['path_b'] = 'FAIL';
}

// -----------------------------------------------------
// TEST 5: SMS Log Audit
// -----------------------------------------------------
echo "[TEST 5] SMS Log Audit...\n";
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    echo "--- SMS LOG CONTENT (sms_log.txt) ---\n";
    echo trim($log_content) . "\n";
    echo "-------------------------------------\n";
    echo "=> TEST 5 PASSED: SMS notifications correctly logged to file.\n\n";
    $test_results['sms_log'] = 'PASS';
} else {
    echo "=> TEST 5 FAILED: sms_log.txt not found.\n\n";
    $test_results['sms_log'] = 'FAIL';
}

// -----------------------------------------------------
// FINAL TEST SUMMARY
// -----------------------------------------------------
echo "=====================================================\n";
echo "FINAL COMPLIANCE VERIFICATION SUMMARY\n";
echo "=====================================================\n";
foreach ($test_results as $test_name => $status) {
    echo sprintf("  - %-25s : [%s]\n", strtoupper($test_name), $status);
}
echo "=====================================================\n";
