<?php
require_once __DIR__ . '/wp-load.php';

$shop_id = wc_get_page_id('shop');

// Set front page to static page (Shop page)
update_option('show_on_front', 'page');
update_option('page_on_front', $shop_id);

// Remove blank template meta if any
delete_post_meta($shop_id, '_wp_page_template');

// Add products shortcode to Shop page content
$content = '[products columns="4" limit="12"]';

wp_update_post(array(
    'ID'           => $shop_id,
    'post_content' => $content,
    'post_title'   => 'UrbanFit BD Store'
));

echo "Homepage grid fixed successfully!\n";
