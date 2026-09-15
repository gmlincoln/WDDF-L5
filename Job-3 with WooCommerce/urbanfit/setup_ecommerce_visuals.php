<?php
/**
 * Setup E-Commerce Product Images & Hero Layout for UrbanFit BD
 */
require_once __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

echo "Downloading and attaching product images for WooCommerce store...\n";

$json_file = __DIR__ . '/Sample Product Data.json';
$products_data = json_decode(file_get_contents($json_file), true);

foreach ($products_data as $data) {
    $sku = $data['sku'];
    $image_url = $data['image'];
    $name = $data['name'];

    $product_id = wc_get_product_id_by_sku($sku);
    if (!$product_id) continue;

    // Check if thumbnail already set
    if (has_post_thumbnail($product_id)) {
        echo "Product $name ($sku) already has thumbnail.\n";
        continue;
    }

    echo "Fetching image for $name ($sku)... ";

    // Download image file
    $tmp = download_url($image_url);
    if (is_wp_error($tmp)) {
        echo "Failed download: " . $tmp->get_error_message() . "\n";
        continue;
    }

    $file_array = array(
        'name'     => sanitize_title($name) . '.jpg',
        'tmp_name' => $tmp
    );

    // Attach to product
    $attachment_id = media_handle_sideload($file_array, $product_id, $name);
    if (is_wp_error($attachment_id)) {
        @unlink($file_array['tmp_name']);
        echo "Failed media sideload: " . $attachment_id->get_error_message() . "\n";
        continue;
    }

    set_post_thumbnail($product_id, $attachment_id);
    echo "Attached Image ID $attachment_id to Product ID $product_id.\n";
}

echo "Image Setup Completed Successfully!\n";
