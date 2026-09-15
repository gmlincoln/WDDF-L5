<?php
define('WP_USE_THEMES', false);
require('wp-load.php');
flush_rewrite_rules();

$urls = array(
    'Homepage' => 'http://localhost/urbanfit/',
    'Shop'     => get_permalink(wc_get_page_id('shop')),
    'Cart'     => get_permalink(wc_get_page_id('cart')),
    'Checkout' => get_permalink(wc_get_page_id('checkout')),
);

foreach ($urls as $name => $url) {
    $res = @file_get_contents($url);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : 'FAILED';
    echo "$name ($url): $status\n";
}
