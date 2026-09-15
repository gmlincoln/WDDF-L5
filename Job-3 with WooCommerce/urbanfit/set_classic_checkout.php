<?php
require_once __DIR__ . '/wp-load.php';

$checkout_id = wc_get_page_id('checkout');

if ($checkout_id) {
    wp_update_post(array(
        'ID'           => $checkout_id,
        'post_content' => '[woocommerce_checkout]',
        'post_title'   => 'Checkout'
    ));
    echo "Checkout page content updated to [woocommerce_checkout] shortcode successfully!\n";
}
