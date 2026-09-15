<?php
require_once __DIR__ . '/wp-load.php';

$shop_id = wc_get_page_id('shop');

// Set front page to static page (Shop page)
update_option('show_on_front', 'page');
update_option('page_on_front', $shop_id);

// Clear custom shortcode from page_content so Storefront native archive renders product loop ONCE
wp_update_post(array(
    'ID'           => $shop_id,
    'post_content' => '',
    'post_title'   => 'UrbanFit BD Store'
));

// Update Payment Gateway order to include SSLCommerz
update_option('woocommerce_gateway_order', array(
    'sslcommerz'     => 1,
    'mobile_banking' => 2,
    'cod'            => 3
));

// Ensure SSLCommerz is enabled
$sslc_settings = array(
    'enabled' => 'yes',
    'title'   => 'SSLCommerz Online Gateway (Cards / bKash / Nagad / Net Banking)',
);
update_option('woocommerce_sslcommerz_settings', $sslc_settings);

flush_rewrite_rules();

echo "Homepage cleaned up and SSLCommerz gateway activated successfully!\n";
