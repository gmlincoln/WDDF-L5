<?php
/**
 * Activate Storefront & Configure E-Commerce Homepage for UrbanFit BD
 */
require_once __DIR__ . '/wp-load.php';

echo "Setting up Storefront Theme & E-Commerce Front Page...\n";

// 1. Switch Theme to Storefront
switch_theme('storefront');
echo "Active Theme Switched to: " . get_option('stylesheet') . "\n";

// 2. Set Front Page to WooCommerce Shop Page
$shop_page_id = wc_get_page_id('shop');
if ($shop_page_id) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $shop_page_id);
    echo "Front page set to WooCommerce Shop Page (ID: $shop_page_id)\n";
}

// 3. Setup Custom E-Commerce Primary Menu
$menu_name = 'UrbanFit Main Navigation';
$menu_exists = wp_get_nav_menu_object($menu_name);

if ($menu_exists) {
    wp_delete_nav_menu($menu_name);
}

$menu_id = wp_create_nav_menu($menu_name);

// Add Home / Shop Page
wp_update_nav_menu_item($menu_id, 0, array(
    'menu-item-title'   => 'Shop Catalog',
    'menu-item-object-id' => $shop_page_id,
    'menu-item-object'  => 'page',
    'menu-item-type'    => 'post_type',
    'menu-item-status'  => 'publish'
));

// Add Categories: Men's Fashion, Women's Fashion, Accessories
$categories = array("Men's Fashion", "Women's Fashion", "Accessories");
foreach ($categories as $cat_name) {
    $term = get_term_by('name', $cat_name, 'product_cat');
    if ($term) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title'     => $term->name,
            'menu-item-object-id' => $term->term_id,
            'menu-item-object'    => 'product_cat',
            'menu-item-type'      => 'taxonomy',
            'menu-item-status'    => 'publish'
        ));
    }
}

// Add Cart & Checkout Pages
$cart_id = wc_get_page_id('cart');
if ($cart_id) {
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title'   => 'Cart',
        'menu-item-object-id' => $cart_id,
        'menu-item-object'  => 'page',
        'menu-item-type'    => 'post_type',
        'menu-item-status'  => 'publish'
    ));
}

$checkout_id = wc_get_page_id('checkout');
if ($checkout_id) {
    wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title'   => 'Checkout',
        'menu-item-object-id' => $checkout_id,
        'menu-item-object'  => 'page',
        'menu-item-type'    => 'post_type',
        'menu-item-status'  => 'publish'
    ));
}

// Assign Menu to Theme Location
$locations = get_theme_mod('nav_menu_locations');
$locations['primary'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "Primary Navigation Menu Configured Successfully!\n";

// 4. Flush Rewrite Rules
flush_rewrite_rules();

echo "E-Commerce Homepage Configuration Completed!\n";
