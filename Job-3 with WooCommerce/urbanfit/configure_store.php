<?php
/**
 * WooCommerce Store Configurator for UrbanFit BD Assessment
 */

require_once __DIR__ . '/wp-load.php';

if (!class_exists('WooCommerce')) {
    die("WooCommerce is not active.\n");
}

echo "Configuring WooCommerce Settings for UrbanFit BD...\n";

// 1. Set Currency to BDT
update_option('woocommerce_currency', 'BDT');
update_option('woocommerce_currency_pos', 'left_space');
update_option('woocommerce_price_thousand_sep', ',');
update_option('woocommerce_price_decimal_sep', '.');
update_option('woocommerce_price_num_decimals', '0');

// 2. Enable COD Payment Gateway
$cod_settings = array(
    'enabled'          => 'yes',
    'title'            => 'Cash on Delivery (COD)',
    'description'      => 'Pay with cash upon delivery of your order.',
    'instructions'     => 'Pay with cash upon delivery.',
    'enable_for_methods' => array(),
    'enable_for_virtual' => 'yes',
);
update_option('woocommerce_cod_settings', $cod_settings);

// Enable Gateways in Payment Gateways order array
update_option('woocommerce_gateway_order', array(
    'mobile_banking' => 1,
    'cod'            => 2
));

// 3. Configure EMS Shipping Zone
global $wpdb;
$zone_name = "Bangladesh - Local EMS";
$zone_id = $wpdb->get_var($wpdb->prepare("SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones WHERE zone_name = %s", $zone_name));

if (!$zone_id) {
    $zone = new WC_Shipping_Zone();
    $zone->set_zone_name($zone_name);
    $zone->set_zone_order(1);
    $zone->add_location('BD', 'country');
    $zone_id = $zone->save();
} else {
    $zone = new WC_Shipping_Zone($zone_id);
}

// Add EMS shipping method to zone if not already present
$methods = $zone->get_shipping_methods();
$has_ems = false;
foreach ($methods as $m) {
    if ($m->id === 'ems_shipping' || $m->id === 'flat_rate') {
        $has_ems = true;
        break;
    }
}

if (!$has_ems) {
    $instance_id = $zone->add_shipping_method('flat_rate');
    update_option('woocommerce_flat_rate_' . $instance_id . '_settings', array(
        'title'      => 'EMS (Local Delivery)',
        'tax_status' => 'none',
        'cost'       => '100'
    ));
}

// 4. Force SSL / HTTPS Checkout Option
update_option('woocommerce_force_ssl_checkout', 'yes');

// 5. Ensure permalinks are set to Post Name for clean store URLs
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();

echo "WooCommerce Configuration Completed Successfully!\n";
